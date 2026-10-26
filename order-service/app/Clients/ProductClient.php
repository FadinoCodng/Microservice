<?php

namespace App\Clients;

use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.product.url', 'http://localhost:3000');
        $this->timeout = (int) config('services.product.timeout', 5);
    }

    /**
     * Fetch product details from product-service.
     *
     * @param int $productId
     * @return array|null Null if product is not found (404)
     * @throws ServiceUnavailableException If product-service is unreachable or times out (503)
     */
    public function getProduct(int $productId): ?array
    {
        $url = rtrim($this->baseUrl, '/') . "/products/{$productId}";

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($url);

            if ($response->status() === 404) {
                return null;
            }

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::error("Product service returned HTTP {$response->status()} for product ID {$productId}", [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new ServiceUnavailableException('Product service');
        } catch (ConnectionException $e) {
            Log::error("Product service connection failed for product ID {$productId}: {$e->getMessage()}", [
                'url' => $url,
                'timeout' => $this->timeout,
                'error' => $e->getMessage(),
            ]);

            throw new ServiceUnavailableException('Product service');
        } catch (Throwable $e) {
            if ($e instanceof ServiceUnavailableException) {
                throw $e;
            }

            Log::error("Unexpected failure connecting to Product service: {$e->getMessage()}", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new ServiceUnavailableException('Product service');
        }
    }
}
