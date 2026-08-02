<?php
namespace App\Services\Filing;

use PDO;
use \App\Traits\ServiceTenantTrait;

/**
 * EFilingService — Central e-filing orchestrator for TDS + GST
 * Manages submission lifecycle, deadlines, and portal interactions
 */
class EFilingService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct($pdo = null)
    {
        $this->db = $pdo ?: \App\Core\Database\Database::getInstance()->getConnection();
    }

    private function getPdo(): PDO
    {
        if ($this->db instanceof PDO) return $this->db;
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
        return $this->db;
    }

    // ========== Submission Lifecycle ==========

    public function createSubmission(array $data): int
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("INSERT INTO efiling_submissions
            (submission_type, reference_table, reference_id, financial_year, quarter, period_month, period_year,
             gstin, tan, pan, filing_date, due_date, status, filing_mode, total_records, total_amount, prepared_by, notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['submission_type'],
            $data['reference_table'] ?? null,
            $data['reference_id'] ?? null,
            $data['financial_year'],
            $data['quarter'] ?? null,
            $data['period_month'] ?? null,
            $data['period_year'] ?? null,
            $data['gstin'] ?? null,
            $data['tan'] ?? null,
            $data['pan'] ?? null,
            $data['filing_date'] ?? date('Y-m-d'),
            $data['due_date'] ?? null,
            'draft',
            $data['filing_mode'] ?? 'offline',
            $data['total_records'] ?? 0,
            $data['total_amount'] ?? 0,
            $data['prepared_by'] ?? null,
            $data['notes'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function updateSubmissionStatus(int $id, string $status, array $extra = []): bool
    {
        $pdo = $this->getPdo();
        $sets = ['status = ?'];
        $params = [$status];

        if ($status === 'submitted') {
            $sets[] = "submitted_by = ?";
            $params[] = $extra['submitted_by'] ?? null;
        }
        if (!empty($extra['portal_reference'])) {
            $sets[] = "portal_reference = ?";
            $params[] = $extra['portal_reference'];
        }
        if (!empty($extra['portal_response_json'])) {
            $sets[] = "portal_response_json = ?";
            $params[] = $extra['portal_response_json'];
        }
        if (!empty($extra['json_file_path'])) {
            $sets[] = "json_file_path = ?";
            $params[] = $extra['json_file_path'];
        }
        if (!empty($extra['error_message'])) {
            $sets[] = "error_message = ?";
            $params[] = $extra['error_message'];
        }

        $params[] = $id;
        $stmt = $pdo->prepare("UPDATE efiling_submissions SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function getSubmission(int $id): ?array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM efiling_submissions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listSubmissions(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['submission_type'])) {
            $where[] = "submission_type = ?";
            $params[] = $filters['submission_type'];
        }
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['financial_year'])) {
            $where[] = "financial_year = ?";
            $params[] = $filters['financial_year'];
        }
        if (!empty($filters['quarter'])) {
            $where[] = "quarter = ?";
            $params[] = $filters['quarter'];
        }

        $limit = min((int)($filters['limit'] ?? 50), 200);
        $offset = (int)($filters['offset'] ?? 0);

        $sql = "SELECT * FROM efiling_submissions WHERE " . implode(' AND ', $where)
             . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubmissionStats(): array
    {
        $pdo = $this->getPdo();
        $stats = [
            'total' => 0, 'draft' => 0, 'prepared' => 0, 'submitted' => 0,
            'accepted' => 0, 'rejected' => 0, 'this_month' => 0,
        ];
        try {
            $row = $pdo->query("SELECT
                COUNT(*) as total,
                SUM(status='draft') as draft,
                SUM(status='prepared') as prepared,
                SUM(status='submitted') as submitted,
                SUM(status='accepted') as accepted,
                SUM(status='rejected') as rejected,
                SUM(MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())) as this_month
                FROM efiling_submissions")->fetch(PDO::FETCH_ASSOC);
            if ($row) $stats = array_merge($stats, $row);
        } catch (\Exception $e) {
            error_log("[EFilingService] getSubmissionStats() exception: " . $e->getMessage());
        }
        return $stats;
    }

    // ========== Deadline Management ==========

    public function getUpcomingDeadlines(int $days = 30): array
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT * FROM efiling_deadlines
                WHERE due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND status IN ('upcoming','extended')
                ORDER BY due_date ASC");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EFilingService] getUpcomingDeadlines() exception: " . $e->getMessage());
            return [];
        }
    }

    public function getOverdueDeadlines(): array
    {
        try {
            $stmt = $this->getPdo()->query("SELECT * FROM efiling_deadlines
                WHERE due_date < CURDATE() AND status IN ('upcoming','extended')
                ORDER BY due_date ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EFilingService] getOverdueDeadlines() exception: " . $e->getMessage());
            return [];
        }
    }

    public function getDeadlineStats(): array
    {
        $pdo = $this->getPdo();
        try {
            $row = $pdo->query("SELECT
                COUNT(*) as total,
                SUM(status='upcoming') as upcoming,
                SUM(status='completed') as completed,
                SUM(status='overdue') as overdue,
                SUM(due_date < CURDATE() AND status IN ('upcoming','extended')) as actual_overdue,
                SUM(due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status='upcoming') as due_this_week,
                SUM(due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status='upcoming') as due_this_month
                FROM efiling_deadlines")->fetch(PDO::FETCH_ASSOC);
            return $row ?: [];
        } catch (\Exception $e) {
            error_log("[EFilingService] getDeadlineStats() exception: " . $e->getMessage());
            return [];
        }
    }

    public function markDeadlineComplete(int $deadlineId, int $submissionId = null): bool
    {
        try {
            $stmt = $this->getPdo()->prepare("UPDATE efiling_deadlines
                SET status = 'completed', submission_id = ? WHERE id = ?");
            return $stmt->execute([$submissionId, $deadlineId]);
        } catch (\Exception $e) {
            error_log("[EFilingService] markDeadlineComplete() exception: " . $e->getMessage());
            return false;
        }
    }

    public function getAllDeadlines(string $fy = null, string $type = null): array
    {
        $where = ['1=1'];
        $params = [];
        if ($fy) { $where[] = "financial_year = ?"; $params[] = $fy; }
        if ($type) { $where[] = "filing_type = ?"; $params[] = $type; }

        try {
            $stmt = $this->getPdo()->prepare("SELECT * FROM efiling_deadlines
                WHERE " . implode(' AND ', $where) . " ORDER BY due_date ASC");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EFilingService] getAllDeadlines() exception: " . $e->getMessage());
            return [];
        }
    }

    // ========== Dashboard ==========

    public function getDashboardData(): array
    {
        $stats = $this->getSubmissionStats();
        $deadlineStats = $this->getDeadlineStats();
        $upcoming = $this->getUpcomingDeadlines(14);
        $overdue = $this->getOverdueDeadlines();

        // TDS summary
        $tdsPending = 0;
        try {
            $tdsPending = (int)$this->getPdo()->query(
                "SELECT COUNT(*) FROM tds_register WHERE status='pending'"
            )->fetchColumn();
        } catch (\Exception $e) { error_log("[EFilingService] TDS pending query: " . $e->getMessage()); }

        // GST summary
        $gstPending = 0;
        try {
            $gstPending = (int)$this->getPdo()->query(
                "SELECT COUNT(*) FROM gst_returns WHERE filing_status IN ('draft','pending')"
            )->fetchColumn();
        } catch (\Exception $e) { error_log("[EFilingService] GST pending query: " . $e->getMessage()); }

        // Recent submissions
        $recent = $this->listSubmissions(['limit' => 10]);

        return [
            'stats' => $stats,
            'deadline_stats' => $deadlineStats,
            'upcoming_deadlines' => $upcoming,
            'overdue_deadlines' => $overdue,
            'tds_pending_count' => $tdsPending,
            'gst_pending_count' => $gstPending,
            'recent_submissions' => $recent,
        ];
    }

    // ========== Utility ==========

    public function getCurrentFinancialYear(): string
    {
        $month = (int)date('m');
        $year = (int)date('Y');
        return ($month >= 4 ? $year : $year - 1) . '-' . substr($year, -2);
    }

    public function getCurrentQuarter(): string
    {
        $month = (int)date('m');
        if ($month <= 3) return 'Q4';
        if ($month <= 6) return 'Q1';
        if ($month <= 9) return 'Q2';
        return 'Q3';
    }

    public function getFinancialYearPeriods(string $fy): array
    {
        // FY 2025-26 = Apr 2025 to Mar 2026
        $startYear = (int)substr($fy, 0, 4);
        $periods = [];
        $months = ['Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar'];
        for ($i = 0; $i < 12; $i++) {
            $m = (($i + 3) % 12) + 1;
            $y = ($i < 9) ? $startYear : $startYear + 1;
            $periods[] = [
                'month' => $m,
                'year' => $y,
                'label' => $months[$i] . ' ' . $y,
                'quarter' => 'Q' . (intdiv($i, 3) + 1),
            ];
        }
        return $periods;
    }
}
