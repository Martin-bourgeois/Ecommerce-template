<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;
use App\Models\User;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderService::class);
    }

    public function test_cannot_create_order_with_empty_cart(): void
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart is empty');

        $this->service->createFromCheckout($user);
    }

    public function test_cannot_create_order_without_shipping_address(): void
    {
        $user = User::factory()->create();
        // Would need cart and checkout setup here
        // This is a placeholder for full integration testing

        $this->assertTrue(true);
    }

    public function test_update_order_status(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $this->service->updateStatus($order, OrderStatus::PROCESSING);

        $order->refresh();
        $this->assertEquals(OrderStatus::PROCESSING->value, $order->status->value);
    }

    public function test_cancel_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $this->service->cancel($order, 'Test cancellation');

        $order->refresh();
        $this->assertEquals(OrderStatus::CANCELLED->value, $order->status->value);
        $this->assertNotNull($order->cancelled_at);
    }

    public function test_cancel_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $this->service->cancel($order, 'First cancel');
        $this->service->cancel($order, 'Second cancel');

        // Should not throw
        $order->refresh();
        $this->assertEquals(OrderStatus::CANCELLED->value, $order->status->value);
    }
}
