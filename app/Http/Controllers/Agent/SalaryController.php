<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;
use App\Services\SalariedAgentService;

/**
 * Agent portal: own salary structure + current-month payroll.
 * Read-only; HR manages structures via /admin/agents/salaried.
 * (Additive file — does not touch existing portal controllers.)
 */
class SalaryController extends BaseController
{
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
            $_SESSION['error'] = 'Please login as an agent to access this page';
            $this->redirect('/agent/login');
        }
    }

    public function index()
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $structure = null;
        $history = [];
        $payroll = ['success' => false, 'error' => 'No active salary structure'];
        $isSalaried = false;
        try {
            $svc = new SalariedAgentService();
            $structure = $svc->getSalaryStructure($userId);
            $history = $svc->getSalaryHistory($userId);
            if ($structure) {
                $isSalaried = true;
                $payroll = $svc->calculateMonthlyPayroll($userId, (int)date('n'), (int)date('Y'));
            }
        } catch (\Throwable $e) {
            error_log('AgentSalaryController: ' . $e->getMessage());
        }
        $this->render('agent/salary', [
            'page_title' => 'My Salary - Agent Portal',
            'page_description' => 'Your salary structure and monthly payroll',
            'is_salaried' => $isSalaried,
            'structure' => $structure,
            'history' => $history,
            'payroll' => $payroll,
        ], 'layouts/agent');
    }
}
