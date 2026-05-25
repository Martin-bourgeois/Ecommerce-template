<?php

declare(strict_types=1);

namespace App\Enums;

enum ItemCondition: string
{
    case UNOPENED = 'unopened';
    case OPENED = 'opened';
    case DAMAGED = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::UNOPENED => 'Non ouvert',
            self::OPENED => 'Ouvert',
            self::DAMAGED => 'Endommagé',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::UNOPENED => '📦',
            self::OPENED => '📂',
            self::DAMAGED => '⚠️',
        };
    }
}
