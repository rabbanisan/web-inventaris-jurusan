<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Ruangan;
use RuntimeException;

class BarangImport extends SpreadsheetImport
{
    /**
     * @return array<int, string>
     */
    protected function requiredColumns(): array
    {
        return ['kode', 'nama', 'kategori', 'ruangan'];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function processRow(array $row, int $line): void
    {
        $kode = $this->text($row['kode'] ?? null);
        $nama = $this->text($row['nama'] ?? null);
        $kategoriNama = $this->text($row['kategori'] ?? null);
        $ruanganNama = $this->text($row['ruangan'] ?? null);

        if ($kode === '') {
            throw new RuntimeException('kolom "kode" wajib diisi.');
        }

        if (mb_strlen($kode) > 50) {
            throw new RuntimeException('kode maksimal 50 karakter.');
        }

        if ($nama === '') {
            throw new RuntimeException('kolom "nama" wajib diisi.');
        }

        if (mb_strlen($nama) > 255) {
            throw new RuntimeException('nama maksimal 255 karakter.');
        }

        if ($kategoriNama === '') {
            throw new RuntimeException('kolom "kategori" wajib diisi.');
        }

        if ($ruanganNama === '') {
            throw new RuntimeException('kolom "ruangan" wajib diisi.');
        }

        $jumlah = $this->jumlah($row['jumlah'] ?? null);
        $kondisi = $this->kondisi($row['kondisi'] ?? null);
        $keterangan = $this->text($row['keterangan'] ?? null);

        $kategori = Kategori::firstOrCreate(['nama' => $kategoriNama]);
        $ruangan = Ruangan::firstOrCreate(['nama' => $ruanganNama]);

        $attributes = [
            'nama' => $nama,
            'kategori_id' => $kategori->id,
            'ruangan_id' => $ruangan->id,
            'kondisi' => $kondisi,
            'jumlah' => $jumlah,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ];

        $gambar = $this->storeImage($row['gambar'] ?? null, 'barang');

        $barang = Barang::where('kode', $kode)->first();

        if ($barang) {
            if ($gambar !== null) {
                $this->deleteImage($barang->gambar);
                $attributes['gambar'] = $gambar;
            }

            $barang->update($attributes);
            $this->updated++;

            return;
        }

        if ($gambar !== null) {
            $attributes['gambar'] = $gambar;
        }

        Barang::create(['kode' => $kode] + $attributes);
        $this->created++;
    }

    private function jumlah(mixed $value): int
    {
        $value = $this->text($value);

        if ($value === '') {
            return 1;
        }

        if (! is_numeric($value) || (int) $value < 1) {
            throw new RuntimeException('jumlah harus angka bulat minimal 1.');
        }

        return (int) $value;
    }

    private function kondisi(mixed $value): string
    {
        $value = str_replace([' ', '-'], '_', strtolower($this->text($value)));

        if ($value === '') {
            return 'baik';
        }

        if (! in_array($value, ['baik', 'rusak_ringan', 'rusak_berat'], true)) {
            throw new RuntimeException('kondisi harus salah satu dari: baik, rusak_ringan, rusak_berat.');
        }

        return $value;
    }
}
