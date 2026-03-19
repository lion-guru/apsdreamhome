<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Project Controller - Custom MVC Implementation
 * Handles project management operations in the Admin panel
 */
class ProjectController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * List all projects
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT p.*, 
                           COUNT(pr.id) as property_count,
                           COALESCE(SUM(pr.total_area), 0) as developed_area,
                           COALESCE(SUM(pr.price), 0) as total_value
                    FROM projects p
                    LEFT JOIN properties pr ON p.id = pr.project_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (p.project_name LIKE ? OR p.location LIKE ? OR p.description LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }

            if (!empty($type)) {
                $sql .= " AND p.project_type = ?";
                $params[] = $type;
            }

            $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT p.*, COUNT(pr.id) as property_count, COALESCE(SUM(pr.total_area), 0) as developed_area, COALESCE(SUM(pr.price), 0) as total_value", "SELECT COUNT(DISTINCT p.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $projects = $stmt->fetchAll();

            $data = [
                'page_title' => 'Project Management - APS Dream Home',
                'active_page' => 'projects',
                'projects' => $projects,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type
                ]
            ];

            return $this->render('admin/projects/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Project Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load projects');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Create Project - APS Dream Home',
                'active_page' => 'projects'
            ];

            return $this->render('admin/projects/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Project Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load project form');
            return $this->redirect('admin/projects');
        }
    }

    /**
     * Store a newly created project
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['project_name', 'location', 'project_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate numeric fields
            $totalArea = (float)($data['total_area'] ?? 0);
            $estimatedBudget = (float)($data['estimated_budget'] ?? 0);

            if ($totalArea <= 0) {
                return $this->jsonError('Total area must be greater than 0', 400);
            }

            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if (!$imageValidation['valid']) {
                    return $this->jsonError($imageValidation['error'], 400);
                }
                $imagePath = $this->uploadImage($_FILES['image']);
                if (!$imagePath) {
                    return $this->jsonError('Failed to upload image', 500);
                }
            }

            // Insert project
            $sql = "INSERT INTO projects 
                    (project_name, location, project_type, total_area, estimated_budget, 
                     description, image, start_date, end_date, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'planning', NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['project_name'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['location'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['project_type'], 'string'),
                $totalArea,
                $estimatedBudget,
                CoreFunctionsServiceCustom::validateInput($data['description'] ?? '', 'string'),
                $imagePath,
                $data['start_date'] ?? null,
                $data['end_date'] ?? null
            ]);

            if ($result) {
                $projectId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'project_created', [
                    'project_id' => $projectId,
                    'project_name' => $data['project_name']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Project created successfully',
                    'project_id' => $projectId
                ]);
            }

            // Clean up uploaded image if database insert failed
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }

            return $this->jsonError('Failed to create project', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Project Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create project', 500);
        }
    }

    /**
     * Display the specified project
     */
    public function show($id)
    {
        try {
            $projectId = intval($id);
            if ($projectId <= 0) {
                $this->setFlash('error', 'Invalid project ID');
                return $this->redirect('admin/projects');
            }

            // Get project details
            $sql = "SELECT p.*, 
                           COUNT(pr.id) as property_count,
                           COALESCE(SUM(pr.total_area), 0) as developed_area,
                           COALESCE(SUM(pr.price), 0) as total_value
                    FROM projects p
                    LEFT JOIN properties pr ON p.id = pr.project_id
                    WHERE p.id = ?
                    GROUP BY p.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();

            if (!$project) {
                $this->setFlash('error', 'Project not found');
                return $this->redirect('admin/projects');
            }

            // Get properties in this project
            $sql = "SELECT pr.*, 
                           b.booking_number,
                           c.name as customer_name
                    FROM properties pr
                    LEFT JOIN bookings b ON pr.id = b.property_id
                    LEFT JOIN users c ON b.customer_id = c.id
                    WHERE pr.project_id = ?
                    ORDER BY pr.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            $properties = $stmt->fetchAll();

            $data = [
                'page_title' => 'Project Details - APS Dream Home',
                'active_page' => 'projects',
                'project' => $project,
                'properties' => $properties
            ];

            return $this->render('admin/projects/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Project Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load project details');
            return $this->redirect('admin/projects');
        }
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit($id)
    {
        try {
            $projectId = intval($id);
            if ($projectId <= 0) {
                $this->setFlash('error', 'Invalid project ID');
                return $this->redirect('admin/projects');
            }

            // Get project details
            $sql = "SELECT * FROM projects WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();

            if (!$project) {
                $this->setFlash('error', 'Project not found');
                return $this->redirect('admin/projects');
            }

            $data = [
                'page_title' => 'Edit Project - APS Dream Home',
                'active_page' => 'projects',
                'project' => $project
            ];

            return $this->render('admin/projects/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Project Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load project form');
            return $this->redirect('admin/projects');
        }
    }

    /**
     * Update the specified project
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $projectId = intval($id);
            if ($projectId <= 0) {
                return $this->jsonError('Invalid project ID', 400);
            }

            $data = $_POST;

            // Check if project exists
            $sql = "SELECT * FROM projects WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();

            if (!$project) {
                return $this->jsonError('Project not found', 404);
            }

            // Handle image upload
            $imagePath = $project['image']; // Keep existing image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if (!$imageValidation['valid']) {
                    return $this->jsonError($imageValidation['error'], 400);
                }

                $newImagePath = $this->uploadImage($_FILES['image']);
                if (!$newImagePath) {
                    return $this->jsonError('Failed to upload image', 500);
                }

                // Delete old image if exists
                if ($project['image'] && file_exists($project['image'])) {
                    unlink($project['image']);
                }

                $imagePath = $newImagePath;
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['project_name'])) {
                $updateFields[] = "project_name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['project_name'], 'string');
            }

            if (!empty($data['location'])) {
                $updateFields[] = "location = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['location'], 'string');
            }

            if (!empty($data['project_type'])) {
                $updateFields[] = "project_type = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['project_type'], 'string');
            }

            if (isset($data['total_area'])) {
                $totalArea = (float)$data['total_area'];
                if ($totalArea <= 0) {
                    return $this->jsonError('Total area must be greater than 0', 400);
                }
                $updateFields[] = "total_area = ?";
                $updateValues[] = $totalArea;
            }

            if (isset($data['estimated_budget'])) {
                $updateFields[] = "estimated_budget = ?";
                $updateValues[] = (float)$data['estimated_budget'];
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            if (isset($data['start_date'])) {
                $updateFields[] = "start_date = ?";
                $updateValues[] = $data['start_date'];
            }

            if (isset($data['end_date'])) {
                $updateFields[] = "end_date = ?";
                $updateValues[] = $data['end_date'];
            }

            if (isset($data['status'])) {
                $validStatuses = ['planning', 'in_progress', 'completed', 'on_hold', 'cancelled'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            $updateFields[] = "image = ?";
            $updateValues[] = $imagePath;
            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $projectId;

            $sql = "UPDATE projects SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'project_updated', [
                    'project_id' => $projectId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Project updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update project', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Project Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update project', 500);
        }
    }

    /**
     * Remove the specified project
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $projectId = intval($id);
            if ($projectId <= 0) {
                return $this->jsonError('Invalid project ID', 400);
            }

            // Check if project exists
            $sql = "SELECT * FROM projects WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();

            if (!$project) {
                return $this->jsonError('Project not found', 404);
            }

            // Check if project has properties
            $sql = "SELECT COUNT(*) as property_count FROM properties WHERE project_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            $propertyCount = $stmt->fetch()['property_count'];

            if ($propertyCount > 0) {
                return $this->jsonError('Cannot delete project with existing properties', 400);
            }

            // Delete image if exists
            if ($project['image'] && file_exists($project['image'])) {
                unlink($project['image']);
            }

            // Delete project
            $sql = "DELETE FROM projects WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$projectId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'project_deleted', [
                    'project_id' => $projectId,
                    'project_name' => $project['project_name']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Project deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete project', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Project Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete project', 500);
        }
    }

    /**
     * Display project analytics
     */
    public function analytics()
    {
        try {
            $data = [
                'page_title' => 'Project Analytics - APS Dream Home',
                'active_page' => 'projects',
                'analytics_data' => $this->getProjectAnalytics()
            ];

            return $this->render('admin/projects/analytics', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Project Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load project analytics');
            return $this->redirect('admin/projects');
        }
    }

    /**
     * Validate uploaded image
     */
    private function validateImage(array $file): array
    {
        // Check file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Image size too large. Maximum 5MB allowed.'];
        }

        // Check image type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid image type. Allowed types: JPG, PNG, GIF, WebP'];
        }

        return ['valid' => true];
    }

    /**
     * Upload image
     */
    private function uploadImage(array $file): ?string
    {
        try {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('project_') . '.' . $extension;

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/projects/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                return $filePath;
            }

            return null;
        } catch (Exception $e) {
            $this->loggingService->error("Upload Image error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get project analytics
     */
    private function getProjectAnalytics(): array
    {
        try {
            $analytics = [];

            // Project status distribution
            $sql = "SELECT status, COUNT(*) as count FROM projects GROUP BY status";
            $analytics['status_distribution'] = $this->db->fetchAll($sql) ?: [];

            // Project type distribution
            $sql = "SELECT project_type, COUNT(*) as count FROM projects GROUP BY project_type";
            $analytics['type_distribution'] = $this->db->fetchAll($sql) ?: [];

            // Top projects by value
            $sql = "SELECT p.project_name, COUNT(pr.id) as property_count,
                           COALESCE(SUM(pr.price), 0) as total_value
                    FROM projects p
                    LEFT JOIN properties pr ON p.id = pr.project_id
                    GROUP BY p.id
                    ORDER BY total_value DESC
                    LIMIT 10";
            $analytics['top_projects'] = $this->db->fetchAll($sql) ?: [];

            // Project progress timeline
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                    FROM projects
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC";
            $analytics['timeline'] = $this->db->fetchAll($sql) ?: [];

            return $analytics;
        } catch (Exception $e) {
            $this->loggingService->error("Get Project Analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total projects
            $sql = "SELECT COUNT(*) as total FROM projects";
            $result = $this->db->fetchOne($sql);
            $stats['total_projects'] = (int)($result['total'] ?? 0);

            // Active projects
            $sql = "SELECT COUNT(*) as total FROM projects WHERE status IN ('planning', 'in_progress')";
            $result = $this->db->fetchOne($sql);
            $stats['active_projects'] = (int)($result['total'] ?? 0);

            // Completed projects
            $sql = "SELECT COUNT(*) as total FROM projects WHERE status = 'completed'";
            $result = $this->db->fetchOne($sql);
            $stats['completed_projects'] = (int)($result['total'] ?? 0);

            // Total area
            $sql = "SELECT COALESCE(SUM(total_area), 0) as total FROM projects";
            $result = $this->db->fetchOne($sql);
            $stats['total_area'] = (float)($result['total'] ?? 0);

            // Total budget
            $sql = "SELECT COALESCE(SUM(estimated_budget), 0) as total FROM projects";
            $result = $this->db->fetchOne($sql);
            $stats['total_budget'] = (float)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Project Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch project stats'
            ], 500);
        }
    }
}
