<?php

declare(strict_types=1);

namespace App\Enums;

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

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'blue',
            self::IN_PROGRESS => 'amber',
            self::WAITING_CUSTOMER => 'orange',
            self::RESOLVED => 'green',
            self::CLOSED => 'gray',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::CLOSED;
    }
}
