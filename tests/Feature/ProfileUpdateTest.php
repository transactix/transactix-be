<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can update profile.
     */
    public function test_user_can_update_profile(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        // Update name
        $response = $this->actingAs($user)
            ->putJson('/api/user/profile', [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'name' => 'Updated Name',
                        'email' => 'test@example.com',
                    ],
                ],
            ]);

        // Update email
        $emailResponse = $this->actingAs($user)
            ->putJson('/api/user/profile', [
                'email' => 'updated@example.com',
            ]);

        $emailResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'name' => 'Updated Name',
                        'email' => 'updated@example.com',
                    ],
                ],
            ]);

        // Update password
        $passwordResponse = $this->actingAs($user)
            ->putJson('/api/user/profile', [
                'current_password' => 'Password123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $passwordResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        // Verify login with new password works
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'updated@example.com',
            'password' => 'NewPassword123!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);
    }

    /**
     * Test user cannot update password with incorrect current password.
     */
    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        // Try to update password with incorrect current password
        $response = $this->actingAs($user)
            ->putJson('/api/user/profile', [
                'current_password' => 'WrongPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect',
            ]);
    }

    /**
     * Test user cannot update email to an existing email.
     */
    public function test_user_cannot_update_email_to_existing_email(): void
    {
        // Create two users
        $user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        // Try to update user1's email to user2's email
        $response = $this->actingAs($user1)
            ->putJson('/api/user/profile', [
                'email' => 'test2@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
