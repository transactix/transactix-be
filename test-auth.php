<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Load the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Check database connection
try {
    echo "Checking database connection...\n";
    DB::connection()->getPdo();
    echo "Database connection successful: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (\Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in .env file.\n";
    exit(1);
}

// Run migrations if needed
echo "Running migrations to ensure all tables exist...\n";
try {
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
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
        $userResponse = $kernel->handle(
            Request::create('/api/user', 'GET', [], [], [], [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $loginToken,
                'HTTP_ACCEPT' => 'application/json',
            ])
        );

        $userContent = json_decode($userResponse->getContent(), true);
        echo "Response status code: " . $userResponse->getStatusCode() . "\n";
        echo "Response: " . json_encode($userContent, JSON_PRETTY_PRINT) . "\n\n";

        if ($userContent && isset($userContent['success']) && $userContent['success']) {
            echo "Protected route access successful!\n";

            // Test logout
            echo "Testing logout...\n";
            $logoutResponse = $kernel->handle(
                Request::create('/api/logout', 'POST', [], [], [], [
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $loginToken,
                    'HTTP_ACCEPT' => 'application/json',
                ])
            );

            $logoutContent = json_decode($logoutResponse->getContent(), true);
            echo "Response status code: " . $logoutResponse->getStatusCode() . "\n";
            echo "Response: " . json_encode($logoutContent, JSON_PRETTY_PRINT) . "\n\n";

            if ($logoutContent && isset($logoutContent['success']) && $logoutContent['success']) {
                echo "Logout successful!\n";

                // Try to access protected route after logout
                echo "Testing protected route after logout...\n";
                $afterLogoutResponse = $kernel->handle(
                    Request::create('/api/user', 'GET', [], [], [], [
                        'HTTP_AUTHORIZATION' => 'Bearer ' . $loginToken,
                        'HTTP_ACCEPT' => 'application/json',
                    ])
                );

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
} catch (\Exception $e) {
    echo "Error checking user in database: " . $e->getMessage() . "\n";
}
