<?php

namespace App\Models;

use App\Facades\Supabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class SupabaseModel extends Model
{
    /**
     * The Supabase table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Create a new model instance.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Set the table name if not already set
        if (!isset($this->table)) {
            $this->table = $this->getTable();
        }
    }

    /**
     * Find a model by its primary key.
     *
     * @param mixed $id
     * @param bool $useServiceRole
     * @return static|null
     */
    public static function find($id, bool $useServiceRole = false)
    {
        $instance = new static;
        $result = Supabase::query(
            $instance->table,
            [
                'where' => [$instance->primaryKey => $id],
                'limit' => 1
            ],
            $useServiceRole
        );

        if (empty($result)) {
            return null;
        }

        return new static($result[0]);
    }

    /**
     * Get all models.
     *
     * @param array $columns
     * @param bool $useServiceRole
     * @return \Illuminate\Support\Collection
     */
    public static function all($columns = ['*'], bool $useServiceRole = false)
    {
        $instance = new static;
        $options = [];

        if ($columns !== ['*']) {
            $options['select'] = $columns;
        }

        $results = Supabase::query($instance->table, $options, $useServiceRole);

        if (empty($results)) {
            return collect();
        }

        return collect(array_map(function ($item) {
            return new static($item);
        }, $results));
    }

    /**
     * Create a new model in the database.
     *
     * @param array $attributes
     * @param bool $useServiceRole
     * @return static|null
     */
    public static function create(array $attributes, bool $useServiceRole = false)
    {
        $instance = new static($attributes);

        // Add timestamps if enabled
        if ($instance->timestamps) {
            $now = now()->toDateTimeString();
            $attributes['created_at'] = $now;
            $attributes['updated_at'] = $now;
        }

        $result = Supabase::insert($instance->table, $attributes, $useServiceRole);

        if (empty($result)) {
            return null;
        }

        return new static($result[0]);
    }

    /**
     * Update the model in the database.
     *
     * @param array $attributes
     * @param bool $useServiceRole
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        // Add updated_at timestamp if enabled
        if ($this->timestamps) {
            $attributes['updated_at'] = now()->toDateTimeString();
        }

        $result = Supabase::update(
            $this->table,
            $attributes,
            [$this->primaryKey => $this->{$this->primaryKey}],
            $options['useServiceRole'] ?? false
        );

        if (empty($result)) {
            return false;
        }

        // Update the model attributes
        foreach ($result[0] as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return true;
    }

    /**
     * Delete the model from the database.
     *
     * @param bool $useServiceRole
     * @return bool
     */
    public function delete(bool $useServiceRole = false)
    {
        return Supabase::delete(
            $this->table,
            [$this->primaryKey => $this->{$this->primaryKey}],
            $useServiceRole
        );
    }

    /**
     * Get models where the column value matches the given value.
     *
     * @param string $column
     * @param mixed $value
     * @param bool $useServiceRole
     * @return \Illuminate\Support\Collection
     */
    public static function where(string $column, $value, bool $useServiceRole = false)
    {
        $instance = new static;
        $results = Supabase::query(
            $instance->table,
            [
                'where' => [$column => $value]
            ],
            $useServiceRole
        );

        if (empty($results)) {
            return collect();
        }

        return collect(array_map(function ($item) {
            return new static($item);
        }, $results));
    }
}
