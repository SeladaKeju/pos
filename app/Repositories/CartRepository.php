<?php

namespace App\Repositories;

use App\Interfaces\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Products;
use App\Models\Orders;
use App\Models\OrderItems;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function getUserCart(int $userId)
    {
        return Cart::with(['cartItems.product'])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    public function getOrCreateCart(int $userId)
    {
        $cart = $this->getUserCart($userId);
        
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'status' => 'active',
                'total_amount' => 0,
            ]);
        }

        return $cart->load(['cartItems.product']);
    }

    public function addItem(int $cartId, array $itemData)
    {
        $product = Products::find($itemData['product_id']);
        
        if (!$product) {
            throw new \Exception('Product not found');
        }

        if (!$product->is_active) {
            throw new \Exception('Product is not active');
        }

        if ($product->stock < $itemData['quantity']) {
            throw new \Exception("Insufficient stock. Available: {$product->stock}");
        }

        // Check if item already exists in cart
        $cartItem = CartItem::where('cart_id', $cartId)
            ->where('product_id', $itemData['product_id'])
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $itemData['quantity'];
            
            if ($product->stock < $newQuantity) {
                throw new \Exception("Insufficient stock. Available: {$product->stock}");
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->calculateSubtotal();
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cartId,
                'product_id' => $product->id,
                'quantity' => $itemData['quantity'],
                'price' => $product->price,
                'subtotal' => $product->price * $itemData['quantity'],
            ]);
        }

        // Update cart total
        $cart = Cart::find($cartId);
        $cart->updateTotalAmount();

        return $cartItem->load('product');
    }

    public function updateItem(int $cartItemId, int $quantity)
    {
        $cartItem = CartItem::find($cartItemId);
        
        if (!$cartItem) {
            throw new \Exception('Cart item not found');
        }

        $product = $cartItem->product;

        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        if ($product->stock < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$product->stock}");
        }

        $cartItem->quantity = $quantity;
        $cartItem->calculateSubtotal();

        // Update cart total
        $cartItem->cart->updateTotalAmount();

        return $cartItem->load('product');
    }

    public function removeItem(int $cartItemId)
    {
        $cartItem = CartItem::find($cartItemId);
        
        if (!$cartItem) {
            throw new \Exception('Cart item not found');
        }

        $cart = $cartItem->cart;
        $cartItem->delete();

        // Update cart total
        $cart->updateTotalAmount();

        return true;
    }

    public function clearCart(int $cartId)
    {
        $cart = Cart::find($cartId);
        
        if (!$cart) {
            throw new \Exception('Cart not found');
        }

        $cart->cartItems()->delete();
        $cart->total_amount = 0;
        $cart->save();

        return true;
    }

    public function getCartItems(int $cartId)
    {
        return CartItem::with('product')
            ->where('cart_id', $cartId)
            ->get();
    }

    public function checkout(int $cartId)
    {
        return DB::transaction(function () use ($cartId) {
            $cart = Cart::with(['cartItems.product', 'user'])->find($cartId);

            if (!$cart) {
                throw new \Exception('Cart not found');
            }

            if ($cart->cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            // Validate stock
            foreach ($cart->cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$item->product->name}");
                }
            }

            // Create order
            $order = Orders::create([
                'user_id' => $cart->user_id,
                'order_number' => 'ORD-' . time() . '-' . $cart->id,
                'order_date' => now(),
                'status' => 'completed',
                'total_amount' => $cart->total_amount,
                'is_paid' => true,
            ]);

            // Create order items and update stock
            foreach ($cart->cartItems as $item) {
                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                // Update stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Mark cart as completed and clear items
            $cart->status = 'completed';
            $cart->save();
            $cart->cartItems()->delete();

            return $order->load(['orderItems.product', 'user']);
        });
    }
}
