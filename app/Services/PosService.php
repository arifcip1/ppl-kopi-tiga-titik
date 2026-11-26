<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service Transaksi POS Kasir Offline (Lapak Fisik) & Aturan Rule-Based Restok (PPL-32 / F10 / F12).
 *
 * Aturan Bisnis Terkait:
 * - BR-13: Transaksi POS memakai sumber_transaksi=pos, metode_pemenuhan=null, user_id boleh null/admin.
 *          Pembayaran tunai/QRIS langsung di lapak, status berhasil dicatat setelah konfirmasi Owner.
 *          POS tidak memanggil Midtrans atau webhook.
 * - BR-14: Transaksi POS berhasil mengurangi stok secara atomik. Transaksi gagal tidak mengurangi stok.
 * - BR-15: Penjualan POS dicatat ke sales_history dengan sumber=transaksi, akumulasi harian (idempoten).
 * - BR-18 / F10: Peringatan restok muncul saat stok <= 5, saran produksi max(0, 10 - stok).
 */
class PosService
{
    /**
     * Memproses transaksi POS Kasir secara atomik.
     *
     * @param  array<string, mixed>  $data  [items => [...], metode_pembayaran => 'tunai'|'qris', nama_pelanggan => '...', uang_diterima => ...]
     * @param  User|null  $admin  Pengguna kasir/admin yang login
     * @return array<string, mixed>  Data nota pesanan dan status stok produk terbaru
     *
     * @throws PosException
     */
    public function createTransaction(array $data, ?User $admin = null): array
    {
        // 1. Validasi awal daftar item
        $rawItems = $data['items'] ?? [];
        if (empty($rawItems) || !is_array($rawItems)) {
            throw new PosException('Keranjang kasir masih kosong. Silakan pilih minimal 1 produk.', [
                'items' => ['Keranjang kasir masih kosong.'],
            ]);
        }

        // Konsolidasi item jika ada product_id yang duplikat dalam array
        $consolidatedItems = [];
        foreach ($rawItems as $idx => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['jumlah'] ?? $item['qty'] ?? 0);

            if ($productId <= 0) {
                throw new PosException("Produk pada baris ke-" . ($idx + 1) . " tidak valid.", [
                    'items' => ["Produk pada baris ke-" . ($idx + 1) . " tidak valid."],
                ]);
            }

            if ($qty <= 0) {
                throw new PosException("Kuantitas produk harus minimal 1 botol.", [
                    'items' => ["Kuantitas produk harus minimal 1 botol."],
                ]);
            }

            if (!isset($consolidatedItems[$productId])) {
                $consolidatedItems[$productId] = 0;
            }
            $consolidatedItems[$productId] += $qty;
        }

        $metodePembayaran = strtolower(trim((string) ($data['metode_pembayaran'] ?? 'tunai')));
        if (!in_array($metodePembayaran, ['tunai', 'qris'], true)) {
            throw new PosException('Metode pembayaran harus berupa tunai atau qris.', [
                'metode_pembayaran' => ['Metode pembayaran harus berupa tunai atau qris.'],
            ]);
        }

        $namaPelanggan = trim((string) ($data['nama_pelanggan'] ?? 'Pelanggan Lapak'));
        if ($namaPelanggan === '') {
            $namaPelanggan = 'Pelanggan Lapak';
        }

        $uangDiterimaInput = isset($data['uang_diterima']) && $data['uang_diterima'] !== ''
            ? (int) $data['uang_diterima']
            : null;

        // Ambil konfigurasi aturan restok (F10)
        $batasStokMinimum = (int) (Setting::where('key', 'batas_stok_minimum')->value('value') ?? 5);
        $stokHarianStandar = (int) (Setting::where('key', 'stok_harian_standar')->value('value') ?? 10);

