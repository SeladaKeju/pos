<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the cart
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate and update total amount
     */
    public function updateTotalAmount()
    {
        $this->total_amount = $this->cartItems()->sum('subtotal');
        $this->save();
        return $this->total_amount;
    }
}
