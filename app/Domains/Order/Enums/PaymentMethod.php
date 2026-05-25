<?php

declare(strict_types=1);

namespace App\Domains\Order\Enums;

enum PaymentMethod: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case CARD = 'card';
    case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::PAYPAL => 'PayPal',
            self::STRIPE => 'Stripe',
            self::CARD => 'Carte bancaire',
            self::BANK_TRANSFER => 'Virement bancaire',
        };
    }
}
