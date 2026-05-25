<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Domains\Customer\Enums\UserStatus;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    protected static string $guard_name = 'web';

    /**
     * Get the customer profile for this user.
     */
    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    /**
     * Get the customer addresses for this user (polymorphic).
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(\App\Domains\Checkout\Models\Address::class, 'addressable');
    }

    /**
     * Get the orders for this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Domains\Order\Models\Order::class, 'user_id');
    }

    /**
     * Get the loyalty account for this user.
     */
    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(\App\Domains\Loyalty\Models\LoyaltyAccount::class, 'user_id');
    }

    /**
     * Get the loyalty transactions for this user.
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(\App\Domains\Loyalty\Models\LoyaltyTransaction::class, 'user_id');
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Check if user is suspended or banned.
     */
    public function isSuspended(): bool
    {
        return $this->status->isSuspended();
    }

    /**
     * Check if user has customer role.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Check if user has staff role.
     */
    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Get the tickets created by this user.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Domains\Support\Models\Ticket::class, 'user_id');
    }

    /**
     * Get the tickets assigned to this user.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(\App\Domains\Support\Models\Ticket::class, 'assigned_to');
    }

    /**
     * Scope query to active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE->value);
    }

    /**
     * Check if user has reviewed a product.
     */
    public function hasReviewedProduct(int $productId): bool
    {
        return $this->hasMany(\App\Domains\Catalog\Models\Review::class, 'user_id')
            ->where('product_id', $productId)
            ->exists();
    }
}
