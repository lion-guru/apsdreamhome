<?php

namespace App\Services\Finance;

use App\Core\Database\Database;

/**
 * EMI Calculator & Payment Plans Service
 * Financial planning tools for property buyers
 */
class EMICalculatorService
{
    private $database;
    
    // Default bank interest rates
    private $defaultRates = [
        'sbi' => ['name' => 'State Bank of India', 'rate' => 8.50, 'max_tenure' => 30],
        'hdfc' => ['name' => 'HDFC Bank', 'rate' => 8.60, 'max_tenure' => 30],
        'icici' => ['name' => 'ICICI Bank', 'rate' => 8.65, 'max_tenure' => 30],
        'axis' => ['name' => 'Axis Bank', 'rate' => 8.70, 'max_tenure' => 30],
        'pnb' => ['name' => 'Punjab National Bank', 'rate' => 8.55, 'max_tenure' => 30],
        'bob' => ['name' => 'Bank of Baroda', 'rate' => 8.50, 'max_tenure' => 30],
        'lic' => ['name' => 'LIC Housing Finance', 'rate' => 8.50, 'max_tenure' => 30],
    ];
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // EMI calculations history
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Payment plans
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Payment plan milestones
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Buyer payment schedules
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Installment details
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Bank interest rates
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Seed default rates
        $this->seedBankRates();
    }
    
    /**
     * Seed default bank rates
     */
    private function seedBankRates(): void
    {
        $sql = "INSERT INTO bank_interest_rates 
            (bank_code, bank_name, rate, max_tenure) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            bank_name = VALUES(bank_name), 
            rate = VALUES(rate)";
        
        $stmt = $this->database->prepare($sql);
        
        foreach ($this->defaultRates as $code => $data) {
            $stmt->execute([$code, $data['name'], $data['rate'], $data['max_tenure']]);
        }
    }
    
    /**
     * Calculate EMI
     */
    public function calculateEMI(float $principal, float $annualRate, int $years, ?int $userId = null, ?int $propertyId = null): array
    {
        $months = $years * 12;
        $monthlyRate = ($annualRate / 12) / 100;
        
        // EMI Formula: P × r × (1 + r)^n / ((1 + r)^n - 1)
        $emi = $principal * $monthlyRate * pow(1 + $monthlyRate, $months) / (pow(1 + $monthlyRate, $months) - 1);
        
        $totalPayment = $emi * $months;
        $totalInterest = $totalPayment - $principal;
        
        $result = [
            'principal' => $principal,
            'interest_rate' => $annualRate,
            'tenure_years' => $years,
            'tenure_months' => $months,
            'emi_amount' => round($emi, 2),
            'total_interest' => round($totalInterest, 2),
            'total_payment' => round($totalPayment, 2),
            'interest_component' => round(($totalInterest / $totalPayment) * 100, 2)
        ];
        
        // Save calculation
        if ($userId) {
            $this->saveCalculation($userId, $propertyId, $result);
        }
        
        return $result;
    }
    
    /**
     * Get detailed EMI schedule
     */
    public function getEMISchedule(float $principal, float $annualRate, int $years): array
    {
        $months = $years * 12;
        $monthlyRate = ($annualRate / 12) / 100;
        $emi = $principal * $monthlyRate * pow(1 + $monthlyRate, $months) / (pow(1 + $monthlyRate, $months) - 1);
        
        $schedule = [];
        $balance = $principal;
        $totalInterest = 0;
        
        for ($month = 1; $month <= $months; $month++) {
            $interest = $balance * $monthlyRate;
            $principalPaid = $emi - $interest;
            $balance -= $principalPaid;
            $totalInterest += $interest;
            
            $schedule[] = [
                'month' => $month,
                'emi' => round($emi, 2),
                'principal' => round($principalPaid, 2),
                'interest' => round($interest, 2),
                'balance' => round(max(0, $balance), 2),
                'cumulative_interest' => round($totalInterest, 2)
            ];
        }
        
        return $schedule;
    }
    
    /**
     * Compare multiple loan options
     */
    public function compareLoans(float $principal, array $scenarios): array
    {
        $comparisons = [];
        
        foreach ($scenarios as $scenario) {
            $result = $this->calculateEMI(
                $principal,
                $scenario['rate'],
                $scenario['years']
            );
            
            $comparisons[] = [
                'bank' => $scenario['bank'] ?? 'Custom',
                'rate' => $scenario['rate'],
                'years' => $scenario['years'],
                'emi' => $result['emi_amount'],
                'total_interest' => $result['total_interest'],
                'total_payment' => $result['total_payment']
            ];
        }
        
        // Sort by total payment (lowest first)
        usort($comparisons, fn($a, $b) => $a['total_payment'] <=> $b['total_payment']);
        
        return [
            'principal' => $principal,
            'comparisons' => $comparisons,
            'best_option' => $comparisons[0] ?? null
        ];
    }
    
    /**
     * Get all bank rates
     */
    public function getBankRates(): array
    {
        $sql = "SELECT * FROM bank_interest_rates WHERE is_active = 1 ORDER BY rate ASC";
        return $this->database->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Calculate affordability
     */
    public function calculateAffordability(float $monthlyIncome, float $existingEMIs = 0, float $otherObligations = 0): array
    {
        // Banks typically allow 50-60% of income for all EMIs
        $maxEMIPercent = 0.50;
        $availableForEMI = ($monthlyIncome * $maxEMIPercent) - $existingEMIs - $otherObligations;
        
        if ($availableForEMI <= 0) {
            return [
                'affordable' => false,
                'message' => 'Current obligations exceed maximum allowed EMI',
                'max_emi' => 0,
                'recommended_loan' => 0
            ];
        }
        
        // Calculate loan eligibility (assuming 8.5% rate for 20 years)
        $rate = 8.5 / 12 / 100;
        $months = 20 * 12;
        $maxLoan = $availableForEMI * ((pow(1 + $rate, $months) - 1) / ($rate * pow(1 + $rate, $months)));
        
        return [
            'affordable' => true,
            'monthly_income' => $monthlyIncome,
            'existing_emis' => $existingEMIs,
            'other_obligations' => $otherObligations,
            'available_for_emi' => round($availableForEMI, 2),
            'max_emi' => round($monthlyIncome * $maxEMIPercent, 2),
            'recommended_loan_amount' => round($maxLoan, 2),
            'recommended_emi' => round($availableForEMI, 2),
            'down_payment_needed' => round($maxLoan * 0.20, 2) // 20% down payment
        ];
    }
    
    /**
     * Calculate prepayment benefit
     */
    public function calculatePrepaymentBenefit(float $outstandingPrincipal, float $annualRate, int $remainingYears, float $prepaymentAmount): array
    {
        $remainingMonths = $remainingYears * 12;
        $monthlyRate = ($annualRate / 12) / 100;
        
        // Current EMI
        $currentEMI = $outstandingPrincipal * $monthlyRate * pow(1 + $monthlyRate, $remainingMonths) / 
                      (pow(1 + $monthlyRate, $remainingMonths) - 1);
        
        $currentTotalPayment = $currentEMI * $remainingMonths;
        $currentTotalInterest = $currentTotalPayment - $outstandingPrincipal;
        
        // After prepayment
        $newPrincipal = $outstandingPrincipal - $prepaymentAmount;
        $newEMI = $newPrincipal * $monthlyRate * pow(1 + $monthlyRate, $remainingMonths) / 
                  (pow(1 + $monthlyRate, $remainingMonths) - 1);
        
        $newTotalPayment = $newEMI * $remainingMonths + $prepaymentAmount;
        $newTotalInterest = $newTotalPayment - $outstandingPrincipal;
        
        $interestSaved = $currentTotalInterest - $newTotalInterest;
        $emiReduced = $currentEMI - $newEMI;
        
        return [
            'outstanding_principal' => $outstandingPrincipal,
            'prepayment_amount' => $prepaymentAmount,
            'current_emi' => round($currentEMI, 2),
            'new_emi' => round($newEMI, 2),
            'emi_reduction' => round($emiReduced, 2),
            'interest_saved' => round($interestSaved, 2),
            'tenure_unchanged' => true,
            'break_even_months' => $emiReduced > 0 ? round($prepaymentAmount / $emiReduced, 1) : 0
        ];
    }
    
    /**
     * Create payment plan
     */
    public function createPaymentPlan(int $propertyId, array $data): array
    {
        try {
            $pdo = $this->database->getConnection();
            $pdo->beginTransaction();
            
            // Insert plan
            $sql = "INSERT INTO payment_plans 
                (property_id, plan_name, plan_type, total_amount, down_payment_percent, 
                 number_of_installments, installment_frequency, interest_applicable, interest_rate, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $propertyId,
                $data['name'],
                $data['type'],
                $data['total_amount'],
                $data['down_payment_percent'] ?? 20,
                $data['number_of_installments'],
                $data['frequency'] ?? 'milestone',
                $data['interest_applicable'] ?? 0,
                $data['interest_rate'] ?? null,
                $data['description'] ?? null
            ]);
            
            $planId = $this->database->lastInsertId();
            
            // Create milestones
            if (!empty($data['milestones'])) {
                $milestoneSql = "INSERT INTO payment_plan_milestones 
                    (plan_id, milestone_order, milestone_name, percentage, amount, due_date, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $milestoneStmt = $this->database->prepare($milestoneSql);
                
                foreach ($data['milestones'] as $index => $milestone) {
                    $milestoneStmt->execute([
                        $planId,
                        $index + 1,
                        $milestone['name'],
                        $milestone['percentage'],
                        ($data['total_amount'] * $milestone['percentage']) / 100,
                        $milestone['due_date'] ?? null,
                        $milestone['description'] ?? null
                    ]);
                }
            }
            
            $pdo->commit();
            
            return ['success' => true, 'plan_id' => $planId];
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get payment plans for property
     */
    public function getPropertyPaymentPlans(int $propertyId): array
    {
        try {
            $sql = "SELECT pp.*, 
                COUNT(ppm.id) as milestone_count,
                SUM(ppm.amount) as milestones_total
                FROM payment_plans pp
                LEFT JOIN payment_plan_milestones ppm ON pp.id = ppm.plan_id
                WHERE pp.property_id = ? AND pp.is_active = 1
                GROUP BY pp.id
                ORDER BY pp.created_at DESC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$propertyId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Enroll buyer in payment plan
     */
    public function enrollInPlan(int $userId, int $propertyId, int $planId): array
    {
        try {
            try {
                // Get plan details
                $planSql = "SELECT * FROM payment_plans WHERE id = ?";
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $planStmt = $this->database->prepare($planSql);
            $planStmt->execute([$planId]);
            $plan = $planStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$plan) {
                return ['success' => false, 'error' => 'Payment plan not found'];
            }
            
            $downPayment = ($plan['total_amount'] * $plan['down_payment_percent']) / 100;
            $remainingAmount = $plan['total_amount'] - $downPayment;
            
            // Create schedule
            $scheduleSql = "INSERT INTO buyer_payment_schedules 
                (user_id, property_id, payment_plan_id, total_amount, remaining_amount) 
                VALUES (?, ?, ?, ?, ?)";
            
            $scheduleStmt = $this->database->prepare($scheduleSql);
            $scheduleStmt->execute([
                $userId, $propertyId, $planId, $plan['total_amount'], $remainingAmount
            ]);
            
            $scheduleId = $this->database->lastInsertId();
            
            // Create installments
            $milestoneSql = "SELECT * FROM payment_plan_milestones WHERE plan_id = ? ORDER BY milestone_order";
            $milestoneStmt = $this->database->prepare($milestoneSql);
            $milestoneStmt->execute([$planId]);
            $milestones = $milestoneStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $installmentSql = "INSERT INTO payment_installments 
                (schedule_id, installment_number, milestone_id, amount, due_date, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
            
            $installmentStmt = $this->database->prepare($installmentSql);
            
            foreach ($milestones as $index => $milestone) {
                $installmentStmt->execute([
                    $scheduleId,
                    $index + 1,
                    $milestone['id'],
                    $milestone['amount'],
                    $milestone['due_date'],
                    'pending'
                ]);
            }
            
            return [
                'success' => true,
                'schedule_id' => $scheduleId,
                'down_payment' => $downPayment,
                'installment_count' => count($milestones)
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Save calculation to history
     */
    private function saveCalculation(?int $userId, ?int $propertyId, array $data): void
    {
        $sql = "INSERT INTO emi_calculations 
            (user_id, property_id, principal_amount, interest_rate, tenure_years, 
             emi_amount, total_interest, total_payment, calculation_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $userId,
            $propertyId,
            $data['principal'],
            $data['interest_rate'],
            $data['tenure_years'],
            $data['emi_amount'],
            $data['total_interest'],
            $data['total_payment'],
            json_encode($data)
        ]);
    }
    
    /**
     * Get calculation history
     */
    public function getCalculationHistory(int $userId, int $limit = 10): array
    {
        $sql = "SELECT ec.*, p.title as property_title, pi.image_path as property_image
            FROM emi_calculations ec
            LEFT JOIN properties p ON ec.property_id = p.id
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE ec.user_id = ?
            ORDER BY ec.created_at DESC
            LIMIT ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
