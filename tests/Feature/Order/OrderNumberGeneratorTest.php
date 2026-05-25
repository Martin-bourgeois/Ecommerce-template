<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderNumberGenerator;
use Tests\TestCase;

class OrderNumberGeneratorTest extends TestCase
{
    private OrderNumberGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = app(OrderNumberGenerator::class);
    }

    public function test_generates_valid_order_number_format(): void
    {
        $number = $this->generator->generate();

        $this->assertMatchesRegularExpression('/^ORD-\d{4}-\d{6}$/', $number);
    }

    public function test_generates_unique_order_numbers(): void
    {
        $number1 = $this->generator->generate();
        $number2 = $this->generator->generate();
        $number3 = $this->generator->generate();

        $this->assertNotEquals($number1, $number2);
        $this->assertNotEquals($number2, $number3);
        $this->assertNotEquals($number1, $number3);
    }

    public function test_order_numbers_increment_sequentially(): void
    {
        $this->generator->resetSequence();

        $number1 = $this->generator->generate();
        $number2 = $this->generator->generate();

        // Extract sequences
        preg_match('/ORD-\d{4}-(\d{6})/', $number1, $matches1);
        preg_match('/ORD-\d{4}-(\d{6})/', $number2, $matches2);

        $seq1 = intval($matches1[1]);
        $seq2 = intval($matches2[1]);

        $this->assertEquals($seq1 + 1, $seq2);
    }

    public function test_order_number_stored_in_database(): void
    {
        $number = $this->generator->generate();

        $order = Order::create([
            'order_number' => $number,
            'user_id' => 1,
            'status' => OrderStatus::PENDING_PAYMENT->value,
            'subtotal_cents' => 0,
            'shipping_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 0,
        ]);

        $this->assertTrue(Order::where('order_number', $number)->exists());
    }

    public function test_order_number_unique_constraint(): void
    {
        $number = $this->generator->generate();

        Order::create([
            'order_number' => $number,
            'user_id' => 1,
            'status' => OrderStatus::PENDING_PAYMENT->value,
            'subtotal_cents' => 0,
            'shipping_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 0,
        ]);

        $this->expectException(\Exception::class);

        Order::create([
            'order_number' => $number,
            'user_id' => 1,
            'status' => OrderStatus::PENDING_PAYMENT->value,
            'subtotal_cents' => 0,
            'shipping_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 0,
        ]);
    }
}
