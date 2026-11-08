<?php

namespace App\Services;

use RuntimeException;

/**
 * Kegagalan checkout yang dapat ditampilkan ke pelanggan.
 * Controller (Tahap 3) memetakan message dan errors ke respons JSON.
 */
class CheckoutException extends RuntimeException
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(string $message, private array $errors = [])
    {
        parent::__construct($message);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
