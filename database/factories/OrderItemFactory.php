<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $jumlah = fake()->numberBetween(1, 5);
        $harga = fake()->randomElement([
            8000,
            10000,
            12000,
            15000,
        ]);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'nama_produk' => fake()->words(2, true),
            'rasa' => fake()->randomElement([
                'Original',
                'Coklat',
                'Caramel',
                'Vanilla',
                'Matcha',
            ]),
            'jumlah' => $jumlah,
            'harga_satuan' => $harga,
            'subtotal' => $jumlah * $harga,
        ];
    }
}