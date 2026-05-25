<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Basse',
            self::MEDIUM => 'Moyenne',
            self::HIGH => 'Haute',
            self::CRITICAL => 'Critique',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => 'green',
            self::MEDIUM => 'blue',
            self::HIGH => 'orange',
            self::CRITICAL => 'red',
        };
    }

    public function slaHours(): int
    {
        return match ($this) {
            self::LOW => 72,
            self::MEDIUM => 24,
            self::HIGH => 4,
            self::CRITICAL => 1,
        };
    }
}
