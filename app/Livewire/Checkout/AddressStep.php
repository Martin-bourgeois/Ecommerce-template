<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Domains\Checkout\Models\Address;
use App\Domains\Checkout\Services\CheckoutService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class AddressStep extends Component
{
    #[Validate('required|string')]
    public string $firstName = '';

    #[Validate('required|string')]
    public string $lastName = '';

    #[Validate('nullable|string')]
    public ?string $company = null;

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|regex:/^(\+33|0)[1-9](?:[0-9]{8})$/')]
    public string $phone = '';

    #[Validate('required|string')]
    public string $streetAddress = '';

    #[Validate('required|string')]
    public string $city = '';

    #[Validate('required|string')]
    public string $postalCode = '';

    #[Validate('required|in:FR,MC,AD,ES,IT,CH,DE,BE,LU,NL')]
    public string $country = 'FR';

    #[Validate('nullable|string')]
    public ?string $stateProvince = null;

    public bool $useSameForBilling = true;
    public bool $selectExisting = false;
    public ?int $existingAddressId = null;

    public array $existingAddresses = [];

    public function mount(): void
    {
        if (Auth::check()) {
            $this->loadExistingAddresses();
            $this->loadDefaultAddress();
        }
    }

    public function loadExistingAddresses(): void
    {
        $user = Auth::user();
        $this->existingAddresses = $user
            ->addresses()
            ->where('type', 'shipping')
            ->get()
            ->map(fn (Address $a) => [
                'id' => $a->id,
                'label' => "{$a->street_address}, {$a->postal_code} {$a->city}",
                'formatted' => $a->formatted_address,
            ])
            ->toArray();
    }

    public function loadDefaultAddress(): void
    {
        $user = Auth::user();
        $default = $user->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();

        if ($default) {
            $this->existingAddressId = $default->id;
            $this->selectExisting = true;
        }
    }

    public function proceedToNext(CheckoutService $checkoutService): void
    {
        if ($this->selectExisting && $this->existingAddressId) {
            $address = Address::findOrFail($this->existingAddressId);
            $checkoutService->setShippingAddress($address);

            if ($this->useSameForBilling) {
                $checkoutService->setBillingAddress($address);
            }
        } else {
            $this->validate();

            $user = Auth::user();
            $address = $user->addresses()->create([
                'type' => 'shipping',
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'company' => $this->company,
                'email' => $this->email,
                'phone' => $this->phone,
                'street_address' => $this->streetAddress,
                'city' => $this->city,
                'postal_code' => $this->postalCode,
                'country' => $this->country,
                'state_province' => $this->stateProvince,
            ]);

            $checkoutService->setShippingAddress($address);

            if ($this->useSameForBilling) {
                $checkoutService->setBillingAddress($address);
            }
        }

        $this->dispatch('checkout:next-step', step: 2);
    }

    public function render()
    {
        return view('livewire.checkout.address-step', [
            'existingAddresses' => $this->existingAddresses,
        ]);
    }
}
