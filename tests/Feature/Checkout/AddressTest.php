<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use Tests\TestCase;
use App\Domains\Checkout\Models\Address;
use App\Domains\Checkout\Models\ShippingZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test can create address for user.
     */
    public function test_can_create_address_for_user(): void
    {
        $address = $this->user->addresses()->create([
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

        $this->assertNotNull($address->id);
        $this->assertEquals('John', $address->first_name);
    }

    /**
     * Test user can have multiple addresses.
     */
    public function test_user_can_have_multiple_addresses(): void
    {
        $this->user->addresses()->create([
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

        $this->user->addresses()->create([
            'type' => 'billing',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '0687654321',
            'street_address' => '456 Rue de la Liberté',
            'city' => 'Lyon',
            'postal_code' => '69000',
            'country' => 'FR',
        ]);

        $this->assertCount(2, $this->user->addresses);
    }

    /**
     * Test can mark address as default.
     */
    public function test_can_mark_address_as_default(): void
    {
        $address1 = $this->user->addresses()->create([
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

        $address2 = $this->user->addresses()->create([
            'type' => 'shipping',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '0687654321',
            'street_address' => '456 Rue de la Liberté',
            'city' => 'Lyon',
            'postal_code' => '69000',
            'country' => 'FR',
        ]);

        $address2->markAsDefaultShipping();

        $this->assertTrue($address2->fresh()->is_default);
        $this->assertFalse($address1->fresh()->is_default);
    }

    /**
     * Test formatted address output.
     */
    public function test_formatted_address_output(): void
    {
        $address = $this->user->addresses()->create([
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

        $formatted = $address->formatted_address;

        $this->assertStringContainsString('123 Rue de la Paix', $formatted);
        $this->assertStringContainsString('75001 Paris', $formatted);
        $this->assertStringContainsString('FR', $formatted);
    }

    /**
     * Test full name generation.
     */
    public function test_full_name_generation(): void
    {
        $address = $this->user->addresses()->create([
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

        $this->assertEquals('John Doe', $address->full_name);
    }
}
