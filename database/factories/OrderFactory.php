<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20000, 100000);
        $ongkir = fake()->numberBetween(0, 10000);

        return [
            'kode' => Order::generateOrderCode(),
            'user_id' => User::factory(),
            'courier_id' => null,
            'status' => 'menunggu_pembayaran',
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total' => $subtotal + $ongkir,
            'nama_penerima' => fake()->name(),
            'telepon' => '08' . fake()->numerify('##########'),
            'alamat' => fake()->address(),
            'lat' => fake()->latitude(-8.5, -7.0),
            'lng' => fake()->longitude(112.0, 113.0),
            'catatan' => fake()->optional()->sentence(),
            'stok_dikembalikan' => false,
        ];
    }
}