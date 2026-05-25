<?php

declare(strict_types=1);

namespace App\Domains\Checkout\Models;

use App\Domains\Checkout\Enums\AddressType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'type',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone',
        'street_address',
        'city',
        'postal_code',
        'country',
        'state_province',
        'is_default',
    ];

    protected $casts = [
        'type' => AddressType::class,
        'is_default' => 'boolean',
    ];

    /**
     * Get the owning addressable model (polymorphic).
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get full address as formatted string.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [
            $this->street_address,
            $this->postal_code . ' ' . $this->city,
            $this->country,
        ];

        return implode("\n", array_filter($parts));
    }

    /**
     * Mark as default shipping address.
     */
    public function markAsDefaultShipping(): void
    {
        // Unmark other default addresses of same type
        $this->addressable
            ->addresses()
            ->where('type', AddressType::SHIPPING)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Mark this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Mark as default billing address.
     */
    public function markAsDefaultBilling(): void
    {
        // Unmark other default addresses of same type
        $this->addressable
            ->addresses()
            ->where('type', AddressType::BILLING)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Mark this as default
        $this->update(['is_default' => true]);
    }
}
