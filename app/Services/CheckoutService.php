<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Shipping\ShippingCostResolver;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Pembuatan pesanan dari keranjang (PPL-18, BR-04, BR-07 sampai BR-10, BR-32, BR-36).
 *
 * Satu-satunya tempat stok dikurangi. Pembayaran berhasil tidak mengurangi stok lagi;
 * pengembalian stok ditangani OrderStockService.
 */
class CheckoutService
{
    /** Batas waktu pembayaran (menit), BR-36. */
    public const PAYMENT_TTL_MINUTES = 10;

    public function __construct(private readonly ShippingCostResolver $shippingResolver)
    {
    }

    /**
     * @param  array<string, mixed>  $dataPenerima  nama_penerima, telepon, alamat, lat, lng, catatan (opsional).
     *                                              Kunci lain (harga, subtotal, total, ongkir, status) diabaikan.
     *
     * @throws CheckoutException
     */
    public function createFromCart(User $user, array $dataPenerima): Order
    {
        $penerima = $this->validateRecipient($dataPenerima);

        if (! CartItem::query()->where('user_id', $user->id)->exists()) {
            throw new CheckoutException('Keranjang masih kosong.', ['keranjang' => ['Keranjang masih kosong.']]);
        }

        // Dihitung sebelum transaksi: tidak menahan kunci baris selama menunggu layanan routing.
        // Kegagalan resolver melempar exception sehingga belum ada data yang tersimpan.
        $ongkir = $this->shippingResolver->resolve($penerima['lat'], $penerima['lng']);

        if ($ongkir < 0) {
            throw new CheckoutException('Perhitungan ongkir tidak valid.', ['ongkir' => ['Perhitungan ongkir tidak valid.']]);
        }

        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                return DB::transaction(function () use ($user, $penerima, $ongkir) {
                    $cartItems = CartItem::query()
                        ->where('user_id', $user->id)
                        ->orderBy('product_id')
                        ->lockForUpdate()
                        ->get();

                    if ($cartItems->isEmpty()) {
                        throw new CheckoutException('Keranjang masih kosong.', ['keranjang' => ['Keranjang masih kosong.']]);
                    }

                    // Produk dikunci berurutan menurut id; harga dan stok hanya dari database.
                    $products = Product::query()
                        ->whereIn('id', $cartItems->pluck('product_id'))
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    $errors = [];
                    $lines = [];
                    $subtotal = 0;

                    foreach ($cartItems as $cartItem) {
                        $product = $products->get($cartItem->product_id);

                        if ($product === null || ! $product->aktif) {
                            $nama = $product?->nama ?? 'Produk';
                            $errors['produk'][] = "{$nama} sudah tidak tersedia.";
                            continue;
                        }

                        if ($product->stok < $cartItem->jumlah) {
                            $errors['stok'][] = "Stok {$product->nama} tidak tersedia (tersisa {$product->stok}).";
                            continue;
                        }

                        $lineSubtotal = $product->harga * $cartItem->jumlah;
                        $subtotal += $lineSubtotal;
                        $lines[] = [$product, $cartItem->jumlah, $lineSubtotal];
                    }

                    if ($errors !== []) {
                        throw new CheckoutException('Sebagian item keranjang tidak dapat dipesan.', $errors);
                    }

                    $order = Order::create([
                        'kode' => Order::generateOrderCode(),
                        'user_id' => $user->id,
                        'courier_id' => null,
                        'status' => 'menunggu_pembayaran',
                        'subtotal' => $subtotal,
                        'ongkir' => $ongkir,
                        'total' => $subtotal + $ongkir,
                        'nama_penerima' => $penerima['nama_penerima'],
                        'telepon' => $penerima['telepon'],
                        'alamat' => $penerima['alamat'],
                        'lat' => $penerima['lat'],
                        'lng' => $penerima['lng'],
                        'catatan' => $penerima['catatan'],
                        'stok_dikembalikan' => false,
                    ]);

                    foreach ($lines as [$product, $jumlah, $lineSubtotal]) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'nama_produk' => $product->nama,
                            'rasa' => $product->rasa,
                            'jumlah' => $jumlah,
                            'harga_satuan' => $product->harga,
                            'subtotal' => $lineSubtotal,
                        ]);
                    }

