<?php
/**
 * StatutoryComplianceService
 * Handles Form 16, PF/ESI ECR, Professional Tax returns
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class StatutoryComplianceService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /* ═════════════════════════════════════════════════════════════
       FORM 16 - TDS Certificate under Section 203
       ═════════════════════════════════════════════════════════════ */

    /**
     * Generate Form 16 for an employee for a financial year
     */
    public function generateForm16(int $employeeId, int $financialYear): array
    {
        // Get employee details
        $emp = $this->fetchOne("
            SELECT e.*, u.name, u.email, u.pan_number, u.address
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.id = ?{$this->tenantSql()}
        ", array_merge([$employeeId], $this->tVal()));
        
        if (!$emp) throw new \Exception('Employee not found');

        // Get all salary payments for the financial year (April to March)
        $fyStart = $financialYear . '-04-01';
        $fyEnd = ($financialYear + 1) . '-03-31';
        
        $payments = $this->fetchAll("
            SELECT sp.*, ss.basic_salary, ss.hra, ss.conveyance, ss.medical_allowance, ss.special_allowance, ss.other_allowances,
                   ss.pf_employee, ss.esi_employee, ss.tds, ss.professional_tax
            FROM salary_payments sp
            LEFT JOIN salary_structures ss ON sp.salary_structure_id = ss.id{$this->tenantSqlForAlias('ss')}
            WHERE sp.employee_id = ? AND sp.payment_date BETWEEN ? AND ? AND sp.payment_status = 'paid'
            ORDER BY sp.payment_date
        ", array_merge([$employeeId, $fyStart, $fyEnd], $this->tVal()));

        // Calculate totals
        $totals = [
            'gross_salary' => 0,
            'basic_salary' => 0,
            'hra' => 0,
            'conveyance' => 0,
            'medical_allowance' => 0,
            'special_allowance' => 0,
            'other_allowances' => 0,
            'pf' => 0,
            'esi' => 0,
            'tds' => 0,
            'professional_tax' => 0,
            'total_deductions' => 0,
            'net_salary' => 0,
        ];

        foreach ($payments as $p) {
            $totals['gross_salary'] += (float)$p['gross_amount'];
            $totals['basic_salary'] += (float)$p['basic_amount'];
            $totals['pf'] += (float)($p['pf_employee'] ?? 0);
            $totals['esi'] += (float)($p['esi_employee'] ?? 0);
            $totals['tds'] += (float)$p['tds'];
            $totals['professional_tax'] += (float)$p['professional_tax'];
            $totals['net_salary'] += (float)$p['net_amount'];
        }
        $totals['total_deductions'] = $totals['pf'] + $totals['esi'] + $totals['tds'] + $totals['professional_tax'];

        // Calculate HRA exemption (Section 10(13A))
        $hraReceived = 0; // Would need rent receipts data
        $hraExempt = 0;   // min(hra_received, 50% of basic for metro, 40% non-metro, rent - 10% basic)

        // Standard deduction
        $standardDeduction = 50000;

        // Taxable income
        $grossSalary = $totals['gross_salary'];
        $taxableIncome = max(0, $grossSalary - $standardDeduction - $totals['pf'] - $hraExempt);

        return [
            'employee' => $emp,
            'financial_year' => $financialYear . '-' . ($financialYear + 1),
            'period' => 'April ' . $financialYear . ' to March ' . ($financialYear + 1),
            'payments' => $payments,
            'totals' => $totals,
            'taxable_income' => $taxableIncome,
            'standard_deduction' => $standardDeduction,
            'hra_exempt' => $hraExempt,
            'generated_at' => date('Y-m-d H:i:s'),
            'form16_no' => 'F16-' . $financialYear . '-' . str_pad($employeeId, 4, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * Generate Form 16 Part A (TDS deducted and deposited)
     */
    public function generateForm16PartA(int $employeeId, int $financialYear): array
    {
        $fyStart = $financialYear . '-04-01';
        $fyEnd = ($financialYear + 1) . '-03-31';
        
        $challans = $this->fetchAll("
            SELECT quarter, challan_no, challan_date, tds_amount, deposited_date
            FROM tds_challans
            WHERE employee_id = ? AND financial_year = ?{$this->tenantSql()}
            ORDER BY quarter, challan_date
        ", array_merge([$employeeId, $financialYear], $this->tVal()));

        $quarterlyTds = [
            'Q1' => 0, 'Q2' => 0, 'Q3' => 0, 'Q4' => 0
        ];
        
        foreach ($challans as $c) {
            $quarterlyTds[$c['quarter']] += (float)$c['tds_amount'];
        }

        return [
            'challans' => $challans,
            'quarterly_tds' => $quarterlyTds,
            'total_tds' => array_sum($quarterlyTds),
        ];
    }

    /* ═════════════════════════════════════════════════════════════
       PF ECR (Electronic Challan cum Return) - EPFO
       ═════════════════════════════════════════════════════════════ */

    /**
     * Generate PF ECR text file for EPFO portal upload
     * Format: Member ID | Member Name | Father's/Spouse Name | DOB | Gender | DOJ | DOE | Wages | EPF | EPS | EDLI | NCP Days | Refunds
     */
    public function generatePfEcr(int $month, int $year): string
    {
        $fyStart = date('Y-m-d', strtotime("{$year}-{$month}-01"));
        $fyEnd = date('Y-m-t', strtotime($fyStart));
        
        $employees = $this->fetchAll("
            SELECT e.*, u.name, u.pan_number, u.aadhaar_number, u.date_of_birth,
                   ss.basic_salary, ss.pf_employee, ss.pf_employer
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id{$this->tJoin('u')}
            LEFT JOIN salary_structures ss ON ss.employee_id = e.id AND ss.status='active'{$this->tenantSqlForAlias('ss')}
            WHERE e.status = 'active' AND e.tenant_id = ?
        ", [$this->tenantId()]);

        $lines = [];
        // Header (optional, some portals need it)
        $lines[] = "ECR for Month: " . date('F Y', strtotime($fyStart));
        $lines[] = "Establishment ID: " . ($this->getSetting('epf_establishment_id') ?? 'XXXXX');
        $lines[] = "";

        foreach ($employees as $emp) {
            if (!$emp['basic_salary']) continue;
            
            $pfBasic = min((float)$emp['basic_salary'], 15000);
            $epf = round($pfBasic * 0.12, 2);        // 12% EE
            $eps = round($pfBasic * 0.0833, 2);      // 8.33% ER to EPS (pension)
            $edli = round($pfBasic * 0.005, 2);      // 0.5% ER to EDLI
            $adminCharges = round($pfBasic * 0.005, 2); // 0.5% admin
            $totalEr = round($eps + $edli + $adminCharges, 2);
            
            // NCP (Non-Contributory Period) days - days absent without pay
            $att = $this->fetchOne("
                SELECT COUNT(*) as absent_days 
                FROM employee_attendance 
                WHERE employee_id=? AND attendance_date BETWEEN ? AND ? AND status='absent'
            ", [$emp['id'], $fyStart, $fyEnd]);
            $ncpDays = (int)($att['absent_days'] ?? 0);

            $memberId = $emp['employee_code'] ?? 'EMP' . str_pad($emp['id'], 6, '0', STR_PAD_LEFT);
            $name = $emp['name'] ?? 'UNKNOWN';
            $fatherName = ''; // Would need separate field
            $dob = $emp['date_of_birth'] ? date('d-m-Y', strtotime($emp['date_of_birth'])) : '01-01-1990';
            $gender = 'M'; // Would need gender field
            $doj = $emp['joining_date'] ? date('d-m-Y', strtotime($emp['joining_date'])) : date('d-m-Y');
            $doe = ''; // Date of exit (if applicable)
            $wages = round($pfBasic, 2);

            $line = implode('|', [
                $memberId,           // Member ID
                $name,               // Member Name
                $fatherName,         // Father's/Spouse Name
                $dob,                // Date of Birth
                $gender,             // Gender
                $doj,                // Date of Joining
                $doe,                // Date of Exit
                $wages,              // Wages
                $epf,                // EPF (EE 12%)
                $eps,                // EPS (ER 8.33%)
                $edli,               // EDLI (ER 0.5%)
                $ncpDays,            // NCP Days
                0,                   // Refund of Advances
            ]);
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    /* ═════════════════════════════════════════════════════════════
       ESI ECR - ESIC Contribution Return
       ════════════════════════════════════════════════════════════ */

    /**
     * Generate ESI ECR for ESIC portal
     */
    public function generateEsiEcr(int $month, int $year): string
    {
        $fyStart = date('Y-m-d', strtotime("{$year}-{$month}-01"));
        $fyEnd = date('Y-m-t', strtotime($fyStart));
        
        // ESI applicable only if gross <= 21000
        $employees = $this->fetchAll("
            SELECT e.*, u.name, u.esi_number,
                   sp.gross_amount, sp.esi_employee, sp.esi_employer
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id{$this->tJoin('u')}
            LEFT JOIN salary_payments sp ON sp.employee_id = e.id 
                AND sp.payment_month = ? AND sp.payment_year = ? AND sp.payment_status = 'paid'
            WHERE e.status = 'active' AND e.tenant_id = ?
        ", [$month, $year, $this->tenantId()]);

        $lines = [];
        $lines[] = "ESI ECR for " . date('F Y', strtotime($fyStart));
        $lines[] = "Employer Code: " . ($this->getSetting('esi_employer_code') ?? 'XXXXX');
        $lines[] = "";

        foreach ($employees as $emp) {
            $gross = (float)($emp['gross_amount'] ?? 0);
            if ($gross > 21000) continue; // ESI not applicable
            
            $esiEe = round($gross * 0.0075, 2);  // 0.75% EE
            $esiEr = round($gross * 0.0325, 2);  // 3.25% ER
            $total = $esiEe + $esiEr;

            $line = implode(',', [
                $emp['esi_number'] ?? $emp['employee_code'] ?? 'EMP' . str_pad($emp['id'], 6, '0', STR_PAD_LEFT),
                $emp['name'] ?? 'UNKNOWN',
                $gross,
                $esiEe,
                $esiEr,
                $total,
                0, // Days worked (would need calculation)
            ]);
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    /* ═════════════════════════════════════════════════════════════
       Professional Tax Return (State-wise)
       ════════════════════════════════════════════════════════════ */

    /**
     * Generate Professional Tax return (Maharashtra format as default)
     */
    public function generatePtReturn(int $month, int $year): array
    {
        $fyStart = date('Y-m-d', strtotime("{$year}-{$month}-01"));
        $fyEnd = date('Y-m-t', strtotime($fyStart));
        
        $employees = $this->fetchAll("
            SELECT e.*, u.name, u.pan_number,
                   sp.professional_tax, sp.gross_amount
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id{$this->tJoin('u')}
            LEFT JOIN salary_payments sp ON sp.employee_id = e.id 
                AND sp.payment_month = ? AND sp.payment_year = ? AND sp.payment_status = 'paid'
            WHERE e.status = 'active' AND e.tenant_id = ?
        ", [$month, $year, $this->tenantId()]);

        $totalPt = 0;
        $details = [];

        foreach ($employees as $emp) {
            $pt = (float)($emp['professional_tax'] ?? 0);
            if ($pt > 0) {
                $totalPt += $pt;
                $details[] = [
                    'employee_code' => $emp['employee_code'] ?? 'EMP' . str_pad($emp['id'], 6, '0', STR_PAD_LEFT),
                    'name' => $emp['name'] ?? 'UNKNOWN',
                    'pan' => $emp['pan_number'] ?? '',
                    'gross' => (float)$emp['gross_amount'] ?? 0,
                    'pt' => $pt,
                ];
            }
        }

        return [
            'period' => date('F Y', strtotime($fyStart)),
            'state' => 'Maharashtra', // Configurable
            'registration_no' => $this->getSetting('pt_registration_no') ?? 'PXXXXXXXX',
            'total_employees' => count($details),
            'total_pt' => $totalPt,
            'details' => $details,
            'due_date' => date('Y-m-d', strtotime('+15 days', strtotime($fyEnd))), // 15th of next month
        ];
    }

    /* ═════════════════════════════════════════════════════════════
       Helper Methods
       ════════════════════════════════════════════════════════════ */

    private function getSetting(string $key): ?string
    {
        $row = $this->fetchOne("SELECT setting_value FROM settings WHERE setting_key=? AND tenant_id=?", [$key, $this->tenantId()]);
        return $row['setting_value'] ?? null;
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('StatutoryComplianceService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('StatutoryComplianceService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }
}