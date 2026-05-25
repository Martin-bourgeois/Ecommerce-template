<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(UserPolicy::class);
    }

    /**
     * Test user can view own profile.
     */
    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->view($user, $user));
    }

    /**
     * Test user cannot view other profile.
     */
    public function test_user_cannot_view_other_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertFalse($this->policy->view($user1, $user2));
    }

    /**
     * Test admin can view any profile.
     */
    public function test_admin_can_view_any_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $this->assertTrue($this->policy->view($admin, $user));
    }

    /**
     * Test user can edit own profile.
     */
    public function test_user_can_edit_own_profile(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->edit($user, $user));
    }

    /**
     * Test user cannot edit other profile.
     */
    public function test_user_cannot_edit_other_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertFalse($this->policy->edit($user1, $user2));
    }

    /**
     * Test admin cannot suspend self.
     */
    public function test_admin_cannot_suspend_self(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertFalse($this->policy->suspend($admin, $admin));
    }

    /**
     * Test admin can suspend other user.
     */
    public function test_admin_can_suspend_other_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $this->assertTrue($this->policy->suspend($admin, $user));
    }
}
