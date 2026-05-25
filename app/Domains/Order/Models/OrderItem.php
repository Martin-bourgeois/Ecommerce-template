<?php

declare(strict_types=1);

namespace App\Domains\Order\Models;

use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'product_id',
        'sku',
        'name',
        'qty',
        'price_cents',
        'total_cents',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted unit price.
     */
    public function getFormattedPrice(): string
    {
        return number_format($this->price_cents / 100, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotal(): string
    {
        return number_format($this->total_cents / 100, 2, ',', ' ') . ' €';
    }

    /**
     * Get unit price in euros.
     */
    public function getPriceInEuros(): float
    {
        return $this->price_cents / 100;
    }
}
