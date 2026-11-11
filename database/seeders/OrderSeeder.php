<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = User::where('email', 'nabil@gmail.com')->first();

        if (!$pelanggan) {
            $pelanggan = User::where('role', 'pelanggan')->first();
        }

        if (!$pelanggan) {
            return;
        }

        $kurir = User::where('role', 'kurir')->first();

        $titikHitam = Product::where('nama', 'Titik Hitam')->first();
        $titikTemu = Product::where('nama', 'Titik Temu')->first();
        $titikManja = Product::where('nama', 'Titik Manja')->first();

        /*
        |--------------------------------------------------------------------------
        | 1. Pesanan Sedang Diproses
        |--------------------------------------------------------------------------
        */

        $orderActive = Order::updateOrCreate(
            ['kode' => 'KTT-20261108-0001'],
            [
                'sumber_transaksi' => 'online',
                'metode_pemenuhan' => 'pengiriman',

                'user_id' => $pelanggan->id,
                'courier_id' => $kurir?->id,

                'status' => 'diproses',

                'subtotal' => 28000,
                'ongkir' => 0,
                'total' => 28000,

                'nama_pelanggan' => null,
                'nama_penerima' => $pelanggan->name,
                'telepon' => '081238499210',
                'alamat' => 'Jl. Hayam Wuruk No. 42, Mergelo, Kec. Magersari, Kota Mojokerto',

                'lat' => -7.4721000,
                'lng' => 112.4340000,

                // Snapshot lokasi asal belum tersedia pada data demo.
                'nama_lokasi_asal' => null,
                'alamat_lokasi_asal' => null,
                'latitude_lokasi_asal' => null,
                'longitude_lokasi_asal' => null,

                // Jarak tidak diisi karena lokasi asal belum tersedia.
                'jarak_pengiriman' => null,

                'batas_pengambilan' => null,

                'catatan' => 'Es batu dikit aja kak',
                'stok_dikembalikan' => false,

                'created_at' => Carbon::parse('2026-11-08 10:00:00'),
                'updated_at' => Carbon::parse('2026-11-08 10:05:00'),
            ]
        );

        if ($titikTemu) {
            OrderItem::updateOrCreate(
                [
                    'order_id' => $orderActive->id,
                    'nama_produk' => 'Titik Temu',
                ],
                [
                    'product_id' => $titikTemu->id,
                    'rasa' => 'Kopi Susu Gula Aren',
                    'jumlah' => 2,
                    'harga_satuan' => 10000,
                    'subtotal' => 20000,
                ]
            );
        }

        if ($titikHitam) {
            OrderItem::updateOrCreate(
                [
                    'order_id' => $orderActive->id,
                    'nama_produk' => 'Titik Hitam',
                ],
                [
                    'product_id' => $titikHitam->id,
                    'rasa' => 'Americano',
                    'jumlah' => 1,
                    'harga_satuan' => 8000,
                    'subtotal' => 8000,
                ]
            );
        }

        // Pembayaran Midtrans berhasil.
        Payment::updateOrCreate(
            ['referensi_provider' => 'DEMO-MIDTRANS-001'],
            [
                'order_id' => $orderActive->id,
                'metode_pembayaran' => 'midtrans',
                'status' => 'berhasil',
                'jumlah' => 28000,
                'kedaluwarsa_pada' => null,
                'waktu_verifikasi' => Carbon::parse('2026-11-08 10:05:00'),
            ]
        );

        // Riwayat perubahan status pesanan.
        OrderStatusHistory::updateOrCreate(
            [
                'order_id' => $orderActive->id,
                'status_baru' => 'menunggu_pembayaran',
            ],
            [
                'diubah_oleh' => $pelanggan->id,
                'waktu_perubahan' => Carbon::parse('2026-11-08 10:00:00'),
                'keterangan' => 'Pesanan berhasil dibuat oleh pelanggan.',
            ]
        );

        OrderStatusHistory::updateOrCreate(
            [
                'order_id' => $orderActive->id,
                'status_baru' => 'diproses',
            ],
            [
                'diubah_oleh' => null,
                'waktu_perubahan' => Carbon::parse('2026-11-08 10:05:00'),
                'keterangan' => 'Pembayaran berhasil dikonfirmasi dan pesanan mulai diproses.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Pesanan Selesai (Riwayat)
        |--------------------------------------------------------------------------
        */

        $orderDone = Order::updateOrCreate(
            ['kode' => 'KTT-20261024-0002'],
            [
                'sumber_transaksi' => 'online',
                'metode_pemenuhan' => 'pengiriman',

                'user_id' => $pelanggan->id,
                'courier_id' => $kurir?->id,

                'status' => 'selesai',

                'subtotal' => 20000,
                'ongkir' => 0,
                'total' => 20000,

                'nama_pelanggan' => null,
                'nama_penerima' => $pelanggan->name,
                'telepon' => '081238499210',
                'alamat' => 'Jl. Kuwung No. 12, Mergelo, Kota Mojokerto',

                'lat' => -7.4650000,
                'lng' => 112.4300000,

                // Snapshot lokasi asal belum tersedia pada data demo.
                'nama_lokasi_asal' => null,
                'alamat_lokasi_asal' => null,
                'latitude_lokasi_asal' => null,
                'longitude_lokasi_asal' => null,

                'jarak_pengiriman' => null,
                'batas_pengambilan' => null,

                'catatan' => null,
                'stok_dikembalikan' => false,

                'created_at' => Carbon::parse('2026-10-24 10:00:00'),
                'updated_at' => Carbon::parse('2026-10-24 10:15:00'),
            ]
        );

        if ($titikManja) {
            OrderItem::updateOrCreate(
                [
                    'order_id' => $orderDone->id,
                    'nama_produk' => 'Titik Manja',
                ],
                [
                    'product_id' => $titikManja->id,
                    'rasa' => 'Kopi Susu Caramel',
                    'jumlah' => 1,
                    'harga_satuan' => 10000,
                    'subtotal' => 10000,
                ]
            );
        }

        if ($titikTemu) {
            OrderItem::updateOrCreate(
                [
                    'order_id' => $orderDone->id,
                    'nama_produk' => 'Titik Temu',
                ],
                [
                    'product_id' => $titikTemu->id,
                    'rasa' => 'Kopi Susu Gula Aren',
                    'jumlah' => 1,
                    'harga_satuan' => 10000,
                    'subtotal' => 10000,
                ]
            );
        }

        Payment::updateOrCreate(
            ['referensi_provider' => 'DEMO-MIDTRANS-002'],
            [
                'order_id' => $orderDone->id,
                'metode_pembayaran' => 'midtrans',
                'status' => 'berhasil',
                'jumlah' => 20000,
                'kedaluwarsa_pada' => null,
                'waktu_verifikasi' => Carbon::parse('2026-10-24 10:05:00'),
            ]
        );

        // Riwayat status lengkap untuk pesanan selesai.
        $history = [
            [
                'status_baru' => 'menunggu_pembayaran',
                'diubah_oleh' => $pelanggan->id,
                'keterangan' => 'Pesanan berhasil dibuat oleh pelanggan.',
            ],
            [
                'status_baru' => 'diproses',
                'diubah_oleh' => null,
                'keterangan' => 'Pembayaran berhasil dikonfirmasi dan pesanan mulai diproses.',
            ],
            [
                'status_baru' => 'dikirim',
                'diubah_oleh' => $kurir?->id,
                'keterangan' => 'Pesanan sedang dikirim kepada pelanggan.',
            ],
            [
                'status_baru' => 'selesai',
                'diubah_oleh' => $kurir?->id,
                'keterangan' => 'Pesanan telah diterima oleh pelanggan.',
            ],
        ];

        foreach ($history as $index => $item) {
            OrderStatusHistory::updateOrCreate(
                [
                    'order_id' => $orderDone->id,
                    'status_baru' => $item['status_baru'],
                ],
                [
                    'diubah_oleh' => $item['diubah_oleh'],
                    'waktu_perubahan' => Carbon::parse('2026-10-24 10:00:00')
                        ->addMinutes($index * 5),
                    'keterangan' => $item['keterangan'],
                ]
            );
        }
    }
}
