<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    /** @use HasFactory<\Database\Factories\OrdersFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_date',
        'status',
        'total_amount',
        'is_paid',
    ];


    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
}
