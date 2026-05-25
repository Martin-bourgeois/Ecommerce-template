<?php

declare(strict_types=1);

namespace App\Domains\Checkout\Services;

use App\Domains\Cart\Services\CartService;
use App\Domains\Checkout\Data\AddressData;
use App\Domains\Checkout\Data\CheckoutSummary;
use App\Domains\Checkout\Models\Address;
use App\Domains\Checkout\Models\ShippingZone;
use Illuminate\Support\Facades\Session;

class CheckoutService
{
    private ?Address $shippingAddress = null;
    private ?Address $billingAddress = null;

    public function __construct(private CartService $cartService) {}

    /**
     * Set shipping address.
     */
    public function setShippingAddress(Address $address): void
    {
        $this->shippingAddress = $address;
        Session::put('checkout_state.shipping_address_id', $address->id);
    }

    /**
     * Set billing address.
     */
    public function setBillingAddress(Address $address): void
    {
        $this->billingAddress = $address;
        Session::put('checkout_state.billing_address_id', $address->id);
    }

    /**
     * Get shipping address.
     */
    public function getShippingAddress(): ?Address
    {
        if ($this->shippingAddress) {
            return $this->shippingAddress;
        }

        $addressId = Session::get('checkout_state.shipping_address_id');
        if ($addressId) {
            return Address::findOrFail($addressId);
        }

        return null;
    }

    /**
     * Get billing address.
     */
    public function getBillingAddress(): ?Address
    {
        if ($this->billingAddress) {
            return $this->billingAddress;
        }

        $addressId = Session::get('checkout_state.billing_address_id');
        if ($addressId) {
            return Address::findOrFail($addressId);
        }

        return null;
    }

    /**
     * Calculate shipping cost.
     */
    public function calculateShipping(): int
    {
        $shippingAddress = $this->getShippingAddress();
        if (!$shippingAddress) {
            throw new \InvalidArgumentException('Shipping address is required');
        }

        $zone = ShippingZone::query()
            ->where('is_active', true)
            ->get()
            ->first(fn (ShippingZone $z) => $z->hasCountry($shippingAddress->country));

        if (!$zone) {
            throw new \InvalidArgumentException(
                "No shipping zone configured for {$shippingAddress->country}"
            );
        }

        $cartTotal = $this->cartService->total();
        $weight = $this->calculateCartWeight(); // Would need to be implemented in Product

        return $zone->calculateCost($weight, $cartTotal);
    }

    /**
     * Get checkout summary.
     */
    public function getSummary(): CheckoutSummary
    {
        if ($this->cartService->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        $shippingAddress = $this->getShippingAddress();
        if (!$shippingAddress) {
            throw new \InvalidArgumentException('Shipping address is required');
        }

        $subtotal = $this->cartService->total();
        $shipping = $this->calculateShipping();

        // Calculate tax (simplified: single rate per country)
        $taxRate = $this->getTaxRate($shippingAddress->country);
        $tax = (int) round($subtotal * $taxRate / 100);

        $total = $subtotal + $shipping + $tax;

        return new CheckoutSummary(
            subtotalCents: $subtotal,
            shippingCents: $shipping,
            taxCents: $tax,
            totalCents: $total,
            itemCount: $this->cartService->count(),
            shippingAddress: $this->addressToData($shippingAddress),
            billingAddress: $this->billingAddress ? $this->addressToData($this->billingAddress) : null,
        );
    }

    /**
     * Validate checkout state.
     */
    public function validate(): bool
    {
        // Check cart not empty
        if ($this->cartService->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        // Check addresses
        if (!$this->getShippingAddress()) {
            throw new \InvalidArgumentException('Shipping address is required');
        }

        // Check stock for all items
        foreach ($this->cartService->getContent() as $item) {
            // Stock verification would be done here
        }

        return true;
    }

    /**
     * Clear checkout state.
     */
    public function clear(): void
    {
        Session::forget('checkout_state');
        $this->shippingAddress = null;
        $this->billingAddress = null;
    }

    /**
     * Calculate total weight of cart items.
     */
    private function calculateCartWeight(): float
    {
        // This would need Product::weight attribute
        // For now, return default
        return 0.0;
    }

    /**
     * Get tax rate for country.
     */
    private function getTaxRate(string $country): float
    {
        // Simplified: 20% for France, 0% for others
        return $country === 'FR' ? 20.0 : 0.0;
    }

    /**
     * Convert Address model to AddressData DTO.
     */
    private function addressToData(Address $address): AddressData
    {
        return new AddressData(
            id: $address->id,
            type: $address->type->value,
            firstName: $address->first_name,
            lastName: $address->last_name,
            company: $address->company,
            email: $address->email,
            phone: $address->phone,
            streetAddress: $address->street_address,
            city: $address->city,
            postalCode: $address->postal_code,
            country: $address->country,
            stateProvince: $address->state_province,
            isDefault: $address->is_default,
        );
    }
}
