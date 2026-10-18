<?php

namespace App\Http\Controllers\Admin;

use \App\Traits\TenantAwareTrait;

class IVRFlowController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->data = [];
    }

    public function index()
    {
        $this->requireAdmin();

        $flows = [];
        try {
            $flows = $this->db->fetchAll("
                SELECT f.*, 
                    (SELECT COUNT(*) FROM ivr_flow_steps WHERE flow_id = f.id) as step_count,
                    (SELECT COUNT(*) FROM ai_call_sessions WHERE ivr_flow_id = f.id) as call_count
                FROM ivr_flows f
                ORDER BY f.created_at DESC
            ");
        } catch (\Exception $e) {}

        $this->data = [
            'page_title' => 'IVR Flows',
            'flows' => $flows,
        ];
        $this->render('admin/ivr_flows/index');
    }

    public function create()
    {
        $this->requireAdmin();
        $this->data = ['page_title' => 'Create IVR Flow'];
        $this->render('admin/ivr_flows/create');
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $welcome_message = trim($_POST['welcome_message'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Flow name is required');
            $this->redirect('/admin/ivr-flows/create');
            return;
        }

        try {
            $this->db->execute("
                INSERT INTO ivr_flows (name, description, welcome_message, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ", [$name, $description, $welcome_message, $is_active]);
            $this->setFlash('success', 'IVR Flow created successfully');
            $this->redirect('/admin/ivr-flows');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create flow: ' . $e->getMessage());
            $this->redirect('/admin/ivr-flows/create');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();

        $flow = null;
        try {
            $flow = $this->db->fetchOne("SELECT * FROM ivr_flows WHERE id = ?", [$id]);
            $steps = $this->db->fetchAll("SELECT * FROM ivr_flow_steps WHERE flow_id = ? ORDER BY step_order", [$id]);
        } catch (\Exception $e) {}

        if (!$flow) {
            $this->setFlash('error', 'Flow not found');
            $this->redirect('/admin/ivr-flows');
            return;
        }

        $this->data = [
            'page_title' => 'Edit IVR Flow: ' . $flow['name'],
            'flow' => $flow,
            'steps' => $steps,
        ];
        $this->render('admin/ivr_flows/edit');
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $welcome_message = trim($_POST['welcome_message'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $this->db->execute("
                UPDATE ivr_flows SET name = ?, description = ?, welcome_message = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [$name, $description, $welcome_message, $is_active, $id]);
            $this->setFlash('success', 'IVR Flow updated successfully');
            $this->redirect('/admin/ivr-flows');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update flow: ' . $e->getMessage());
            $this->redirect("/admin/ivr-flows/$id/edit");
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $this->db->execute("DELETE FROM ivr_flow_steps WHERE flow_id = ?", [$id]);
            $this->db->execute("DELETE FROM ivr_flows WHERE id = ?", [$id]);
            $this->setFlash('success', 'IVR Flow deleted successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to delete flow: ' . $e->getMessage());
        }
        $this->redirect('/admin/ivr-flows');
    }

    public function steps($flowId)
    {
        $this->requireAdmin();

        $flow = $this->db->fetchOne("SELECT * FROM ivr_flows WHERE id = ?", [$flowId]);
        if (!$flow) {
            $this->setFlash('error', 'Flow not found');
            $this->redirect('/admin/ivr-flows');
            return;
        }

        $steps = [];
        try {
            $steps = $this->db->fetchAll("SELECT * FROM ivr_flow_steps WHERE flow_id = ? ORDER BY step_order", [$flowId]);
        } catch (\Exception $e) {}

        $this->data = [
            'page_title' => 'IVR Steps: ' . $flow['name'],
            'flow' => $flow,
            'steps' => $steps,
        ];
        $this->render('admin/ivr_flows/steps');
    }
}