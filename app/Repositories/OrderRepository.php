<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Orders; 

class OrderRepository implements OrderRepositoryInterface
{
    public function getAllOrders()
    {
        return Orders::with('customer')->get();
    }

    public function getOrderById(int $orderId)
    {
        return Orders::with('customer')->find($orderId);
    }

    public function createOrder(array $orderData)
    {
        return Orders::create($orderData);
    }

    public function updateOrder(int $orderId, array $orderData)
    {
        $order = Orders::find($orderId);

        if ($order) {
            $order->update($orderData);
            return $order;
        }

        return null;
    }

    public function deleteOrder(int $orderId)
    {
        $order = Orders::find($orderId);

        if ($order) {
            return $order->delete();
        }

        return false;
    }

    public function getOrderWithItems(int $orderId)
    {
        return Orders::with(['customer', 'orderItems.product'])->find($orderId);
    }

    public function recalcTotals(int $orderId): void
    {
        $order = Orders::with('orderItems')->find($orderId);
        if ($order) {
            $totalAmount = $order->orderItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            $order->total_amount = $totalAmount;
            $order->save();
        }
    }

    public function finalize(int $orderId): void
    {
        $order = Orders::find($orderId);
        if ($order) {
            $order->status = 'paid';
            $order->paid_at = now();
            $order->save();
        }
    }
}