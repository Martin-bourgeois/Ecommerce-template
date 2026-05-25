<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Customer\Enums\CustomerGroup;
use App\Domains\Customer\Enums\NewsletterStatus;

class CustomerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'group',
        'loyalty_points',
        'birth_date',
        'newsletter_status',
    ];

    protected $casts = [
        'loyalty_points' => 'decimal:2',
        'birth_date' => 'date',
        'group' => CustomerGroup::class,
        'newsletter_status' => NewsletterStatus::class,
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if customer is VIP.
     */
    public function isVip(): bool
    {
        return $this->group === CustomerGroup::VIP;
    }

    /**
     * Check if customer is subscribed to newsletter.
     */
    public function isSubscribedToNewsletter(): bool
    {
        return $this->newsletter_status === NewsletterStatus::SUBSCRIBED;
    }

    /**
     * Add loyalty points.
     */
    public function addLoyaltyPoints(float $points): void
    {
        $this->increment('loyalty_points', $points);
    }

    /**
     * Deduct loyalty points.
     */
    public function deductLoyaltyPoints(float $points): bool
    {
        if ($this->loyalty_points < $points) {
            return false;
        }

        $this->decrement('loyalty_points', $points);

        return true;
    }
}
