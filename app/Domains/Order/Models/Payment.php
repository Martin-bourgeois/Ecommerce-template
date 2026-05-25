<?php

declare(strict_types=1);

namespace App\Domains\Order\Models;

use App\Domains\Order\Enums\PaymentMethod;
use App\Domains\Order\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'payable_id',
        'payable_type',
        'method',
        'status',
        'amount_cents',
        'currency',
        'reference',
        'metadata',
        'confirmed_by',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'amount_cents' => 'integer',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the payable model (Order, etc).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the admin who confirmed the payment.
     */
    public function confirmedByUser(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by')->withoutGlobalScopes();
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Check if payment is expired (> 48 hours).
     */
    public function isExpired(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        return $this->created_at->addHours(48)->isPast();
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(User $admin): void
    {
        $this->update([
            'status' => PaymentStatus::COMPLETED,
            'confirmed_by' => $admin->id,
            'paid_at' => now(),
        ]);

        activity('payment_confirmed')
            ->causedBy($admin)
            ->performedOn($this)
            ->log("Paiement {$this->reference} confirmé");
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(string $reason = ''): void
    {
        $this->update(['status' => PaymentStatus::FAILED]);

        activity('payment_failed')
            ->performedOn($this)
            ->withProperties(['reason' => $reason])
            ->log("Paiement {$this->reference} échoué");
    }

    /**
     * Mark payment as cancelled.
     */
    public function markAsCancelled(string $reason = ''): void
    {
        $this->update([
            'status' => PaymentStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);

        activity('payment_cancelled')
            ->performedOn($this)
            ->withProperties(['reason' => $reason])
            ->log("Paiement {$this->reference} annulé: {$reason}");
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Generate unique payment reference.
     */
    public static function generateReference(int $orderId): string
    {
        return sprintf('PAY-%d-%d', $orderId, (int) (microtime(true) * 1000));
    }
}
