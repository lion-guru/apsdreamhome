<?php

/**
 * Admin Commission Controller
 * Handles administrative commission operations: calculation, approval, and payouts
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\HybridMLMCalculator;
use App\Models\AuditLog;
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
            'page_title' => ($this->mlSupport ? $this->mlSupport->translate('Commission Overview') : 'Commission Overview') . ' - ' . APP_NAME,
            'pendingCommissions' => $pendingCommissions
        ]);
    }

    /**
     * Calculate commissions for a period (AJAX)
     */
    public function calculate()
    {
        if ($this->request->method() !== 'POST') {
            return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('Invalid request method.') : 'Invalid request method.')], 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('Security validation failed. Please try again.') : 'Security validation failed.')], 400);
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
            $calculator = new HybridMLMCalculator();

            foreach ($associates as $associate) {
                // Pass associate_id, not user_id
                $calculator->calculateCommission(intval($associate['associate_id']), 0);
                $calculatedCount++;
            }

            // Log Activity
            $auditLog = new AuditLog();
            $auditLog->log($this->session->get('user_id', 0), 'Commission Calculation', 'commission', 0, 'Triggered calculation for ' . $calculatedCount . ' associates');

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            // Notify Admin
            try {
                $this->notificationService->sendEmail(
                    getenv('MAIL_ADMIN') ?: 'admin@apsdreamhome.com',
                    'Commission Calculation Completed',
                    "Commission calculation triggered for $calculatedCount associates by " . ($this->session->get('username', 'Admin')),
                    'commission_calc_report'
                );
            } catch (\Exception $e) {
                // Log error
                error_log("Failed to send commission notification: " . $e->getMessage());
            }

            return $this->json([
                'success' => true,
                'message' => ($this->mlSupport ? $this->mlSupport->translate('Commission calculation triggered for ') : 'Commission calculation triggered for ') . $calculatedCount . ($this->mlSupport ? $this->mlSupport->translate(' associates') : ' associates'),
                'count' => $calculatedCount
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('Calculation failed: ') : 'Calculation failed: ') . h($e->getMessage())], 500);
        }
    }

    /**
     * Approve pending commissions (AJAX)
     */
    public function approve()
    {
        if ($this->request->method() !== 'POST') {
            return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('Invalid request method.') : 'Invalid request method.')], 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('Security validation failed. Please try again.') : 'Security validation failed.')], 400);
        }

        $id = intval($this->request->get('id'));

        if (!$id) {
            return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('No commission ID provided') : 'No commission ID provided')], 400);
        }

        try {
            // Ensure we only approve pending commissions
            // Fix: update method requires WHERE clause as string
            $updated = $this->db->update(
                'mlm_commission_ledger',
                ['status' => 'approved'],
                'id = :id AND status = :status',
                ['id' => $id, 'status' => 'pending']
            );

            if ($updated) {
                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }

                // Log Activity
                $auditLog = new AuditLog();
                $auditLog->log($this->session->get('user_id', 0), 'Commission Approval', 'commission', $id, 'Approved commission ID: ' . $id);

                return $this->json([
                    'success' => true,
                    'message' => ($this->mlSupport ? $this->mlSupport->translate('Commission approved successfully') : 'Commission approved successfully')
                ]);
            } else {
                return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('Commission not found or already processed.') : 'Commission not found or already processed.')], 404);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => ($this->mlSupport ? $this->mlSupport->translate('Approval failed: ') : 'Approval failed: ') . h($e->getMessage())], 500);
        }
    }

    /**
     * Process payouts for approved commissions
     */
    public function processPayout()
    {
        if ($this->request->method() === 'POST') {
            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', ($this->mlSupport ? $this->mlSupport->translate('Security validation failed. Please try again.') : 'Security validation failed.'));
                return $this->back();
            }

            try {
                $payoutService = new \App\Services\PayoutService();
                $result = $payoutService->createBatch($this->request->all());

                if ($result['success']) {
                    // Invalidate dashboard cache
                    if (function_exists('getPerformanceManager')) {
                        getPerformanceManager()->clearCache('query_');
                    }

                    // Log Activity
                    $auditLog = new AuditLog();
                    $auditLog->log($this->session->get('user_id', 0), 'Payout Batch Created', 'payout_batch', $result['batch_id'] ?? 0, 'Batch ID: ' . ($result['batch_id'] ?? 'unknown'));

                    $this->setFlash('success', ($this->mlSupport ? $this->mlSupport->translate('Payout batch created successfully: ') : 'Payout batch created successfully: ') . h($result['batch_id']));
                } else {
                    $this->setFlash('error', ($this->mlSupport ? $this->mlSupport->translate('Payout failed: ') : 'Payout failed: ') . h($result['message']));
                }
            } catch (\Exception $e) {
                $this->setFlash('error', ($this->mlSupport ? $this->mlSupport->translate('System error: ') : 'System error: ') . h($e->getMessage()));
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
            'page_title' => ($this->mlSupport ? $this->mlSupport->translate('Process Payouts') : 'Process Payouts') . ' - ' . APP_NAME,
            'approvedCommissions' => $approvedCommissions
        ]);
    }
}
