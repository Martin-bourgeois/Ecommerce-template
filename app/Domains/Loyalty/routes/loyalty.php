<?php

declare(strict_types=1);

use App\Domains\Loyalty\Controllers\LoyaltyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Get loyalty account
    Route::get('/loyalty/account', [LoyaltyController::class, 'getAccount'])->name('loyalty.account');

    // Get loyalty transactions
    Route::get('/loyalty/transactions', [LoyaltyController::class, 'getTransactions'])->name('loyalty.transactions');

    // Spend points
    Route::post('/loyalty/spend', [LoyaltyController::class, 'spendPoints'])->name('loyalty.spend');

    // Get summary
    Route::get('/loyalty/summary', [LoyaltyController::class, 'getSummary'])->name('loyalty.summary');
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/loyalty/top-earners', [LoyaltyController::class, 'getTopEarners'])->name('loyalty.top-earners');
});
