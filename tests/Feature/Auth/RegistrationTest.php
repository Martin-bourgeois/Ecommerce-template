<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\CustomerProfile;
use App\Domains\Customer\Enums\UserStatus;
use App\Domains\Customer\Enums\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful registration.
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'subscribe_newsletter' => true,
        ]);

        // Check user was created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'status' => UserStatus::ACTIVE->value,
        ]);

        // Check customer profile was created
        $user = User::where('email', 'test@example.com')->first();
        $this->assertDatabaseHas('customer_profiles', [
            'user_id' => $user->id,
            'group' => CustomerGroup::STANDARD->value,
        ]);

        // Check customer role was assigned
        $this->assertTrue($user->hasRole('customer'));
    }

    /**
     * Test registration validation.
     */
    public function test_registration_validation(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);
    }

    /**
     * Test duplicate email rejection.
     */
    public function test_cannot_register_with_duplicate_email(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
