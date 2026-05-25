<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Domains\Checkout\Services\CheckoutService;
use Livewire\Component;
use Livewire\Attributes\Validate;

class PaymentStep extends Component
{
    #[Validate('required|in:paypal,stripe,card')]
    public string $paymentMethod = '';

    #[Validate('required|accepted')]
    public bool $agreeTerms = false;

    public function mount(): void
    {
        // Pre-select first available payment method
        $this->paymentMethod = 'paypal';
    }

    public function placeOrder(CheckoutService $checkoutService): void
    {
        $this->validate();

        try {
            $checkoutService->validate();

            // Order creation will be handled in next prompt (Order domain)
            $this->dispatch('checkout:order-placed', paymentMethod: $this->paymentMethod);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $checkoutService = app(CheckoutService::class);

        try {
            $summary = $checkoutService->getSummary();

            return view('livewire.checkout.payment-step', [
                'summary' => $summary,
                'paymentMethods' => [
                    'paypal' => 'PayPal',
                    'stripe' => 'Carte bancaire (Stripe)',
                    'card' => 'Carte bancaire',
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return view('livewire.checkout.payment-step', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