        // 2. Eksekusi Transaksi Database Atomik
        return DB::transaction(function () use (
            $consolidatedItems,
            $metodePembayaran,
            $namaPelanggan,
            $uangDiterimaInput,
            $admin,
            $batasStokMinimum,
            $stokHarianStandar
        ) {
            // Urutkan kunci ID produk untuk mencegah potensi deadlocks pada query beruntun
            ksort($consolidatedItems);
            $productIds = array_keys($consolidatedItems);

            // Kunci baris produk dengan lockForUpdate()
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $orderItemsToCreate = [];
            $subtotal = 0;

            foreach ($consolidatedItems as $productId => $qty) {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if ($product === null) {
                    throw new PosException("Produk dengan ID {$productId} tidak ditemukan di sistem.", [
                        'items' => ["Produk dengan ID {$productId} tidak ditemukan."],
                    ]);
                }

                if (!$product->aktif) {
                    throw new PosException("Varian kopi '{$product->nama}' sedang dinonaktifkan.", [
                        'items' => ["Varian kopi '{$product->nama}' sedang dinonaktifkan."],
                    ]);
                }

                if ($product->stok < $qty) {
                    throw new PosException(
                        "Stok varian '{$product->nama}' tidak mencukupi. Tersedia: {$product->stok} botol, diminta: {$qty} botol.",
                        ['items' => ["Stok varian '{$product->nama}' tidak mencukupi (sisa {$product->stok} botol)."]]
                    );
                }

                $hargaSatuan = (int) $product->harga;
                $itemSubtotal = $hargaSatuan * $qty;
                $subtotal += $itemSubtotal;

                $orderItemsToCreate[] = [
                    'product'      => $product,
                    'product_id'   => $product->id,
                    'nama_produk'  => $product->nama,
                    'rasa'         => $product->rasa,
                    'jumlah'       => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal'     => $itemSubtotal,
                ];
            }

            $total = $subtotal;
            $uangDiterima = 0;
            $kembalian = 0;

            if ($metodePembayaran === 'tunai') {
                $uangDiterima = $uangDiterimaInput !== null ? $uangDiterimaInput : $total;
                if ($uangDiterima < $total) {
                    throw new PosException(
                        'Uang tunai yang diterima kurang dari total belanja (Total: Rp ' . number_format($total, 0, ',', '.') . ').',
                        ['uang_diterima' => ['Uang tunai yang diterima kurang dari total belanja.']]
                    );
                }
                $kembalian = $uangDiterima - $total;
            } else {
                // QRIS Lapak Langsung
                $uangDiterima = $total;
                $kembalian = 0;
            }

            // 3. Simpan entri pesanan di tabel `orders` (BR-13)
            $orderCode = Order::generatePosCode();

            $order = Order::create([
                'kode'              => $orderCode,
                'sumber_transaksi'  => 'pos',
                'metode_pemenuhan'  => null,
                'user_id'           => $admin?->id,
                'courier_id'        => null,
                'status'            => 'selesai',
                'subtotal'          => $subtotal,
                'ongkir'            => 0,
                'total'             => $total,
                'nama_pelanggan'    => $namaPelanggan,
                'nama_penerima'     => null,
                'telepon'           => null,
                'alamat'            => null,
                'stok_dikembalikan' => false,
            ]);

            // 4. Simpan rincian item di `order_items` & kurangi stok produk secara atomik (BR-14)
            $createdItems = [];
            foreach ($orderItemsToCreate as $itemData) {
                $createdItem = OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $itemData['product_id'],
                    'nama_produk'  => $itemData['nama_produk'],
                    'rasa'         => $itemData['rasa'],
                    'jumlah'       => $itemData['jumlah'],
                    'harga_satuan' => $itemData['harga_satuan'],
                    'subtotal'     => $itemData['subtotal'],
                ]);
                $createdItems[] = $createdItem;

                /** @var Product $prod */
                $prod = $itemData['product'];
                $prod->decrement('stok', $itemData['jumlah']);
            }

