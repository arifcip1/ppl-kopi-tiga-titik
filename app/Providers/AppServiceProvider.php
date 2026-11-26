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
        // PPL-26: Resolver ongkir berbasis rute jalan OpenStreetMap / OpenRouteService
        $this->app->bind(
            \App\Services\Shipping\ShippingCostResolver::class,
            \App\Services\Shipping\OsmRouteShippingCostResolver::class
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

            // Data Lokasi Lapak Aktif & Jam Operasional Terkini (PPL-25, FR-11.6)
            $lokasiAktif = null;
            $jamBuka = '15:00';
            $jamTutup = '18:00';
            $statusLapak = 'buka';
            $statusLapakManual = 'buka';
            $isLapakOpen = true;
            $isOutsideOperatingHours = false;
            $lapakClosedMessage = null;

            if (Schema::hasTable('shipping_locations') && Schema::hasTable('settings')) {
                $activeLocId = \App\Models\Setting::where('key', 'lokasi_pengiriman_aktif_id')->value('value');
                if ($activeLocId) {
                    $lokasiAktif = \App\Models\ShippingLocation::find($activeLocId);
                }
                if (! $lokasiAktif) {
                    $lokasiAktif = \App\Models\ShippingLocation::where('aktif', true)->first();
                }

                $operational = app(\App\Services\LapakOperationalService::class)->getStatus();
                $jamBuka = $operational['jam_buka'];
                $jamTutup = $operational['jam_tutup'];
                $statusLapak = $operational['effective_status'];
                $statusLapakManual = $operational['manual_status'];
                $isLapakOpen = $operational['is_open'];
                $isOutsideOperatingHours = $operational['is_outside_hours'];
                $lapakClosedMessage = $operational['closed_message'];
            }

            $view->with([
                'cartCount'               => $cartCount,
                'lokasiAktif'             => $lokasiAktif,
                'jamBuka'                 => $jamBuka,
                'jamTutup'                => $jamTutup,
                'statusLapak'             => $statusLapak,
                'statusLapakManual'       => $statusLapakManual,
                'isLapakOpen'             => $isLapakOpen,
                'isOutsideOperatingHours' => $isOutsideOperatingHours,
                'lapakClosedMessage'      => $lapakClosedMessage,
            ]);
        });

        // PPL-39 & PPL-41: Suntikkan notifikasi web dinamis secara konsisten pada seluruh halaman layout Owner, Kurir & Pelanggan
        View::composer(['layouts.owner', 'layouts.kurir', 'layouts.pelanggan'], function ($view) {
            if (array_key_exists('notifications', $view->getData())) {
                return;
            }

            $notifications = [];
            if (Auth::check() && Schema::hasTable('orders') && Schema::hasTable('products') && Schema::hasTable('settings')) {
                $notifications = app(\App\Services\WebNotificationService::class)->getNotificationsForUser(Auth::user());
            }

            $view->with('notifications', $notifications);
        });
    }
}
