<?php

use App\Http\Controllers\Api\v1\ApiAuthController;
use App\Http\Controllers\Api\v1\ApiMenuController;
use App\Http\Controllers\Api\v1\ApiOrderController;
use App\Http\Controllers\Api\v1\ApiSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Versioned REST API (v1)
| Designed for Android WebView & Mobile Applications
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Settings & Meta
    Route::get('/settings', [ApiSettingsController::class, 'index']);

    // Public Menu & Categories
    Route::get('/categories', [ApiMenuController::class, 'categories']);
    Route::get('/products', [ApiMenuController::class, 'products']);
    Route::get('/products/{slug}', [ApiMenuController::class, 'productDetails']);

    // Public Order Tracking
    Route::get('/orders/track/{token}', [ApiOrderController::class, 'track']);

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [ApiAuthController::class, 'register']);
        Route::post('/login', [ApiAuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/profile', [ApiAuthController::class, 'profile']);
            Route::post('/logout', [ApiAuthController::class, 'logout']);
        });
    });

    // Authenticated Mobile Customer Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-orders', [ApiOrderController::class, 'myOrders']);
    });

    // Device Token Management (FCM Push Notifications for Android / iOS)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('devices')->group(function () {
        Route::post('/register', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
        Route::post('/remove', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'remove']);
        Route::get('/my-tokens', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'myTokens']);
    });
});
