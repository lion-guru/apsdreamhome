<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class AssociateExtensionController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.name, u.email, u.phone, u.role, u.status as user_status
                FROM users a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY u.name ASC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $users = [];
        }
        return $this->render('admin/associate-extensions/index', [
            'page_title' => 'Associate Extensions',
            'users' => $users
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.name, u.email, u.phone, u.role, u.status as user_status,
                    u.created_at as user_created_at
                FROM users a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $associate = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $associate = null;
        }
        if (!$associate) {
            $this->setFlash('error', 'Associate not found');
            $this->redirect('/admin/associate-extensions');
        }
        return $this->render('admin/associate-extensions/show', [
            'page_title' => 'Associate: ' . ($associate['name'] ?? ''),
            'associate' => $associate
        ]);
    }

    public function updatePoints($id)
    {
        $this->requireAdmin();
        $points = $_POST['points'] ?? 0;
        $badges = $_POST['badges'] ?? '';
        $training_progress = $_POST['training_progress'] ?? 0;
        try {
            [$tw, $tp] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE users SET total_points = ? WHERE id = ?" . $tw);
            $stmt->execute([$points, $id, ...$tp]);
            $this->setFlash('success', 'Associate extension updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update: ' . $e->getMessage());
        }
        $this->redirect('/admin/associate-extensions/show/' . $id);
    }
}
