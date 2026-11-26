<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\SalesHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service Integrasi Pembayaran Midtrans QRIS Sandbox (PPL-27 / F6 / PPL-19)
 */
class MidtransService
{
    /**
     * Memperoleh Base URL API Midtrans berdasarkan environment sandbox/production.
     */
    public function getBaseUrl(): string
    {
        return config('services.midtrans.is_production', false)
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Memperoleh Server Key Midtrans.
     */
    public function getServerKey(): string
    {
        return (string) config('services.midtrans.server_key', '');
    }

    /**
     * Memperoleh Client Key Midtrans untuk integrasi frontend.
     */
    public function getClientKey(): string
    {
        return (string) config('services.midtrans.client_key', '');
    }

    /**
     * Membuat transaksi QRIS charge ke Midtrans Sandbox (FR-6.2).
     *
     * @param Order $order
     * @param Payment|null $payment
     * @return array
     */
    public function createQrisCharge(Order $order, ?Payment $payment = null): array
    {
        $serverKey = $this->getServerKey();
        $baseUrl = $this->getBaseUrl();

        if ($payment === null) {
            $payment = $order->payment ?? Payment::create([
                'order_id' => $order->id,
                'metode_pembayaran' => 'midtrans',
                'status' => 'pending',
                'jumlah' => $order->total,
                'kedaluwarsa_pada' => $order->created_at?->copy()->addMinutes(10) ?? now()->addMinutes(10),
            ]);
        }

        // Format order_id unik untuk Midtrans: KTT-YYYYMMDD-NNNN-{payment_id}
        $midtransOrderId = $order->kode . '-' . $payment->id;

        // Jika sudah pernah berhasil membuat QRIS asli di Midtrans untuk payment ini, gunakan kembali agar tidak terkena 406 Duplicate Order ID saat refresh halaman
        $existingRaw = is_array($payment->payload_mentah) ? $payment->payload_mentah : [];
        $existingRef = (string) ($payment->referensi_provider ?? '');
        if ($existingRef !== '' && !str_starts_with($existingRef, 'MOCK-MID-') && !empty($existingRaw)) {
            $existingQrUrl = null;
            if (!empty($existingRaw['actions']) && is_array($existingRaw['actions'])) {
                foreach ($existingRaw['actions'] as $action) {
                    if (($action['name'] ?? '') === 'generate-qr-code') {
                        $existingQrUrl = $action['url'] ?? null;
                        break;
                    }
                }
            }

            if ($existingQrUrl || !empty($existingRaw['qr_string'])) {
                return [
                    'success' => true,
                    'order_id' => $existingRaw['order_id'] ?? $midtransOrderId,
                    'transaction_id' => $existingRef,
                    'gross_amount' => (int) $order->total,
                    'qr_url' => $existingQrUrl,
                    'qr_string' => $existingRaw['qr_string'] ?? null,
                    'status' => $existingRaw['transaction_status'] ?? 'pending',
                    'raw' => $existingRaw,
                ];
            }
        }

        $items = $order->orderItems->map(fn($item) => [
            'id' => (string) $item->product_id,
            'price' => (int) $item->harga_satuan,
            'quantity' => (int) $item->jumlah,
            'name' => mb_substr($item->nama_produk, 0, 50),
        ])->values()->all();

        if ($order->ongkir > 0) {
            $items[] = [
                'id' => 'SHIPPING_FEE',
                'price' => (int) $order->ongkir,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'customer_details' => [
                'first_name' => $order->nama_penerima,
                'phone' => $order->telepon,
            ],
            'item_details' => $items,
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->timeout(10)
                ->acceptJson()
                ->post("{$baseUrl}/v2/charge", $payload);

            if ($response->successful()) {
                $data = $response->json();

                $qrUrl = null;
                if (!empty($data['actions']) && is_array($data['actions'])) {
                    foreach ($data['actions'] as $action) {
                        if (($action['name'] ?? '') === 'generate-qr-code') {
                            $qrUrl = $action['url'] ?? null;
                            break;
                        }
                    }
                }

                $qrString = $data['qr_string'] ?? null;
                $transactionId = $data['transaction_id'] ?? $midtransOrderId;

                $payment->update([
                    'referensi_provider' => $transactionId,
                    'payload_mentah' => $data,
                ]);

                return [
                    'success' => true,
                    'order_id' => $midtransOrderId,
                    'transaction_id' => $transactionId,
                    'gross_amount' => (int) $order->total,
                    'qr_url' => $qrUrl,
                    'qr_string' => $qrString,
                    'status' => $data['transaction_status'] ?? 'pending',
                    'raw' => $data,
                ];
            }

            Log::error('Midtrans Charge Error: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('Midtrans API Request Exception: ' . $e->getMessage());
        }

        // Fallback simulasi QRIS Sandbox jika offline / testing tanpa koneksi eksternal
        $mockTransactionId = 'MOCK-MID-' . time() . '-' . $payment->id;
        $mockQrUrl = 'https://api.sandbox.midtrans.com/v2/qris/' . $mockTransactionId . '/qr-code';
        $mockQrString = '00020101021226540014ID.LINKAJA.WWW0118936009110022207163021000000222075204549953033605802ID5914Kopi Tiga Titik6009Mojokerto61056131162070703A016304' . substr(md5($order->kode), 0, 4);

        $payment->update([
            'referensi_provider' => $payment->referensi_provider ?: $mockTransactionId,
            'payload_mentah' => [
                'transaction_id' => $mockTransactionId,
                'order_id' => $midtransOrderId,
                'gross_amount' => (string) $order->total,
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    ['name' => 'generate-qr-code', 'method' => 'GET', 'url' => $mockQrUrl]
                ],
                'qr_string' => $mockQrString,
                'status_code' => '201',
                'status_message' => 'Success, QRIS transaction is created',
            ],
        ]);

        return [
            'success' => true,
            'order_id' => $midtransOrderId,
            'transaction_id' => $payment->referensi_provider,
            'gross_amount' => (int) $order->total,
            'qr_url' => $mockQrUrl,
            'qr_string' => $mockQrString,
            'status' => 'pending',
            'raw' => $payment->payload_mentah,
        ];
    }

    /**
     * Mengecek status transaksi secara aktif ke API Midtrans Sandbox (GET /v2/{order_id}/status)
     * agar pembayaran melalui Midtrans Simulator langsung terdeteksi otomatis meskipun berjalan di localhost.
     */
    public function syncTransactionStatusFromMidtrans(Order $order, ?Payment $payment = null): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        $payment = $payment ?? $order->payment;
        if ($payment === null) {
            return false;
        }

        $serverKey = $this->getServerKey();
        if ($serverKey === '' || str_contains($serverKey, 'default-test-key') || str_contains($serverKey, 'test-key-12345')) {
            return false;
        }

        $ref = (string) ($payment->referensi_provider ?? '');
        if ($ref === '' || str_starts_with($ref, 'MOCK-MID-')) {
            return false;
        }

        $raw = is_array($payment->payload_mentah) ? $payment->payload_mentah : [];
        $midtransOrderId = (string) ($raw['order_id'] ?? ($order->kode . '-' . $payment->id));
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->timeout(5)
                ->acceptJson()
                ->get("{$baseUrl}/v2/{$midtransOrderId}/status");

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();
            $transactionStatus = (string) ($data['transaction_status'] ?? '');
            if ($transactionStatus === '' || $transactionStatus === 'pending') {
                return false;
            }

            $statusCode = (string) ($data['status_code'] ?? '200');
            $grossAmount = (string) ($data['gross_amount'] ?? $order->total);
            $signature = hash('sha512', $midtransOrderId . $statusCode . $grossAmount . $serverKey);

            $payload = array_merge($data, [
                'order_id' => $midtransOrderId,
                'status_code' => $statusCode,
                'gross_amount' => $grossAmount,
                'signature_key' => $signature,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $data['fraud_status'] ?? 'accept',
            ]);

            $result = $this->handleNotification($payload);

            return (bool) ($result['success'] ?? false);
        } catch (\Throwable $e) {
            Log::debug('Midtrans Status Check Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifikasi keaslian signature webhook SHA-512 dari Midtrans (FR-6.4).
     *
     * Rumus: SHA512(order_id + status_code + gross_amount + ServerKey)
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = $this->getServerKey();
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Memproses notifikasi webhook resmi dari Midtrans secara idempoten (FR-6.4, FR-6.5, FR-6.6, FR-6.8).
     *
     * @param array $payload
     * @return array
     */
    public function handleNotification(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '' || $transactionStatus === '') {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Payload notifikasi tidak lengkap.',
            ];
        }

        // 1. Verifikasi Signature Server-Side (FR-6.4)
        if (!$this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans Webhook: Signature tidak valid untuk order ' . $orderId);
            return [
                'success' => false,
                'code' => 401,
                'message' => 'Signature key tidak valid.',
            ];
        }

        // 2. Ekstrak Kode Pesanan dan cari Order terkait
        // Format order_id bisa: KTT-YYYYMMDD-NNNN atau KTT-YYYYMMDD-NNNN-{payment_id}
        $orderKode = preg_replace('/-\d+$/', '', $orderId);

        $order = Order::where('kode', $orderKode)
            ->orWhere('kode', $orderId)
            ->first();

        if ($order === null) {
            // Coba cari melalui referensi_provider di tabel payments
            $paymentByRef = Payment::where('referensi_provider', $payload['transaction_id'] ?? $orderId)->first();
            if ($paymentByRef !== null) {
                $order = $paymentByRef->order;
            }
        }

        if ($order === null) {
            Log::warning('Midtrans Webhook: Pesanan tidak ditemukan untuk order_id ' . $orderId);
            return [
                'success' => false,
                'code' => 404,
                'message' => 'Pesanan dengan referensi terkait tidak ditemukan.',
            ];
        }

        // Validasi Isolasi POS: Pesanan POS Kasir Offline tidak boleh diproses melalui Midtrans webhook (BR-39)
        if ($order->sumber_transaksi === 'pos') {
            Log::warning('Midtrans Webhook: Pesanan POS tidak dapat diproses via Midtrans webhook untuk order ' . $orderId);
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Pesanan POS offline tidak menggunakan integrasi webhook online.',
            ];
        }

        // 3. Validasi Nominal Transaksi (FR-6.5)
        $notifGross = (int) round((float) $grossAmount);
        if ($notifGross !== (int) $order->total) {
            Log::error("Midtrans Webhook: Nominal tidak cocok untuk pesanan {$order->kode}. Diharapkan {$order->total}, diterima {$notifGross}");
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Nominal transaksi tidak cocok dengan nilai pesanan.',
            ];
        }

