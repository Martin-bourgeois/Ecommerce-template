<?php

declare(strict_types=1);

// Helper to get cart service
if (!function_exists('cart')) {
    function cart(): \App\Domains\Cart\Contracts\CartInterface
    {
        return app(\App\Domains\Cart\Contracts\CartInterface::class);
    }
}

// Helper to get cart count
if (!function_exists('cart_count')) {
    function cart_count(): int
    {
        return cart()->count();
    }
}

// Helper to get cart total in cents
if (!function_exists('cart_total')) {
    function cart_total(): int
    {
        return cart()->total();
    }
}

// Helper to check if cart is empty
if (!function_exists('cart_empty')) {
    function cart_empty(): bool
    {
        return cart()->isEmpty();
    }
}
