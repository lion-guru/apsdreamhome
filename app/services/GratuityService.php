<?php
/**
 * GratuityService
 * Standalone gratuity calculation service for 5+ years service
 * Can be used independently or as part of F&F settlement
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class GratuityService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /**
     * Calculate gratuity for an employee
     * 
     * @param int $employeeId Employee ID
     * @param string $calculationDate Date for calculation (defaults to today)
     * @return array Gratuity calculation details
     */
    public function calculateGratuity(int $employeeId, string $calculationDate = null): array
    {
        $calculationDate = $calculationDate ?? date('Y-m-d');
        
        // Get employee details
        $employee = $this->fetchOne("
            SELECT e.*, u.name, u.pan_number, ss.basic_salary
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN salary_structures ss ON ss.employee_id = e.id AND ss.status = 'active'
            WHERE e.id = ? AND e.tenant_id = ?
            ORDER BY ss.effective_date DESC
            LIMIT 1
        ", [$employeeId, $this->tenantId()]);

        if (!$employee) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        $basicSalary = (float)($employee['basic_salary'] ?? 0);
        if ($basicSalary <= 0) {
            return ['success' => false, 'message' => 'Basic salary not found in salary structure'];
        }

        $joiningDate = $employee['joining_date'] ?? $employee['created_at'] ?? date('Y-m-d');
        $tenure = $this->calculateTenure($joiningDate, $calculationDate);
        $serviceYears = $tenure['years'] + ($tenure['months'] >= 6 ? 1 : 0);

        // Gratuity eligibility: 5 years continuous service
        $eligible = $serviceYears >= 5;

        $result = [
            'success' => true,
            'employee' => [
                'id' => $employeeId,
                'name' => $employee['name'],
                'employee_code' => $employee['employee_code'],
                'designation' => $employee['designation'],
                'department' => $employee['department'],
                'joining_date' => $joiningDate,
                'calculation_date' => $calculationDate,
            ],
            'tenure' => $tenure,
            'service_years_for_gratuity' => $serviceYears,
            'basic_salary' => $basicSalary,
            'eligible' => $eligible,
        ];

        if (!$eligible) {
            $result['message'] = "Not eligible - requires 5 years continuous service (current: {$serviceYears} years)";
            $result['gratuity_amount'] = 0;
            $result['details'] = [];
            return $result;
        }

        // Gratuity formula: (15/26) * Basic * Years of Service
        // 15 days salary per year, 26 working days per month
        $perDayBasic = $basicSalary / 26;
        $gratuityAmount = round($perDayBasic * 15 * $serviceYears, 2);

        // Statutory cap: ₹20 Lakhs
        $maxGratuity = 2000000;
        $capped = false;
        if ($gratuityAmount > $maxGratuity) {
            $gratuityAmount = $maxGratuity;
            $capped = true;
        }

        $result['gratuity_amount'] = $gratuityAmount;
        $result['capped'] = $capped;
        $result['per_day_basic'] = round($perDayBasic, 2);
        $result['formula'] = '(15/26) × Basic × Years of Service';
        $result['details'] = [
            'basic_salary' => $basicSalary,
            'per_day_basic' => round($perDayBasic, 2),
            'service_years' => $serviceYears,
            'calculation' => "({$basicSalary} / 26) × 15 × {$serviceYears} = ₹" . number_format($gratuityAmount, 2),
            'statutory_cap' => $maxGratuity,
            'capped' => $capped,
        ];

        return $result;
    }

    /**
     * Get gratuity eligibility for all employees
     */
    public function getAllEligibility(): array
    {
        $employees = $this->fetchAll("
            SELECT e.id, e.employee_code, u.name, e.designation, e.department, e.joining_date,
                   ss.basic_salary
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN salary_structures ss ON ss.employee_id = e.id AND ss.status = 'active'
            WHERE e.status = 'active' AND e.tenant_id = ?
            ORDER BY u.name
        ", [$this->tenantId()]);

        $results = [];
        foreach ($employees as $emp) {
            $calc = $this->calculateGratuity($emp['id']);
            $results[] = [
                'employee_id' => $emp['id'],
                'employee_code' => $emp['employee_code'],
                'name' => $emp['name'],
                'designation' => $emp['designation'],
                'department' => $emp['department'],
                'joining_date' => $emp['joining_date'],
                'basic_salary' => $emp['basic_salary'] ?? 0,
                'eligible' => $calc['eligible'] ?? false,
                'gratuity_amount' => $calc['gratuity_amount'] ?? 0,
                'service_years' => $calc['service_years_for_gratuity'] ?? 0,
                'tenure' => $calc['tenure'] ?? [],
            ];
        }

        return $results;
    }

    /**
     * Get gratuity liability report for finance
     */
    public function getLiabilityReport(): array
    {
        $all = $this->getAllEligibility();
        
        $totalLiability = 0;
        $eligibleCount = 0;
        $details = [];

        foreach ($all as $emp) {
            if ($emp['eligible']) {
                $eligibleCount++;
                $totalLiability += $emp['gratuity_amount'];
                $details[] = $emp;
            }
        }

        return [
            'total_employees' => count($all),
            'eligible_employees' => $eligibleCount,
            'total_liability' => round($totalLiability, 2),
            'details' => $details,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Calculate tenure between two dates
     */
    private function calculateTenure(string $joiningDate, string $calculationDate): array
    {
        $join = new \DateTime($joiningDate);
        $exit = new \DateTime($calculationDate);
        $diff = $join->diff($exit);
        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'total_days' => $diff->days,
        ];
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('GratuityService::fetchOne: ' . $e->getMessage());
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
            error_log('GratuityService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }
}