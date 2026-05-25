<?php

declare(strict_types=1);

namespace App\Domains\Support\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING_CUSTOMER = 'waiting_customer';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Ouvert',
            self::IN_PROGRESS => 'En cours',
            self::WAITING_CUSTOMER => 'En attente du client',
            self::RESOLVED => 'Résolu',
            self::CLOSED => 'Fermé',
        };
    }
}

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
}
