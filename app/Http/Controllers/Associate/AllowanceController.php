<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Services\MLM\LeadershipExpenseFundService;
use App\Core\Database\Database;
use Exception;
use PDO;

/**
 * Associate Allowance Controller
 * Handles Office Support Allowance and Site Visit Vehicle/Fuel Reimbursement claims.
 */
class AllowanceController extends BaseController
{
    use TenantAwareTrait;

    private LeadershipExpenseFundService $fundService;

    public function __construct()
    {
        parent::__construct();
        $this->fundService = new LeadershipExpenseFundService();
    }

    private function requireAuth(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access Allowances & Claims.';
            $this->redirect('/associate/login');
        }
    }

    public function index()
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        try {
            $db = Database::getInstance()->getConnection();

            // Fetch user's rank
            $stmt = $db->prepare("SELECT mlm_rank, name, email, phone FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $rank = $user['mlm_rank'] ?? 'Ass.';

            // Evaluate office support
            $officeEval = $this->fundService->evaluateOfficeSupport($userId, $rank);

            // Fetch user's completed site visits eligible for fuel claim
            $stmt = $db->prepare("
                SELECT sv.*, 
                       (SELECT COUNT(*) FROM mlm_leader_allowances WHERE site_visit_id = sv.id) as already_claimed
                FROM site_visits sv
                WHERE (sv.user_id = ? OR sv.assigned_to = ?)
                ORDER BY sv.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$userId, $userId]);
            $allVisits = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Fetch past claims
            $claims = $this->fundService->getUserAllowances($userId);

            $this->render('associate/allowances', [
                'page_title'       => 'Leadership & Site Visit Allowances — Associate Portal',
                'page_description' => 'Claim branch office rent support and customer site visit travel/fuel reimbursements.',
                'user'             => $user,
                'rank'             => $rank,
                'office_eval'      => $officeEval,
                'site_visits'      => $allVisits,
                'claims'           => $claims,
                'current_page'     => 'allowances',
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('[Associate\AllowanceController] index error: ' . $e->getMessage());
            $this->setFlash('error', 'Error loading allowances: ' . $e->getMessage());
            return $this->redirect('/associate/dashboard');
        }
    }

    public function claimOffice()
    {
        $this->requireAuth();
        $this->verifyCsrfOrDie();
        $userId = (int)$_SESSION['user_id'];
        $notes = trim($_POST['notes'] ?? '');

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT mlm_rank FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $rank = (string)$stmt->fetchColumn();

            $res = $this->fundService->submitOfficeSupportClaim($userId, $rank, $notes);
            if (!empty($res['success'])) {
                $this->setFlash('success', $res['message']);
            } else {
                $this->setFlash('error', $res['message']);
            }
        } catch (\Throwable $e) {
            error_log('[Associate\AllowanceController] claimOffice error: ' . $e->getMessage());
            $this->setFlash('error', 'Exception: ' . $e->getMessage());
        }
        return $this->redirect('/associate/allowances');
    }

    public function claimVisit()
    {
        $this->requireAuth();
        $this->verifyCsrfOrDie();
        $userId = (int)$_SESSION['user_id'];
        $siteVisitId = (int)($_POST['site_visit_id'] ?? 0);
        $visitType = in_array($_POST['visit_type'] ?? '', ['local', 'outstation'], true) ? $_POST['visit_type'] : 'local';
        $notes = trim($_POST['notes'] ?? '');

        if ($siteVisitId <= 0) {
            $this->setFlash('error', 'Please select a valid site visit record.');
            return $this->redirect('/associate/allowances');
        }

        try {
            $res = $this->fundService->submitSiteVisitClaim($userId, $siteVisitId, $visitType, $notes);
            if (!empty($res['success'])) {
                $this->setFlash('success', $res['message']);
            } else {
                $this->setFlash('error', $res['message']);
            }
        } catch (\Throwable $e) {
            error_log('[Associate\AllowanceController] claimVisit error: ' . $e->getMessage());
            $this->setFlash('error', 'Exception: ' . $e->getMessage());
        }
        return $this->redirect('/associate/allowances');
    }
}
