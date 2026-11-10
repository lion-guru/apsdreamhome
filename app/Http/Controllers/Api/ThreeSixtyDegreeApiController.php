<?php

namespace App\Http\Controllers\Api;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * ThreeSixtyDegreeApiController
 *
 * 360-degree analytics over the whole business: customers, associates,
 * plots, bookings and colonies. One endpoint per lens so the admin
 * dashboard can drill into any dimension.
 *
 * GET /api/v2/admin/360-degree/dashboard
 * GET /api/v2/admin/360-degree/customer
 * GET /api/v2/admin/360-degree/associate
 * GET /api/v2/admin/360-degree/plot
 * GET /api/v2/admin/360-degree/booking
 * GET /api/v2/admin/360-degree/colony
 */
class ThreeSixtyDegreeApiController extends BaseApiController
{
    private function db()
    {
        return Database::getInstance();
    }

    private function tenantId(): int
    {
        return (int)(TenantContext::getId() ?: 1);
    }

    /**
     * Run a query and return [] when the table/column does not exist.
     * A missing dimension must never take the whole 360 dashboard down.
     */
    private function safeAll(string $sql, array $params = []): array
    {
        try {
            return $this->db()->fetchAll($sql, $params) ?: [];
        } catch (\Throwable $e) {
            error_log('[ThreeSixtyDegreeApiController] ' . $e->getMessage());
            return [];
        }
    }

    private function safeOne(string $sql, array $params = []): array
    {
        try {
            return $this->db()->fetchOne($sql, $params) ?: [];
        } catch (\Throwable $e) {
            error_log('[ThreeSixtyDegreeApiController] ' . $e->getMessage());
            return [];
        }
    }

    private function scope(array $filters = []): array
    {
        $tid = $this->tenantId();
        $from = $filters['from'] ?? (date('Y-m-d', strtotime('-30 days')));
        $to   = $filters['to'] ?? date('Y-m-d');
        return [$tid, $from, $to];
    }

    /**
     * Cross-dimension overview.
     */
    public function dashboard()
    {
        try {
            $db = $this->db();
            $tid = $this->tenantId();
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
            $to   = $_GET['to'] ?? date('Y-m-d');

            $customers = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) AS period_new
                 FROM users WHERE role = 'customer' AND tenant_id = ?",
                [$from, $to, $tid]
            );

            $associates = $this->safeOne(
                "SELECT COUNT(*) AS total FROM associates WHERE tenant_id = ?",
                [$tid]
            );

