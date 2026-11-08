<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutException;
use App\Services\CheckoutService;
use App\Services\OrderStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Memproses checkout pesanan pelanggan (PPL-18).
     * Endpoint: POST /checkout
     */
    public function checkout(Request $request, CheckoutService $checkoutService): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama_penerima' => ['required', 'string', 'max:255'],
            'telepon'       => ['required', 'string', 'max:20'],
            'alamat'        => ['required', 'string'],
            'lat'           => ['required', 'numeric', 'between:-90,90'],
            'lng'           => ['required', 'numeric', 'between:-180,180'],
            'catatan'       => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'telepon.required'       => 'Nomor telepon wajib diisi.',
            'alamat.required'        => 'Alamat pengiriman wajib diisi.',
            'lat.required'           => 'Titik lokasi tujuan (latitude) wajib diisi.',
            'lat.numeric'            => 'Titik lokasi tujuan (latitude) harus berupa angka valid.',
            'lat.between'            => 'Titik lokasi tujuan (latitude) tidak valid.',
            'lng.required'           => 'Titik lokasi tujuan (longitude) wajib diisi.',
            'lng.numeric'            => 'Titik lokasi tujuan (longitude) harus berupa angka valid.',
            'lng.between'            => 'Titik lokasi tujuan (longitude) tidak valid.',
        ]);

        try {
            // Nilai finansial (subtotal, ongkir, total), stok, status, dan user_id dihitung murni di backend.
            $order = $checkoutService->createFromCart($request->user(), $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pesanan berhasil dibuat.',
                    'data'    => [
                        'id'         => $order->id,
                        'kode'       => $order->kode,
                        'subtotal'   => $order->subtotal,
                        'ongkir'     => $order->ongkir,
                        'total'      => $order->total,
                        'status'     => $order->status,
                        'expires_at' => $order->expires_at?->toISOString(),
                        'items'      => $order->orderItems,
                    ],
                ], 201);
            }

            return redirect()->route('payment')->with([
                'order_id' => $order->id,
                'success'  => 'Pesanan berhasil dibuat.',
            ]);
        } catch (CheckoutException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }

            return back()->withInput()->withErrors($e->errors())->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan sistem saat memproses pesanan. Silakan coba beberapa saat lagi.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses pesanan.');
        }
    }

    /**
     * Membatalkan pesanan oleh pelanggan yang berhak (PPL-18).
     * Endpoint: POST /orders/{order}/cancel
     */
    public function cancel(Request $request, $order, OrderStockService $stockService): JsonResponse|RedirectResponse
    {
        $orderModel = $order instanceof Order ? $order : Order::find($order);

        if (! $orderModel) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            abort(404, 'Pesanan tidak ditemukan.');
        }

        // Pastikan pesanan milik pengguna yang sedang login (aman terhadap perbedaan tipe data)
        if ((int) $orderModel->user_id !== (int) $request->user()->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk membatalkan pesanan ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membatalkan pesanan ini.');
        }

        // Validasi awal menggunakan aturan model Order (termasuk canBeCancelledByCustomer)
        if (! $orderModel->canBeCancelledByCustomer()) {
            $message = 'Pesanan tidak dapat dibatalkan.';
            if ($orderModel->payment && $orderModel->payment->isSuccess()) {
                $message = 'Pesanan yang sudah dibayar tidak dapat dibatalkan.';
            } elseif ($orderModel->status !== 'menunggu_pembayaran') {
                $message = 'Pesanan dengan status saat ini tidak dapat dibatalkan.';
            } elseif ($orderModel->expires_at && now()->greaterThanOrEqualTo($orderModel->expires_at)) {
                $message = 'Waktu pembayaran telah kedaluwarsa. Pesanan tidak dapat dibatalkan.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        // Delegasikan pengembalian stok dan perubahan status ke OrderStockService
        // dengan validasi kedaluwarsa atomik di dalam transaksi
        $restored = $stockService->cancelByCustomer(
            $orderModel,
            (int) $request->user()->id
        );

        if (! $restored) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pesanan tidak dapat dibatalkan atau waktu pembayaran telah kedaluwarsa.',
                ], 422);
            }

            return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
        }

        $orderModel->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pesanan berhasil dibatalkan dan stok telah dikembalikan.',
                'data'    => [
                    'id'                => $orderModel->id,
                    'kode'              => $orderModel->kode,
                    'status'            => $orderModel->status,
                    'stok_dikembalikan' => $orderModel->stok_dikembalikan,
                ],
            ], 200);
        }

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
