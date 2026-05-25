<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use App\Domains\Order\Contracts\PaymentInterface;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\PaymentMethod;
use App\Domains\Order\Enums\PaymentStatus;
use App\Domains\Order\Events\PaymentConfirmed;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class PayPalPaymentService implements PaymentInterface
{
    /**
     * Initiate a PayPal payment.
     *
     * Returns a payment object with reference and PayPal email.
     */
    public function initiate(Order $order): Payment
    {
        // Check if payment already exists for this order (idempotence)
        $existingPayment = $order->payments()
            ->where('method', PaymentMethod::PAYPAL)
            ->whereIn('status', [PaymentStatus::PENDING, PaymentStatus::COMPLETED])
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        $reference = Payment::generateReference($order->id);

        $payment = $order->payments()->create([
            'method' => PaymentMethod::PAYPAL,
            'status' => PaymentStatus::PENDING,
            'amount_cents' => $order->total_cents,
            'currency' => 'EUR',
            'reference' => $reference,
            'metadata' => [
                'paypal_email' => $this->getPayPalEmail(),
                'expires_at' => now()->addHours(48)->toIso8601String(),
            ],
        ]);

        activity('payment_initiated')
            ->performedOn($payment)
            ->withProperties(['method' => 'paypal', 'amount' => $order->total_cents])
            ->log("Paiement PayPal initié ({$reference})");

        return $payment;
    }

    /**
     * Confirm a payment (admin action).
     *
     * Updates payment status to completed and triggers order processing.
     */
    public function confirm(Payment $payment, User $admin): void
    {
        if (!$payment->isPending()) {
            throw new \InvalidArgumentException("Cannot confirm non-pending payment ({$payment->status->value})");
        }

        $payment->markAsCompleted($admin);

        // Update order status to processing
        $order = $payment->payable;
        $order->updateStatus(OrderStatus::PROCESSING);

        // Fire event to trigger next steps (email, inventory, etc.)
        PaymentConfirmed::dispatch($payment);
    }

    /**
     * Cancel a payment.
     */
    public function cancel(Payment $payment, string $reason = ''): void
    {
        if ($payment->status === PaymentStatus::CANCELLED) {
            return; // Already cancelled
        }

        $payment->markAsCancelled($reason ?: 'Cancelled by admin');

        // Cancel associated order if still pending
        $order = $payment->payable;
        if ($order->status === OrderStatus::PENDING_PAYMENT) {
            $order->updateStatus(OrderStatus::CANCELLED);
        }
    }

    /**
     * Refund a payment.
     */
    public function refund(Payment $payment, User $admin): void
    {
        if ($payment->status !== PaymentStatus::COMPLETED) {
            throw new \InvalidArgumentException("Only completed payments can be refunded");
        }

        $payment->update(['status' => PaymentStatus::REFUNDED]);

        activity('payment_refunded')
            ->causedBy($admin)
            ->performedOn($payment)
            ->log("Paiement {$payment->reference} remboursé");
    }

    /**
     * Auto-cancel expired payments.
     */
    public function cancelExpired(): int
    {
        $expiredPayments = Payment::query()
            ->where('method', PaymentMethod::PAYPAL)
            ->where('status', PaymentStatus::PENDING)
            ->where('created_at', '<', now()->subHours(48))
            ->get();

        foreach ($expiredPayments as $payment) {
            $this->cancel($payment, 'Expired (48 hour timeout)');
        }

        return $expiredPayments->count();
    }

    /**
     * Get PayPal email from settings.
     */
    private function getPayPalEmail(): string
    {
        return Config::get('services.paypal.email', 'payment@example.com');
    }
}
