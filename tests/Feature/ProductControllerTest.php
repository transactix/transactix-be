<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $admin;
    protected $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin and cashier users for testing
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
        ]);
    }

    /**
     * Test listing products.
     */
    public function test_admin_can_list_products(): void
    {
        // Create some products
        Product::create([
            'name' => 'Test Product 1',
            'description' => 'Description for test product 1',
            'price' => 19.99,
            'stock_quantity' => 100,
            'sku' => 'TP001',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Test Product 2',
            'description' => 'Description for test product 2',
            'price' => 29.99,
            'stock_quantity' => 50,
            'sku' => 'TP002',
            'is_active' => true,
        ]);

        // Test that admin can list products
        $response = $this->actingAs($this->admin)->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock_quantity',
                        'sku',
                        'is_active',
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test cashier can list products.
     */
    public function test_cashier_can_list_products(): void
    {
        // Create a product
        Product::create([
            'name' => 'Test Product',
            'description' => 'Description for test product',
            'price' => 19.99,
            'stock_quantity' => 100,
            'sku' => 'TP001',
            'is_active' => true,
        ]);

        // Test that cashier can list products
        $response = $this->actingAs($this->cashier)->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock_quantity',
                        'sku',
                        'is_active',
                    ]
                ]
            ]);
    }

    /**
     * Test showing a specific product.
     */
    public function test_can_show_product(): void
    {
        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Description for test product',
            'price' => 19.99,
            'stock_quantity' => 100,
            'sku' => 'TP001',
            'is_active' => true,
        ]);

        // Test that admin can view the product
        $response = $this->actingAs($this->admin)->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'sku',
                    'is_active',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                    'price' => 19.99,
                    'stock_quantity' => 100,
                    'sku' => 'TP001',
                ]
            ]);
    }

    /**
     * Test creating a product.
     */
    public function test_admin_can_create_product(): void
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'Description for new product',
            'price' => 29.99,
            'stock_quantity' => 50,
            'sku' => 'NP001',
            'is_active' => true,
        ];

        // Test that admin can create a product
        $response = $this->actingAs($this->admin)->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'sku',
                    'is_active',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $productData
            ]);

        // Check that the product was actually created in the database
        $this->assertDatabaseHas('products', $productData);
    }

    /**
     * Test cashier cannot create a product.
     */
    public function test_cashier_cannot_create_product(): void
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'Description for new product',
            'price' => 29.99,
            'stock_quantity' => 50,
            'sku' => 'NP001',
            'is_active' => true,
        ];

        // Test that cashier cannot create a product
        $response = $this->actingAs($this->cashier)->postJson('/api/products', $productData);

        $response->assertStatus(403);

        // Check that the product was not created in the database
        $this->assertDatabaseMissing('products', $productData);
    }

    /**
     * Test updating a product.
     */
    public function test_admin_can_update_product(): void
    {
        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Description for test product',
            'price' => 19.99,
            'stock_quantity' => 100,
            'sku' => 'TP001',
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'price' => 24.99,
        ];

        // Test that admin can update the product
        $response = $this->actingAs($this->admin)->putJson('/api/products/' . $product->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'sku',
                    'is_active',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'name' => 'Updated Product',
                    'price' => 24.99,
                ]
            ]);

        // Check that the product was actually updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 24.99,
        ]);
    }

    /**
     * Test deleting a product.
     */
    public function test_admin_can_delete_product(): void
    {
        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Description for test product',
            'price' => 19.99,
            'stock_quantity' => 100,
            'sku' => 'TP001',
            'is_active' => true,
        ]);

        // Test that admin can delete the product
        $response = $this->actingAs($this->admin)->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);

        // Check that the product was actually deleted from the database
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
