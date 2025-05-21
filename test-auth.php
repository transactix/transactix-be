<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Load the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Bootstrap the application
$app->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    \Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

// Check database connection
try {
    echo "Checking database connection...\n";
    DB::connection()->getPdo();
    echo "Database connection successful: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (\Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in .env file.\n";
    echo "Continuing with the test anyway...\n\n";
}

// Run migrations if needed
echo "Running migrations to ensure all tables exist...\n";
try {
    // Create the personal_access_tokens table if it doesn't exist
    if (!Schema::hasTable('personal_access_tokens')) {
        Schema::create('personal_access_tokens', function ($table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        echo "Created personal_access_tokens table.\n";
    } else {
        echo "personal_access_tokens table already exists.\n";
    }

    echo "Migrations completed successfully.\n\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    echo "Continuing with the test...\n\n";
}

// Generate a unique email for testing
$testEmail = 'test_' . time() . '@example.com';

// Create a test user
echo "Creating a test user with email: {$testEmail}...\n";
$response = $kernel->handle(
    Request::create('/api/register', 'POST', [
        'name' => 'Test User',
        'email' => $testEmail,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])
);

$content = json_decode($response->getContent(), true);
echo "Response status code: " . $response->getStatusCode() . "\n";
echo "Response: " . json_encode($content, JSON_PRETTY_PRINT) . "\n\n";

if ($content && isset($content['success']) && $content['success']) {
    $email = $content['data']['user']['email'];
    $token = $content['data']['access_token'];

    echo "User created successfully with email: {$email}\n";
    echo "Access token: {$token}\n\n";

    // Test login
    echo "Testing login...\n";
    $loginResponse = $kernel->handle(
        Request::create('/api/login', 'POST', [
            'email' => $email,
            'password' => 'Password123!',
        ])
    );

    $loginContent = json_decode($loginResponse->getContent(), true);
    echo "Response status code: " . $loginResponse->getStatusCode() . "\n";
    echo "Response: " . json_encode($loginContent, JSON_PRETTY_PRINT) . "\n\n";

    if ($loginContent && isset($loginContent['success']) && $loginContent['success']) {
        echo "Login successful!\n";
        $loginToken = $loginContent['data']['access_token'];

        // Test protected route
        echo "Testing protected route...\n";
        $request = Request::create('/api/user', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $loginToken);
        $request->headers->set('Accept', 'application/json');
        $userResponse = $kernel->handle($request);

        $userContent = json_decode($userResponse->getContent(), true);
        echo "Response status code: " . $userResponse->getStatusCode() . "\n";
        echo "Response: " . json_encode($userContent, JSON_PRETTY_PRINT) . "\n\n";

        if ($userContent && isset($userContent['success']) && $userContent['success']) {
            echo "Protected route access successful!\n";

            // Test logout
            echo "Testing logout...\n";
            $logoutRequest = Request::create('/api/logout', 'POST');
            $logoutRequest->headers->set('Authorization', 'Bearer ' . $loginToken);
            $logoutRequest->headers->set('Accept', 'application/json');
            $logoutResponse = $kernel->handle($logoutRequest);

            $logoutContent = json_decode($logoutResponse->getContent(), true);
            echo "Response status code: " . $logoutResponse->getStatusCode() . "\n";
            echo "Response: " . json_encode($logoutContent, JSON_PRETTY_PRINT) . "\n\n";

            if ($logoutContent && isset($logoutContent['success']) && $logoutContent['success']) {
                echo "Logout successful!\n";

                // Try to access protected route after logout
                echo "Testing protected route after logout...\n";
                $afterLogoutRequest = Request::create('/api/user', 'GET');
                $afterLogoutRequest->headers->set('Authorization', 'Bearer ' . $loginToken);
                $afterLogoutRequest->headers->set('Accept', 'application/json');
                $afterLogoutResponse = $kernel->handle($afterLogoutRequest);

                $afterLogoutContent = json_decode($afterLogoutResponse->getContent(), true);
                echo "Response status code: " . $afterLogoutResponse->getStatusCode() . "\n";
                echo "Response: " . json_encode($afterLogoutContent, JSON_PRETTY_PRINT) . "\n\n";

                if ($afterLogoutResponse->getStatusCode() === 401) {
                    echo "Authentication test completed successfully! The token was properly invalidated after logout.\n";
                } else {
                    echo "Error: Token was not properly invalidated after logout.\n";
                }
            } else {
                echo "Error: Logout failed.\n";
            }
        } else {
            echo "Error: Protected route access failed.\n";
        }
    } else {
        echo "Error: Login failed.\n";
    }
} else {
    echo "Error: User creation failed.\n";
}

// Check if the user was created in Supabase
echo "\nChecking if user was created in Supabase...\n";
try {
    // Try using Supabase facade directly
    $supabase = app('supabase');
    $result = $supabase->query('users', [
        'where' => ['email' => $testEmail],
        'limit' => 1
    ], true);

    if (!empty($result) && is_array($result) && isset($result[0])) {
        $user = $result[0];
        echo "User found in Supabase with ID: {$user['id']}\n";
        echo "User details: " . json_encode([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ], JSON_PRETTY_PRINT) . "\n";
    } else {
        // Try using DB facade as fallback
        $user = DB::table('users')->where('email', $testEmail)->first();
        if ($user) {
            echo "User found in database with ID: {$user->id}\n";
            echo "User details: " . json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "User not found in database. This may indicate an issue with the Supabase integration.\n";
        }
    }
} catch (\Exception $e) {
    echo "Error checking user in database: " . $e->getMessage() . "\n";
    echo "Trying alternative method...\n";

    try {
        // Try using App\Facades\Supabase
        $result = \App\Facades\Supabase::query('users', [
            'where' => ['email' => $testEmail],
            'limit' => 1
        ], true);

        if (!empty($result) && is_array($result) && isset($result[0])) {
            $user = $result[0];
            echo "User found in Supabase with ID: {$user['id']}\n";
            echo "User details: " . json_encode([
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "User not found using Supabase facade.\n";
        }
    } catch (\Exception $e2) {
        echo "Error using Supabase facade: " . $e2->getMessage() . "\n";
    }
}
