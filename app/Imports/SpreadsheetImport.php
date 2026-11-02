<?php

namespace App\Imports;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

abstract class SpreadsheetImport
{
    protected const MAX_ERRORS = 50;

    protected int $created = 0;

    protected int $updated = 0;

    protected int $failed = 0;

    /** @var array<int, string> */
    protected array $errors = [];

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
     * Import dari file yang diunggah user.
     *
     * @return array{total: int, created: int, updated: int, failed: int, errors: array<int, string>}
     */
    public function importFromUpload(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
        $path = tempnam(sys_get_temp_dir(), 'import-').'.'.$extension;

        copy($file->getRealPath(), $path);

        try {
            return $this->import($path);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }
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
