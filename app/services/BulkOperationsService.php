<?php
namespace App\Services;

use PDO;

class BulkOperationsService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    private $allowedTables = [
        'leads' => ['name', 'email', 'phone', 'source', 'city', 'state', 'pincode', 'message'],
        'user_properties' => ['name', 'phone', 'email', 'property_type', 'listing_type', 'address', 'area_sqft', 'price', 'description'],
        'plots' => ['colony_id', 'plot_number', 'type', 'area_sqft', 'price', 'status'],
        'customers' => ['name', 'email', 'phone', 'address', 'city', 'state', 'pincode'],
        'newsletter_subscribers' => ['email', 'name', 'is_active'],
    ];

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    public function getAllowedTables(): array
    {
        return array_keys($this->allowedTables);
    }

    public function getTemplate(string $table): ?string
    {
        if (!isset($this->allowedTables[$table])) return null;
        $cols = $this->allowedTables[$table];
        return implode(',', $cols) . "\n" . $this->generateSampleRow($table, $cols);
    }

    private function generateSampleRow(string $table, array $cols): string
    {
        $samples = [
            'leads' => ['John Doe', 'john@example.com', '9876543210', 'website', 'Mumbai', 'Maharashtra', '400001', 'Interested in 2BHK'],
            'user_properties' => ['Amit Kumar', '9876543210', 'amit@example.com', 'plot', 'sale', 'Plot 123, Gorakhpur', '1200', '2500000', 'Corner plot with road access'],
            'plots' => ['1', 'A-101', 'residential', '1200', '2500000', 'available'],
            'customers' => ['Priya Singh', 'priya@example.com', '9876543211', '123 Main St', 'Delhi', 'Delhi', '110001'],
            'newsletter_subscribers' => ['subscriber@example.com', 'Subscriber Name', '1'],
        ];
        $row = $samples[$table] ?? array_fill(0, count($cols), 'value');
        return implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $row));
    }

    public function importCSV(string $table, string $csvContent, ?int $userId = null): array
    {
        if (!isset($this->allowedTables[$table])) {
            return ['ok' => false, 'error' => "Table '$table' not allowed for import"];
        }

        $lines = array_filter(array_map('trim', explode("\n", $csvContent)));
        if (count($lines) < 2) {
            return ['ok' => false, 'error' => 'CSV must have header + at least 1 row'];
        }

        $header = str_getcsv(array_shift($lines));
        $header = array_map('strtolower', array_map('trim', $header));
        $validCols = $this->allowedTables[$table];
        $invalidCols = array_diff($header, $validCols);
        if (!empty($invalidCols)) {
            return ['ok' => false, 'error' => 'Invalid columns: ' . implode(', ', $invalidCols)];
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        $colList = implode(',', array_map(fn($c) => "`$c`", $header));
        $tenantIns = $this->tenantInsertData();
        if (!empty($tenantIns)) {
            $colList .= ', `tenant_id`';
            $placeholders = implode(',', array_fill(0, count($header) + 1, '?'));
        } else {
            $placeholders = implode(',', array_fill(0, count($header), '?'));
        }
        $st = $this->db->prepare("INSERT INTO $table ($colList) VALUES ($placeholders)");

        foreach ($lines as $i => $line) {
            if (empty(trim($line))) continue;
            $row = str_getcsv($line);
            $row = array_pad(array_slice($row, 0, count($header)), count($header), null);
            try {
                $st->execute(array_merge($row, array_values($tenantIns)));
                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
            }
        }

        return [
            'ok' => true,
            'imported' => $imported,
            'failed' => $failed,
            'errors' => array_slice($errors, 0, 10),
            'total_rows' => count($lines)
        ];
    }

    public function exportCSV(string $table, array $filters = [], int $limit = 1000): ?string
    {
        $allowedForExport = $this->allowedTables + [
            'bookings' => ['id', 'user_id', 'plot_id', 'total_amount', 'status', 'created_at'],
            'commissions' => ['id', 'user_id', 'booking_id', 'amount', 'status', 'created_at'],
            'users' => ['id', 'name', 'email', 'phone', 'role', 'status', 'created_at'],
        ];

        if (!isset($allowedForExport[$table])) return null;
        $cols = $allowedForExport[$table];

        $sql = "SELECT " . implode(',', array_map(fn($c) => "`$c`", $cols)) . " FROM $table";
        $params = [];
        $whereParts = [];
        if (!empty($filters)) {
            $where = [];
            foreach ($filters as $col => $val) {
                if (in_array($col, $cols)) {
                    $where[] = "`$col` = ?";
                    $params[] = $val;
                }
            }
            if ($where) $whereParts = $where;
        }
        if ($this->tenantId() > 1) {
            $whereParts[] = "tenant_id = ?";
            $params[] = $this->tenantId();
        }
        if ($whereParts) $sql .= " WHERE " . implode(' AND ', $whereParts);
        $sql .= " LIMIT ?";
        $params[] = $limit;

        try {
            $st = $this->db->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return null;
        }

        $output = "﻿" . implode(',', $cols) . "\n";
        foreach ($rows as $row) {
            $output .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', array_map(fn($c) => $row[$c] ?? '', $cols))) . "\n";
        }
        return $output;
    }

    public function getRowCount(string $table): int
    {
        if (!isset($this->allowedTables[$table])) return 0;
        try {
            $st = $this->db->query("SELECT COUNT(*) FROM `$table` WHERE 1=1" . $this->tenantSql());
            return (int)$st->fetchColumn();
        } catch (\Throwable $e) { return 0; }
    }
}
