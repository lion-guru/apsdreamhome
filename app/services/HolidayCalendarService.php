<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class HolidayCalendarService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getHolidays(array $filters = []): array
    {
        $query = "SELECT * FROM holiday_calendar WHERE tenant_id = 1";
        $params = [1];

        if (!empty($filters['type'])) {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['region'])) {
            $query .= " AND (region = ? OR region IS NULL)";
            $params[] = $filters['region'];
        }
        if (!empty($filters['is_active'])) {
            $query .= " AND is_active = ?";
            $params[] = (int)$filters['is_active'];
        }
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(date) = ?";
            $params[] = $filters['year'];
        }
        if (!empty($filters['month'])) {
            $query .= " AND MONTH(date) = ?";
            $params[] = $filters['month'];
        }

        $query .= " ORDER BY date ASC";
        return $this->fetchAll($query, $params);
    }

    public function getHolidaysInRange(string $startDate, string $endDate): array
    {
        return $this->fetchAll("
            SELECT * FROM holiday_calendar
            WHERE tenant_id = 1 AND date BETWEEN ? AND ?
            ORDER BY date ASC", [1, $startDate, $endDate]);
    }

    public function isHoliday(string $date): bool
    {
        $result = $this->fetchOne("
            SELECT 1 FROM holiday_calendar
            WHERE date = ? AND is_active = 1 AND tenant_id = 1", [$date]);
        return (bool)$result;
    }

    public function createHoliday(array $data): array
    {
        $required = ['name', 'date', 'type'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field '$field' is required"];
            }
        }

        try {
            $this->execute("
                INSERT INTO holiday_calendar
                (tenant_id, name, date, type, region, description, is_half_day, is_working_day, is_recurring, recurrence_rule, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [
                    $this->tenantId(),
                    $data['name'],
                    $data['date'],
                    $data['type'],
                    $data['region'] ?? null,
                    $data['description'] ?? null,
                    $data['is_half_day'] ?? 0,
                    $data['is_working_day'] ?? 0,
                    $data['is_recurring'] ?? 0,
                    $data['recurrence_rule'] ?? null
                ]);

            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Holiday already exists for this date'];
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateHoliday(int $id, array $data): array
    {
        $allowed = ['name', 'date', 'type', 'region', 'description', 'is_half_day', 'is_working_day', 'is_recurring', 'recurrence_rule', 'is_active'];
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) return ['success' => false, 'message' => 'No valid fields'];

        $params[] = $id;
        $this->execute(
            "UPDATE holiday_calendar SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ? AND tenant_id = 1",
            array_merge($params, [$id])
        );

        return ['success' => true];
    }

    public function deleteHoliday(int $id): array
    {
        $this->execute("DELETE FROM holiday_calendar WHERE id = ? AND tenant_id = 1", [$id]);
        return ['success' => true];
    }

    public function getHolidayTypes(): array
    {
        return [
            'national' => 'National Holiday',
            'state' => 'State Holiday',
            'company' => 'Company Holiday',
            'regional' => 'Regional Holiday',
            'religious' => 'Religious Holiday',
            'optional' => 'Optional Holiday'
        ];
    }

    public function getStats(): array
    {
        $total = $this->fetchOne("SELECT COUNT(*) as c FROM holiday_calendar WHERE tenant_id = 1")['c'] ?? 0;
        $active = $this->fetchOne("SELECT COUNT(*) as c FROM holiday_calendar WHERE tenant_id = 1 AND is_active = 1")['c'] ?? 0;
        $thisYear = $this->fetchOne("SELECT COUNT(*) as c FROM holiday_calendar WHERE tenant_id = 1 AND YEAR(date) = YEAR(CURDATE())")['c'] ?? 0;
        $byType = $this->fetchAll("SELECT type, COUNT(*) as c FROM holiday_calendar WHERE tenant_id = 1 GROUP BY type");
        return [
            'total' => $total,
            'active' => $active,
            'this_year' => $thisYear,
            'by_type' => $byType
        ];
    }

    public function getNextHoliday(string $fromDate = null): ?array
    {
        $fromDate = $fromDate ?? date('Y-m-d');
        return $this->fetchOne("
            SELECT * FROM holiday_calendar
            WHERE tenant_id = 1 AND is_active = 1 AND date >= ?
            ORDER BY date ASC LIMIT 1", [$fromDate]);
    }

    public function bulkImport(array $holidays): array
    {
        $results = ['created' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($holidays as $index => $holiday) {
            $result = $this->createHoliday($holiday);
            if ($result['success']) {
                $results['created']++;
            } elseif (strpos($result['message'] ?? '', 'Duplicate') !== false) {
                $results['skipped']++;
            } else {
                $results['errors'][] = "Row " . ($index + 1) . ": " . ($result['message'] ?? 'Unknown error');
            }
        }
        return $results;
    }

    public function getHoliday(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM holiday_calendar WHERE id = ? AND tenant_id = 1", [$id]);
    }
}