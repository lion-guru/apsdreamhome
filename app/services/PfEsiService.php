<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class PfEsiService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function getConfig(): array
    {
        $tid = $this->tenantId();
        $rows = $this->db->fetchAll("SELECT config_key, config_value, description FROM pf_esi_config WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : []);
        $config = [];
        foreach ($rows as $r) { $config[$r['config_key']] = $r['config_value']; }
        return $config;
    }

    public function getDeductions(?string $monthYear = null, ?string $status = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM pf_esi_deductions WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($monthYear) { $sql .= " AND month_year = ?"; $params[] = $monthYear; }
        if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
        $sql .= " ORDER BY employee_name";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM pf_esi_deductions WHERE id = ?", [$id]);
    }

    public function processMonth(string $monthYear, int $userId): int
    {
        $tid = $this->tenantId();
        $config = $this->getConfig();
        $pfEmpRate = (float)($config['pf_employee_rate'] ?? 12);
        $pfEmprRate = (float)($config['pf_employer_rate'] ?? 12);
        $pfAdminRate = (float)($config['pf_admin_rate'] ?? 1.61);
        $pfWageLimit = (float)($config['pf_wage_limit'] ?? 15000);
        $esiEmpRate = (float)($config['esi_employee_rate'] ?? 0.75);
        $esiEmprRate = (float)($config['esi_employer_rate'] ?? 3.25);
        $esiWageLimit = (float)($config['esi_wage_limit'] ?? 21000);

        $employees = $this->db->fetchAll(
            "SELECT e.id, e.name, e.department, e.designation, e.basic_salary, e.gross_salary, e.status as emp_status
             FROM employees e WHERE e.status = 'active'" . ($tid > 1 ? " AND e.tenant_id = ?" : ""),
            $tid > 1 ? [$tid] : []
        );

        $count = 0;
        $this->db->beginTransaction();
        try {
            foreach ($employees as $emp) {
                $basic = (float)($emp['basic_salary'] ?? 0);
                $gross = (float)($emp['gross_salary'] ?? 0);
                if ($basic <= 0 && $gross <= 0) continue;

                $pfWage = min($basic, $pfWageLimit);
                $esiWage = min($gross, $esiWageLimit);

                $pfEmployee = round($pfWage * $pfEmpRate / 100, 2);
                $pfEmployer = round($pfWage * $pfEmprRate / 100, 2);
                $pfAdmin = round($pfWage * $pfAdminRate / 100, 2);
                $esiEmployee = round($esiWage * $esiEmpRate / 100, 2);
                $esiEmployer = round($esiWage * $esiEmprRate / 100, 2);

                $totalDeduction = $pfEmployee + $esiEmployee;
                $totalEmployer = $pfEmployer + $pfAdmin + $esiEmployer;

                $existing = $this->db->fetchOne("SELECT id FROM pf_esi_deductions WHERE employee_id = ? AND month_year = ?", [$emp['id'], $monthYear]);
                if ($existing) {
                    $this->db->update('pf_esi_deductions', [
                        'employee_name' => $emp['name'],
                        'department' => $emp['department'],
                        'designation' => $emp['designation'],
                        'basic_salary' => $basic,
                        'gross_salary' => $gross,
                        'pf_wage' => $pfWage,
                        'esi_wage' => $esiWage,
                        'pf_employee' => $pfEmployee,
                        'pf_employer' => $pfEmployer,
                        'pf_admin_charges' => $pfAdmin,
                        'esi_employee' => $esiEmployee,
                        'esi_employer' => $esiEmployer,
                        'total_deduction' => $totalDeduction,
                        'total_employer' => $totalEmployer,
                        'status' => 'processed',
                        'processed_by' => $userId,
                        'processed_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$existing['id']]);
                } else {
                    $this->db->insert('pf_esi_deductions', [
                        'tenant_id' => $tid,
                        'employee_id' => $emp['id'],
                        'employee_name' => $emp['name'],
                        'department' => $emp['department'],
                        'designation' => $emp['designation'],
                        'month_year' => $monthYear,
                        'basic_salary' => $basic,
                        'gross_salary' => $gross,
                        'pf_wage' => $pfWage,
                        'esi_wage' => $esiWage,
                        'pf_employee' => $pfEmployee,
                        'pf_employer' => $pfEmployer,
                        'pf_admin_charges' => $pfAdmin,
                        'esi_employee' => $esiEmployee,
                        'esi_employer' => $esiEmployer,
                        'total_deduction' => $totalDeduction,
                        'total_employer' => $totalEmployer,
                        'status' => 'processed',
                        'processed_by' => $userId,
                        'processed_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $count++;
            }
            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markPaid(array $ids, string $paymentRef, int $userId): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $this->db->update('pf_esi_deductions', [
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'payment_ref' => $paymentRef,
            ], 'id = ?', [(int)$id]);
            $count++;
        }
        return $count;
    }

    public function getStats(?string $monthYear = null): array
    {
        $tid = $this->tenantId();
        $tf = $tid > 1 ? " AND tenant_id = ?" : "";
        $tp = $tid > 1 ? [$tid] : [];
        $mf = $monthYear ? " AND month_year = ?" : [];
        $mp = $monthYear ? [$monthYear] : [];
        return [
            'total_employees' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM pf_esi_deductions WHERE 1=1" . $tf . $mf, array_merge($tp, $mp)),
            'total_pf_employee' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(pf_employee),0) FROM pf_esi_deductions WHERE 1=1" . $tf . $mf, array_merge($tp, $mp)),
            'total_pf_employer' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(pf_employer),0) FROM pf_esi_deductions WHERE 1=1" . $tf . $mf, array_merge($tp, $mp)),
            'total_esi_employee' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(esi_employee),0) FROM pf_esi_deductions WHERE 1=1" . $tf . $mf, array_merge($tp, $mp)),
            'total_esi_employer' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(esi_employer),0) FROM pf_esi_deductions WHERE 1=1" . $tf . $mf, array_merge($tp, $mp)),
            'total_deduction' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_deduction),0) FROM pf_esi_deductions WHERE 1=1" . $tf . $mf, array_merge($tp, $mp)),
            'total_employer' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_employer),0) FROM pf_esi_deductions WHERE 1=1" . $tf . $mf, array_merge($tp, $mp)),
            'processed' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM pf_esi_deductions WHERE status='processed'" . $tf . $mf, array_merge($tp, $mp)),
            'paid' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM pf_esi_deductions WHERE status='paid'" . $tf . $mf, array_merge($tp, $mp)),
        ];
    }

    public function getMonthYears(): array
    {
        return $this->db->fetchAll("SELECT DISTINCT month_year FROM pf_esi_deductions ORDER BY month_year DESC") ?: [];
    }

    public function generateECR(string $monthYear): array
    {
        $tid = $this->tenantId();
        $deductions = $this->db->fetchAll(
            "SELECT * FROM pf_esi_deductions WHERE month_year = ? AND status = 'paid'" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$monthYear, $tid] : [$monthYear]
        ) ?: [];

        $config = $this->getConfig();
        $estCode = $config['epf_est_code'] ?? 'MH/MUM/0000000/000';
        $esiCode = $config['esi_code'] ?? '0000000000000000';

        $lines = [];
        $lines[] = "ECR Filing - EPFO";
        $lines[] = "Establishment Code: $estCode";
        $lines[] = "ESI Code: $esiCode";
        $lines[] = "Month Year: $monthYear";
        $lines[] = "Generated: " . date('Y-m-d H:i:s');
        $lines[] = str_repeat('-', 80);
        $lines[] = sprintf("%-6s %-30s %-15s %-15s %-15s %-15s %-15s %-15s",
            'Emp ID', 'Name', 'PF Wage', 'ESI Wage', 'PF Emp', 'PF Er', 'ESI Emp', 'ESI Er');

        $totPFWage = $totESIWage = $totPFEmp = $totPFEr = $totESIEmp = $totESIEr = 0.0;

        foreach ($deductions as $d) {
            $lines[] = sprintf("%-6s %-30s %-15s %-15s %-15s %-15s %-15s %-15s",
                $d['employee_id'], substr($d['employee_name'], 0, 30),
                number_format((float)$d['pf_wage'], 2),
                number_format((float)$d['esi_wage'], 2),
                number_format((float)$d['pf_employee'], 2),
                number_format((float)$d['pf_employer'], 2),
                number_format((float)$d['esi_employee'], 2),
                number_format((float)$d['esi_employer'], 2));
            $totPFWage += (float)$d['pf_wage'];
            $totESIWage += (float)$d['esi_wage'];
            $totPFEmp += (float)$d['pf_employee'];
            $totPFEr += (float)$d['pf_employer'];
            $totESIEmp += (float)$d['esi_employee'];
            $totESIEr += (float)$d['esi_employer'];
        }

        $lines[] = str_repeat('-', 80);
        $lines[] = sprintf("%-6s %-30s %-15s %-15s %-15s %-15s %-15s %-15s",
            'TOTAL', '', number_format($totPFWage, 2), number_format($totESIWage, 2),
            number_format($totPFEmp, 2), number_format($totPFEr, 2),
            number_format($totESIEmp, 2), number_format($totESIEr, 2));

        return [
            'month_year' => $monthYear,
            'est_code' => $estCode,
            'esi_code' => $esiCode,
            'content' => implode(PHP_EOL, $lines),
            'count' => count($deductions),
            'totals' => [
                'pf_wage' => $totPFWage, 'esi_wage' => $totESIWage,
                'pf_employee' => $totPFEmp, 'pf_employer' => $totPFEr,
                'esi_employee' => $totESIEmp, 'esi_employer' => $totESIEr,
            ],
        ];
    }

    public function getLedger(int $employeeId): array
    {
        $tid = $this->tenantId();
        $employee = $this->db->fetchOne("SELECT * FROM employees WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$employeeId, $tid] : [$employeeId]);
        if (!$employee) {
            return ['employee' => null, 'ledger' => []];
        }

        $rows = $this->db->fetchAll(
            "SELECT * FROM pf_esi_deductions WHERE employee_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY month_year ASC",
            $tid > 1 ? [$employeeId, $tid] : [$employeeId]
        ) ?: [];

        $runningPF = $runningESI = $runningEmployer = 0.0;
        $ledger = [];
        foreach ($rows as $r) {
            $runningPF += (float)$r['total_deduction'];
            $runningESI += (float)$r['total_employer'];
            $runningEmployer += (float)$r['total_employer'];
            $ledger[] = array_merge($r, [
                'running_pf_deduction' => round($runningPF, 2),
                'running_esi_contribution' => round($runningESI, 2),
                'running_employer_cost' => round($runningEmployer, 2),
            ]);
        }

        return [
            'employee' => $employee,
            'ledger' => $ledger,
            'count' => count($ledger),
        ];
    }

    public function getChallanData(string $monthYear): array
    {
        $tid = $this->tenantId();
        $deductions = $this->db->fetchAll(
            "SELECT * FROM pf_esi_deductions WHERE month_year = ? AND status = 'paid'" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$monthYear, $tid] : [$monthYear]
        ) ?: [];

        $config = $this->getConfig();
        $totals = [
            'pf_employee' => 0.0, 'pf_employer' => 0.0, 'pf_admin_charges' => 0.0,
            'esi_employee' => 0.0, 'esi_employer' => 0.0,
            'total_deduction' => 0.0, 'total_employer' => 0.0,
        ];

        foreach ($deductions as $d) {
            $totals['pf_employee'] += (float)$d['pf_employee'];
            $totals['pf_employer'] += (float)$d['pf_employer'];
            $totals['pf_admin_charges'] += (float)$d['pf_admin_charges'];
            $totals['esi_employee'] += (float)$d['esi_employee'];
            $totals['esi_employer'] += (float)$d['esi_employer'];
            $totals['total_deduction'] += (float)$d['total_deduction'];
            $totals['total_employer'] += (float)$d['total_employer'];
        }

        $grandTotal = $totals['total_deduction'] + $totals['total_employer'];

        return [
            'month_year' => $monthYear,
            'est_code' => $config['epf_est_code'] ?? 'MH/MUM/0000000/000',
            'esi_code' => $config['esi_code'] ?? '0000000000000000',
            'deductions' => $deductions,
            'count' => count($deductions),
            'totals' => $totals,
            'grand_total' => round($grandTotal, 2),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }
}