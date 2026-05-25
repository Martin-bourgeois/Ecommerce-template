<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\Api\PushSubscriptionController;

// Routes publiques PWA
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');
Route::get('/ping', [PwaController::class, 'ping'])->name('pwa.ping');

// Routes API protégées (authentifiées)
Route::middleware('auth:sanctum')->group(function () {
    // Push Notifications
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::get('/push/subscriptions', [PushSubscriptionController::class, 'getSubscriptions'])->name('push.subscriptions');
    Route::post('/push/test', [PushSubscriptionController::class, 'testNotification'])->name('push.test');
    Route::get('/push/pending', [PushSubscriptionController::class, 'getPending'])->name('push.pending');
    Route::post('/push/action', [PushSubscriptionController::class, 'handleAction'])->name('push.action');

    // Panier offline
    Route::post('/cart/add', function () {
        // La vraie logique sera implémentée dans le contrôleur du panier
        return response()->json(['success' => true], 201);
    })->name('cart.add');

    // Notifications en attente
    Route::get('/notifications/pending', function () {
        return response()->json(['notifications' => []]);
    })->name('notifications.pending');
});
