<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour vérifier qu'une requête API a un token valide
 * Bloque les requêtes sans token sur les endpoints protégés
 */
class EnsureApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier que l'utilisateur est authentifié via token
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'errors' => [
                    'authentication' => ['Vous devez fournir un token valide'],
                ],
            ], 401);
        }

        // Vérifier que c'est un token API (pas une session web)
        $token = $request->user('sanctum')?->currentAccessToken();
        
        if (!$token) {
            return response()->json([
                'message' => 'Invalid token type',
                'errors' => [
                    'authentication' => ['Ce token n\'est pas valide pour l\'API'],
                ],
            ], 401);
        }

        return $next($request);
    }
}
