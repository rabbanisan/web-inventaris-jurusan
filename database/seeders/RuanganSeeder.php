<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruangan;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rooms = [
            ['nama' => 'Gudang A'],
            ['nama' => 'Gudang B'],
            ['nama' => 'Ruang Kantor'],
        ];

        foreach ($rooms as $room) {
            Ruangan::create($room);
        }
    }
}
