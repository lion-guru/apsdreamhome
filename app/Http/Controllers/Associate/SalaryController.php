<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Services\MLM\LeadershipSalaryService;
use Exception;

/**
 * Associate Salary & Incentive Dashboard Controller
 * Handles Diwali Dhamaka salary targets, 40:60 leg balance, monthly maintenance, and fast-start kickstart bonus.
 */
class SalaryController extends BaseController
{
    use TenantAwareTrait;

    private LeadershipSalaryService $salaryService;

    public function __construct()
    {
        parent::__construct();
        $this->salaryService = new LeadershipSalaryService();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access your Salary Dashboard.';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Display Associate Salary Income Dashboard
     */
    public function index()
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        try {
            $data = $this->salaryService->getAssociateSalaryDashboardData($userId);

            $this->render('associate/salary', [
                'page_title'       => 'Salary Income Plan & Progress — Associate Portal',
                'page_description' => 'Track your Diwali Dhamaka monthly salary targets, 40:60 leg balance, and active monthly maintenance status.',
                'data'             => $data,
                'current_page'     => 'salary',
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('[Associate\SalaryController] index error: ' . $e->getMessage());
            $this->setFlash('error', 'Unable to load salary dashboard: ' . $e->getMessage());
            return $this->redirect('/associate/dashboard');
        }
    }
}

