
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('kode')->unique();

            // Sumber dan metode pemenuhan pesanan
            $table->enum('sumber_transaksi', [
                'online',
                'pos'
            ]);

            $table->enum('metode_pemenuhan', [
                'pengiriman',
                'ambil_sendiri'
            ])->nullable();

            // Relasi pelanggan dan kurir
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('courier_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Status pesanan
            $table->enum('status', [
                'menunggu_pembayaran',
                'diproses',
                'dikirim',
                'selesai',
                'dibatalkan'
            ])->default('menunggu_pembayaran');

            // Informasi harga
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('ongkir')->default(0);
            $table->unsignedBigInteger('total');

            // Informasi pelanggan POS
            $table->string('nama_pelanggan', 100)->nullable();

            // Informasi penerima pesanan online
            $table->string('nama_penerima')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();

            // Koordinat tujuan pengiriman
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Snapshot lokasi usaha saat pesanan dibuat
            $table->string('nama_lokasi_asal', 150)->nullable();
            $table->text('alamat_lokasi_asal')->nullable();
            $table->decimal('latitude_lokasi_asal', 10, 7)->nullable();
            $table->decimal('longitude_lokasi_asal', 10, 7)->nullable();

            // Jarak rute pengiriman
            $table->decimal('jarak_pengiriman', 8, 2)->nullable();

            // Batas waktu pengambilan mandiri
            $table->dateTime('batas_pengambilan')->nullable();

            // Catatan dan pengembalian stok
            $table->text('catatan')->nullable();
            $table->boolean('stok_dikembalikan')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
