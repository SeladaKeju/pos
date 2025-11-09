<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PaymentController;

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected Auth Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Xendit Webhook (Public - no auth required)
Route::post('/webhooks/xendit', [PaymentController::class, 'xenditCallback']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Products CRUD
    Route::apiResource('products', ProductsController::class);
    
    // Orders CRUD
    Route::apiResource('orders', OrdersController::class);
    
    // Order Extra Routes
    Route::get('/orders/{id}/items', [OrdersController::class, 'getOrderWithItems']);     // Get order with items
    Route::post('/orders/{id}/finalize', [OrdersController::class, 'finalizeOrder']);     // Finalize order
    
    // Cart Routes
    Route::prefix('cart')->group(function () {
        // Get user's active cart
        Route::get('/', [CartController::class, 'index']);
        
        // Cart Items Management
        Route::post('/items', [CartController::class, 'addItem']);           // Add item to cart
        Route::put('/items/{id}', [CartController::class, 'updateItem']);    // Update item quantity
        Route::delete('/items/{id}', [CartController::class, 'removeItem']); // Remove item from cart
        
        // Clear entire cart
        Route::delete('/', [CartController::class, 'clearCart']);
        
        // Checkout (convert cart to order)
        Route::post('/checkout', [CartController::class, 'checkout']);
    });
    
    // Payment Routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);                          // Get all payments
        Route::post('/', [PaymentController::class, 'store']);                         // Create payment
        Route::get('/{id}', [PaymentController::class, 'show']);                       // Get payment detail
        Route::get('/external/{externalId}', [PaymentController::class, 'getByExternalId']); // Get by external ID
        Route::put('/{id}/status', [PaymentController::class, 'updateStatus']);        // Update payment status
        Route::post('/{id}/refund', [PaymentController::class, 'refund']);             // Process refund
    });
});
