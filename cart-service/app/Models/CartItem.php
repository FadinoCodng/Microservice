<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    // Kolom yang boleh diisi massal lewat create() atau firstOrNew().
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    // Scope untuk mengambil item milik user tertentu,
    // dengan item terbaru ditampilkan lebih dulu.
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->latest();
    }
}
