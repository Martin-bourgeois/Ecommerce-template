<?php

declare(strict_types=1);

namespace App\Domains\Checkout\Data;

class CheckoutSummary
{
    public function __construct(
        public int $subtotalCents,
        public int $shippingCents,
        public int $taxCents,
        public int $totalCents,
        public int $itemCount,
        public ?AddressData $shippingAddress = null,
        public ?AddressData $billingAddress = null,
    ) {}

    public function getSubtotalFormatted(): string
    {
        return $this->formatCents($this->subtotalCents);
    }

    public function getShippingFormatted(): string
    {
        return $this->formatCents($this->shippingCents);
    }

    public function getTaxFormatted(): string
    {
        return $this->formatCents($this->taxCents);
    }

    public function getTotalFormatted(): string
    {
        return $this->formatCents($this->totalCents);
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, ',', ' ') . ' €';
    }

    public function toArray(): array
    {
        return [
            'subtotal_cents' => $this->subtotalCents,
            'shipping_cents' => $this->shippingCents,
            'tax_cents' => $this->taxCents,
            'total_cents' => $this->totalCents,
            'item_count' => $this->itemCount,
            'shipping_address' => $this->shippingAddress?->toArray(),
            'billing_address' => $this->billingAddress?->toArray(),
        ];
    }
}
