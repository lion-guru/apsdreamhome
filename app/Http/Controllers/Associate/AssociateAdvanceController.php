<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Services\CommissionAdvanceService;

/**
 * Associate Commission Advance & Festival Loan Portal
 */
class AssociateAdvanceController extends BaseController
{
    protected string $layout = 'layouts/associate';
    private CommissionAdvanceService $advanceService;

    public function __construct()
    {
        parent::__construct();
        $this->advanceService = new CommissionAdvanceService();
    }

    private function associateId(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $id = (int)($_SESSION['user_id'] ?? 0);
        $role = $_SESSION['role'] ?? '';
        if ($id <= 0 || !in_array($role, ['associate', 'agent'], true)) {
            $_SESSION['error'] = 'Please login as an associate to access advances portal';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/associate/login');
            exit;
        }
        return $id;
    }

    public function index()
    {
        $aid = $this->associateId();
        $advances = $this->advanceService->getAssociateAdvances($aid);

        $activeAdvance = null;
        $totalRecovered = 0.0;
        foreach ($advances as $a) {
            if (in_array($a['status'], ['approved', 'disbursed'], true) && (float)$a['recovered_amount'] < (float)$a['approved_amount']) {
                $activeAdvance = $a;
            }
            $totalRecovered += (float)($a['recovered_amount'] ?? 0);
        }

        $this->render('associate/advances', [
            'page_title' => 'My Commission Advances & Loans',
            'advances' => $advances,
            'active_advance' => $activeAdvance,
            'total_recovered' => $totalRecovered,
        ], 'layouts/associate');
    }

    public function apply()
    {
        $aid = $this->associateId();
        $this->validateCsrfOrFail();

        $amount = (float)($_POST['requested_amount'] ?? 0);
        $tenure = (int)($_POST['tenure_months'] ?? 3);
        $purpose = trim((string)($_POST['purpose'] ?? 'Festival / Medical Advance'));

        $res = $this->advanceService->requestAdvance($aid, $amount, $tenure, $purpose);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect('/associate/advances');
    }
}
