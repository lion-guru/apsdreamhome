<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class AssociateExtensionController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.name, u.email, u.phone, u.role, u.status as user_status
                FROM associates a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY u.name ASC
            ");
            $stmt->execute();
            $associates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $associates = [];
        }
        return $this->render('admin/associate-extensions/index', [
            'page_title' => 'Associate Extensions',
            'associates' => $associates
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.name, u.email, u.phone, u.role, u.status as user_status,
                    u.created_at as user_created_at
                FROM associates a
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
            $stmt = $this->db->prepare("UPDATE associates SET points = ?, badges = ?, training_progress = ? WHERE id = ?");
            $stmt->execute([$points, $badges, $training_progress, $id]);
            $this->setFlash('success', 'Associate extension updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update: ' . $e->getMessage());
        }
        $this->redirect('/admin/associate-extensions/show/' . $id);
    }
}
