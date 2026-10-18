<?php
/**
 * ScheduledReportService — CRUD for scheduled report email definitions (Gap 13).
 *
 * Wraps the scheduled_reports table. The actual firing is done by
 * scripts/cron_scheduled_reports.php (reads active rows whose next_run <= NOW(),
 * generates the report via ReportService, emails it, advances next_run).
 */
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;

class ScheduledReportService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(?string $status = null): array
    {
        $tid = $this->tenantId();
        $params = [$tid];
        $where = "tenant_id = ?";
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        return $this->db->fetchAll(
            "SELECT * FROM scheduled_reports WHERE $where ORDER BY next_run ASC, title ASC",
            $params
        ) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        return $this->db->fetchOne(
            "SELECT * FROM scheduled_reports WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        ) ?: null;
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $userId = $_SESSION['user_id'] ?? 1;

        $frequency = $data['frequency'] ?? 'weekly';
        $dayOfWeek = $data['day_of_week'] ?? null;
        $dayOfMonth = $data['day_of_month'] ?? null;
        $nextRun = $this->computeNextRun($frequency, $dayOfWeek, $dayOfMonth);

        $this->db->beginTransaction();
        try {
            $id = (int)$this->db->insert('scheduled_reports', [
                'tenant_id' => $tid,
                'title' => $data['title'] ?? '',
                'report_type' => $data['report_type'] ?? 'sales',
                'frequency' => $frequency,
                'format' => $data['format'] ?? 'csv',
                'parameters' => !empty($data['parameters']) ? json_encode($data['parameters']) : null,
                'recipients' => $data['recipients'] ?? null,
                'day_of_week' => $dayOfWeek,
                'day_of_month' => $dayOfMonth,
                'next_run' => $nextRun,
                'last_run' => null,
                'status' => !empty($data['status']) ? $data['status'] : 'active',
                'notes' => $data['notes'] ?? null,
            ]);
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $tid = $this->tenantId();
        $existing = $this->db->fetchOne(
            "SELECT id FROM scheduled_reports WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        );
        if (!$existing) {
            return false;
        }
        $update = [];
        foreach (['title', 'report_type', 'format', 'recipients', 'day_of_week', 'day_of_month', 'status', 'notes'] as $f) {
            if (array_key_exists($f, $data)) {
                $update[$f] = $data[$f];
            }
        }
        if (!empty($data['parameters'])) {
            $update['parameters'] = json_encode($data['parameters']);
        }
        if (!empty($data['frequency'])) {
            $update['frequency'] = $data['frequency'];
            $update['next_run'] = $this->computeNextRun(
                $data['frequency'],
                $data['day_of_week'] ?? null,
                $data['day_of_month'] ?? null
            );
        }
        if (empty($update)) {
            return true;
        }
        return (bool)$this->db->update('scheduled_reports', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $tid = $this->tenantId();
        $existing = $this->db->fetchOne(
            "SELECT id FROM scheduled_reports WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        );
        if (!$existing) {
            return false;
        }
        return (bool)$this->db->delete('scheduled_reports', ['id' => $id]);
    }

    /**
     * Force a run now: set next_run to NOW() so the cron picks it up.
     */
    public function runNow(int $id): bool
    {
        $tid = $this->tenantId();
        $existing = $this->db->fetchOne(
            "SELECT id FROM scheduled_reports WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        );
        if (!$existing) {
            return false;
        }
        return (bool)$this->db->update('scheduled_reports', ['next_run' => date('Y-m-d H:i:s')], ['id' => $id]);
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $total = (int)$this->db->fetchOne("SELECT COUNT(*) FROM scheduled_reports WHERE tenant_id = ?", [$tid])[''] ?? 0;
        $active = (int)$this->db->fetchOne("SELECT COUNT(*) FROM scheduled_reports WHERE tenant_id = ? AND status = 'active'", [$tid])[''] ?? 0;
        $due = (int)$this->db->fetchOne(
            "SELECT COUNT(*) FROM scheduled_reports WHERE tenant_id = ? AND status = 'active' AND next_run IS NOT NULL AND next_run <= NOW()",
            [$tid]
        )[''] ?? 0;
        return ['total' => $total, 'active' => $active, 'due' => $due];
    }

    private function computeNextRun(string $frequency, ?string $dayOfWeek, ?string $dayOfMonth): string
    {
        $now = time();
        switch ($frequency) {
            case 'daily':
                $next = strtotime('+1 day', $now);
                break;
            case 'weekly':
                $targetDay = $dayOfWeek ? date('N', strtotime($dayOfWeek)) : date('N', $now);
                $next = strtotime('next ' . ($dayOfWeek ?: 'monday'), $now);
                break;
            case 'monthly':
                $targetDay = $dayOfMonth ? (int)$dayOfMonth : 1;
                $next = strtotime('first day of next month', $now);
                if ($targetDay > 1) {
                    $next = strtotime('+' . ($targetDay - 1) . ' days', $next);
                }
                break;
            default:
                $next = strtotime('+1 week', $now);
                break;
        }
        return date('Y-m-d 09:00:00', $next);
    }

    private function tenantId(): int
    {
        return (int)TenantContext::getId();
    }
}
