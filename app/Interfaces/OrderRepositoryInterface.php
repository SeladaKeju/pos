<?php

namespace App\Interfaces;

interface OrderRepositoryInterface
{
    public function getAllOrders();
    public function getOrderById(int $orderId);
    public function createOrder(array $orderData);
    public function updateOrder(int $orderId, array $orderData);
    public function deleteOrder(int $orderId);

    public function getOrderWithItems(int $orderId);
    public function recalcTotals(int $orderId): void;  // hitung dari order_items
    public function finalize(int $orderId): void;      // status=paid, paid_at=now
}