<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ShippingLocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    /**
     * Menampilkan antarmuka Dashboard Owner & Manajemen Antrean Pesanan (FR-8.1, FR-8.8).
     * Endpoint: GET /owner/dashboard
     */
    public function index(Request $request): View
    {
        // 1. Ringkasan Metrik Operasional Hari Ini
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', ['diproses', 'dikirim', 'selesai'])
            ->sum('subtotal'); // Omzet bersih tanpa ongkir sesuai FR-15 / BR-16

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

        $activeLocation = ShippingLocation::where('aktif', true)->first();

        // 2. Query Daftar Pesanan dengan Filter Status & Pencarian (FR-8.1)
        $ordersQuery = Order::with(['orderItems.product', 'payment', 'courier', 'user', 'statusHistories'])
            ->latest();

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

        $orders = $ordersQuery->paginate(8)->withQueryString();

        // Format atribut pembantu untuk setiap pesanan
        $orders->getCollection()->transform(function ($order) {
            $this->formatOrderDisplay($order);
            return $order;
        });

        // 3. Data Pendukung Antarmuka
        $couriers = User::where('role', 'kurir')->orderBy('name')->get();
        $products = Product::where('aktif', true)->orderBy('id')->get();

        // Hitung jumlah pesanan per status untuk badge filter tab
        $statusCounts = [
            'semua' => Order::count(),
            'menunggu_pembayaran' => Order::where('status', 'menunggu_pembayaran')->count(),
            'diproses' => Order::where('status', 'diproses')->count(),
            'dikirim' => Order::where('status', 'dikirim')->count(),
            'selesai' => Order::where('status', 'selesai')->count(),
            'dibatalkan' => Order::where('status', 'dibatalkan')->count(),
        ];

        // Buat notifikasi web dinamis (F14) tanpa tabel baru
        $notifications = [];
        if ($needCourierCount > 0) {
            $notifications[] = [
                'tipe'  => 'kurir',
                'judul' => "{$needCourierCount} Pesanan Butuh Kurir",
                'pesan' => 'Ada pesanan siap antar yang belum ditugaskan ke kurir.',
                'waktu' => 'Segera tindak lanjuti',
            ];
        }
        if ($lowStockCount > 0) {
            $notifications[] = [
                'tipe'  => 'stok',
                'judul' => "{$lowStockCount} Menu Menipis (Stok ≤ 5)",
                'pesan' => 'Varian: ' . $lowStockProducts->pluck('nama')->implode(', '),
                'waktu' => 'Perlu restok',
            ];
        }

        return view('Owner Dashboard & Mini POS Page', compact(
            'todayRevenue',
            'pendingOrdersCount',
            'needCourierCount',
            'activeStock',
            'lowStockProducts',
            'lowStockCount',
            'activeLocation',
            'orders',
            'couriers',
            'products',
            'currentStatus',
            'statusCounts',
            'notifications'
        ));
    }

    /**
     * Mengambil detail pesanan dalam format JSON untuk modal interaktif (FR-8.2).
     * Endpoint: GET /owner/orders/{order}/detail
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['orderItems.product', 'payment', 'courier', 'user', 'statusHistories.user']);
        $this->formatOrderDisplay($order);

        return response()->json([
            'success' => true,
            'data'    => $order,
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

        $courier = User::where('id', $request->courier_id)
            ->where('role', 'kurir')
            ->first();

        if (!$courier) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pengguna yang dipilih bukan kurir aktif.'], 422);
            }
            return back()->with('error', 'Pengguna yang dipilih bukan kurir aktif.');
        }

        // Validasi transisi status: Hanya pesanan 'diproses' (atau 'dikirim' untuk penugasan ulang) yang boleh ditugaskan
        if (!in_array($order->status, ['diproses', 'dikirim'])) {
            $msg = 'Pesanan dengan status "' . $order->status . '" tidak dapat ditugaskan ke kurir.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $order->courier_id = $courier->id;
        $order->status = 'dikirim';
        $order->save();

        // Catat pada rekam jejak status (order_status_history - FR-8.5)
        OrderStatusHistory::create([
            'order_id'        => $order->id,
            'status_baru'     => 'dikirim',
            'diubah_oleh'     => Auth::id(),
            'waktu_perubahan' => now(),
            'keterangan'      => 'Pesanan dialihkan ke status Dikirim dan ditugaskan kepada Kurir: ' . $courier->name,
        ]);

        $successMsg = 'Pesanan ' . $order->kode . ' berhasil ditugaskan kepada ' . $courier->name . ' (Status: Dikirim).';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'data'    => $order,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Menandai selesai pesanan ambil sendiri yang telah diserahkan di lapak (Bab 8 & FR-8.3).
     * Endpoint: POST /owner/orders/{order}/complete-pickup
     */
    public function completePickup(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        if ($order->metode_pemenuhan !== 'ambil_sendiri' && $order->sumber_transaksi !== 'pos') {
            $msg = 'Aksi ini hanya berlaku untuk pesanan ambil sendiri di lapak atau transaksi POS.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if (!in_array($order->status, ['diproses', 'menunggu_pembayaran'])) {
            $msg = 'Pesanan dengan status "' . $order->status . '" tidak dapat diselesaikan secara manual.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $order->status = 'selesai';
        $order->save();

        OrderStatusHistory::create([
            'order_id'        => $order->id,
            'status_baru'     => 'selesai',
            'diubah_oleh'     => Auth::id(),
            'waktu_perubahan' => now(),
            'keterangan'      => 'Pesanan ambil sendiri telah diserahkan kepada pembeli di lapak dan ditandai selesai oleh Owner.',
        ]);

        $successMsg = 'Pesanan ' . $order->kode . ' berhasil diselesaikan!';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'data'    => $order,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Format atribut visual pesanan untuk Blade & modal.
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

