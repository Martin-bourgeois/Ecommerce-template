<?php

declare(strict_types=1);

namespace App\Domains\Cart\Models;

use Illuminate\Contracts\Support\Arrayable;

final class Price implements Arrayable
{
    private function __construct(
        public readonly int $cents,
        public readonly string $currency = 'EUR',
    ) {}

    public static function fromCents(int $cents, string $currency = 'EUR'): self
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }

        return new self($cents, $currency);
    }

    public static function fromEuro(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function inEuro(): float
    {
        return $this->cents / 100;
    }

    public function inCents(): int
    {
        return $this->cents;
    }

    public function format(): string
    {
        return number_format($this->inEuro(), 2, ',', ' ') . ' €';
    }

    public function toArray(): array
    {
        return [
            'cents' => $this->cents,
            'currency' => $this->currency,
            'formatted' => $this->format(),
        ];
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
