<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class PlotImportService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;
    private $importStats;

    private const BATCH_SIZE = 100;

    private const EXPECTED_HEADERS = [
        'plot_number', 'width_ft', 'length_ft', 'total_price', 'status',
        'colony_id', 'facing', 'corner_plot', 'park_facing', 'road_width_ft'
    ];

    private const ALLOWED_STATUS = ['available', 'booked', 'sold', 'hold', 'reserved', 'under_construction'];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getPdo();
        $this->importStats = [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'error_details' => []
        ];
    }

    public function importCsv($filePath, $colonyId = null)
    {
        $this->importStats = [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'error_details' => []
        ];

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $rows = $this->parseCsv($filePath);
        if (count($rows) < 2) {
            throw new \Exception("CSV file is empty or has no data rows");
        }

        $headers = array_map('strtolower', array_map('trim', $rows[0]));
        $headerValidation = $this->validateHeaders($headers);
        if (!$headerValidation['valid']) {
            throw new \Exception($headerValidation['error']);
        }

        $tid = $this->tenantId();
        $batch = [];
        $batchCount = 0;

        $this->pdo->beginTransaction();

        try {
            for ($i = 1; $i < count($rows); $i++) {
                $rowNum = $i;
                $row = $rows[$i];

                if (count(array_filter($row, fn($c) => trim((string)$c) !== '')) === 0) {
                    continue;
                }

                $this->importStats['total']++;

                $data = $this->rowToAssoc($row, $headers);
                $validation = $this->validateRow($data, $rowNum);

                if (!empty($validation['errors'])) {
                    $this->importStats['errors']++;
                    $this->importStats['error_details'][] = "Row {$rowNum}: " . implode(', ', $validation['errors']);
                    continue;
                }

                if ($colonyId !== null) {
                    $data['colony_id'] = (int)$colonyId;
                }

                $existing = $this->db->fetchOne(
                    "SELECT id FROM plots WHERE plot_number = ? AND colony_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    array_merge([$data['plot_number'], $data['colony_id']], $tid > 1 ? [$tid] : [])
                );

                if ($existing) {
                    $this->importStats['errors']++;
                    $this->importStats['error_details'][] = "Row {$rowNum}: Plot {$data['plot_number']} already exists in colony {$data['colony_id']}";
                    continue;
                }

                $batch[] = $this->buildInsertData($data);
                $batchCount++;

                if ($batchCount >= self::BATCH_SIZE) {
                    $this->insertBatch($batch);
                    $this->importStats['success'] += count($batch);
                    $batch = [];
                    $batchCount = 0;
                }
            }

            if (!empty($batch)) {
                $this->insertBatch($batch);
                $this->importStats['success'] += count($batch);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return $this->importStats;
    }

    public function validateRow($row, $lineNumber)
    {
        $errors = [];

        if (empty($row['plot_number'])) {
            $errors[] = 'plot_number is required';
        }

        if (empty($row['colony_id'])) {
            $errors[] = 'colony_id is required';
        } elseif (!is_numeric($row['colony_id']) || (int)$row['colony_id'] <= 0) {
            $errors[] = 'colony_id must be a positive integer';
        }

        if (!empty($row['width_ft']) && (!is_numeric($row['width_ft']) || (float)$row['width_ft'] <= 0)) {
            $errors[] = 'width_ft must be a positive number';
        }

        if (!empty($row['length_ft']) && (!is_numeric($row['length_ft']) || (float)$row['length_ft'] <= 0)) {
            $errors[] = 'length_ft must be a positive number';
        }

        if (!empty($row['total_price']) && (!is_numeric($row['total_price']) || (float)$row['total_price'] < 0)) {
            $errors[] = 'total_price must be a non-negative number';
        }

        if (!empty($row['status']) && !in_array(strtolower($row['status']), self::ALLOWED_STATUS)) {
            $errors[] = 'status must be one of: ' . implode(', ', self::ALLOWED_STATUS);
        }

        if (!empty($row['road_width_ft']) && (!is_numeric($row['road_width_ft']) || (float)$row['road_width_ft'] < 0)) {
            $errors[] = 'road_width_ft must be a non-negative number';
        }

        return ['errors' => $errors, 'valid' => empty($errors)];
    }

    public function exportCsv($colonyId = null)
    {
        $tid = $this->tenantId();
        $where = ['1=1'];
        $params = [];

        if ($tid > 1) {
            $where[] = 'tenant_id = ?';
            $params[] = $tid;
        }

        if ($colonyId !== null) {
            $where[] = 'colony_id = ?';
            $params[] = (int)$colonyId;
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $this->db->prepare("SELECT plot_number, width_ft, length_ft, total_price, status, colony_id, facing, corner_plot, park_facing, road_width_ft FROM plots WHERE {$whereClause} ORDER BY colony_id, plot_number");
        $stmt->execute($params);
        $plots = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $filename = 'plots_export_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = STORAGE_PATH . '/exports/' . $filename;

        $exportDir = STORAGE_PATH . '/exports/';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $handle = fopen($filepath, 'w');

        fputcsv($handle, self::EXPECTED_HEADERS);

        foreach ($plots as $plot) {
            fputcsv($handle, [
                $plot['plot_number'],
                $plot['width_ft'],
                $plot['length_ft'],
                $plot['total_price'],
                $plot['status'],
                $plot['colony_id'],
                $plot['facing'],
                $plot['corner_plot'],
                $plot['park_facing'],
                $plot['road_width_ft']
            ]);
        }

        fclose($handle);

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'records' => count($plots)
        ];
    }

    public function downloadTemplate()
    {
        $filename = 'plots_import_template.csv';
        $filepath = STORAGE_PATH . '/exports/' . $filename;

        $exportDir = STORAGE_PATH . '/exports/';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $handle = fopen($filepath, 'w');

        fputcsv($handle, self::EXPECTED_HEADERS);

        fputcsv($handle, [
            'A-001', '30', '40', '1500000', 'available', '2', 'north', '0', '1', '20'
        ]);
        fputcsv($handle, [
            'A-002', '35', '45', '1800000', 'available', '2', 'east', '1', '0', '25'
        ]);
        fputcsv($handle, [
            'B-001', '40', '50', '2200000', 'booked', '3', 'south', '0', '0', '30'
        ]);

        fclose($handle);

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }

    public function getImportStats()
    {
        return $this->importStats;
    }

    public function parseCsv($filePath)
    {
        $content = file_get_contents($filePath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $rows = [];
        $row = [];
        $field = '';
        $inQuotes = false;
        $i = 0;
        $len = strlen($content);

        while ($i < $len) {
            $c = $content[$i];
            if ($inQuotes) {
                if ($c === '"') {
                    if ($i + 1 < $len && $content[$i + 1] === '"') {
                        $field .= '"';
                        $i += 2;
                        continue;
                    }
                    $inQuotes = false;
                    $i++;
                    continue;
                }
                $field .= $c;
                $i++;
            } else {
                if ($c === '"') {
                    $inQuotes = true;
                    $i++;
                } elseif ($c === ',') {
                    $row[] = $field;
                    $field = '';
                    $i++;
                } elseif ($c === "\n") {
                    $row[] = $field;
                    $field = '';
                    if (count($row) > 1 || ($row[0] ?? '') !== '') {
                        $rows[] = $row;
                    }
                    $row = [];
                    $i++;
                } else {
                    $field .= $c;
                    $i++;
                }
            }
        }

        if ($field !== '' || count($row) > 0) {
            $row[] = $field;
            if (count($row) > 1 || ($row[0] ?? '') !== '') {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function validateHeaders($headers)
    {
        $missing = array_diff(self::EXPECTED_HEADERS, $headers);

        if (!empty($missing)) {
            return [
                'valid' => false,
                'error' => 'Missing required columns: ' . implode(', ', $missing)
            ];
        }

        return ['valid' => true];
    }

    private function rowToAssoc($row, $header)
    {
        $data = [];
        foreach ($header as $idx => $col) {
            $data[$col] = isset($row[$idx]) ? trim($row[$idx]) : '';
        }
        return $data;
    }

    private function buildInsertData($data)
    {
        $insert = [
            'plot_number' => $data['plot_number'],
            'colony_id' => (int)$data['colony_id'],
            'width_ft' => !empty($data['width_ft']) ? (float)$data['width_ft'] : null,
            'length_ft' => !empty($data['length_ft']) ? (float)$data['length_ft'] : null,
            'total_price' => !empty($data['total_price']) ? (float)$data['total_price'] : 0,
            'status' => !empty($data['status']) ? strtolower($data['status']) : 'available',
            'facing' => !empty($data['facing']) ? $data['facing'] : null,
            'corner_plot' => !empty($data['corner_plot']) ? 1 : 0,
            'park_facing' => !empty($data['park_facing']) ? 1 : 0,
            'road_width_ft' => !empty($data['road_width_ft']) ? (float)$data['road_width_ft'] : 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $insert + $this->tenantInsertData();
    }

    private function insertBatch($rows)
    {
        if (empty($rows)) {
            return 0;
        }

        $cols = array_keys($rows[0]);
        $colList = implode(',', array_map(fn($c) => "`$c`", $cols));
        $placeholders = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql = "INSERT INTO plots ({$colList}) VALUES " . implode(',', array_fill(0, count($rows), $placeholders));

        $stmt = $this->pdo->prepare($sql);
        $params = [];
        foreach ($rows as $r) {
            foreach ($r as $v) {
                $params[] = $v;
            }
        }
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
