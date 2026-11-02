<?php

namespace App\Imports;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;
use ZipArchive;

abstract class SpreadsheetImport
{
    protected const MAX_ERRORS = 50;

    protected int $created = 0;

    protected int $updated = 0;

    protected int $failed = 0;

    /** @var array<int, string> */
    protected array $errors = [];

    /** @var array<string, string> peta nama file (lowercase) => path absolut hasil ekstrak ZIP */
    protected array $images = [];

    /**
     * Nama kolom (setelah normalisasi) yang wajib ada pada baris pertama file.
     *
     * @return array<int, string>
     */
    abstract protected function requiredColumns(): array;

    /**
     * Proses satu baris data. Lempar exception bila baris tidak valid.
     *
     * @param  array<string, mixed>  $row
     */
    abstract protected function processRow(array $row, int $line): void;

    /**
     * Import dari file yang diunggah user, opsional bersama ZIP berisi gambar.
     *
     * @return array{total: int, created: int, updated: int, failed: int, errors: array<int, string>}
     */
    public function importFromUpload(UploadedFile $file, ?UploadedFile $images = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
        $path = tempnam(sys_get_temp_dir(), 'import-').'.'.$extension;

        copy($file->getRealPath(), $path);

        $this->images = [];
        $imageDirectory = null;

        try {
            if ($images !== null) {
                $imageDirectory = $this->extractImages($images);
            }

            return $this->import($path);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }

            if ($imageDirectory !== null) {
                $this->removeDirectory($imageDirectory);
            }
        }
    }

    /**
     * Ekstrak ZIP gambar ke direktori sementara lalu simpan peta nama file => path.
     */
    private function extractImages(UploadedFile $zip): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ekstensi ZIP tidak tersedia di server.');
        }

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'import-images-'.Str::uuid();

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('folder sementara untuk gambar tidak dapat dibuat.');
        }

        $archive = new ZipArchive;

        if ($archive->open($zip->getRealPath()) !== true) {
            throw new RuntimeException('file ZIP gambar tidak dapat dibuka.');
        }

        try {
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $entry = $archive->getNameIndex($index);

                if ($entry === false || str_ends_with($entry, '/')) {
                    continue;
                }

                if (str_contains($entry, '__MACOSX') || str_starts_with(basename($entry), '._')) {
                    continue;
                }

                $contents = $archive->getFromIndex($index);

                if ($contents === false) {
                    continue;
                }

                $target = $directory.DIRECTORY_SEPARATOR.basename($entry);
                file_put_contents($target, $contents);

                $this->images[strtolower(basename($entry))] = $target;
            }
        } finally {
            $archive->close();
        }

        return $directory;
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($directory);
    }

    /**
     * @return array{total: int, created: int, updated: int, failed: int, errors: array<int, string>}
     */
    public function import(string $path): array
    {
        $this->created = 0;
        $this->updated = 0;
        $this->failed = 0;
        $this->errors = [];

        $rows = array_filter($this->readRows($path), fn (array $row) => $this->hasContent($row));
        $headerLine = array_key_first($rows);

        if ($headerLine === null) {
            return $this->result(0, ['File tidak memiliki data.']);
        }

        $header = $this->normalizeHeader($rows[$headerLine]);
        unset($rows[$headerLine]);

        $missing = array_values(array_diff($this->requiredColumns(), $header));

        if ($missing !== []) {
            return $this->result(0, ['Kolom wajib tidak ditemukan: '.implode(', ', $missing).'.']);
        }

        foreach ($rows as $line => $values) {
            $row = [];

            foreach ($header as $position => $column) {
                $row[$column] = $values[$position] ?? null;
            }

            try {
                DB::transaction(fn () => $this->processRow($row, $line));
            } catch (Throwable $e) {
                $this->failed++;
                $this->addError('Baris '.$line.': '.$this->errorMessage($e));
            }
        }

        return $this->result(count($rows));
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);

        try {
            $rows = [];

            foreach ($spreadsheet->getActiveSheet()->toArray(null, true, true, false) as $index => $values) {
                $rows[$index + 1] = array_map(
                    fn ($value) => is_string($value) ? trim($value) : $value,
                    $values,
                );
            }

            return $rows;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, string>
     */
    private function normalizeHeader(array $values): array
    {
        return array_map(function ($value) {
            $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);

            return str_replace([' ', '-'], '_', strtolower(trim($value)));
        }, $values);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function hasContent(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    protected function text(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }

    /**
     * Simpan gambar hasil ekstrak ZIP ke disk public, kembalikan path relatifnya.
     */
    protected function storeImage(mixed $value, string $directory): ?string
    {
        $name = trim($this->text($value));

        if ($name === '') {
            return null;
        }

        $source = $this->images[strtolower(basename($name))] ?? null;

        if ($source === null) {
            throw new RuntimeException('file gambar "'.$name.'" tidak ditemukan di dalam ZIP.');
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'jpg';
        $baseName = Str::slug(pathinfo($name, PATHINFO_FILENAME)) ?: 'gambar';
        $target = trim($directory, '/').'/'.$baseName.'-'.Str::lower(Str::random(8)).'.'.$extension;

        Storage::disk('public')->put($target, file_get_contents($source));

        return $target;
    }

    protected function deleteImage(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    private function errorMessage(Throwable $e): string
    {
        if ($e instanceof ValidationException) {
            return implode(' ', Arr::flatten($e->errors()));
        }

        return $e->getMessage() !== '' ? $e->getMessage() : 'baris tidak valid.';
    }

    private function addError(string $message): void
    {
        if (count($this->errors) < self::MAX_ERRORS) {
            $this->errors[] = $message;
        }
    }

    /**
     * @param  array<int, string>  $messages
     * @return array{total: int, created: int, updated: int, failed: int, errors: array<int, string>}
     */
    private function result(int $total, array $messages = []): array
    {
        foreach ($messages as $message) {
            $this->failed++;
            $this->addError($message);
        }

        return [
            'total' => $total,
            'created' => $this->created,
            'updated' => $this->updated,
            'failed' => $this->failed,
            'errors' => $this->errors,
        ];
    }
}
