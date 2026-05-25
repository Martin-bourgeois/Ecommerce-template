<?php

declare(strict_types=1);

namespace App\Domains\RMA\Enums;

enum RMAStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case RECEIVED = 'received';
    case ANALYZED = 'analyzed';
    case EXCHANGED = 'exchanged';
    case REFUNDED = 'refunded';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Demandé',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::IN_TRANSIT => 'En transit',
            self::RECEIVED => 'Reçu',
            self::ANALYZED => 'Analysé',
            self::EXCHANGED => 'Échangé',
            self::REFUNDED => 'Remboursé',
            self::CLOSED => 'Fermé',
        };
    }
}

enum RMAReason: string
{
    case DEFECTIVE = 'defective';
    case DAMAGED = 'damaged';
    case NOT_AS_DESCRIBED = 'not_as_described';
    case MISSING_PARTS = 'missing_parts';
    case CHANGED_MIND = 'changed_mind';
    case NOT_NEEDED = 'not_needed';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DEFECTIVE => 'Défectueux',
            self::DAMAGED => 'Endommagé',
            self::NOT_AS_DESCRIBED => 'Ne correspond pas à la description',
            self::MISSING_PARTS => 'Pièces manquantes',
            self::CHANGED_MIND => 'J\'ai changé d\'avis',
            self::NOT_NEEDED => 'Je n\'en ai plus besoin',
            self::OTHER => 'Autre',
        };
    }
}
