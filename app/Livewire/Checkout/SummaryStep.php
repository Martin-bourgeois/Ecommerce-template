<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Domains\Checkout\Services\CheckoutService;
use Livewire\Component;

class SummaryStep extends Component
{
    public function render()
    {
        $checkoutService = app(CheckoutService::class);

        try {
            $summary = $checkoutService->getSummary();

            return view('livewire.checkout.summary-step', [
                'summary' => $summary,
            ]);
        } catch (\InvalidArgumentException $e) {
            return view('livewire.checkout.summary-step', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function modifyAddress(CheckoutService $checkoutService): void
    {
        $this->dispatch('checkout:previous-step', step: 1);
    }

    public function proceedToPayment(): void
    {
        $this->dispatch('checkout:next-step', step: 3);
    }
}
