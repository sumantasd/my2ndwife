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
    Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
        Route::get('/my-orders', [ApiOrderController::class, 'myOrders']);
        Route::post('/account/delete-request', [\App\Http\Controllers\Api\v1\ApiAccountDeletionController::class, 'submit']);
        Route::get('/account/delete-request', [\App\Http\Controllers\Api\v1\ApiAccountDeletionController::class, 'status']);
    });

    // Device Token Management (FCM Push Notifications for Android / iOS)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/devices/register', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
        Route::post('/devices/remove', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'remove']);
        Route::get('/devices/my-tokens', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'myTokens']);

        // Singular & direct aliases to prevent 404 with Android Retrofit integrations
        Route::post('/device/register', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
        Route::post('/device-token', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
        Route::post('/device-tokens', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
    });
});

// Non-versioned fallbacks for mobile apps configuring baseUrl ending in /api/
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/devices/register', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
    Route::post('/devices/remove', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'remove']);
    Route::get('/devices/my-tokens', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'myTokens']);
    Route::post('/device/register', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
    Route::post('/device-token', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
    Route::post('/device-tokens', [\App\Http\Controllers\Api\v1\ApiDeviceTokenController::class, 'register']);
    Route::post('/account/delete-request', [\App\Http\Controllers\Api\v1\ApiAccountDeletionController::class, 'submit']);
    Route::get('/account/delete-request', [\App\Http\Controllers\Api\v1\ApiAccountDeletionController::class, 'status']);
});
