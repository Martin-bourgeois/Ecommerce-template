<?php

declare(strict_types=1);

namespace App\Domains\Order\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::COMPLETED => 'Complété',
            self::FAILED => 'Échoué',
            self::REFUNDED => 'Remboursé',
            self::CANCELLED => 'Annulé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'info',
            self::CANCELLED => 'secondary',
        };
    }
}
