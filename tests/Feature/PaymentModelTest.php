<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_has_order_relationship(): void
    {
        $payment = Payment::factory()->create();

        $this->assertNotNull($payment->order);

        $this->assertEquals(
            $payment->order_id,
            $payment->order->id
        );
    }

    public function test_payment_status_helpers_work_correctly(): void
    {
        $payment = Payment::factory()->create([
            'status' => 'pending',
        ]);

        $this->assertTrue($payment->isPending());
        $this->assertFalse($payment->isSuccess());
        $this->assertFalse($payment->isExpired());
        $this->assertFalse($payment->isFailed());

        $payment->update(['status' => 'berhasil']);
        $this->assertTrue($payment->isSuccess());

        $payment->update(['status' => 'kedaluwarsa']);
        $this->assertTrue($payment->isExpired());

        $payment->update(['status' => 'gagal']);
        $this->assertTrue($payment->isFailed());
    }

    public function test_payment_amount_is_cast_to_integer(): void
    {
        $payment = Payment::factory()->create([
            'jumlah' => 30000,
        ]);

        $this->assertIsInt($payment->jumlah);
    }

    public function test_payment_datetime_attributes_are_cast_correctly(): void
    {
        $payment = Payment::factory()->create([
            'kedaluwarsa_pada' => now()->addMinutes(10),
            'waktu_verifikasi' => now(),
        ]);

        $this->assertInstanceOf(
            \Illuminate\Support\Carbon::class,
            $payment->kedaluwarsa_pada
        );

        $this->assertInstanceOf(
            \Illuminate\Support\Carbon::class,
            $payment->waktu_verifikasi
        );
    }

    public function test_payment_payload_is_cast_to_array(): void
    {
        $payload = [
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];

        $payment = Payment::factory()->create([
            'payload_mentah' => $payload,
        ]);

        $this->assertIsArray($payment->payload_mentah);

        $this->assertEquals(
            $payload,
            $payment->payload_mentah
        );
    }

    public function test_one_order_can_only_have_one_payment(): void
    {
        $order = Order::factory()->create();

        Payment::factory()->for($order)->create();

        $this->expectException(
            \Illuminate\Database\QueryException::class
        );

        Payment::factory()->for($order)->create();
    }
}
