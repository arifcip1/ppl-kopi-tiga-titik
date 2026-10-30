
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->string('rasa');
            $table->text('komposisi');
            $table->text('deskripsi')->nullable();
            $table->string('keawetan')->nullable();

            $table->unsignedBigInteger('harga');
            $table->unsignedInteger('stok')->default(0);

            $table->string('foto_path')->nullable();
            $table->boolean('aktif')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
