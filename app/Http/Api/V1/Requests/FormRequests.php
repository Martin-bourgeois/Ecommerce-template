<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request pour l'authentification - Login
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Endpoint public
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email doit être valide',
            'password.required' => 'Le mot de passe est requis',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères',
            'device_name.required' => 'Le nom de l\'appareil est requis',
        ];
    }
}

/**
 * Form Request pour l'authentification - Register
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Endpoint public
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'phone:AUTO'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis',
            'email.required' => 'L\'email est requis',
            'email.unique' => 'Cet email est déjà utilisé',
            'password.required' => 'Le mot de passe est requis',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
        ];
    }
}

/**
 * Form Request pour ajouter au panier
 */
class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'L\'ID du produit est requis',
            'product_id.exists' => 'Le produit n\'existe pas',
            'quantity.required' => 'La quantité est requise',
            'quantity.min' => 'La quantité doit être au moins 1',
            'quantity.max' => 'La quantité ne peut pas dépasser 100',
        ];
    }
}

/**
 * Form Request pour mettre à jour une commande
 */
class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'La quantité est requise',
            'quantity.min' => 'La quantité doit être au moins 1',
            'quantity.max' => 'La quantité ne peut pas dépasser 100',
        ];
    }
}

/**
 * Form Request pour créer une commande
 */
class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['required', 'integer', 'exists:addresses,id'],
            'payment_method' => ['required', 'string', 'in:card,paypal,bank_transfer'],
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address_id.required' => 'L\'adresse de livraison est requise',
            'shipping_address_id.exists' => 'L\'adresse sélectionnée n\'existe pas',
            'payment_method.required' => 'La méthode de paiement est requise',
            'payment_method.in' => 'La méthode de paiement est invalide',
        ];
    }
}

/**
 * Form Request pour filtrer et chercher les produits
 */
class ProductSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Endpoint public
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort_by' => ['nullable', 'string', 'in:name,price,newest,popular,rating'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'La catégorie n\'existe pas',
            'min_price.numeric' => 'Le prix minimum doit être un nombre',
            'max_price.numeric' => 'Le prix maximum doit être un nombre',
            'sort_by.in' => 'Le tri demandé n\'est pas valide',
        ];
    }
}

/**
 * Form Request pour mettre à jour le profil utilisateur
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        $userId = auth('sanctum')->id();

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', "unique:users,email,{$userId}"],
            'phone' => ['nullable', 'phone:AUTO'],
            'current_password' => ['nullable', 'current_password:sanctum'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'required_with:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Cet email est déjà utilisé',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
        ];
    }
}
