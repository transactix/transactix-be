<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sending password reset link.
     */
    public function test_can_send_password_reset_link(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        $response = $this->postJson('/api/password/email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'email',
                ],
            ]);
    }

    /**
     * Test resetting password with valid token.
     */
    public function test_can_reset_password_with_valid_token(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        // Request password reset
        $emailResponse = $this->postJson('/api/password/email', [
            'email' => 'test@example.com',
        ]);

        $token = $emailResponse->json('data.token');

        // Reset password
        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);

        // Try to login with new password
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);
    }

    /**
     * Test resetting password with invalid token.
     */
    public function test_cannot_reset_password_with_invalid_token(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        // Reset password with invalid token
        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid token',
            ]);
    }
}
