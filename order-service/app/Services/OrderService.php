<?php

namespace App\Services;

use App\Clients\CartClient;
use App\Clients\ProductClient;
use App\Exceptions\CartEmptyException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderService
{
    public function __construct(
        private CartClient $cartClient,
        private ProductClient $productClient
    ) {
    }

    /**
     * Execute the checkout process: transform cart items into an order.
     *
     * @param int $userId
     * @return Order
     * @throws CartEmptyException If the cart has no items (409)
     * @throws ProductNotFoundException If any product in the cart does not exist (409)
     * @throws InsufficientStockException If requested quantity exceeds stock (409)
     * @throws \App\Exceptions\ServiceUnavailableException If cart or product service is down (503)
     */
    public function createOrder(int $userId): Order
    {
        // 1. Fetch cart from cart-service
        $cart = $this->cartClient->getCart($userId);
        $items = $cart['items'] ?? [];

        if (empty($items)) {
            throw new CartEmptyException('Keranjang belanja kosong');
        }

        // 2. Validate each item against product-service & check stock & compute totals
        $totalAmount = 0;
        $orderItemsData = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $requestedQty = (int) $item['quantity'];

            // Fetch product snapshot from product-service
            $product = $this->productClient->getProduct($productId);

            if ($product === null) {
                throw new ProductNotFoundException($productId);
            }

            $availableStock = (int) ($product['stock'] ?? 0);
            if ($availableStock < $requestedQty) {
                throw new InsufficientStockException(
                    $product['name'] ?? "ID {$productId}",
                    $requestedQty,
                    $availableStock
                );
            }

            // Price from product-service is formatted as text (e.g., "450000.00"). Convert to float/number.
            $unitPrice = (float) $product['price'];
            $subtotal = $unitPrice * $requestedQty;
            $totalAmount += $subtotal;

            $orderItemsData[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'unit_price' => $unitPrice,
                'quantity' => $requestedQty,
                'subtotal' => $subtotal,
            ];
        }

        // 3. Persist order and snapshot items in a database transaction
        $order = DB::transaction(function () use ($userId, $totalAmount, $orderItemsData) {
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'PENDING',
                'total_amount' => $totalAmount,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order->load('items');
        });

        // 4. Request cart-service to empty user's cart
        try {
            $cleared = $this->cartClient->clearCart($userId);
            if (!$cleared) {
                Log::warning("Pesanan #{$order->id} berhasil dibuat namun gagal mengosongkan keranjang untuk user ID {$userId}");
            }
        } catch (Throwable $e) {
            Log::warning("Pesanan #{$order->id} berhasil dibuat namun terjadi kesalahan saat mengosongkan keranjang user ID {$userId}: {$e->getMessage()}");
        }

        return $order;
    }

    /**
     * Get details of a single order by ID, eager loading its items.
     *
     * @param int $id
     * @return Order
     * @throws OrderNotFoundException If order does not exist (404)
     */
    public function getOrder(int $id): Order
    {
        $order = Order::with('items')->find($id);

        if (!$order) {
            throw new OrderNotFoundException($id);
        }

        return $order;
    }

    /**
     * Get all orders belonging to a specific user, sorted newest first, with optional status filter.
     *
     * @param int $userId
     * @param string|null $status
     * @return Collection
     */
    public function getUserOrders(int $userId, ?string $status = null): Collection
    {
        return Order::with('items')
            ->forUser($userId)
            ->withStatus($status)
            ->get();
    }

    /**
     * Simulate paying an order: change status from PENDING to PAID.
     *
     * @param int $id
     * @return Order
     * @throws OrderNotFoundException If order does not exist (404)
     * @throws InvalidOrderStatusException If order is not in PENDING status (409)
     */
    public function payOrder(int $id): Order
    {
        $order = Order::with('items')->find($id);

        if (!$order) {
            throw new OrderNotFoundException($id);
        }

        if ($order->status !== 'PENDING') {
            throw new InvalidOrderStatusException($order->status);
        }

        $order->update([
            'status' => 'PAID',
        ]);

        return $order->fresh('items');
    }
}
