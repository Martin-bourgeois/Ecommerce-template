<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Enums;

enum ProductType: string
{
    case CONFIGURABLE = 'configurable';
    case SIMPLE = 'simple';

    public function label(): string
    {
        return match ($this) {
            self::CONFIGURABLE => 'Produit Configurable',
            self::SIMPLE => 'Produit Simple',
        };
    }

    public function isConfigurable(): bool
    {
        return $this === self::CONFIGURABLE;
    }

    public function isSimple(): bool
    {
        return $this === self::SIMPLE;
    }
}
