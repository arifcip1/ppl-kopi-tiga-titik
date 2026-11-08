<?php

namespace App\Services\Shipping;

/**
 * Implementasi default: belum ada aturan ongkir yang terintegrasi (menunggu PPL 26).
 * Sengaja tidak mengembalikan Rp0 atau tarif sementara; checkout ditolak.
 */
class UnavailableShippingCostResolver implements ShippingCostResolver
{
    public function resolve(float $lat, float $lng): int
    {
        throw new ShippingCostUnavailableException();
    }
}
