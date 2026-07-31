<?php
/**
 * Property CSV Import Service
 * Bulk import properties into user_properties with full validation,
 * duplicate detection, transactional batches, and error logging.
 */

namespace App\Services\Bulk;

use App\Traits\ServiceTenantTrait;
use PDO;

class PropertyImportService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;

    private const ALLOWED_TYPES = ['plot', 'flat', 'house', 'shop', 'farmhouse', 'land', 'apartment', 'villa'];
    private const ALLOWED_LISTING = ['sale', 'sell', 'rent'];
    private const REQUIRED = ['title', 'type', 'listing_type', 'price'];
    private const BATCH_SIZE = 100;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    /**
     * Parse CSV content into rows. Handles:
     *  - Quoted fields with commas
     *  - Escaped quotes ("")
     *  - Multiline cells (newlines inside quotes)
     *  - BOM
     *  - Mixed line endings
     */
    public function parseCsv(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // strip BOM
        $content = str_replace(["\r\n", "\r"], "\n", $content);     // normalize line endings

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
                    if (count($row) > 1 || ($row[0] ?? '') !== '') $rows[] = $row;
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
            if (count($row) > 1 || ($row[0] ?? '') !== '') $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Validate a single row. Returns array of error strings (empty if valid).
     */
    public function validateRow(array $row, array $header, int $rowNum): array
    {
        $errors = [];
        $data = $this->rowToAssoc($row, $header);

        foreach (self::REQUIRED as $field) {
            if (empty($data[$field])) $errors[] = "Row {$rowNum}: missing required field '{$field}'";
        }
        if (!empty($data['type']) && !in_array($data['type'], self::ALLOWED_TYPES, true)) {
            $errors[] = "Row {$rowNum}: invalid type '{$data['type']}' (allowed: " . implode(', ', self::ALLOWED_TYPES) . ")";
        }
        if (!empty($data['listing_type']) && !in_array($data['listing_type'], self::ALLOWED_LISTING, true)) {
            $errors[] = "Row {$rowNum}: invalid listing_type '{$data['listing_type']}' (allowed: sale|sell|rent)";
        }
        if (isset($data['price']) && (float)$data['price'] <= 0) {
            $errors[] = "Row {$rowNum}: price must be > 0";
        }

        return [$data, $errors];
    }

    private function rowToAssoc(array $row, array $header): array
    {
        $data = [];
        foreach ($header as $idx => $col) {
            $data[$col] = isset($row[$idx]) ? trim($row[$idx]) : '';
        }
        return $data;
    }

    /**
     * Get CSV template (header + 1 example row).
     */
    public function getTemplate(): string
    {
        $cols = $this->getColumns();
        $sample = $this->getSampleData();
        $rows = [];
        $rows[] = $cols;
        $rows[] = $sample[0];
        return $this->arrayToCsv($rows);
    }

    /**
     * Get 5 sample rows for download.
     */
    public function getSampleData(): array
    {
        return [
            ['2 BHK Flat in Gorakhpur', 'flat', 'sale', '4500000', '1100', 'Civil Lines, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', '273001', 'Amit Kumar', '9876543210', 'amit@example.com', 'Spacious 2BHK with parking', 'parking;lift;power_backup;security', 'https://example.com/img1.jpg;https://example.com/img2.jpg'],
            ['1200 sqft Plot near Highway', 'plot', 'sale', '1800000', '1200', 'NH-28, Kushinagar', 'Kushinagar', 'Uttar Pradesh', '274303', 'Priya Singh', '9876543211', 'priya@example.com', 'Corner plot with road access', 'water_supply;security', ''],
            ['3 BHK House for Rent', 'house', 'rent', '25000', '1800', 'Sector 5, Lucknow', 'Lucknow', 'Uttar Pradesh', '226010', 'Rajesh Verma', '9876543212', 'rajesh@example.com', 'Furnished house, gated community', 'parking;gym;swimming_pool;security;cctv', ''],
            ['Commercial Shop in Market', 'shop', 'rent', '35000', '400', 'Main Market, Varanasi', 'Varanasi', 'Uttar Pradesh', '221001', 'Suresh Gupta', '9876543213', 'suresh@example.com', 'High footfall location', 'parking;power_backup', ''],
            ['5 Acre Farmhouse', 'farmhouse', 'sale', '8500000', '217800', 'NH-24, Bareilly', 'Bareilly', 'Uttar Pradesh', '243001', 'Neha Sharma', '9876543214', 'neha@example.com', '5 acres with borewell, organic certified', 'water_supply;rainwater_harvesting;garden', ''],
        ];
    }

    public function getSampleCsv(): string
    {
        $rows = array_merge([$this->getColumns()], $this->getSampleData());
        return $this->arrayToCsv($rows);
    }

    private function getColumns(): array
    {
        return ['title', 'type', 'listing_type', 'price', 'area', 'location', 'city', 'state', 'pincode', 'owner_name', 'owner_phone', 'owner_email', 'description', 'amenities', 'images'];
    }

    private function arrayToCsv(array $rows): string
    {
        $out = '';
        foreach ($rows as $row) {
            $cells = array_map(function ($v) {
                $v = (string)$v;
                if (strpos($v, ',') !== false || strpos($v, '"') !== false || strpos($v, "\n") !== false) {
                    return '"' . str_replace('"', '""', $v) . '"';
                }
                return $v;
            }, $row);
            $out .= implode(',', $cells) . "\r\n";
        }
        return $out;
    }

    /**
     * Preview first 10 rows + validation errors.
     */
    public function previewImport(string $content): array
    {
        $rows = $this->parseCsv($content);
        if (count($rows) < 2) {
            return ['ok' => false, 'error' => 'CSV must have header + at least 1 row'];
        }
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $validCols = $this->getColumns();
        $invalidCols = array_diff($header, $validCols);
        if (!empty($invalidCols)) {
            return ['ok' => false, 'error' => 'Invalid columns: ' . implode(', ', $invalidCols)];
        }
        $preview = [];
        $errors = [];
        $max = min(10, count($rows) - 1);
        for ($i = 1; $i <= $max; $i++) {
            [$data, $errs] = $this->validateRow($rows[$i], $header, $i);
            $preview[] = ['row' => $i, 'data' => $data, 'valid' => empty($errs), 'errors' => $errs];
            $errors = array_merge($errors, $errs);
        }
        return [
            'ok' => true,
            'header' => $header,
            'total_rows' => count($rows) - 1,
            'preview' => $preview,
            'errors' => $errors,
        ];
    }

    /**
     * Execute the import. Returns summary stats.
     */
    public function importCsv(string $content, array $options = []): array
    {
        $rows = $this->parseCsv($content);
        if (count($rows) < 2) {
            return ['ok' => false, 'error' => 'CSV must have header + at least 1 row', 'imported' => 0, 'skipped' => 0, 'errors' => []];
        }
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $validCols = $this->getColumns();
        $invalidCols = array_diff($header, $validCols);
        if (!empty($invalidCols)) {
            return ['ok' => false, 'error' => 'Invalid columns: ' . implode(', ', $invalidCols), 'imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $skipDuplicates = $options['skip_duplicates'] ?? true;
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $batch = [];
        $batchCount = 0;
        $this->pdo->beginTransaction();

        try {
            for ($i = 1; $i < count($rows); $i++) {
                $rowNum = $i;
                $row = $rows[$i];
                if (count(array_filter($row, fn($c) => trim((string)$c) !== '')) === 0) continue;
                [$data, $errs] = $this->validateRow($row, $header, $rowNum);
                if (!empty($errs)) {
                    $errors = array_merge($errors, $errs);
                    $skipped++;
                    continue;
                }
                if ($skipDuplicates && $this->isDuplicate($data)) {
                    $errors[] = "Row {$rowNum}: duplicate of existing property (title+location match)";
                    $skipped++;
                    continue;
                }
                $batch[] = $this->buildInsertData($data);
                $batchCount++;
                if ($batchCount >= self::BATCH_SIZE) {
                    $imported += $this->insertBatch($batch);
                    $batch = [];
                    $batchCount = 0;
                }
            }
            if (!empty($batch)) $imported += $this->insertBatch($batch);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['ok' => false, 'error' => 'Import failed: ' . $e->getMessage(), 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
        }

        return [
            'ok' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 50),
            'total_errors' => count($errors),
        ];
    }

    private function isDuplicate(array $data): bool
    {
        $title = $data['title'] ?? '';
        $location = trim(($data['location'] ?? '') . ' ' . ($data['city'] ?? ''));
        if ($title === '') return false;
$stmt = $this->pdo->prepare("SELECT id FROM user_properties WHERE name = ? AND (address = ? OR location = ?) AND tenant_id = ? LIMIT 1");
         $stmt->execute([$title, $data['location'] ?? '', $location, $this->tenantId()]);
        return (bool)$stmt->fetchColumn();
    }

    private function buildInsertData(array $data): array
    {
        $listingType = in_array($data['listing_type'] ?? '', ['sell', 'sale'], true) ? 'sell' : 'rent';
        $amenities = !empty($data['amenities']) ? str_replace(';', "\n", $data['amenities']) : '';
        $description = $data['description'] ?? '';
        if ($amenities !== '') $description .= ($description ? "\n\n" : '') . "Amenities:\n" . $amenities;
        if (!empty($data['images'])) $description .= "\n\nImages: " . str_replace(';', "\n", $data['images']);
        if (!empty($data['pincode'])) $description .= "\nPincode: " . $data['pincode'];

        return [
            'name' => $data['title'] ?? 'Untitled',
            'phone' => $data['owner_phone'] ?? '',
            'email' => $data['owner_email'] ?? null,
            'property_type' => in_array($data['type'] ?? '', self::ALLOWED_TYPES, true) ? $data['type'] : 'plot',
            'listing_type' => $listingType,
            'address' => $data['location'] ?? '',
            'location' => $data['city'] ?? '',
            'area_sqft' => (int)($data['area'] ?? 0),
            'price' => (float)($data['price'] ?? 0),
            'price_type' => 'lakh',
            'description' => $description,
            'status' => 'pending',
            'user_id' => null,
            'posted_by' => null,
            'posted_by_type' => 'admin_import',
            'created_at' => date('Y-m-d H:i:s'),
        ] + $this->tenantInsertData();
    }

    private function insertBatch(array $rows): int
    {
        if (empty($rows)) return 0;
        $cols = array_keys($rows[0]);
        $colList = implode(',', array_map(fn($c) => "`$c`", $cols));
        $placeholders = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql = "INSERT INTO user_properties ($colList) VALUES " . implode(',', array_fill(0, count($rows), $placeholders));
        $stmt = $this->pdo->prepare($sql);
        $params = [];
        foreach ($rows as $r) {
            foreach ($r as $v) $params[] = $v;
        }
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
