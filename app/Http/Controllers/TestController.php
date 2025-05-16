<?php

namespace App\Http\Controllers;

use App\Facades\Supabase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Test the Supabase integration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testSupabase()
    {
        try {
            // Test direct Supabase query for products
            $products = Supabase::query('products', [
                'select' => ['id', 'name', 'price'],
                'limit' => 5
            ]);

            // Test Product model
            $productModels = Product::all(['id', 'name', 'price', 'stock_quantity'], true);

            // Test User model
            $userModels = User::all(['id', 'name', 'email', 'role'], true);

            return response()->json([
                'success' => true,
                'message' => 'Supabase integration is working correctly',
                'data' => [
                    'direct_query' => [
                        'products' => $products
                    ],
                    'models' => [
                        'products' => $productModels->toArray(),
                        'users' => $userModels->toArray()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Supabase test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Supabase integration test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
