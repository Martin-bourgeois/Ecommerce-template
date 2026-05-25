<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\CustomerProfile;
use App\Domains\Customer\Enums\UserStatus;
use App\Domains\Customer\Enums\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user is active.
     */
    public function test_user_is_active(): void
    {
        $user = User::factory()->create(['status' => UserStatus::ACTIVE]);

        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isSuspended());
    }

    /**
     * Test user is suspended.
     */
    public function test_user_is_suspended(): void
    {
        $user = User::factory()->create(['status' => UserStatus::SUSPENDED]);

        $this->assertFalse($user->isActive());
        $this->assertTrue($user->isSuspended());
    }

    /**
     * Test user is banned.
     */
    public function test_user_is_banned(): void
    {
        $user = User::factory()->create(['status' => UserStatus::BANNED]);

        $this->assertFalse($user->isActive());
        $this->assertTrue($user->isSuspended());
    }

    /**
     * Test user has customer role.
     */
    public function test_user_has_customer_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->assertTrue($user->isCustomer());
        $this->assertFalse($user->isStaff());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test user has staff role.
     */
    public function test_user_has_staff_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $this->assertFalse($user->isCustomer());
        $this->assertTrue($user->isStaff());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test user has admin role.
     */
    public function test_user_has_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertFalse($user->isCustomer());
        $this->assertFalse($user->isStaff());
        $this->assertTrue($user->isAdmin());
    }

    /**
     * Test user has customer profile.
     */
    public function test_user_has_customer_profile(): void
    {
        $user = User::factory()->create();
        CustomerProfile::create([
            'user_id' => $user->id,
            'group' => CustomerGroup::STANDARD->value,
        ]);

        $this->assertNotNull($user->customerProfile);
        $this->assertEquals(CustomerGroup::STANDARD, $user->customerProfile->group);
    }

    /**
     * Test active scope.
     */
    public function test_active_scope(): void
    {
        User::factory()->create(['status' => UserStatus::ACTIVE]);
        User::factory()->create(['status' => UserStatus::SUSPENDED]);
        User::factory()->create(['status' => UserStatus::BANNED]);

        $activeUsers = User::active()->get();

        $this->assertEquals(1, $activeUsers->count());
    }
}
