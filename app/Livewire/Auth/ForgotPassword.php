<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class ForgotPassword extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public bool $link_sent = false;

    /**
     * Handle forgot password request.
     */
    public function requestPasswordReset(): void
    {
        $this->validate();

        // Check if user exists
        $user = User::where('email', $this->email)->first();

        if (!$user) {
            // Return generic message for security
            $this->link_sent = true;
            return;
        }

        // Send reset link
        try {
            $status = Password::sendResetLink(['email' => $this->email]);

            if ($status === Password::RESET_LINK_SENT) {
                $this->link_sent = true;
                $this->dispatch('reset-link-sent');
            } else {
                $this->addError('email', 'Impossible d\'envoyer le lien de réinitialisation.');
            }
        } catch (\Exception $e) {
            $this->addError('email', 'Une erreur est survenue lors de l\'envoi.');
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
