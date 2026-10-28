<?php

namespace App\Http\Controllers;

use App\Exceptions\CartEmptyException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\GetOrdersByUserRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    /**
     * Health check endpoint.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'order-service',
        ]);
    }

    /**
     * Checkout: Create an order from the user's shopping cart.
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $userId = (int) $request->validated()['user_id'];
            $order = $this->orderService->createOrder($userId);

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'data' => $order,
            ], 201);
        } catch (CartEmptyException | ProductNotFoundException | InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (ServiceUnavailableException $e) {
            return $this->serviceUnavailableResponse($e);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'membuat pesanan');
        }
    }

    /**
     * Detail of a single order with its items.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($id);

            return response()->json([
                'message' => 'Berhasil mengambil pesanan',
                'data' => $order,
            ]);
        } catch (OrderNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'mengambil pesanan');
        }
    }

    /**
     * List all orders for a specific user, sorted newest first with optional ?status=.
     */
    public function userOrders(GetOrdersByUserRequest $request, int $userId): JsonResponse
    {
        try {
            $status = $request->validated()['status'] ?? null;
            $orders = $this->orderService->getUserOrders($userId, $status);

            return response()->json([
                'message' => 'Berhasil mengambil daftar pesanan',
                'data' => $orders,
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'mengambil daftar pesanan');
        }
    }

    /**
     * Pay simulation: change status from PENDING to PAID.
     */
    public function pay(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->payOrder($id);

            return response()->json([
                'message' => 'Pesanan berhasil dibayar',
                'data' => $order,
            ]);
        } catch (OrderNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        } catch (InvalidOrderStatusException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'membayar pesanan');
        }
    }

    /**
     * Format 503 response when external services are down.
     */
    private function serviceUnavailableResponse(ServiceUnavailableException $e): JsonResponse
    {
        $body = [
            'message' => $e->getMessage(),
        ];

        if (config('app.debug')) {
            $body['error'] = $e->getMessage();
            $body['location'] = $e->getFile() . ':' . $e->getLine();
        }

        return response()->json($body, 503);
    }

    /**
     * Format 500 response for unexpected server errors.
     */
    private function errorResponse(Throwable $e, string $action): JsonResponse
    {
        Log::error("Gagal {$action}", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        $body = [
            'message' => "Terjadi kesalahan saat {$action}",
        ];

        if (config('app.debug')) {
            $body['error'] = $e->getMessage();
            $body['location'] = $e->getFile() . ':' . $e->getLine();
        }

        return response()->json($body, 500);
    }
}
