<?php

namespace App\Http\Controllers;

use App\Exceptions\CartEmptyException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\CreateOrderRequest;
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
     * Checkout: Create an order from the user's shopping cart.
     *
     * @param CreateOrderRequest $request
     * @return JsonResponse
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
            $body = [
                'message' => $e->getMessage(),
            ];

            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
                $body['location'] = $e->getFile() . ':' . $e->getLine();
            }

            return response()->json($body, 503);
        } catch (Throwable $e) {
            Log::error('Gagal membuat pesanan', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $body = [
                'message' => 'Terjadi kesalahan saat memproses pesanan',
            ];

            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
                $body['location'] = $e->getFile() . ':' . $e->getLine();
            }

            return response()->json($body, 500);
        }
    }
}
