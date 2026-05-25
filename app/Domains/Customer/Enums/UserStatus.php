<?php

declare(strict_types=1);

namespace App\Domains\Customer\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::SUSPENDED => 'Suspendu',
            self::BANNED => 'Banni',
        };
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED || $this === self::BANNED;
    }
}

enum CustomerGroup: string
{
    case STANDARD = 'standard';
    case VIP = 'vip';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::VIP => 'VIP',
        };
    }
}

enum NewsletterStatus: string
{
    case SUBSCRIBED = 'subscribed';
    case UNSUBSCRIBED = 'unsubscribed';

    public function label(): string
    {
        return match ($this) {
            self::SUBSCRIBED => 'Abonné',
            self::UNSUBSCRIBED => 'Désabonné',
        };
    }
}
