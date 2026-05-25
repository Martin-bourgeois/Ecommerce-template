<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $table = 'customer_addresses';

    protected $fillable = [
        'user_id',
        'type',
        'first_name',
        'last_name',
        'street_address',
        'street_address_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get full address.
     */
    public function getFullAddressAttribute(): string
    {
        $address = "{$this->street_address}, {$this->postal_code} {$this->city}, {$this->state}";

        if ($this->street_address_2) {
            $address = "{$this->street_address} - {$this->street_address_2}, {$this->postal_code} {$this->city}, {$this->state}";
        }

        return $address;
    }

    /**
     * Set as default address.
     */
    public function setAsDefault(): void
    {
        // Unset other defaults of same type
        self::where('user_id', $this->user_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }
}
