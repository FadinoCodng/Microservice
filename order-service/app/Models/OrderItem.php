<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    /**
     * Hidden attributes for serialization to match the UTS API response specification.
     */
    protected $hidden = [
        'id',
        'order_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Attribute type casting.
     */
    protected $casts = [
        'product_id' => 'integer',
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'subtotal' => 'integer',
    ];

    /**
     * Relationship: OrderItem belongs to an Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
