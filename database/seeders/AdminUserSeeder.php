<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        if (!User::findByEmail('admin@transactix.com')) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@transactix.com',
                'password' => 'Admin123!',
                'role' => 'admin',
            ]);

            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
