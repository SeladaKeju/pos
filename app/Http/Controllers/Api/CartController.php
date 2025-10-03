<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\CartRepositoryInterface;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Get user's active cart
     * GET /api/cart
     */
    public function index(Request $request)
    {
        try {
            $cart = $this->cartRepository->getOrCreateCart(Auth::user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to cart
     * POST /api/cart/items
     */
    public function addItem(AddToCartRequest $request)
    {
        try {
            $cart = $this->cartRepository->getOrCreateCart(Auth::user()->id);
            $cartItem = $this->cartRepository->addItem($cart->id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => $cartItem
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update cart item quantity
     * PUT /api/cart/items/{id}
     */
    public function updateItem(UpdateCartItemRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $cartItem = $this->cartRepository->updateItem($id, $validated['quantity']);

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $cartItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove item from cart
     * DELETE /api/cart/items/{id}
     */
    public function removeItem($id)
    {
        try {
            $this->cartRepository->removeItem($id);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clear cart
     * DELETE /api/cart
     */
    public function clearCart(Request $request)
    {
        try {
            $cart = $this->cartRepository->getUserCart(Auth::user()->id);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found'
                ], 404);
            }

            $this->cartRepository->clearCart($cart->id);

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Checkout cart (convert to order)
     * POST /api/cart/checkout
     */
    public function checkout(Request $request)
    {
        try {
            $cart = $this->cartRepository->getUserCart(Auth::user()->id);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found'
                ], 404);
            }

            $order = $this->cartRepository->checkout($cart->id);

            return response()->json([
                'success' => true,
                'message' => 'Checkout completed successfully',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
