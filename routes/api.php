<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\AccountController;
use App\Http\Api\V1\Controllers\AuthController;
use App\Http\Api\V1\Controllers\CartController;
use App\Http\Api\V1\Controllers\OrderController;
use App\Http\Api\V1\Controllers\ProductController;
use App\Http\Api\V1\Middleware\EnsureApiToken;
use App\Http\Api\V1\Middleware\ThrottleApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoints pour l'API REST mobile
| Version: v1
| Préfixe: /api/v1/
|
*/

Route::prefix('api/v1')->middleware([ThrottleApi::class])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Authentication Routes (Public)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('register', [AuthController::class, 'register'])->name('register');

        // Routes protégées
        Route::middleware(['auth:sanctum', EnsureApiToken::class])->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Product Routes (Public)
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('featured', [ProductController::class, 'featured'])->name('featured');
        Route::get('{product}', [ProductController::class, 'show'])->name('show');
        Route::get('{product}/related', [ProductController::class, 'related'])->name('related');
    });

    /*
    |--------------------------------------------------------------------------
    | Cart Routes (Protected)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', EnsureApiToken::class])
        ->prefix('cart')
        ->name('cart.')
        ->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('add', [CartController::class, 'add'])->name('add');
            Route::patch('items/{product_id}', [CartController::class, 'update'])->name('update');
            Route::delete('items/{product_id}', [CartController::class, 'remove'])->name('remove');
            Route::delete('/', [CartController::class, 'clear'])->name('clear');
            Route::post('coupon', [CartController::class, 'applyCoupon'])->name('coupon');
        });

    /*
    |--------------------------------------------------------------------------
    | Order Routes (Protected)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', EnsureApiToken::class])
        ->prefix('orders')
        ->name('orders.')
        ->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('{order}', [OrderController::class, 'show'])->name('show');
            Route::post('{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::get('{order}/tracking', [OrderController::class, 'tracking'])->name('tracking');
        });

    /*
    |--------------------------------------------------------------------------
    | Account Routes (Protected)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', EnsureApiToken::class])
        ->prefix('account')
        ->name('account.')
        ->group(function () {
            // Profile
            Route::get('profile', [AccountController::class, 'profile'])->name('profile');
            Route::patch('profile', [AccountController::class, 'updateProfile'])->name('update-profile');

            // Addresses
            Route::get('addresses', [AccountController::class, 'addresses'])->name('addresses');
            Route::post('addresses', [AccountController::class, 'createAddress'])->name('create-address');
            Route::patch('addresses/{address}', [AccountController::class, 'updateAddress'])->name('update-address');
            Route::delete('addresses/{address}', [AccountController::class, 'deleteAddress'])->name('delete-address');

            // Loyalty
            Route::get('loyalty', [AccountController::class, 'loyalty'])->name('loyalty');
            Route::get('loyalty/history', [AccountController::class, 'loyaltyHistory'])->name('loyalty-history');
        });

    /*
    |--------------------------------------------------------------------------
    | Health Check (Public)
    |--------------------------------------------------------------------------
    */
    Route::get('health', function () {
        return response()->json(['status' => 'ok'], 200);
    })->name('health');

    /*
    |--------------------------------------------------------------------------
    | 404 Fallback
    |--------------------------------------------------------------------------
    */
    Route::fallback(function () {
        return response()->json([
            'message' => 'Not found',
            'errors' => [
                'endpoint' => ['L\'endpoint demandé n\'existe pas'],
            ],
        ], 404);
    });
});
