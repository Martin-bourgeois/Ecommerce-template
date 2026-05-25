<?php

declare(strict_types=1);

namespace App\Domains\Cart\Collections;

use App\Domains\Cart\Models\CartItem;
use Illuminate\Support\Collection;

final class CartCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    /**
     * Get cart total in cents.
     */
    public function total(): int
    {
        return $this->sum(fn (CartItem $item) => $item->getTotal());
    }

    /**
     * Get total quantity of items.
     */
    public function count(): int
    {
        return $this->sum(fn (CartItem $item) => $item->qty);
    }

    /**
     * Get unique SKU count.
     */
    public function uniqueCount(): int
    {
        return parent::count();
    }

    /**
     * Get item by SKU.
     */
    public function getItem(string $sku): ?CartItem
    {
        return $this->first(fn (CartItem $item) => $item->sku === $sku);
    }

    /**
     * Convert to array for Redis storage.
     */
    public function toRedisArray(): array
    {
        return $this->mapWithKeys(function (CartItem $item) {
            return [$item->sku => $item->toArray()];
        })->all();
    }

    /**
     * Create collection from Redis array.
     */
    public static function fromRedisArray(array $data): self
    {
        $items = collect($data)->map(fn (array $itemData) => CartItem::make(
            $itemData['sku'],
            $itemData['qty'],
            $itemData['price'],
            $itemData['name'],
            $itemData['options'] ?? [],
        ));

        return new self($items);
    }
}
