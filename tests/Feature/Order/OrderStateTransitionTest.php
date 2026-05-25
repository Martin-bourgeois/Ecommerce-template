<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Catalog\Models\Product;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderStatusHistory;
use App\Domains\Order\Services\OrderService;
use App\Models\User;
use Tests\TestCase;

class OrderStateTransitionTest extends TestCase
{
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
    }

    public function test_can_transition_from_pending_payment_to_processing(): void
    {
        $order = Order::factory()->pending()->create();

        $result = $this->orderService->transitionStatus($order, OrderStatus::PROCESSING);

        $this->assertTrue($order->canTransitionTo(OrderStatus::PROCESSING));
        $result->refresh();
        $this->assertEquals(OrderStatus::PROCESSING->value, $result->status->value);
    }

    public function test_can_transition_from_processing_to_shipped(): void
    {
        $order = Order::factory()->processing()->create();

        $result = $this->orderService->transitionStatus($order, OrderStatus::SHIPPED);

        $result->refresh();
        $this->assertEquals(OrderStatus::SHIPPED->value, $result->status->value);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $order = Order::factory()->pending()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->transitionStatus($order, OrderStatus::SHIPPED);
    }

    public function test_cannot_transition_from_completed(): void
    {
        $order = Order::factory()->completed()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->transitionStatus($order, OrderStatus::PROCESSING);
    }

    public function test_transition_records_status_history(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create();

        $this->orderService->transitionStatus(
            $order,
            OrderStatus::PROCESSING,
            $user,
            'Test reason',
        );

        $history = $order->statusHistories()
            ->where('to_status', OrderStatus::PROCESSING->value)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals($user->id, $history->user_id);
        $this->assertEquals('Test reason', $history->reason);
    }

    public function test_transition_to_processing_decrements_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create();

        // Create order item
        $order->items()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'qty' => 3,
            'price_cents' => 10000,
            'total_cents' => 30000,
        ]);

        $this->orderService->transitionStatus($order, OrderStatus::PROCESSING);

        $product->refresh();
        $this->assertEquals(7, $product->stock_quantity);
    }

    public function test_all_transitions_flow_correctly(): void
    {
        $order = Order::factory()->pending()->create();

        // PENDING → PROCESSING
        $this->orderService->transitionStatus($order, OrderStatus::PROCESSING);
        $order->refresh();
        $this->assertEquals(OrderStatus::PROCESSING->value, $order->status->value);

        // PROCESSING → SHIPPED
        $this->orderService->transitionStatus($order, OrderStatus::SHIPPED);
        $order->refresh();
        $this->assertEquals(OrderStatus::SHIPPED->value, $order->status->value);

        // SHIPPED → DELIVERED
        $this->orderService->transitionStatus($order, OrderStatus::DELIVERED);
        $order->refresh();
        $this->assertEquals(OrderStatus::DELIVERED->value, $order->status->value);

        // DELIVERED → COMPLETED
        $this->orderService->transitionStatus($order, OrderStatus::COMPLETED);
        $order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->status->value);
    }
}
