<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_code_is_generated_correctly(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 5));

        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        $this->assertSame('KTT-20261105-0001', $order1->kode);
        $this->assertSame('KTT-20261105-0002', $order2->kode);

        $this->travelBack();
    }

    public function test_order_has_customer_relationship(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->user);
        $this->assertEquals(
            $order->user_id,
            $order->user->id
        );
    }

    public function test_order_has_courier_relationship(): void
    {
        $order = Order::factory()->create();

        $this->assertNull($order->courier);

        $courier = \App\Models\User::factory()->kurir()->create();

        $order->update([
            'courier_id' => $courier->id,
        ]);

        ;$this->assertEquals(
            $courier->id,
            $order->fresh('courier')->courier->id
        );
    }

    public function test_order_has_order_items_relationship(): void
    {
        $order = Order::factory()->create();

        $order->orderItems()->create(
            \App\Models\OrderItem::factory()->make()->toArray()
        );

        $this->assertCount(1, $order->orderItems);
    }

    public function test_order_has_payment_relationship(): void
    {
        $order = Order::factory()->create();

        Payment::factory()->for($order)->create();

        $this->assertNotNull($order->payment);
        $this->assertEquals(
            $order->id,
            $order->payment->order_id
        );
    }

    public function test_order_expiration_uses_payment_expiration(): void
    {
        $order = Order::factory()->create();

        $expiration = now()->addMinutes(10);

        Payment::factory()->for($order)->create([
            'kedaluwarsa_pada' => $expiration,
        ]);

        $this->assertEquals(
            $expiration->timestamp,
            $order->fresh()->expires_at->timestamp
        );
    }

    public function test_order_expiration_uses_fallback_without_payment(): void
    {
        $order = Order::factory()->create();

        $createdAt = now()->subMinutes(2);

        $order->created_at = $createdAt;
        $order->save();

        $this->assertEquals(
            $createdAt->copy()->addMinutes(10)->timestamp,
            $order->fresh()->expires_at->timestamp
        );
    }

    public function test_customer_can_cancel_waiting_payment_order(): void
    {
        $this->travelTo(now());

        $order = Order::factory()->create([
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::factory()->for($order)->create([
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(10),
        ]);

        $this->assertTrue(
            $order->canBeCancelledByCustomer()
        );

        $this->travelBack();
    }

    public function test_customer_cannot_cancel_paid_order(): void
    {
        $order = Order::factory()->create([
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::factory()->for($order)->create([
            'status' => 'berhasil',
            'kedaluwarsa_pada' => now()->addMinutes(10),
        ]);

        $this->assertFalse(
            $order->canBeCancelledByCustomer()
        );
    }

    public function test_customer_cannot_cancel_order_after_expiration(): void
    {
        $order = Order::factory()->create([
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::factory()->for($order)->create([
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->subMinute(),
        ]);

        $this->assertFalse(
            $order->canBeCancelledByCustomer()
        );
    }
}
