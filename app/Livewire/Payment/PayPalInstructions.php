<?php

declare(strict_types=1);

namespace App\Livewire\Payment;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\PayPalPaymentService;
use Livewire\Component;

class PayPalInstructions extends Component
{
    public Order $order;
    public ?string $paypalEmail = null;
    public string $reference = '';
    public string $amount = '';
    public int $hoursRemaining = 48;
    public bool $showCountdown = true;

    public function mount(Order $order): void
    {
        $this->order = $order;
        $payment = $order->payment;

        if (!$payment) {
            $service = app(PayPalPaymentService::class);
            $payment = $service->initiate($order);
        }

        $this->paypalEmail = $payment->metadata['paypal_email'] ?? 'payment@example.com';
        $this->reference = $payment->reference;
        $this->amount = $payment->getFormattedAmount();

        // Calculate hours remaining
        $expiresAt = \Carbon\Carbon::parse($payment->metadata['expires_at'] ?? now()->addHours(48));
        $this->hoursRemaining = max(0, (int) $expiresAt->diffInHours(now(), false));

        if ($this->hoursRemaining <= 0) {
            $this->showCountdown = false;
        }
    }

    #[\Livewire\Attributes\On('refresh-payment')]
    public function refreshPayment(): void
    {
        $this->mount($this->order->fresh());
    }

    public function render()
    {
        return view('livewire.payment.paypal-instructions');
    }
}
