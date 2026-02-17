<?php

/**
 * Admin Commission Controller
 * Handles administrative commission operations: calculation, approval, and payouts
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\NotificationService;

class CommissionController extends AdminController
{
    protected $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('csrf', ['only' => ['calculate', 'approve', 'processPayout']]);
        $this->notificationService = new NotificationService();
    }

    /**
     * Display commission overview
     */
    public function index()
    {
        // Fetch pending commissions from mlm_commission_ledger
        $query = "SELECT mcl.*, u.name as associate_name
                  FROM mlm_commission_ledger mcl
                  LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                  WHERE mcl.status = 'pending'
                  ORDER BY mcl.created_at DESC";

        $pendingCommissions = $this->db->fetchAll($query);

        return $this->render('admin/commissions/index', [
            'page_title' => $this->mlSupport->translate('Commission Overview') . ' - ' . $this->getConfig('app_name'),
            'pendingCommissions' => $pendingCommissions
        ]);
    }

    /**
     * Calculate commissions for a period (AJAX)
     */
    public function calculate()
    {
        if (!$this->validateCsrfToken()) {
            return $this->jsonError($this->mlSupport->translate('Security validation failed. Please try again.'));
        }

        try {
            // Fetch all active associates (using associate_id) to trigger calculation
            // Join with users to check active status if needed, or check associates status
            $associatesQuery = "SELECT a.associate_id 
                              FROM associates a 
                              JOIN users u ON a.user_id = u.id 
                              WHERE a.status = 'active' AND u.status = 'active'";
            $associates = $this->db->fetchAll($associatesQuery);

            $calculatedCount = 0;
            $calculator = $this->model('HybridMLMCalculator');

            foreach ($associates as $associate) {
                // Pass associate_id, not user_id
                $calculator->calculateCommission(intval($associate['associate_id']), 0);
                $calculatedCount++;
            }

            $this->logActivity('Commission Calculation', 'Triggered calculation for ' . $calculatedCount . ' associates');

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            // Notify Admin
            try {
                $this->notificationService->sendEmail(
                    getenv('MAIL_ADMIN') ?: 'admin@apsdreamhome.com',
                    'Commission Calculation Completed',
                    "Commission calculation triggered for $calculatedCount associates by " . ($_SESSION['username'] ?? 'Admin'),
                    'commission_calc_report'
                );
            } catch (\Exception $e) {
                // Log error
                error_log("Failed to send commission notification: " . $e->getMessage());
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => $this->mlSupport->translate('Commission calculation triggered for ') . $calculatedCount . $this->mlSupport->translate(' associates'),
                'count' => $calculatedCount
            ]);
        } catch (\Exception $e) {
            return $this->jsonError($this->mlSupport->translate('Calculation failed: ') . h($e->getMessage()));
        }
    }

    /**
     * Approve pending commissions (AJAX)
     */
    public function approve()
    {
        if (!$this->validateCsrfToken()) {
            return $this->jsonError($this->mlSupport->translate('Security validation failed. Please try again.'));
        }

        $request = $this->request();
        $id = intval($request->get('id'));

        if (!$id) {
            return $this->jsonError($this->mlSupport->translate('No commission ID provided'));
        }

        try {
            // Ensure we only approve pending commissions
            $updated = $this->db->update('mlm_commission_ledger', ['status' => 'approved'], ['id' => $id, 'status' => 'pending']);

            if ($updated) {
                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->logActivity('Commission Approval', 'Approved commission ID: ' . $id);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => $this->mlSupport->translate('Commission approved successfully')
                ]);
            } else {
                return $this->jsonError($this->mlSupport->translate('Commission not found or already processed.'));
            }
        } catch (\Exception $e) {
            return $this->jsonError($this->mlSupport->translate('Approval failed: ') . h($e->getMessage()));
        }
    }

    /**
     * Process payouts for approved commissions
     */
    public function processPayout()
    {
        $request = $this->request();
        if ($request->method() === 'POST') {
            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
                return $this->back();
            }

            try {
                $payoutService = new \App\Services\PayoutService();
                $result = $payoutService->createBatch($request->all());

                if ($result['success']) {
                    // Invalidate dashboard cache
                    if (function_exists('getPerformanceManager')) {
                        getPerformanceManager()->clearCache('query_');
                    }
                    $this->logActivity('Payout Batch Created', 'Batch ID: ' . ($result['batch_id'] ?? 'unknown'));
                    $this->setFlash('success', $this->mlSupport->translate('Payout batch created successfully: ') . h($result['batch_id']));
                } else {
                    $this->setFlash('error', $this->mlSupport->translate('Payout failed: ') . h($result['message']));
                }
            } catch (\Exception $e) {
                $this->setFlash('error', $this->mlSupport->translate('System error: ') . h($e->getMessage()));
            }
            return $this->redirect('admin/commissions/payout');
        }

        // Fetch approved but unpaid commissions for the payout view
        $query = "SELECT mcl.*, u.name as associate_name
                  FROM mlm_commission_ledger mcl
                  LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                  WHERE mcl.status = 'approved'
                  ORDER BY mcl.created_at DESC";

        $approvedCommissions = $this->db->fetchAll($query);

        return $this->render('admin/commissions/payout', [
            'page_title' => $this->mlSupport->translate('Process Payouts') . ' - ' . $this->getConfig('app_name'),
            'approvedCommissions' => $approvedCommissions
        ]);
    }
}
