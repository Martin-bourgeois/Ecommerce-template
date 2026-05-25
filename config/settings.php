<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | E-Commerce Settings
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the e-commerce platform
    |
    */

    'shop' => [
        'name' => [
            'default' => env('SHOP_NAME', 'My Shop'),
            'description' => 'Shop name',
        ],
        'currency' => [
            'default' => env('SHOP_CURRENCY', 'EUR'),
            'description' => 'Default currency code (ISO 4217)',
        ],
        'timezone' => [
            'default' => env('APP_TIMEZONE', 'Europe/Paris'),
            'description' => 'Shop timezone',
        ],
        'url' => [
            'default' => env('APP_URL', 'http://localhost'),
            'description' => 'Shop public URL',
        ],
        'email' => [
            'default' => env('SHOP_EMAIL', 'contact@example.com'),
            'description' => 'Shop contact email',
        ],
    ],

    'tax' => [
        'vat_rate' => [
            'default' => 20.0,
            'description' => 'Default VAT rate (%)',
        ],
        'vat_include_in_prices' => [
            'default' => true,
            'description' => 'Whether VAT is included in product prices',
        ],
    ],

    'shipping' => [
        'free_threshold' => [
            'default' => 100.00,
            'description' => 'Minimum order amount for free shipping',
        ],
        'default_method' => [
            'default' => 'standard',
            'description' => 'Default shipping method',
        ],
        'max_weight' => [
            'default' => 30,
            'description' => 'Maximum shippable weight in kg',
        ],
    ],

    'loyalty' => [
        'enabled' => [
            'default' => true,
            'description' => 'Enable loyalty program',
        ],
        'points_per_euro' => [
            'default' => 1,
            'description' => 'Loyalty points earned per euro spent',
        ],
        'points_to_euro_ratio' => [
            'default' => 100,
            'description' => 'Points needed to earn 1 euro discount',
        ],
    ],

    'catalog' => [
        'products_per_page' => [
            'default' => 12,
            'description' => 'Default products per page in catalog',
        ],
        'show_out_of_stock' => [
            'default' => false,
            'description' => 'Show out of stock products in catalog',
        ],
        'enable_reviews' => [
            'default' => true,
            'description' => 'Enable product reviews',
        ],
        'min_review_length' => [
            'default' => 10,
            'description' => 'Minimum review comment length',
        ],
    ],

    'cart' => [
        'max_items' => [
            'default' => 999,
            'description' => 'Maximum items in cart',
        ],
        'expire_days' => [
            'default' => 30,
            'description' => 'Cart expiration time in days',
        ],
    ],

    'orders' => [
        'auto_confirm' => [
            'default' => true,
            'description' => 'Automatically confirm orders after payment',
        ],
        'require_account' => [
            'default' => false,
            'description' => 'Require customer account to checkout',
        ],
        'retention_days' => [
            'default' => 2555,
            'description' => 'Order retention period in days (7 years)',
        ],
    ],

    'rma' => [
        'enabled' => [
            'default' => true,
            'description' => 'Enable RMA (Returns Management) system',
        ],
        'return_days' => [
            'default' => 30,
            'description' => 'Number of days to request an RMA',
        ],
        'restocking_fee_percent' => [
            'default' => 15,
            'description' => 'Restocking fee percentage',
        ],
    ],

    'support' => [
        'enabled' => [
            'default' => true,
            'description' => 'Enable support ticket system',
        ],
        'auto_assign' => [
            'default' => false,
            'description' => 'Automatically assign tickets to support agents',
        ],
        'sla_hours' => [
            'default' => 24,
            'description' => 'Support ticket SLA response time in hours',
        ],
    ],

    'notifications' => [
        'send_order_confirmation' => [
            'default' => true,
            'description' => 'Send order confirmation email',
        ],
        'send_shipping_notification' => [
            'default' => true,
            'description' => 'Send shipping notification email',
        ],
        'send_delivery_notification' => [
            'default' => true,
            'description' => 'Send delivery notification email',
        ],
        'newsletter_enabled' => [
            'default' => true,
            'description' => 'Enable newsletter signup',
        ],
    ],

    'payment' => [
        'stripe_enabled' => [
            'default' => false,
            'description' => 'Enable Stripe payment gateway',
        ],
        'paypal_enabled' => [
            'default' => false,
            'description' => 'Enable PayPal payment gateway',
        ],
    ],

    'analytics' => [
        'track_events' => [
            'default' => true,
            'description' => 'Track analytics events',
        ],
        'retention_days' => [
            'default' => 365,
            'description' => 'Analytics retention period in days',
        ],
    ],

    'security' => [
        'password_min_length' => [
            'default' => 8,
            'description' => 'Minimum password length',
        ],
        'max_login_attempts' => [
            'default' => 5,
            'description' => 'Maximum login attempts before lockout',
        ],
        'lockout_minutes' => [
            'default' => 15,
            'description' => 'Account lockout duration in minutes',
        ],
    ],
];
