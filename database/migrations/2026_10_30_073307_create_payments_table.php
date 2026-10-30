
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

            $table->foreignId('order_id')
                ->unique()
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('referensi_provider')->nullable();

            $table->enum('status', [
                'pending',
                'berhasil',
                'gagal',
                'kedaluwarsa'
            ])->default('pending');

            $table->unsignedBigInteger('jumlah');

            $table->timestamp('kedaluwarsa_pada')->nullable();
            $table->timestamp('waktu_verifikasi')->nullable();

            $table->json('payload_mentah')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
