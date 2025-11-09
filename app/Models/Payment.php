<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'external_id',
        'xendit_invoice_id',
        'invoice_url',
        'amount',
        'status',
        'xendit_response',
        'callback_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'xendit_response' => 'array',
        'callback_data' => 'array',
    ];

    /**
     * Get the order that owns the payment
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if payment is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    /**
     * Check if payment is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'EXPIRED';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'PAID',
        ]);

        // Update order status
        if ($this->order) {
            $this->order->update([
                'is_paid' => true,
                'status' => 'completed',
            ]);
        }

        return $this;
    }

    /**
     * Mark payment as expired
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => 'EXPIRED',
        ]);

        return $this;
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => 'FAILED',
        ]);

        return $this;
    }

    /**
     * Mark payment as cancelled
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => 'CANCELLED',
        ]);

        return $this;
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope for paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'PAID');
    }

    /**
     * Scope for expired payments
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'EXPIRED');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }
}
