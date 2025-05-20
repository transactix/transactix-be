<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can access admin routes.
     */
    public function test_admin_can_access_admin_routes(): void
    {
        // Create an admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password123!',
            'role' => 'admin',
        ]);

        // Create a test product
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10.99,
            'stock_quantity' => 100,
            'sku' => 'TEST001',
            'is_active' => true,
        ];

        // Test admin can create a product
        $response = $this->actingAs($admin)
            ->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
            ]);

        // Get the product ID from the response
        $productId = $response->json('data.id');

        // Test admin can update a product
        $updateResponse = $this->actingAs($admin)
            ->putJson("/api/products/{$productId}", [
                'name' => 'Updated Product',
                'price' => 15.99,
            ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully',
            ]);

        // Test admin can delete a product
        $deleteResponse = $this->actingAs($admin)
            ->deleteJson("/api/products/{$productId}");

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
    }

    /**
     * Test cashier cannot access admin routes.
     */
    public function test_cashier_cannot_access_admin_routes(): void
    {
        // Create a cashier user
        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        // Create a test product
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10.99,
            'stock_quantity' => 100,
            'sku' => 'TEST001',
            'is_active' => true,
        ];

        // Test cashier cannot create a product
        $response = $this->actingAs($cashier)
            ->postJson('/api/products', $productData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. This action requires admin privileges.',
            ]);

        // Create a product as admin for testing update and delete
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password123!',
            'role' => 'admin',
        ]);

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/products', $productData);

        $productId = $createResponse->json('data.id');

        // Test cashier cannot update a product
        $updateResponse = $this->actingAs($cashier)
            ->putJson("/api/products/{$productId}", [
                'name' => 'Updated Product',
                'price' => 15.99,
            ]);

        $updateResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. This action requires admin privileges.',
            ]);

        // Test cashier cannot delete a product
        $deleteResponse = $this->actingAs($cashier)
            ->deleteJson("/api/products/{$productId}");

        $deleteResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. This action requires admin privileges.',
            ]);
    }

    /**
     * Test cashier can access cashier routes.
     */
    public function test_cashier_can_access_cashier_routes(): void
    {
        // Create a cashier user
        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'password' => 'Password123!',
            'role' => 'cashier',
        ]);

        // Create a product as admin for testing
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password123!',
            'role' => 'admin',
        ]);

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10.99,
            'stock_quantity' => 100,
            'sku' => 'TEST001',
            'is_active' => true,
        ];

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/products', $productData);

        $productId = $createResponse->json('data.id');

        // Test cashier can view products
        $viewResponse = $this->actingAs($cashier)
            ->getJson('/api/products');

        $viewResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock_quantity',
                    ],
                ],
            ]);

        // Test cashier can decrease stock
        $decreaseResponse = $this->actingAs($cashier)
            ->putJson("/api/products/{$productId}/stock/decrease", [
                'quantity' => 5,
            ]);

        $decreaseResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
