<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Payout Controller - Custom MVC Implementation
 * Admin UI endpoints for MLM payout automation
 */
class PayoutController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'processPayout', 'batchProcess']]);
    }

    /**
     * Display payout management dashboard
     */
    public function index()
    {
        try {
            $data = [
                'page_title' => 'MLM Payout Management - APS Dream Home',
                'active_page' => 'payouts',
                'payout_stats' => $this->getPayoutStats(),
                'pending_payouts' => $this->getPendingPayouts(),
                'recent_payouts' => $this->getRecentPayouts()
            ];

            return $this->render('admin/payouts/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Payout Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payout data');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Display list of payouts
     */
    public function list()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $method = $_GET['method'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT p.*, 
                           u.name as associate_name,
                           u.email as associate_email,
                           u.bank_account,
                           u.bank_name,
                           u.ifsc_code
                    FROM commission_payouts p
                    JOIN users u ON p.associate_id = u.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR p.transaction_id LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }

            if (!empty($method)) {
                $sql .= " AND p.payout_method = ?";
                $params[] = $method;
            }

            $sql .= " ORDER BY p.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT p.*, u.name as associate_name, u.email as associate_email, u.bank_account, u.bank_name, u.ifsc_code", "SELECT COUNT(DISTINCT p.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $payouts = $stmt->fetchAll();

            $data = [
                'page_title' => 'Payout List - APS Dream Home',
                'active_page' => 'payouts',
                'payouts' => $payouts,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'method' => $method
                ]
            ];

            return $this->render('admin/payouts/list', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Payout List error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payout list');
            return $this->redirect('admin/payouts');
        }
    }

    /**
     * Display the specified payout
     */
    public function show($id)
    {
        try {
            $payoutId = intval($id);
            if ($payoutId <= 0) {
                $this->setFlash('error', 'Invalid payout ID');
                return $this->redirect('admin/payouts');
            }

            // Get payout details
            $sql = "SELECT p.*, 
                           u.name as associate_name,
                           u.email as associate_email,
                           u.phone as associate_phone,
                           u.bank_account,
                           u.bank_name,
                           u.ifsc_code,
                           u.address as associate_address
                    FROM commission_payouts p
                    JOIN users u ON p.associate_id = u.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$payoutId]);
            $payout = $stmt->fetch();

            if (!$payout) {
                $this->setFlash('error', 'Payout not found');
                return $this->redirect('admin/payouts');
            }

            // Get commission details for this payout
            $sql = "SELECT mcl.*, b.booking_number, c.name as customer_name
                    FROM mlm_commission_ledger mcl
                    LEFT JOIN commission_payout_commissions cpc ON mcl.id = cpc.commission_id
                    LEFT JOIN bookings b ON mcl.source_booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    WHERE cpc.payout_id = ?
                    ORDER BY mcl.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$payoutId]);
            $commissions = $stmt->fetchAll();

            $data = [
                'page_title' => 'Payout Details - APS Dream Home',
                'active_page' => 'payouts',
                'payout' => $payout,
                'commissions' => $commissions
            ];

            return $this->render('admin/payouts/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Payout Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payout details');
            return $this->redirect('admin/payouts');
        }
    }

    /**
     * Process payout
     */
    public function processPayout($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $payoutId = intval($id);
            $method = $_POST['payout_method'] ?? '';
            $transactionId = $_POST['transaction_id'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if ($payoutId <= 0 || empty($method)) {
                return $this->jsonError('Invalid payout details', 400);
            }

            $this->db->beginTransaction();

            try {
                // Get payout details
                $sql = "SELECT * FROM commission_payouts WHERE id = ? AND status = 'pending'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$payoutId]);
                $payout = $stmt->fetch();

                if (!$payout) {
                    $this->db->rollBack();
                    return $this->jsonError('Payout not found or already processed', 404);
                }

                // Update payout status
                $sql = "UPDATE commission_payouts 
                        SET status = 'processed', payout_method = ?, transaction_id = ?, 
                            processed_date = NOW(), notes = ?, processed_by = ?
                        WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$method, $transactionId, $notes, $_SESSION['user_id'] ?? 0, $payoutId]);

                // Update commission ledger status
                $sql = "UPDATE mlm_commission_ledger mcl
                        JOIN commission_payout_commissions cpc ON mcl.id = cpc.commission_id
                        SET mcl.status = 'paid', mcl.payout_date = NOW()
                        WHERE cpc.payout_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$payoutId]);

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'payout_processed', [
                    'payout_id' => $payoutId,
                    'amount' => $payout['amount'],
                    'method' => $method
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Payout processed successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Process Payout error: " . $e->getMessage());
            return $this->jsonError('Failed to process payout', 500);
        }
    }

    /**
     * Batch process payouts
     */
    public function batchProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $payoutIds = $_POST['payout_ids'] ?? [];
            $method = $_POST['payout_method'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($payoutIds) || empty($method)) {
                return $this->jsonError('Invalid parameters', 400);
            }

            $results = [];
            $processed = 0;
            $failed = 0;

            foreach ($payoutIds as $payoutId) {
                try {
                    $result = $this->processSinglePayout((int)$payoutId, $method, $notes);
                    if ($result['success']) {
                        $processed++;
                    } else {
                        $failed++;
                    }
                    $results[] = $result;
                } catch (Exception $e) {
                    $failed++;
                    $results[] = ['payout_id' => $payoutId, 'success' => false, 'message' => $e->getMessage()];
                }
            }

            // Log batch activity
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'batch_payout_processed', [
                'processed' => $processed,
                'failed' => $failed,
                'method' => $method
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => "Batch processing completed: {$processed} processed, {$failed} failed",
                'results' => $results
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Batch Process Payout error: " . $e->getMessage());
            return $this->jsonError('Failed to process batch payout', 500);
        }
    }

    /**
     * Create payout from approved commissions
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $associateIds = $_POST['associate_ids'] ?? [];
            $includePending = (bool)($_POST['include_pending'] ?? false);

            if (empty($associateIds)) {
                return $this->jsonError('No associates selected', 400);
            }

            $results = [];
            $created = 0;
            $failed = 0;

            foreach ($associateIds as $associateId) {
                try {
                    $result = $this->createPayoutForAssociate((int)$associateId, $includePending);
                    if ($result['success']) {
                        $created++;
                    } else {
                        $failed++;
                    }
                    $results[] = $result;
                } catch (Exception $e) {
                    $failed++;
                    $results[] = ['associate_id' => $associateId, 'success' => false, 'message' => $e->getMessage()];
                }
            }

            // Log activity
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'payouts_created', [
                'created' => $created,
                'failed' => $failed
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => "Payout creation completed: {$created} created, {$failed} failed",
                'results' => $results
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Create Payout error: " . $e->getMessage());
            return $this->jsonError('Failed to create payouts', 500);
        }
    }

    /**
     * Display payout analytics
     */
    public function analytics()
    {
        try {
            $data = [
                'page_title' => 'Payout Analytics - APS Dream Home',
                'active_page' => 'payouts',
                'analytics_data' => $this->getPayoutAnalytics()
            ];

            return $this->render('admin/payouts/analytics', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Payout Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payout analytics');
            return $this->redirect('admin/payouts');
        }
    }

    /**
     * Get payout statistics
     */
    private function getPayoutStats(): array
    {
        try {
            $stats = [];

            // Total payouts
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as total_amount
                    FROM commission_payouts";
            $result = $this->db->fetchOne($sql);
            $stats['total_payouts'] = (int)($result['total'] ?? 0);
            $stats['total_amount'] = (float)($result['total_amount'] ?? 0);

            // Pending payouts
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as total_amount
                    FROM commission_payouts WHERE status = 'pending'";
            $result = $this->db->fetchOne($sql);
            $stats['pending_payouts'] = (int)($result['total'] ?? 0);
            $stats['pending_amount'] = (float)($result['total_amount'] ?? 0);

            // Processed payouts
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as total_amount
                    FROM commission_payouts WHERE status = 'processed'";
            $result = $this->db->fetchOne($sql);
            $stats['processed_payouts'] = (int)($result['total'] ?? 0);
            $stats['processed_amount'] = (float)($result['total_amount'] ?? 0);

            // This month's payouts
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as total_amount
                    FROM commission_payouts 
                    WHERE MONTH(processed_date) = MONTH(CURRENT_DATE) 
                    AND YEAR(processed_date) = YEAR(CURRENT_DATE)
                    AND status = 'processed'";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_payouts'] = (int)($result['total'] ?? 0);
            $stats['monthly_amount'] = (float)($result['total_amount'] ?? 0);

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Payout Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending payouts
     */
    private function getPendingPayouts(): array
    {
        try {
            $sql = "SELECT p.*, u.name as associate_name, u.email as associate_email
                    FROM commission_payouts p
                    JOIN users u ON p.associate_id = u.id
                    WHERE p.status = 'pending'
                    ORDER BY p.created_at ASC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Pending Payouts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent payouts
     */
    private function getRecentPayouts(): array
    {
        try {
            $sql = "SELECT p.*, u.name as associate_name, u.email as associate_email
                    FROM commission_payouts p
                    JOIN users u ON p.associate_id = u.id
                    WHERE p.status = 'processed'
                    ORDER BY p.processed_date DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Recent Payouts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process single payout
     */
    private function processSinglePayout(int $payoutId, string $method, string $notes): array
    {
        try {
            $sql = "SELECT * FROM commission_payouts WHERE id = ? AND status = 'pending'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$payoutId]);
            $payout = $stmt->fetch();

            if (!$payout) {
                return ['payout_id' => $payoutId, 'success' => false, 'message' => 'Payout not found or already processed'];
            }

            $sql = "UPDATE commission_payouts 
                    SET status = 'processed', payout_method = ?, processed_date = NOW(), notes = ?, processed_by = ?
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$method, $notes, $_SESSION['user_id'] ?? 0, $payoutId]);

            if ($result) {
                return ['payout_id' => $payoutId, 'success' => true, 'message' => 'Payout processed successfully'];
            }

            return ['payout_id' => $payoutId, 'success' => false, 'message' => 'Failed to process payout'];
        } catch (Exception $e) {
            return ['payout_id' => $payoutId, 'success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create payout for associate
     */
    private function createPayoutForAssociate(int $associateId, bool $includePending): array
    {
        try {
            // Get approved commissions for associate
            $sql = "SELECT id, amount FROM mlm_commission_ledger 
                    WHERE associate_id = ? AND status = 'approved'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$associateId]);
            $commissions = $stmt->fetchAll();

            if ($includePending) {
                // Also include pending commissions
                $sql = "SELECT id, amount FROM mlm_commission_ledger 
                        WHERE associate_id = ? AND status = 'pending'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$associateId]);
                $pendingCommissions = $stmt->fetchAll();
                $commissions = array_merge($commissions, $pendingCommissions);
            }

            if (empty($commissions)) {
                return ['associate_id' => $associateId, 'success' => false, 'message' => 'No commissions found'];
            }

            $totalAmount = array_sum(array_column($commissions, 'amount'));

            $this->db->beginTransaction();

            try {
                // Create payout record
                $sql = "INSERT INTO commission_payouts 
                        (associate_id, amount, status, created_at)
                        VALUES (?, ?, 'pending', NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$associateId, $totalAmount]);
                $payoutId = $this->db->lastInsertId();

                // Link commissions to payout
                foreach ($commissions as $commission) {
                    $sql = "INSERT INTO commission_payout_commissions (payout_id, commission_id)
                            VALUES (?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$payoutId, $commission['id']]);
                }

                $this->db->commit();

                return ['associate_id' => $associateId, 'success' => true, 'payout_id' => $payoutId, 'amount' => $totalAmount];
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return ['associate_id' => $associateId, 'success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get payout analytics
     */
    private function getPayoutAnalytics(): array
    {
        try {
            $analytics = [];

            // Payout trends (last 30 days)
            $sql = "SELECT DATE(processed_date) as date, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
                    FROM commission_payouts
                    WHERE status = 'processed' AND processed_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(processed_date)
                    ORDER BY date DESC";
            $analytics['trends'] = $this->db->fetchAll($sql) ?: [];

            // Payout methods distribution
            $sql = "SELECT payout_method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
                    FROM commission_payouts
                    WHERE status = 'processed' AND processed_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY payout_method
                    ORDER BY total DESC";
            $analytics['methods'] = $this->db->fetchAll($sql) ?: [];

            // Top earning associates
            $sql = "SELECT u.name, u.email, COUNT(p.id) as payout_count, COALESCE(SUM(p.amount), 0) as total_earned
                    FROM commission_payouts p
                    JOIN users u ON p.associate_id = u.id
                    WHERE p.status = 'processed'
                    GROUP BY u.id
                    ORDER BY total_earned DESC
                    LIMIT 10";
            $analytics['top_associates'] = $this->db->fetchAll($sql) ?: [];

            return $analytics;
        } catch (Exception $e) {
            $this->loggingService->error("Get Payout Analytics error: " . $e->getMessage());
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

    /**
     * JSON error helper
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}