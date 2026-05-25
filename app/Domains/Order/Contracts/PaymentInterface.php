<?php

declare(strict_types=1);

namespace App\Domains\Order\Contracts;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\Payment;
use App\Models\User;

interface PaymentInterface
{
    /**
     * Initiate a payment for an order.
     */
    public function initiate(Order $order): Payment;

    /**
     * Confirm a payment.
     */
    public function confirm(Payment $payment, User $admin): void;

    /**
     * Cancel a payment.
     */
    public function cancel(Payment $payment, string $reason = ''): void;

    /**
     * Refund a payment.
     */
    public function refund(Payment $payment, User $admin): void;
}
