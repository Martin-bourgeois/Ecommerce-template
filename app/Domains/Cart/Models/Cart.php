<?php

declare(strict_types=1);

namespace App\Domains\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\User;

/**
 * Modèle de panier utilisateur
 */
class Cart extends Model
{
    protected $table = 'carts';
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItemModel::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items()->sum(
            DB::raw('(price * quantity)')
        );
    }

    public function getTaxAttribute(): float
    {
        return $this->subtotal * (config('shop.tax_rate') ?? 0.20);
    }

    public function getTotalAttribute(): float
    {
        $discount = $this->discount ?? 0;
        return max(0, ($this->subtotal + $this->tax) - $discount);
    }
}
