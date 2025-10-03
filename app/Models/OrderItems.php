<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemsFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the order that owns the item
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Get subtotal for this item
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price;
    }
}
