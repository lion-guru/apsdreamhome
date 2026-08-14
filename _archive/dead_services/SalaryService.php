<?php

namespace App\Services;

use App\Core\Database\Database;

class SalaryService
{
    private $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function calculateSalary(int $userId, string $period): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, d.designation_name
            FROM users u
            LEFT JOIN designations d ON u.designation_id = d.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $designation = $user['designation_name'] ?? 'associate';
        $baseSalary = $this->getBaseSalary($designation);

        // Calculate incentives based on GBV
        $gbv = $this->getUserGBV($userId, $period);
        $incentive = $this->calculateIncentive($designation, $gbv);

        // Calculate deductions
        $deductions = $this->calculateDeductions($baseSalary, $gbv);

        $grossSalary = $baseSalary + $incentive;
        $netSalary = $grossSalary - $deductions;

        return [
            'success' => true,
            'user_id' => $userId,
            'period' => $period,
            'designation' => $designation,
            'base_salary' => $baseSalary,
            'gbv' => $gbv,
            'incentive' => $incentive,
            'gross_salary' => $grossSalary,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
        ];
    }

    private function getBaseSalary(string $designation): float
    {
        $salaries = [
            'associate' => 15000,
            'sr_associate' => 20000,
            'bdm' => 30000,
            'sr_bdm' => 40000,
            'vice_president' => 60000,
            'president' => 80000,
            'site_manager' => 100000,
        ];
        return $salaries[strtolower($designation)] ?? 15000;
    }

    private function getUserGBV(int $userId, string $period): float
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(gbv_amount), 0) as gbv
            FROM user_gbv_log
            WHERE user_id = ? AND period = ?
        ");
        $stmt->execute([$userId, $period]);
        return (float)$stmt->fetchColumn();
    }

    private function calculateIncentive(string $designation, float $gbv): float
    {
        $rates = [
            'associate' => 0.005,
            'sr_associate' => 0.0075,
            'bdm' => 0.01,
            'sr_bdm' => 0.0125,
            'vice_president' => 0.015,
            'president' => 0.02,
            'site_manager' => 0.025,
        ];
        $rate = $rates[strtolower($designation)] ?? 0.005;
        return $gbv * $rate;
    }

    private function calculateDeductions(float $baseSalary, float $gbv): float
    {
        // PF, ESI, PT, TDS
        $pf = min(1800, $baseSalary * 0.12);
        $esi = ($baseSalary <= 21000) ? $baseSalary * 0.0075 : 0;
        $pt = 200; // Professional tax
        $tds = ($baseSalary * 12 > 250000) ? ($baseSalary * 0.10) : 0;

        return $pf + $esi + $pt + $tds;
    }
}?>