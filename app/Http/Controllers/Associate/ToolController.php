<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateToolController
 * Handles tools and calculators
 */
class ToolController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Tools page
     */
    public function tools()
    {
        $this->requireAuth();
        $this->render('associate/tools', [
            'page_title' => 'Tools - Associate Portal',
            'page_description' => 'Useful calculators and tools',
        ], 'layouts/associate');
    }

    /**
     * EMI Calculator
     */
    public function emiCalculator()
    {
        $this->requireAuth();
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $principal = (float)($_POST['principal'] ?? 0);
                $rate = (float)($_POST['rate'] ?? 8.5) / 100 / 12; // Monthly rate
                $tenure = (int)($_POST['tenure'] ?? 20) * 12; // Months

                if ($principal <= 0 || $rate <= 0 || $tenure <= 0) {
                    throw new Exception('All fields must be greater than 0');
                }

                $emi = $principal * $rate * pow(1 + $rate, $tenure) / (pow(1 + $rate, $tenure) - 1);
                $totalPayment = $emi * $tenure;
                $totalInterest = $totalPayment - $principal;

                $result = [
                    'emi' => round($emi, 2),
                    'total_payment' => round($totalPayment, 2),
                    'total_interest' => round($totalInterest, 2),
                    'principal' => $principal,
                    'rate' => $_POST['rate'] ?? 8.5,
                    'tenure_years' => $_POST['tenure'] ?? 20,
                ];
            } catch (\Throwable $e) {
                $_SESSION['error'] = 'Calculation error: ' . $e->getMessage();
            }
        }

        $this->render('associate/emi_calculator', [
            'page_title' => 'EMI Calculator - Associate Portal',
            'page_description' => 'Calculate your EMI',
            'result' => $result,
        ], 'layouts/associate');
    }

    /**
     * Stamp Duty Calculator
     */
    public function stampDutyCalculator()
    {
        $this->requireAuth();
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $propertyValue = (float)($_POST['property_value'] ?? 0);
                $state = $_POST['state'] ?? 'Uttar Pradesh';
                $gender = $_POST['gender'] ?? 'male';
                $propertyType = $_POST['property_type'] ?? 'residential';

                if ($propertyValue <= 0) throw new Exception('Property value must be greater than 0');

                // Simplified stamp duty rates (varies by state)
                $rates = [
                    'Uttar Pradesh' => ['male' => 7, 'female' => 6],
                    'Maharashtra' => ['male' => 6, 'female' => 5],
                    'Delhi' => ['male' => 6, 'female' => 4],
                    'Karnataka' => ['male' => 5.6, 'female' => 5.6],
                    'Tamil Nadu' => ['male' => 7, 'female' => 7],
                    'Gujarat' => ['male' => 4.9, 'female' => 4.9],
                    'Rajasthan' => ['male' => 6, 'female' => 5],
                ];

                $rate = $rates[$state][$gender] ?? 7;
                $stampDuty = $propertyValue * $rate / 100;
                $registrationFee = $propertyValue * 0.01; // 1%

                $result = [
                    'stamp_duty' => round($stampDuty, 2),
                    'registration_fee' => round($registrationFee, 2),
                    'total' => round($stampDuty + $registrationFee, 2),
                    'rate' => $rate,
                    'property_value' => $propertyValue,
                ];
            } catch (\Throwable $e) {
                $_SESSION['error'] = 'Calculation error: ' . $e->getMessage();
            }
        }

        $this->render('associate/stamp_duty_calculator', [
            'page_title' => 'Stamp Duty Calculator - Associate Portal',
            'page_description' => 'Calculate stamp duty and registration fees',
            'result' => $result,
        ], 'layouts/associate');
    }

    /**
     * Plot Converter
     */
    public function plotConverter()
    {
        $this->requireAuth();
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $value = (float)($_POST['value'] ?? 0);
                $from = $_POST['from_unit'] ?? 'sqft';
                $to = $_POST['to_unit'] ?? 'sqyd';

                if ($value <= 0) throw new Exception('Value must be greater than 0');

                $units = [
                    'sqft' => 1,
                    'sqyd' => 9,
                    'sqm' => 10.764,
                    'acre' => 43560,
                    'hectare' => 107639,
                    'bigha' => 27000, // UP standard
                    'biswa' => 1350,
                ];

                if (!isset($units[$from]) || !isset($units[$to])) {
                    throw new Exception('Invalid unit');
                }

                $result = [
                    'input' => $value,
                    'from' => $from,
                    'to' => $to,
                    'output' => round($value * $units[$from] / $units[$to], 4),
                ];
            } catch (\Throwable $e) {
                $_SESSION['error'] = 'Conversion error: ' . $e->getMessage();
            }
        }

        $this->render('associate/plot_converter', [
            'page_title' => 'Plot Converter - Associate Portal',
            'page_description' => 'Convert between area units',
            'result' => $result,
        ], 'layouts/associate');
    }
}

