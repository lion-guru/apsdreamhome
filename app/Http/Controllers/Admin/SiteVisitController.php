<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace

/**
 * Admin Site Visit Management
 * View and manage all site visits across the platform
 */
class SiteVisitController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all site visits with stats
     */
    public function index()
    {
        $this->requireAdmin();
        $this->layout = 'layouts/admin';
        $currentPage = 'site-visits';

        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $tab = $_GET['tab'] ?? 'all';
        $search = trim($_GET['q'] ?? '');

        $stats = ['total' => 0, 'today' => 0, 'upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
        $visits = [];

        try {
            // Stats
            $stats['total'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits")->fetchColumn();
            $stats['today'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE visit_date = CURDATE() AND status NOT IN ('cancelled','completed')")->fetchColumn();
            $stats['upcoming'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE visit_date > CURDATE() AND status NOT IN ('cancelled','completed')")->fetchColumn();
            $stats['completed'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE status = 'completed'")->fetchColumn();
            $stats['cancelled'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE status = 'cancelled'")->fetchColumn();

            // Query
            $sql = "SELECT sv.*, l.name as lead_name, l.phone as lead_phone,
                           u.name as associate_name, u.email as associate_email
                    FROM site_visits sv
                    LEFT JOIN leads l ON l.id = sv.lead_id
                    LEFT JOIN users u ON u.id = sv.assigned_to
                    WHERE 1=1";

            $params = [];

            if ($tab === 'today') {
                $sql .= " AND sv.visit_date = CURDATE() AND sv.status NOT IN ('cancelled','completed')";
            } elseif ($tab === 'upcoming') {
                $sql .= " AND sv.visit_date >= CURDATE() AND sv.status NOT IN ('cancelled','completed')";
            } elseif ($tab === 'completed') {
                $sql .= " AND sv.status = 'completed'";
            } elseif ($tab === 'cancelled') {
                $sql .= " AND sv.status = 'cancelled'";
            }

            if ($search) {
                $sql .= " AND (sv.visitor_name LIKE ? OR sv.visitor_phone LIKE ? OR l.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $sql .= " ORDER BY sv.visit_date DESC, sv.visit_time DESC LIMIT 100";

            $st = $pdo->prepare($sql);
            $st->execute($params);
            $visits = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Admin siteVisits error: ' . $e->getMessage());
        }

        $this->render('admin/site_visits/index', [
            'page_title' => 'Site Visits Management',
            'currentPage' => $currentPage,
            'visits' => $visits,
            'stats' => $stats,
            'active_tab' => $tab,
            'search' => $search,
        ]);
    }

    /**
     * Update site visit status (AJAX)
     */
    public function updateStatus($id)
    {
        $this->requireAdmin();
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $status = $_POST['status'] ?? '';
        $valid = ['scheduled','completed','cancelled','rescheduled','no_show'];
        if (!in_array($status, $valid)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid status']);
            return;
        }

        try {
            $st = $pdo->prepare("UPDATE site_visits SET status = ? WHERE id = ?");
            $st->execute([$status, $id]);
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