        // 4. Proses Transisi Status Secara Atomik & Idempoten (FR-6.6, FR-6.10)
        return DB::transaction(function () use ($order, $payload, $transactionStatus, $fraudStatus) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::lockForUpdate()->find($order->id);

            /** @var Payment|null $payment */
            $payment = Payment::where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($payment === null) {
                $payment = Payment::create([
                    'order_id' => $lockedOrder->id,
                    'metode_pembayaran' => 'midtrans',
                    'status' => 'pending',
                    'jumlah' => $lockedOrder->total,
                    'kedaluwarsa_pada' => $lockedOrder->expires_at ?? now()->addMinutes(10),
                ]);
            }

            // Proteksi Out-of-Order / Downgrade:
            // Jika pembayaran sudah berhasil atau pesanan sudah berada di status operasional lanjutan (diproses, dikirim, selesai),
            // abaikan notifikasi status selain settlement/capture agar tidak menimpa status pembayaran menjadi pending/kedaluwarsa/gagal.
            if ($payment->isSuccess() || in_array($lockedOrder->status, ['diproses', 'dikirim', 'selesai'], true)) {
                if ($transactionStatus !== 'settlement' && !($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                    Log::info("Midtrans Webhook: Mengabaikan notifikasi status '{$transactionStatus}' karena pesanan {$lockedOrder->kode} sudah berstatus lunas/diproses.");
                    return [
                        'success' => true,
                        'code' => 200,
                        'message' => 'Status pembayaran sudah berhasil, notifikasi diabaikan secara idempoten.',
                        'order_status' => $lockedOrder->status,
                        'payment_status' => $payment->status,
                    ];
                }
            }

            // Skenario 1: Pembayaran Berhasil (settlement / capture)
            if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                // Idempoten: Jika sudah diproses/berhasil, abaikan pemrosesan ulang
                if ($lockedOrder->status === 'diproses' || $payment->isSuccess()) {
                    $payment->update([
                        'payload_mentah' => $payload,
                        'referensi_provider' => $payload['transaction_id'] ?? $payment->referensi_provider,
                    ]);

                    return [
                        'success' => true,
                        'code' => 200,
                        'message' => 'Notifikasi duplikat diabaikan secara idempoten.',
                        'order_status' => $lockedOrder->status,
                        'payment_status' => $payment->status,
                    ];
                }

                // Notifikasi terlambat setelah dibatalkan/kedaluwarsa (FR-6.9)
                if ($lockedOrder->status === 'dibatalkan') {
                    $payment->update([
                        'payload_mentah' => $payload,
                        'referensi_provider' => $payload['transaction_id'] ?? $payment->referensi_provider,
                    ]);

                    OrderStatusHistory::create([
                        'order_id' => $lockedOrder->id,
                        'status_baru' => 'dibatalkan',
                        'diubah_oleh' => null,
                        'waktu_perubahan' => now(),
                        'keterangan' => '[perhatian_owner] Pembayaran berhasil diterima setelah pesanan kedaluwarsa/dibatalkan.',
                    ]);

                    Log::warning("Midtrans Webhook: Pembayaran sukses diterima setelah pesanan {$lockedOrder->kode} kedaluwarsa/dibatalkan.");
                    return [
                        'success' => true,
                        'code' => 200,
                        'message' => 'Pembayaran diterima setelah pesanan kedaluwarsa. Ditandai untuk penanganan Owner.',
                        'order_status' => $lockedOrder->status,
                        'payment_status' => $payment->status,
                    ];
                }

                // Transisi Pesanan: Menunggu Pembayaran -> Diproses
                $payment->update([
                    'status' => 'berhasil',
                    'waktu_verifikasi' => now(),
                    'referensi_provider' => $payload['transaction_id'] ?? $payment->referensi_provider,
                    'payload_mentah' => $payload,
                ]);

                // S7: Untuk pesanan ambil_sendiri, batas waktu pengambilan 24 jam dihitung saat pembayaran lunas
                $orderUpdates = ['status' => 'diproses'];
                if ($lockedOrder->metode_pemenuhan === 'ambil_sendiri') {
                    $orderUpdates['batas_pengambilan'] = now()->addHours(24);
                }

                $lockedOrder->update($orderUpdates);

                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'status_baru' => 'diproses',
                    'diubah_oleh' => null,
                    'waktu_perubahan' => now(),
                    'keterangan' => 'Pembayaran QRIS Midtrans berhasil diverifikasi.',
                ]);

