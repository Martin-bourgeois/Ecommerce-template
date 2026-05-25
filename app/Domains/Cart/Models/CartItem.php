<?php

declare(strict_types=1);

namespace App\Domains\Cart\Models;

use Illuminate\Contracts\Support\Arrayable;

final class CartItem implements Arrayable
{
    private function __construct(
        public readonly string $sku,
        public readonly int $qty,
        public readonly int $price, // in cents
        public readonly string $name,
        public readonly array $options = [],
    ) {}

    public static function make(
        string $sku,
        int $qty,
        int $price,
        string $name,
        array $options = [],
    ): self {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        if ($price < 0) {
            throw new \InvalidArgumentException('Price must be >= 0');
        }

        return new self($sku, $qty, $price, $name, $options);
    }

    public function withQty(int $qty): self
    {
        return self::make($this->sku, $qty, $this->price, $this->name, $this->options);
    }

    public function withOptions(array $options): self
    {
        return self::make($this->sku, $this->qty, $this->price, $this->name, $options);
    }

    public function getTotal(): int
    {
        return $this->qty * $this->price;
    }

    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'qty' => $this->qty,
            'price' => $this->price,
            'name' => $this->name,
            'options' => $this->options,
            'total' => $this->getTotal(),
        ];
    }
}