            $plots = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) AS booked,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold,
                        COALESCE(SUM(total_price), 0) AS inventory_value
                 FROM plots WHERE tenant_id = ? AND deleted_at IS NULL",
                [$tid]
            );

            $bookings = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        COALESCE(SUM(total_amount), 0) AS value
                 FROM bookings WHERE tenant_id = ? AND DATE(created_at) BETWEEN ? AND ?",
                [$tid, $from, $to]
            );

            $leads = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status IN ('won','closed_won') THEN 1 ELSE 0 END) AS won
                 FROM leads WHERE tenant_id = ? AND DATE(created_at) BETWEEN ? AND ?",
                [$tid, $from, $to]
            );

            $colonies = $this->safeOne(
                "SELECT COUNT(*) AS total FROM colonies WHERE tenant_id = ?",
                [$tid]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'period'    => ['from' => $from, 'to' => $to],
                    'customers' => $customers,
                    'associates' => $associates,
                    'plots'     => $plots,
                    'bookings'  => $bookings,
                    'leads'     => $leads,
                    'colonies'  => $colonies,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, '360 Degree Dashboard');
        }
    }

    /**
     * Customer lens.
     */
    public function customer()
    {
        try {
            $tid = $this->tenantId();
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
            $to   = $_GET['to'] ?? date('Y-m-d');
            $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));

            $customers = $this->safeAll(
                "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                        (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = u.id AND b.tenant_id = ?) AS bookings,
                        (SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b WHERE b.customer_id = u.id AND b.tenant_id = ?) AS lifetime_value
                 FROM users u
                 WHERE u.role = 'customer' AND u.tenant_id = ?
                 ORDER BY lifetime_value DESC, u.id DESC
                 LIMIT $limit",
                [$tid, $tid, $tid]
            );

            $byStatus = $this->safeAll(
                "SELECT status, COUNT(*) AS count FROM users
                 WHERE role = 'customer' AND tenant_id = ? GROUP BY status",
                [$tid]
            );

            $newInPeriod = $this->safeOne(
                "SELECT COUNT(*) AS total FROM users
                 WHERE role = 'customer' AND tenant_id = ? AND DATE(created_at) BETWEEN ? AND ?",
                [$tid, $from, $to]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'customers'       => $customers,
                    'by_status'       => $byStatus,
                    'new_in_period'   => $newInPeriod,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, '360 Degree Customer');
        }
    }

    /**
     * Associate lens.
     */
    public function associate()
    {
        try {
            $tid = $this->tenantId();
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
            $to   = $_GET['to'] ?? date('Y-m-d');
            $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));

            $associates = $this->safeAll(
                "SELECT a.id, a.user_id, a.referral_code, a.status, a.created_at,
                        COALESCE(u.name, '') AS name,
                        COALESCE(u.phone, '') AS phone,
                        (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = a.user_id AND l.tenant_id = ?) AS leads,
                        (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = a.user_id AND l.tenant_id = ? AND l.status IN ('won','closed_won')) AS won,
                        (SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b
                           JOIN leads l ON l.id = b.lead_id
                          WHERE l.assigned_to = a.user_id AND b.tenant_id = ?) AS revenue
                 FROM associates a
                 LEFT JOIN users u ON u.id = a.user_id
                 WHERE a.tenant_id = ?
                 ORDER BY revenue DESC, a.id DESC
                 LIMIT $limit",
                [$tid, $tid, $tid, $tid]
            );

            $summary = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active
                 FROM associates WHERE tenant_id = ?",
                [$tid]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'associates' => $associates,
                    'summary'    => $summary,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, '360 Degree Associate');
        }
    }

    /**
     * Plot lens — inventory health + price distribution.
     */
    public function plot()
    {
        try {
            $tid = $this->tenantId();
            $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));

            $stats = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) AS booked,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold,
                        SUM(CASE WHEN status = 'hold' THEN 1 ELSE 0 END) AS hold,
                        COALESCE(SUM(total_price), 0) AS inventory_value,
                        COALESCE(AVG(base_price_per_sqft), 0) AS avg_rate,
                        COALESCE(MIN(base_price_per_sqft), 0) AS min_rate,
                        COALESCE(MAX(base_price_per_sqft), 0) AS max_rate
                 FROM plots WHERE tenant_id = ? AND deleted_at IS NULL",
                [$tid]
            );

            $plots = $this->safeAll(
                "SELECT id, plot_number, colony_id, plot_type, area_sqft,
                        base_price_per_sqft, total_price, status, facing, created_at
                 FROM plots WHERE tenant_id = ? AND deleted_at IS NULL
                 ORDER BY created_at DESC LIMIT $limit",
                [$tid]
            );

            $byType = $this->safeAll(
                "SELECT plot_type, COUNT(*) AS count, COALESCE(SUM(total_price), 0) AS value
                 FROM plots WHERE tenant_id = ? AND deleted_at IS NULL
                 GROUP BY plot_type ORDER BY count DESC",
                [$tid]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'stats'   => $stats,
                    'plots'   => $plots,
                    'by_type' => $byType,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, '360 Degree Plot');
        }
    }

    /**
     * Booking lens.
     */
    public function booking()
    {
        try {
            $tid = $this->tenantId();
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
            $to   = $_GET['to'] ?? date('Y-m-d');
            $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));

            $stats = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        COALESCE(SUM(total_amount), 0) AS value,
                        COALESCE(AVG(total_amount), 0) AS avg_value
                 FROM bookings WHERE tenant_id = ? AND DATE(created_at) BETWEEN ? AND ?",
                [$tid, $from, $to]
            );

            $byStatus = $this->safeAll(
                "SELECT status, COUNT(*) AS count, COALESCE(SUM(total_amount), 0) AS value
                 FROM bookings WHERE tenant_id = ? AND DATE(created_at) BETWEEN ? AND ?
                 GROUP BY status ORDER BY count DESC",
                [$tid, $from, $to]
            );

            $trend = $this->safeAll(
                "SELECT DATE(created_at) AS day, COUNT(*) AS count, COALESCE(SUM(total_amount), 0) AS value
                 FROM bookings WHERE tenant_id = ? AND DATE(created_at) BETWEEN ? AND ?
                 GROUP BY DATE(created_at) ORDER BY day ASC",
                [$tid, $from, $to]
            );

            $recent = $this->safeAll(
                "SELECT b.id, b.booking_number, b.status, b.total_amount, b.created_at,
                        c.name AS customer_name, col.name AS colony_name
                 FROM bookings b
                 LEFT JOIN plot_bookings pb ON pb.booking_id = b.id
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN colonies col ON col.id = p.colony_id
                 LEFT JOIN users c ON c.id = b.customer_id
                 WHERE b.tenant_id = ?
                 ORDER BY b.id DESC LIMIT $limit",
                [$tid]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'stats'     => $stats,
                    'by_status' => $byStatus,
                    'trend'     => $trend,
                    'recent'    => $recent,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, '360 Degree Booking');
        }
    }

    /**
     * Colony lens.
     */
    public function colony()
    {
        try {
            $tid = $this->tenantId();
            $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));

            $colonies = $this->safeAll(
                "SELECT c.id, c.name, c.status, c.total_plots, c.available_plots,
                        c.starting_price, c.created_at,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.deleted_at IS NULL) AS plot_count,
                        (SELECT COALESCE(SUM(p.total_price), 0) FROM plots p WHERE p.colony_id = c.id AND p.deleted_at IS NULL) AS inventory_value,
                        (SELECT COUNT(*) FROM bookings b JOIN plot_bookings pb ON pb.booking_id = b.id
                                                 JOIN plots p2 ON p2.id = pb.plot_id
                          WHERE p2.colony_id = c.id AND b.tenant_id = ?) AS bookings,
                        (SELECT COUNT(*) FROM leads l WHERE l.colony_id = c.id AND l.tenant_id = ?) AS leads
                 FROM colonies c
                 WHERE c.tenant_id = ?
                 ORDER BY inventory_value DESC, c.id DESC
                 LIMIT $limit",
                [$tid, $tid, $tid]
            );

            $summary = $this->safeOne(
                "SELECT COUNT(*) AS total,
                        COALESCE(SUM(total_plots), 0) AS plots,
                        COALESCE(SUM(available_plots), 0) AS available
                 FROM colonies WHERE tenant_id = ?",
                [$tid]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'colonies' => $colonies,
                    'summary'  => $summary,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, '360 Degree Colony');
        }
    }
}
