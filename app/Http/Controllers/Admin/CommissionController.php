<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Commission Controller - Custom MVC Implementation
 * Handles administrative commission operations: calculation, approval, and payouts
 */
class CommissionController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Commission Dashboard
     */
    public function index()
    {
        try {
            $data = [
                'page_title' => 'Commission Management - APS Dream Home',
                'active_page' => 'commission',
                'commission_stats' => $this->getCommissionStats(),
                'pending_commissions' => $this->getPendingCommissions(),
                'recent_payouts' => $this->getRecentPayouts()
            ];

            return $this->render('admin/commission/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Commission Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load commission data');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Calculate Commissions
     */
    public function calculate()
    {
        try {
            $data = [
                'page_title' => 'Calculate Commissions - APS Dream Home',
                'active_page' => 'commission_calculate',
                'booking_list' => $this->getBookingsForCommission(),
                'calculation_rules' => $this->getCalculationRules()
            ];

            return $this->render('admin/commission/calculate', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Commission Calculate error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load commission calculation');
            return $this->redirect('admin/commission');
        }
    }

    /**
     * Process commission calculation
     */
    public function processCalculation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 400);
        }

        try {
            $bookingIds = $_POST['booking_ids'] ?? [];
            $calculationRule = $_POST['calculation_rule'] ?? 'standard';

            if (empty($bookingIds)) {
                return $this->jsonResponse(['success' => false, 'message' => 'No bookings selected'], 400);
            }

            $results = [];
            foreach ($bookingIds as $bookingId) {
                $result = $this->calculateCommissionForBooking((int)$bookingId, $calculationRule);
                $results[] = $result;
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Commission calculation completed',
                'results' => $results
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Process Commission Calculation error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Calculation failed'], 500);
        }
    }

    /**
     * Approve Commissions
     */
    public function approve()
    {
        try {
            $data = [
                'page_title' => 'Approve Commissions - APS Dream Home',
                'active_page' => 'commission_approve',
                'pending_approvals' => $this->getPendingApprovals()
            ];

            return $this->render('admin/commission/approve', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Commission Approve error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load commission approvals');
            return $this->redirect('admin/commission');
        }
    }

    /**
     * Process commission approval
     */
    public function processApproval()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 400);
        }

        try {
            $commissionIds = $_POST['commission_ids'] ?? [];
            $action = $_POST['action'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($commissionIds) || !in_array($action, ['approve', 'reject'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters'], 400);
            }

            $results = [];
            foreach ($commissionIds as $commissionId) {
                $result = $this->processCommissionApproval((int)$commissionId, $action, $notes);
                $results[] = $result;
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => "Commission {$action}al completed",
                'results' => $results
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Process Commission Approval error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Approval failed'], 500);
        }
    }

    /**
     * Payout Management
     */
    public function payout()
    {
        try {
            $data = [
                'page_title' => 'Commission Payouts - APS Dream Home',
                'active_page' => 'commission_payout',
                'approved_commissions' => $this->getApprovedCommissions(),
                'payout_history' => $this->getPayoutHistory()
            ];

            return $this->render('admin/commission/payout', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Commission Payout error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payout data');
            return $this->redirect('admin/commission');
        }
    }

    /**
     * Process payout
     */
    public function processPayout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 400);
        }

        try {
            $commissionIds = $_POST['commission_ids'] ?? [];
            $payoutMethod = $_POST['payout_method'] ?? 'bank_transfer';

            if (empty($commissionIds)) {
                return $this->jsonResponse(['success' => false, 'message' => 'No commissions selected'], 400);
            }

            $results = [];
            foreach ($commissionIds as $commissionId) {
                $result = $this->processCommissionPayout((int)$commissionId, $payoutMethod);
                $results[] = $result;
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Payout processed successfully',
                'results' => $results
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Process Payout error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Payout failed'], 500);
        }
    }

    /**
     * Commission Reports
     */
    public function reports()
    {
        try {
            $data = [
                'page_title' => 'Commission Reports - APS Dream Home',
                'active_page' => 'commission_reports',
                'report_data' => $this->getCommissionReportData()
            ];

            return $this->render('admin/commission/reports', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Commission Reports error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load commission reports');
            return $this->redirect('admin/commission');
        }
    }

    /**
     * Get commission statistics
     */
    private function getCommissionStats(): array
    {
        try {
            $stats = [];

            // Total commissions
            $sql = "SELECT 
                        COUNT(*) as total_commissions,
                        COALESCE(SUM(amount), 0) as total_amount,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count
                    FROM mlm_commission_ledger";
            $result = $this->db->fetchOne($sql);
            $stats = array_merge($stats, $result);

            // This month's commissions
            $sql = "SELECT 
                        COUNT(*) as this_month_count,
                        COALESCE(SUM(amount), 0) as this_month_amount
                    FROM mlm_commission_ledger
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['this_month_count'] = (int)($result['this_month_count'] ?? 0);
            $stats['this_month_amount'] = (float)($result['this_month_amount'] ?? 0);

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Commission Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending commissions
     */
    private function getPendingCommissions(): array
    {
        try {
            $sql = "SELECT mcl.*, u.name as associate_name, u.email as associate_email,
                           b.booking_number, p.title as property_title
                    FROM mlm_commission_ledger mcl
                    JOIN users u ON mcl.associate_id = u.id
                    LEFT JOIN bookings b ON mcl.source_booking_id = b.id
                    LEFT JOIN properties p ON b.property_id = p.id
                    WHERE mcl.status = 'pending'
                    ORDER BY mcl.created_at DESC
                    LIMIT 20";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Pending Commissions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent payouts
     */
    private function getRecentPayouts(): array
    {
        try {
            $sql = "SELECT mcl.*, u.name as associate_name, u.email as associate_email,
                           mcl.payout_method, mcl.payout_date
                    FROM mlm_commission_ledger mcl
                    JOIN users u ON mcl.associate_id = u.id
                    WHERE mcl.status = 'paid' AND mcl.payout_date IS NOT NULL
                    ORDER BY mcl.payout_date DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Recent Payouts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get bookings for commission calculation
     */
    private function getBookingsForCommission(): array
    {
        try {
            $sql = "SELECT b.*, u.name as associate_name, p.title as property_title
                    FROM bookings b
                    LEFT JOIN users u ON b.associate_id = u.id
                    LEFT JOIN properties p ON b.property_id = p.id
                    WHERE b.status = 'confirmed' 
                    AND b.id NOT IN (
                        SELECT DISTINCT source_booking_id 
                        FROM mlm_commission_ledger 
                        WHERE source_booking_id IS NOT NULL
                    )
                    ORDER BY b.created_at DESC
                    LIMIT 50";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Bookings For Commission error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get calculation rules
     */
    private function getCalculationRules(): array
    {
        try {
            $sql = "SELECT * FROM commission_calculation_rules WHERE is_active = 1 ORDER BY priority";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Calculation Rules error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate commission for booking
     */
    private function calculateCommissionForBooking(int $bookingId, string $rule): array
    {
        try {
            // Get booking details
            $sql = "SELECT b.*, u.id as associate_id, u.mlm_rank
                    FROM bookings b
                    LEFT JOIN users u ON b.associate_id = u.id
                    WHERE b.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            if (!$booking || !$booking['associate_id']) {
                return ['success' => false, 'message' => 'Invalid booking or no associate assigned'];
            }

            // Get commission rate based on rule and associate rank
            $commissionRate = $this->getCommissionRate($rule, $booking['mlm_rank']);
            $commissionAmount = $booking['total_amount'] * ($commissionRate / 100);

            // Insert commission record
            $sql = "INSERT INTO mlm_commission_ledger 
                    (associate_id, source_booking_id, commission_type, rate, amount, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $booking['associate_id'],
                $bookingId,
                $rule,
                $commissionRate,
                $commissionAmount
            ]);

            if ($result) {
                $commissionId = $this->db->lastInsertId();
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'commission_calculated', [
                    'commission_id' => $commissionId,
                    'booking_id' => $bookingId,
                    'amount' => $commissionAmount
                ]);

                return [
                    'success' => true,
                    'commission_id' => $commissionId,
                    'amount' => $commissionAmount,
                    'message' => 'Commission calculated successfully'
                ];
            }

            return ['success' => false, 'message' => 'Failed to save commission'];
        } catch (Exception $e) {
            $this->loggingService->error("Calculate Commission For Booking error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Calculation failed'];
        }
    }

    /**
     * Get commission rate
     */
    private function getCommissionRate(string $rule, string $rank): float
    {
        try {
            $sql = "SELECT rate_percentage 
                    FROM commission_calculation_rules 
                    WHERE rule_name = ? AND mlm_rank = ? AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$rule, $rank]);
            $result = $stmt->fetch();

            return (float)($result['rate_percentage'] ?? 5.0); // Default 5%
        } catch (Exception $e) {
            $this->loggingService->error("Get Commission Rate error: " . $e->getMessage());
            return 5.0;
        }
    }

    /**
     * Get pending approvals
     */
    private function getPendingApprovals(): array
    {
        try {
            $sql = "SELECT mcl.*, u.name as associate_name, u.email as associate_email,
                           b.booking_number, p.title as property_title
                    FROM mlm_commission_ledger mcl
                    JOIN users u ON mcl.associate_id = u.id
                    LEFT JOIN bookings b ON mcl.source_booking_id = b.id
                    LEFT JOIN properties p ON b.property_id = p.id
                    WHERE mcl.status = 'pending'
                    ORDER BY mcl.created_at ASC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Pending Approvals error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process commission approval
     */
    private function processCommissionApproval(int $commissionId, string $action, string $notes): array
    {
        try {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            
            $sql = "UPDATE mlm_commission_ledger 
                    SET status = ?, approval_notes = ?, approved_by = ?, approved_at = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$status, $notes, $_SESSION['user_id'] ?? 0, $commissionId]);

            if ($result) {
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'commission_' . $action, [
                    'commission_id' => $commissionId,
                    'notes' => $notes
                ]);

                return [
                    'success' => true,
                    'commission_id' => $commissionId,
                    'status' => $status,
                    'message' => "Commission {$action}d successfully"
                ];
            }

            return ['success' => false, 'message' => 'Failed to update commission'];
        } catch (Exception $e) {
            $this->loggingService->error("Process Commission Approval error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Approval failed'];
        }
    }

    /**
     * Get approved commissions
     */
    private function getApprovedCommissions(): array
    {
        try {
            $sql = "SELECT mcl.*, u.name as associate_name, u.email as associate_email,
                           u.bank_account, u.bank_name, u.ifsc_code
                    FROM mlm_commission_ledger mcl
                    JOIN users u ON mcl.associate_id = u.id
                    WHERE mcl.status = 'approved' AND (mcl.payout_date IS NULL OR mcl.payout_date = '')
                    ORDER BY mcl.approved_at ASC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Approved Commissions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payout history
     */
    private function getPayoutHistory(): array
    {
        try {
            $sql = "SELECT mcl.*, u.name as associate_name, u.email as associate_email
                    FROM mlm_commission_ledger mcl
                    JOIN users u ON mcl.associate_id = u.id
                    WHERE mcl.status = 'paid' AND mcl.payout_date IS NOT NULL
                    ORDER BY mcl.payout_date DESC
                    LIMIT 20";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Payout History error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process commission payout
     */
    private function processCommissionPayout(int $commissionId, string $payoutMethod): array
    {
        try {
            $this->db->beginTransaction();

            // Get commission details
            $sql = "SELECT * FROM mlm_commission_ledger WHERE id = ? AND status = 'approved'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$commissionId]);
            $commission = $stmt->fetch();

            if (!$commission) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Commission not found or not approved'];
            }

            // Update commission status
            $sql = "UPDATE mlm_commission_ledger 
                    SET status = 'paid', payout_method = ?, payout_date = NOW(), processed_by = ?
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$payoutMethod, $_SESSION['user_id'] ?? 0, $commissionId]);

            if ($result) {
                // Create payout record
                $sql = "INSERT INTO commission_payouts 
                        (commission_id, associate_id, amount, payout_method, processed_by, processed_at)
                        VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $commissionId,
                    $commission['associate_id'],
                    $commission['amount'],
                    $payoutMethod,
                    $_SESSION['user_id'] ?? 0
                ]);

                $this->db->commit();

                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'commission_paid', [
                    'commission_id' => $commissionId,
                    'amount' => $commission['amount'],
                    'payout_method' => $payoutMethod
                ]);

                return [
                    'success' => true,
                    'commission_id' => $commissionId,
                    'amount' => $commission['amount'],
                    'message' => 'Payout processed successfully'
                ];
            }

            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to process payout'];
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->loggingService->error("Process Commission Payout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payout failed'];
        }
    }

    /**
     * Get commission report data
     */
    private function getCommissionReportData(): array
    {
        try {
            $data = [];

            // Monthly commission trends
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                           COUNT(*) as count, 
                           COALESCE(SUM(amount), 0) as total
                    FROM mlm_commission_ledger
                    WHERE status = 'paid'
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month DESC
                    LIMIT 12";
            $data['monthly_trends'] = $this->db->fetchAll($sql) ?: [];

            // Top performers
            $sql = "SELECT u.name, u.email, 
                           COALESCE(SUM(mcl.amount), 0) as total_commission,
                           COUNT(mcl.id) as commission_count
                    FROM users u
                    LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                    WHERE u.role = 'associate'
                    GROUP BY u.id
                    ORDER BY total_commission DESC
                    LIMIT 10";
            $data['top_performers'] = $this->db->fetchAll($sql) ?: [];

            // Commission by type
            $sql = "SELECT commission_type, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
                    FROM mlm_commission_ledger
                    WHERE status = 'paid'
                    GROUP BY commission_type
                    ORDER BY total DESC";
            $data['by_type'] = $this->db->fetchAll($sql) ?: [];

            return $data;
        } catch (Exception $e) {
            $this->loggingService->error("Get Commission Report Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}