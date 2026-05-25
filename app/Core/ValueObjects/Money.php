<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

final class Money
{
    private int $amount;
    private string $currency;

    public function __construct(int $amount, string $currency = 'EUR')
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromFloat(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getAmountAsFloat(): float
    {
        return $this->amount / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract money with different currencies');
        }

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(int|float $factor): self
    {
        return new self((int) round($this->amount * $factor), $this->currency);
    }

    public function __toString(): string
    {
        return sprintf('%s %.2f', $this->currency, $this->getAmountAsFloat());
    }
}
