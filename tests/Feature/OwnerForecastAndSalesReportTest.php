<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OwnerForecastAndSalesReportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function sale(Product $p, $date, int $qty, string $sumber = 'transaksi'): void
    {
        SalesHistory::create([
            'product_id' => $p->id,
            'tanggal' => $date,
            'jumlah_terjual' => $qty,
            'sumber' => $sumber,
        ]);
    }

    public function test_forecast_insufficient_without_real_transactions(): void
    {
        $p = Product::factory()->create();
        $this->sale($p, today()->subDays(25), 10, 'simulasi');

        $this->actingAs($this->admin())->get('/owner/prediksi')
            ->assertOk()
            ->assertViewHas('isDataSufficient', false)
            ->assertViewHas('predictions', []);
    }

    public function test_forecast_insufficient_when_history_younger_than_21_days(): void
    {
        $p = Product::factory()->create();
        $this->sale($p, today()->subDays(10), 5);

        $this->actingAs($this->admin())->get('/owner/prediksi')
            ->assertViewHas('isDataSufficient', false);
    }

    public function test_forecast_calculates_moving_average_with_missing_days_as_zero(): void
    {
        $p = Product::factory()->create(['stok' => 1]);
        $idle = Product::factory()->create(['stok' => 3]);

        $this->sale($p, today()->subDays(21), 10);   // minggu 1
        $this->sale($p, today()->subDays(15), 5);    // minggu 1
        $this->sale($p, today()->subDays(14), 20);   // minggu 2
        $this->sale($p, today()->subDays(8), 10);    // minggu 2
        $this->sale($p, today()->subDays(7), 7);     // minggu 3
        $this->sale($p, today()->subDays(1), 4);     // minggu 3
        $this->sale($p, today()->subDays(3), 99, 'simulasi'); // diabaikan

        $response = $this->actingAs($this->admin())->get('/owner/prediksi')->assertOk();
        $response->assertViewHas('isDataSufficient', true);

        $rows = collect($response->viewData('predictions'))->keyBy('nama');
        $a = $rows[$p->nama];
        $this->assertSame(15, $a['minggu_1']);
        $this->assertSame(30, $a['minggu_2']);
        $this->assertSame(11, $a['minggu_3']);
        $this->assertSame(2.7, $a['estimasi']);       // (15+30+11)/21 = 2.67
        $this->assertSame(2, $a['saran_produksi']);   // ceil(2.7)=3 - 1

        $b = collect($response->viewData('predictions'))->firstWhere('nama', $idle->nama);
        $this->assertSame(0, $b['minggu_1']);
        $this->assertSame(0.0, $b['estimasi']);
        $this->assertSame(0, $b['saran_produksi']);
    }

    public function test_sales_history_uses_real_transactions_only_without_duplication(): void
    {
        $p = Product::factory()->create(['harga' => 10000]);
        $this->sale($p, today(), 3);
        $this->sale($p, today(), 50, 'simulasi');

        // Dua order pada hari yang sama dengan item produk sama: tidak boleh menggandakan botol/omzet
        foreach (['online', 'pos'] as $kanal) {
            $order = Order::factory()->create([
                'sumber_transaksi' => $kanal,
                'status' => 'selesai',
            ]);
            DB::table('order_items')->insert([
                'order_id' => $order->id, 'product_id' => $p->id, 'nama_produk' => $p->nama,
                'rasa' => $p->rasa, 'jumlah' => 1, 'harga_satuan' => 10000, 'subtotal' => 10000,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->admin())->get('/owner/riwayat-penjualan?periode=harian')->assertOk();

        $summary = $response->viewData('summary');
        $this->assertSame(3, $summary['total_botol']);
        $this->assertSame(30000, $summary['total_omzet']);
        $sales = $response->viewData('sales');
        $this->assertCount(1, $sales);
        $this->assertSame('Online & POS', $sales[0]['sumber']);
        $response->assertSee('Online &amp; POS', false);
    }

    public function test_sales_history_default_source_label(): void
    {
        $p = Product::factory()->create(['harga' => 8000]);
        $this->sale($p, today(), 2);

        $this->actingAs($this->admin())->get('/owner/riwayat-penjualan?periode=harian')
            ->assertSee('Transaksi Riil (Online/POS)')
            ->assertSee(route('owner.export.sales', ['periode' => 'harian']), false);
    }

    public function test_export_requires_admin_role(): void
    {
        $this->get('/owner/riwayat-penjualan/export')->assertRedirect();

        $this->actingAs(User::factory()->pelanggan()->create())
            ->get('/owner/riwayat-penjualan/export')
            ->assertForbidden();
    }

    public function test_export_downloads_xlsx(): void
    {
        if (!extension_loaded('zip')) {
            $this->markTestSkipped('Ekstensi PHP zip belum aktif; pembuatan .xlsx membutuhkannya.');
        }

        $p = Product::factory()->create();
        $this->sale($p, today(), 2);

        $this->actingAs($this->admin())->get('/owner/riwayat-penjualan/export?periode=harian')
            ->assertOk()
            ->assertDownload('riwayat-penjualan-harian-'.today()->format('Ymd').'.xlsx');
    }
}
