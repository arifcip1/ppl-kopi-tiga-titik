
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Satu pesanan dapat memiliki beberapa percobaan pembayaran
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Referensi transaksi dari Midtrans
            $table->string('referensi_provider')->nullable()->unique();

            // Metode pembayaran
            $table->enum('metode_pembayaran', [
                'midtrans',
                'tunai',
                'qris'
            ]);

            // Status pembayaran
            $table->enum('status', [
                'pending',
                'berhasil',
                'gagal',
                'kedaluwarsa'
            ])->default('pending');

            $table->unsignedBigInteger('jumlah');

            $table->timestamp('kedaluwarsa_pada')->nullable();
            $table->timestamp('waktu_verifikasi')->nullable();

            // Respons asli dari provider pembayaran
            $table->json('payload_mentah')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
