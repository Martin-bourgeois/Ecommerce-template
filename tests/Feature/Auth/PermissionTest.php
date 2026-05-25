<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user with permission can perform action.
     */
    public function test_user_with_permission_can_perform_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get('/admin/products/create')
            ->assertOk();
    }

    /**
     * Test user without permission cannot perform action.
     */
    public function test_user_without_permission_cannot_perform_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get('/admin/products/create')
            ->assertForbidden();
    }

    /**
     * Test permission inheritance through role.
     */
    public function test_permission_inherited_through_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        // Staff should have products.view permission
        $this->actingAs($user)
            ->get('/admin/products')
            ->assertOk();
    }
}
