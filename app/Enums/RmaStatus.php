<?php

declare(strict_types=1);

namespace App\Enums;

enum RmaStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case RECEIVED = 'received';
    case INSPECTED = 'inspected';
    case REFUNDED = 'refunded';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Demandé',
            self::APPROVED => 'Approuvé',
            self::RECEIVED => 'Reçu',
            self::INSPECTED => 'Inspecté',
            self::REFUNDED => 'Remboursé',
            self::REJECTED => 'Rejeté',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REQUESTED => 'blue',
            self::APPROVED => 'amber',
            self::RECEIVED => 'cyan',
            self::INSPECTED => 'purple',
            self::REFUNDED => 'green',
            self::REJECTED => 'red',
        };
    }

    public function isFinalized(): bool
    {
        return $this === self::REFUNDED || $this === self::REJECTED;
    }
}
