<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

/**
 * Service Notifikasi Web Dinamis (F14 / FR-14 / BR-19).
 * Beroperasi secara transient/in-memory tanpa menambah tabel ke-11 ke database.
 */
class WebNotificationService
{
    /**
     * Mengambil daftar notifikasi aktif untuk pengguna berdasarkan perannya.
     */
    public function getNotificationsForUser(User $user): array
    {
        return match ($user->role) {
            'admin', 'owner' => $this->getOwnerNotifications(),
            'kurir'          => $this->getCourierNotifications($user),
            'pelanggan'      => $this->getCustomerNotifications($user),
            default          => [],
        };
    }

    /**
     * Notifikasi untuk Owner: Pesanan butuh kurir & stok menipis (<= 5).
     */
    protected function getOwnerNotifications(): array
    {
        $notifications = [];

        // 1. Pesanan Online siap antar yang belum ditugaskan kurir
        $needCourierOrders = Order::where('status', 'diproses')
            ->where('metode_pemenuhan', 'pengiriman')
            ->whereNull('courier_id')
            ->latest()
            ->take(5)
            ->get();

        if ($needCourierOrders->isNotEmpty()) {
            $count = $needCourierOrders->count();
            $notifications[] = [
                'id'       => 'owner_need_courier',
                'tipe'     => 'kurir',
                'judul'    => "{$count} Pesanan Butuh Penugasan Kurir",
                'pesan'    => "Ada {$count} pesanan berstatus diproses yang siap ditugaskan kepada kurir.",
                'waktu'    => $needCourierOrders->first()->created_at?->diffForHumans() ?? 'Baru saja',
                'link'     => route('owner.dashboard', ['status' => 'diproses']),
            ];
        }

        // 2. Pesanan Ambil Sendiri (Pickup) yang sudah lunas dan siap diracik/diserahkan di lapak
        $pickupOrders = Order::where('status', 'diproses')
            ->where('metode_pemenuhan', 'ambil_sendiri')
            ->latest()
            ->take(5)
            ->get();

        if ($pickupOrders->isNotEmpty()) {
            $count = $pickupOrders->count();
            $notifications[] = [
                'id'       => 'owner_pickup_ready',
                'tipe'     => 'pickup',
                'judul'    => "{$count} Pesanan Ambil Sendiri Siap Diserahkan",
                'pesan'    => "Ada {$count} pesanan ambil sendiri berstatus diproses menunggu penyerahan di lapak.",
                'waktu'    => $pickupOrders->first()->created_at?->diffForHumans() ?? 'Baru saja',
                'link'     => route('owner.dashboard', ['status' => 'diproses']),
            ];
        }

        // 3. Peringatan Stok Menipis (BR-10 & BR-19) — ambang batas dari tabel settings
        $batasStok = (int) (\App\Models\Setting::where('key', 'batas_stok_minimum')->value('value') ?? 5);
        $lowStockProducts = Product::where('aktif', true)
            ->where('stok', '<=', $batasStok)
            ->get();

        if ($lowStockProducts->isNotEmpty()) {
            $count = $lowStockProducts->count();
            $names = $lowStockProducts->pluck('nama')->take(3)->implode(', ');
            $notifications[] = [
                'id'       => 'owner_low_stock',
                'tipe'     => 'stok',
                'judul'    => "{$count} Menu Menipis (Stok ≤ {$batasStok})",
                'pesan'    => "Varian: {$names}. Segera isi ulang stok cooler box lapak.",
                'waktu'    => 'Hari ini',
                'link'     => url('/owner/produk'),
            ];
        }

        return $notifications;
    }

    /**
     * Notifikasi untuk Kurir: Tugas pengantaran baru yang ditugaskan kepadanya.
     */
    protected function getCourierNotifications(User $courier): array
    {
        $notifications = [];

        $activeTasks = Order::where('courier_id', $courier->id)
            ->where('status', 'dikirim')
            ->latest()
            ->take(5)
            ->get();

        foreach ($activeTasks as $task) {
            $notifications[] = [
                'id'       => "courier_task_{$task->id}",
                'tipe'     => 'tugas_baru',
                'judul'    => "Tugas Baru: Antar ke {$task->nama_penerima}",
                'pesan'    => "Pesanan #{$task->kode} - {$task->alamat}",
                'waktu'    => $task->updated_at?->diffForHumans() ?? 'Baru saja',
                'link'     => route('kurir.tugas-baru'),
            ];
        }

        return $notifications;
    }

    /**
     * Notifikasi untuk Pelanggan: Pembaruan status pesanan miliknya.
     */
    protected function getCustomerNotifications(User $customer): array
    {
        $notifications = [];

        $recentOrders = Order::where('user_id', $customer->id)
            ->whereIn('status', ['diproses', 'dikirim', 'selesai'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($recentOrders as $order) {
            $diprosesMessage = $order->metode_pemenuhan === 'ambil_sendiri'
                ? 'Pembayaran terverifikasi, pesanan sedang disiapkan dan siap diambil di lapak (batas 24 jam).'
                : 'Pembayaran terverifikasi, pesanan sedang disiapkan.';

            $statusLabels = [
                'diproses' => $diprosesMessage,
                'dikirim'  => 'Pesananmu sedang dalam perjalanan diantar oleh kurir!',
                'selesai'  => 'Pesanan telah selesai diterima. Terima kasih sudah menikmati Kopi Tiga Titik!',
            ];

            $notifications[] = [
                'id'       => "customer_order_{$order->id}",
                'tipe'     => 'status_pesanan',
                'judul'    => "Status Pesanan #{$order->kode}: " . ucfirst($order->status),
                'pesan'    => $statusLabels[$order->status] ?? "Status pesanan: {$order->status}",
                'waktu'    => $order->updated_at?->diffForHumans() ?? 'Baru saja',
                'link'     => url('/riwayat-pesanan'),
            ];
        }

        return $notifications;
    }
}
