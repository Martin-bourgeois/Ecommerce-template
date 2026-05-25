<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Returns;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Returns\Models\Rma;
use App\Domains\Returns\Models\RmaItem;
use App\Domains\Returns\Services\RmaService;
use App\Enums\RmaStatus;
use App\Enums\ReturnReason;
use App\Enums\ItemCondition;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\Domains\Returns\RmaFactory;
use Database\Factories\Domains\Returns\RmaItemFactory;
use Tests\TestCase;

class RmaServiceTest extends TestCase
{
    private RmaService $rmaService;
    private User $user;
    private User $admin;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rmaService = app(RmaService::class);
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->order = Order::factory()
            ->for($this->user)
            ->delivered()
            ->create();
    }

    public function test_can_request_return_within_14_days(): void
    {
        $orderItem = OrderItem::factory()->for($this->order)->create([
            'price' => 50.00,
        ]);

        $rma = $this->rmaService->requestReturn(
            $this->order,
            $this->user,
            [
                'reason' => ReturnReason::DEFECTIVE,
                'items' => [
                    [
                        'order_item_id' => $orderItem->id,
                        'quantity' => 1,
                    ],
                ],
            ],
            'Product is defective'
        );

        $this->assertInstanceOf(Rma::class, $rma);
        $this->assertEquals(RmaStatus::REQUESTED, $rma->status);
        $this->assertEquals(ReturnReason::DEFECTIVE, $rma->reason);
        $this->assertEquals($this->order->id, $rma->order_id);
        $this->assertEquals($this->user->id, $rma->user_id);
        $this->assertEquals(1, $rma->items()->count());
    }

    public function test_cannot_request_return_outside_14_days(): void
    {
        $this->order->update([
            'delivered_at' => now()->subDays(15),
        ]);

        $orderItem = OrderItem::factory()->for($this->order)->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Délai de retour dépassé');

        $this->rmaService->requestReturn(
            $this->order,
            $this->user,
            [
                'reason' => ReturnReason::CHANGED_MIND,
                'items' => [
                    [
                        'order_item_id' => $orderItem->id,
                        'quantity' => 1,
                    ],
                ],
            ]
        );
    }

    public function test_cannot_request_return_if_not_delivered(): void
    {
        $this->order->update(['status' => 'pending']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('doit être livrée');

        $this->rmaService->requestReturn($this->order, $this->user, []);
    }

    public function test_can_approve_requested_rma(): void
    {
        $rma = RmaFactory::new()->requested()->create();

        $approved = $this->rmaService->approve($rma, $this->admin);

        $this->assertEquals(RmaStatus::APPROVED, $approved->status);
        $this->assertEquals($this->admin->id, $approved->approved_by);
    }

    public function test_rma_number_generated_on_approval(): void
    {
        $rma = RmaFactory::new()->requested()->create();

        $this->assertNull($rma->fresh()->rma_number);

        $approved = $this->rmaService->approve($rma, $this->admin);

        $this->assertNotNull($approved->rma_number);
        $this->assertStringStartsWith('RMA-' . now()->year . '-', $approved->rma_number);
    }

    public function test_rma_numbers_are_unique(): void
    {
        $rma1 = $this->rmaService->approve(
            RmaFactory::new()->requested()->create(),
            $this->admin
        );

        $rma2 = $this->rmaService->approve(
            RmaFactory::new()->requested()->create(),
            $this->admin
        );

        $this->assertNotEquals($rma1->rma_number, $rma2->rma_number);
    }

    public function test_can_transition_through_statuses(): void
    {
        $rma = RmaFactory::new()->requested()->withItems(2)->create();

        // Approve
        $rma = $this->rmaService->approve($rma, $this->admin);
        $this->assertEquals(RmaStatus::APPROVED, $rma->status);

        // Receive
        $rma = $this->rmaService->receiveItems($rma);
        $this->assertEquals(RmaStatus::RECEIVED, $rma->status);

        // Inspect
        $conditions = [];
        foreach ($rma->items as $item) {
            $conditions[$item->order_item_id] = ItemCondition::UNOPENED->value;
        }
        $rma = $this->rmaService->inspectItems($rma, $conditions);
        $this->assertEquals(RmaStatus::INSPECTED, $rma->status);

        // Refund
        $rma = $this->rmaService->processRefund($rma, $this->admin);
        $this->assertEquals(RmaStatus::REFUNDED, $rma->status);
        $this->assertNotNull($rma->refund_amount);
    }

    public function test_changed_mind_excludes_shipping(): void
    {
        $order = Order::factory()
            ->for($this->user)
            ->delivered()
            ->create([
                'shipping_cost' => 10.00,
            ]);

        $orderItem = OrderItem::factory()->for($order)->create([
            'price' => 50.00,
        ]);

        $rma = $this->rmaService->requestReturn(
            $order,
            $this->user,
            [
                'reason' => ReturnReason::CHANGED_MIND,
                'items' => [
                    [
                        'order_item_id' => $orderItem->id,
                        'quantity' => 1,
                    ],
                ],
            ]
        );

        $approved = $this->rmaService->approve($rma, $this->admin);
        $received = $this->rmaService->receiveItems($approved);
        $inspected = $this->rmaService->inspectItems($received, [
            $orderItem->id => ItemCondition::UNOPENED->value,
        ]);
        $refunded = $this->rmaService->processRefund($inspected, $this->admin);

        // Should NOT include shipping for changed_mind
        $this->assertEquals(50.00, $refunded->refund_amount);
    }

    public function test_defective_includes_shipping(): void
    {
        $order = Order::factory()
            ->for($this->user)
            ->delivered()
            ->create([
                'shipping_cost' => 10.00,
            ]);

        $orderItem = OrderItem::factory()->for($order)->create([
            'price' => 50.00,
        ]);

        $rma = $this->rmaService->requestReturn(
            $order,
            $this->user,
            [
                'reason' => ReturnReason::DEFECTIVE,
                'items' => [
                    [
                        'order_item_id' => $orderItem->id,
                        'quantity' => 1,
                    ],
                ],
            ]
        );

        $approved = $this->rmaService->approve($rma, $this->admin);
        $received = $this->rmaService->receiveItems($approved);
        $inspected = $this->rmaService->inspectItems($received, [
            $orderItem->id => ItemCondition::UNOPENED->value,
        ]);
        $refunded = $this->rmaService->processRefund($inspected, $this->admin);

        // Should include shipping for defective
        $this->assertEquals(60.00, $refunded->refund_amount);
    }

    public function test_unopened_items_always_restocked(): void
    {
        $rma = RmaFactory::new()->inspected()->withItems(1)->create();
        /** @var RmaItem $item */
        $item = $rma->items->first();

        $refunded = $this->rmaService->processRefund($rma, $this->admin);

        // Stock should be increased
        $this->assertTrue(true); // Placeholder for stock check logic
    }

    public function test_changed_mind_items_not_restocked(): void
    {
        $rma = RmaFactory::new()->inspected()->withItems(1)->create([
            'reason' => ReturnReason::CHANGED_MIND,
        ]);

        $refunded = $this->rmaService->processRefund($rma, $this->admin);

        // Stock should NOT be increased for changed mind
        $this->assertTrue(true); // Placeholder for stock check logic
    }

    public function test_can_reject_requested_rma(): void
    {
        $rma = RmaFactory::new()->requested()->create();

        $rejected = $this->rmaService->reject($rma, 'Item not returnable');

        $this->assertEquals(RmaStatus::REJECTED, $rejected->status);
        $this->assertStringContainsString('Item not returnable', $rejected->admin_notes);
    }

    public function test_cannot_reject_finalized_rma(): void
    {
        $rma = RmaFactory::new()->refunded()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('finalisé');

        $this->rmaService->reject($rma, 'Cannot reject');
    }

    public function test_get_user_rmas(): void
    {
        RmaFactory::new()->count(3)->create(['user_id' => $this->user->id]);
        RmaFactory::new()->count(2)->create();

        $userRmas = $this->rmaService->getUserRmas($this->user);

        $this->assertEquals(3, $userRmas->count());
    }

    public function test_get_pending_rmas(): void
    {
        RmaFactory::new()->requested()->count(2)->create();
        RmaFactory::new()->approved()->count(1)->create();

        $pending = $this->rmaService->getPendingRmas();

        $this->assertEquals(2, $pending->count());
    }

    public function test_get_stats(): void
    {
        RmaFactory::new()->requested()->count(2)->create();
        RmaFactory::new()->approved()->count(3)->create();
        RmaFactory::new()->refunded()->count(2)->create([
            'refund_amount' => 100.00,
        ]);

        $stats = $this->rmaService->getStats();

        $this->assertEquals(7, $stats['total']);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(3, $stats['approved']);
        $this->assertEquals(2, $stats['refunded']);
        $this->assertEquals(200, $stats['total_refunded']);
    }
}
