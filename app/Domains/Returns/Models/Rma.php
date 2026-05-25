<?php

declare(strict_types=1);

namespace App\Domains\Returns\Models;

use App\Enums\RmaStatus;
use App\Enums\ReturnReason;
use App\Models\User;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rma extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'user_id',
        'rma_number',
        'status',
        'reason',
        'customer_notes',
        'admin_notes',
        'approved_by',
        'refunded_at',
        'refund_amount',
    ];

    protected $casts = [
        'status' => RmaStatus::class,
        'reason' => ReturnReason::class,
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RmaItem::class);
    }

    public function scopeRequested($query)
    {
        return $query->where('status', RmaStatus::REQUESTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', RmaStatus::APPROVED);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', RmaStatus::RECEIVED);
    }

    public function scopeInspected($query)
    {
        return $query->where('status', RmaStatus::INSPECTED);
    }

    public function scopeFinalized($query)
    {
        return $query->whereIn('status', [RmaStatus::REFUNDED, RmaStatus::REJECTED]);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [RmaStatus::REFUNDED, RmaStatus::REJECTED]);
    }

    public function scopeByRmaNumber($query, string $rmaNumber)
    {
        return $query->where('rma_number', $rmaNumber);
    }

    public function scopeByStatus($query, RmaStatus $status)
    {
        return $query->where('status', $status);
    }

    public function isPending(): bool
    {
        return $this->status === RmaStatus::REQUESTED;
    }

    public function isApproved(): bool
    {
        return $this->status === RmaStatus::APPROVED;
    }

    public function isAwaitingInspection(): bool
    {
        return $this->status === RmaStatus::RECEIVED;
    }

    public function isFinalized(): bool
    {
        return $this->status->isFinalized();
    }

    public function getTotalRefundAmount(): float
    {
        return (float) $this->items()->sum('refund_amount');
    }

    public function canBeApproved(): bool
    {
        return $this->status === RmaStatus::REQUESTED;
    }

    public function canBeReceived(): bool
    {
        return $this->status === RmaStatus::APPROVED;
    }

    public function canBeInspected(): bool
    {
        return $this->status === RmaStatus::RECEIVED;
    }

    public function canBeRefunded(): bool
    {
        return $this->status === RmaStatus::INSPECTED;
    }
}
