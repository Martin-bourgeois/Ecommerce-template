<?php

declare(strict_types=1);

namespace App\Enums;

enum ReturnReason: string
{
    case DEFECTIVE = 'defective';
    case WRONG_ITEM = 'wrong_item';
    case NOT_AS_DESCRIBED = 'not_as_described';
    case CHANGED_MIND = 'changed_mind';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DEFECTIVE => 'Article défectueux',
            self::WRONG_ITEM => 'Mauvais article',
            self::NOT_AS_DESCRIBED => 'Ne correspond pas à la description',
            self::CHANGED_MIND => 'Changement d\'avis',
            self::OTHER => 'Autre',
        };
    }

    public function includesShipping(): bool
    {
        return $this !== self::CHANGED_MIND;
    }

    public function restockItem(): bool
    {
        return $this !== self::CHANGED_MIND;
    }
}
