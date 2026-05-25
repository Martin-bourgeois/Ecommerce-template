<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();
        $isAdmin = in_array('admin', $userRoles) || in_array('staff', $userRoles);

        if (!$isAdmin) {
            abort(403, 'Unauthorized access to admin panel');
        }

        return $next($request);
    }
}
