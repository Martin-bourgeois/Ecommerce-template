<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Models;

use App\Domains\Order\Models\Order;
use App\Domains\Loyalty\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    protected $table = 'loyalty_transactions';

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'type',
        'points',
        'description',
        'order_id',
        'created_at',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'created_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class, 'account_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get description with type label
     */
    public function getFullDescription(): string
    {
        $typeLabel = $this->type->label();
        $pointsText = abs($this->points) . ' points';

        if ($this->description) {
            return "{$typeLabel}: {$this->description} ({$pointsText})";
        }

        return "{$typeLabel} ({$pointsText})";
    }

    /**
     * Get sign prefix for display
     */
    public function getSignPrefix(): string
    {
        if ($this->type->isCredit()) {
            return '+';
        }

        return '-';
    }
}
