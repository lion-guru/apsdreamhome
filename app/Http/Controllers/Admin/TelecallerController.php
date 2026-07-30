<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use \App\Traits\TenantAwareTrait;

class TelecallerController extends AdminController
{
    use TenantAwareTrait;
    public function index()
    {
        $this->requireAdmin();

        $dateFilter = $_GET['date'] ?? date('Y-m-d');
        $telecallerFilter = $_GET['telecaller_id'] ?? '';

        try {
            $today = date('Y-m-d');

            $todayStats = $this->db->fetch("SELECT COALESCE(SUM(calls_made),0) as total_calls, COALESCE(SUM(calls_connected),0) as connected, COALESCE(SUM(leads_converted),0) as converted, COALESCE(SUM(pending_calls),0) as pending FROM telecaller_daily_tasks WHERE task_date = ?", [$today]);

            $where = "WHERE t.task_date = ?";
            $params = [$dateFilter];
            if (!empty($telecallerFilter)) {
                $where .= " AND t.user_id = ?";
                $params[] = (int)$telecallerFilter;
            }

            $tasks = $this->db->fetchAll("SELECT t.*, u.name as telecaller_name, u.email, u.phone FROM telecaller_daily_tasks t LEFT JOIN users u ON t.user_id = u.id $where ORDER BY t.created_at DESC", $params);

            [$tidSql, $tidParams] = $this->tenantWhere();
            $telecallers = $this->db->fetchAll("SELECT id, name, email, phone FROM users WHERE role IN ('telecaller','employee','agent'){$tidSql} ORDER BY name", $tidParams);

        } catch (\Exception $e) {
            $todayStats = ['total_calls' => 0, 'connected' => 0, 'converted' => 0, 'pending' => 0];
            $tasks = [];
            $telecallers = [];
        }

        return $this->render('admin/telecaller/index', [
            'page_title' => 'Telecaller Management',
            'todayStats' => $todayStats,
            'tasks' => $tasks,
            'telecallers' => $telecallers,
            'dateFilter' => $dateFilter,
            'telecallerFilter' => $telecallerFilter,
        ]);
    }

    public function performance()
    {
        $this->requireAdmin();

        $telecallerFilter = $_GET['telecaller_id'] ?? '';
        $periodFilter = $_GET['period'] ?? '';

        try {
            $overallStats = $this->db->fetch("SELECT COALESCE(SUM(total_calls),0) as total_calls, COALESCE(SUM(connected_calls),0) as connected, COALESCE(SUM(leads_converted),0) as converted, COALESCE(SUM(total_commission),0) as total_commission FROM telecaller_performance");

            $where = "WHERE 1=1";
            $params = [];
            if (!empty($telecallerFilter)) {
                $where .= " AND p.telecaller_id = ?";
                $params[] = (int)$telecallerFilter;
            }

            $records = $this->db->fetchAll("SELECT p.*, u.name as telecaller_name, u.email FROM telecaller_performance p LEFT JOIN users u ON p.telecaller_id = u.id $where ORDER BY p.period_start DESC", $params);

            [$tidSql, $tidParams] = $this->tenantWhere();
            $telecallers = $this->db->fetchAll("SELECT id, name, email, phone FROM users WHERE role IN ('telecaller','employee','agent'){$tidSql} ORDER BY name", $tidParams);

        } catch (\Exception $e) {
            $overallStats = ['total_calls' => 0, 'connected' => 0, 'converted' => 0, 'total_commission' => 0];
            $records = [];
            $telecallers = [];
        }

        return $this->render('admin/telecaller/performance', [
            'page_title' => 'Telecaller Performance',
            'overallStats' => $overallStats,
            'records' => $records,
            'telecallers' => $telecallers,
            'telecallerFilter' => $telecallerFilter,
            'periodFilter' => $periodFilter,
        ]);
    }

    public function showTask($id)
    {
        $this->requireAdmin();

        try {
            $task = $this->db->fetch("SELECT t.*, u.name as telecaller_name, u.email, u.phone FROM telecaller_daily_tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?", [(int)$id]);
            if (!$task) {
                header('Location: ' . BASE_URL . '/admin/telecaller');
                exit;
            }
        } catch (\Exception $e) {
            header('Location: ' . BASE_URL . '/admin/telecaller');
            exit;
        }

        return $this->render('admin/telecaller/show', [
            'page_title' => 'Task #' . $id,
            'task' => $task,
        ]);
    }

    public function showPerformance($id)
    {
        $this->requireAdmin();

        try {
            $record = $this->db->fetch("SELECT p.*, u.name as telecaller_name, u.email, u.phone FROM telecaller_performance p LEFT JOIN users u ON p.telecaller_id = u.id WHERE p.id = ?", [(int)$id]);
            if (!$record) {
                header('Location: ' . BASE_URL . '/admin/telecaller/performance');
                exit;
            }
        } catch (\Exception $e) {
            header('Location: ' . BASE_URL . '/admin/telecaller/performance');
            exit;
        }

        return $this->render('admin/telecaller/show', [
            'page_title' => 'Performance #' . $id,
            'record' => $record,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();

        $userId = (int)($_POST['user_id'] ?? 0);
        $taskDate = $_POST['task_date'] ?? date('Y-m-d');
        $leadsAssigned = (int)($_POST['total_leads_assigned'] ?? 0);
        $callsMade = (int)($_POST['calls_made'] ?? 0);
        $callsConnected = (int)($_POST['calls_connected'] ?? 0);
        $leadsConverted = (int)($_POST['leads_converted'] ?? 0);
        $leadsCallback = (int)($_POST['leads_callback'] ?? 0);
        $leadsNotInterested = (int)($_POST['leads_not_interested'] ?? 0);
        $pendingCalls = (int)($_POST['pending_calls'] ?? 0);
        $targetCalls = (int)($_POST['target_calls'] ?? 0);
        $notes = $_POST['notes'] ?? '';

        try {
            $this->db->query("INSERT INTO telecaller_daily_tasks (user_id, task_date, total_leads_assigned, calls_made, calls_connected, leads_converted, leads_callback, leads_not_interested, pending_calls, target_calls, notes, tenant_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())", [$userId, $taskDate, $leadsAssigned, $callsMade, $callsConnected, $leadsConverted, $leadsCallback, $leadsNotInterested, $pendingCalls, $targetCalls, $notes, (int)$this->tenantId()]);
            $this->setFlash('success', 'Daily task saved successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to save task: ' . $e->getMessage());
        }

        $this->redirect('/admin/telecaller');
    }

    public function updatePerformance()
    {
        $this->requireAdmin();

        $id = (int)($_POST['id'] ?? 0);
        $totalCalls = (int)($_POST['total_calls'] ?? 0);
        $connectedCalls = (int)($_POST['connected_calls'] ?? 0);
        $leadsConverted = (int)($_POST['leads_converted'] ?? 0);
        $totalCommission = (float)($_POST['total_commission'] ?? 0);
        $targetAchieved = (float)($_POST['target_achieved'] ?? 0);
        $rating = $_POST['rating'] ?? 'average';

        try {
            $this->db->query("UPDATE telecaller_performance SET total_calls = ?, connected_calls = ?, leads_converted = ?, total_commission = ?, target_achieved = ?, rating = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?", [$totalCalls, $connectedCalls, $leadsConverted, $totalCommission, $targetAchieved, $rating, $id, (int)$this->tenantId()]);
            $this->setFlash('success', 'Performance updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update performance: ' . $e->getMessage());
        }

        $this->redirect('/admin/telecaller/performance');
    }
}
