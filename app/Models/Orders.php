<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    /** @use HasFactory<\Database\Factories\OrdersFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'order_date',
        'status',
        'total_amount',
        'is_paid',
        'user_id',
        'cart_id', // Tambahan untuk track cart asal
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    /**
     * Get the user that owns the order
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the cart that created this order
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    /**
     * Get the order items
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    /**
     * Get the payment for this order
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    /**
     * Calculate and update total amount from order items
     */
    public function updateTotalAmount()
    {
        $this->total_amount = $this->orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        $this->save();
        return $this->total_amount;
    }
}
