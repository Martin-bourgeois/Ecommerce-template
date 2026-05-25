<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Livewire\Cart\CartPage;
use App\Livewire\Checkout\CheckoutForm;
use App\Http\Livewire\Account\ProfileForm;
use App\Http\Livewire\Account\OrdersList;
use App\Http\Livewire\Account\OrderDetail;
use App\Http\Livewire\Account\AddressesManager;
use App\Http\Livewire\Account\LoyaltyDashboard;
use App\Livewire\Admin\ReviewModeration;
use App\Livewire\Support\TicketList;
use App\Livewire\Support\TicketDetail as ClientTicketDetail;
use App\Livewire\Admin\TicketQueue;
use App\Livewire\Admin\TicketDetail as AdminTicketDetail;
use App\Livewire\Account\OrderReturns;
use App\Livewire\Account\RmaDetail;
use App\Livewire\Admin\RmaManagement;

/*
|--------------------------------------------------------------------------
| Web Routes - Public
|--------------------------------------------------------------------------
|
| Public routes accessible to all users (authenticated or not)
*/

/**
 * Homepage - Main landing page with featured products
 */
Route::get('/', HomeController::class)->name('home');

/*
|--------------------------------------------------------------------------
| Catalog Routes - Public
|--------------------------------------------------------------------------
|
| Browse products by category, search, and view product details
*/

Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('', [CatalogController::class, 'index'])->name('index');
    Route::get('{product:slug}', [CatalogController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Contact Routes - Public
|--------------------------------------------------------------------------
|
| Contact form and newsletter subscription
*/

Route::prefix('contact')->name('contact.')->group(function () {
    Route::get('', [ContactController::class, 'create'])->name('form');
    Route::post('', [ContactController::class, 'store'])->name('store');
});

/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
|
| Shopping cart management
*/

Route::post('cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('cart/count', [CartController::class, 'get'])->middleware('auth')->name('cart.count');

Route::prefix('cart')->name('cart.')->middleware('auth')->group(function () {
    Route::get('', CartPage::class)->name('index');
});

/*
|--------------------------------------------------------------------------
| Checkout Routes - Authenticated
|--------------------------------------------------------------------------
|
| Checkout workflow (3 steps: address, shipping/payment, review)
| Requires authentication
*/

Route::prefix('checkout')->name('checkout.')->middleware('auth')->group(function () {
    Route::get('', CheckoutForm::class)->name('index');
});

/*
|--------------------------------------------------------------------------
| Customer Account Routes - Authenticated
|--------------------------------------------------------------------------
|
| User profile, orders, addresses, loyalty, support, and returns
| All routes require authentication
*/

Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
    // Profile management
    Route::get('profile', ProfileForm::class)->name('profile');

    // Orders and order details
    Route::get('orders', OrdersList::class)->name('orders');
    Route::get('orders/{order}', OrderDetail::class)->name('order-detail');
    Route::get('orders/{order}/download-proof', [App\Http\Controllers\OrderController::class, 'downloadProof'])->name('order.download-proof');

    // Address management
    Route::get('addresses', AddressesManager::class)->name('addresses');

    // Returns and RMA (Return Merchandise Authorization)
    Route::get('returns', OrderReturns::class)->name('returns');
    Route::get('rma/{rma}', RmaDetail::class)->name('rma.show');

    // Loyalty program dashboard
    Route::get('loyalty', LoyaltyDashboard::class)->name('loyalty');

    // Support tickets (customer view)
    Route::get('support/tickets', TicketList::class)->name('support.tickets');
    Route::get('support/tickets/{ticket}', ClientTicketDetail::class)->name('support.show');
});

/*
|--------------------------------------------------------------------------
| Admin Routes - Admin/Staff Only
|--------------------------------------------------------------------------
|
| Administrative functions for moderating content, managing support tickets,
| and processing returns. Requires 'admin' or 'staff' role.
*/

Route::middleware(['auth', 'role:admin|staff'])->prefix('admin')->name('admin.')->group(function () {
    // Product review moderation
    Route::get('reviews/moderation', ReviewModeration::class)->name('reviews.moderation');

    // Support ticket queue and detail view for staff
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('queue', TicketQueue::class)->name('queue');
        Route::get('tickets/{ticket}', AdminTicketDetail::class)->name('ticket.show');
    });

    // Returns (RMA) management queue for staff
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('queue', RmaManagement::class)->name('queue');
    });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Login, registration, password reset routes
| Handled by Filament Admin Panel
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Fallback 404 Route
|--------------------------------------------------------------------------
|
| Catch all undefined routes and display 404 error page
*/

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
