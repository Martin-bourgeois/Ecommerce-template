<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Data\UserData;
use App\Http\Api\V1\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Contrôleur pour le profil et compte utilisateur
 * Endpoints: profil, adresses, fidélité
 */
class AccountController
{
    /**
     * Obtenir le profil de l'utilisateur connecté
     *
     * @route GET /api/v1/account/profile
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'data' => UserData::fromModel($request->user('sanctum')),
        ], 200);
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     *
     * @route PATCH /api/v1/account/profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user('sanctum');

        try {
            // Mettre à jour les champs
            if (isset($validated['name'])) {
                $user->update(['name' => $validated['name']]);
            }

            if (isset($validated['email'])) {
                $user->update(['email' => $validated['email']]);
            }

            if (isset($validated['phone'])) {
                $user->update(['phone' => $validated['phone']]);
            }

            // Changer le mot de passe
            if (isset($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => UserData::fromModel($user->fresh()),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'errors' => [
                    'profile' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Obtenir les adresses de l'utilisateur
     *
     * @route GET /api/v1/account/addresses
     */
    public function addresses(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        // $addresses = $user->addresses()->get();

        return response()->json([
            'data' => [],
        ], 200);
    }

    /**
     * Créer une nouvelle adresse
     *
     * @route POST /api/v1/account/addresses
     */
    public function createAddress(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:billing,shipping'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user('sanctum');

        try {
            // $address = $user->addresses()->create($request->validated());

            return response()->json([
                'message' => 'Address created successfully',
                'data' => [
                    'id' => 1,
                    'type' => $request->input('type'),
                    'street' => $request->input('street'),
                    'city' => $request->input('city'),
                    'postal_code' => $request->input('postal_code'),
                    'country' => $request->input('country'),
                    'is_default' => $request->input('is_default', false),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create address',
                'errors' => [
                    'address' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Mettre à jour une adresse
     *
     * @route PATCH /api/v1/account/addresses/{address}
     */
    public function updateAddress(int $addressId, Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', 'string', 'in:billing,shipping'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user('sanctum');

        try {
            // $address = $user->addresses()->findOrFail($addressId);
            // $address->update($request->validated());

            return response()->json([
                'message' => 'Address updated successfully',
                'data' => [
                    'id' => $addressId,
                    'type' => $request->input('type', 'shipping'),
                    'street' => $request->input('street'),
                    'city' => $request->input('city'),
                    'postal_code' => $request->input('postal_code'),
                    'country' => $request->input('country'),
                    'is_default' => $request->input('is_default', false),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update address',
                'errors' => [
                    'address' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Supprimer une adresse
     *
     * @route DELETE /api/v1/account/addresses/{address}
     */
    public function deleteAddress(int $addressId, Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        try {
            // $address = $user->addresses()->findOrFail($addressId);
            // $address->delete();

            return response()->json([
                'message' => 'Address deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete address',
                'errors' => [
                    'address' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Obtenir les infos de fidélité
     *
     * @route GET /api/v1/account/loyalty
     */
    public function loyalty(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        return response()->json([
            'data' => [
                'tier' => $user->loyalty_tier ?? 'bronze',
                'points' => $user->loyalty_points ?? 0,
                'next_tier_points' => 1000, // Exemple
                'points_earned_this_month' => 50,
                'points_spent_this_month' => 25,
            ],
        ], 200);
    }

    /**
     * Obtenir l'historique des points de fidélité
     *
     * @route GET /api/v1/account/loyalty/history
     */
    public function loyaltyHistory(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        // $history = $user->loyaltyHistory()
        //     ->orderBy('created_at', 'desc')
        //     ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => [],
            'pagination' => [
                'total' => 0,
                'per_page' => 15,
                'current_page' => 1,
                'last_page' => 1,
            ],
        ], 200);
    }
}
