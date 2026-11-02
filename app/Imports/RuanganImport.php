<?php

namespace App\Imports;

use App\Models\Ruangan;
use RuntimeException;

class RuanganImport extends SpreadsheetImport
{
    /**
     * @return array<int, string>
     */
    protected function requiredColumns(): array
    {
        return ['nama'];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function processRow(array $row, int $line): void
    {
        $nama = $this->text($row['nama'] ?? null);
        $lokasi = $this->text($row['lokasi'] ?? null);
        $keterangan = $this->text($row['keterangan'] ?? null);

        if ($nama === '') {
            throw new RuntimeException('kolom "nama" wajib diisi.');
        }

        if (mb_strlen($nama) > 255) {
            throw new RuntimeException('nama maksimal 255 karakter.');
        }

        $ruangan = Ruangan::where('nama', $nama)->first();

        if (! $ruangan) {
            Ruangan::create([
                'nama' => $nama,
                'lokasi' => $lokasi !== '' ? $lokasi : null,
                'keterangan' => $keterangan !== '' ? $keterangan : null,
            ]);
            $this->created++;

            return;
        }

        $ruangan->fill(array_filter([
            'lokasi' => $lokasi,
            'keterangan' => $keterangan,
        ], fn ($value) => $value !== ''))->save();

        $this->updated++;
    }
}
