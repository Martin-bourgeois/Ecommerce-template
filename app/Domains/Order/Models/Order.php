<?php

declare(strict_types=1);

namespace App\Domains\Order\Models;

use App\Domains\Checkout\Models\Address;
use App\Domains\Order\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'street',
        'city',
        'postal_code',
        'country',
        'shipping_method',
        'payment_method',
        'paypal_proof_path',
        'paypal_transaction_id',
        'payment_verified_at',
        'subtotal_cents',
        'shipping_cents',
        'tax_cents',
        'total_cents',
        'shipping_address_id',
        'billing_address_id',
        'notes',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_verified_at' => 'datetime',
    ];

    // ===== Relations =====

    /**
     * Get the user who placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the shipping address.
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Get the billing address.
     */
    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    /**
     * Get the payments for this order.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get the primary payment (latest).
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')->latest();
    }

    /**
     * Get status history.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    // ===== Scopes =====

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING_PAYMENT->value);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PROCESSING->value);
    }

    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::SHIPPED->value);
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::DELIVERED->value);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::COMPLETED->value);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::CANCELLED->value);
    }

    // ===== State Machine Methods =====

    /**
     * Check if order can transition to new status.
     */
    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    /**
     * Get allowed next statuses.
     */
    public function getAllowedTransitions(): array
    {
        return $this->status->getAllowedTransitions();
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING_PAYMENT,
            OrderStatus::PROCESSING,
        ]);
    }

    /**
     * Check if order is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === OrderStatus::DELIVERED;
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::COMPLETED;
    }

    /**
     * Record a status change.
     */
    public function recordStatusChange(OrderStatus $newStatus, ?User $user = null, ?string $reason = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $this->id,
            'from_status' => $this->status->value,
            'to_status' => $newStatus->value,
            'user_id' => $user?->id,
            'reason' => $reason,
        ]);

        $this->update(['status' => $newStatus]);
    }

    // ===== Formatting =====

    /**
     * Get formatted total.
     */
    public function getTotalFormatted(): string
    {
        return number_format($this->total_cents / 100, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted subtotal.
     */
    public function getSubtotalFormatted(): string
    {
        return number_format($this->subtotal_cents / 100, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted shipping cost.
     */
    public function getShippingFormatted(): string
    {
        return number_format($this->shipping_cents / 100, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted tax.
     */
    public function getTaxFormatted(): string
    {
        return number_format($this->tax_cents / 100, 2, ',', ' ') . ' €';
    }
}
