<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:shipping,billing'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(\+33|0)[1-9](?:[0-9]{8})$/'],
            'street_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', $this->postalCodeRule()],
            'country' => ['required', 'in:FR,MC,AD,ES,IT,CH,DE,BE,LU,NL'],
            'state_province' => ['nullable', 'string', 'max:100'],
            'is_default' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Le téléphone doit être au format français (+33 ou 0)',
            'postal_code' => 'Le code postal est invalide pour le pays sélectionné',
            'country.in' => 'Pays non supporté',
        ];
    }

    /**
     * Get postal code validation rule based on country.
     */
    private function postalCodeRule()
    {
        $country = $this->input('country', 'FR');

        return match ($country) {
            'FR' => 'regex:/^(?:0|97)[0-9]{3}$/', // France: 5 digits (00000-99999 or 97xxx for DOM-TOM)
            'MC' => 'regex:/^980\d{2}$/', // Monaco: 98000-98099
            'AD' => 'regex:/^AD\d{3}$/', // Andorra: AD000-AD999
            'ES' => 'regex:/^\d{5}$/', // Spain: 5 digits
            'IT' => 'regex:/^\d{5}$/', // Italy: 5 digits
            'CH' => 'regex:/^\d{4}$/', // Switzerland: 4 digits
            'DE' => 'regex:/^\d{5}$/', // Germany: 5 digits
            'BE' => 'regex:/^\d{4}$/', // Belgium: 4 digits
            'LU' => 'regex:/^\d{4}$/', // Luxembourg: 4 digits
            'NL' => 'regex:/^\d{4}\s?[A-Z]{2}$/', // Netherlands: 4 digits + 2 letters
            default => 'string',
        };
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
