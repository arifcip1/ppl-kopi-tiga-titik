<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Service untuk Pengelolaan Akun dan Penugasan Kurir (US-31, US-32, FR-8.3, FR-8.9).
 */
class CourierManagementService
{
    public const MAX_ACTIVE_COURIERS = 2;

    public function __construct(
        protected OrderStatusService $statusService
    ) {}

    /**
     * Mendapatkan daftar semua akun kurir beserta metrik tugasnya.
     */
    public function getCouriers(): Collection
    {
        return User::where('role', 'kurir')
            ->withCount([
                'assignedOrders as active_tasks_count' => function ($q) {
                    $q->where('status', 'dikirim');
                },
                'assignedOrders as completed_tasks_count' => function ($q) {
                    $q->where('status', 'selesai');
                },
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Membuat akun kurir baru dengan penegakan batas maksimal 2 akun (US-31).
     *
     * @throws ValidationException
     */
    public function createCourier(array $data): User
    {
        $currentCount = User::where('role', 'kurir')->count();

        if ($currentCount >= self::MAX_ACTIVE_COURIERS) {
            throw ValidationException::withMessages([
                'courier' => 'Maksimal hanya 2 akun kurir yang dapat aktif/terdaftar secara bersamaan (Sesuai Aturan Bisnis US-31).',
            ]);
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'kurir',
            'telepon' => $data['telepon'] ?? null,
        ]);
    }

    /**
     * Menghapus akun kurir (hanya jika tidak memiliki antaran yang sedang dikirim).
     *
     * @throws ValidationException
     */
    public function deleteCourier(User $courier): bool
    {
        if ($courier->role !== 'kurir') {
            throw ValidationException::withMessages([
                'courier' => 'Pengguna yang dipilih bukan ber-role kurir.',
            ]);
        }

        $activeDeliveries = Order::where('courier_id', $courier->id)
            ->where('status', 'dikirim')
            ->count();

        if ($activeDeliveries > 0) {
            throw ValidationException::withMessages([
                'courier' => "Kurir {$courier->name} sedang menjalankan {$activeDeliveries} pengantaran aktif dan tidak dapat dihapus saat ini.",
            ]);
        }

        return $courier->delete();
    }

    /**
     * Menugaskan kurir pada pesanan 'diproses' dan mengupdate status ke 'dikirim'.
     *
     * @throws ValidationException
     */
    public function assignCourier(Order $order, int $courierId, ?User $actor = null): Order
    {
        // 1. Validasi pesanan harus berstatus 'diproses'
        if ($order->status !== 'diproses') {
            throw ValidationException::withMessages([
                'order' => 'Hanya pesanan berstatus diproses yang dapat ditugaskan ke kurir.',
            ]);
        }

        // 2. Validasi metode pemenuhan harus 'pengiriman'
        if ($order->metode_pemenuhan !== 'pengiriman') {
            throw ValidationException::withMessages([
                'order' => 'Pesanan dengan metode ambil sendiri di lapak tidak memerlukan kurir.',
            ]);
        }

        // 3. Validasi kurir
        $courier = User::where('id', $courierId)->where('role', 'kurir')->first();
        if (!$courier) {
            throw ValidationException::withMessages([
                'courier_id' => 'Kurir yang dipilih tidak valid atau bukan akun kurir resmi.',
            ]);
        }

        // 4. Lakukan transisi status lewat OrderStatusService
        return $this->statusService->transition(
            $order,
            'dikirim',
            $actor,
            "Pesanan ditugaskan kepada kurir {$courier->name}.",
            ['courier_id' => $courier->id]
        );
    }
}

