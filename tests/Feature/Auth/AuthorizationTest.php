<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'customer']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'admin']);
    }

    /**
     * Test customer can access customer routes.
     */
    public function test_customer_can_access_customer_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get('/customer/dashboard')
            ->assertOk();
    }

    /**
     * Test customer cannot access admin routes.
     */
    public function test_customer_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    /**
     * Test staff can access staff routes.
     */
    public function test_staff_can_access_staff_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $this->actingAs($user)
            ->get('/admin/products')
            ->assertOk();
    }

    /**
     * Test staff cannot access settings.
     */
    public function test_staff_cannot_access_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertForbidden();
    }

    /**
     * Test admin can access all routes.
     */
    public function test_admin_can_access_all_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertOk();
    }

    /**
     * Test unauthenticated user redirected to login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect('/login');
    }

    /**
     * Test suspended user cannot access routes.
     */
    public function test_suspended_user_cannot_access_routes(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get('/customer/dashboard')
            ->assertRedirect('/login');
    }
}
