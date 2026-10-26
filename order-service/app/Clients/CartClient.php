<?php

namespace App\Clients;

use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CartClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.cart.url', 'http://localhost:3001');
        $this->timeout = (int) config('services.cart.timeout', 5);
    }

    /**
     * Retrieve the user's shopping cart from cart-service.
     *
     * @param int $userId
     * @return array
     * @throws ServiceUnavailableException If cart-service is unreachable or times out (503)
     */
    public function getCart(int $userId): array
    {
        $url = rtrim($this->baseUrl, '/') . "/carts/{$userId}";

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($url);

            if ($response->status() === 404) {
                return [
                    'user_id' => $userId,
                    'items' => [],
                    'total_quantity' => 0,
                ];
            }

            if ($response->successful()) {
                $data = $response->json('data');
                return is_array($data) ? $data : [];
            }

            Log::error("Cart service returned HTTP {$response->status()} for user ID {$userId}", [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new ServiceUnavailableException('Cart service');
        } catch (ConnectionException $e) {
            Log::error("Cart service connection failed for user ID {$userId}: {$e->getMessage()}", [
                'url' => $url,
                'timeout' => $this->timeout,
                'error' => $e->getMessage(),
            ]);

            throw new ServiceUnavailableException('Cart service');
        } catch (Throwable $e) {
            if ($e instanceof ServiceUnavailableException) {
                throw $e;
            }

            Log::error("Unexpected failure connecting to Cart service: {$e->getMessage()}", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new ServiceUnavailableException('Cart service');
        }
    }

    /**
     * Request cart-service to empty the user's cart after order placement.
     *
     * @param int $userId
     * @return bool True if successfully cleared, false otherwise
     */
    public function clearCart(int $userId): bool
    {
        $url = rtrim($this->baseUrl, '/') . "/carts/{$userId}";

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->delete($url);

            if ($response->successful()) {
                return true;
            }

            Log::warning("Failed to clear cart for user ID {$userId}, received HTTP {$response->status()}", [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::warning("Exception while attempting to clear cart for user ID {$userId}: {$e->getMessage()}", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
