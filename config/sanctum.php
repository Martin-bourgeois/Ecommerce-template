<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    | Domains that should be considered "stateful" for Sanctum's security.
    | These domains will not require CSRF validation.
    |
    */
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,127.0.0.1:3000,',
        env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    | This value defines the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request.
    |
    */
    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do not
    | expire.
    |
    */
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 30 * 24 * 60), // 30 days

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    | Sanctum can prefix new tokens with a given value to help identify their
    | purpose. For example, you may wish to prefix mobile app tokens so you're
    | able to know the source of the token.
    |
    */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | The middleware that should be assigned to every Sanctum routes.
    |
    */
    'middleware' => [
        'verify_csrf_token' => \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
    ],
];
