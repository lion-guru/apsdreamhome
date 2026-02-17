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
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->redirect('/admin/projects/create');
        }

        $request = $this->request();
        $data = $request->post();

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

        $projectData['created_by'] = $request->session('user_id') ?? 1;
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
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->redirect("/admin/projects/edit/$id");
        }

        $request = $this->request();
        $data = $request->post();

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
}
