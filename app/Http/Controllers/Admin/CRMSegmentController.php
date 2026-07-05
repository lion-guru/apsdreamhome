<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database;

class CRMSegmentController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $segments = $db->query("SELECT s.*, (SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL AND (JSON_EXTRACT(s.filter_criteria, '$.status') IS NULL OR status = JSON_UNQUOTE(JSON_EXTRACT(s.filter_criteria, '$.status')))) as lead_count FROM crm_segments s ORDER BY s.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            // Try simpler query if JSON functions fail
            try {
                $segments = $db->query("SELECT s.*, 0 as lead_count FROM crm_segments s ORDER BY s.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e2) {
                $segments = [];
            }
        }

        // Update lead counts manually
        foreach ($segments as &$seg) {
            try {
                $criteria = json_decode($seg['filter_criteria'] ?? '{}', true) ?? [];
                $where = ["deleted_at IS NULL"];
                $params = [];
                if (!empty($criteria['status'])) { $where[] = "status = ?"; $params[] = $criteria['status']; }
                if (!empty($criteria['source'])) { $where[] = "source = ?"; $params[] = $criteria['source']; }
                if (!empty($criteria['min_score'])) { $where[] = "lead_score >= ?"; $params[] = (int)$criteria['min_score']; }
                if (!empty($criteria['city'])) { $where[] = "city = ?"; $params[] = $criteria['city']; }
                if (!empty($criteria['min_budget'])) { $where[] = "budget >= ?"; $params[] = (float)$criteria['min_budget']; }
                if (!empty($criteria['max_budget'])) { $where[] = "budget <= ?"; $params[] = (float)$criteria['max_budget']; }
                $seg['lead_count'] = (int)$db->query("SELECT COUNT(*) FROM leads WHERE " . implode(' AND ', $where), $params)->fetchColumn();
            } catch (\Throwable $e) {
                $seg['lead_count'] = 0;
            }
        }

        return $this->render('admin/crm/segments/index', [
            'segments' => $segments,
            'page_title' => 'Lead Segments',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $this->setFlash('error', 'Segment name is required');
                return $this->redirect('/admin/crm/segments');
            }

            $criteria = [];
            if (!empty($_POST['status'])) $criteria['status'] = $_POST['status'];
            if (!empty($_POST['source'])) $criteria['source'] = $_POST['source'];
            if (!empty($_POST['city'])) $criteria['city'] = $_POST['city'];
            if (!empty($_POST['min_score'])) $criteria['min_score'] = (int)$_POST['min_score'];
            if (!empty($_POST['min_budget'])) $criteria['min_budget'] = (float)$_POST['min_budget'];
            if (!empty($_POST['max_budget'])) $criteria['max_budget'] = (float)$_POST['max_budget'];

            $db->query("INSERT INTO crm_segments (name, description, filter_criteria, created_by, created_at) VALUES (?, ?, ?, ?, NOW())", [
                $name, $description, json_encode($criteria), $_SESSION['admin_id'] ?? 0
            ]);
            $this->setFlash('success', 'Segment created');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to create segment');
        }
        return $this->redirect('/admin/crm/segments');
    }

    public function delete($id)
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $db->query("DELETE FROM crm_segments WHERE id = ?", [$id]);
            $this->setFlash('success', 'Segment deleted');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to delete segment');
        }
        return $this->redirect('/admin/crm/segments');
    }

    public function leads($id)
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $seg = $db->query("SELECT * FROM crm_segments WHERE id = $id")->fetch(\PDO::FETCH_ASSOC);
            $criteria = json_decode($seg['filter_criteria'] ?? '{}', true) ?? [];

            $where = ["deleted_at IS NULL"];
            $params = [];
            if (!empty($criteria['status'])) { $where[] = "status = ?"; $params[] = $criteria['status']; }
            if (!empty($criteria['source'])) { $where[] = "source = ?"; $params[] = $criteria['source']; }
            if (!empty($criteria['min_score'])) { $where[] = "lead_score >= ?"; $params[] = (int)$criteria['min_score']; }
            if (!empty($criteria['city'])) { $where[] = "city = ?"; $params[] = $criteria['city']; }
            if (!empty($criteria['min_budget'])) { $where[] = "budget >= ?"; $params[] = (float)$criteria['min_budget']; }
            if (!empty($criteria['max_budget'])) { $where[] = "budget <= ?"; $params[] = (float)$criteria['max_budget']; }

            $leads = $db->query("SELECT l.*, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id WHERE " . implode(' AND ', $where) . " ORDER BY l.created_at DESC LIMIT 200", $params)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $leads = [];
            $seg = null;
        }
        return $this->render('admin/crm/segments/leads', [
            'leads' => $leads,
            'segment' => $seg,
            'page_title' => 'Segment: ' . ($seg['name'] ?? ''),
        ]);
    }
}
