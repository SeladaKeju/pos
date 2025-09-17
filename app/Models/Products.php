<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    /** @use HasFactory<\Database\Factories\ProductsFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'sku',
        'is_active',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'product_id');
    }
}
