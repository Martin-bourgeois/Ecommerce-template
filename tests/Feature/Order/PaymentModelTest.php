<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Order\Enums\PaymentMethod;
use App\Domains\Order\Enums\PaymentStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\Payment;
use App\Models\User;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    public function test_payment_is_pending(): void
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::PENDING->value,
        ]);

        $this->assertTrue($payment->isPending());
    }

    public function test_payment_is_expired_after_48_hours(): void
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::PENDING->value,
            'created_at' => now()->subHours(49),
        ]);

        $this->assertTrue($payment->isExpired());
    }

    public function test_payment_is_not_expired_before_48_hours(): void
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::PENDING->value,
            'created_at' => now()->subHours(24),
        ]);

        $this->assertFalse($payment->isExpired());
    }

    public function test_completed_payment_is_never_expired(): void
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::COMPLETED->value,
            'created_at' => now()->subMonths(1),
        ]);

        $this->assertFalse($payment->isExpired());
    }

    public function test_mark_as_completed(): void
    {
        $admin = User::factory()->create();
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::PENDING->value,
        ]);

        $payment->markAsCompleted($admin);

        $payment->refresh();
        $this->assertEquals(PaymentStatus::COMPLETED->value, $payment->status->value);
        $this->assertEquals($admin->id, $payment->confirmed_by);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_mark_as_failed(): void
    {
        $payment = Payment::factory()->create();

        $payment->markAsFailed('Payment declined');

        $payment->refresh();
        $this->assertEquals(PaymentStatus::FAILED->value, $payment->status->value);
    }

    public function test_mark_as_cancelled(): void
    {
        $payment = Payment::factory()->create();

        $payment->markAsCancelled('Expired');

        $payment->refresh();
        $this->assertEquals(PaymentStatus::CANCELLED->value, $payment->status->value);
        $this->assertNotNull($payment->cancelled_at);
    }

    public function test_formatted_amount(): void
    {
        $payment = Payment::factory()->create([
            'amount_cents' => 12345,
            'currency' => 'EUR',
        ]);

        $this->assertEquals('123,45 EUR', $payment->getFormattedAmount());
    }

    public function test_generate_reference(): void
    {
        $reference = Payment::generateReference(42);

        $this->assertStringStartsWith('PAY-42-', $reference);
    }

    public function test_morphed_by_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = Payment::factory()->create([
            'payable_id' => $order->id,
            'payable_type' => Order::class,
        ]);

        $this->assertInstanceOf(Order::class, $payment->payable);
        $this->assertEquals($order->id, $payment->payable->id);
    }
}
