<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesHistorySeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::orderBy('id')->take(4)->get();

        // Periode 2 bulan terakhir sampai hari ini
        $startDate = Carbon::today()->subMonths(2);
        $endDate = Carbon::today();

        // Pola penjualan
        $salesPatterns = [
            [5, 6, 7, 8, 6, 9, 10, 8, 11, 12, 10, 13, 14, 12],
            [18, 17, 16, 15, 17, 14, 13, 12, 14, 11, 10, 9, 11, 8],
            [10, 11, 9, 10, 12, 10, 9, 11, 10, 12, 10, 9, 11, 10],
            [4, 15, 7, 18, 5, 13, 8, 20, 6, 16, 9, 19, 5, 14],
        ];

        $date = $startDate->copy();
        $day = 0;

        while ($date->lte($endDate)) {
            // Acak sumber transaksi untuk hari ini (50% Online, 50% POS)
            $sumberTransaksi = rand(0, 1) ? 'online' : 'pos';

            // 1. Buat Nota Utama (Orders)
            $order = Order::create([
                'kode' => 'KTT-' . $date->format('Ymd') . '-' . str_pad($day, 4, '0', STR_PAD_LEFT),
                'sumber_transaksi' => $sumberTransaksi,
                'metode_pemenuhan' => $sumberTransaksi === 'online' ? 'pengiriman' : 'ambil_sendiri',
                'status' => 'selesai',
                'subtotal' => 0,
                'ongkir' => $sumberTransaksi === 'online' ? 10000 : 0,
                'total' => 0,
                'nama_pelanggan' => 'Pelanggan Simulasi',
                'created_at' => $date->copy()->setHour(10),
                'updated_at' => $date->copy()->setHour(10),
            ]);

            $subtotalOrder = 0;

            foreach ($products as $index => $product) {
                $pattern = $salesPatterns[$index];
                $jumlahTerjual = $pattern[$day % count($pattern)];

                if ($jumlahTerjual > 0) {
                    $hargaItem = $product->harga ?? 15000;
                    $subtotalItem = $jumlahTerjual * $hargaItem;
                    
                    // 2. Masukkan rincian botol ke Order Items (Ditambah nama_produk)
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'nama_produk' => $product->nama, // <--- Perbaikan di sini
                        'jumlah' => $jumlahTerjual,
                        'harga_satuan' => $hargaItem,
                        'subtotal' => $subtotalItem,
                        'created_at' => $date->copy()->setHour(10),
                        'updated_at' => $date->copy()->setHour(10),
                    ]);

                    $subtotalOrder += $subtotalItem;

                    // 3. Catat di tabel agregasi Sales History
                    SalesHistory::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'tanggal' => $date->toDateString(),
                            'sumber' => 'transaksi', 
                        ],
                        [
                            'jumlah_terjual' => $jumlahTerjual,
                        ]
                    );
                }
            }

            // 4. Update nominal harga total di Nota Order (pertahankan created_at & updated_at historis)
            $totalOrder = $subtotalOrder + $order->ongkir;
            $order->timestamps = false;
            $order->update([
                'subtotal' => $subtotalOrder,
                'total' => $totalOrder,
                'created_at' => $date->copy()->setHour(10),
                'updated_at' => $date->copy()->setHour(10),
            ]);
            $order->timestamps = true;

            // 5. Buat catatan pembayaran berhasil (Lunas) agar sinkron dengan status pesanan 'selesai'
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'referensi_provider' => 'SIMULASI-PAY-' . $order->id,
                    'metode_pembayaran' => $sumberTransaksi === 'pos' ? 'tunai' : 'qris',
                    'status' => 'berhasil',
                    'jumlah' => $totalOrder,
                    'waktu_verifikasi' => $date->copy()->setHour(10),
                    'created_at' => $date->copy()->setHour(10),
                    'updated_at' => $date->copy()->setHour(10),
                ]
            );

            // 6. Buat jejak audit status selesai
            OrderStatusHistory::firstOrCreate(
                ['order_id' => $order->id, 'status_baru' => 'selesai'],
                [
                    'diubah_oleh' => null,
                    'waktu_perubahan' => $date->copy()->setHour(10),
                    'keterangan' => 'Transaksi selesai dan lunas.',
                    'created_at' => $date->copy()->setHour(10),
                    'updated_at' => $date->copy()->setHour(10),
                ]
            );

            $date->addDay();
            $day++;
        }
    }
}