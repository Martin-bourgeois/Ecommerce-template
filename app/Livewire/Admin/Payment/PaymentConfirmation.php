<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Payment;

use App\Domains\Order\Models\Payment;
use App\Domains\Order\Services\PayPalPaymentService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class PaymentConfirmation extends Component
{
    use WithPagination;

    public int $perPage = 20;

    public function confirm(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        $service = app(PayPalPaymentService::class);
        $service->confirm($payment, Auth::user());

        $this->dispatch('notification', message: "Paiement confirmé: {$payment->reference}");
        $this->resetPage();
    }

    public function reject(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        $service = app(PayPalPaymentService::class);
        $service->cancel($payment, 'Rejected by admin');

        $this->dispatch('notification', message: "Paiement rejeté: {$payment->reference}");
        $this->resetPage();
    }

    public function getPendingPaymentsProperty()
    {
        return Payment::query()
            ->where('status', 'pending')
            ->with('payable')
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.payment.payment-confirmation', [
            'payments' => $this->pendingPayments,
        ]);
    }
}
