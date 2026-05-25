<?php

declare(strict_types=1);

namespace App\Http\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AddressesManager extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $type = 'shipping';
    public string $street = '';
    public string $city = '';
    public string $postalCode = '';
    public string $country = '';
    public bool $isDefault = false;

    public function render()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->paginate(10);

        return view('livewire.account.addresses-manager', [
            'addresses' => $addresses,
        ]);
    }

    public function openForm(): void
    {
        $this->showForm = true;
        $this->resetForm();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->type = 'shipping';
        $this->street = '';
        $this->city = '';
        $this->postalCode = '';
        $this->country = '';
        $this->isDefault = false;
    }

    public function save(): void
    {
        $this->validate([
            'type' => 'required|in:shipping,billing',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postalCode' => 'required|string|max:20',
            'country' => 'required|string|max:100',
        ]);

        $user = Auth::user();

        if ($this->editingId) {
            $address = $user->addresses()->find($this->editingId);
            if ($address) {
                $address->update([
                    'type' => $this->type,
                    'street' => $this->street,
                    'city' => $this->city,
                    'postal_code' => $this->postalCode,
                    'country' => $this->country,
                    'is_default' => $this->isDefault,
                ]);
            }
        } else {
            $user->addresses()->create([
                'type' => $this->type,
                'street' => $this->street,
                'city' => $this->city,
                'postal_code' => $this->postalCode,
                'country' => $this->country,
                'is_default' => $this->isDefault,
            ]);
        }

        $this->closeForm();
    }

    public function delete(int $id): void
    {
        Auth::user()->addresses()->where('id', $id)->delete();
    }
}
