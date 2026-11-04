<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Ruangan;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Pastikan kategori dan ruangan sudah ada
        $kategoriIds = Kategori::pluck('id')->toArray(); // [1,2,3,4,5]
        $ruanganIds  = Ruangan::pluck('id')->toArray(); // [1,2,3]

        $items = [
            [
                'kode' => 'BRG001',
                'nama' => 'Laptop Dell XPS 13',
                'kategori_id' => $kategoriIds[0] ?? 1,
                'ruangan_id'  => $ruanganIds[0] ?? 1,
                'keterangan'  => 'Laptop kerja untuk staff IT',
            ],
            [
                'kode' => 'BRG002',
                'nama' => 'Meja Kerja Kayu',
                'kategori_id' => $kategoriIds[1] ?? 2,
                'ruangan_id'  => $ruanganIds[2] ?? 3,
                'keterangan'  => 'Meja kerja ergonomis',
            ],
            [
                'kode' => 'BRG003',
                'nama' => 'Kursi Kantor',
                'kategori_id' => $kategoriIds[1] ?? 2,
                'ruangan_id'  => $ruanganIds[2] ?? 3,
                'keterangan'  => 'Kursi dengan sandaran tinggi',
            ],
            [
                'kode' => 'BRG004',
                'nama' => 'Printer HP LaserJet',
                'kategori_id' => $kategoriIds[0] ?? 1,
                'ruangan_id'  => $ruanganIds[0] ?? 1,
                'keterangan'  => 'Printer untuk dokumen kantor',
            ],
            [
                'kode' => 'BRG005',
                'nama' => 'Pulpen Pilot',
                'kategori_id' => $kategoriIds[2] ?? 3,
                'ruangan_id'  => $ruanganIds[1] ?? 2,
                'keterangan'  => 'Pulpen biru 0.5mm',
            ],
            [
                'kode' => 'BRG006',
                'nama' => 'Buku Agenda',
                'kategori_id' => $kategoriIds[2] ?? 3,
                'ruangan_id'  => $ruanganIds[1] ?? 2,
                'keterangan'  => 'Agenda harian',
            ],
            [
                'kode' => 'BRG007',
                'nama' => 'Sepeda Motor',
                'kategori_id' => $kategoriIds[3] ?? 4,
                'ruangan_id'  => $ruanganIds[0] ?? 1,
                'keterangan'  => 'Motor untuk keperluan operasional',
            ],
            [
                'kode' => 'BRG008',
                'nama' => 'Kabel HDMI 2m',
                'kategori_id' => $kategoriIds[0] ?? 1,
                'ruangan_id'  => $ruanganIds[0] ?? 1,
                'keterangan'  => 'Kabel HDMI untuk monitor',
            ],
            [
                'kode' => 'BRG009',
                'nama' => 'Rak Penyimpanan',
                'kategori_id' => $kategoriIds[1] ?? 2,
                'ruangan_id'  => $ruanganIds[0] ?? 1,
                'keterangan'  => 'Rak logam 5 tingkat',
            ],
            [
                'kode' => 'BRG010',
                'nama' => 'Mouse Wireless',
                'kategori_id' => $kategoriIds[0] ?? 1,
                'ruangan_id'  => $ruanganIds[2] ?? 3,
                'keterangan'  => 'Mouse Bluetooth',
            ],
        ];

        foreach ($items as $item) {
            Barang::create($item);
        }
    }
}
