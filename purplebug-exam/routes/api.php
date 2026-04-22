<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Public product listing (guests can browse)
Route::get('/products', [ProductController::class, 'index']);

// Authenticated routes
Route::middleware(['auth:sanctum', 'inactivity'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Cart (guest users)
    Route::get('/cart',              [CartController::class, 'index']);
    Route::post('/cart',             [CartController::class, 'add']);
    Route::delete('/cart/{cart}',    [CartController::class, 'remove']);
    Route::post('/checkout',         [OrderController::class, 'checkout']);
    Route::get('/my-orders',         [OrderController::class, 'myOrders']);

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // User management
        Route::get('/users',               [UserController::class, 'index']);
        Route::post('/users',              [UserController::class, 'store']);
        Route::put('/users/{user}',        [UserController::class, 'update']);
        Route::delete('/users/{user}',     [UserController::class, 'destroy']);
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);

        // Product management
        Route::post('/products',           [ProductController::class, 'store']);
        Route::put('/products/{product}',  [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Order management
        Route::get('/orders',              [OrderController::class, 'allOrders']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);

        // Logs
        Route::get('/logs', function () {
            return response()->json(\App\Models\ActivityLog::with('user')->latest()->get());
        });
    });
});
