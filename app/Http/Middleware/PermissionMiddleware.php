<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is suspended or banned
        if ($user->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Votre compte a été suspendu. Veuillez contacter le support.');
        }

        // Check if user has all required permissions
        foreach ($permissions as $permission) {
            if (!$user->hasPermissionTo($permission)) {
                abort(403, 'Vous n\'avez pas la permission d\'accéder à cette ressource.');
            }
        }

        return $next($request);
    }
}