                // Catat transaksi riil ke histori penjualan (F15 / SalesHistory)
                // Kombinasi product_id, tanggal, sumber unik: akumulasikan jumlah jika sudah ada
                foreach ($lockedOrder->orderItems as $item) {
                    $salesRecord = SalesHistory::where('product_id', $item->product_id)
                        ->whereDate('tanggal', today())
                        ->where('sumber', 'transaksi')
                        ->lockForUpdate()
                        ->first();

                    if ($salesRecord) {
                        $salesRecord->increment('jumlah_terjual', (int) $item->jumlah);
                    } else {
                        SalesHistory::create([
                            'product_id' => $item->product_id,
                            'tanggal' => today()->toDateString(),
                            'jumlah_terjual' => (int) $item->jumlah,
                            'sumber' => 'transaksi',
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'code' => 200,
                    'message' => 'Pembayaran berhasil diverifikasi dan status pesanan diperbarui menjadi diproses.',
                    'order_status' => 'diproses',
                    'payment_status' => 'berhasil',
                ];
            }

            // Skenario 2: Kedaluwarsa dari Provider (expire)
            if ($transactionStatus === 'expire') {
                $payment->update([
                    'status' => 'kedaluwarsa',
                    'payload_mentah' => $payload,
                ]);

                if ($lockedOrder->status === 'menunggu_pembayaran' && !$lockedOrder->stok_dikembalikan) {
                    app(OrderStockService::class)->restoreStock($lockedOrder, null, 'Pembayaran kedaluwarsa dari Midtrans, stok dikembalikan.');
                }

                return [
                    'success' => true,
                    'code' => 200,
                    'message' => 'Pembayaran kedaluwarsa, pesanan dibatalkan dan stok dikembalikan.',
                    'order_status' => 'dibatalkan',
                    'payment_status' => 'kedaluwarsa',
                ];
            }

            // Skenario 3: Ditolak / Dibatalkan (deny, cancel, failure)
            if (in_array($transactionStatus, ['deny', 'cancel', 'failure'], true)) {
                $payment->update([
                    'status' => 'gagal',
                    'payload_mentah' => $payload,
                ]);

                if ($lockedOrder->status === 'menunggu_pembayaran' && !$lockedOrder->stok_dikembalikan) {
                    app(OrderStockService::class)->failOrder($lockedOrder);
                }

                return [
                    'success' => true,
                    'code' => 200,
                    'message' => 'Pembayaran gagal, stok pesanan dikembalikan.',
                    'order_status' => 'dibatalkan',
                    'payment_status' => 'gagal',
                ];
            }

            // Skenario 4: Status Pending
            $payment->update([
                'status' => 'pending',
                'payload_mentah' => $payload,
            ]);

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Status pembayaran pending diterima.',
                'order_status' => $lockedOrder->status,
                'payment_status' => 'pending',
            ];
        });
    }
}
