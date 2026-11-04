<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['nama' => 'Elektronik'],
            ['nama' => 'Furniture'],
            ['nama' => 'Alat Tulis'],
            ['nama' => 'Kendaraan'],
            ['nama' => 'Lainnya'],
        ];

        foreach ($categories as $category) {
            Kategori::create($category);
        }
    }
}
