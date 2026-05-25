<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PWA Configuration
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'New Commerce'),
    'short_name' => 'Commerce',
    'description' => 'Plateforme e-commerce progressive avec accès offline',

    /*
    |--------------------------------------------------------------------------
    | Display Mode
    |--------------------------------------------------------------------------
    | Modes: standalone, fullscreen, minimal-ui, browser
    */
    'display' => 'standalone',

    /*
    |--------------------------------------------------------------------------
    | Couleurs
    |--------------------------------------------------------------------------
    */
    'theme_color' => '#3b82f6',
    'background_color' => '#ffffff',

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'version' => env('PWA_CACHE_VERSION', 'v1'),
        'max_age' => 30 * 24 * 60 * 60, // 30 jours

        // Assets à mettre en cache au chargement
        'static_assets' => [
            '/css/app.css',
            '/js/app.js',
            '/js/bootstrap.js',
            '/js/pwa-manager.js',
            '/images/logo.png',
            '/images/icon-192x192.png',
            '/images/icon-512x512.png',
            '/manifest.json',
        ],

        // Patterns de pages à mettre en cache dynamiquement
        'dynamic_patterns' => [
            'products',
            'categories',
            'cart',
            'orders',
            'account',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notifications
    |--------------------------------------------------------------------------
    */
    'push' => [
        'provider' => env('PUSH_PROVIDER', 'web-push'), // web-push ou onesignal
        'vapid_public_key' => env('VAPID_PUBLIC_KEY'),
        'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Icônes
    |--------------------------------------------------------------------------
    */
    'icons' => [
        '192x192' => '/images/icon-192x192.png',
        '512x512' => '/images/icon-512x512.png',
        'maskable_192x192' => '/images/icon-maskable-192x192.png',
        'maskable_512x512' => '/images/icon-maskable-512x512.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Screenshots
    |--------------------------------------------------------------------------
    */
    'screenshots' => [
        [
            'src' => '/images/screenshot-1.png',
            'sizes' => '540x720',
            'form_factor' => 'narrow',
        ],
        [
            'src' => '/images/screenshot-2.png',
            'sizes' => '1280x720',
            'form_factor' => 'wide',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Shortcuts
    |--------------------------------------------------------------------------
    */
    'shortcuts' => [
        [
            'name' => 'Nouveau produit',
            'short_name' => 'Produits',
            'description' => 'Consulter les nouveaux produits',
            'url' => '/products?sort=newest',
        ],
        [
            'name' => 'Panier',
            'short_name' => 'Panier',
            'description' => 'Accéder à votre panier',
            'url' => '/cart',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Background Sync
    |--------------------------------------------------------------------------
    */
    'background_sync' => [
        'enabled' => true,
        'tags' => [
            'sync-cart',
            'sync-notifications',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Offline Page
    |--------------------------------------------------------------------------
    */
    'offline_page' => '/offline',

    /*
    |--------------------------------------------------------------------------
    | Apple Web App
    |--------------------------------------------------------------------------
    */
    'apple' => [
        'capable' => true,
        'status_bar_style' => 'black-translucent',
        'touch_icon' => '/images/apple-touch-icon.png',
    ],
];
