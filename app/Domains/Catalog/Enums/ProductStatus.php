<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Enums;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case DISCONTINUED = 'discontinued';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::ACTIVE => 'Actif',
            self::DISCONTINUED => 'Discontinué',
        };
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isDiscontinued(): bool
    {
        return $this === self::DISCONTINUED;
    }
}
