<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Domains\Customer\Enums\UserStatus;

class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle login.
     */
    public function login(): void
    {
        $this->validate();

        // Find user
        $user = User::where('email', $this->email)->first();

        // Check if user exists and account is active
        if (!$user) {
            $this->addError('email', 'Aucun compte trouvé avec cet email.');
            return;
        }

        if ($user->isSuspended()) {
            $this->addError('email', 'Votre compte a été suspendu. Veuillez contacter le support.');
            return;
        }

        // Attempt login
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('password', 'Identifiants invalides.');
            return;
        }

        Session::regenerate();

        $this->dispatch('login-success');

        redirect()->intended(route('dashboard'))
            ->with('success', 'Connecté avec succès!');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
