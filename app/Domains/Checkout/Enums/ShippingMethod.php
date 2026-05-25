<?php

declare(strict_types=1);

namespace App\Domains\Checkout\Enums;

enum ShippingMethod: string
{
    case STANDARD = 'standard';
    case EXPRESS = 'express';
    case OVERNIGHT = 'overnight';
    case PICKUP = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::EXPRESS => 'Express',
            self::OVERNIGHT => 'Nuit',
            self::PICKUP => 'Retrait en magasin',
        };
    }
}

enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PAYPAL = 'paypal';
    case BANK_TRANSFER = 'bank_transfer';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Carte de crédit',
            self::DEBIT_CARD => 'Carte de débit',
            self::PAYPAL => 'PayPal',
            self::BANK_TRANSFER => 'Virement bancaire',
            self::OTHER => 'Autre',
        };
    }
}
