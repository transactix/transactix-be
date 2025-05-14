<?php

namespace App\Models;

class Category extends SupabaseModel
{
    /**
     * The Supabase table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the products for the category.
     *
     * @return \Illuminate\Support\Collection
     */
    public function products()
    {
        return Product::where('category_id', $this->id);
    }

    /**
     * Get active categories.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function active()
    {
        return self::where('active', true);
    }

    /**
     * Find a category by its slug.
     *
     * @param string $slug
     * @return static|null
     */
    public static function findBySlug(string $slug)
    {
        $instance = new static;
        $result = \App\Facades\Supabase::query(
            $instance->table,
            [
                'where' => ['slug' => $slug],
                'limit' => 1
            ]
        );
        
        if (empty($result)) {
            return null;
        }
        
        return new static($result[0]);
    }
}
