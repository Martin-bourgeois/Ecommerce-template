<?php

declare(strict_types=1);

namespace App\Domains\Checkout\Data;

use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public int $id,
        public string $type,
        public string $firstName,
        public string $lastName,
        public ?string $company,
        public string $email,
        public string $phone,
        public string $streetAddress,
        public string $city,
        public string $postalCode,
        public string $country,
        public ?string $stateProvince,
        public bool $isDefault = false,
    ) {}

    public function getFullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    public function getFormattedAddress(): string
    {
        $parts = [
            $this->streetAddress,
            $this->postalCode . ' ' . $this->city,
            $this->country,
        ];

        return implode("\n", array_filter($parts));
    }
}
