<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => fake()->words(2, true),
            'rasa' => fake()->randomElement([
                'Original',
                'Coklat',
                'Caramel',
                'Vanilla',
                'Matcha',
            ]),
            'komposisi' => fake()->sentence(),
            'deskripsi' => fake()->optional()->sentence(),
            'keawetan' => '3 hari',
            'harga' => fake()->randomElement([
                8000,
                10000,
                12000,
                15000,
            ]),
            'stok' => fake()->numberBetween(0, 100),
            'foto_path' => null,
            'aktif' => true,
        ];
    }
}