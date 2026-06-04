<?php
namespace App\Services;

use PDO;

/**
 * PayrollService - employee advances, bonuses, salary contracts, payroll entries
 */
class PayrollService
{
    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function requestAdvance(int $employeeId, float $amount, string $reason, int $approverId = 0): array
    {
        if ($amount <= 0) return ['error' => 'Amount must be positive'];
        $st = $this->db->prepare("INSERT INTO employee_advances (employee_id, amount, reason, status, approved_by, requested_at) VALUES (:e, :a, :r, 'pending', :ap, NOW())");
        $st->execute([':e' => $employeeId, ':a' => $amount, ':r' => $reason, ':ap' => $approverId ?: null]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function approveAdvance(int $id, int $approverId, bool $approve = true): array
    {
        $st = $this->db->prepare("UPDATE employee_advances SET status = :s, approved_by = :a, approved_at = NOW() WHERE id = :id");
        $st->execute([':s' => $approve ? 'approved' : 'rejected', ':a' => $approverId, ':id' => $id]);
        return ['ok' => true];
    }

    public function listAdvances(int $employeeId = 0, string $status = ''): array
    {
        try {
            $sql = "SELECT a.*, e.name as employee_name, u.name as approver_name FROM employee_advances a LEFT JOIN users e ON a.employee_id = e.id LEFT JOIN users u ON a.approved_by = u.id WHERE 1=1";
            $params = [];
            if ($employeeId) { $sql .= " AND a.employee_id = :e"; $params[':e'] = $employeeId; }
            if ($status) { $sql .= " AND a.approval_status = :s"; $params[':s'] = $status; }
            $sql .= " ORDER BY a.created_at DESC LIMIT 100";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("PayrollService::listAdvances error: " . $e->getMessage());
            return [];
        }
    }

    public function giveBonus(int $employeeId, float $amount, string $type, string $reason, int $givenBy = 0): array
    {
        $st = $this->db->prepare("INSERT INTO employee_bonuses (employee_id, amount, bonus_type, reason, given_by, given_at) VALUES (:e, :a, :t, :r, :g, NOW())");
        $st->execute([':e' => $employeeId, ':a' => $amount, ':t' => $type, ':r' => $reason, ':g' => $givenBy ?: null]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function listBonuses(int $employeeId = 0, string $type = ''): array
    {
        try {
            $sql = "SELECT b.*, e.name as employee_name, u.name as giver_name FROM employee_bonuses b LEFT JOIN users e ON b.employee_id = e.id LEFT JOIN users u ON b.approved_by = u.id WHERE 1=1";
            $params = [];
            if ($employeeId) { $sql .= " AND b.employee_id = :e"; $params[':e'] = $employeeId; }
            if ($type) { $sql .= " AND b.bonus_type = :t"; $params[':t'] = $type; }
            $sql .= " ORDER BY b.created_at DESC LIMIT 100";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("PayrollService::listBonuses error: " . $e->getMessage());
            return [];
        }
    }

    public function createSalaryContract(int $employeeId, float $baseSalary, float $hra, float $allowances, float $deductions, string $effectiveFrom, ?string $effectiveTo = null): array
    {
        $st = $this->db->prepare("INSERT INTO salary_contracts (employee_id, base_salary, hra, allowances, deductions, net_salary, effective_from, effective_to, status, created_at)
                                  VALUES (:e, :bs, :hra, :a, :d, :n, :f, :t, 'active', NOW())");
        $st->execute([
            ':e' => $employeeId, ':bs' => $baseSalary, ':hra' => $hra, ':a' => $allowances,
            ':d' => $deductions, ':n' => $baseSalary + $hra + $allowances - $deductions,
            ':f' => $effectiveFrom, ':t' => $effectiveTo
        ]);
        $id = (int)$this->db->lastInsertId();

        $st2 = $this->db->prepare("INSERT INTO salary_history (contract_id, employee_id, change_type, base_salary, hra, allowances, deductions, effective_from, created_at)
                                   VALUES (:c, :e, 'created', :bs, :hra, :a, :d, :f, NOW())");
        $st2->execute([':c' => $id, ':e' => $employeeId, ':bs' => $baseSalary, ':hra' => $hra, ':a' => $allowances, ':d' => $deductions, ':f' => $effectiveFrom]);

        return ['ok' => true, 'id' => $id];
    }

    public function getActiveContract(int $employeeId): ?array
    {
        $st = $this->db->prepare("SELECT * FROM salary_contracts WHERE employee_id = :e AND status = 'active' AND effective_from <= CURDATE() AND (effective_to IS NULL OR effective_to >= CURDATE()) ORDER BY effective_from DESC LIMIT 1");
        $st->execute([':e' => $employeeId]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function generatePayroll(int $month, int $year, int $generatedBy = 0): array
    {
        $st = $this->db->prepare("SELECT u.id as employee_id, u.name, sc.base_salary, sc.hra, sc.allowances, sc.deductions
                                  FROM users u
                                  LEFT JOIN salary_contracts sc ON sc.employee_id = u.id AND sc.status = 'active'
                                  WHERE u.role = 'employee' AND u.status = 'active'");
        $st->execute();
        $employees = $st->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $totalAmount = 0;
        foreach ($employees as $emp) {
            $base = (float)($emp['base_salary'] ?? 0);
            $hra = (float)($emp['hra'] ?? 0);
            $allw = (float)($emp['allowances'] ?? 0);
            $ded = (float)($emp['deductions'] ?? 0);
            $net = $base + $hra + $allw - $ded;

            $st2 = $this->db->prepare("INSERT INTO payroll_entries (employee_id, month, year, base_salary, hra, allowances, deductions, net_salary, status, generated_at, generated_by)
                                       VALUES (:e, :m, :y, :b, :hra, :a, :d, :n, 'generated', NOW(), :g)
                                       ON DUPLICATE KEY UPDATE base_salary = VALUES(base_salary), hra = VALUES(hra), allowances = VALUES(allowances), deductions = VALUES(deductions), net_salary = VALUES(net_salary), generated_at = NOW()");
            $st2->execute([':e' => $emp['employee_id'], ':m' => $month, ':y' => $year, ':b' => $base, ':hra' => $hra, ':a' => $allw, ':d' => $ded, ':n' => $net, ':g' => $generatedBy]);
            $count++;
            $totalAmount += $net;
        }
        return ['ok' => true, 'entries' => $count, 'total' => $totalAmount];
    }

    public function listPayroll(int $month, int $year, int $employeeId = 0): array
    {
        try {
            $sql = "SELECT p.*, u.name as employee_name, r.run_name, r.pay_period_start, r.pay_period_end
                    FROM payroll_entries p
                    LEFT JOIN users u ON p.employee_id = u.id
                    LEFT JOIN payroll_runs r ON p.payroll_run_id = r.id
                    WHERE MONTH(r.pay_period_start) = :m AND YEAR(r.pay_period_start) = :y";
            $params = [':m' => $month, ':y' => $year];
            if ($employeeId) { $sql .= " AND p.employee_id = :e"; $params[':e'] = $employeeId; }
            $sql .= " ORDER BY u.name LIMIT 200";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("PayrollService::listPayroll error: " . $e->getMessage());
            return [];
        }
    }

    public function markPaid(int $entryId, int $paidBy, string $method = 'bank_transfer'): array
    {
        $st = $this->db->prepare("UPDATE payroll_entries SET status = 'paid', paid_at = NOW(), paid_by = :b, payment_method = :m WHERE id = :id");
        $st->execute([':b' => $paidBy, ':m' => $method, ':id' => $entryId]);
        return ['ok' => true];
    }

    public function getSettings(): array
    {
        try {
            $st = $this->db->query("SELECT * FROM attendance_settings ORDER BY setting_key LIMIT 100");
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $out = [];
            foreach ($rows as $r) $out[$r['setting_key']] = json_decode($r['setting_value'] ?? 'null', true) ?? $r['setting_value'];
            return $out;
        } catch (\Throwable $e) {
            error_log("PayrollService::getSettings error: " . $e->getMessage());
            return [];
        }
    }

    public function setSetting(string $key, $value, string $desc = ''): array
    {
        $val = is_string($value) ? $value : json_encode($value);
        $st = $this->db->prepare("INSERT INTO attendance_settings (setting_key, setting_value, description, updated_at) VALUES (:k, :v, :d, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description), updated_at = NOW()");
        $st->execute([':k' => $key, ':v' => $val, ':d' => $desc]);
        return ['ok' => true];
    }
}
