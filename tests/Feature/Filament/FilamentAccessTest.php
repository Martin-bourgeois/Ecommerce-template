<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\OrderResource;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class FilamentAccessTest extends TestCase
{
    private User $adminUser;
    private User $staffUser;
    private User $clientUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'staff']);

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->staffUser = User::factory()->create();
        $this->staffUser->assignRole('staff');

        $this->clientUser = User::factory()->create();
    }

    public function test_client_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/admin');

        $this->assertTrue($response->status() === 403 || $response->status() === 302);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_staff_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get('/admin');

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_unauthenticated_user_cannot_access_admin(): void
    {
        $response = $this->get('/admin');

        $this->assertEquals(302, $response->status());
    }

    public function test_client_cannot_access_product_resource(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/admin/products');

        $this->assertTrue($response->status() === 403 || $response->status() === 302);
    }

    public function test_admin_can_access_product_resource(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products');

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_product_resource_exists(): void
    {
        $this->assertTrue(class_exists(ProductResource::class));
    }

    public function test_order_resource_exists(): void
    {
        $this->assertTrue(class_exists(OrderResource::class));
    }
}
