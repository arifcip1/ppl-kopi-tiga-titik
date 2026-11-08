<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
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

