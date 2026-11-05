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
                    $cartCount = (int) $user->cartItems()->count();
                }
            }

            if ($cartCount === 0 && session()->has('cart')) {
                $cart = session('cart', []);
                $cartCount = is_countable($cart) ? count($cart) : 0;
            }

            $view->with('cartCount', $cartCount);
        });
    }
}
