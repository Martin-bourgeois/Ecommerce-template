<?php

declare(strict_types=1);

namespace App\Domains\Returns\Models;

use App\Enums\ItemCondition;
use App\Domains\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmaItem extends Model
{
    protected $fillable = [
        'rma_id',
        'order_item_id',
        'quantity',
        'condition',
        'refund_amount',
        'notes',
    ];

    protected $casts = [
        'condition' => ItemCondition::class,
        'refund_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rma(): BelongsTo
    {
        return $this->belongsTo(Rma::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
