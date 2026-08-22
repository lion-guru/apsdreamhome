<?php

namespace App\Http\Controllers\Api;

require_once __DIR__ . '/../BaseController.php';
use \App\Traits\TenantAwareTrait;

/**
 * AdminMobileController — JSON API endpoints for Flutter admin pages.
 * Returns JSON data for: bookings, commissions, plots, users.
 */
class AdminMobileController extends \App\Http\Controllers\BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mobile admin API uses Bearer token auth (ApiAuthMiddleware) — stateless,
     * no session-based CSRF. Skip CSRF for all POST endpoints.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * GET /api/v2/mobile/admin/bookings
     * Returns all plot bookings for the admin approvals page.
     */
    public function bookings()
    {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM bookings")['cnt'] ?? 0;

            $bookings = $this->db->fetchAll(
                "SELECT b.id, b.booking_number, b.status, b.total_amount, b.booking_amount as token_amount,
                        b.created_at, b.updated_at,
                        COALESCE(u.name, 'N/A') as customer_name,
                        p.plot_number,
                        c.name as colony_name
                 FROM bookings b
                 LEFT JOIN users u ON b.user_id = u.id
                 LEFT JOIN plots p ON b.plot_id = p.id
                 LEFT JOIN colonies c ON b.colony_id = c.id
                 ORDER BY b.created_at DESC
                 LIMIT {$limit} OFFSET {$offset}"
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $bookings,
                'pagination' => ['page' => $page, 'limit' => $limit, 'total' => (int)$total, 'pages' => (int)ceil($total / $limit)]
            ]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::bookings error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * POST /api/v2/mobile/admin/bookings/{id}/status
     * Update booking status (approve/reject).
     */
    public function updateBookingStatus($id)
    {
        try {
            $status = $_POST['status'] ?? '';

            // Map mobile/approval status values to the real `bookings` enum
            // (pending, confirmed, cancelled, completed).
            $statusMap = [
                'rejected'          => 'cancelled',
                'cancelled'         => 'cancelled',
                'approve'           => 'confirmed',
                'approved'          => 'confirmed',
                'confirmed'         => 'confirmed',
                'emi_active'        => 'confirmed',
                'token_paid'        => 'confirmed',
                'agreement_signed'  => 'confirmed',
                'partially_paid'    => 'confirmed',
                'fully_paid'        => 'completed',
                'registration_done' => 'completed',
                'completed'         => 'completed',
                'pending'           => 'pending',
            ];

            if (!isset($statusMap[$status])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Invalid status'], 400);
            }

            $dbStatus = $statusMap[$status];
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?", [$dbStatus, $id, $tid]);

            return $this->jsonResponse(['success' => true, 'message' => 'Booking status updated']);
        } catch (\Exception $e) {
            error_log('AdminMobileController::updateBookingStatus error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/commissions
     * Returns commission ledger entries for the admin approvals page.
     */
    public function commissions()
    {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM mlm_commission_ledger")['cnt'] ?? 0;

            $commissions = $this->db->fetchAll(
                "SELECT l.id, l.commission_type, l.amount, l.commission_percentage,
                        l.status, l.notes, l.created_at,
                        COALESCE(u.name, 'N/A') as agent_name,
                        l.source_user_name,
                        l.rank_at_time, l.level
                 FROM mlm_commission_ledger l
                 LEFT JOIN users u ON l.beneficiary_user_id = u.id
                 ORDER BY l.created_at DESC
                 LIMIT {$limit} OFFSET {$offset}"
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $commissions,
                'pagination' => ['page' => $page, 'limit' => $limit, 'total' => (int)$total, 'pages' => (int)ceil($total / $limit)]
            ]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::commissions error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * POST /api/v2/mobile/admin/commissions/{id}/action
     * Approve or reject a commission entry.
     */
    public function commissionAction($id)
    {
        try {
            $action = $_POST['action'] ?? '';
            if (!in_array($action, ['approve', 'reject'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
            }

            $newStatus = $action === 'approve' ? 'approved' : 'cancelled';
            $this->db->execute(
                "UPDATE mlm_commission_ledger SET status = ? WHERE id = ?",
                [$newStatus, $id]
            );

            return $this->jsonResponse(['success' => true, 'message' => "Commission $action'd"]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::commissionAction error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/plots
     * Returns all plots for the admin plot management page.
     */
    public function plots()
    {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM plots")['cnt'] ?? 0;

            $plots = $this->db->fetchAll(
                "SELECT p.id, p.plot_number, p.status, p.area_sqft, p.total_price,
                        p.width_ft, p.length_ft, p.block,
                        c.name as colony_name
                 FROM plots p
                 LEFT JOIN colonies c ON p.colony_id = c.id
                 ORDER BY p.created_at DESC
                 LIMIT {$limit} OFFSET {$offset}"
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $plots,
                'pagination' => ['page' => $page, 'limit' => $limit, 'total' => (int)$total, 'pages' => (int)ceil($total / $limit)]
            ]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::plots error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/users
     * Returns all users for the admin user management page.
     */
    public function users()
    {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role IS NOT NULL AND role != ''")['cnt'] ?? 0;

            $users = $this->db->fetchAll(
                "SELECT id, name, email, phone, role, status, created_at
                 FROM users
                 WHERE role IS NOT NULL AND role != ''
                 ORDER BY created_at DESC
                 LIMIT {$limit} OFFSET {$offset}"
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $users,
                'pagination' => ['page' => $page, 'limit' => $limit, 'total' => (int)$total, 'pages' => (int)ceil($total / $limit)]
            ]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::users error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/reports
     * Returns summary stats for the admin reports page.
     */
    public function reports()
    {
        try {
            $reports = [];

            // Sales Report — total revenue from bookings
            try {
                $sales = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(total_amount), 0) as total_sales,
                            COUNT(*) as total_bookings,
                            SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) as this_month
                     FROM bookings WHERE status != 'cancelled'"
                );
                $prevMonthSales = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(total_amount), 0) as prev_sales
                     FROM bookings
                     WHERE status != 'cancelled'
                       AND created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
                       AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')"
                );
                $salesChange = ($prevMonthSales['prev_sales'] ?? 0) > 0
                    ? round((($sales['total_sales'] - $prevMonthSales['prev_sales']) / $prevMonthSales['prev_sales']) * 100)
                    : 0;
                $reports['sales'] = [
                    'value' => '₹' . number_format($sales['total_sales'] / 100000, 1) . ' Cr',
                    'change' => ($salesChange >= 0 ? '+' : '') . $salesChange . '% vs last month',
                    'positive' => $salesChange >= 0,
                ];
            } catch (\Exception $e) {
                $reports['sales'] = ['value' => '₹0', 'change' => 'N/A', 'positive' => true];
            }

            // Booking Report — total bookings count
            try {
                $bookings = $this->db->fetchOne(
                    "SELECT COUNT(*) as total,
                            SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) as this_month
                     FROM bookings WHERE status != 'cancelled'"
                );
                $reports['bookings'] = [
                    'value' => (string)($bookings['total'] ?? 0),
                    'change' => ($bookings['this_month'] ?? 0) . ' this month',
                    'positive' => true,
                ];
            } catch (\Exception $e) {
                $reports['bookings'] = ['value' => '0', 'change' => 'N/A', 'positive' => true];
            }

            // Collection Report — payments collected
            try {
                $collections = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(amount), 0) as collected
                     FROM payment_transactions WHERE payment_status = 'completed'"
                );
                $totalDue = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(amount), 0) as total_due
                     FROM booking_payment_schedules WHERE status IN ('pending', 'overdue')"
                );
                $collectionRate = ($totalDue['total_due'] ?? 0) > 0
                    ? round(($collections['collected'] / $totalDue['total_due']) * 100)
                    : 0;
                $reports['collections'] = [
                    'value' => '₹' . number_format($collections['collected'] / 100000, 1) . ' Cr',
                    'change' => $collectionRate . '% collection rate',
                    'positive' => $collectionRate >= 80,
                ];
            } catch (\Exception $e) {
                $reports['collections'] = ['value' => '₹0', 'change' => 'N/A', 'positive' => true];
            }

            // Agent Performance — active agents
            try {
                $agents = $this->db->fetchOne(
                    "SELECT COUNT(DISTINCT u.id) as total,
                            SUM(CASE WHEN u.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) as new_this_month
                     FROM users u
                     WHERE u.role = 'agent' AND u.status = 'active'"
                );
                $reports['agents'] = [
                    'value' => (string)($agents['total'] ?? 0),
                    'change' => ($agents['new_this_month'] ?? 0) . ' new this month',
                    'positive' => true,
                ];
            } catch (\Exception $e) {
                $reports['agents'] = ['value' => '0', 'change' => 'N/A', 'positive' => true];
            }

            // Colony Progress — active colonies
            try {
                $colonies = $this->db->fetchOne(
                    "SELECT COUNT(*) as total,
                            SUM(CASE WHEN status = 'launching' THEN 1 ELSE 0 END) as launching
                     FROM colonies WHERE status IN ('active', 'launching', 'development')"
                );
                $reports['colonies'] = [
                    'value' => (string)($colonies['total'] ?? 0),
                    'change' => ($colonies['launching'] ?? 0) . ' launching soon',
                    'positive' => true,
                ];
            } catch (\Exception $e) {
                $reports['colonies'] = ['value' => '0', 'change' => 'N/A', 'positive' => true];
            }

            // EMI Status — overdue payments
            try {
                $emi = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(installment_amount), 0) as pending_amount,
                            SUM(CASE WHEN due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_count
                     FROM booking_payment_schedules WHERE status IN ('pending', 'overdue')"
                );
                $reports['emi'] = [
                    'value' => '₹' . number_format(($emi['pending_amount'] ?? 0) / 100000, 0) . 'L',
                    'change' => ($emi['overdue_count'] ?? 0) . ' overdue',
                    'positive' => ($emi['overdue_count'] ?? 0) == 0,
                ];
            } catch (\Exception $e) {
                $reports['emi'] = ['value' => '₹0', 'change' => 'N/A', 'positive' => true];
            }

            return $this->jsonResponse(['success' => true, 'data' => $reports]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::reports error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/dashboard-stats
     */
    public function dashboardStats()
    {
        try {
            $revenue = $this->db->fetchOne("SELECT COALESCE(SUM(total_amount),0) as total FROM bookings WHERE status!='cancelled'");
            $bookings = $this->db->fetchOne("SELECT COUNT(*) as total FROM bookings WHERE status!='cancelled'");
            $tid = (int)$this->tenantId();
            $tidFilter = $tid > 1 ? ' AND tenant_id = ?' : '';
            $tidParam = $tid > 1 ? [$tid] : [];
            $users = $this->db->fetchOne("SELECT COUNT(*) as total FROM users WHERE status='active'{$tidFilter}", $tidParam);
            $leads = $this->db->fetchOne("SELECT COUNT(*) as total FROM leads");
            return $this->jsonResponse(['success'=>true,'data'=>[
                'total_revenue' => (float)($revenue['total']??0),
                'total_bookings' => (int)($bookings['total']??0),
                'total_users' => (int)($users['total']??0),
                'total_leads' => (int)($leads['total']??0),
            ]]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::dashboardStats error: ' . $e->getMessage());
            return $this->jsonResponse(['success'=>false,'error'=>'Internal server error'],500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/sales-trend
     */
    public function salesTrend()
    {
        try {
            $data = $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue
                 FROM bookings WHERE status!='cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month ASC"
            );
            return $this->jsonResponse(['success'=>true,'data'=>$data?:[]]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::salesTrend error: ' . $e->getMessage());
            return $this->jsonResponse(['success'=>false,'data'=>[],'error'=>'Internal server error'],500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/top-associates
     */
    public function topAssociates()
    {
        try {
            $data = $this->db->fetchAll(
                "SELECT u.id, u.name, u.email, u.phone, COUNT(b.id) as bookings, COALESCE(SUM(b.total_amount),0) as revenue
                 FROM users u INNER JOIN bookings b ON u.id = b.user_id
                 WHERE b.status!='cancelled' GROUP BY u.id ORDER BY revenue DESC LIMIT 10"
            );
            return $this->jsonResponse(['success'=>true,'data'=>$data?:[]]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::topAssociates error: ' . $e->getMessage());
            return $this->jsonResponse(['success'=>false,'data'=>[],'error'=>'Internal server error'],500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/colony-performance
     */
    public function colonyPerformance()
    {
        try {
            $data = $this->db->fetchAll(
                "SELECT c.id, c.name, COUNT(p.id) as total_plots,
                        SUM(CASE WHEN p.status='booked' THEN 1 ELSE 0 END) as booked_plots,
                        SUM(CASE WHEN p.status='available' THEN 1 ELSE 0 END) as available_plots
                 FROM colonies c LEFT JOIN plots p ON c.id = p.colony_id
                 GROUP BY c.id ORDER BY c.name ASC"
            );
            return $this->jsonResponse(['success'=>true,'data'=>$data?:[]]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::colonyPerformance error: ' . $e->getMessage());
            return $this->jsonResponse(['success'=>false,'data'=>[],'error'=>'Internal server error'],500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/emi-collection
     * Returns today's dues, stats, history, and earnings for field agents.
     */
    public function emiCollection()
    {
        try {
            $userId = $GLOBALS['api_user_id'] ?? $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            if (!$userId) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }
            $today = date('Y-m-d');

            // Today's dues (individual items with customer info)
            $todayDues = $this->db->fetchAll(
                "SELECT bps.id, bps.booking_id, bps.installment_no, bps.due_date, bps.amount as emi_amount,
                        bps.status, bps.paid_amount, bps.accrued_penalty, bps.late_fee,
                        bps.remarks, bps.opening_balance, bps.closing_balance,
                        u.id as customer_id, u.name as customer_name, u.phone,
                        p.plot_number, c.name as colony_name,
                        DATEDIFF(CURDATE(), bps.due_date) as days_overdue
                 FROM booking_payment_schedules bps
                 JOIN bookings b ON bps.booking_id = b.id
                 LEFT JOIN users u ON b.user_id = u.id
                 LEFT JOIN plots p ON b.plot_id = p.id
                 LEFT JOIN colonies c ON b.colony_id = c.id
                 WHERE bps.status IN ('pending','overdue')
                 ORDER BY
                    CASE WHEN bps.due_date < CURDATE() THEN 0 ELSE 1 END,
                    bps.due_date ASC
                 LIMIT 50"
            );

            // Today's stats
            $todayStats = $this->db->fetchOne(
                "SELECT
                    COUNT(*) as total_due,
                    COALESCE(SUM(CASE WHEN due_date < CURDATE() THEN 1 ELSE 0 END), 0) as overdue_count,
                    COALESCE(SUM(CASE WHEN due_date = CURDATE() THEN 1 ELSE 0 END), 0) as due_today,
                    COALESCE(SUM(amount), 0) as total_amount_due
                 FROM booking_payment_schedules
                 WHERE status IN ('pending','overdue')"
            );

            // Today's collections (paid today)
            $todayCollections = $this->db->fetchAll(
                "SELECT bps.id, bps.booking_id, bps.amount, bps.paid_amount,
                        bps.paid_date, bps.late_fee, bps.remarks,
                        u.name as customer_name, p.plot_number, c.name as colony_name
                 FROM booking_payment_schedules bps
                 JOIN bookings b ON bps.booking_id = b.id
                 LEFT JOIN users u ON b.user_id = u.id
                 LEFT JOIN plots p ON b.plot_id = p.id
                 LEFT JOIN colonies c ON b.colony_id = c.id
                 WHERE bps.paid_date = CURDATE()
                 ORDER BY bps.paid_date DESC
                 LIMIT 20"
            );

            // Collection history (last 30 days)
            $history = $this->db->fetchAll(
                "SELECT DATE(paid_date) as date, COUNT(*) as count,
                        COALESCE(SUM(paid_amount), 0) as collected
                 FROM booking_payment_schedules
                 WHERE paid_date IS NOT NULL AND paid_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(paid_date)
                 ORDER BY date DESC"
            );

            // Earnings summary (current month)
            $earnings = $this->db->fetchOne(
                "SELECT
                    COALESCE(SUM(CASE WHEN status='paid' THEN amount ELSE 0 END), 0) as total_collected,
                    COUNT(CASE WHEN status='paid' THEN 1 END) as total_paid_count,
                    COALESCE(SUM(late_fee), 0) as total_late_fees
                 FROM booking_payment_schedules
                 WHERE MONTH(due_date) = MONTH(CURDATE()) AND YEAR(due_date) = YEAR(CURDATE())"
            );

            return $this->jsonResponse(['success' => true, 'data' => [
                'today_dues' => $todayDues ?: [],
                'today_stats' => $todayStats ?: [
                    'total_due' => 0, 'overdue_count' => 0,
                    'due_today' => 0, 'total_amount_due' => 0,
                ],
                'today_collections' => $todayCollections ?: [],
                'history' => $history ?: [],
                'earnings' => $earnings ?: [
                    'total_collected' => 0, 'total_paid_count' => 0, 'total_late_fees' => 0,
                ],
            ]]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::emiCollection error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/lead-conversion
     */
    public function leadConversion()
    {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM leads");
            $won = $this->db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE status='closed_won'");
            $lost = $this->db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE status='closed_lost'");
            $totalCount = (int)($total['count']??0);
            $wonCount = (int)($won['count']??0);
            $rate = $totalCount > 0 ? round(($wonCount / $totalCount) * 100, 1) : 0;
            return $this->jsonResponse(['success'=>true,'data'=>[
                'total_leads' => $totalCount,
                'won' => $wonCount,
                'lost' => (int)($lost['count']??0),
                'conversion_rate' => $rate,
                'pipeline' => $this->db->fetchAll("SELECT status, COUNT(*) as count FROM leads GROUP BY status"),
            ]]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::leadConversion error: ' . $e->getMessage());
            return $this->jsonResponse(['success'=>false,'data'=>[],'error'=>'Internal server error'],500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/daily-sales
     */
    public function dailySales()
    {
        try {
            $data = $this->db->fetchAll(
                "SELECT DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue
                 FROM bookings WHERE status!='cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) ORDER BY date ASC"
            );
            return $this->jsonResponse(['success'=>true,'data'=>$data?:[]]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::dailySales error: ' . $e->getMessage());
            return $this->jsonResponse(['success'=>false,'data'=>[],'error'=>'Internal server error'],500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/telecaller-dashboard
     * Returns telecaller dashboard data: assigned leads, today's stats, earnings.
     */
    public function telecallerDashboard()
    {
        try {
            $userId = $GLOBALS['api_user_id'] ?? $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            if (!$userId) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $data = [];

            // Today's stats from telecaller_daily_tasks
            try {
                $today = date('Y-m-d');
                $todayStats = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(calls_made), 0) as completed_calls,
                            COALESCE(SUM(calls_connected), 0) as connected,
                            COALESCE(SUM(leads_converted), 0) as valid_leads,
                            COALESCE(SUM(leads_callback), 0) as callback,
                            COALESCE(SUM(pending_calls), 0) as pending_calls,
                            COALESCE(SUM(target_calls), 50) as target_calls
                     FROM telecaller_daily_tasks
                     WHERE user_id = ? AND task_date = ?",
                    [$userId, $today]
                );
                $data['today_stats'] = $todayStats ?: [
                    'completed_calls' => 0, 'connected' => 0, 'valid_leads' => 0,
                    'callback' => 0, 'pending_calls' => 0, 'target_calls' => 50,
                ];
            } catch (\Exception $e) {
                $data['today_stats'] = [
                    'completed_calls' => 0, 'connected' => 0, 'valid_leads' => 0,
                    'callback' => 0, 'pending_calls' => 0, 'target_calls' => 50,
                ];
            }

            // Assigned leads (leads assigned to this telecaller)
            try {
                $leads = $this->db->fetchAll(
                    "SELECT l.id, l.name, l.phone, l.source, l.status, l.priority,
                            l.notes, l.created_at as lastCall,
                            0 as callCount
                     FROM leads l
                     WHERE l.assigned_to = ?
                       AND l.status NOT IN ('closed_won', 'closed_lost')
                     ORDER BY l.priority DESC, l.created_at DESC
                     LIMIT 20",
                    [$userId]
                );
                $data['leads'] = $leads;
            } catch (\Exception $e) {
                $data['leads'] = [];
            }

            // Earnings (telecaller commissions)
            try {
                $earnings = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(commission_amount), 0) as total_earnings,
                            COALESCE(SUM(CASE WHEN status = 'pending' THEN commission_amount ELSE 0 END), 0) as pending_earnings
                     FROM telecaller_commissions
                     WHERE telecaller_id = ?
                       AND MONTH(created_at) = MONTH(NOW())
                       AND YEAR(created_at) = YEAR(NOW())",
                    [$userId]
                );
                $data['earnings'] = $earnings ?: ['total_earnings' => 0, 'pending_earnings' => 0];
            } catch (\Exception $e) {
                $data['earnings'] = ['total_earnings' => 0, 'pending_earnings' => 0];
            }

            // Monthly performance
            try {
                $performance = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(total_calls), 0) as total_calls,
                            COALESCE(SUM(connected_calls), 0) as connected_calls,
                            COALESCE(SUM(leads_converted), 0) as leads_converted,
                            COALESCE(SUM(total_commission), 0) as total_commission
                     FROM telecaller_performance
                     WHERE telecaller_id = ?
                       AND period_start >= DATE_FORMAT(NOW(), '%Y-%m-01')",
                    [$userId]
                );
                $data['monthly_performance'] = $performance ?: [
                    'total_calls' => 0, 'connected_calls' => 0,
                    'leads_converted' => 0, 'total_commission' => 0,
                ];
            } catch (\Exception $e) {
                $data['monthly_performance'] = [
                    'total_calls' => 0, 'connected_calls' => 0,
                    'leads_converted' => 0, 'total_commission' => 0,
                ];
            }

            return $this->jsonResponse(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::telecallerDashboard error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => 'Internal server error'], 500);
        }
    }
}
