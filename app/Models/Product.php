<?php

namespace App\Models;

class Product extends SupabaseModel
{
    /**
     * The Supabase table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'category_id',
        'sku',
        'barcode',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'stock_quantity' => 'integer',
        'category_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category that owns the product.
     *
     * @return \App\Models\Category|null
     */
    public function category()
    {
        return Category::find($this->category_id);
    }

    /**
     * Get products by category ID.
     *
     * @param int $categoryId
     * @return \Illuminate\Support\Collection
     */
    public static function findByCategory(int $categoryId)
    {
        return self::where('category_id', $categoryId);
    }

    /**
     * Get active products.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function active()
    {
        return self::where('is_active', true);
    }

    /**
     * Get products with low stock.
     *
     * @param int $threshold
     * @return \Illuminate\Support\Collection
     */
    public static function lowStock(int $threshold = 10)
    {
        $instance = new static;
        $results = \App\Facades\Supabase::query(
            $instance->table,
            [
                'where' => [
                    'stock_quantity' => 'lt.' . $threshold,
                    'is_active' => 'eq.true'
                ]
            ]
        );
        
        if (empty($results)) {
            return collect();
        }
        
        return collect(array_map(function ($item) {
            return new static($item);
        }, $results));
    }

    /**
     * Update the stock quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function updateStock(int $quantity)
    {
        return $this->update([
            'stock_quantity' => $quantity
        ]);
    }

    /**
     * Decrease the stock quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function decreaseStock(int $quantity)
    {
        if ($this->stock_quantity < $quantity) {
            return false;
        }
        
        return $this->updateStock($this->stock_quantity - $quantity);
    }

    /**
     * Increase the stock quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function increaseStock(int $quantity)
    {
        return $this->updateStock($this->stock_quantity + $quantity);
    }
}
