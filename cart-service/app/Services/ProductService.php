<?php

namespace App\Services;

use App\Exceptions\CartItemNotFoundException;
use App\Models\CartItem;

class ProductService
{
   public function getProduct(int $productId): array
   {
       try {
           $response = Http:: baseUrl(config('services.product_service.url'))
               ->get("products/{$productId}");
            if ($response->succesfull() && isset($response->json()['data'])) {
                return $response['data'];
            }
            return null;
       } catch(throwable $e) {
            log::error("Failed to fetch product data for product ID {$productId}: " . $e->getMessage());
           return null;
       }
   }
}
