<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CourierDashboardController extends Controller
{
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

        return view('Dashboard Kurir Tab Tugas Baru Page', compact('tasks', 'stats', 'courier'));
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

        return view('Dashboard Kurir Tab Selesai Page', compact('completedTasks', 'stats', 'courier'));
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

        $order->status = 'selesai';
        $order->save();

        // 3. Rekam jejak status ke tabel order_status_history (FR-8.5)
        OrderStatusHistory::create([
            'order_id'        => $order->id,
            'status_baru'     => 'selesai',
            'diubah_oleh'     => Auth::id(),
            'waktu_perubahan' => now(),
            'keterangan'      => 'Pesanan telah berhasil diantar dan diterima oleh pelanggan melalui Kurir: ' . Auth::user()->name,
        ]);

        $successMsg = 'Pesanan ' . $order->kode . ' berhasil diselesaikan! Antaran Anda telah tercatat.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'data'    => $order,
            ]);
        }

        return redirect()->route('kurir.tugas-baru')->with('success', $successMsg);
    }

    /**
     * Hitung ringkasan statistik antaran kurir.
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

        $totalBottlesDelivered = OrderItem::whereHas('order', function ($q) use ($courierId) {
            $q->where('courier_id', $courierId)->where('status', 'selesai');
        })->sum('jumlah');

        return [
            'active_tasks'            => $activeCount,
            'completed_today'         => $completedTodayCount,
            'total_bottles_delivered' => $totalBottlesDelivered,
        ];
    }

    /**
     * Format visual atribut pesanan kurir.
     */
    private function formatTaskDisplay(Order $order): void
    {
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

