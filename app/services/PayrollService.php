<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class PayrollService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    // ==================== PAYROLL RUNS ====================

    public function getRuns(array $filters = []): array
    {
        $where = ['pr.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['status'])) { $where[] = 'pr.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['from_date'])) { $where[] = 'pr.pay_period_start >= ?'; $params[] = $filters['from_date']; }
        if (!empty($filters['to_date'])) { $where[] = 'pr.pay_period_end <= ?'; $params[] = $filters['to_date']; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT pr.*, u.name as processed_by_name
            FROM payroll_runs pr
            LEFT JOIN users u ON pr.processed_by = u.id
            $whereSql
            ORDER BY pr.pay_period_start DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRun(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM payroll_runs WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$id, $this->tenantId()]);
        $run = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($run) {
            $run['items'] = $this->getRunItems($id);
        }
        return $run ?: null;
    }

    /**
     * Payroll entries (payroll_entries).
     *
     * NOTE (S451): admin/features/payroll called listPayroll(), which never
     * existed -> "Call to undefined method PayrollService::listPayroll()".
     *
     * The view reads base_salary / hra / allowances / deductions /
     * net_salary / status, but the table columns are basic_salary,
     * transport_allowance + medical_allowance + special_allowance and
     * total_deductions -> aliased here.
     */
    public function listPayroll(int $month = 0, int $year = 0, int $limit = 100): array
    {
        try {
            $tid = $this->tenantId();
            $where = ['1=1'];
            $params = [];
            if ($tid > 1) { $where[] = 'pe.tenant_id = ?'; $params[] = $tid; }
            $stmt = $this->conn->prepare(
                "SELECT pe.id, pe.employee_id, pe.basic_salary AS base_salary, pe.hra,
                        (COALESCE(pe.transport_allowance, 0) + COALESCE(pe.medical_allowance, 0)
                         + COALESCE(pe.special_allowance, 0)) AS allowances,
                        COALESCE(pe.total_deductions, 0) AS deductions,
                        pe.net_salary, pe.status, pe.created_at,
                        e.name as employee_name, e.employee_code
                 FROM payroll_entries pe
                 LEFT JOIN employees e ON e.id = pe.employee_id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY pe.created_at DESC LIMIT " . (int)$limit
            );
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[PayrollService::listPayroll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Employee bonuses (employee_bonuses).
     *
     * NOTE (S451): admin/features/payroll called listBonuses(), which never
     * existed -> "Call to undefined method PayrollService::listBonuses()".
     */
    public function listBonuses(int $employeeId = 0, int $limit = 100): array
    {
        try {
            $tid = $this->tenantId();
            $where = ['1=1'];
            $params = [];
            if ($employeeId > 0) { $where[] = 'eb.employee_id = ?'; $params[] = $employeeId; }
            if ($tid > 1) { $where[] = 'eb.tenant_id = ?'; $params[] = $tid; }
            $stmt = $this->conn->prepare(
                "SELECT eb.*, e.name as employee_name, e.employee_code
                 FROM employee_bonuses eb
                 LEFT JOIN employees e ON e.id = eb.employee_id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY eb.created_at DESC LIMIT " . (int)$limit
            );
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[PayrollService::listBonuses] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Employee salary advances (employee_advances).
     *
     * NOTE (S451): admin/features/payroll called listAdvances(), which never
     * existed -> "Call to undefined method PayrollService::listAdvances()".
     */
    public function listAdvances(int $employeeId = 0, int $limit = 100): array
    {
        try {
            $tid = $this->tenantId();
            $where = ['1=1'];
            $params = [];
            if ($employeeId > 0) { $where[] = 'ea.employee_id = ?'; $params[] = $employeeId; }
            if ($tid > 1) { $where[] = 'ea.tenant_id = ?'; $params[] = $tid; }
            $stmt = $this->conn->prepare(
                "SELECT ea.*, e.name as employee_name, e.employee_code
                 FROM employee_advances ea
                 LEFT JOIN employees e ON e.id = ea.employee_id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY ea.created_at DESC LIMIT " . (int)$limit
            );
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[PayrollService::listAdvances] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Payroll / attendance settings shown on the admin payroll desk.
     *
     * NOTE (S451): admin/features/payroll called getSettings(), which never
     * existed -> "Call to undefined method PayrollService::getSettings()".
     * The values come from app_settings (setting_key / setting_value); the
     * defaults below are what the desk falls back to.
     */
    public function getSettings(): array
    {
        $defaults = [
            'payroll' => [
                'working_days'          => 26,
                'pf_employee_percent'   => 12,
                'pf_employer_percent'   => 12,
                'esi_employee_percent'  => 0.75,
                'tds_monthly_threshold' => 50000,
                'pay_day'               => 1,
            ],
            'attendance' => [
                'grace_minutes'      => 15,
                'half_day_minutes'   => 240,
                'full_day_minutes'   => 480,
                'late_mark_percent'  => 0,
                'absent_mark_percent' => 0,
                'week_working_days'  => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
            ],
        ];

        try {
            $tid = $this->tenantId();
            $stmt = $this->conn->prepare(
                "SELECT setting_key, setting_value FROM app_settings
                 WHERE (setting_key LIKE 'payroll.%' OR setting_key LIKE 'attendance.%')"
                . ($tid > 1 ? " AND tenant_id = ?" : "")
            );
            $stmt->execute($tid > 1 ? [$tid] : []);
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $key = $row['setting_key'];
                $value = $row['setting_value'];
                if (strpos($key, '.') === false) { continue; }
                [$group, $name] = explode('.', $key, 2);
                if (!isset($defaults[$group])) { continue; }
                $defaults[$group][$name] = is_numeric($value) ? (strpos($value, '.') !== false ? (float)$value : (int)$value) : $value;
            }
        } catch (\Throwable $e) {
            error_log('[PayrollService::getSettings] ' . $e->getMessage());
        }

        return $defaults;
    }

    public function getRunItems(int $runId): array
    {
        $stmt = $this->conn->prepare("
            SELECT pi.*, u.name as employee_name, u.email as employee_email,
                   s.basic_salary, s.hra, s.da, s.conveyance, s.medical_allowance, s.special_allowance,
                   s.pf_employee as pf_pct, s.tds as tds_pct
            FROM payroll_items pi
            LEFT JOIN employee_profiles ep ON ep.legacy_id = pi.employee_id OR ep.user_id = pi.employee_id OR ep.id = pi.employee_id
            LEFT JOIN users u ON u.id = ep.user_id
            LEFT JOIN salary_structures s ON pi.salary_structure_id = s.id
            WHERE pi.run_id = ? AND pi.tenant_id = ?
            ORDER BY u.name
        ");
        $stmt->execute([$runId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createRun(array $data): array
    {
        // Accept both legacy (period_start) and canonical (pay_period_start) keys.
        $data['pay_period_start'] = $data['pay_period_start'] ?? $data['period_start'] ?? null;
        $data['pay_period_end'] = $data['pay_period_end'] ?? $data['period_end'] ?? null;
        $required = ['pay_period_start', 'pay_period_end', 'pay_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $runNumber = 'PRL-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $columns = array_merge(
            ['run_name', 'pay_period_start', 'pay_period_end', 'pay_date', 'status', 'notes', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $runNumber, $data['pay_period_start'], $data['pay_period_end'], $data['pay_date'],
            'draft', $data['notes'] ?? null, date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO payroll_runs (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'run_number' => $runNumber, 'message' => 'Payroll run created'];
    }

    public function updateRun(int $id, array $data): array
    {
        $allowed = ['pay_period_start', 'pay_period_end', 'pay_date', 'status', 'notes'];
        $fields = []; $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) { $fields[] = "$field = ?"; $params[] = $data[$field]; }
        }
        if (empty($fields)) return ['success' => false, 'message' => 'No valid fields'];

        // Status transition timestamps
        if (isset($data['status'])) {
            if ($data['status'] === 'approved') { $fields[] = 'approved_at = NOW()'; $params[] = $_SESSION['admin_id'] ?? 1; }
            elseif ($data['status'] === 'completed') { $fields[] = 'approved_at = NOW()'; }
        }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE payroll_runs SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Run updated' : 'Not found'];
    }

    public function calculateRun(int $runId): array
    {
        $run = $this->getRun($runId);
        if (!$run) return ['success' => false, 'message' => 'Run not found'];
        if ($run['status'] !== 'draft') return ['success' => false, 'message' => 'Only draft runs can be calculated'];

        $this->conn->beginTransaction();
        try {
            // Get all active employees with salary structures (profile-based, legacy-id compatible; INSERT still uses legacy_id)
            $stmt = $this->conn->prepare("
                SELECT ep.legacy_id as id, u.name, u.id as user_id, u.email, s.*
                FROM employee_profiles ep
                LEFT JOIN users u ON u.id = ep.user_id
                JOIN salary_structures s ON (s.employee_id = ep.legacy_id OR s.employee_id = ep.user_id OR s.employee_id = ep.id)
                WHERE ep.status = 'active' AND u.status = 'active' AND s.status = 'active'
            ");
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalGross = $totalDeductions = $totalNet = 0;
            $processed = 0;

            foreach ($employees as $emp) {
                $s = $emp; // salary structure data

                // Earnings
                $basic = $s['basic_salary'] ?? 0;
                $hra = $s['hra'] ?? 0;
                $da = $s['da'] ?? 0;
                $conveyance = $s['conveyance'] ?? 0;
                $medical = $s['medical_allowance'] ?? 0;
                $special = $s['special_allowance'] ?? 0;

                $gross = $basic + $hra + $da + $conveyance + $medical + $special;

                // Deductions
                $pfPct = $s['pf_employee'] ?? 12;
                $pf = round($basic * $pfPct / 100, 2);
                $tdsPct = $s['tds'] ?? 0;
                $tds = $tdsPct > 0 ? round($gross * $tdsPct / 100, 2) : 0;
                $profTax = 200; // Professional tax (varies by state)
                $esi = 0; // ESI if applicable

                // Advance/Loan recovery (from salary_advances)
                $advStmt = $this->conn->prepare("SELECT COALESCE(SUM(emi_amount),0) as total FROM salary_advances WHERE employee_id = ? AND status IN ('disbursed','repaid') AND remaining_balance > 0");
                $advStmt->execute([$emp['id']]);
                $advanceRecovery = (float)($advStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

                $deductions = $pf + $tds + $profTax + $esi + $advanceRecovery;
                $net = $gross - $deductions;

                // Insert payroll item
                $columns = array_merge(
                    ['run_id', 'employee_id', 'salary_structure_id',
                     'basic_salary', 'hra', 'da', 'conveyance', 'medical_allowance', 'special_allowance',
                     'gross_earnings',
                     'pf_employee', 'tds', 'professional_tax', 'esi_employee', 'advance_recovery',
                     'total_deductions', 'net_pay',
                     'status', 'created_at'],
                    array_keys($this->tenantInsertData())
                );

                $values = array_merge([
                    $runId, $emp['id'], $emp['id'], // salary_structure_id = emp id for now
                    $s['basic_salary'] ?? 0, $s['hra'] ?? 0, $s['da'] ?? 0, $s['conveyance'] ?? 0, $s['medical_allowance'] ?? 0, $s['special_allowance'] ?? 0,
                    $gross,
                    $pf, $tds, $profTax, 0, 0,
                    $pf + $tds + $profTax + $advanceRecovery,
                    $net,
                    'calculated', date('Y-m-d H:i:s')
                ], array_values($this->tenantInsertData()));

                $placeholders = str_repeat('?,', count($columns) - 1) . '?';
                $sql = "INSERT INTO payroll_items (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")";
                $itemStmt = $this->conn->prepare($sql);
                $itemStmt->execute($values);

                $totalGross += $gross;
                $totalDeductions += $deductions;
                $totalNet += $net;
                $processed++;
            }

            // Update run totals (payroll_runs status enum: draft|processing|completed|cancelled)
            $updStmt = $this->conn->prepare("UPDATE payroll_runs SET status = 'processing', total_employees = ?, total_gross = ?, total_deductions = ?, total_net = ? WHERE id = ? AND tenant_id = ?");
            $updStmt->execute([$processed, $totalGross, $totalDeductions, $totalNet, $runId, $this->tenantId()]);

            $this->conn->commit();
            return ['success' => true, 'message' => "Calculated $processed employees", 'processed' => $processed, 'totals' => ['gross' => $totalGross, 'deductions' => $totalDeductions, 'net' => $totalNet]];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function approveRun(int $runId): array
    {
        $stmt = $this->conn->prepare("UPDATE payroll_runs SET status = 'completed', approved_by = ?, approved_at = NOW() WHERE id = ? AND tenant_id = ? AND status = 'processing'");
        $stmt->execute([$_SESSION['admin_id'] ?? 1, $runId, $this->tenantId()]);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Payroll approved' : 'Run not in processing state'];
    }

    public function processRun(int $runId): array
    {
        $run = $this->getRun($runId);
        if (!$run || $run['status'] !== 'completed') return ['success' => false, 'message' => 'Run must be approved first'];

        // Mark items as paid
        $stmt = $this->conn->prepare("UPDATE payroll_items SET status = 'paid', paid_at = NOW() WHERE run_id = ? AND tenant_id = ?");
        $stmt->execute([$runId, $this->tenantId()]);
        $stmt = $this->conn->prepare("UPDATE payroll_runs SET processed_by = ? WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$_SESSION['admin_id'] ?? 1, $runId, $this->tenantId()]);

        // TODO: Generate bank transfer file, update advances, etc.
        return ['success' => true, 'message' => 'Payroll processed'];
    }

    // ==================== STATUTORY RETURNS ====================

    public function getStatutoryReturns(array $filters = []): array
    {
        $where = ['sr.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['return_type'])) { $where[] = 'sr.return_type = ?'; $params[] = $filters['return_type']; }
        if (!empty($filters['status'])) { $where[] = 'sr.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['period_year'])) { $where[] = 'sr.period_year = ?'; $params[] = $filters['period_year']; }
        if (!empty($filters['period_month'])) { $where[] = 'sr.period_month = ?'; $params[] = $filters['period_month']; }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT sr.*, u.name as filed_by_name
            FROM statutory_returns sr
            LEFT JOIN users u ON sr.filed_by = u.id
            WHERE $whereSql
            ORDER BY sr.period_year DESC, sr.period_month DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markReturnFiled(int $id, array $data): array
    {
        $fields = ['status = ?', 'filed_by = ?', 'filed_at = NOW()'];
        $params = [$data['status'] ?? 'filed', $_SESSION['admin_id'] ?? 1];

        if (!empty($data['challan_number'])) { $fields[] = 'challan_number = ?'; $params[] = $data['challan_number']; }
        if (!empty($data['challan_date'])) { $fields[] = 'challan_date = ?'; $params[] = $data['challan_date']; }
        if (!empty($data['acknowledgement_number'])) { $fields[] = 'acknowledgement_number = ?'; $params[] = $data['acknowledgement_number']; }
        if (!empty($data['acknowledgement_date'])) { $fields[] = 'acknowledgement_date = ?'; $params[] = $data['acknowledgement_date']; }
        if (!empty($data['amount_paid'])) { $fields[] = 'amount_paid = ?'; $params[] = $data['amount_paid']; }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE statutory_returns SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Return updated' : 'Not found'];
    }

    // ==================== ADVANCE RECOVERY ====================

    public function getAdvanceRecoveryForPayroll(int $runId): array
    {
        $stmt = $this->conn->prepare("
            SELECT pi.employee_id, pi.advance_recovery, u.name
            FROM payroll_items pi
            LEFT JOIN employee_profiles ep ON ep.legacy_id = pi.employee_id OR ep.user_id = pi.employee_id OR ep.id = pi.employee_id
            LEFT JOIN users u ON u.id = ep.user_id
            WHERE pi.run_id = ? AND pi.advance_recovery > 0 AND pi.tenant_id = ?
        ");
        $stmt->execute([$runId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c, status FROM payroll_runs WHERE tenant_id = ? GROUP BY status');
        $stmt->execute([$this->tenantId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $stats['runs_by_status'][$row['status']] = $row['c'];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM statutory_returns WHERE tenant_id = ? AND status = "pending" AND due_date <= CURDATE()');
        $stmt->execute([$this->tenantId()]);
        $stats['overdue_returns'] = $stmt->fetchColumn();

        return $stats;
    }
}