<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiting personnalisé pour l'API
 * - 60 requêtes/minute pour authentification
 * - 1000 requêtes/minute pour catalogue
 * - 500 requêtes/minute pour les autres endpoints
 */
class ThrottleApi
{
    public function __construct(
        protected RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Déterminer le limite basée sur le path
        $limit = $this->getLimit($request);
        $key = $this->getKey($request);

        // Vérifier le rate limit
        if ($this->limiter->tooManyAttempts($key, $limit)) {
            $retryAfter = $this->limiter->availableIn($key);

            return response()->json([
                'message' => 'Too many requests',
                'errors' => [
                    'rate_limit' => [
                        sprintf(
                            'Trop de requêtes. Réessayez dans %d secondes.',
                            $retryAfter
                        ),
                    ],
                ],
            ], 429)
                ->header('Retry-After', $retryAfter)
                ->header('X-RateLimit-Limit', $limit)
                ->header('X-RateLimit-Remaining', max(0, $limit - $this->limiter->attempts($key)));
        }

        $this->limiter->hit($key, 60); // Decay après 60 secondes

        $response = $next($request);

        return $response
            ->header('X-RateLimit-Limit', $limit)
            ->header('X-RateLimit-Remaining', max(0, $limit - $this->limiter->attempts($key)));
    }

    /**
     * Obtenir la limite de requêtes basée sur le path
     */
    protected function getLimit(Request $request): int
    {
        $path = $request->getPathInfo();

        // Auth endpoints: 60 req/min
        if (str_starts_with($path, '/api/v1/auth')) {
            return 60;
        }

        // Catalogue (produits, catégories): 1000 req/min
        if (str_starts_with($path, '/api/v1/products') || str_starts_with($path, '/api/v1/categories')) {
            return 1000;
        }

        // Par défaut: 500 req/min
        return 500;
    }

    /**
     * Obtenir la clé pour le rate limiting
     * Utiliser l'ID utilisateur si authentifié, sinon l'IP
     */
    protected function getKey(Request $request): string
    {
        $userId = $request->user('sanctum')?->id ?? $request->ip();

        return "api_requests_{$userId}";
    }
}
