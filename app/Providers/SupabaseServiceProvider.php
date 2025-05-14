<?php

namespace App\Providers;

use App\Services\Supabase\SupabaseClient;
use Illuminate\Support\ServiceProvider;

class SupabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SupabaseClient::class, function ($app) {
            return new SupabaseClient(
                config('services.supabase.url'),
                config('services.supabase.key'),
                config('services.supabase.secret')
            );
        });

        $this->app->alias(SupabaseClient::class, 'supabase');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
