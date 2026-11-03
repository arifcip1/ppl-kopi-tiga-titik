<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $cartCount = 0;
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->role === 'pelanggan' && Schema::hasTable('cart_items')) {
                    $itemCount = $user->cartItems()->count();

                    // Jika akun pelanggan belum memiliki item keranjang sama sekali,
                    // inisialisasi default 2 produk demo dari Cart Page (Titik Temu & Titik Hitam)
                    if ($itemCount === 0 && Schema::hasTable('products')) {
                        $titikTemu = Product::where('nama', 'Titik Temu')->first();
                        $titikHitam = Product::where('nama', 'Titik Hitam')->first();

                        if ($titikTemu) {
                            CartItem::firstOrCreate([
                                'user_id' => $user->id,
                                'product_id' => $titikTemu->id,
                            ], ['jumlah' => 2]);
                        }
                        if ($titikHitam) {
                            CartItem::firstOrCreate([
                                'user_id' => $user->id,
                                'product_id' => $titikHitam->id,
                            ], ['jumlah' => 1]);
                        }
                        $itemCount = $user->cartItems()->count();
                    }
                    $cartCount = (int) $itemCount;
                }
            }

            if ($cartCount === 0 && session()->has('cart')) {
                $sessionCart = session('cart');
                $cartCount = is_countable($sessionCart) ? count($sessionCart) : 0;
            }

            $view->with('cartCount', $cartCount);
        });
    }
}