                    Payment::create([
                        'order_id' => $order->id,
                        'status' => 'pending',
                        'jumlah' => $order->total,
                        'kedaluwarsa_pada' => now()->addMinutes(self::PAYMENT_TTL_MINUTES),
                    ]);

                    // Satu-satunya pengurangan stok untuk pesanan ini.
                    foreach ($lines as [$product, $jumlah]) {
                        $product->update(['stok' => $product->stok - $jumlah]);
                    }

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status_sebelumnya' => null,
                        'status_baru' => 'menunggu_pembayaran',
                        'user_id' => $user->id,
                        'keterangan' => 'Pesanan dibuat, stok ditahan.',
                    ]);

                    // Keranjang dikosongkan terakhir, hanya jika semua langkah di atas berhasil.
                    CartItem::query()->where('user_id', $user->id)->delete();

                    return $order->load(['orderItems', 'payment']);
                });
            } catch (QueryException $e) {
                if ($this->isOrderCodeDuplicate($e) && $attempt < $maxAttempts) {
                    continue;
                }

                throw $e;
            }
        }

        throw new \RuntimeException('Checkout gagal setelah percobaan maksimal.');
    }

    /**
     * Memeriksa apakah QueryException disebabkan secara spesifik oleh duplicate key pada kolom orders.kode.
     * Tidak mengandalkan SQLSTATE 23000 saja karena dapat dipicu oleh foreign key atau constraint lain.
     */
    private function isOrderCodeDuplicate(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        // 1. Deteksi SQLite: pesan spesifik unique constraint pada orders.kode
        if (str_contains($message, 'unique constraint failed: orders.kode')) {
            return true;
        }

        // 2. Deteksi MySQL: kode error duplicate entry 1062 dan mengidentifikasi constraint/kolom orders.kode
        $isMysqlDuplicate = isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062;
        if ($isMysqlDuplicate) {
            return str_contains($message, 'orders_kode_unique')
                || str_contains($message, 'orders.kode_unique')
                || (str_contains($message, 'duplicate entry') && str_contains($message, 'orders.kode'));
        }

        return false;
    }

    /**
     * Hanya field penerima yang dipercaya dari input; nilai finansial tidak pernah dibaca.
     *
     * @return array{nama_penerima: string, telepon: string, alamat: string, lat: float, lng: float, catatan: ?string}
     */
    private function validateRecipient(array $data): array
    {
        $errors = [];

        foreach (['nama_penerima' => 'Nama penerima', 'telepon' => 'Nomor telepon', 'alamat' => 'Alamat'] as $key => $label) {
            if (! isset($data[$key]) || ! is_string($data[$key]) || trim($data[$key]) === '') {
                $errors[$key][] = "{$label} wajib diisi.";
            }
        }

        $lat = $data['lat'] ?? null;
        $lng = $data['lng'] ?? null;

        if (! is_numeric($lat) || $lat < -90 || $lat > 90) {
            $errors['lat'][] = 'Titik lokasi tujuan wajib diisi dan valid.';
        }

        if (! is_numeric($lng) || $lng < -180 || $lng > 180) {
            $errors['lng'][] = 'Titik lokasi tujuan wajib diisi dan valid.';
        }

        if ($errors !== []) {
            throw new CheckoutException('Data pengiriman tidak valid.', $errors);
        }

        $catatan = isset($data['catatan']) && is_string($data['catatan']) && trim($data['catatan']) !== ''
            ? trim($data['catatan'])
            : null;

        return [
            'nama_penerima' => trim($data['nama_penerima']),
            'telepon' => trim($data['telepon']),
            'alamat' => trim($data['alamat']),
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'catatan' => $catatan,
        ];
    }
}
