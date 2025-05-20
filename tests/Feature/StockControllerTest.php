<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $admin;
    protected $cashier;
    protected $product;

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

        // Create a test product
        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Description for test product',
            'price' => 19.99,
            'stock_quantity' => 100,
            'sku' => 'TP001',
            'is_active' => true,
        ]);
    }

    /**
     * Test getting low stock products.
     */
    public function test_can_get_low_stock_products(): void
    {
        // Create some products with low stock
        Product::create([
            'name' => 'Low Stock Product 1',
            'description' => 'Description for low stock product 1',
            'price' => 29.99,
            'stock_quantity' => 5,
            'sku' => 'LSP001',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Low Stock Product 2',
            'description' => 'Description for low stock product 2',
            'price' => 39.99,
            'stock_quantity' => 8,
            'sku' => 'LSP002',
            'is_active' => true,
        ]);

        // Test that admin can get low stock products
        $response = $this->actingAs($this->admin)->getJson('/api/products/low-stock?threshold=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'threshold',
                    'count',
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'stock_quantity',
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'threshold' => 10,
                    'count' => 2,
                ]
            ]);

        // Test that cashier can also get low stock products
        $response = $this->actingAs($this->cashier)->getJson('/api/products/low-stock?threshold=10');
        $response->assertStatus(200);
    }

    /**
     * Test updating stock quantity.
     */
    public function test_admin_can_update_stock(): void
    {
        $data = [
            'stock_quantity' => 150,
        ];

        // Test that admin can update stock
        $response = $this->actingAs($this->admin)->putJson('/api/products/' . $this->product->id . '/stock', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'product_id',
                    'product_name',
                    'old_quantity',
                    'new_quantity',
                    'difference',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'old_quantity' => 100,
                    'new_quantity' => 150,
                    'difference' => 50,
                ]
            ]);

        // Check that the stock was actually updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 150,
        ]);
    }

    /**
     * Test cashier cannot update stock.
     */
    public function test_cashier_cannot_update_stock(): void
    {
        $data = [
            'stock_quantity' => 150,
        ];

        // Test that cashier cannot update stock
        $response = $this->actingAs($this->cashier)->putJson('/api/products/' . $this->product->id . '/stock', $data);

        $response->assertStatus(403);

        // Check that the stock was not updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 100, // Original value
        ]);
    }

    /**
     * Test increasing stock quantity.
     */
    public function test_admin_can_increase_stock(): void
    {
        $data = [
            'quantity' => 50,
        ];

        // Test that admin can increase stock
        $response = $this->actingAs($this->admin)->putJson('/api/products/' . $this->product->id . '/stock/increase', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'product_id',
                    'product_name',
                    'old_quantity',
                    'new_quantity',
                    'added',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Stock increased successfully',
                'data' => [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'old_quantity' => 100,
                    'new_quantity' => 150,
                    'added' => 50,
                ]
            ]);

        // Check that the stock was actually increased in the database
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 150,
        ]);
    }

    /**
     * Test decreasing stock quantity.
     */
    public function test_admin_can_decrease_stock(): void
    {
        $data = [
            'quantity' => 30,
        ];

        // Test that admin can decrease stock
        $response = $this->actingAs($this->admin)->putJson('/api/products/' . $this->product->id . '/stock/decrease', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'product_id',
                    'product_name',
                    'old_quantity',
                    'new_quantity',
                    'removed',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Stock decreased successfully',
                'data' => [
                    'product_id' => $this->product->id,
                    'product_name' => 'Test Product',
                    'old_quantity' => 100,
                    'new_quantity' => 70,
                    'removed' => 30,
                ]
            ]);

        // Check that the stock was actually decreased in the database
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 70,
        ]);
    }

    /**
     * Test cashier can decrease stock.
     */
    public function test_cashier_can_decrease_stock(): void
    {
        $data = [
            'quantity' => 20,
        ];

        // Test that cashier can decrease stock
        $response = $this->actingAs($this->cashier)->putJson('/api/products/' . $this->product->id . '/stock/decrease', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'product_id',
                    'product_name',
                    'old_quantity',
                    'new_quantity',
                    'removed',
                ]
            ]);

        // Check that the stock was actually decreased in the database
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 80,
        ]);
    }

    /**
     * Test cannot decrease stock below zero.
     */
    public function test_cannot_decrease_stock_below_zero(): void
    {
        $data = [
            'quantity' => 150, // More than available stock
        ];

        // Test that stock cannot be decreased below zero
        $response = $this->actingAs($this->admin)->putJson('/api/products/' . $this->product->id . '/stock/decrease', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'product_id',
                    'product_name',
                    'current_stock',
                    'requested_quantity',
                ]
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient stock',
            ]);

        // Check that the stock was not changed in the database
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 100, // Original value
        ]);
    }
}
