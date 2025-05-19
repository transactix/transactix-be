<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    /**
     * Update the stock quantity of a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStock(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldQuantity = $product->stock_quantity;
        $newQuantity = $request->stock_quantity;
        
        $product->stock_quantity = $newQuantity;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'difference' => $newQuantity - $oldQuantity
            ]
        ]);
    }

    /**
     * Increase the stock quantity of a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function increaseStock(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldQuantity = $product->stock_quantity;
        $product->stock_quantity += $request->quantity;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock increased successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $product->stock_quantity,
                'added' => $request->quantity
            ]
        ]);
    }

    /**
     * Decrease the stock quantity of a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function decreaseStock(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'current_stock' => $product->stock_quantity,
                    'requested_quantity' => $request->quantity
                ]
            ], 422);
        }

        $oldQuantity = $product->stock_quantity;
        $product->stock_quantity -= $request->quantity;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock decreased successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $product->stock_quantity,
                'removed' => $request->quantity
            ]
        ]);
    }

    /**
     * Get products with low stock.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lowStock(Request $request)
    {
        $threshold = $request->threshold ?? 10; // Default threshold is 10
        
        $products = Product::where('stock_quantity', '<', $threshold)
            ->where('is_active', true)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'threshold' => $threshold,
                'count' => $products->count(),
                'products' => $products
            ]
        ]);
    }
}
