<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidOrderStatusException extends RuntimeException
{
    public function __construct(string $currentStatus)
    {
        parent::__construct(
            "Pesanan berstatus {$currentStatus}, hanya pesanan PENDING yang dapat dibayar"
        );
    }
}
