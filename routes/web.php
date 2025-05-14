<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test route for Supabase integration
Route::get('/test-supabase', [TestController::class, 'testSupabase']);
