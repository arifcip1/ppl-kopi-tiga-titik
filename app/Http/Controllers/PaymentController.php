<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Services\MidtransService;
use App\Services\OrderStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
        protected OrderStockService $orderStockService
    ) {}

    /**
     * Menampilkan halaman pembayaran QRIS pelanggan (FR-6.3 / PPL-27).
     *
     * URL: GET /pembayaran-qris atau /pembayaran-qris/{kode}
     */
    public function show(Request $request, ?string $kode = null): Response|View|RedirectResponse
    {
        $orderId = $request->query('order_id') ?? session('order_id');

        $query = Order::with(['orderItems', 'payment']);

        if ($kode !== null) {
            $order = $query->where('kode', $kode)->first();
        } elseif ($orderId !== null) {
            $order = $query->where('id', $orderId)->first();
        } else {
            // Jika tidak ada parameter, ambil pesanan menunggu pembayaran terbaru milik user login
            $order = $query->where('user_id', Auth::id())
                ->where('status', 'menunggu_pembayaran')
                ->latest()
                ->first();
        }

        if ($order === null) {
            return redirect()->route('orders')->with('error', 'Pesanan tidak ditemukan atau belum dipilih.');
        }

        // Otorisasi: Hanya pemilik pesanan atau admin yang dapat mengakses
        if (Auth::check() && Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak untuk mengakses pembayaran pesanan ini.');
        }

        // Isolasi Transaksi: Pesanan POS kasir offline tidak menggunakan pembayaran gateway online
        if ($order->sumber_transaksi === 'pos') {
            return redirect()->route('orders')->with('error', 'Pesanan kasir offline POS tidak menggunakan halaman pembayaran online.');
        }

        // Jika pesanan sudah dibayar, langsung alihkan ke pelacakan status
        if ($order->isProcessing() || $order->isShipped() || $order->isCompleted()) {
            return redirect()->route('tracking.detail', $order->kode)
                ->with('success', 'Pembayaran pesanan ini telah berhasil diselesaikan.');
        }

        // Pengecekan masa kedaluwarsa realtime saat halaman dibuka (FR-6.8)
        if ($order->isWaitingPayment()) {
            $expiresAt = $order->expires_at;
            if ($expiresAt !== null && now()->greaterThanOrEqualTo($expiresAt)) {
                $this->orderStockService->expireOrder($order);
                $order->refresh();
            }
        }

        if ($order->isCancelled()) {
            return redirect()->route('orders')->with('error', 'Pesanan telah dibatalkan karena melewati batas waktu pembayaran.');
        }

        // Request QRIS Charge ke Midtrans Sandbox
        $qrisData = $this->midtransService->createQrisCharge($order, $order->payment);

        // Ambil info lapak aktif
        $activeLocationId = Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
        $lokasiLapak = ShippingLocation::find($activeLocationId)
            ?? ShippingLocation::where('aktif', true)->first();

        // Hitung sisa detik untuk hitung mundur frontend (10 menit TTL)
        $expiresAt = $order->expires_at ?? now()->addMinutes(10);
        $remainingSeconds = max(0, now()->diffInSeconds($expiresAt, false));

        return response()
            ->view('Pembayaran QRIS Page', [
                'order' => $order,
                'payment' => $order->payment,
                'qrisData' => $qrisData,
                'lokasiLapak' => $lokasiLapak,
                'remainingSeconds' => (int) $remainingSeconds,
                'expiresAt' => $expiresAt,
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Endpoint API polling status pembayaran realtime (FR-6.3).
     *
     * URL: GET /api/orders/{identifier}/payment-status
     */
    public function status(Request $request, string $identifier): JsonResponse
    {
        $order = Order::with('payment')
            ->where('id', $identifier)
            ->orWhere('kode', $identifier)
            ->first();

        if ($order === null) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        // Otorisasi kepemilikan pesanan
        if (Auth::check() && !in_array(Auth::user()->role, ['admin', 'kurir']) && $order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        // Pengecekan kedaluwarsa & sinkronisasi status langsung ke API Midtrans Sandbox saat polling (FR-6.3 & FR-6.8)
        if ($order->isWaitingPayment()) {
            $this->midtransService->syncTransactionStatusFromMidtrans($order, $order->payment);
            $order->refresh();
        }

        if ($order->isWaitingPayment()) {
            $expiresAt = $order->expires_at;
            if ($expiresAt !== null && now()->greaterThanOrEqualTo($expiresAt)) {
                $this->orderStockService->expireOrder($order);
                $order->refresh();
            }
        }

        $payment = $order->payment;

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'kode_pesanan' => $order->kode,
                'order_status' => $order->status,
                'payment_status' => $payment?->status ?? 'pending',
                'is_paid' => in_array($order->status, ['diproses', 'dikirim', 'selesai']),
                'is_expired' => $order->status === 'dibatalkan',
                'verified_at' => $payment?->waktu_verifikasi?->toISOString(),
                'expires_at' => $order->expires_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Endpoint Webhook Handler Server-to-Server dari Midtrans Sandbox (FR-6.4).
     *
     * URL: POST /api/payments/webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        $result = $this->midtransService->handleNotification($payload);

        $httpCode = $result['code'] ?? ($result['success'] ? 200 : 400);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'order_status' => $result['order_status'] ?? null,
            'payment_status' => $result['payment_status'] ?? null,
        ], $httpCode);
    }

    /**
     * Endpoint simulasi pembayaran sukses khusus mode Sandbox / Pengujian Lokal.
     *
     * URL: POST /api/orders/{identifier}/simulate-payment
     */
    public function simulateSuccess(Request $request, string $identifier): JsonResponse
    {
        // Proteksi: Simulasi hanya diizinkan di environment non-produksi (local / testing)
        if (!app()->environment(['local', 'testing']) && config('services.midtrans.is_production', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Simulasi pembayaran hanya tersedia di lingkungan non-produksi.',
            ], 403);
        }

        $order = Order::with('payment')
            ->where('id', $identifier)
            ->orWhere('kode', $identifier)
            ->first();

        if ($order === null) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        // Proteksi Otorisasi: Hanya pemilik pesanan atau admin/owner yang berhak mensimulasikan pembayaran
        $user = Auth::user();
        if ($order->user_id !== null) {
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu untuk mengakses pesanan ini.',
                ], 401);
            }

            if ((int) $user->id !== (int) $order->user_id && !in_array($user->role, ['admin', 'owner'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak berhak melakukan simulasi pembayaran pesanan ini.',
                ], 403);
            }
        } else {
            if (!$user || !in_array($user->role, ['admin', 'owner'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin/owner yang dapat mensimulasikan pembayaran pesanan ini.',
                ], 403);
            }
        }

        if (!$order->isWaitingPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah tidak menunggu pembayaran (Status: ' . $order->status . ').',
            ], 400);
        }

        $payment = $order->payment;
        $midtransOrderId = $order->kode . '-' . ($payment ? $payment->id : 1);
        $serverKey = config('services.midtrans.server_key', '');
        $grossAmount = (string) $order->total;
        $statusCode = '200';
        $signature = hash('sha512', $midtransOrderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $midtransOrderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
        ];

        $result = $this->midtransService->handleNotification($payload);

        return response()->json([
            'success' => $result['success'] ?? true,
            'message' => 'Pembayaran berhasil disimulasikan lunas.',
            'redirect_url' => route('tracking.detail', $order->kode),
        ]);
    }
}

