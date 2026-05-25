<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Domains\Customer\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login.
     */
    public function test_user_can_login(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => UserStatus::ACTIVE->value,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    /**
     * Test suspended user cannot login.
     */
    public function test_suspended_user_cannot_login(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => UserStatus::SUSPENDED->value,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    /**
     * Test remember me functionality.
     */
    public function test_remember_me_stores_token(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertNotNull($user->fresh()->remember_token);
    }

    /**
     * Test user not found.
     */
    public function test_login_fails_with_nonexistent_user(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }
}
