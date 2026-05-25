<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CustomerProfile;
use App\Domains\Customer\Enums\CustomerGroup;
use App\Domains\Customer\Enums\NewsletterStatus;

class Register extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|string|same:password')]
    public string $password_confirmation = '';

    public bool $subscribe_newsletter = false;

    public bool $form_submitted = false;

    /**
     * Handle registration.
     */
    public function register(): void
    {
        $this->validate();

        try {
            // Create user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => 'active',
            ]);

            // Assign customer role
            $user->assignRole('customer');

            // Create customer profile
            CustomerProfile::create([
                'user_id' => $user->id,
                'group' => CustomerGroup::STANDARD->value,
                'newsletter_status' => $this->subscribe_newsletter 
                    ? NewsletterStatus::SUBSCRIBED->value 
                    : NewsletterStatus::UNSUBSCRIBED->value,
            ]);

            $this->form_submitted = true;

            // Redirect to login after 2 seconds
            $this->dispatch('registration-success');

            // Simulate delay and redirect
            redirect()->route('login')
                ->with('success', 'Inscription réussie! Veuillez vous connecter.');
        } catch (\Exception $e) {
            $this->addError('email', 'Une erreur est survenue lors de l\'inscription.');
        }
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
