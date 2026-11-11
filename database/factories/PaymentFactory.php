<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'referensi_provider' => 'TEST-' . fake()->unique()->numerify('########'),
            'metode_pembayaran' => 'midtrans',
            'status' => 'pending',
            'jumlah' => 30000,
            'kedaluwarsa_pada' => now()->addMinutes(10),
            'waktu_verifikasi' => null,
            'payload_mentah' => null,
        ];
    }

    public function midtrans(): static
    {
        return $this->state(fn (array $attributes) => [
            'metode_pembayaran' => 'midtrans',
        ]);
    }

    public function tunai(): static
    {
        return $this->state(fn (array $attributes) => [
            'metode_pembayaran' => 'tunai',
        ]);
    }

    public function qris(): static
    {
        return $this->state(fn (array $attributes) => [
            'metode_pembayaran' => 'qris',
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Payment $payment) {
            $payment->update([
                'jumlah' => $payment->order->total,
            ]);
        });
    }
}