            // 5. Simpan baris pembayaran di tabel `payments` (BR-13)
            $payloadMentah = [
                'tipe'          => 'pos_offline',
                'uang_diterima' => $uangDiterima,
                'kembalian'     => $kembalian,
                'kasir_id'      => $admin?->id,
                'kasir_nama'    => $admin?->name ?? 'Owner',
            ];

            $payment = Payment::create([
                'order_id'          => $order->id,
                'referensi_provider'=> null,
                'metode_pembayaran' => $metodePembayaran,
                'status'            => 'berhasil',
                'jumlah'            => $total,
                'kedaluwarsa_pada'  => null,
                'waktu_verifikasi'  => now(),
                'payload_mentah'    => $payloadMentah,
            ]);

            // 6. Catat riwayat status pesanan di `order_status_history`
            OrderStatusHistory::create([
                'order_id'        => $order->id,
                'status_baru'     => 'selesai',
                'diubah_oleh'     => $admin?->id,
                'waktu_perubahan' => now(),
                'keterangan'      => 'Transaksi POS kasir lapak offline selesai langsung di tempat.',
            ]);

            // 7. Akumulasi jumlah terjual di `sales_history` (BR-15 / Pengamanan Wajib #2)
            $tanggal = today()->toDateString();
            foreach ($orderItemsToCreate as $itemData) {
                $salesRecord = SalesHistory::where('product_id', $itemData['product_id'])
                    ->whereDate('tanggal', $tanggal)
                    ->where('sumber', 'transaksi')
                    ->lockForUpdate()
                    ->first();

                if ($salesRecord) {
                    $salesRecord->increment('jumlah_terjual', (int) $itemData['jumlah']);
                } else {
                    SalesHistory::create([
                        'product_id'     => $itemData['product_id'],
                        'tanggal'        => $tanggal,
                        'jumlah_terjual' => (int) $itemData['jumlah'],
                        'sumber'         => 'transaksi',
                    ]);
                }
            }

            // 8. Ambil data produk terbaru & evaluasi Rule-Based Peringatan Restok (F10 / Pengamanan Wajib #3)
            $allActiveProducts = Product::where('aktif', true)
                ->orderBy('nama')
                ->get()
                ->map(function (Product $p) use ($batasStokMinimum, $stokHarianStandar) {
                    $stok = (int) $p->stok;
                    $isLowStock = $stok <= $batasStokMinimum;
                    $isOutOfStock = $stok <= 0;
                    $saranProduksi = max(0, $stokHarianStandar - $stok);

                    return [
                        'id'             => $p->id,
                        'nama'           => $p->nama,
                        'rasa'           => $p->rasa,
                        'harga'          => (int) $p->harga,
                        'stok'           => $stok,
                        'is_low_stock'   => $isLowStock,
                        'is_out_of_stock'=> $isOutOfStock,
                        'saran_produksi' => $saranProduksi,
                    ];
                });

            $lowStockProducts = $allActiveProducts->filter(fn ($p) => $p['is_low_stock'])->values();

            return [
                'order' => [
                    'id'               => $order->id,
                    'kode'             => $order->kode,
                    'waktu'            => $order->created_at->format('d M Y, H:i'),
                    'tanggal'          => $order->created_at->format('d/m/Y'),
                    'nama_pelanggan'   => $order->nama_pelanggan,
                    'metode_pembayaran'=> $metodePembayaran,
                    'subtotal'         => $subtotal,
                    'total'            => $total,
                    'uang_diterima'    => $uangDiterima,
                    'kembalian'        => $kembalian,
                    'items'            => $createdItems,
                ],
                'products'           => $allActiveProducts,
                'low_stock_products' => $lowStockProducts,
                'restock_settings'   => [
                    'batas_stok_minimum'  => $batasStokMinimum,
                    'stok_harian_standar' => $stokHarianStandar,
                ],
            ];
        });
    }
}
