<?php

declare(strict_types=1);

namespace App\Domains\Cart\Enums;

enum CartStatus: string
{
    case ACTIVE = 'active';
    case ABANDONED = 'abandoned';
    case CONVERTED = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::ABANDONED => 'Abandonné',
            self::CONVERTED => 'Convertir',
        };
    }
}
