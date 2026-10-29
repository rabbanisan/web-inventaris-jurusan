<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@inventaris.ac.id'],
            [
                'name' => 'Petugas Inventaris',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@inventaris.ac.id'],
            [
                'name' => 'User biasa / Mahasiswa',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );
    }
}
