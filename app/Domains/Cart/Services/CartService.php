<?php

declare(strict_types=1);

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Collections\CartCollection;
use App\Domains\Cart\Contracts\CartInterface;
use App\Domains\Cart\Models\CartItem;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class CartService implements CartInterface
{
    private const GUEST_CART_TTL = 30 * 24 * 60 * 60; // 30 days
    private const GUEST_PREFIX = 'cart:guest:';
    private const USER_PREFIX = 'cart:user:';

    private ?string $guestSessionId = null;
    private ?int $userId = null;

    public function __construct()
    {
        $this->guestSessionId = Session::getId();
    }

    public function setUser(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * Add item to cart.
     * If item exists, increment quantity.
     */
    public function add(string $sku, int $qty, array $options = []): void
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        // Verify stock
        $this->verifyStock($sku, $qty);

        $cart = $this->getCartData();
        $key = $this->getCacheKey();

        if (isset($cart[$sku])) {
            $existing = CartItem::make(
                $cart[$sku]['sku'],
                $cart[$sku]['qty'],
                $cart[$sku]['price'],
                $cart[$sku]['name'],
                $cart[$sku]['options'] ?? [],
            );

            // Verify total stock
            $newQty = $existing->qty + $qty;
            $this->verifyStock($sku, $newQty);

            $cart[$sku]['qty'] = $newQty;
        } else {
            // Get product details
            $product = $this->getProductBySku($sku);

            $cart[$sku] = [
                'sku' => $sku,
                'qty' => $qty,
                'price' => $product->getPrice(), // in cents
                'name' => $product->name,
                'options' => $options,
            ];
        }

        Cache::put($key, json_encode($cart), now()->addSeconds(self::GUEST_CART_TTL));

        event(new \App\Domains\Cart\Events\CartUpdated($this->guestSessionId, $this->userId));
    }

    /**
     * Update item quantity.
     */
    public function update(string $sku, int $qty): void
    {
        if ($qty <= 0) {
            $this->remove($sku);
            return;
        }

        // Verify stock
        $this->verifyStock($sku, $qty);

        $cart = $this->getCartData();
        $key = $this->getCacheKey();

        if (isset($cart[$sku])) {
            $cart[$sku]['qty'] = $qty;
            Cache::put($key, json_encode($cart), now()->addSeconds(self::GUEST_CART_TTL));

            event(new \App\Domains\Cart\Events\CartUpdated($this->guestSessionId, $this->userId));
        }
    }

    /**
     * Remove item from cart.
     */
    public function remove(string $sku): void
    {
        $cart = $this->getCartData();
        $key = $this->getCacheKey();

        if (isset($cart[$sku])) {
            unset($cart[$sku]);

            if (empty($cart)) {
                Cache::forget($key);
            } else {
                Cache::put($key, json_encode($cart), now()->addSeconds(self::GUEST_CART_TTL));
            }

            event(new \App\Domains\Cart\Events\CartUpdated($this->guestSessionId, $this->userId));
        }
    }

    /**
     * Clear entire cart.
     */
    public function clear(): void
    {
        $key = $this->getCacheKey();
        Cache::forget($key);

        event(new \App\Domains\Cart\Events\CartUpdated($this->guestSessionId, $this->userId));
    }

    /**
     * Get cart contents.
     */
    public function getContent(): CartCollection
    {
        $data = $this->getCartData();
        return CartCollection::fromRedisArray($data);
    }

    /**
     * Merge guest cart into user cart.
     */
    public function merge(string $fromSessionId, int $userId): void
    {
        $guestKey = self::GUEST_PREFIX . $fromSessionId;
        $userKey = self::USER_PREFIX . $userId;

        $guestCart = $this->getRedisData($guestKey);
        $userCart = $this->getRedisData($userKey);

        if (empty($guestCart)) {
            return; // Nothing to merge
        }

        // Merge: sum quantities, keep max if conflict
        foreach ($guestCart as $sku => $item) {
            if (isset($userCart[$sku])) {
                $userCart[$sku]['qty'] = max($userCart[$sku]['qty'], $item['qty']);
            } else {
                $userCart[$sku] = $item;
            }
        }

        Cache::put($userKey, json_encode($userCart), now()->addSeconds(self::GUEST_CART_TTL));
        Cache::forget($guestKey); // Clean up guest cart

        event(new \App\Domains\Cart\Events\CartMerged($userId, count($guestCart)));
    }

    /**
     * Get cart total in cents.
     */
    public function total(): int
    {
        return $this->getContent()->total();
    }

    /**
     * Get item count (sum of quantities).
     */
    public function count(): int
    {
        return $this->getContent()->count();
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->getContent()->isEmpty();
    }

    /**
     * Get cache key for current user/session.
     */
    private function getCacheKey(): string
    {
        if ($this->userId) {
            return self::USER_PREFIX . $this->userId;
        }

        return self::GUEST_PREFIX . $this->guestSessionId;
    }

    /**
     * Get cart data from Redis.
     */
    private function getCartData(): array
    {
        return $this->getRedisData($this->getCacheKey());
    }

    /**
     * Get data from Redis key.
     */
    private function getRedisData(string $key): array
    {
        $data = Cache::get($key);
        if (!$data) {
            return [];
        }

        return json_decode($data, true) ?? [];
    }

    /**
     * Verify product stock.
     */
    private function verifyStock(string $sku, int $qty): void
    {
        $product = $this->getProductBySku($sku);

        if ($product->type === 'simple') {
            if (!$product->isInStock() || $product->getTotalStock() < $qty) {
                throw new \InvalidArgumentException("Insufficient stock for SKU: {$sku}");
            }
        } else {
            // For configurable products, check if variants have stock
            if (!$product->activeVariants()->where('stock', '>=', $qty)->exists()) {
                throw new \InvalidArgumentException("Insufficient stock for SKU: {$sku}");
            }
        }
    }

    /**
     * Get product by SKU.
     */
    private function getProductBySku(string $sku): Product
    {
        $product = Product::where('sku', $sku)
            ->where('status', 'published')
            ->with('activeVariants')
            ->firstOrFail();

        return $product;
    }
}
