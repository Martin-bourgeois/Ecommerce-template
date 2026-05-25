<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Data\UserData;
use App\Http\Api\V1\Requests\LoginRequest;
use App\Http\Api\V1\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Contrôleur pour l'authentification API
 * Endpoints: login, register, logout, refresh token
 */
class AuthController
{
    /**
     * Se connecter et obtenir un token API
     *
     * @route POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::whereEmail($request->validated('email'))->first();

        // Vérifier l'email existe et mot de passe correct
        if (!$user || !Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'authentication' => ['Email ou mot de passe incorrect'],
                ],
            ], 401);
        }

        // Marquer email comme vérifié si nécessaire
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email not verified',
                'errors' => [
                    'verification' => ['Veuillez vérifier votre email avant de continuer'],
                ],
            ], 403);
        }

        // Créer un token
        $token = $user->createToken(
            name: $request->validated('device_name'),
            abilities: ['*']
        )->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => UserData::fromModel($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Créer un nouveau compte et obtenir un token
     *
     * @route POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Créer l'utilisateur
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(), // Auto-verify for testing, remove in production
        ]);

        // Créer un token
        $token = $user->createToken(
            name: 'mobile-app',
            abilities: ['*']
        )->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully',
            'data' => [
                'user' => UserData::fromModel($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Se déconnecter et révoquer le token actuel
     *
     * @route POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Révoquer le token actuel
        $request->user('sanctum')?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Révoquer tous les tokens de l'utilisateur
     *
     * @route POST /api/v1/auth/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Révoquer tous les tokens
        $request->user('sanctum')?->tokens()?->delete();

        return response()->json([
            'message' => 'All sessions logged out',
        ], 200);
    }

    /**
     * Obtenir les infos de l'utilisateur connecté
     *
     * @route GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => UserData::fromModel($request->user('sanctum')),
        ], 200);
    }

    /**
     * Rafraîchir l'authentification (vérifier si le token est toujours valide)
     *
     * @route POST /api/v1/auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        // Vérifier si l'utilisateur existe toujours et est actif
        if (!$user || !$user->is_active) {
            return response()->json([
                'message' => 'User not found or inactive',
                'errors' => [
                    'authentication' => ['Utilisateur introuvable ou inactif'],
                ],
            ], 401);
        }

        return response()->json([
            'message' => 'Token is valid',
            'data' => [
                'user' => UserData::fromModel($user),
                'token_expires_in' => config('sanctum.expiration'),
            ],
        ], 200);
    }
}
