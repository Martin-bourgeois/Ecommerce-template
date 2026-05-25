<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Data;

use App\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $phone,
        public ?string $avatar_url,
        public array $addresses,
        public int $loyalty_points,
        public string $loyalty_tier,
        public bool $email_verified,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    /**
     * Créer une UserData depuis un modèle User
     */
    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            phone: $user->phone ?? '',
            avatar_url: $user->getFirstMediaUrl('avatars'),
            addresses: $user->addresses()
                ->get()
                ->map(fn($addr) => [
                    'id' => $addr->id,
                    'type' => $addr->type,
                    'street' => $addr->street,
                    'city' => $addr->city,
                    'postal_code' => $addr->postal_code,
                    'country' => $addr->country,
                    'is_default' => $addr->is_default,
                ])
                ->toArray(),
            loyalty_points: (int) ($user->loyalty_points ?? 0),
            loyalty_tier: $user->loyalty_tier ?? 'bronze',
            email_verified: $user->email_verified_at !== null,
            created_at: $user->created_at,
            updated_at: $user->updated_at,
        );
    }

    /**
     * Version publique sans infos sensibles
     */
    public static function fromModelPublic(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: '',
            phone: '',
            avatar_url: $user->getFirstMediaUrl('avatars'),
            addresses: [],
            loyalty_points: (int) ($user->loyalty_points ?? 0),
            loyalty_tier: $user->loyalty_tier ?? 'bronze',
            email_verified: false,
            created_at: $user->created_at,
            updated_at: $user->updated_at,
        );
    }
}
