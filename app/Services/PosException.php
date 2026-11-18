<?php

namespace App\Services;

use RuntimeException;

/**
 * Pengecualian / kegagalan transaksi POS kasir lapak offline (PPL-32).
 */
class PosException extends RuntimeException
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
