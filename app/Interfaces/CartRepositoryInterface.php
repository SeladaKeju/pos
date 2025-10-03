<?php

namespace App\Interfaces;

interface CartRepositoryInterface
{
    public function getUserCart(int $userId);
    public function getOrCreateCart(int $userId);
    public function addItem(int $cartId, array $itemData);
    public function updateItem(int $cartItemId, int $quantity);
    public function removeItem(int $cartItemId);
    public function clearCart(int $cartId);
    public function getCartItems(int $cartId);
    public function checkout(int $cartId);
}
