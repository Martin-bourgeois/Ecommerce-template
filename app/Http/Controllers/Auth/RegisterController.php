<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomerProfile;
use App\Domains\Customer\Enums\CustomerGroup;
use App\Domains\Customer\Enums\NewsletterStatus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'newsletter' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        // Create customer profile
        CustomerProfile::create([
            'user_id' => $user->id,
            'group' => CustomerGroup::STANDARD,
            'newsletter_status' => $validated['newsletter'] ? NewsletterStatus::SUBSCRIBED : NewsletterStatus::UNSUBSCRIBED,
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Inscription réussie !');
    }
}
