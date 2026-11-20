<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingLocation;
use App\Models\User;
use App\Services\CourierManagementService;
use App\Services\ForecastService;
use App\Services\OrderStatusService;
use App\Services\OrderStockService;
use App\Services\SalesReportService;
use App\Services\WebNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function __construct(
        protected CourierManagementService $courierService,
        protected OrderStatusService $statusService,
        protected WebNotificationService $notificationService,
        protected SalesReportService $reportService,
        protected OrderStockService $orderStockService
    ) {}

    /**
     * Prediksi kebutuhan stok Moving Average 3 minggu (PPL-30).
     * Endpoint: GET /owner/prediksi
     */
    public function prediksi(ForecastService $forecast): View
    {
        $result = $forecast->getForecast();

        return view('owner.prediksi', [
            'isDataSufficient' => $result['isDataSufficient'],
            'predictions' => $result['predictions'],
        ]);
    }

    /**
     * Riwayat penjualan riil + grafik (PPL-30).
     * Endpoint: GET /owner/riwayat-penjualan
     */
    public function riwayatPenjualan(Request $request, SalesReportService $report): View
    {
        return view('owner.riwayat-penjualan', $report->getReport($request->query('periode')));
    }

    /**
     * Menampilkan antarmuka Dashboard Owner & Manajemen Antrean Pesanan (FR-8.1, FR-8.8).
     * Endpoint: GET /owner/dashboard
     */
    public function index(Request $request): View
    {
        // 1. Ringkasan Metrik Operasional Hari Ini
        // Menggunakan SalesReportService agar 100% sinkron dengan Halaman Riwayat Penjualan & Ekspor Excel (PPL-30 / FR-15)
        $harianReport = $this->reportService->getReport('harian');
        $todayRevenue = (int) ($harianReport['summary']['total_omzet'] ?? 0);

        // Fallback jika sales_history hari ini belum ada baris namun terdapat pesanan selesai hari ini di tabel orders
        if ($todayRevenue === 0) {
            $todayRevenue = (int) Order::where(function ($query) {
                    $query->whereDate('updated_at', today())
                          ->where('status', 'selesai');
                })
                ->orWhere(function ($query) {
                    $query->whereDate('created_at', today())
                          ->whereIn('status', ['diproses', 'dikirim', 'selesai']);
                })
                ->sum('subtotal'); // Omzet bersih tanpa ongkir sesuai FR-15 / BR-16
        }

        $pendingOrdersCount = Order::whereIn('status', ['menunggu_pembayaran', 'diproses'])->count();

        $needCourierCount = Order::where('status', 'diproses')
            ->whereNull('courier_id')
            ->where('metode_pemenuhan', 'pengiriman')
            ->count();

        $activeStock = Product::where('aktif', true)->sum('stok');

        // Peringatan stok menipis (FR-10.1: stok <= 5)
        $lowStockProducts = Product::where('aktif', true)
            ->where('stok', '<=', 5)
            ->get();
        $lowStockCount = $lowStockProducts->count();

        // Data Lokasi Lapak Aktif & Jam Operasional (Sinkron dengan Pengaturan Lapak PPL-25/PPL-30)
        $activeLocationId = \App\Models\Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
        $activeLocation = $activeLocationId ? ShippingLocation::find($activeLocationId) : null;
        if (! $activeLocation) {
            $activeLocation = ShippingLocation::where('aktif', true)->first();
        }

        $operational = app(\App\Services\LapakOperationalService::class)->getStatus();
        $jamBuka = $operational['jam_buka'];
        $jamTutup = $operational['jam_tutup'];
        $statusLapak = $operational['effective_status'];
        $isOutsideOperatingHours = $operational['is_outside_hours'];
        $lokasiAktif = $activeLocation;

        // 2. Query Daftar Pesanan Khusus Hari Ini dengan Filter Status, Pencarian, & Sorting (Point 8)
        $ordersQuery = Order::whereDate('created_at', today())
            ->with(['orderItems.product', 'payment', 'courier', 'user', 'statusHistories']);

        $currentStatus = $request->query('status', 'semua');
        if ($currentStatus !== 'semua' && in_array($currentStatus, ['menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])) {
            $ordersQuery->where('status', $currentStatus);
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama_penerima', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        $sort = $request->query('sort', 'terbaru');
        match ($sort) {
            'terlama'          => $ordersQuery->oldest('id'),
            'total_tertinggi'  => $ordersQuery->orderByDesc('total'),
            'total_terendah'   => $ordersQuery->orderBy('total', 'asc'),
            default            => $ordersQuery->latest('id'),
        };

        $orders = $ordersQuery->paginate(8)->withQueryString();

        // Format atribut pembantu untuk setiap pesanan
        $orders->getCollection()->transform(function ($order) {
            $this->formatOrderDisplay($order);
            return $order;
        });

        // 3. Data Pendukung Antarmuka
        $couriers = $this->courierService->getCouriers();
        $products = Product::where('aktif', true)->orderBy('id')->get();

        // Hitung jumlah pesanan per status khusus hari ini untuk badge filter tab
        $statusCounts = [
            'semua' => Order::whereDate('created_at', today())->count(),
            'menunggu_pembayaran' => Order::whereDate('created_at', today())->where('status', 'menunggu_pembayaran')->count(),
            'diproses' => Order::whereDate('created_at', today())->where('status', 'diproses')->count(),
            'dikirim' => Order::whereDate('created_at', today())->where('status', 'dikirim')->count(),
            'selesai' => Order::whereDate('created_at', today())->where('status', 'selesai')->count(),
            'dibatalkan' => Order::whereDate('created_at', today())->where('status', 'dibatalkan')->count(),
        ];

        // Buat notifikasi web dinamis (F14) tanpa tabel baru
        $notifications = $this->notificationService->getNotificationsForUser(Auth::user());

        return view('Owner Dashboard & Mini POS Page', compact(
            'todayRevenue',
            'pendingOrdersCount',
            'needCourierCount',
            'activeStock',
            'lowStockProducts',
            'lowStockCount',
            'activeLocation',
            'lokasiAktif',
            'jamBuka',
            'jamTutup',
            'statusLapak',
            'isOutsideOperatingHours',
            'orders',
            'couriers',
            'products',
            'currentStatus',
            'statusCounts',
            'notifications'
        ));
    }

    /**
     * API Daftar Pesanan untuk integrasi frontend/mobile (FR-8.1).
     * Endpoint: GET /api/owner/orders
     */
    public function apiOrdersIndex(Request $request): JsonResponse
    {
        $ordersQuery = Order::whereDate('created_at', today())
            ->with(['orderItems.product', 'payment', 'courier', 'user', 'statusHistories']);

        $status = $request->query('status');
        if ($status && in_array($status, ['menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])) {
            $ordersQuery->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama_penerima', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        $sort = $request->query('sort', 'terbaru');
        match ($sort) {
            'terlama'          => $ordersQuery->oldest('id'),
            'total_tertinggi'  => $ordersQuery->orderByDesc('total'),
            'total_terendah'   => $ordersQuery->orderBy('total', 'asc'),
            default            => $ordersQuery->latest('id'),
        };

        $orders = $ordersQuery->paginate($request->query('per_page', 10));

        $orders->getCollection()->transform(function ($order) {
            $this->formatOrderDisplay($order);
            return $order;
        });

        return response()->json([
            'success' => true,
            'data'    => $orders->items(),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * Mengambil detail pesanan dalam format JSON untuk modal interaktif (FR-8.2).
     * Endpoint: GET /owner/orders/{order}/detail
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['orderItems.product', 'payment', 'courier', 'user', 'statusHistories.user']);
        $this->formatOrderDisplay($order);

        $orderData = $order->toArray();
        $orderData['items'] = $order->orderItems->map(fn($it) => [
            'id'           => $it->id,
            'nama_produk'  => $it->nama_produk ?? optional($it->product)->nama,
            'jumlah'       => $it->jumlah,
            'harga_satuan' => (int) $it->harga_satuan,
            'subtotal'     => (int) $it->subtotal,
        ])->values()->all();

        $orderData['history'] = $order->statusHistories
            ->filter(fn($h) => !str_contains($h->keterangan ?? '', '[sales_recorded]'))
            ->map(fn($h) => [
                'status_sebelumnya' => $h->status_sebelumnya,
                'status_baru'       => $h->status_baru,
                'keterangan'        => $h->catatan ?? $h->keterangan,
                'waktu'             => $h->created_at ? $h->created_at->translatedFormat('d M Y, H:i') . ' WIB' : '-',
                'actor'             => optional($h->user)->name ?? 'Sistem',
            ])->values()->all();

        $orderData['customer'] = [
            'name'    => $order->nama_penerima ?? $order->nama_pelanggan ?? optional($order->user)->name ?? 'Pelanggan',
            'telepon' => $order->telepon ?? optional($order->user)->no_telepon ?? '-',
        ];
        $orderData['alamat_pengiriman'] = $order->alamat ?? $order->alamat_lokasi_asal ?? '-';
        $orderData['telepon_penerima']  = $order->telepon ?? optional($order->user)->no_telepon ?? '-';

        return response()->json([
            'success' => true,
            'data'    => $orderData,
        ]);
    }

    /**
     * Menugaskan kurir pada pesanan dan memperbarui status menjadi 'dikirim' (FR-8.3 & Bab 8).
     * Endpoint: POST /owner/orders/{order}/assign-courier
     */
    public function assignCourier(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $request->validate([
            'courier_id' => ['required', 'exists:users,id'],
        ], [
            'courier_id.required' => 'Silakan pilih kurir yang akan ditugaskan.',
            'courier_id.exists'   => 'Kurir yang dipilih tidak terdaftar di sistem.',
        ]);

        try {
            $updatedOrder = $this->courierService->assignCourier($order, (int)$request->courier_id, Auth::user());
            $courier = User::find($request->courier_id);

            $successMsg = 'Pesanan ' . $updatedOrder->kode . ' berhasil ditugaskan kepada ' . ($courier?->name ?? 'Kurir') . ' (Status: Dikirim).';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'data'    => $updatedOrder,
                ]);
            }

            return back()->with('success', $successMsg);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menandai selesai pesanan ambil sendiri yang telah diserahkan di lapak (Bab 8 & FR-8.3).
     * Endpoint: POST /owner/orders/{order}/complete-pickup
     */
    public function completePickup(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        if ($order->metode_pemenuhan !== 'ambil_sendiri') {
            $msg = 'Hanya pesanan ambil sendiri yang dapat diselesaikan langsung oleh Owner.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        try {
            $updatedOrder = $this->statusService->transition(
                $order,
                'selesai',
                Auth::user(),
                'Pesanan diserahkan langsung kepada pembeli di lapak.'
            );

            $this->orderStockService->recordSalesHistory($updatedOrder);

            $msg = 'Pesanan ambil sendiri #' . $updatedOrder->kode . ' berhasil diserahkan dan ditandai selesai!';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data'    => $updatedOrder,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mendapatkan daftar akun kurir (US-31 / F8).
     * Endpoint: GET /owner/couriers
     */
    public function couriersIndex(Request $request): JsonResponse|RedirectResponse
    {
        if (!$request->expectsJson()) {
            return redirect()->route('owner.dashboard', ['modal' => 'couriers']);
        }

        $couriers = $this->courierService->getCouriers();

        return response()->json([
            'success' => true,
            'data'    => $couriers,
            'meta'    => [
                'active_couriers_count' => $couriers->count(),
                'max_allowed_couriers'  => CourierManagementService::MAX_ACTIVE_COURIERS,
            ],
        ]);
    }

    /**
     * Membuat akun kurir baru dengan batas maksimal 2 akun aktif (US-31).
     * Endpoint: POST /owner/couriers
     */
    public function couriersStore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'telepon'               => ['nullable', 'string', 'max:20'],
        ], [
            'name.required'         => 'Nama kurir wajib diisi.',
            'email.required'        => 'Email kurir wajib diisi.',
            'email.unique'          => 'Email sudah terdaftar untuk pengguna lain.',
            'password.required'     => 'Kata sandi wajib diisi.',
            'password.min'          => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        try {
            $newCourier = $this->courierService->createCourier($validated);
            $msg = "Akun kurir {$newCourier->name} berhasil didaftarkan!";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data'    => $newCourier,
                ], 201);
            }

            return back()->with('success', $msg);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Menghapus akun kurir (US-31).
     * Endpoint: DELETE /owner/couriers/{user}
     */
    public function couriersDestroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            $this->courierService->deleteCourier($user);
            $msg = "Akun kurir {$user->name} berhasil dihapus.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                ]);
            }

            return back()->with('success', $msg);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Format visual atribut pesanan agar konsisten di antarmuka Blade & JSON.
     */
    private function formatOrderDisplay(Order $order): void
    {
        $order->subtotal_formatted = 'Rp ' . number_format($order->subtotal, 0, ',', '.');
        $order->ongkir_formatted = $order->ongkir > 0
            ? 'Rp ' . number_format($order->ongkir, 0, ',', '.')
            : 'Gratis';
        $order->total_formatted = 'Rp ' . number_format($order->total, 0, ',', '.');
        $order->tanggal_formatted = $order->created_at
            ? $order->created_at->isoFormat('D MMM Y • HH:mm') . ' WIB'
            : '-';

        $order->total_botol = $order->orderItems ? $order->orderItems->sum('jumlah') : 0;

        $statusConfig = [
            'menunggu_pembayaran' => [
                'label' => 'Menunggu Pembayaran',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-300',
                'dot'   => 'bg-amber-500',
                'icon'  => 'schedule',
            ],
            'diproses' => [
                'label' => 'Diproses (Butuh Kurir)',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-300',
                'dot'   => 'bg-rose-500',
                'icon'  => 'moped',
            ],
            'dikirim' => [
                'label' => 'Sedang Dikirim',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-300',
                'dot'   => 'bg-blue-500',
                'icon'  => 'two_wheeler',
            ],
            'selesai' => [
                'label' => 'Selesai',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'dot'   => 'bg-emerald-500',
                'icon'  => 'check_circle',
            ],
            'dibatalkan' => [
                'label' => 'Dibatalkan',
                'badge' => 'bg-slate-100 text-slate-700 border-slate-300',
                'dot'   => 'bg-slate-500',
                'icon'  => 'cancel',
            ],
        ];

        $cfg = $statusConfig[$order->status] ?? [
            'label' => ucfirst(str_replace('_', ' ', $order->status)),
            'badge' => 'bg-slate-100 text-slate-800 border-slate-300',
            'dot'   => 'bg-slate-500',
            'icon'  => 'info',
        ];

        $order->status_label = $cfg['label'];
        $order->status_badge = $cfg['badge'];
        $order->status_dot   = $cfg['dot'];
        $order->status_icon  = $cfg['icon'];
    }
}
