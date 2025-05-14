<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|null query(string $table, array $options = [], bool $useServiceRole = false)
 * @method static array|null insert(string $table, array $data, bool $useServiceRole = false)
 * @method static array|null update(string $table, array $data, array $conditions, bool $useServiceRole = false)
 * @method static bool delete(string $table, array $conditions, bool $useServiceRole = false)
 * @method static array|null rpc(string $function, array $params = [])
 * 
 * @see \App\Services\Supabase\SupabaseClient
 */
class Supabase extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'supabase';
    }
}
