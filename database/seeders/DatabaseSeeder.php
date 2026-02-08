<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user if none exists
        if (!User::where('role', User::ROLE_ADMIN)->exists()) {
            User::factory()->create([
                'name' => 'مدیر',
                'email' => 'admin@example.com',
                'role' => User::ROLE_ADMIN,
                'can_delete_invoice' => true,
                'can_delete_contact' => true,
                'can_delete_lead' => true,
            ]);
        }
    }
}
