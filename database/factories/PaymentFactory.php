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
            'status' => 'pending',
            'jumlah' => 30000,
            'kedaluwarsa_pada' => now()->addMinutes(10),
            'waktu_verifikasi' => null,
            'payload_mentah' => null,
        ];
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