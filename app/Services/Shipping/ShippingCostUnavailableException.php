<?php

namespace App\Services\Shipping;

use App\Services\CheckoutException;

class ShippingCostUnavailableException extends CheckoutException
{
    public function __construct(string $message = 'Perhitungan ongkir belum tersedia. Checkout belum dapat dilanjutkan.')
    {
        parent::__construct($message, ['ongkir' => [$message]]);
    }
}
