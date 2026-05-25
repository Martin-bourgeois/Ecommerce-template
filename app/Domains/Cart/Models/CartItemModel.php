<?php

declare(strict_types=1);

namespace App\Domains\Cart\Models;

use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItemModel extends Model
{
    use HasFactory;

    protected $table = 'cart_items';
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price_cents', 'options'];
    protected $casts = [
        'options' => 'array',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
