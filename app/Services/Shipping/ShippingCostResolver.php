<?php

namespace App\Services\Shipping;

/**
 * Abstraksi perhitungan ongkir (PPL-18). Implementasi tarif berbasis rute OSM
 * akan disediakan PPL 26; sampai saat itu binding default menolak checkout.
 */
interface ShippingCostResolver
{
    /**
     * Hitung ongkir (rupiah, bilangan bulat) dari koordinat tujuan.
     *
     * @throws ShippingCostUnavailableException jika ongkir tidak dapat dihitung.
     */
    public function resolve(float $lat, float $lng): int;
}
