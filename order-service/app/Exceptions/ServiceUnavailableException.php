<?php

namespace App\Exceptions;

use RuntimeException;

class ServiceUnavailableException extends RuntimeException
{
    public function __construct(string $serviceName = 'Service')
    {
        parent::__construct("{$serviceName} tidak dapat dihubungi, coba lagi nanti");
    }
}
