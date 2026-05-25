<?php

declare(strict_types=1);

namespace App\Domains\Cart\Providers;

use App\Domains\Cart\Contracts\CartInterface;
use App\Domains\Cart\Services\CartService;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartInterface::class, CartService::class);
        $this->app->singleton(CartService::class, fn () => new CartService());
    }

    public function boot(): void
    {
        // Register Livewire components
        \Livewire\Livewire::component('cart.mini-cart', \App\Livewire\Cart\MiniCart::class);
        \Livewire\Livewire::component('cart.cart-page', \App\Livewire\Cart\CartPage::class);
        \Livewire\Livewire::component('cart.add-to-cart', \App\Livewire\Cart\AddToCart::class);
        
        // Register checkout components
        \Livewire\Livewire::component('checkout.checkout-form', \App\Livewire\Checkout\CheckoutForm::class);
    }
}
