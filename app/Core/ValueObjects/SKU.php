<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

final class SKU
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value) || !preg_match('/^[A-Z0-9\-]+$/', $value)) {
            throw new \InvalidArgumentException('Invalid SKU format: ' . $value);
        }

        $this->value = strtoupper($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(SKU $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
