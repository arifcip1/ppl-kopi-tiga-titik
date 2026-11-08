<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Tampilkan halaman keranjang belanja atau kembalikan data keranjang (FR-4.1, FR-4.2, FR-4.3).
     * Endpoint: GET /cart atau GET /api/cart
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $cartItems = $user->cartItems()
            ->with('product')
            ->orderBy('id', 'asc')
            ->get();

        // Hitung subtotal tiap baris produk
        $cartItems->each(function ($item) {
            $item->subtotal = $item->product ? ($item->product->harga * $item->jumlah) : 0;
            $item->subtotal_formatted = 'Rp ' . number_format($item->subtotal, 0, ',', '.');
        });

        $totalPesanan = (int) $cartItems->sum('subtotal');
        $totalJumlah = (int) $cartItems->sum('jumlah');
        $cartCount = $cartItems->count();

        // Sinkronkan ke session untuk kompabilitas
        session()->put('cart', $cartItems->pluck('jumlah', 'product_id')->toArray());

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Berhasil mengambil keranjang belanja',
                'data' => [
                    'items'         => $cartItems,
                    'total_pesanan' => $totalPesanan,
                    'total_jumlah'  => $totalJumlah,
                    'cart_count'    => $cartCount,
                ],
            ], 200);
        }

        return view('Cart Page', compact('cartItems', 'totalPesanan', 'totalJumlah'));
    }

    /**
     * Tambah produk ke keranjang belanja (FR-4.4, FR-4.6, BR-04).
     * Endpoint: POST /cart/items atau POST /api/cart/items
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);

        // BR-13: Validasi produk aktif
        if (!$product->aktif) {
            return response()->json([
                'message' => 'Produk ini sedang tidak aktif dan tidak dapat ditambahkan ke keranjang.',
                'errors'  => ['product_id' => ['Produk sedang tidak aktif.']],
            ], 422);
        }

        // BR-04: Validasi stok tersedia
        if ($product->stok <= 0) {
            return response()->json([
                'message' => 'Stok produk habis.',
                'errors'  => ['jumlah' => ['Stok produk habis.']],
            ], 422);
        }

        $jumlahToAdd = (int) ($validated['jumlah'] ?? 1);

        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        // FR-4.6: Jika produk sudah ada, akumulasikan jumlahnya
        $newTotal = $cartItem ? ($cartItem->jumlah + $jumlahToAdd) : $jumlahToAdd;

        // FR-4.4: Validasi tidak melebihi stok yang tersedia
        if ($newTotal > $product->stok) {
            return response()->json([
                'message' => "Jumlah melebihi stok yang tersedia (maksimal {$product->stok} botol).",
                'errors'  => ['jumlah' => ["Jumlah melebihi stok yang tersedia (maksimal {$product->stok} botol)."]],
            ], 422);
        }

        if ($cartItem) {
            $cartItem->jumlah = $newTotal;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id'    => Auth::id(),
                'product_id' => $product->id,
                'jumlah'     => $jumlahToAdd,
            ]);
        }

        // Sinkronkan ke session
        $cartMap = Auth::user()->cartItems()->pluck('jumlah', 'product_id')->toArray();
        session()->put('cart', $cartMap);

        $cartItems = Auth::user()->cartItems()->with('product')->get();
        $totalPesanan = (int) $cartItems->sum(fn($i) => $i->product ? ($i->product->harga * $i->jumlah) : 0);
        $totalJumlah = (int) $cartItems->sum('jumlah');
        $cartCount = $cartItems->count();

        return response()->json([
            'message'       => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count'    => $cartCount,
            'total_jumlah'  => $totalJumlah,
            'total_pesanan' => $totalPesanan,
            'item'          => $cartItem->load('product'),
        ], 200);
    }

    /**
     * Ubah jumlah item di keranjang (FR-4.2, FR-4.4, BR-04).
     * Endpoint: PATCH /cart/items/{id} atau PATCH /api/cart/items/{id}
     */
    public function update(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $cartItem = CartItem::where('user_id', Auth::id())
            ->with('product')
            ->findOrFail($id);

        // BR-13: Validasi status produk aktif
        if (!$cartItem->product || !$cartItem->product->aktif) {
            return response()->json([
                'message' => 'Produk ini tidak aktif lagi.',
                'errors'  => ['product' => ['Produk tidak aktif.']],
            ], 422);
        }

        // FR-4.4 & BR-04: Validasi jumlah tidak melebihi stok
        if ($validated['jumlah'] > $cartItem->product->stok) {
            return response()->json([
                'message' => "Jumlah melebihi stok yang tersedia (tersisa {$cartItem->product->stok} botol).",
                'errors'  => ['jumlah' => ["Jumlah melebihi stok yang tersedia (tersisa {$cartItem->product->stok} botol)."]],
            ], 422);
        }

        $cartItem->jumlah = (int) $validated['jumlah'];
        $cartItem->save();

        // Sinkronkan ke session
        $cartMap = Auth::user()->cartItems()->pluck('jumlah', 'product_id')->toArray();
        session()->put('cart', $cartMap);

        $cartItems = Auth::user()->cartItems()->with('product')->get();
        $subtotal = $cartItem->product ? ($cartItem->product->harga * $cartItem->jumlah) : 0;
        $totalPesanan = (int) $cartItems->sum(fn($i) => $i->product ? ($i->product->harga * $i->jumlah) : 0);
        $totalJumlah = (int) $cartItems->sum('jumlah');
        $cartCount = $cartItems->count();

        return response()->json([
            'message'                 => 'Jumlah produk berhasil diperbarui',
            'data'                    => [
                'id'                      => $cartItem->id,
                'jumlah'                  => $cartItem->jumlah,
                'subtotal'                => $subtotal,
                'subtotal_formatted'      => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                'total_pesanan'           => $totalPesanan,
                'total_pesanan_formatted' => 'Rp ' . number_format($totalPesanan, 0, ',', '.'),
                'total_jumlah'            => $totalJumlah,
                'cart_count'              => $cartCount,
            ],
        ], 200);
    }

    /**
     * Hapus item dari keranjang (FR-4.2).
     * Endpoint: DELETE /cart/items/{id} atau DELETE /api/cart/items/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $cartItem = CartItem::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->delete();

        // Sinkronkan ke session
        $cartMap = Auth::user()->cartItems()->pluck('jumlah', 'product_id')->toArray();
        session()->put('cart', $cartMap);

        $cartItems = Auth::user()->cartItems()->with('product')->get();
        $totalPesanan = (int) $cartItems->sum(fn($i) => $i->product ? ($i->product->harga * $i->jumlah) : 0);
        $totalJumlah = (int) $cartItems->sum('jumlah');
        $cartCount = $cartItems->count();

        return response()->json([
            'message' => 'Produk berhasil dihapus dari keranjang',
            'data'    => [
                'cart_count'              => $cartCount,
                'total_pesanan'           => $totalPesanan,
                'total_pesanan_formatted' => 'Rp ' . number_format($totalPesanan, 0, ',', '.'),
                'total_jumlah'            => $totalJumlah,
                'is_empty'                => $cartCount === 0,
            ],
        ], 200);
    }
}

