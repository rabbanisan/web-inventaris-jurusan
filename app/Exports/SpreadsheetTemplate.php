<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetTemplate
{
    /**
     * Buat file Excel template (header + contoh baris) lalu download.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, array $headers, array $rows = [])
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $sheet->fromArray($headers, null, 'A1', true);

        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2', true);
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        foreach (range(1, count($headers)) as $index) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }

        $sheet->freezePane('A2');

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
