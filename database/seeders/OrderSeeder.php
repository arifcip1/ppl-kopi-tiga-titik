<?php

namespace Database\Seeders;

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

        // 1. Pesanan Sedang Diproses (Aktif)
        $orderActive = Order::updateOrCreate(
            ['kode' => 'KTT-20261108-0001'],
            [
                'user_id' => $pelanggan->id,
                'courier_id' => $kurir ? $kurir->id : null,
                'status' => 'diproses',
                'subtotal' => 28000,
                'ongkir' => 0,
                'total' => 28000,
                'nama_penerima' => $pelanggan->name,
                'telepon' => '081238499210',
                'alamat' => 'Jl. Hayam Wuruk No. 42, Mergelo, Kec. Magersari, Kota Mojokerto',
                'lat' => -7.4721000,
                'lng' => 112.4340000,
                'catatan' => 'Es batu dikit aja kak',
                'stok_dikembalikan' => false,
            ]
        );

        if ($titikTemu) {
            OrderItem::updateOrCreate(
                ['order_id' => $orderActive->id, 'nama_produk' => 'Titik Temu'],
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
                ['order_id' => $orderActive->id, 'nama_produk' => 'Titik Hitam'],
                [
                    'product_id' => $titikHitam->id,
                    'rasa' => 'Americano',
                    'jumlah' => 1,
                    'harga_satuan' => 8000,
                    'subtotal' => 8000,
                ]
            );
        }

        Payment::updateOrCreate(
            ['order_id' => $orderActive->id],
            [
                'referensi_provider' => 'QRIS-DEMO-001',
                'status' => 'berhasil',
                'jumlah' => 28000,
                'kedaluwarsa_pada' => now()->addMinutes(10),
                'waktu_verifikasi' => now(),
            ]
        );

        OrderStatusHistory::updateOrCreate(
            ['order_id' => $orderActive->id, 'status_baru' => 'menunggu_pembayaran'],
            [
                'status_sebelumnya' => null,
                'user_id' => $pelanggan->id,
                'keterangan' => 'Pesanan berhasil dibuat oleh pelanggan',
            ]
        );

        OrderStatusHistory::updateOrCreate(
            ['order_id' => $orderActive->id, 'status_baru' => 'diproses'],
            [
                'status_sebelumnya' => 'menunggu_pembayaran',
                'user_id' => null,
                'keterangan' => 'Pembayaran QRIS lunas, barista mulai meracik kopi',
            ]
        );

        // 2. Pesanan Selesai (Riwayat)
        $orderDone = Order::updateOrCreate(
            ['kode' => 'KTT-20261024-0002'],
            [
                'user_id' => $pelanggan->id,
                'courier_id' => $kurir ? $kurir->id : null,
                'status' => 'selesai',
                'subtotal' => 20000,
                'ongkir' => 0,
                'total' => 20000,
                'nama_penerima' => $pelanggan->name,
                'telepon' => '081238499210',
                'alamat' => 'Jl. Kuwung No. 12, Mergelo, Kota Mojokerto',
                'lat' => -7.4650000,
                'lng' => 112.4300000,
                'catatan' => null,
                'stok_dikembalikan' => false,
            ]
        );

        if ($titikManja) {
            OrderItem::updateOrCreate(
                ['order_id' => $orderDone->id, 'nama_produk' => 'Titik Manja'],
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
                ['order_id' => $orderDone->id, 'nama_produk' => 'Titik Temu (Riwayat)'],
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
            ['order_id' => $orderDone->id],
            [
                'referensi_provider' => 'QRIS-DEMO-002',
                'status' => 'berhasil',
                'jumlah' => 20000,
                'kedaluwarsa_pada' => now()->subDays(10),
                'waktu_verifikasi' => now()->subDays(10),
            ]
        );

        OrderStatusHistory::updateOrCreate(
            ['order_id' => $orderDone->id, 'status_baru' => 'selesai'],
            [
                'status_sebelumnya' => 'dikirim',
                'user_id' => $kurir ? $kurir->id : null,
                'keterangan' => 'Pesanan telah diterima oleh pembeli',
            ]
        );
    }
}

