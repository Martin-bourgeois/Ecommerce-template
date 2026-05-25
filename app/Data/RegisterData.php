<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $password_confirmation,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
