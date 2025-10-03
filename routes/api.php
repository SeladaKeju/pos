<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;

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

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Products CRUD
    Route::apiResource('products', ProductsController::class);
    
    // Orders CRUD
    Route::apiResource('orders', OrdersController::class);
    
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
});
