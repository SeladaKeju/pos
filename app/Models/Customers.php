<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    /** @use HasFactory<\Database\Factories\CustomersFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    public function orders()
    {
        return $this->hasMany(Orders::class, 'customer_id');
    }
}
