<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateSiteVisitController
 * Handles site visit scheduling and management
 */
class SiteVisitController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Site visits list
     */
    public function siteVisits()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $status = $_GET['status'] ?? '';
            $where = "WHERE sv.agent_id = ?{$tidSql}";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            if ($status) {
                $where .= " AND sv.status = ?";
                $params[] = $status;
            }

            $siteVisits = $db->fetchAll("
                SELECT sv.*, pl.plot_number, u.name as customer_name, u.phone
                FROM site_visits sv
                JOIN plots pl ON pl.id = sv.plot_id
                LEFT JOIN users u ON u.id = sv.user_id
                WHERE {$where}
                ORDER BY sv.visit_date DESC, sv.visit_time DESC
            ", $params) ?: [];

            $this->render('associate/site_visits', [
                'page_title' => 'Site Visits - Associate Portal',
                'page_description' => 'Manage site visits',
                'site_visits' => $siteVisits,
                'status' => $status,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController error: ' . $e->getMessage());
        }
    }

    /**
     * Schedule site visit
     */
    public function scheduleSiteVisit()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $data = [
                'agent_id' => $userId,
                'user_id' => (int)($_POST['customer_id'] ?? 0),
                'plot_id' => (int)($_POST['plot_id'] ?? 0),
                'visit_date' => $_POST['visit_date'] ?? '',
                'visit_time' => $_POST['visit_time'] ?? '10:00:00',
                'status' => 'scheduled',
                'notes' => trim($_POST['notes'] ?? ''),
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Validate
            if (empty($data['customer_id'])) throw new Exception('Customer is required');
            if (empty($data['plot_id'])) throw new Exception('Plot is required');
            if (empty($data['visit_date'])) throw new Exception('Visit date is required');

            // Check if plot exists
            $stmt = $db->prepare("SELECT id FROM plots WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1");
            $stmt->execute(array_merge([$data['plot_id']], $tid > 1 ? [$tid] : []));
            if (!$stmt->fetch()) throw new Exception('Plot not found');

            // Check if customer exists
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$data['user_id']];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();
            $stmt = $db->prepare("SELECT id FROM users WHERE id = ?{$tidSql} LIMIT 1");
            $stmt->execute($params);
            if (!$stmt->fetch()) throw new Exception('Customer not found');

            $db->insert('site_visits', $data);
            $visitId = (int)$db->lastInsertId();

            $this->logActivity($userId, 'site_visit_scheduled', ['visit_id' => $visitId]);

            $_SESSION['success'] = 'Site visit scheduled successfully!';
            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::scheduleSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to schedule: ' . $e->getMessage();
            $this->redirect('/associate/site-visits');
        }
    }

    /**
     * Complete site visit
     */
    public function completeSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $feedback = trim($_POST['feedback'] ?? '');

            $stmt = $db->prepare("UPDATE site_visits SET status = 'completed', feedback = ?, completed_at = NOW() WHERE id = ? AND agent_id = ?{$tidSql}");
            $params = array_merge([$feedback, $nextAction, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->logActivity($userId, 'site_visit_completed', ['visit_id' => $id]);
                $_SESSION['success'] = 'Site visit marked as completed!';
            } else {
                $_SESSION['error'] = 'Site visit not found or access denied';
            }

            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::completeSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to complete: ' . $e->getMessage();
            $this->redirect('/associate/site-visits');
        }
    }

    /**
     * Cancel site visit
     */
    public function cancelSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $reason = trim($_POST['reason'] ?? '');

            $stmt = $db->prepare("UPDATE site_visits SET status = 'cancelled' WHERE id = ? AND agent_id = ?{$tidSql}");
            $params = array_merge([$reason, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->logActivity($userId, 'site_visit_cancelled', ['visit_id' => $id, 'reason' => $reason]);
                $_SESSION['success'] = 'Site visit cancelled!';
            } else {
                $_SESSION['error'] = 'Site visit not found or access denied';
            }

            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::cancelSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to cancel: ' . $e->getMessage();
            $this->redirect('/associate/site-visits');
        }
    }

    /**
     * Reschedule site visit
     */
    public function rescheduleSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $newDate = $_POST['new_date'] ?? '';
            $newTime = $_POST['new_time'] ?? '10:00:00';

            if (empty($newDate)) throw new Exception('New date is required');

            $stmt = $db->prepare("UPDATE site_visits SET visit_date = ?, visit_time = ?, status = 'rescheduled', rescheduled_at = NOW() WHERE id = ? AND associate_id = ?{$tidSql}");
            $params = array_merge([$newDate, $newTime, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->logActivity($userId, 'site_visit_rescheduled', ['visit_id' => $id, 'new_date' => $newDate]);
                $_SESSION['success'] = 'Site visit rescheduled!';
            } else {
                $_SESSION['error'] = 'Site visit not found or access denied';
            }

            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::rescheduleSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to reschedule: ' . $e->getMessage();
            $this->redirect('/associate/site-visits');
        }
    }

    /**
     * Calendar data for site visits
     */
    public function calendarData()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        $params = array_merge([$userId, $start, $end], $tid > 1 ? [$tid] : []);
        $siteVisits = $db->fetchAll("
            SELECT sv.id, sv.visit_date, sv.visit_time, sv.status, pl.plot_number, c.name as customer_name
            FROM site_visits sv
            JOIN plots pl ON pl.id = sv.plot_id
            JOIN customers c ON c.id = sv.user_id
            WHERE sv.agent_id = ? AND sv.visit_date BETWEEN ? AND ?{$tidSql}
        ", $params) ?: [];

        $events = [];
        foreach ($siteVisits as $sv) {
            $color = match($sv['status']) {
                'scheduled' => '#3b82f6',
                'completed' => '#22c55e',
                'cancelled' => '#ef4444',
                'rescheduled' => '#f59e0b',
                default => '#6b7280'
            };
            $events[] = [
                'id' => $sv['id'],
                'title' => $sv['plot_number'] . ' - ' . $sv['customer_name'],
                'start' => $sv['visit_date'] . 'T' . $sv['visit_time'],
                'color' => $color,
                'extendedProps' => [
                    'status' => $sv['status'],
                    'customer' => $sv['customer_name'],
                    'plot' => $sv['plot_number'],
                ],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }
}

