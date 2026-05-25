<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;

class CheckoutForm extends Component
{
    #[Url]
    public int $step = 1;

    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $phone = '';
    public string $street = '';
    public string $city = '';
    public string $postalCode = '';
    public string $country = '';
    
    public string $shippingMethod = 'standard';
    public string $paymentMethod = 'card';

    public array $cartItems = [];
    public float $subtotal = 0;
    public float $tax = 0;
    public float $shipping = 0;
    public float $total = 0;

    public bool $processing = false;
    public ?string $message = null;

    public function mount(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('login');
        }

        $user = Auth::user();
        $this->firstName = explode(' ', $user->name)[0] ?? '';
        $this->lastName = explode(' ', $user->name)[1] ?? '';
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';

        $this->loadCart();
    }

    private function loadCart(): void
    {
        $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
        $cart = $cartFacade->getCart(Auth::user());

        $this->cartItems = $cart->items()
            ->with('product')
            ->get()
            ->map(fn($item) => [
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->product->discounted_price ?? $item->product->price,
                'total' => ($item->product->discounted_price ?? $item->product->price) * $item->quantity,
            ])
            ->toArray();

        $this->subtotal = collect($this->cartItems)->sum('total');
        $this->tax = $this->subtotal * 0.20;
        $this->shipping = $this->subtotal > 100 ? 0 : 9.99;
        $this->total = $this->subtotal + $this->tax + $this->shipping;
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'firstName' => 'required|string|max:100',
                'lastName' => 'required|string|max:100',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'street' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'postalCode' => 'required|string|max:20',
                'country' => 'required|string|max:100',
            ]);
            $this->step = 2;
        } elseif ($this->step === 2) {
            $this->validate([
                'shippingMethod' => 'required|in:standard,express',
                'paymentMethod' => 'required|in:card,paypal,bank',
            ]);
            $this->step = 3;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        $this->processing = true;
        $this->message = null;

        try {
            $orderFacade = app(\App\Domains\Order\Services\OrderFacade::class);
            $order = $orderFacade->createOrder(
                Auth::user(),
                [
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'street' => $this->street,
                    'city' => $this->city,
                    'postal_code' => $this->postalCode,
                    'country' => $this->country,
                    'shipping_method' => $this->shippingMethod,
                    'payment_method' => $this->paymentMethod,
                ]
            );

            $this->redirectRoute('account.order-detail', $order->id);
        } catch (\Exception $e) {
            $this->message = 'Erreur lors de la création de la commande';
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.checkout.checkout-form');
    }
}
