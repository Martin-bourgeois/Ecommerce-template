<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Cart\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SyncCart
{
    public function __construct(private CartService $cartService) {}

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // If user just logged in, merge guest cart
        if (Auth::check() && !Session::has('cart_synced')) {
            $guestSessionId = Session::getId();
            $userId = Auth::id();

            $this->cartService->merge($guestSessionId, $userId);
            Session::put('cart_synced', true);
        }

        return $response;
    }
}
