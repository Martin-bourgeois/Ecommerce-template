<?php

declare(strict_types=1);

namespace App\Http\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public bool $saving = false;
    public ?string $message = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
        ]);

        $this->saving = true;

        try {
            $user = Auth::user();
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
            ]);

            $this->message = 'Profil mis à jour avec succès';
        } catch (\Exception $e) {
            $this->message = 'Erreur lors de la mise à jour';
        } finally {
            $this->saving = false;
        }
    }

    public function render()
    {
        return view('livewire.account.profile-form');
    }
}
