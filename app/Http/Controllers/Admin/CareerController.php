<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Career Controller - Custom MVC Implementation
 * Handles career management operations in Admin panel
 */
class CareerController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of careers
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $department = $_GET['department'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT c.*, 
                           COUNT(ca.id) as application_count
                    FROM careers c
                    LEFT JOIN career_applications ca ON c.id = ca.career_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND c.status = ?";
                $params[] = $status;
            }

            if (!empty($department)) {
                $sql .= " AND c.department = ?";
                $params[] = $department;
            }

            $sql .= " ORDER BY c.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT c.*, COUNT(ca.id) as application_count", "SELECT COUNT(DISTINCT c.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $careers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Careers - APS Dream Home',
                'active_page' => 'careers',
                'careers' => $careers,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'department' => $department
                ]
            ];

            return $this->render('admin/careers/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Career Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load careers');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new career
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Create Career - APS Dream Home',
                'active_page' => 'careers',
                'departments' => ['Sales', 'Marketing', 'Finance', 'HR', 'IT', 'Operations', 'Management'],
                'employment_types' => ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance']
            ];

            return $this->render('admin/careers/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Career Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load career form');
            return $this->redirect('admin/careers');
        }
    }

    /**
     * Store a newly created career
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['title', 'description', 'department', 'location', 'employment_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate employment type
            $validTypes = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'];
            if (!in_array($data['employment_type'], $validTypes)) {
                return $this->jsonError('Invalid employment type', 400);
            }

            // Insert career
            $sql = "INSERT INTO careers 
                    (title, description, department, location, employment_type, 
                     experience_level, salary_range, requirements, benefits, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['title'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['description'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['department'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['location'], 'string'),
                $data['employment_type'],
                CoreFunctionsServiceCustom::validateInput($data['experience_level'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['salary_range'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['requirements'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['benefits'] ?? '', 'string'),
                $data['status'] ?? 'active'
            ]);

            if ($result) {
                $careerId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'career_created', [
                    'career_id' => $careerId,
                    'title' => $data['title'],
                    'department' => $data['department']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Career created successfully',
                    'career_id' => $careerId
                ]);
            }

            return $this->jsonError('Failed to create career', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Career Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create career', 500);
        }
    }

    /**
     * Display the specified career
     */
    public function show($id)
    {
        try {
            $careerId = intval($id);
            if ($careerId <= 0) {
                $this->setFlash('error', 'Invalid career ID');
                return $this->redirect('admin/careers');
            }

            // Get career details
            $sql = "SELECT c.*, 
                           COUNT(ca.id) as application_count
                    FROM careers c
                    LEFT JOIN career_applications ca ON c.id = ca.career_id
                    WHERE c.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$careerId]);
            $career = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$career) {
                $this->setFlash('error', 'Career not found');
                return $this->redirect('admin/careers');
            }

            // Get recent applications
            $sql = "SELECT ca.*, u.name as applicant_name, u.email as applicant_email
                    FROM career_applications ca
                    LEFT JOIN users u ON ca.applicant_id = u.id
                    WHERE ca.career_id = ?
                    ORDER BY ca.created_at DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$careerId]);
            $recentApplications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Career Details - APS Dream Home',
                'active_page' => 'careers',
                'career' => $career,
                'recent_applications' => $recentApplications
            ];

            return $this->render('admin/careers/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Career Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load career details');
            return $this->redirect('admin/careers');
        }
    }

    /**
     * Show the form for editing the specified career
     */
    public function edit($id)
    {
        try {
            $careerId = intval($id);
            if ($careerId <= 0) {
                $this->setFlash('error', 'Invalid career ID');
                return $this->redirect('admin/careers');
            }

            // Get career details
            $sql = "SELECT * FROM careers WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$careerId]);
            $career = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$career) {
                $this->setFlash('error', 'Career not found');
                return $this->redirect('admin/careers');
            }

            $data = [
                'page_title' => 'Edit Career - APS Dream Home',
                'active_page' => 'careers',
                'career' => $career,
                'departments' => ['Sales', 'Marketing', 'Finance', 'HR', 'IT', 'Operations', 'Management'],
                'employment_types' => ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance']
            ];

            return $this->render('admin/careers/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Career Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load career form');
            return $this->redirect('admin/careers');
        }
    }

    /**
     * Update the specified career
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $careerId = intval($id);
            if ($careerId <= 0) {
                return $this->jsonError('Invalid career ID', 400);
            }

            $data = $_POST;

            // Check if career exists
            $sql = "SELECT * FROM careers WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$careerId]);
            $career = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$career) {
                return $this->jsonError('Career not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['title'], 'string');
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            if (isset($data['department'])) {
                $updateFields[] = "department = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['department'], 'string');
            }

            if (isset($data['location'])) {
                $updateFields[] = "location = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['location'], 'string');
            }

            if (isset($data['employment_type'])) {
                $validTypes = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'];
                if (in_array($data['employment_type'], $validTypes)) {
                    $updateFields[] = "employment_type = ?";
                    $updateValues[] = $data['employment_type'];
                }
            }

            if (isset($data['experience_level'])) {
                $updateFields[] = "experience_level = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['experience_level'], 'string');
            }

            if (isset($data['salary_range'])) {
                $updateFields[] = "salary_range = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['salary_range'], 'string');
            }

            if (isset($data['requirements'])) {
                $updateFields[] = "requirements = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['requirements'], 'string');
            }

            if (isset($data['benefits'])) {
                $updateFields[] = "benefits = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['benefits'], 'string');
            }

            if (isset($data['status'])) {
                $validStatuses = ['active', 'inactive', 'closed'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $careerId;

            $sql = "UPDATE careers SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'career_updated', [
                    'career_id' => $careerId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Career updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update career', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Career Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update career', 500);
        }
    }

    /**
     * Remove the specified career
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $careerId = intval($id);
            if ($careerId <= 0) {
                return $this->jsonError('Invalid career ID', 400);
            }

            // Check if career exists
            $sql = "SELECT * FROM careers WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$careerId]);
            $career = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$career) {
                return $this->jsonError('Career not found', 404);
            }

            // Delete career and related applications
            $this->db->beginTransaction();

            try {
                // Delete applications first
                $sql = "DELETE FROM career_applications WHERE career_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$careerId]);

                // Delete career
                $sql = "DELETE FROM careers WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$careerId]);

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'career_deleted', [
                    'career_id' => $careerId,
                    'title' => $career['title']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Career deleted successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Career Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete career', 500);
        }
    }

    /**
     * Get career statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total careers
            $sql = "SELECT COUNT(*) as total FROM careers";
            $result = $this->db->fetchOne($sql);
            $stats['total_careers'] = (int)($result['total'] ?? 0);

            // Careers by status
            $sql = "SELECT status, COUNT(*) as count FROM careers GROUP BY status";
            $result = $this->db->fetchAll($sql);
            $stats['by_status'] = $result ?: [];

            // Careers by department
            $sql = "SELECT department, COUNT(*) as count FROM careers GROUP BY department";
            $result = $this->db->fetchAll($sql);
            $stats['by_department'] = $result ?: [];

            // Total applications
            $sql = "SELECT COUNT(*) as total FROM career_applications";
            $result = $this->db->fetchOne($sql);
            $stats['total_applications'] = (int)($result['total'] ?? 0);

            // Applications this month
            $sql = "SELECT COUNT(*) as this_month FROM career_applications 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['applications_this_month'] = (int)($result['this_month'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Career Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}