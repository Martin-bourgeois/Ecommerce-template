<?php

declare(strict_types=1);

namespace App\Domains\Checkout\Enums;

enum AddressType: string
{
    case SHIPPING = 'shipping';
    case BILLING = 'billing';
}
