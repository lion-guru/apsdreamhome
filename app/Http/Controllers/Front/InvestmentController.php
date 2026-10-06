<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\InvestmentService;

class InvestmentController extends BaseController
{
    private InvestmentService $investmentService;

    public function __construct()
    {
        parent::__construct();
        $this->investmentService = new InvestmentService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function requireCustomer(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    public function plans()
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        
        $plans = $this->investmentService->listPlans();
        $userInvestments = $this->investmentService->getUserInvestments($userId);
        $userStats = $this->investmentService->getStats($userId);

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/pages/investment_plans.php';
    }

    public function myInvestments()
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        
        $userInvestments = $this->investmentService->getUserInvestments($userId);
        $userStats = $this->investmentService->getStats($userId);

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/pages/user_investments.php';
    }

    public function invest()
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        
        $planId = (int)($_POST['plan_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMode = $_POST['payment_mode'] ?? 'wallet';

        if (!$planId || $amount <= 0) {
            $_SESSION['error'] = 'Invalid investment data';
            header('Location: ' . BASE_URL . '/investment-plans');
            exit;
        }

        try {
            $result = $this->investmentService->invest($userId, $planId, [
                'amount' => $amount,
                'payment_mode' => $paymentMode,
            ]);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'] ?? 'Investment successful!';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Investment failed';
            }
        } catch (\Throwable $e) {
            error_log('InvestmentController::invest error: ' . $e->getMessage());
            $_SESSION['error'] = 'Investment failed: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/user/investments');
        exit;
    }

    public function cancel()
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        $investmentId = (int)($_POST['investment_id'] ?? 0);
        $reason = trim((string)($_POST['reason'] ?? 'Customer cancelled'));

        if (!$investmentId) {
            $_SESSION['error'] = 'Invalid investment';
            header('Location: ' . BASE_URL . '/user/investments');
            exit;
        }

        try {
            $result = $this->investmentService->cancelInvestment($userId, $investmentId, $reason);
            if ($result['success']) {
                $_SESSION['success'] = $result['message'] ?? 'Investment cancelled';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Cancellation failed';
            }
        } catch (\Throwable $e) {
            error_log('InvestmentController::cancel error: ' . $e->getMessage());
            $_SESSION['error'] = 'Cancellation failed';
        }

        header('Location: ' . BASE_URL . '/user/investments');
        exit;
    }
}