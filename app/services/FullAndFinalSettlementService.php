<?php
/**
 * FullAndFinalSettlementService
 * Calculates complete exit settlement for departing employees
 * Includes: Notice pay, Leave encashment, Gratuity, Bonus, Deductions, PF/ESI/PT
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class FullAndFinalSettlementService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /**
     * Calculate full & final settlement for an employee
     */
    public function calculateSettlement(int $employeeId, array $params = []): array
    {
        $employee = $this->getEmployeeDetails($employeeId);
        if (!$employee) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        $lastWorkingDay = $params['last_working_day'] ?? date('Y-m-d');
        $resignationDate = $params['resignation_date'] ?? date('Y-m-d');
        $noticePeriodDays = (int)($params['notice_period_days'] ?? 30);
        $noticeServedDays = (int)($params['notice_served_days'] ?? 0);
        $exitType = $params['exit_type'] ?? 'resignation'; // resignation, termination, retirement

        // Get salary structure
        $salaryStructure = $this->getLatestSalaryStructure($employeeId);
        if (!$salaryStructure) {
            return ['success' => false, 'message' => 'No active salary structure found'];
        }

        $basicSalary = (float)$salaryStructure['basic_salary'];
        $grossSalary = (float)($salaryStructure['gross_salary'] ?? $basicSalary * 2);
        $ctc = (float)($salaryStructure['ctc'] ?? $grossSalary * 12);

        // Calculate joining date and tenure
        $joiningDate = $employee['joining_date'] ?? $employee['created_at'] ?? date('Y-m-d');
        $tenure = $this->calculateTenure($joiningDate, $lastWorkingDay);

        $settlement = [
            'employee' => [
                'id' => $employeeId,
                'name' => $employee['name'],
                'employee_code' => $employee['employee_code'],
                'designation' => $employee['designation'],
                'department' => $employee['department'],
                'joining_date' => $joiningDate,
                'last_working_day' => $lastWorkingDay,
                'tenure_years' => $tenure['years'],
                'tenure_months' => $tenure['months'],
                'tenure_days' => $tenure['days'],
            ],
            'salary' => [
                'basic' => $basicSalary,
                'gross_monthly' => $grossSalary,
                'ctc_annual' => $ctc,
            ],
            'components' => [],
            'totals' => [
                'earnings' => 0,
                'deductions' => 0,
                'net_payable' => 0,
            ],
        ];

        // 1. UNPAID SALARY (for days worked in final month)
        $unpaidSalary = $this->calculateUnpaidSalary($employeeId, $lastWorkingDay, $grossSalary);
        $settlement['components']['unpaid_salary'] = $unpaidSalary;
        $settlement['totals']['earnings'] += $unpaidSalary['amount'];

        // 2. NOTICE PAY / NOTICE RECOVERY
        $noticePay = $this->calculateNoticePay($basicSalary, $grossSalary, $noticePeriodDays, $noticeServedDays, $exitType);
        $settlement['components']['notice_pay'] = $noticePay;
        if ($noticePay['type'] === 'recovery') {
            $settlement['totals']['deductions'] += abs($noticePay['amount']);
        } else {
            $settlement['totals']['earnings'] += $noticePay['amount'];
        }

        // 3. LEAVE ENCASHMENT
        $leaveEncashment = $this->calculateLeaveEncashment($employeeId, $basicSalary, $lastWorkingDay);
        $settlement['components']['leave_encashment'] = $leaveEncashment;
        $settlement['totals']['earnings'] += $leaveEncashment['amount'];

        // 4. GRATUITY (if eligible - 5+ years)
        $gratuity = $this->calculateGratuity($basicSalary, $tenure['years'], $tenure['months'], $exitType);
        $settlement['components']['gratuity'] = $gratuity;
        if ($gratuity['eligible']) {
            $settlement['totals']['earnings'] += $gratuity['amount'];
        }

        // 5. BONUS / VARIABLE PAY (pro-rata)
        $bonus = $this->calculateProRataBonus($employeeId, $lastWorkingDay);
        $settlement['components']['bonus'] = $bonus;
        $settlement['totals']['earnings'] += $bonus['amount'];

        // 6. ADVANCE / LOAN RECOVERY
        $advanceRecovery = $this->calculateOutstandingAdvances($employeeId);
        $settlement['components']['advance_recovery'] = $advanceRecovery;
        $settlement['totals']['deductions'] += $advanceRecovery['amount'];

        // 7. ASSET RECOVERY (laptop, phone, etc.)
        $assetRecovery = $this->calculateAssetRecovery($employeeId);
        $settlement['components']['asset_recovery'] = $assetRecovery;
        $settlement['totals']['deductions'] += $assetRecovery['amount'];

        // 8. TDS ON SETTLEMENT
        $tds = $this->calculateSettlementTds($settlement['totals']['earnings'], $employeeId);
        $settlement['components']['tds'] = $tds;
        $settlement['totals']['deductions'] += $tds['amount'];

        // 9. PF / ESI CONTRIBUTIONS (employer share if applicable)
        $pfEsi = $this->calculateFinalPfEsi($employeeId, $basicSalary, $lastWorkingDay);
        $settlement['components']['pf_esi'] = $pfEsi;

        // FINAL CALCULATION
        $settlement['totals']['net_payable'] = $settlement['totals']['earnings'] - $settlement['totals']['deductions'];
        $settlement['totals']['net_payable'] = max(0, round($settlement['totals']['net_payable'], 2));

        // Add metadata
        $settlement['metadata'] = [
            'calculated_at' => date('Y-m-d H:i:s'),
            'calculated_by' => $_SESSION['admin_id'] ?? 0,
            'exit_type' => $exitType,
            'notice_period_days' => $noticePeriodDays,
            'notice_served_days' => $noticeServedDays,
        ];

        return ['success' => true, 'settlement' => $settlement];
    }

    /**
     * Process and record the settlement
     */
    public function processSettlement(int $employeeId, array $params = []): array
    {
        $calculation = $this->calculateSettlement($employeeId, $params);
        if (!$calculation['success']) return $calculation;

        $settlement = $calculation['settlement'];
        $this->pdo->beginTransaction();

        try {
            $tid = $this->tenantId();
            
            // Create settlement record
            $settlementNo = 'FNF-' . date('Ymd') . '-' . str_pad($employeeId, 4, '0', STR_PAD_LEFT);
            $stmt = $this->pdo->prepare("
                INSERT INTO employee_fnf_settlements 
                (settlement_no, employee_id, resignation_date, last_working_day, exit_type,
                 notice_period_days, notice_served_days, earnings_total, deductions_total, 
                 net_payable, settlement_details, status, tenant_id, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'calculated', ?, ?, NOW())
            ");
            $stmt->execute([
                $settlementNo, $employeeId, $params['resignation_date'] ?? date('Y-m-d'),
                $params['last_working_day'] ?? date('Y-m-d'), $params['exit_type'] ?? 'resignation',
                $params['notice_period_days'] ?? 30, $params['notice_served_days'] ?? 0,
                $settlement['totals']['earnings'], $settlement['totals']['deductions'],
                $settlement['totals']['net_payable'], json_encode($settlement['components']),
                $tid, $_SESSION['admin_id'] ?? 0
            ]);
            $settlementId = (int)$this->pdo->lastInsertId();

            // Create payment record in salary_payments if net payable > 0
            if ($settlement['totals']['net_payable'] > 0) {
                $month = (int)date('n', strtotime($params['last_working_day'] ?? date('Y-m-d')));
                $year = (int)date('Y', strtotime($params['last_working_day'] ?? date('Y-m-d')));
                
                $this->pdo->prepare("
                    INSERT INTO salary_payments 
                    (employee_id, salary_structure_id, payment_month, payment_year, payment_date,
                     basic_amount, gross_amount, deduction_amount, net_amount,
                     payment_status, payment_type, remarks, created_by, tenant_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'fnf_settlement', ?, ?, ?, NOW())
                ")->execute([
                    $employeeId, null, $month, $year, date('Y-m-d'),
                    0, 0, $settlement['totals']['deductions'], $settlement['totals']['net_payable'],
                    "Full & Final Settlement - {$settlementNo}", $_SESSION['admin_id'] ?? 0, $tid
                ]);
            }

            // Mark employee as offboarded
            $tid = $this->tenantId();
            $this->execute("UPDATE employees SET status='terminated', offboarded_at=NOW(), offboard_reason=? WHERE id=? AND tenant_id=?", 
                [$params['exit_type'] ?? 'resignation', $employeeId, $tid]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Settlement processed', 'settlement_id' => $settlementId, 'settlement_no' => $settlementNo, 'net_payable' => $settlement['totals']['net_payable']];

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ========== PRIVATE CALCULATION METHODS ==========

    private function getEmployeeDetails(int $employeeId): ?array
    {
        return $this->fetchOne("
            SELECT e.*, u.name, u.email, u.phone
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.id=?{$this->tenantSql()}
        ", array_merge([$employeeId], $this->tVal()));
    }

    private function getLatestSalaryStructure(int $employeeId): ?array
    {
        return $this->fetchOne("
            SELECT * FROM salary_structures 
            WHERE employee_id=? AND status='active'{$this->tenantSql()}
            ORDER BY effective_date DESC LIMIT 1
        ", array_merge([$employeeId], $this->tVal()));
    }

    private function calculateTenure(string $joiningDate, string $lastWorkingDay): array
    {
        $join = new \DateTime($joiningDate);
        $exit = new \DateTime($lastWorkingDay);
        $diff = $join->diff($exit);
        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'total_days' => $diff->days,
        ];
    }

    private function calculateUnpaidSalary(int $employeeId, string $lastWorkingDay, float $grossSalary): array
    {
        $date = new \DateTime($lastWorkingDay);
        $daysInMonth = (int)$date->format('t');
        $dayOfMonth = (int)$date->format('d');
        $daysWorked = $dayOfMonth; // Assuming worked all days up to last working day

        $perDaySalary = $grossSalary / $daysInMonth;
        $amount = round($perDaySalary * $daysWorked, 2);

        return [
            'type' => 'earning',
            'description' => "Salary for {$daysWorked} days worked in final month",
            'days_worked' => $daysWorked,
            'per_day_salary' => round($perDaySalary, 2),
            'amount' => $amount,
        ];
    }

    private function calculateNoticePay(float $basicSalary, float $grossSalary, int $noticePeriodDays, int $noticeServedDays, string $exitType): array
    {
        $perDayGross = $grossSalary / 30;
        $noticeDaysBalance = max(0, $noticePeriodDays - $noticeServedDays);

        if ($noticeDaysBalance === 0) {
            return ['type' => 'none', 'description' => 'Full notice period served', 'amount' => 0];
        }

        $amount = round($perDayGross * $noticeDaysBalance, 2);

        if ($exitType === 'termination' || $exitType === 'resignation') {
            // Company pays if terminated, employee pays if resigned without serving notice
            if ($exitType === 'termination') {
                return ['type' => 'earning', 'description' => "Notice pay for {$noticeDaysBalance} days (termination)", 'amount' => $amount];
            } else {
                return ['type' => 'recovery', 'description' => "Notice pay recovery for {$noticeDaysBalance} days not served", 'amount' => -$amount];
            }
        }

        return ['type' => 'none', 'amount' => 0];
    }

    private function calculateLeaveEncashment(int $employeeId, float $basicSalary, string $lastWorkingDay): array
    {
        // Get leave balances
        $emp = $this->fetchOne("SELECT id FROM employees WHERE user_id=?", [$employeeId]);
        if (!$emp) return ['eligible' => false, 'amount' => 0, 'description' => 'Employee record not found'];

        $balances = $this->fetchAll("
            SELECT lb.*, lt.name, lt.code, lt.is_encashable
            FROM employee_leave_balances lb
            LEFT JOIN leave_types lt ON lb.leave_type_id = lt.id
            WHERE lb.employee_id=? AND lb.year=YEAR(?)
        ", [$emp['id'], $lastWorkingDay]);

        $totalEncashableDays = 0;
        $details = [];
        foreach ($balances as $b) {
            if (($b['is_encashable'] ?? 1) && ($b['remaining_days'] ?? 0) > 0) {
                $days = (float)$b['remaining_days'];
                $totalEncashableDays += $days;
                $details[] = [
                    'leave_type' => $b['name'] ?? $b['code'],
                    'days' => $days,
                ];
            }
        }

        if ($totalEncashableDays === 0) {
            return ['eligible' => false, 'amount' => 0, 'description' => 'No encashable leave balance'];
        }

        // Encashment on basic salary (standard practice)
        $perDayBasic = $basicSalary / 30;
        $amount = round($perDayBasic * $totalEncashableDays, 2);

        return [
            'eligible' => true,
            'type' => 'earning',
            'description' => "Leave encashment for {$totalEncashableDays} days",
            'days' => $totalEncashableDays,
            'per_day_basic' => round($perDayBasic, 2),
            'details' => $details,
            'amount' => $amount,
        ];
    }

    private function calculateGratuity(float $basicSalary, int $years, int $months, string $exitType): array
    {
        // Gratuity eligibility: 5 years continuous service
        // For gratuity calculation, months >= 6 rounds up to next year
        $serviceYears = $years + ($months >= 6 ? 1 : 0);
        
        $eligible = $serviceYears >= 5;
        
        if (!$eligible) {
            return [
                'eligible' => false,
                'amount' => 0,
                'service_years' => $serviceYears,
                'description' => "Not eligible - requires 5 years continuous service (current: {$serviceYears} years)",
            ];
        }

        // Gratuity formula: (15/26) * Basic * Years of Service
        // 15 days salary per year, 26 working days per month
        $perDayBasic = $basicSalary / 26;
        $gratuityAmount = round($perDayBasic * 15 * $serviceYears, 2);

        // Cap at ₹20 Lakhs (statutory limit)
        $maxGratuity = 2000000;
        if ($gratuityAmount > $maxGratuity) {
            $gratuityAmount = $maxGratuity;
        }

        return [
            'eligible' => true,
            'type' => 'earning',
            'service_years' => $serviceYears,
            'per_day_basic' => round($perDayBasic, 2),
            'formula' => '(15/26) × Basic × Years',
            'description' => "Gratuity for {$serviceYears} years service",
            'amount' => $gratuityAmount,
            'capped' => $gratuityAmount === $maxGratuity,
        ];
    }

    private function calculateProRataBonus(int $employeeId, string $lastWorkingDay): array
    {
        // Get bonus from salary_payments for current FY
        $fyStart = date('Y-04-01', strtotime($lastWorkingDay));
        if (date('m-d', strtotime($lastWorkingDay)) < '04-01') {
            $fyStart = date('Y-04-01', strtotime('-1 year', strtotime($lastWorkingDay)));
        }
        $fyEnd = date('Y-m-d', strtotime('+1 year -1 day', strtotime($fyStart)));

        $bonuses = $this->fetchAll("
            SELECT SUM(net_amount) as total_bonus
            FROM salary_payments
            WHERE employee_id=? AND payment_type='bonus' AND payment_status='paid'
            AND payment_date BETWEEN ? AND ?
        ", [$employeeId, $fyStart, $fyEnd]);

        $totalBonus = (float)($bonuses[0]['total_bonus'] ?? 0);

        // Pro-rata for current year if not full year
        $fyStartDate = new \DateTime($fyStart);
        $exitDate = new \DateTime($lastWorkingDay);
        $daysInFY = $fyStartDate->diff(new \DateTime($fyEnd))->days + 1;
        $daysWorked = $fyStartDate->diff($exitDate)->days + 1;
        $proRataFactor = min(1, $daysWorked / $daysInFY);

        $proRataBonus = round($totalBonus * $proRataFactor, 2);
        $proRataPct = round($proRataFactor * 100, 2);

        return [
            'type' => 'earning',
            'description' => "Pro-rata bonus for current FY ({$proRataPct}%)",
            'total_bonus' => $totalBonus,
            'pro_rata_factor' => round($proRataFactor, 4),
            'amount' => $proRataBonus,
        ];
    }

    private function calculateOutstandingAdvances(int $employeeId): array
    {
        $advances = $this->fetchAll("
            SELECT a.*, COALESCE(SUM(r.emi_recovered),0) as recovered
            FROM employee_advances a
            LEFT JOIN advance_recovery_log r ON a.id = r.advance_id
            WHERE a.employee_id=? AND a.status='active' AND a.tenant_id=?
            GROUP BY a.id
        ", [$employeeId, $this->tenantId()]);

        $totalOutstanding = 0;
        $details = [];
        foreach ($advances as $adv) {
            $outstanding = (float)$adv['amount'] - (float)$adv['recovered'];
            if ($outstanding > 0) {
                $totalOutstanding += $outstanding;
                $details[] = [
                    'advance_no' => $adv['advance_no'],
                    'original_amount' => (float)$adv['amount'],
                    'recovered' => (float)$adv['recovered'],
                    'outstanding' => $outstanding,
                ];
            }
        }

        return [
            'type' => 'deduction',
            'description' => 'Outstanding advance/loan recovery',
            'details' => $details,
            'amount' => round($totalOutstanding, 2),
        ];
    }

    private function calculateAssetRecovery(int $employeeId): array
    {
        // Check for assigned assets not returned
        $assets = $this->fetchAll("
            SELECT * FROM employee_assets 
            WHERE employee_id=? AND returned_at IS NULL AND tenant_id=?
        ", [$employeeId, $this->tenantId()]);

        $totalRecovery = 0;
        $details = [];
        foreach ($assets as $asset) {
            $depreciatedValue = (float)$asset['current_value'] ?? (float)$asset['purchase_value'] * 0.5;
            $totalRecovery += $depreciatedValue;
            $details[] = [
                'asset_name' => $asset['asset_name'],
                'purchase_value' => (float)$asset['purchase_value'],
                'current_value' => $depreciatedValue,
            ];
        }

        return [
            'type' => 'deduction',
            'description' => 'Unreturned company assets recovery',
            'details' => $details,
            'amount' => round($totalRecovery, 2),
        ];
    }

    private function calculateSettlementTds(float $totalEarnings, int $employeeId): array
    {
        // TDS on settlement earnings (excluding exempt components like gratuity up to limit)
        // Simplified: 10% TDS on taxable settlement amount
        $taxableAmount = $totalEarnings; // In reality, would exclude exempt portions
        $tdsRate = 0.10; // 10% for salary income
        
        $tdsAmount = round($taxableAmount * $tdsRate, 2);

        return [
            'type' => 'deduction',
            'description' => 'TDS on settlement earnings @ 10%',
            'taxable_amount' => $taxableAmount,
            'rate' => $tdsRate * 100,
            'amount' => $tdsAmount,
        ];
    }

    private function calculateFinalPfEsi(int $employeeId, float $basicSalary, string $lastWorkingDay): array
    {
        // Final PF contribution for the month of exit
        $pfEmployee = round($basicSalary * 0.12, 2);
        $pfEmployer = round(min($basicSalary, 15000) * 0.12, 2);
        $esiEmployee = 0;
        $esiEmployer = 0;
        
        $gross = $basicSalary * 2; // Approximate
        if ($gross <= 21000) {
            $esiEmployee = round($gross * 0.0075, 2);
            $esiEmployer = round($gross * 0.0325, 2);
        }

        return [
            'pf_employee' => $pfEmployee,
            'pf_employer' => $pfEmployer,
            'esi_employee' => $esiEmployee,
            'esi_employer' => $esiEmployer,
            'description' => 'Final PF/ESI contributions for exit month',
        ];
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('FullAndFinalSettlementService::fetchOne: ' . $e->getMessage());
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
            error_log('FullAndFinalSettlementService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('FullAndFinalSettlementService::execute: ' . $e->getMessage());
            return 0;
        }
    }
}