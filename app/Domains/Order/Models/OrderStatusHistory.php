<?php

declare(strict_types=1);

namespace App\Domains\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'user_id',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who performed the change.
     */
    public function user(): ?BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    /**
     * Get human-readable description.
     */
    public function getDescription(): string
    {
        $baseText = "{$this->from_status} → {$this->to_status}";

        if ($this->reason) {
            return "{$baseText} ({$this->reason})";
        }

        return $baseText;
    }
}
