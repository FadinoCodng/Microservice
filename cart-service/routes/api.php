<?php
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

// GET /carts/{userId}
Route::get('/carts/{userId}', [CartController::class, 'show'])
    ->whereNumber('userId');

// POST /carts
Route::post('/carts', [CartController::class, 'store']);

// PUT /carts/items/{id}
Route::put('/carts/items/{id}', [CartController::class, 'update'])
    ->whereNumber('id');

// DELETE /carts/items/{id}
Route::delete('/carts/items/{id}', [CartController::class, 'destroy'])
    ->whereNumber('id');

// DELETE /carts/{userId}
Route::delete('/carts/{userId}', [CartController::class, 'clear'])
        ->whereNumber('userId');