<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Orders;
use App\Models\OrderItems;
use App\Models\Products;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function getAllOrders()
    {
        return Orders::with(['user', 'orderItems.product'])->get();
    }

    public function getOrderById(int $orderId)
    {
        return Orders::with(['user', 'orderItems.product'])->find($orderId);
    }

    public function createOrder(array $orderData)
    {
        return DB::transaction(function () use ($orderData) {
            // Generate order number if not provided
            if (empty($orderData['order_number'])) {
                $orderData['order_number'] = 'ORD-' . time() . '-' . rand(1000, 9999);
            }

            // Set default values
            $orderData['order_date'] = $orderData['order_date'] ?? now();
            $orderData['status'] = $orderData['status'] ?? 'pending';
            $orderData['is_paid'] = $orderData['is_paid'] ?? false;
            $orderData['total_amount'] = $orderData['total_amount'] ?? 0;

            // Create order
            $order = Orders::create($orderData);

            // Create order items if provided
            if (isset($orderData['items']) && is_array($orderData['items'])) {
                foreach ($orderData['items'] as $item) {
                    $product = Products::find($item['product_id']);
                    
                    if (!$product) {
                        throw new \Exception("Product not found: {$item['product_id']}");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }

                    OrderItems::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'] ?? $product->price,
                    ]);

                    // Update stock
                    $product->decrement('stock', $item['quantity']);
                }

                // Recalculate total
                $this->recalcTotals($order->id);
            }

            return $order->load(['orderItems.product', 'user']);
        });
    }

    public function updateOrder(int $orderId, array $orderData)
    {
        $order = Orders::find($orderId);

        if ($order) {
            $order->update($orderData);
            return $order->load(['orderItems.product', 'user']);
        }

        return null;
    }

    public function deleteOrder(int $orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $order = Orders::with('orderItems')->find($orderId);

            if ($order) {
                // Restore stock for each item
                foreach ($order->orderItems as $item) {
                    $product = Products::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                // Delete order items first
                $order->orderItems()->delete();
                
                // Delete order
                return $order->delete();
            }

            return false;
        });
    }

    public function getOrderWithItems(int $orderId)
    {
        return Orders::with(['user', 'orderItems.product'])->find($orderId);
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
            $order->status = 'completed';
            $order->is_paid = true;
            $order->save();
        }
    }
}