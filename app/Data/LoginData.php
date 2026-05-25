<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class LoginData extends Data
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    public static function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }
}
