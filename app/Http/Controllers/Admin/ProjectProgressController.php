<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class ProjectProgressController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, d.name as district_name,
                    (SELECT COUNT(*) FROM plots WHERE colony_id = c.id) as total_plots,
                    (SELECT COUNT(*) FROM plots WHERE colony_id = c.id AND status = 'sold') as sold_plots
                FROM projects p
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute();
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $projects = [];
        }
        return $this->render('admin/projects/progress/index', [
            'page_title' => 'Project Progress',
            'projects' => $projects
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, d.name as district_name, s.name as state_name,
                    c.name as colony_name
                FROM projects p
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $project = null;
        }
        if (!$project) {
            $this->setFlash('error', 'Project not found');
            $this->redirect('/admin/projects/progress');
        }
        $milestones = [];
        if (!empty($project['milestone_json'])) {
            $milestones = json_decode($project['milestone_json'], true) ?? [];
        }
        return $this->render('admin/projects/progress/show', [
            'page_title' => 'Progress: ' . ($project['name'] ?? ''),
            'project' => $project,
            'milestones' => $milestones
        ]);
    }

    public function updateProgress($id)
    {
        $this->requireAdmin();
        $progress_pct = $_POST['progress_pct'] ?? 0;
        $milestone_title = $_POST['milestone_title'] ?? '';
        $milestone_status = $_POST['milestone_status'] ?? 'pending';
        try {
            $stmt = $this->db->prepare("SELECT milestone_json FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);
            $milestones = [];
            if ($project && !empty($project['milestone_json'])) {
                $milestones = json_decode($project['milestone_json'], true) ?? [];
            }
            if (!empty($milestone_title)) {
                $milestones[] = [
                    'title' => $milestone_title,
                    'status' => $milestone_status,
                    'date' => date('Y-m-d H:i:s')
                ];
            }
            $milestone_json = json_encode($milestones);
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $updateStmt = $this->db->prepare("UPDATE projects SET progress_pct = ?, milestone_json = ?, progress_last_updated = NOW() WHERE id = ?" . $tenantSql);
            $updateStmt->execute(array_merge([$progress_pct, $milestone_json, $id], $tenantParams));
            $this->setFlash('success', 'Project progress updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update progress: ' . $e->getMessage());
        }
        $this->redirect('/admin/projects/progress/show/' . $id);
    }

    public function budget($id)
    {
        $this->requireAdmin();
        $project_budget = $_POST['project_budget'] ?? 0;
        $amount_spent = $_POST['amount_spent'] ?? 0;
        $project_manager = $_POST['project_manager'] ?? '';
        $site_supervisor = $_POST['site_supervisor'] ?? '';
        $contractor_name = $_POST['contractor_name'] ?? '';
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE projects SET project_budget = ?, amount_spent = ?, project_manager = ?, site_supervisor = ?, contractor_name = ? WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$project_budget, $amount_spent, $project_manager, $site_supervisor, $contractor_name, $id], $tenantParams));
            $this->setFlash('success', 'Project budget & team updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update budget: ' . $e->getMessage());
        }
        $this->redirect('/admin/projects/progress/show/' . $id);
    }
}
