<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password reset routes
Route::post('/password/email', [\App\Http\Controllers\API\PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [\App\Http\Controllers\API\PasswordResetController::class, 'reset']);

// Temporarily public for testing (move back to protected later)
Route::get('/user', [AuthController::class, 'user']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::put('/user/profile', [AuthController::class, 'updateProfile']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes (commented out for testing)
    // Route::get('/user', [AuthController::class, 'user']);
    // Route::post('/logout', [AuthController::class, 'logout']);
    // Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Product routes - accessible by both admin and cashier
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/low-stock', [StockController::class, 'lowStock']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        // Product management (admin only)
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Stock management (admin only)
        Route::put('/products/{id}/stock', [StockController::class, 'updateStock']);
        Route::put('/products/{id}/stock/increase', [StockController::class, 'increaseStock']);
        Route::put('/products/{id}/stock/decrease', [StockController::class, 'decreaseStock']);
    });

    // Cashier routes
    Route::middleware('role:cashier')->group(function () {
        // Stock management (cashier can only decrease stock during sales)
        Route::put('/products/{id}/stock/decrease', [StockController::class, 'decreaseStock']);
    });
});
