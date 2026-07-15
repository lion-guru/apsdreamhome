<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class LoanController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function getLoans()
    {
        // Get loans
    }

    public function getLoanDetail($id)
    {
        // Loan detail
    }

    public function getInstallments($id)
    {
        // Installments
    }

    public function applyLoan()
    {
        // Apply loan
    }

    public function getOffers()
    {
        // Loan offers
    }

    public function calculateEligibility()
    {
        // Calculate eligibility
    }

    public function getEarlySettlement($id)
    {
        // Early settlement
    }
}