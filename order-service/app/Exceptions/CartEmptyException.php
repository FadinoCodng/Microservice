<?php

namespace App\Exceptions;

use RuntimeException;

class CartEmptyException extends RuntimeException
{
    public function __construct(string $message = 'Keranjang belanja masih kosong')
    {
        parent::__construct($message);
    }
}
