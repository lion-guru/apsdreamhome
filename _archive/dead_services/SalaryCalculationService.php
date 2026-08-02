<?php
/**
 * SalaryCalculationService
 * Indian payroll calculation engine: Gross → Deductions → Net
 * Handles PF (12% cap ₹15K), ESI (0.75% if gross ≤ ₹21K), TDS, Professional Tax.
 */
namespace App\Services;

class SalaryCalculationService
{
    private const PF_RATE = 0.12;           // 12% of basic (capped at ₹15,000 basic)
    private const PF_CAP = 15000;           // Max basic for PF calculation
    private const ESI_RATE_EE = 0.0075;     // 0.75% employee
    private const ESI_RATE_ER = 0.0375;     // 3.75% employer
    private const ESI_GROSS_CAP = 21000;    // ESI applicable if gross ≤ ₹21,000
    private const PROFESSIONAL_TAX = 200;   // ₹200/month (Maharashtra slab)
    private const STANDARD_DEDUCTION = 50000; // ₹50,000/year for TDS

    /**
     * Calculate full salary breakdown from basic + allowances.
     * Returns: [basic, hra, conveyance, medical, special, other_allowances,
     *           gross, pf_ee, pf_er, esi_ee, esi_er, tds, pt, total_deductions, net]
     */
    public function calculate(array $input): array
    {
        $basic = (float)($input['basic_salary'] ?? 0);
        $hra = (float)($input['hra'] ?? 0);
        $conveyance = (float)($input['conveyance'] ?? 0);
        $medical = (float)($input['medical_allowance'] ?? 0);
        $special = (float)($input['special_allowance'] ?? 0);
        $other = (float)($input['other_allowances'] ?? 0);

        $gross = $basic + $hra + $conveyance + $medical + $special + $other;

        // PF: 12% of basic (capped at ₹15,000 basic)
        $pfBasic = min($basic, self::PF_CAP);
        $pfEmployee = round($pfBasic * self::PF_RATE, 2);
        $pfEmployer = round($pfBasic * self::PF_RATE, 2);

        // ESI: applicable only if gross ≤ ₹21,000
        $esiEmployee = 0;
        $esiEmployer = 0;
        if ($gross <= self::ESI_GROSS_CAP) {
            $esiEmployee = round($gross * self::ESI_RATE_EE, 2);
            $esiEmployer = round($gross * self::ESI_RATE_ER, 2);
        }

        // Professional Tax: ₹200 for gross > ₹15,000
        $pt = $gross > 15000 ? self::PROFESSIONAL_TAX : 0;

        // TDS: calculated on annual income (simplified monthly TDS)
        $tds = $this->calculateMonthlyTDS($gross, $pfEmployee, $esiEmployee);

        $totalDeductions = $pfEmployee + $esiEmployee + $tds + $pt + (float)($input['other_deductions'] ?? 0);
        $net = $gross - $totalDeductions;

        return [
            'basic_salary'       => $basic,
            'hra'                => $hra,
            'conveyance'         => $conveyance,
            'medical_allowance'  => $medical,
            'special_allowance'  => $special,
            'other_allowances'   => $other,
            'gross_salary'       => $gross,
            'pf_employee'        => $pfEmployee,
            'pf_employer'        => $pfEmployer,
            'esi_employee'       => $esiEmployee,
            'esi_employer'       => $esiEmployer,
            'tds'                => $tds,
            'professional_tax'   => $pt,
            'other_deductions'   => (float)($input['other_deductions'] ?? 0),
            'total_deductions'   => $totalDeductions,
            'net_salary'         => round($net, 2),
        ];
    }

    /**
     * Calculate monthly TDS based on annual income slab.
     * New regime slabs (FY 2024-25):
     *   0-3L: 0%, 3-7L: 5%, 7-10L: 10%, 10-12L: 15%, 12-15L: 20%, 15L+: 30%
     */
    private function calculateMonthlyTDS(float $gross, float $pf, float $esi): float
    {
        $annualGross = $gross * 12;
        $annualDeductions = ($pf + $esi) * 12 + self::STANDARD_DEDUCTION;
        $taxableIncome = max(0, $annualGross - $annualDeductions);

        $tax = 0;
        if ($taxableIncome > 1500000) {
            $tax += ($taxableIncome - 1500000) * 0.30;
            $taxableIncome = 1500000;
        }
        if ($taxableIncome > 1200000) {
            $tax += ($taxableIncome - 120000) * 0.20;
            $taxableIncome = 1200000;
        }
        if ($taxableIncome > 1000000) {
            $tax += ($taxableIncome - 1000000) * 0.15;
            $taxableIncome = 1000000;
        }
        if ($taxableIncome > 700000) {
            $tax += ($taxableIncome - 700000) * 0.10;
            $taxableIncome = 700000;
        }
        if ($taxableIncome > 300000) {
            $tax += ($taxableIncome - 300000) * 0.05;
        }

        // Rebate u/s 87A: full tax rebate if taxable ≤ ₹7L
        if ($taxableIncome <= 700000) {
            $tax = 0;
        }

        // Cess: 4% health & education cess
        $tax = $tax * 1.04;

        return round($tax / 12, 2);
    }

    /**
     * Quick preview: given a CTC, suggest basic/HRA/etc breakdown.
     * Standard: Basic=40% of CTC, HRA=50% of Basic, etc.
     */
    public function suggestFromCTC(float $ctc): array
    {
        $basic = round($ctc * 0.40);
        $hra = round($basic * 0.50);
        $conveyance = 1600;
        $medical = 1250;
        $special = $ctc - $basic - $hra - $conveyance - $medical;

        return $this->calculate([
            'basic_salary'      => $basic,
            'hra'               => $hra,
            'conveyance'        => $conveyance,
            'medical_allowance' => $medical,
            'special_allowance' => max(0, $special),
        ]);
    }
}
