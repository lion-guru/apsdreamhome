<?php
/**
 * ExcelExportService — reusable .xlsx export using PhpSpreadsheet.
 *
 * Provides a single entry point to export any tabular data (headers + rows)
 * to an Excel file. All report controllers can reuse this instead of each
 * reinventing CSV-only export.
 *
 * Usage:
 *   $service = new ExcelExportService();
 *   $service->export($headers, $rows, 'filename.xlsx');
 *
 * Headers: array of column labels.
 * Rows:   array of arrays (each inner array = one row, aligned to headers).
 * Filename: optional, defaults to 'export_YYYY-MM-DD.xlsx'.
 */
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExcelExportService
{
    /**
     * Export headers + rows as an .xlsx file and stream it to the browser.
     *
     * After calling this, the caller should `exit;` (output is sent directly).
     *
     * @param array  $headers  Column labels (e.g. ['ID','Name','Amount'])
     * @param array  $rows     Data rows, each an array aligned to $headers
     * @param string $filename Output filename (default: export_YYYY-MM-DD.xlsx)
     * @param string $sheetName Worksheet tab name (default: 'Export')
     * @return void
     */
    public function export(array $headers, array $rows, string $filename = null, string $sheetName = 'Export'): void
    {
        if ($filename === null) {
            $filename = 'export_' . date('Y-m-d') . '.xlsx';
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        // --- Header row (styled) ---
        $headerCol = 'A';
        foreach ($headers as $header) {
            $cell = $headerCol . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E79'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            $headerCol++;
        }

        // Bold + wrap headers, set column widths
        $sheet->getRowDimension(1)->setRowHeight(22);
        foreach (range('A', $headerCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Data rows ---
        $rowNum = 2;
        foreach ($rows as $row) {
            $col = 'A';
            foreach ($row as $value) {
                $sheet->setCellValue($col . $rowNum, $value);
                $sheet->getStyle($col . $rowNum)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ]);
                $col++;
            }
            $rowNum++;
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // --- Stream to browser via temp file + readfile (avoids php://output
        // output-buffering contamination from the framework layout) ---
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xlsx_' . uniqid() . '.xlsx';
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            // Discard any framework-buffered output so the XLSX is clean
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempFile));

            readfile($tempFile);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        exit;
    }
}
