
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->date('tanggal');

            $table->unsignedInteger('jumlah_terjual')->default(0);

            $table->enum('sumber', [
                'transaksi',
                'simulasi'
            ])->default('transaksi');

            $table->timestamps();

            $table->unique([
                'product_id',
                'tanggal',
                'sumber'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_history');
    }
};
