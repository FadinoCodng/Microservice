<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
    ];

    /**
     * Attribute type casting.
     */
    protected $casts = [
        'user_id' => 'integer',
        'total_amount' => 'integer',
    ];

    /**
     * Relationship: Order has many order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope: filter orders for a specific user, sorted newest first.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->latest('id');
    }

    /**
     * Scope: optionally filter orders by status.
     */
    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if ($status !== null && $status !== '') {
            return $query->where('status', $status);
        }

        return $query;
    }
}
