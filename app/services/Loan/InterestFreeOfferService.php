<?php

namespace App\Services\Loan;

use App\Core\Database;
use PDO;
use \App\Traits\ServiceTenantTrait;

class InterestFreeOfferService
{
    use \App\Traits\ServiceTenantTrait;

    protected PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
    }

    public function calculateSavings(float $loanAmount, float $interestRate, int $tenureMonths, int $interestFreeMonths): array
    {
        if ($loanAmount <= 0 || $tenureMonths <= 0) {
            return [
                'normal_emi' => 0,
                'offer_emi' => 0,
                'normal_total_payable' => 0,
                'offer_total_payable' => 0,
                'total_savings' => 0,
                'interest_free_months' => $interestFreeMonths,
            ];
        }

        $monthlyRate = ($interestRate / 12) / 100;

        // Calculate normal EMI (with interest for full tenure)
        $normalEmi = 0;
        if ($monthlyRate > 0) {
            $pow = pow(1 + $monthlyRate, $tenureMonths);
            $normalEmi = $loanAmount * $monthlyRate * $pow / ($pow - 1);
        } else {
            $normalEmi = $loanAmount / $tenureMonths;
        }
        $normalTotal = $normalEmi * $tenureMonths;
        $normalInterest = $normalTotal - $loanAmount;

        // Calculate offer EMI (interest-free for first few months, normal for rest)
        // Interest-free months: principal only
        // Remaining months: normal EMI on reducing balance

        $interestFreeMonths = min($interestFreeMonths, $tenureMonths);
        $normalMonths = $tenureMonths - $interestFreeMonths;

        $offerTotalPayable = 0;

        if ($normalMonths <= 0) {
            // Entire tenure is interest-free
            $offerEmi = $loanAmount / $tenureMonths;
            $offerTotalPayable = $loanAmount;
        } else {
            // First X months: principal only = loanAmount / tenureMonths
            $principalPerMonth = $loanAmount / $tenureMonths;
            $interestFreeTotal = $principalPerMonth * $interestFreeMonths;

            // Remaining months: calculate EMI on remaining principal with interest
            $remainingPrincipal = $loanAmount - ($principalPerMonth * $interestFreeMonths);

            if ($monthlyRate > 0 && $remainingPrincipal > 0) {
                $pow2 = pow(1 + $monthlyRate, $normalMonths);
                $remainingEmi = $remainingPrincipal * $monthlyRate * $pow2 / ($pow2 - 1);
                $remainingTotal = $remainingEmi * $normalMonths;
            } else {
                $remainingEmi = $normalMonths > 0 ? $remainingPrincipal / $normalMonths : 0;
                $remainingTotal = $remainingPrincipal;
            }

            $offerTotalPayable = $interestFreeTotal + $remainingTotal;
            $offerEmi = $offerTotalPayable / $tenureMonths;
        }

        $totalSavings = $normalTotal - $offerTotalPayable;

        return [
            'normal_emi' => round($normalEmi, 2),
            'offer_emi' => round($offerEmi, 2),
            'normal_monthly_interest' => round($normalEmi - ($loanAmount / $tenureMonths), 2),
            'normal_total_payable' => round($normalTotal, 2),
            'normal_total_interest' => round($normalInterest, 2),
            'offer_total_payable' => round($offerTotalPayable, 2),
            'offer_total_interest' => round($offerTotalPayable - $loanAmount, 2),
            'total_savings' => round($totalSavings, 2),
            'savings_percentage' => $normalTotal > 0 ? round(($totalSavings / $normalTotal) * 100, 1) : 0,
            'interest_free_months' => $interestFreeMonths,
            'normal_months' => $normalMonths,
            'loan_amount' => $loanAmount,
            'interest_rate' => $interestRate,
            'tenure_months' => $tenureMonths,
            'original_price' => $loanAmount,
            'waived_amount' => round($normalInterest * ($interestFreeMonths / $tenureMonths), 2),
        ];
    }

    public function checkEligibility(float $loanAmount, int $tenureMonths, array $offer): array
    {
        $maxAmount = (float)($offer['max_amount'] ?? 0);
        $maxTenure = (int)($offer['max_tenure_months'] ?? 0);

        $issues = [];

        if ($maxAmount > 0 && $loanAmount > $maxAmount) {
            $issues[] = 'Loan amount exceeds maximum allowed (₹ ' . number_format($maxAmount) . ')';
        }
        if ($maxTenure > 0 && $tenureMonths > $maxTenure) {
            $issues[] = 'Tenure exceeds maximum allowed (' . $maxTenure . ' months)';
        }

        $validFrom = $offer['valid_from'] ?? null;
        $validUntil = $offer['valid_until'] ?? null;
        $today = date('Y-m-d');

        if ($validFrom && $today < $validFrom) {
            $issues[] = 'Offer is not yet valid (starts ' . $validFrom . ')';
        }
        if ($validUntil && $today > $validUntil) {
            $issues[] = 'Offer has expired (ended ' . $validUntil . ')';
        }
        if (!$offer['is_active']) {
            $issues[] = 'Offer is currently inactive';
        }

        return [
            'eligible' => empty($issues),
            'issues' => $issues,
            'offer_name' => $offer['name'],
        ];
    }
}
