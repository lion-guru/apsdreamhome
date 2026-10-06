<?php
/**
 * ArrearsEngine
 * Calculates salary arrears when salary structure is revised mid-year
 * Handles retrospective calculation for prior months in current financial year
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class ArrearsEngine
{
    use ServiceTenantTrait;

    private \PDO $pdo;
    private SalaryCalculationService $calc;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
        $this->calc = new SalaryCalculationService();
    }

    /**
     * Calculate arrears for an employee when salary structure changes
     * 
     * @param int $employeeId Employee ID
     * @param int $revisedMonth Month when revision takes effect (1-12)
     * @param int $revisedYear Year when revision takes effect
     * @param array $newStructure New salary structure data
     * @param int $effectiveFromMonth Month from which arrears apply (usually April of FY)
     * @param int $effectiveFromYear Year from which arrears apply
     * @return array Arrears calculation details
     */
    public function calculateArrears(
        int $employeeId, 
        int $revisedMonth, 
        int $revisedYear, 
        array $newStructure,
        int $effectiveFromMonth = 4,  // April start of FY
        int $effectiveFromYear = null
    ): array {
        $effectiveFromYear = $effectiveFromYear ?? $revisedYear;
        
        // If revision is before April, arrears from April of same year
        // If revision is April or later, arrears from April of that year
        if ($revisedMonth >= 4) {
            $arrearsFromMonth = 4;
            $arrearsFromYear = $revisedYear;
        } else {
            $arrearsFromMonth = 4;
            $arrearsFromYear = $revisedYear - 1;
        }

        // Get old salary structure (active before revision)
        $oldStructure = $this->getActiveStructureBefore($employeeId, $revisedMonth, $revisedYear);
        if (!$oldStructure) {
            return ['success' => false, 'message' => 'No previous salary structure found'];
        }

        $arrearsMonths = [];
        $totalArrears = 0;
        $totalArrearsBasic = 0;
        $totalArrearsGross = 0;
        $totalArrearsDeductions = 0;
        $totalArrearsNet = 0;

        // Calculate for each month from effective date to month before revision
        $currentMonth = $arrearsFromMonth;
        $currentYear = $arrearsFromYear;
        
        while (true) {
            // Stop if we've reached the revision month
            if ($currentYear > $revisedYear || ($currentYear == $revisedYear && $currentMonth >= $revisedMonth)) {
                break;
            }

            // Check if payment already exists for this month
            $existing = $this->fetchOne("
                SELECT * FROM salary_payments 
                WHERE employee_id=? AND payment_month=? AND payment_year=? AND payment_status='paid'
                {$this->tenantSql()}
            ", array_merge([$employeeId, $currentMonth, $currentYear], $this->tVal()));

            if ($existing) {
                // Calculate what SHOULD have been paid with new structure
                $newBreakdown = $this->calc->calculate($newStructure);
                $oldBreakdown = $this->calc->calculate($oldStructure);
                
                $arrears = [
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'month_name' => date('F', mktime(0,0,0,$currentMonth,1,$currentYear)),
                    'old_basic' => $oldBreakdown['basic_salary'],
                    'new_basic' => $newBreakdown['basic_salary'],
                    'old_gross' => $oldBreakdown['gross_salary'],
                    'new_gross' => $newBreakdown['gross_salary'],
                    'old_deductions' => $oldBreakdown['total_deductions'],
                    'new_deductions' => $newBreakdown['total_deductions'],
                    'old_net' => $oldBreakdown['net_salary'],
                    'new_net' => $newBreakdown['net_salary'],
                    'arrears_basic' => round($newBreakdown['basic_salary'] - $oldBreakdown['basic_salary'], 2),
                    'arrears_gross' => round($newBreakdown['gross_salary'] - $oldBreakdown['gross_salary'], 2),
                    'arrears_deductions' => round($newBreakdown['total_deductions'] - $oldBreakdown['total_deductions'], 2),
                    'arrears_net' => round($newBreakdown['net_salary'] - $oldBreakdown['net_salary'], 2),
                    'already_paid_net' => (float)$existing['net_amount'],
                ];
                
                $arrearsMonths[] = $arrears;
                $totalArrears += $arrears['arrears_net'];
                $totalArrearsBasic += $arrears['arrears_basic'];
                $totalArrearsGross += $arrears['arrears_gross'];
                $totalArrearsDeductions += $arrears['arrears_deductions'];
                $totalArrearsNet += $arrears['arrears_net'];
            }

            // Move to next month
            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
            
            // Safety break - don't go beyond current month/year
            if ($currentYear > date('Y') || ($currentYear == date('Y') && $currentMonth > date('n'))) {
                break;
            }
        }

        return [
            'success' => true,
            'employee_id' => $employeeId,
            'arrears_period' => "$arrearsFromMonth/$arrearsFromYear to " . ($revisedMonth - 1) . "/$revisedYear",
            'arrears_months' => $arrearsMonths,
            'summary' => [
                'total_months' => count($arrearsMonths),
                'total_arrears_basic' => round($totalArrearsBasic, 2),
                'total_arrears_gross' => round($totalArrearsGross, 2),
                'total_arrears_deductions' => round($totalArrearsDeductions, 2),
                'total_arrears_net' => round($totalArrearsNet, 2),
            ],
        ];
    }

    /**
     * Process and record arrears payments
     */
    public function processArrears(int $employeeId, int $revisedMonth, int $revisedYear, array $newStructure, int $processedBy): array
    {
        $calculation = $this->calculateArrears($employeeId, $revisedMonth, $revisedYear, $newStructure);
        
        if (!$calculation['success']) {
            return $calculation;
        }

        $this->pdo->beginTransaction();
        try {
            $tid = $this->tenantId();
            $paymentDate = date('Y-m-d'); // Today's date for arrears payment
            $arrearsId = 0;
            
            // Create the revised structure FIRST so arrears payments can FK to it.
            $newStructureId = $this->createRevisedStructure($employeeId, $newStructure, $revisedMonth, $revisedYear);

            foreach ($calculation['arrears_months'] as $arrears) {
                if ($arrears['arrears_net'] <= 0) continue; // Skip if no arrears

                // Create arrears payment record
                $cols = "employee_id, salary_structure_id, payment_month, payment_year, payment_date,
                         basic_amount, gross_amount, deduction_amount, net_amount,
                         payment_status, created_by, remarks, payment_type";
                $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, 'arrears'";

                $stmt = $this->pdo->prepare("
                    INSERT INTO salary_payments ($cols" . ($tid > 1 ? ", tenant_id" : "") . ")
                    VALUES ($vals" . ($tid > 1 ? ", ?" : "") . ")
                ");

                $params = [
                    $employeeId,
                    $newStructureId,
                    $arrears['month'],
                    $arrears['year'],
                    $paymentDate,
                    $arrears['arrears_basic'],
                    $arrears['arrears_gross'],
                    $arrears['arrears_deductions'],
                    $arrears['arrears_net'],
                    $processedBy,
                    "Arrears for {$arrears['month_name']} {$arrears['year']} (Salary Revision {$revisedMonth}/{$revisedYear})",
                ];
                if ($tid > 1) $params[] = $tid;

                $stmt->execute($params);
                $arrearsId = (int)$this->pdo->lastInsertId();
            }

            // Retire superseded active structures so exactly one stays active.
            $this->pdo->prepare("UPDATE salary_structures SET status='inactive' WHERE employee_id=? AND id<>? AND status='active'" . ($tid > 1 ? " AND tenant_id=?" : ""))->execute($tid > 1 ? [$employeeId, $newStructureId, $tid] : [$employeeId, $newStructureId]);

            $this->pdo->commit();
            return [
                'success' => true,
                'message' => "Arrears processed for {$calculation['summary']['total_months']} months",
                'arrears_id' => $arrearsId,
                'new_structure_id' => $newStructureId,
                'total_arrears' => $calculation['summary']['total_arrears_net'],
            ];

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get active salary structure before a given date
     */
    private function getActiveStructureBefore(int $employeeId, int $month, int $year): ?array
    {
        $date = sprintf('%04d-%02d-01', $year, $month);
        
        return $this->fetchOne("
            SELECT * FROM salary_structures 
            WHERE employee_id=? AND status='active' AND effective_date < ?{$this->tenantSql()}
            ORDER BY effective_date DESC LIMIT 1
        ", array_merge([$employeeId, $date], $this->tVal()));
    }

    /**
     * Create new revised salary structure record
     */
    private function createRevisedStructure(int $employeeId, array $data, int $month, int $year): int
    {
        $tid = $this->tenantId();
        $effectiveDate = sprintf('%04d-%02d-01', $year, $month);
        
        $basic = (float)($data['basic_salary'] ?? 0);
        $hra = (float)($data['hra'] ?? 0);
        $conveyance = (float)($data['conveyance'] ?? 0);
        $medical = (float)($data['medical_allowance'] ?? 0);
        $special = (float)($data['special_allowance'] ?? 0);
        $other = (float)($data['other_allowances'] ?? 0);
        $pf = (float)($data['pf_employee'] ?? 0);
        $tds = (float)($data['tds'] ?? 0);
        
        $gross = $basic + $hra + $conveyance + $medical + $special + $other;
        $deductions = $pf + $tds;
        $net = $gross - $deductions;

        // gross/total/net are STORED GENERATED — never inserted explicitly.
        $cols = "employee_id, basic_salary, hra, da, conveyance, medical_allowance, special_allowance, other_allowances, pf_employee, tds, effective_date, status, tenant_id, created_at";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW()";

        $stmt = $this->pdo->prepare("INSERT INTO salary_structures ($cols" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES ($vals" . ($tid > 1 ? ", ?" : "") . ")");
        $params = [$employeeId, $basic, $hra, (float)($data['da'] ?? 0), $conveyance, $medical, $special, $other, $pf, $tds, $effectiveDate, $tid];
        if ($tid > 1) $params[] = $tid;
        
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('ArrearsEngine::fetchOne: ' . $e->getMessage());
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
            error_log('ArrearsEngine::fetchAll: ' . $e->getMessage());
            return [];
        }
    }
}