# Supabase Integration for Transactix

This document provides instructions for setting up and using Supabase with the Transactix Laravel backend.

## Setup Instructions

### 1. Environment Configuration

Add the following environment variables to your `.env` file:

```
SUPABASE_URL=your-supabase-project-url
SUPABASE_KEY=your-supabase-anon-key
SUPABASE_SECRET=your-supabase-service-role-key
```

You can find these values in your Supabase project dashboard under Project Settings > API.

### 2. Database Setup

Run the migrations to create the necessary tables in your Supabase database:

```bash
php artisan migrate
```

This will create the following tables:
- users
- categories
- products
- password_reset_tokens
- sessions
- personal_access_tokens
- jobs
- job_batches
- failed_jobs
- cache
- cache_locks

### 3. Row-Level Security (RLS) Policies

For proper security, you should set up Row-Level Security policies in your Supabase database. Here are some recommended policies:

#### Users Table

```sql
-- Enable RLS
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Admin can do anything
CREATE POLICY "Admins can do anything" ON users
  USING (auth.uid() IN (SELECT id FROM users WHERE role = 'admin'))
  WITH CHECK (auth.uid() IN (SELECT id FROM users WHERE role = 'admin'));

-- Users can read their own data
CREATE POLICY "Users can read their own data" ON users
  FOR SELECT USING (auth.uid() = id);

-- Users can update their own data
CREATE POLICY "Users can update their own data" ON users
  FOR UPDATE USING (auth.uid() = id);
```

#### Products Table

```sql
-- Enable RLS
ALTER TABLE products ENABLE ROW LEVEL SECURITY;

-- Admin can do anything
CREATE POLICY "Admins can do anything with products" ON products
  USING (auth.uid() IN (SELECT id FROM users WHERE role = 'admin'))
  WITH CHECK (auth.uid() IN (SELECT id FROM users WHERE role = 'admin'));

-- Cashiers can read products
CREATE POLICY "Cashiers can read products" ON products
  FOR SELECT USING (auth.uid() IN (SELECT id FROM users WHERE role = 'cashier'));

-- Cashiers can update stock
CREATE POLICY "Cashiers can update stock" ON products
  FOR UPDATE USING (
    auth.uid() IN (SELECT id FROM users WHERE role = 'cashier')
  )
  WITH CHECK (
    auth.uid() IN (SELECT id FROM users WHERE role = 'cashier')
  );
```

#### Categories Table

```sql
-- Enable RLS
ALTER TABLE categories ENABLE ROW LEVEL SECURITY;

-- Admin can do anything
CREATE POLICY "Admins can do anything with categories" ON categories
  USING (auth.uid() IN (SELECT id FROM users WHERE role = 'admin'))
  WITH CHECK (auth.uid() IN (SELECT id FROM users WHERE role = 'admin'));

-- Cashiers can read categories
CREATE POLICY "Cashiers can read categories" ON categories
  FOR SELECT USING (auth.uid() IN (SELECT id FROM users WHERE role = 'cashier'));
```

## Using the Supabase Client

The Supabase client is available through the `Supabase` facade or by injecting the `SupabaseClient` class.

### Using the Facade

```php
use App\Facades\Supabase;

// Query data
$products = Supabase::query('products', [
    'select' => ['id', 'name', 'price', 'stock_quantity'],
    'where' => ['category_id' => 1],
    'order' => 'name.asc',
    'limit' => 10
]);

// Insert data
$newProduct = Supabase::insert('products', [
    'name' => 'New Product',
    'description' => 'Product description',
    'price' => 19.99,
    'stock_quantity' => 100,
    'category_id' => 1,
    'sku' => 'NP001',
    'is_active' => true
]);

// Update data
$updatedProduct = Supabase::update('products', 
    ['price' => 24.99], 
    ['id' => 1]
);

// Delete data
$deleted = Supabase::delete('products', ['id' => 1]);

// Execute RPC function
$result = Supabase::rpc('get_low_stock_products', ['threshold' => 10]);
```

### Using the Models

The project includes a `SupabaseModel` base class that provides Eloquent-like functionality for Supabase tables.

```php
use App\Models\Product;
use App\Models\Category;
use App\Models\User;

// Find a product by ID
$product = Product::find(1);

// Get all products
$allProducts = Product::all();

// Create a new product
$newProduct = Product::create([
    'name' => 'New Product',
    'description' => 'Product description',
    'price' => 19.99,
    'stock_quantity' => 100,
    'category_id' => 1,
    'sku' => 'NP001',
    'is_active' => true
]);

// Update a product
$product->update(['price' => 24.99]);

// Delete a product
$product->delete();

// Find products by category
$categoryProducts = Product::findByCategory(1);

// Get active products
$activeProducts = Product::active();

// Get products with low stock
$lowStockProducts = Product::lowStock(10);
```

## Authentication

The Transactix backend uses Laravel Sanctum for API authentication. The authentication system is integrated with Supabase through the custom User model.

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create a new user
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password',
    'role' => 'cashier'
]);

// Find a user by email
$user = User::findByEmail('john@example.com');

// Check if a user is an admin
if ($user->isAdmin()) {
    // Admin-specific logic
}

// Check if a user is a cashier
if ($user->isCashier()) {
    // Cashier-specific logic
}
```

## Troubleshooting

### Common Issues

1. **Connection Issues**: Make sure your Supabase URL and API keys are correct in the `.env` file.

2. **Permission Errors**: Check your Row-Level Security policies in Supabase.

3. **Missing Tables**: Run `php artisan migrate` to create the necessary tables.

4. **Query Errors**: Check the Laravel logs for detailed error messages from Supabase.

### Debugging

You can enable debug logging for Supabase queries by setting the `LOG_LEVEL` to `debug` in your `.env` file:

```
LOG_LEVEL=debug
```

This will log all Supabase API requests and responses to the Laravel log file.
