<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Visit Controller - Custom MVC Implementation
 * Handles visit management operations in Admin panel
 */
class VisitController extends AdminController
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
     * Display a listing of visits
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $propertyId = $_GET['property_id'] ?? '';
            $customerId = $_GET['customer_id'] ?? '';
            $associateId = $_GET['associate_id'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT v.*, 
                           p.title as property_title,
                           p.location as property_location,
                           c.name as customer_name,
                           c.email as customer_email,
                           a.name as associate_name,
                           a.email as associate_email
                    FROM visits v
                    LEFT JOIN properties p ON v.property_id = p.id
                    LEFT JOIN users c ON v.customer_id = c.id
                    LEFT JOIN users a ON v.associate_id = a.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (v.purpose LIKE ? OR v.notes LIKE ? OR c.name LIKE ? OR a.name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND v.status = ?";
                $params[] = $status;
            }

            if (!empty($propertyId)) {
                $sql .= " AND v.property_id = ?";
                $params[] = $propertyId;
            }

            if (!empty($customerId)) {
                $sql .= " AND v.customer_id = ?";
                $params[] = $customerId;
            }

            if (!empty($associateId)) {
                $sql .= " AND v.associate_id = ?";
                $params[] = $associateId;
            }

            $sql .= " ORDER BY v.visit_date DESC";

            // Count total
            $countSql = str_replace("SELECT v.*, p.title as property_title, p.location as property_location, c.name as customer_name, c.email as customer_email, a.name as associate_name, a.email as associate_email", "SELECT COUNT(DISTINCT v.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $visits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get dropdown options
            $properties = $this->db->query("SELECT id, title, location FROM properties ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
            $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $associates = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'associate', 'manager') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Visits - APS Dream Home',
                'active_page' => 'visits',
                'visits' => $visits,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'property_id' => $propertyId,
                    'customer_id' => $customerId,
                    'associate_id' => $associateId
                ],
                'properties' => $properties,
                'customers' => $customers,
                'associates' => $associates
            ];

            return $this->render('admin/visits/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Visit Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load visits');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new visit
     */
    public function create()
    {
        try {
            // Get dropdown options
            $properties = $this->db->query("SELECT id, title, location FROM properties ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
            $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $associates = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'associate', 'manager') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Schedule Visit - APS Dream Home',
                'active_page' => 'visits',
                'properties' => $properties,
                'customers' => $customers,
                'associates' => $associates
            ];

            return $this->render('admin/visits/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Visit Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load visit form');
            return $this->redirect('admin/visits');
        }
    }

    /**
     * Store a newly created visit
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['customer_id', 'property_id', 'visit_date', 'purpose'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate visit date
            $visitDate = $data['visit_date'];
            if (!empty($visitDate) && !strtotime($visitDate)) {
                return $this->jsonError('Invalid visit date format', 400);
            }

            // Validate time
            $visitTime = $data['visit_time'] ?? '10:00';
            if (!empty($visitTime) && !preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $visitTime)) {
                return $this->jsonError('Invalid visit time format', 400);
            }

            // Generate visit number
            $visitNumber = 'VIS' . date('YmdHis') . rand(1000, 9999);

            // Insert visit
            $sql = "INSERT INTO visits 
                    (visit_number, customer_id, property_id, associate_id, visit_date, 
                     visit_time, purpose, notes, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $visitNumber,
                (int)$data['customer_id'],
                (int)$data['property_id'],
                !empty($data['associate_id']) ? (int)$data['associate_id'] : null,
                $visitDate,
                $visitTime,
                CoreFunctionsServiceCustom::validateInput($data['purpose'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['notes'] ?? '', 'string'),
                $data['status'] ?? 'scheduled'
            ]);

            if ($result) {
                $visitId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'visit_created', [
                    'visit_id' => $visitId,
                    'visit_number' => $visitNumber,
                    'customer_id' => $data['customer_id'],
                    'property_id' => $data['property_id']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Visit scheduled successfully',
                    'visit_id' => $visitId,
                    'visit_number' => $visitNumber
                ]);
            }

            return $this->jsonError('Failed to schedule visit', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Visit Store error: " . $e->getMessage());
            return $this->jsonError('Failed to schedule visit', 500);
        }
    }

    /**
     * Display the specified visit
     */
    public function show($id)
    {
        try {
            $visitId = intval($id);
            if ($visitId <= 0) {
                $this->setFlash('error', 'Invalid visit ID');
                return $this->redirect('admin/visits');
            }

            // Get visit details
            $sql = "SELECT v.*, 
                           p.title as property_title,
                           p.location as property_location,
                           c.name as customer_name,
                           c.email as customer_email,
                           c.phone as customer_phone,
                           a.name as associate_name,
                           a.email as associate_email,
                           a.phone as associate_phone
                    FROM visits v
                    LEFT JOIN properties p ON v.property_id = p.id
                    LEFT JOIN users c ON v.customer_id = c.id
                    LEFT JOIN users a ON v.associate_id = a.id
                    WHERE v.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$visitId]);
            $visit = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$visit) {
                $this->setFlash('error', 'Visit not found');
                return $this->redirect('admin/visits');
            }

            $data = [
                'page_title' => 'Visit Details - APS Dream Home',
                'active_page' => 'visits',
                'visit' => $visit
            ];

            return $this->render('admin/visits/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Visit Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load visit details');
            return $this->redirect('admin/visits');
        }
    }

    /**
     * Show the form for editing the specified visit
     */
    public function edit($id)
    {
        try {
            $visitId = intval($id);
            if ($visitId <= 0) {
                $this->setFlash('error', 'Invalid visit ID');
                return $this->redirect('admin/visits');
            }

            // Get visit details
            $sql = "SELECT * FROM visits WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$visitId]);
            $visit = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$visit) {
                $this->setFlash('error', 'Visit not found');
                return $this->redirect('admin/visits');
            }

            // Get dropdown options
            $properties = $this->db->query("SELECT id, title, location FROM properties ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
            $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $associates = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'associate', 'manager') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Edit Visit - APS Dream Home',
                'active_page' => 'visits',
                'visit' => $visit,
                'properties' => $properties,
                'customers' => $customers,
                'associates' => $associates
            ];

            return $this->render('admin/visits/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Visit Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load visit form');
            return $this->redirect('admin/visits');
        }
    }

    /**
     * Update the specified visit
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $visitId = intval($id);
            if ($visitId <= 0) {
                return $this->jsonError('Invalid visit ID', 400);
            }

            $data = $_POST;

            // Check if visit exists
            $sql = "SELECT * FROM visits WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$visitId]);
            $visit = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$visit) {
                return $this->jsonError('Visit not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['customer_id'])) {
                $updateFields[] = "customer_id = ?";
                $updateValues[] = (int)$data['customer_id'];
            }

            if (isset($data['property_id'])) {
                $updateFields[] = "property_id = ?";
                $updateValues[] = (int)$data['property_id'];
            }

            if (isset($data['associate_id'])) {
                $updateFields[] = "associate_id = ?";
                $updateValues[] = !empty($data['associate_id']) ? (int)$data['associate_id'] : null;
            }

            if (isset($data['visit_date'])) {
                if (!empty($data['visit_date']) && strtotime($data['visit_date'])) {
                    $updateFields[] = "visit_date = ?";
                    $updateValues[] = $data['visit_date'];
                }
            }

            if (isset($data['visit_time'])) {
                if (!empty($data['visit_time']) && preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['visit_time'])) {
                    $updateFields[] = "visit_time = ?";
                    $updateValues[] = $data['visit_time'];
                }
            }

            if (isset($data['purpose'])) {
                $updateFields[] = "purpose = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['purpose'], 'string');
            }

            if (isset($data['notes'])) {
                $updateFields[] = "notes = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['notes'], 'string');
            }

            if (isset($data['status'])) {
                $validStatuses = ['scheduled', 'completed', 'cancelled', 'rescheduled'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $visitId;

            $sql = "UPDATE visits SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'visit_updated', [
                    'visit_id' => $visitId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Visit updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update visit', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Visit Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update visit', 500);
        }
    }

    /**
     * Remove the specified visit
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $visitId = intval($id);
            if ($visitId <= 0) {
                return $this->jsonError('Invalid visit ID', 400);
            }

            // Check if visit exists
            $sql = "SELECT * FROM visits WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$visitId]);
            $visit = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$visit) {
                return $this->jsonError('Visit not found', 404);
            }

            // Delete visit
            $sql = "DELETE FROM visits WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$visitId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'visit_deleted', [
                    'visit_id' => $visitId,
                    'visit_number' => $visit['visit_number']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Visit deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete visit', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Visit Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete visit', 500);
        }
    }

    /**
     * Get visit statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total visits
            $sql = "SELECT COUNT(*) as total FROM visits";
            $result = $this->db->fetchOne($sql);
            $stats['total_visits'] = (int)($result['total'] ?? 0);

            // Visits by status
            $sql = "SELECT status, COUNT(*) as count FROM visits GROUP BY status";
            $result = $this->db->fetchAll($sql);
            $stats['by_status'] = $result ?: [];

            // Visits this month
            $sql = "SELECT COUNT(*) as this_month FROM visits 
                    WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['this_month'] = (int)($result['this_month'] ?? 0);

            // Visits this week
            $sql = "SELECT COUNT(*) as this_week FROM visits 
                    WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['this_week'] = (int)($result['this_week'] ?? 0);

            // Upcoming visits
            $sql = "SELECT COUNT(*) as upcoming FROM visits 
                    WHERE visit_date > NOW() AND status = 'scheduled'";
            $result = $this->db->fetchOne($sql);
            $stats['upcoming'] = (int)($result['upcoming'] ?? 0);

            // Completed visits this month
            $sql = "SELECT COUNT(*) as completed_this_month FROM visits 
                    WHERE status = 'completed' AND visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['completed_this_month'] = (int)($result['completed_this_month'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Visit Stats error: " . $e->getMessage());
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