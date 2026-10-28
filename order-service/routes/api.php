<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// GET /health (Mengecek service hidup)
Route::get('/health', [OrderController::class, 'health']);

// POST /orders (Checkout: membuat pesanan dari isi keranjang)
Route::post('/orders', [OrderController::class, 'store']);

// GET /orders/{id} (Detail satu pesanan beserta item-itemnya)
Route::get('/orders/{id}', [OrderController::class, 'show'])
    ->whereNumber('id');

// GET /orders/user/{userId} (Daftar pesanan satu user, terbaru di atas, filter opsional ?status=)
Route::get('/orders/user/{userId}', [OrderController::class, 'userOrders'])
    ->whereNumber('userId');

// PATCH /orders/{id}/pay (Bayar simulasi: PENDING menjadi PAID)
Route::patch('/orders/{id}/pay', [OrderController::class, 'pay'])
    ->whereNumber('id');
