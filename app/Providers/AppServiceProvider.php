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
        // PPL-18: sampai PPL 26 (ongkir rute OSM) tersedia, checkout ditolak.
        $this->app->bind(
            \App\Services\Shipping\ShippingCostResolver::class,
            \App\Services\Shipping\UnavailableShippingCostResolver::class
        );
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

            // Data Lokasi Lapak Aktif & Jam Operasional Terkini (PPL-25)
            $lokasiAktif = null;
            $jamBuka = '15:00';
            $jamTutup = '18:00';
            $statusLapak = 'buka';

            if (Schema::hasTable('shipping_locations') && Schema::hasTable('settings')) {
                $activeLocId = \App\Models\Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
                if ($activeLocId) {
                    $lokasiAktif = \App\Models\ShippingLocation::find($activeLocId);
                }
                if (! $lokasiAktif) {
                    $lokasiAktif = \App\Models\ShippingLocation::where('aktif', true)->first();
                }

                $jamBuka = \App\Models\Setting::where('key', 'jam_buka')->value('value') ?? '15:00';
                $jamTutup = \App\Models\Setting::where('key', 'jam_tutup')->value('value') ?? '18:00';
                $statusLapak = \App\Models\Setting::where('key', 'status_lapak')->value('value') ?? 'buka';
            }

            $view->with([
                'cartCount'   => $cartCount,
                'lokasiAktif' => $lokasiAktif,
                'jamBuka'     => $jamBuka,
                'jamTutup'    => $jamTutup,
                'statusLapak' => $statusLapak,
            ]);
        });
    }
}
