<?php

namespace App\Http\Controllers\Api;

use App\Core\Database\Database;
use App\Services\CompanyLoanService;
use App\Traits\TenantAwareTrait;

class LoanController extends BaseApiController
{
    use TenantAwareTrait;
    
    protected $loanService;

    public function __construct()
    {
        parent::__construct();
        try {
            $db = Database::getInstance();
            $pdo = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
            $this->loanService = new CompanyLoanService($pdo);
        } catch (\Exception $e) {
            $this->loanService = null;
        }
    }

    public function getLoans()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $loans = $this->loanService ? $this->loanService->getLoansForUser($userId) : [];
            $this->jsonResponse(['success' => true, 'data' => $loans]);
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch loans: ' . $e->getMessage(), 500);
        }
    }

    public function getLoanDetail($id)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $loan = $this->loanService ? $this->loanService->getLoanDetail((int)$id, $userId) : null;
            if (!$loan) {
                $this->jsonError('Loan not found', 404);
                return;
            }
            $this->jsonResponse(['success' => true, 'data' => $loan]);
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch loan detail: ' . $e->getMessage(), 500);
        }
    }

    public function getInstallments($id)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $installments = $this->loanService ? $this->loanService->getInstallments((int)$id, $userId) : [];
            $this->jsonResponse(['success' => true, 'data' => $installments]);
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch installments: ' . $e->getMessage(), 500);
        }
    }

    public function applyLoan()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            
            $result = $this->loanService ? $this->loanService->applyLoan($userId, $input) : ['success' => false, 'message' => 'Service unavailable'];
            if ($result['success'] ?? false) {
                $this->jsonResponse(['success' => true, 'data' => $result['data'] ?? []]);
            } else {
                $this->jsonError($result['message'] ?? 'Failed to apply for loan', 400);
            }
        } catch (\Exception $e) {
            $this->jsonError('Failed to apply for loan: ' . $e->getMessage(), 500);
        }
    }

    public function getOffers()
    {
        $this->setCorsHeaders();
        try {
            $offers = $this->loanService ? $this->loanService->getOffers() : [];
            $this->jsonResponse(['success' => true, 'data' => $offers]);
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch offers: ' . $e->getMessage(), 500);
        }
    }

    public function calculateEligibility()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $result = $this->loanService ? $this->loanService->calculateEligibility($input) : ['success' => false, 'message' => 'Service unavailable'];
            if ($result['success'] ?? false) {
                $this->jsonResponse(['success' => true, 'data' => $result]);
            } else {
                $this->jsonError($result['message'] ?? 'Failed to calculate eligibility', 400);
            }
        } catch (\Exception $e) {
            $this->jsonError('Failed to calculate eligibility: ' . $e->getMessage(), 500);
        }
    }

    public function getEarlySettlement($id)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $this->jsonError('User ID is required', 401);
                return;
            }
            $result = $this->loanService ? $this->loanService->getEarlySettlement((int)$id, $userId) : null;
            if ($result) {
                $this->jsonResponse(['success' => true, 'data' => $result]);
            } else {
                $this->jsonError('Loan not found', 404);
            }
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch early settlement: ' . $e->getMessage(), 500);
        }
    }
}
