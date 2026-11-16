<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Services\OrderStatusService;
use App\Services\OrderStockService;
use App\Services\WebNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CourierDashboardController extends Controller
{
    public function __construct(
        protected OrderStatusService $statusService,
        protected WebNotificationService $notificationService,
        protected OrderStockService $orderStockService
    ) {}

    /**
     * Menampilkan tab Tugas Baru untuk Kurir yang sedang login (FR-8.7, FR-8.9).
     * Endpoint: GET /kurir/tugas-baru
     */
    public function tugasBaru(Request $request): View
    {
        $courier = Auth::user();

        // Isolasi data: Hanya pesanan yang ditugaskan kepada kurir yang sedang login (FR-8.9)
        $tasks = Order::where('courier_id', $courier->id)
            ->where('status', 'dikirim')
            ->with(['orderItems.product', 'payment', 'user', 'statusHistories'])
            ->latest()
            ->get();

        $tasks->transform(function ($task) {
            $this->formatTaskDisplay($task);
            return $task;
        });

        $stats = $this->getCourierStats($courier->id);
        $notifications = $this->notificationService->getNotificationsForUser($courier);

        return view('Dashboard Kurir Tab Tugas Baru Page', compact('tasks', 'stats', 'courier', 'notifications'));
    }

    /**
     * API Tugas Pengantaran Aktif Kurir yang Login (FR-8.7 & FR-8.9).
     * Endpoint: GET /api/kurir/tasks
     */
    public function apiTasks(Request $request): JsonResponse
    {
        $courier = Auth::user();

        $tasks = Order::where('courier_id', $courier->id)
            ->where('status', 'dikirim')
            ->with(['orderItems.product', 'payment', 'user', 'statusHistories'])
            ->latest()
            ->get();

        $tasks->transform(function ($task) {
            $this->formatTaskDisplay($task);
            return $task;
        });

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'meta'    => [
                'total_tasks' => $tasks->count(),
                'courier'     => [
                    'id'    => $courier->id,
                    'name'  => $courier->name,
                    'email' => $courier->email,
                ],
            ],
        ]);
    }

    /**
     * Menampilkan tab Tugas Selesai untuk Kurir yang sedang login (FR-8.7).
     * Endpoint: GET /kurir/selesai
     */
    public function selesai(Request $request): View
    {
        $courier = Auth::user();

        $completedTasks = Order::where('courier_id', $courier->id)
            ->where('status', 'selesai')
            ->with(['orderItems.product', 'payment', 'user', 'statusHistories'])
            ->latest()
            ->paginate(10);

        $completedTasks->getCollection()->transform(function ($task) {
            $this->formatTaskDisplay($task);
            return $task;
        });

        $stats = $this->getCourierStats($courier->id);
        $notifications = $this->notificationService->getNotificationsForUser($courier);

        return view('Dashboard Kurir Tab Selesai Page', compact('completedTasks', 'stats', 'courier', 'notifications'));
    }

    /**
     * Menampilkan profil kurir (US-31).
     * Endpoint: GET /kurir/profil
     */
    public function showProfile(): View
    {
        $courier = Auth::user();
        $notifications = $this->notificationService->getNotificationsForUser($courier);

        return view('Profil Kurir Page', compact('courier', 'notifications'));
    }

    /**
     * Memperbarui profil dan kontak WhatsApp kurir.
     * Endpoint: PUT /kurir/profil
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'    => ['nullable', 'string', 'max:20'],
            'telepon'  => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $phone = $validated['phone'] ?? $validated['telepon'] ?? $user->telepon;

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->telepon = $phone;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil dan nomor WhatsApp kurir berhasil diperbarui!');
    }

    /**
     * Memperbarui status pesanan menjadi 'selesai' setelah diantar ke pelanggan (FR-8.3 & Bab 8).
     * Endpoint: POST /kurir/orders/{order}/complete
     */
    public function markComplete(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        // 1. Proteksi Otorisasi: Kurir hanya boleh menyelesaikan pesanan miliknya sendiri (FR-8.9)
        if ((int) $order->courier_id !== (int) Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Pesanan ini tidak ditugaskan kepada Anda.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak berhak mengubah pesanan kurir lain.');
        }

        // 2. Validasi Transisi Status: Hanya pesanan berstatus 'dikirim' yang bisa diselesaikan kurir
        if ($order->status !== 'dikirim') {
            $msg = 'Hanya pesanan yang sedang dalam proses pengiriman (status: dikirim) yang dapat ditandai selesai.';
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
                'Pesanan telah berhasil diantar dan diterima oleh pelanggan melalui Kurir: ' . Auth::user()->name
            );

            $this->orderStockService->recordSalesHistory($updatedOrder);

            $successMsg = 'Pesanan ' . $updatedOrder->kode . ' berhasil diselesaikan! Antaran Anda telah tercatat.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'data'    => $updatedOrder,
                ]);
            }

            return redirect()->route('kurir.tugas-baru')->with('success', $successMsg);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mengambil statistik performa kurir hari ini.
     */
    private function getCourierStats(int $courierId): array
    {
        $activeCount = Order::where('courier_id', $courierId)
            ->where('status', 'dikirim')
            ->count();

        $completedTodayCount = Order::where('courier_id', $courierId)
            ->where('status', 'selesai')
            ->whereDate('updated_at', today())
            ->count();

        $completedOrders = Order::where('courier_id', $courierId)
            ->where('status', 'selesai')
            ->with('orderItems')
            ->get();

        $totalBottlesDelivered = $completedOrders->sum(function ($order) {
            return $order->orderItems ? $order->orderItems->sum('jumlah') : 0;
        });

        return [
            'active_tasks'            => $activeCount,
            'completed_today'         => $completedTodayCount,
            'total_completed'         => $completedOrders->count(),
            'total_bottles_delivered' => $totalBottlesDelivered,
        ];
    }

    /**
     * Format visual atribut pesanan kurir.
     */
    private function formatTaskDisplay(Order $order): void
    {
        // Waktu penugasan kurir (dari status history 'dikirim')
        $assignedHistory = $order->statusHistories ? $order->statusHistories->where('status_baru', 'dikirim')->last() : null;
        $assignedTime = $assignedHistory?->created_at ?? $order->updated_at ?? $order->created_at;
        $order->ditugaskan_waktu_formatted = $assignedTime ? $assignedTime->isoFormat('HH:mm') . ' WIB' : '-';

        $order->subtotal_formatted = 'Rp ' . number_format($order->subtotal, 0, ',', '.');
        $order->ongkir_formatted = $order->ongkir > 0
            ? 'Rp ' . number_format($order->ongkir, 0, ',', '.')
            : 'Gratis';
        $order->total_formatted = 'Rp ' . number_format($order->total, 0, ',', '.');
        $order->waktu_formatted = $order->created_at
            ? $order->created_at->isoFormat('HH:mm') . ' WIB'
            : '-';
        $order->tanggal_formatted = $order->created_at
            ? $order->created_at->isoFormat('D MMM Y • HH:mm') . ' WIB'
            : '-';
        $order->selesai_formatted = $order->updated_at
            ? $order->updated_at->isoFormat('D MMM Y • HH:mm') . ' WIB'
            : '-';

        $order->total_botol = $order->orderItems ? $order->orderItems->sum('jumlah') : 0;

        // Bersihkan nomor telepon untuk link WhatsApp
        $rawPhone = preg_replace('/[^0-9]/', '', $order->telepon ?? '');
        if (str_starts_with($rawPhone, '0')) {
            $order->whatsapp_number = '62' . substr($rawPhone, 1);
        } else {
            $order->whatsapp_number = $rawPhone;
        }

        // Tautan navigasi Google Maps
        if ($order->lat && $order->lng) {
            $order->map_url = "https://www.google.com/maps/dir/?api=1&destination={$order->lat},{$order->lng}";
        } else {
            $order->map_url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($order->alamat);
        }
    }
}
