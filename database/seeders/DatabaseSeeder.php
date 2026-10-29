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
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@inventaris.ac.id'],
            [
                'name' => 'Petugas Inventaris',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@inventaris.ac.id'],
            [
                'name' => 'User Biasa',
                'password' => bcrypt('password123'),
                'role' => 'user',
            ]
        );
    }
}
