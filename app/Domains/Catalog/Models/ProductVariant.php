<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'cost',
        'stock',
        'reserved_stock',
        'weight',
        'barcode',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'cost' => 'integer',
        'stock' => 'integer',
        'reserved_stock' => 'integer',
        'weight' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get attribute values.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_variant_id');
    }

    /**
     * Scope: Active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get available stock (stock minus reserved).
     */
    public function getAvailableStock(): int
    {
        return max(0, $this->stock - $this->reserved_stock);
    }

    /**
     * Check if variant is in stock.
     */
    public function isInStock(): bool
    {
        return $this->getAvailableStock() > 0;
    }

    /**
     * Reserve stock.
     */
    public function reserveStock(int $quantity): bool
    {
        if ($this->getAvailableStock() < $quantity) {
            return false;
        }

        $this->reserved_stock += $quantity;
        $this->save();

        return true;
    }

    /**
     * Release reserved stock.
     */
    public function releaseStock(int $quantity): void
    {
        $this->reserved_stock = max(0, $this->reserved_stock - $quantity);
        $this->save();
    }

    /**
     * Deduct stock.
     */
    public function deductStock(int $quantity): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }

        $this->stock -= $quantity;
        $this->reserved_stock = max(0, $this->reserved_stock - $quantity);
        $this->save();

        return true;
    }

    /**
     * Get attribute values with details.
     */
    public function getAttributeValuesPair(): array
    {
        return $this->attributeValues()
            ->with(['attribute', 'attributeOption'])
            ->get()
            ->mapWithKeys(fn ($av) => [
                $av->attribute->slug => $av->attributeOption?->label ?? $av->text_value ?? $av->boolean_value,
            ])
            ->toArray();
    }
}
