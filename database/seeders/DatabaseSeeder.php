<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user if none exists (no factory/Faker dependency for production)
        if (!User::where('role', User::ROLE_ADMIN)->exists()) {
            User::create([
                'name' => 'مدیر',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'can_delete_invoice' => true,
                'can_delete_contact' => true,
                'can_delete_lead' => true,
            ]);
        }

        $this->call(ServqualQuestionBankSeeder::class);
    }
}
