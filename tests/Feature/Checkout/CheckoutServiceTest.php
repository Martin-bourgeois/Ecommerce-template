<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use Tests\TestCase;
use App\Domains\Catalog\Models\Product;
use App\Domains\Cart\Services\CartService;
use App\Domains\Checkout\Models\Address;
use App\Domains\Checkout\Models\ShippingZone;
use App\Domains\Checkout\Services\CheckoutService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutService $checkoutService;
    private CartService $cartService;
    private User $user;
    private Address $shippingAddress;
    private ShippingZone $shippingZone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutService = app(CheckoutService::class);
        $this->cartService = app(CartService::class);

        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create shipping zone
        $this->shippingZone = ShippingZone::create([
            'name' => 'France',
            'countries' => ['FR'],
            'base_cost' => 500, // 5€
            'weight_cost' => 100, // 1€ per kg
            'free_shipping_threshold' => 5000, // Free above 50€
            'is_active' => true,
        ]);

        // Create shipping address
        $this->shippingAddress = $this->user->addresses()->create([
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '0612345678',
            'street_address' => '123 Rue de la Paix',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'FR',
        ]);
    }

    /**
     * Test cannot checkout with empty cart.
     */
    public function test_cannot_checkout_with_empty_cart(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart is empty');

        $this->checkoutService->validate();
    }

    /**
     * Test cannot checkout without shipping address.
     */
    public function test_cannot_checkout_without_shipping_address(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $this->cartService->add($product->sku, 1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Shipping address is required');

        $this->checkoutService->validate();
    }

    /**
     * Test valid checkout passes validation.
     */
    public function test_valid_checkout_passes_validation(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $this->cartService->add($product->sku, 1);

        $this->checkoutService->setShippingAddress($this->shippingAddress);

        $this->assertTrue($this->checkoutService->validate());
    }

    /**
     * Test shipping cost calculation.
     */
    public function test_shipping_cost_calculation(): void
    {
        $product = Product::factory()->create([
            'status' => 'published',
            'type' => 'simple',
            'price' => 1000, // 10€
        ]);
        $this->cartService->add($product->sku, 1);

        $this->checkoutService->setShippingAddress($this->shippingAddress);

        $shippingCost = $this->checkoutService->calculateShipping();

        // Should be base cost (500 cents) + weight cost (0 for now)
        $this->assertEquals(500, $shippingCost);
    }

    /**
     * Test free shipping threshold.
     */
    public function test_free_shipping_at_threshold(): void
    {
        // Update shipping zone to have lower threshold
        $this->shippingZone->update([
            'free_shipping_threshold' => 1000, // Free above 10€
        ]);

        // Add product worth 20€
        $product = Product::factory()->create([
            'status' => 'published',
            'type' => 'simple',
            'price' => 2000, // 20€
        ]);
        $this->cartService->add($product->sku, 1);

        $this->checkoutService->setShippingAddress($this->shippingAddress);

        $shippingCost = $this->checkoutService->calculateShipping();

        // Should be free (order total >= threshold)
        $this->assertEquals(0, $shippingCost);
    }

    /**
     * Test checkout summary contains correct data.
     */
    public function test_checkout_summary_contains_correct_data(): void
    {
        $product = Product::factory()->create([
            'status' => 'published',
            'type' => 'simple',
            'price' => 1000, // 10€
        ]);
        $this->cartService->add($product->sku, 2);

        $this->checkoutService->setShippingAddress($this->shippingAddress);

        $summary = $this->checkoutService->getSummary();

        $this->assertEquals(2000, $summary->subtotalCents); // 20€
        $this->assertEquals(500, $summary->shippingCents); // 5€
        $this->assertGreaterThan(0, $summary->totalCents); // Total > 0
        $this->assertEquals(2, $summary->itemCount);
    }

    /**
     * Test can set same address for billing.
     */
    public function test_can_set_same_address_for_billing(): void
    {
        $this->checkoutService->setShippingAddress($this->shippingAddress);
        $this->checkoutService->setBillingAddress($this->shippingAddress);

        $this->assertEquals($this->shippingAddress->id, $this->checkoutService->getShippingAddress()->id);
        $this->assertEquals($this->shippingAddress->id, $this->checkoutService->getBillingAddress()->id);
    }

    /**
     * Test invalid country raises error.
     */
    public function test_invalid_country_raises_error(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $this->cartService->add($product->sku, 1);

        // Create address in unsupported country
        $invalidAddress = $this->user->addresses()->create([
            'type' => 'shipping',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '+1234567890',
            'street_address' => '456 Main St',
            'city' => 'Unknown City',
            'postal_code' => '12345',
            'country' => 'XX', // Invalid country
        ]);

        $this->checkoutService->setShippingAddress($invalidAddress);

        $this->expectException(\InvalidArgumentException::class);
        $this->checkoutService->calculateShipping();
    }

    /**
     * Test clear checkout state.
     */
    public function test_can_clear_checkout_state(): void
    {
        $this->checkoutService->setShippingAddress($this->shippingAddress);

        $this->assertNotNull($this->checkoutService->getShippingAddress());

        $this->checkoutService->clear();

        $this->assertNull($this->checkoutService->getShippingAddress());
    }
}
