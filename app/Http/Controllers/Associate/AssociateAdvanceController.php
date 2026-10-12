<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\Front\PageController;
use App\Services\CommissionAdvanceService;

/**
 * Associate Commission Advance & Festival Loan Portal
 */
class AssociateAdvanceController extends PageController
{
    private CommissionAdvanceService $advanceService;

    public function __construct()
    {
        parent::__construct();
        $this->advanceService = new CommissionAdvanceService();
    }

    private function associateId(): int
    {
        $id = (int)($_SESSION['user_id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
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
        ]);
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
