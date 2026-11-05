<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser(
            'admin@inventaris.ac.id',
            'Petugas Inventaris',
            'admin',
        );

        $this->seedUser(
            'user@inventaris.ac.id',
            'User biasa / Mahasiswa',
            'user',
        );
    }

    /**
     * Buat/perbarui user tanpa mass assignment.
     *
     * `role` tidak fillable (lihat App\Models\User), jadi wajib di-set
     * lewat forceFill() agar tetap bisa diisi dari seeder.
     */
    private function seedUser(string $email, string $name, string $role): void
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => Hash::make('password123'),
            'role' => $role,
        ])->save();
    }
}
