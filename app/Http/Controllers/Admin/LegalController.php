<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;

class LegalController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function services()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("SELECT * FROM legal_services ORDER BY display_order ASC, created_at DESC");
            $services = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $services = [];
        }
        $total = count($services);
        $active = count(array_filter($services, fn($s) => ($s['status'] ?? '') === 'active'));
        return $this->render('admin/legal/services', [
            'page_title' => 'Legal Services',
            'services' => $services,
            'total' => $total,
            'active' => $active
        ]);
    }

    public function createService()
    {
        $this->requireAdmin();
        return $this->render('admin/legal/create-service', [
            'page_title' => 'Add Legal Service'
        ]);
    }

    public function storeService()
    {
        $this->requireAdmin();
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $icon = $_POST['icon'] ?? 'fa-gavel';
        $price_range = $_POST['price_range'] ?? '';
        $duration = $_POST['duration'] ?? '';
        $features = $_POST['features'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $display_order = (int)($_POST['display_order'] ?? 0);
        try {
            $stmt = $this->db->prepare("INSERT INTO legal_services (title, description, icon, price_range, duration, features, status, display_order, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $icon, $price_range, $duration, $features, $status, $display_order, $this->tenantId()]);
            $this->setFlash('success', 'Legal service created successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create legal service: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/services');
    }

    public function disputes()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("SELECT d.*, u.name as assigned_name FROM legal_disputes d LEFT JOIN users u ON d.assigned_to = u.id ORDER BY d.filed_date DESC");
            $disputes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $disputes = [];
        }
        $total = count($disputes);
        $open = count(array_filter($disputes, fn($d) => ($d['status'] ?? '') === 'open'));
        $resolved = count(array_filter($disputes, fn($d) => ($d['status'] ?? '') === 'resolved'));
        return $this->render('admin/legal/disputes', [
            'page_title' => 'Legal Disputes',
            'disputes' => $disputes,
            'total' => $total,
            'open' => $open,
            'resolved' => $resolved
        ]);
    }

    public function showDispute($id)
    {
        $this->requireAdmin();
        try {
            try {
                $stmt = $this->db->prepare("SELECT d.*, u.name as assigned_name FROM legal_disputes d LEFT JOIN users u ON d.assigned_to = u.id WHERE d.id = ?");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([(int)$id]);
            $dispute = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $dispute = null;
        }
        if (!$dispute) {
            $this->setFlash('error', 'Dispute not found');
            $this->redirect('/admin/legal/disputes');
        }
        try {
            $usersStmt = $this->db->query("SELECT id, name FROM users WHERE role IN ('admin','employee','agent') ORDER BY name");
            $users = $usersStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $users = [];
        }
        return $this->render('admin/legal/dispute-show', [
            'page_title' => 'Dispute Details',
            'dispute' => $dispute,
            'users' => $users
        ]);
    }

    public function updateDispute($id)
    {
        $this->requireAdmin();
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $resolved_date = null;
        if ($status === 'resolved') {
            $resolved_date = date('Y-m-d');
        }
        try {
            [$tw, $tp] = $this->tenantWhere();
            $sql = "UPDATE legal_disputes SET status = ?, notes = ?, assigned_to = ?, resolved_date = COALESCE(?, resolved_date) WHERE id = ?" . $tw;
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status, $notes, $assigned_to, $resolved_date, (int)$id, ...$tp]);
            $this->setFlash('success', 'Dispute updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update dispute: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/disputes/' . $id);
    }

    public function deadlines()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("SELECT d.*, u.name as assigned_name FROM legal_deadlines d LEFT JOIN users u ON d.assigned_to = u.id ORDER BY d.deadline_date ASC");
            $deadlines = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $deadlines = [];
        }
        try {
            $usersStmt = $this->db->query("SELECT id, name FROM users WHERE role IN ('admin','employee','agent') ORDER BY name");
            $users = $usersStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $users = [];
        }
        $total = count($deadlines);
        $overdue = 0;
        $upcoming = 0;
        $now = date('Y-m-d');
        $weekLater = date('Y-m-d', strtotime('+7 days'));
        foreach ($deadlines as $dl) {
            $d = $dl['deadline_date'] ?? '';
            if ($d < $now) $overdue++;
            elseif ($d <= $weekLater) $upcoming++;
        }
        return $this->render('admin/legal/deadlines', [
            'page_title' => 'Legal Deadlines',
            'deadlines' => $deadlines,
            'users' => $users,
            'total' => $total,
            'overdue' => $overdue,
            'upcoming' => $upcoming
        ]);
    }

    public function storeDeadline()
    {
        $this->requireAdmin();
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $legal_type = $_POST['legal_type'] ?? '';
        $deadline_date = $_POST['deadline_date'] ?? '';
        $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $status = $_POST['status'] ?? 'pending';
        try {
            $stmt = $this->db->prepare("INSERT INTO legal_deadlines (title, description, legal_type, deadline_date, assigned_to, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $legal_type, $deadline_date, $assigned_to, $status, $this->tenantId()]);
            $this->setFlash('success', 'Deadline created successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create deadline: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/deadlines');
    }
}
