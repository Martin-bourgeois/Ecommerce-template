<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\PaymentMethod;
use App\Domains\Order\Enums\PaymentStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\Payment;
use App\Domains\Order\Services\PayPalPaymentService;
use App\Models\User;
use Tests\TestCase;

class PayPalPaymentServiceTest extends TestCase
{
    private PayPalPaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PayPalPaymentService::class);
    }

    public function test_initiate_creates_payment_with_reference(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $payment = $this->service->initiate($order);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(PaymentMethod::PAYPAL->value, $payment->method->value);
        $this->assertEquals(PaymentStatus::PENDING->value, $payment->status->value);
        $this->assertStringStartsWith('PAY-' . $order->id . '-', $payment->reference);
        $this->assertEquals($order->total_cents, $payment->amount_cents);
    }

    public function test_initiate_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $payment1 = $this->service->initiate($order);
        $payment2 = $this->service->initiate($order);

        $this->assertEquals($payment1->id, $payment2->id);
        $this->assertEquals($payment1->reference, $payment2->reference);

        // Verify only one payment exists
        $this->assertEquals(1, $order->payments()->count());
    }

    public function test_confirm_updates_status_to_completed(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = $this->service->initiate($order);

        $this->service->confirm($payment, $admin);

        $payment->refresh();
        $this->assertEquals(PaymentStatus::COMPLETED->value, $payment->status->value);
        $this->assertEquals($admin->id, $payment->confirmed_by);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_confirm_updates_order_status_to_processing(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create([
            'status' => OrderStatus::PENDING_PAYMENT->value,
        ]);
        $payment = $this->service->initiate($order);

        $this->service->confirm($payment, $admin);

        $order->refresh();
        $this->assertEquals(OrderStatus::PROCESSING->value, $order->status->value);
    }

    public function test_cannot_confirm_non_pending_payment(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = $this->service->initiate($order);
        $payment->update(['status' => PaymentStatus::COMPLETED->value]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->confirm($payment, $admin);
    }

    public function test_cancel_updates_status_and_sets_cancelled_at(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = $this->service->initiate($order);

        $this->service->cancel($payment, 'Test cancellation');

        $payment->refresh();
        $this->assertEquals(PaymentStatus::CANCELLED->value, $payment->status->value);
        $this->assertNotNull($payment->cancelled_at);
    }

    public function test_cancel_order_if_still_pending(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create([
            'status' => OrderStatus::PENDING_PAYMENT->value,
        ]);
        $payment = $this->service->initiate($order);

        $this->service->cancel($payment, 'Expired');

        $order->refresh();
        $this->assertEquals(OrderStatus::CANCELLED->value, $order->status->value);
    }

    public function test_cancel_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = $this->service->initiate($order);

        $this->service->cancel($payment, 'First cancel');
        $this->service->cancel($payment, 'Second cancel');

        // Should not throw
        $payment->refresh();
        $this->assertEquals(PaymentStatus::CANCELLED->value, $payment->status->value);
    }

    public function test_refund_updates_status(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = $this->service->initiate($order);
        $payment->update(['status' => PaymentStatus::COMPLETED->value]);

        $this->service->refund($payment, $admin);

        $payment->refresh();
        $this->assertEquals(PaymentStatus::REFUNDED->value, $payment->status->value);
    }

    public function test_cannot_refund_non_completed_payment(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = $this->service->initiate($order);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->refund($payment, $admin);
    }

    public function test_cancel_expired_removes_old_pending_payments(): void
    {
        $user = User::factory()->create();
        $order1 = Order::factory()->for($user)->create();
        $order2 = Order::factory()->for($user)->create();

        $payment1 = $this->service->initiate($order1);
        $payment2 = $this->service->initiate($order2);

        // Make payment1 expired (49 hours old)
        $payment1->update(['created_at' => now()->subHours(49)]);

        $count = $this->service->cancelExpired();

        $this->assertEquals(1, $count);
        $payment1->refresh();
        $payment2->refresh();

        $this->assertEquals(PaymentStatus::CANCELLED->value, $payment1->status->value);
        $this->assertEquals(PaymentStatus::PENDING->value, $payment2->status->value);
    }

    public function test_payment_includes_paypal_email_in_metadata(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $payment = $this->service->initiate($order);

        $this->assertArrayHasKey('paypal_email', $payment->metadata);
        $this->assertNotEmpty($payment->metadata['paypal_email']);
    }

    public function test_payment_includes_expiration_time_in_metadata(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $payment = $this->service->initiate($order);

        $this->assertArrayHasKey('expires_at', $payment->metadata);
        $expiresAt = \Carbon\Carbon::parse($payment->metadata['expires_at']);
        $this->assertTrue($expiresAt->isFuture());
    }
}
