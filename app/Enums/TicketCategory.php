<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketCategory: string
{
    case ORDER_ISSUE = 'order_issue';
    case PRODUCT_QUESTION = 'product_question';
    case RETURN_REQUEST = 'return_request';
    case TECHNICAL = 'technical';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_ISSUE => 'Problème de commande',
            self::PRODUCT_QUESTION => 'Question sur produit',
            self::RETURN_REQUEST => 'Demande de retour',
            self::TECHNICAL => 'Problème technique',
            self::OTHER => 'Autre',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ORDER_ISSUE => 'amber',
            self::PRODUCT_QUESTION => 'blue',
            self::RETURN_REQUEST => 'red',
            self::TECHNICAL => 'orange',
            self::OTHER => 'gray',
        };
    }
}
