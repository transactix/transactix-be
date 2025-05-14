<?php

namespace App\Http\Controllers;

use App\Facades\Supabase;
use App\Models\Category;
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
            // Test direct Supabase query
            $categories = Supabase::query('categories', [
                'select' => ['id', 'name', 'slug'],
                'limit' => 5
            ]);

            // Test Category model
            $categoryModels = Category::all(['id', 'name', 'slug'], true);
            
            // Test Product model
            $productModels = Product::all(['id', 'name', 'price', 'stock_quantity'], true);
            
            // Test User model
            $userModels = User::all(['id', 'name', 'email', 'role'], true);
            
            return response()->json([
                'success' => true,
                'message' => 'Supabase integration is working correctly',
                'data' => [
                    'direct_query' => [
                        'categories' => $categories
                    ],
                    'models' => [
                        'categories' => $categoryModels->toArray(),
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
