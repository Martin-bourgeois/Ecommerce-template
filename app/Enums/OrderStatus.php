<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PROCESSING => 'En traitement',
            self::SHIPPED => 'Expédiée',
            self::DELIVERED => 'Livrée',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#f59e0b',
            self::PROCESSING => '#3b82f6',
            self::SHIPPED => '#8b5cf6',
            self::DELIVERED => '#10b981',
            self::CANCELLED => '#ef4444',
        };
    }
}
