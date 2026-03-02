<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Core\Database;

class ProjectController extends AdminController
{
    protected $projectModel;

    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        // AdminController likely handles basic auth, but we can keep specific ones
        //$this->middleware('role:admin'); // AdminController usually checks this in index or constructor
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);

        $this->projectModel = $this->model('Project');
    }

    /**
     * List all projects
     */
    public function index()
    {
        $projects = $this->projectModel->getAllActiveProjects();
        return $this->render('admin/projects/index', [
            'projects' => $projects,
            'page_title' => $this->mlSupport->translate('Project Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show create project form
     */
    public function create()
    {
        return $this->render('admin/projects/create', [
            'page_title' => $this->mlSupport->translate('Add New Project') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Store new project
     */
    public function store()
    {
        if ($this->request->getMethod() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('/admin/projects/create');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect('/admin/projects/create');
        }

        $data = $this->request->all();

        // Basic validation
        if (empty($data['project_name']) || empty($data['project_code'])) {
            $this->setFlash('error', $this->mlSupport->translate('Project name and code are required.'));
            return $this->redirect('/admin/projects/create');
        }

        // Explicitly define fillable fields for security
        $fillableFields = [
            'project_name',
            'project_code',
            'project_type',
            'location',
            'city',
            'state',
            'pincode',
            'description',
            'short_description',
            'total_area',
            'total_plots',
            'available_plots',
            'price_per_sqft',
            'base_price',
            'project_status',
            'possession_date',
            'rera_number',
            'is_featured',
            'is_active',
            'latitude',
            'longitude',
            'address',
            'highlights',
            'amenities'
        ];

        $projectData = [];
        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $projectData[$field] = $data[$field]; // Model should handle sanitization/encoding
            }
        }

        $projectData['created_by'] = $this->session->get('user_id') ?? 1;
        $projectData['is_active'] = isset($data['is_active']) ? 1 : 0;
        $projectData['is_featured'] = isset($data['is_featured']) ? 1 : 0;

        $projectId = $this->projectModel->createProject($projectData);

        if ($projectId) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Project Creation', 'Created project: ' . h($data['project_name']) . ' (ID: ' . $projectId . ')');
            $this->setFlash('success', $this->mlSupport->translate('Project created successfully.'));
            return $this->redirect('/admin/projects');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to create project.'));
            return $this->redirect('/admin/projects/create');
        }
    }

    /**
     * Show edit project form
     */
    public function edit($id)
    {
        $id = \intval($id);
        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            $this->setFlash('error', $this->mlSupport->translate('Project not found.'));
            return $this->redirect('/admin/projects');
        }

        return $this->render('admin/projects/edit', [
            'project' => $project,
            'page_title' => $this->mlSupport->translate('Edit Project') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Update project
     */
    public function update($id)
    {
        $id = \intval($id);

        if ($this->request->getMethod() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect("/admin/projects/edit/$id");
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->redirect("/admin/projects/edit/$id");
        }

        $data = $this->request->all();

        if (empty($data['project_name'])) {
            $this->setFlash('error', $this->mlSupport->translate('Project name is required.'));
            return $this->redirect("/admin/projects/edit/$id");
        }

        // Explicitly define fillable fields for security
        $fillableFields = [
            'project_name',
            'project_code',
            'project_type',
            'location',
            'city',
            'state',
            'pincode',
            'description',
            'short_description',
            'total_area',
            'total_plots',
            'available_plots',
            'price_per_sqft',
            'base_price',
            'project_status',
            'possession_date',
            'rera_number',
            'is_featured',
            'is_active',
            'latitude',
            'longitude',
            'address',
            'highlights',
            'amenities'
        ];

        $projectData = [];
        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $projectData[$field] = $data[$field];
            }
        }

        $projectData['is_active'] = isset($data['is_active']) ? 1 : 0;
        $projectData['is_featured'] = isset($data['is_featured']) ? 1 : 0;

        $updated = $this->projectModel->updateProject($id, $projectData);

        if ($updated) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Project Update', 'Updated project: ' . h($data['project_name']) . ' (ID: ' . $id . ')');
            $this->setFlash('success', $this->mlSupport->translate('Project updated successfully.'));
            return $this->redirect('/admin/projects');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to update project.'));
            return $this->redirect("/admin/projects/edit/$id");
        }
    }

    /**
     * Delete project
     */
    public function destroy($id)
    {
        $id = \intval($id);

        if ($this->request->getMethod() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('/admin/projects');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect('/admin/projects');
        }

        $stmt = $this->db->prepare("DELETE FROM projects WHERE project_id = :id");
        $deleted = $stmt->execute(['id' => $id]);

        if ($deleted) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Project Deletion', 'Deleted project ID: ' . $id);
            $this->setFlash('success', $this->mlSupport->translate('Project deleted successfully.'));
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to delete project.'));
        }
        return $this->redirect('/admin/projects');
    }

    /**
     * Bulk update projects status
     */
    public function bulkUpdateStatus()
    {
        if ($this->request->getMethod() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('/admin/projects');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->redirect('/admin/projects');
        }

        $data = $this->request->all();
        $projectIds = $data['project_ids'] ?? [];
        $status = $data['status'] ?? null;

        if (empty($projectIds) || !is_array($projectIds)) {
            $this->setFlash('error', $this->mlSupport->translate('Please select projects to update.'));
            return $this->redirect('/admin/projects');
        }

        if ($status === null) {
            $this->setFlash('error', $this->mlSupport->translate('Please select a status.'));
            return $this->redirect('/admin/projects');
        }

        $updatedCount = 0;
        foreach ($projectIds as $projectId) {
            $projectId = intval($projectId);
            $updateData = ['project_status' => $status];

            if ($this->projectModel->updateProject($projectId, $updateData)) {
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Bulk Project Status Update', "Updated $updatedCount projects to status: $status");
            $this->setFlash('success', $this->mlSupport->translate("$updatedCount projects updated successfully."));
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to update projects.'));
        }

        return $this->redirect('/admin/projects');
    }

    /**
     * Bulk delete projects
     */
    public function bulkDelete()
    {
        if ($this->request->getMethod() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('/admin/projects');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->redirect('/admin/projects');
        }

        $data = $this->request->all();
        $projectIds = $data['project_ids'] ?? [];

        if (empty($projectIds) || !is_array($projectIds)) {
            $this->setFlash('error', $this->mlSupport->translate('Please select projects to delete.'));
            return $this->redirect('/admin/projects');
        }

        $deletedCount = 0;
        foreach ($projectIds as $projectId) {
            $projectId = intval($projectId);

            $stmt = $this->db->prepare("DELETE FROM projects WHERE project_id = :id");
            if ($stmt->execute(['id' => $projectId])) {
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Bulk Project Deletion', "Deleted $deletedCount projects");
            $this->setFlash('success', $this->mlSupport->translate("$deletedCount projects deleted successfully."));
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to delete projects.'));
        }

        return $this->redirect('/admin/projects');
    }

    /**
     * Toggle featured status for multiple projects
     */
    public function bulkToggleFeatured()
    {
        if ($this->request->getMethod() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('/admin/projects');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->redirect('/admin/projects');
        }

        $data = $this->request->all();
        $projectIds = $data['project_ids'] ?? [];
        $featured = isset($data['featured']) ? 1 : 0;

        if (empty($projectIds) || !is_array($projectIds)) {
            $this->setFlash('error', $this->mlSupport->translate('Please select projects to update.'));
            return $this->redirect('/admin/projects');
        }

        $updatedCount = 0;
        foreach ($projectIds as $projectId) {
            $projectId = intval($projectId);
            $updateData = ['is_featured' => $featured];

            if ($this->projectModel->updateProject($projectId, $updateData)) {
                $updatedCount++;
            }
        }

        $action = $featured ? 'featured' : 'unfeatured';
        if ($updatedCount > 0) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Bulk Project Featured Toggle', "$updatedCount projects $action");
            $this->setFlash('success', $this->mlSupport->translate("$updatedCount projects $action successfully."));
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to update projects.'));
        }

        return $this->redirect('/admin/projects');
    }

    /**
     * Export projects data
     */
    public function export()
    {
        $projects = $this->projectModel->getAllActiveProjects();

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=projects_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'ID', 'Project Name', 'Project Code', 'Location', 'City', 'State',
            'Total Area', 'Total Plots', 'Available Plots', 'Base Price',
            'Status', 'Is Featured', 'Is Active', 'Created Date'
        ]);

        // CSV data
        foreach ($projects as $project) {
            fputcsv($output, [
                $project['project_id'] ?? $project['id'] ?? '',
                $project['project_name'] ?? '',
                $project['project_code'] ?? '',
                $project['location'] ?? '',
                $project['city'] ?? '',
                $project['state'] ?? '',
                $project['total_area'] ?? '',
                $project['total_plots'] ?? '',
                $project['available_plots'] ?? '',
                $project['base_price'] ?? '',
                $project['project_status'] ?? '',
                ($project['is_featured'] ?? 0) ? 'Yes' : 'No',
                ($project['is_active'] ?? 0) ? 'Yes' : 'No',
                $project['created_at'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }
}
