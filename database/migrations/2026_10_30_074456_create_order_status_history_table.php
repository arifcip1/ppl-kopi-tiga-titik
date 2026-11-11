
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();

            // Pesanan yang mengalami perubahan status
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Status terbaru setelah perubahan
            $table->enum('status_baru', [
                'menunggu_pembayaran',
                'diproses',
                'dikirim',
                'selesai',
                'dibatalkan'
            ]);

            // Pengguna yang mengubah status
            // NULL jika perubahan dilakukan otomatis oleh sistem
            $table->foreignId('diubah_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Waktu perubahan status
            $table->timestamp('waktu_perubahan')->useCurrent();

            // Keterangan tambahan jika diperlukan
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'waktu_perubahan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
