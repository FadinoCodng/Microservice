<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(string $productName, int $requestedQty, int $availableStock)
    {
        parent::__construct(
            "Stok {$productName} tidak mencukupi: diminta {$requestedQty}, tersedia {$availableStock}"
        );
    }
}
