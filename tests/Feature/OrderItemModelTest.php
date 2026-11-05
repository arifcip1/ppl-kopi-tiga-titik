<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_item_has_order_relationship(): void
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertNotNull($orderItem->order);

        $this->assertEquals(
            $orderItem->order_id,
            $orderItem->order->id
        );
    }

    public function test_order_item_has_product_relationship(): void
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertNotNull($orderItem->product);

        $this->assertEquals(
            $orderItem->product_id,
            $orderItem->product->id
        );
    }

    public function test_order_item_numeric_attributes_are_cast_to_integer(): void
    {
        $orderItem = OrderItem::factory()->create([
            'jumlah' => 3,
            'harga_satuan' => 10000,
            'subtotal' => 30000,
        ]);

        $this->assertIsInt($orderItem->jumlah);
        $this->assertIsInt($orderItem->harga_satuan);
        $this->assertIsInt($orderItem->subtotal);
    }

    public function test_order_item_stores_product_snapshot(): void
    {
        $orderItem = OrderItem::factory()->create([
            'nama_produk' => 'Titik Manja',
            'rasa' => 'Caramel',
            'jumlah' => 2,
            'harga_satuan' => 10000,
            'subtotal' => 20000,
        ]);

        $this->assertEquals('Titik Manja', $orderItem->nama_produk);
        $this->assertEquals('Caramel', $orderItem->rasa);
        $this->assertEquals(2, $orderItem->jumlah);
        $this->assertEquals(10000, $orderItem->harga_satuan);
        $this->assertEquals(20000, $orderItem->subtotal);
    }
}
