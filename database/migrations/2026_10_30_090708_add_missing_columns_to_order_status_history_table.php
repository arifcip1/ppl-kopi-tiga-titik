
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->after('id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('status_sebelumnya')
                ->nullable()
                ->after('order_id');

            $table->string('status_baru')
                ->after('status_sebelumnya');

            $table->foreignId('user_id')
                ->nullable()
                ->after('status_baru')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('keterangan')
                ->nullable()
                ->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn('status_sebelumnya');
            $table->dropColumn('status_baru');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('keterangan');
        });
    }
};
