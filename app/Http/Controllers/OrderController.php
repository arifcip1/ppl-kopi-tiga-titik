<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Services\CheckoutException;
use App\Services\CheckoutService;
use App\Services\OrderStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Tampilkan formulir checkout dan ringkasan pesanan pelanggan (PPL-16 / PPL-20).
     * Endpoint: GET /checkout
     */
    public function showCheckout(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $cartItems = $user->cartItems()
            ->with('product')
            ->orderBy('id', 'asc')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('warning', 'Keranjang belanja Anda masih kosong. Silakan pilih menu kopi terlebih dahulu.');
        }

        $subtotal = 0;
        $totalJumlah = 0;

        foreach ($cartItems as $item) {
            $itemSubtotal = $item->product ? ($item->product->harga * $item->jumlah) : 0;
            $item->subtotal = $itemSubtotal;
            $subtotal += $itemSubtotal;
            $totalJumlah += $item->jumlah;
        }

        // Ongkos kirim default awal (akan dihitung dinamis via Leaflet peta / quote)
        $ongkir = 0;
        $total = $subtotal + $ongkir;

        // Data Lokasi Lapak Aktif & Jam Operasional (FR-11.4, FR-11.6, PPL-25)
        $activeLocationId = Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
        $lokasiAktif = $activeLocationId ? ShippingLocation::find($activeLocationId) : null;
        if (! $lokasiAktif) {
            $lokasiAktif = ShippingLocation::where('aktif', true)->first();
        }

        $jamBuka = Setting::where('key', 'jam_buka')->value('value') ?? '15:00';
        $jamTutup = Setting::where('key', 'jam_tutup')->value('value') ?? '18:00';
        $statusLapak = Setting::where('key', 'status_lapak')->value('value') ?? 'buka';

        return view('Deskop Cart & Checkout Page', compact(
            'cartItems',
            'subtotal',
            'totalJumlah',
            'ongkir',
            'total',
            'lokasiAktif',
            'jamBuka',
            'jamTutup',
            'statusLapak'
        ));
    }

    /**
     * Memproses checkout pesanan pelanggan (PPL-18).
     * Endpoint: POST /checkout
     */
    public function checkout(Request $request, CheckoutService $checkoutService): JsonResponse|RedirectResponse
    {
        $isAmbilSendiri = $request->input('metode_pemenuhan') === 'ambil_sendiri';

        $validated = $request->validate([
            'metode_pemenuhan' => ['nullable', 'string', 'in:pengiriman,ambil_sendiri'],
            'nama_penerima' => ['required', 'string', 'max:255'],
            'telepon'       => ['required', 'string', 'max:20'],
            'alamat'        => ['required', 'string'],
            'lat'           => [$isAmbilSendiri ? 'nullable' : 'required', 'numeric', 'between:-90,90'],
            'lng'           => [$isAmbilSendiri ? 'nullable' : 'required', 'numeric', 'between:-180,180'],
            'catatan'       => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'telepon.required'       => 'Nomor telepon wajib diisi.',
            'alamat.required'        => 'Alamat pengiriman wajib diisi.',
            'lat.required'           => 'Titik lokasi tujuan (latitude) wajib diisi.',
            'lat.numeric'            => 'Titik lokasi tujuan (latitude) harus berupa angka valid.',
            'lat.between'            => 'Titik lokasi tujuan (latitude) tidak valid.',
            'lng.required'           => 'Titik lokasi tujuan (longitude) wajib diisi.',
            'lng.numeric'            => 'Titik lokasi tujuan (longitude) harus berupa angka valid.',
            'lng.between'            => 'Titik lokasi tujuan (longitude) tidak valid.',
        ]);

        try {
            // Nilai finansial (subtotal, ongkir, total), stok, status, dan user_id dihitung murni di backend.
            $order = $checkoutService->createFromCart($request->user(), $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pesanan berhasil dibuat.',
                    'data'    => [
                        'id'         => $order->id,
                        'kode'       => $order->kode,
                        'subtotal'   => $order->subtotal,
                        'ongkir'     => $order->ongkir,
                        'total'      => $order->total,
                        'status'     => $order->status,
                        'expires_at' => $order->expires_at?->toISOString(),
                        'items'      => $order->orderItems,
                    ],
                ], 201);
            }

            return redirect()->route('payment')->with([
                'order_id' => $order->id,
                'success'  => 'Pesanan berhasil dibuat.',
            ]);
        } catch (CheckoutException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }

            return back()->withInput()->withErrors($e->errors())->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan sistem saat memproses pesanan. Silakan coba beberapa saat lagi.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses pesanan.');
        }
    }

    /**
     * Membatalkan pesanan oleh pelanggan yang berhak (PPL-18).
     * Endpoint: POST /orders/{order}/cancel
     */
    public function cancel(Request $request, $order, OrderStockService $stockService): JsonResponse|RedirectResponse
    {
        $orderModel = $order instanceof Order ? $order : Order::find($order);

        if (! $orderModel) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            abort(404, 'Pesanan tidak ditemukan.');
        }

        // Pastikan pesanan milik pengguna yang sedang login (aman terhadap perbedaan tipe data)
        if ((int) $orderModel->user_id !== (int) $request->user()->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk membatalkan pesanan ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membatalkan pesanan ini.');
        }

        // Validasi awal menggunakan aturan model Order (termasuk canBeCancelledByCustomer)
        if (! $orderModel->canBeCancelledByCustomer()) {
            $message = 'Pesanan tidak dapat dibatalkan.';
            if ($orderModel->payment && $orderModel->payment->isSuccess()) {
                $message = 'Pesanan yang sudah dibayar tidak dapat dibatalkan.';
            } elseif ($orderModel->status !== 'menunggu_pembayaran') {
                $message = 'Pesanan dengan status saat ini tidak dapat dibatalkan.';
            } elseif ($orderModel->expires_at && now()->greaterThanOrEqualTo($orderModel->expires_at)) {
                $message = 'Waktu pembayaran telah kedaluwarsa. Pesanan tidak dapat dibatalkan.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        // Delegasikan pengembalian stok dan perubahan status ke OrderStockService
        // dengan validasi kedaluwarsa atomik di dalam transaksi
        $restored = $stockService->cancelByCustomer(
            $orderModel,
            (int) $request->user()->id
        );

        if (! $restored) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pesanan tidak dapat dibatalkan atau waktu pembayaran telah kedaluwarsa.',
                ], 422);
            }

            return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
        }

        $orderModel->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pesanan berhasil dibatalkan dan stok telah dikembalikan.',
                'data'    => [
                    'id'                => $orderModel->id,
                    'kode'              => $orderModel->kode,
                    'status'            => $orderModel->status,
                    'stok_dikembalikan' => $orderModel->stok_dikembalikan,
                ],
            ], 200);
        }

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    /**
     * Tampilkan daftar riwayat pesanan milik pembeli yang sedang login (FR-8.6, FR-8.9 - PPL 14).
     * Endpoint: GET /riwayat-pesanan atau GET /api/orders
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();

        // Query pesanan khusus milik pembeli yang login (BR-24, FR-8.9)
        $query = $user->orders()
            ->with(['orderItems.product', 'payment', 'courier'])
            ->latest();

        // Filter status opsional untuk API / query string
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'sedang_berjalan' || $status === 'aktif') {
                $query->whereIn('status', ['menunggu_pembayaran', 'diproses', 'dikirim']);
            } elseif ($status === 'selesai_riwayat' || $status === 'arsip') {
                $query->whereIn('status', ['selesai', 'dibatalkan']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->get();

        // Format data pesanan untuk kemudahan tampilan & API
        $orders->each(function ($order) {
            $this->formatOrderAttributes($order);
        });

        // Pisahkan menjadi pesanan aktif (sedang berjalan) dan pesanan selesai untuk tab antarmuka
        $activeOrders = $orders->filter(function ($order) {
            return in_array($order->status, ['menunggu_pembayaran', 'diproses', 'dikirim']);
        })->values();

        $completedOrders = $orders->filter(function ($order) {
            return in_array($order->status, ['selesai', 'dibatalkan']);
        })->values();

        // Jika request menginginkan JSON (API)
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil daftar riwayat pesanan',
                'data' => [
                    'total' => $orders->count(),
                    'active_count' => $activeOrders->count(),
                    'completed_count' => $completedOrders->count(),
                    'orders' => $orders,
                ],
            ], 200);
        }

        $activeTab = $request->query('tab', 'berjalan');

        return view('Riwayat Pesanan Page', compact('orders', 'activeOrders', 'completedOrders', 'activeTab'));
    }

    /**
     * Tampilkan detail pesanan dan pemantauan status pesanan pembeli (FR-8.2, FR-8.6 - PPL 14).
     * Endpoint: GET /riwayat-pesanan/{kode} atau GET /api/orders/{kode} atau GET /order-tracking/{kode}
     */
    public function show(Request $request, string $kode): View|JsonResponse
    {
        $user = Auth::user();

        // Cari pesanan berdasarkan kode atau ID
        $order = Order::where(function ($q) use ($kode) {
            $q->where('kode', $kode)->orWhere('id', $kode);
        })->first();

        if (!$order) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }
            abort(404, 'Pesanan tidak ditemukan.');
        }

        // Keamanan Otorisasi: Pembeli HANYA dapat melihat pesanannya sendiri (FR-8.6, FR-8.9)
        if ($order->user_id !== $user->id && !in_array($user->role, ['admin', 'owner'])) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk melihat pesanan ini.',
                ], 403);
            }
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pesanan ini.');
        }

        // Eager load relasi lengkap
        $order->load([
            'orderItems.product',
            'statusHistories' => fn($q) => $q->orderBy('created_at', 'asc'),
            'payment',
            'courier',
        ]);

        $this->formatOrderAttributes($order);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil detail pesanan',
                'data' => $order,
            ], 200);
        }

        return view('Order Tracking & Live Status', compact('order'));
    }

    /**
     * Tampilkan pelacakan pesanan aktif terkini (shortcut tracking).
     * Endpoint: GET /order-tracking atau GET /tracking
     */
    public function tracking(Request $request, ?string $kode = null): View|JsonResponse
    {
        if ($kode) {
            return $this->show($request, $kode);
        }

        $user = Auth::user();

        // Ambil pesanan aktif terbaru milik pembeli
        $latestOrder = $user->orders()
            ->whereIn('status', ['menunggu_pembayaran', 'diproses', 'dikirim'])
            ->latest()
            ->first();

        // Jika tidak ada pesanan aktif, ambil pesanan terakhir apapun statusnya
        if (!$latestOrder) {
            $latestOrder = $user->orders()->latest()->first();
        }

        if (!$latestOrder) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada pesanan yang dibuat.',
                    'data' => null,
                ], 200);
            }

            // Tampilkan halaman tracking dengan state belum ada pesanan
            return view('Order Tracking & Live Status', ['order' => null]);
        }

        return $this->show($request, $latestOrder->kode);
    }

    /**
     * Helper pemformatan atribut tampilan harga dan badge status pesanan.
     */
    private function formatOrderAttributes(Order $order): void
    {
        $order->subtotal_formatted = 'Rp ' . number_format($order->subtotal, 0, ',', '.');
        $order->ongkir_formatted = $order->ongkir > 0
            ? 'Rp ' . number_format($order->ongkir, 0, ',', '.')
            : 'Gratis';
        $order->total_formatted = 'Rp ' . number_format($order->total, 0, ',', '.');
        $order->tanggal_formatted = $order->created_at
            ? $order->created_at->isoFormat('D MMMM Y • HH:mm') . ' WIB'
            : '-';

        // Hitung total botol
        $order->total_botol = $order->orderItems ? $order->orderItems->sum('jumlah') : 0;

        // Label dan style badge status
        $statusConfig = [
            'menunggu_pembayaran' => [
                'label' => 'Menunggu Pembayaran',
                'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
                'dot' => 'bg-amber-500',
                'icon' => 'schedule',
                'step' => 1,
            ],
            'diproses' => [
                'label' => 'Sedang Diproses',
                'badge' => 'bg-blue-50 text-blue-900 border-blue-200',
                'dot' => 'bg-blue-600',
                'icon' => 'local_cafe',
                'step' => 2,
            ],
            'dikirim' => [
                'label' => 'Sedang Dikirim',
                'badge' => 'bg-purple-50 text-purple-900 border-purple-200',
                'dot' => 'bg-purple-600',
                'icon' => 'moped',
                'step' => 3,
            ],
            'selesai' => [
                'label' => 'Selesai Diterima',
                'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'dot' => 'bg-emerald-600',
                'icon' => 'check_circle',
                'step' => 4,
            ],
            'dibatalkan' => [
                'label' => 'Pesanan Dibatalkan',
                'badge' => 'bg-rose-50 text-rose-800 border-rose-200',
                'dot' => 'bg-rose-600',
                'icon' => 'cancel',
                'step' => 0,
            ],
        ];

        $cfg = $statusConfig[$order->status] ?? [
            'label' => ucfirst(str_replace('_', ' ', $order->status)),
            'badge' => 'bg-slate-100 text-slate-800 border-slate-200',
            'dot' => 'bg-slate-500',
            'icon' => 'info',
            'step' => 1,
        ];

        $order->status_label = $cfg['label'];
        $order->status_badge = $cfg['badge'];
        $order->status_dot = $cfg['dot'];
        $order->status_icon = $cfg['icon'];
        $order->status_step = $cfg['step'];
    }
}
