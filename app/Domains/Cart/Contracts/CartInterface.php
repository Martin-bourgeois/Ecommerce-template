<?php

declare(strict_types=1);

namespace App\Domains\Cart\Contracts;

use App\Domains\Cart\Collections\CartCollection;
use Money\Money;

interface CartInterface
{
    /**
     * Add item to cart (merges if exists).
     */
    public function add(string $sku, int $qty, array $options = []): void;

    /**
     * Update item quantity in cart.
     */
    public function update(string $sku, int $qty): void;

    /**
     * Remove item from cart.
     */
    public function remove(string $sku): void;

    /**
     * Clear entire cart.
     */
    public function clear(): void;

    /**
     * Get cart contents as collection.
     */
    public function getContent(): CartCollection;

    /**
     * Merge guest cart into user cart.
     * Quantites are summed; conflicting skus keep max quantity.
     */
    public function merge(string $fromSessionId, int $userId): void;

    /**
     * Calculate cart total in cents.
     */
    public function total(): int;

    /**
     * Get total item count.
     */
    public function count(): int;

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool;
}
