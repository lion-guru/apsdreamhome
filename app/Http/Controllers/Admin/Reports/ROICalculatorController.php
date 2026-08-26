<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Core\Database\Database;

/**
 * ROI Calculator Controller
 * Property investment return analysis
 */
class ROICalculatorController extends \App\Http\Controllers\Admin\AdminController
{
    private $database;
    
    public function __construct()
    {
        parent::__construct();
        $this->database = Database::getInstance();
    }
    
    /**
     * Show ROI Calculator
     */
    public function index(): void
    {
        $this->requireLogin();
        
        $calculations = [];
        
        // If form submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $calculations = $this->calculateROI($_POST);
        }
        
        // Get sample properties for reference
        $properties = $this->getSampleProperties();
        
        $this->render('admin/reports/roi_calculator', [
            'calculations' => $calculations,
            'properties' => $properties,
            'title' => 'Property ROI Calculator'
        ]);
    }
    
    /**
     * Calculate ROI for property investment
     */
    private function calculateROI(array $data): array
    {
        $propertyPrice = floatval($data['property_price'] ?? 0);
        $downPayment = floatval($data['down_payment'] ?? 0);
        $loanAmount = floatval($data['loan_amount'] ?? 0);
        $interestRate = floatval($data['interest_rate'] ?? 7);
        $loanTenure = intval($data['loan_tenure'] ?? 20);
        $expectedRent = floatval($data['expected_rent'] ?? 0);
        $annualAppreciation = floatval($data['annual_appreciation'] ?? 5);
        $annualExpenses = floatval($data['annual_expenses'] ?? 0);
        
        // EMI Calculation
        $monthlyInterest = ($interestRate / 100) / 12;
        $numPayments = $loanTenure * 12;
        
        if ($monthlyInterest > 0) {
            $emi = $loanAmount * $monthlyInterest * pow(1 + $monthlyInterest, $numPayments) / 
                   (pow(1 + $monthlyInterest, $numPayments) - 1);
        } else {
            $emi = $loanAmount / $numPayments;
        }
        
        // Annual calculations
        $annualEmi = $emi * 12;
        $annualRentalIncome = $expectedRent * 12;
        $netAnnualIncome = $annualRentalIncome - $annualEmi - $annualExpenses;
        
        // Total investment
        $totalInvestment = $downPayment + ($annualEmi * $loanTenure);
        
        // Future property value
        $futureValue = $propertyPrice * pow(1 + ($annualAppreciation / 100), $loanTenure);
        
        // ROI Calculations
        $grossRentalYield = ($annualRentalIncome / $propertyPrice) * 100;
        $netRentalYield = ($netAnnualIncome / $propertyPrice) * 100;
        $capitalAppreciation = $futureValue - $propertyPrice;
        $totalReturn = ($capitalAppreciation + ($netAnnualIncome * $loanTenure));
        $roi = ($totalReturn / $downPayment) * 100;
        $roiAnnualized = $roi / $loanTenure;
        
        // Break-even analysis
        $breakEvenYears = $downPayment / ($netAnnualIncome > 0 ? $netAnnualIncome : 1);
        
        // Cash flow analysis (10 years)
        $cashFlow = [];
        for ($year = 1; $year <= 10; $year++) {
            $yearlyRent = $annualRentalIncome * pow(1.03, $year - 1); // 3% annual rent increase
            $yearlyEmi = $annualEmi;
            $yearlyExpenses = $annualExpenses * pow(1.05, $year - 1); // 5% expense increase
            $yearlyPropertyValue = $propertyPrice * pow(1 + ($annualAppreciation / 100), $year);
            
            $cashFlow[] = [
                'year' => $year,
                'rental_income' => round($yearlyRent, 2),
                'emi_paid' => round($yearlyEmi, 2),
                'expenses' => round($yearlyExpenses, 2),
                'net_cash_flow' => round($yearlyRent - $yearlyEmi - $yearlyExpenses, 2),
                'property_value' => round($yearlyPropertyValue, 2),
                'equity' => round($yearlyPropertyValue - ($loanAmount * (1 - $year / $loanTenure)), 2)
            ];
        }
        
        return [
            'inputs' => [
                'property_price' => $propertyPrice,
                'down_payment' => $downPayment,
                'loan_amount' => $loanAmount,
                'interest_rate' => $interestRate,
                'loan_tenure' => $loanTenure,
                'expected_rent' => $expectedRent,
                'annual_appreciation' => $annualAppreciation,
                'annual_expenses' => $annualExpenses
            ],
            'emi' => round($emi, 2),
            'annual_emi' => round($annualEmi, 2),
            'gross_rental_yield' => round($grossRentalYield, 2),
            'net_rental_yield' => round($netRentalYield, 2),
            'capital_appreciation' => round($capitalAppreciation, 2),
            'total_return' => round($totalReturn, 2),
            'roi' => round($roi, 2),
            'roi_annualized' => round($roiAnnualized, 2),
            'break_even_years' => round($breakEvenYears, 2),
            'future_property_value' => round($futureValue, 2),
            'cash_flow' => $cashFlow,
            'recommendation' => $this->getRecommendation($netRentalYield, $roiAnnualized)
        ];
    }
    
    /**
     * Get investment recommendation
     */
    private function getRecommendation(float $netYield, float $annualizedROI): string
    {
        if ($annualizedROI >= 15 && $netYield > 0) {
            return "🟢 EXCELLENT INVESTMENT - High returns with positive cash flow";
        } elseif ($annualizedROI >= 10 && $netYield > -2) {
            return "🟢 GOOD INVESTMENT - Solid returns, manageable cash flow";
        } elseif ($annualizedROI >= 7 && $netYield > -5) {
            return "🟡 MODERATE INVESTMENT - Average returns, monitor cash flow";
        } elseif ($annualizedROI > 0) {
            return "🟡 RISKY INVESTMENT - Low returns, negative cash flow";
        } else {
            return "🔴 NOT RECOMMENDED - Negative returns expected";
        }
    }
    
    /**
     * Get sample properties for reference
     */
    private function getSampleProperties(): array
    {
        $db = $this->database->getConnection();
        
        $sql = "
            SELECT 
                p.id,
                p.title,
                p.price,
                p.type,
                p.area_sqft,
                c.name as colony_name,
                d.name as district_name
            FROM properties p
            LEFT JOIN colonies c ON p.project_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            WHERE p.status = 'available'
            ORDER BY p.price ASC
            LIMIT 10
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * API endpoint for quick calculation
     */
    public function apiCalculate(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $result = $this->calculateROI($data);
        
        echo json_encode($result);
    }
    
    /**
     * Compare multiple properties
     */
    public function compare(): void
    {
        $this->requireLogin();
        
        $properties = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['property_ids'])) { $this->validateCsrfOrFail();
            $properties = $this->getPropertiesForComparison($_POST['property_ids']);
        }
        
        $this->render('admin/reports/roi_comparison', [
            'properties' => $properties,
            'title' => 'Property ROI Comparison'
        ]);
    }
    
    /**
     * Get properties for comparison
     */
    private function getPropertiesForComparison(array $propertyIds): array
    {
        $db = $this->database->getConnection();
        
        $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
        
        $sql = "
            SELECT 
                p.*,
                c.name as colony_name,
                d.name as district_name,
                s.name as state_name
            FROM properties p
            LEFT JOIN colonies c ON p.project_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id IN ($placeholders)
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($propertyIds);
        
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calculate ROI for each property
        foreach ($properties as &$property) {
            $property['roi_calculation'] = $this->calculateROI([
                'property_price' => $property['price'],
                'down_payment' => $property['price'] * 0.2, // 20% down
                'loan_amount' => $property['price'] * 0.8,
                'interest_rate' => 7.5,
                'loan_tenure' => 20,
                'expected_rent' => $property['price'] * 0.003, // 0.3% monthly rent
                'annual_appreciation' => 5,
                'annual_expenses' => $property['price'] * 0.01 // 1% annual expenses
            ]);
        }
        
        return $properties;
    }
}
