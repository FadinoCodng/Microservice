<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// POST /orders (Checkout: membuat pesanan dari isi keranjang)
Route::post('/orders', [OrderController::class, 'store']);
