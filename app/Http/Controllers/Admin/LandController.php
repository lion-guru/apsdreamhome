<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Land Controller - Custom MVC Implementation
 * Handles land management operations in the Admin panel
 */
class LandController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'storeTransaction']]);
    }

    /**
     * Display land records
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT l.*, 
                           COUNT(p.id) as property_count,
                           COALESCE(SUM(COALESCE(p.area_sqft, p.area)), 0) as total_area
                    FROM land_records l
                    LEFT JOIN properties p ON l.id = p.land_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (l.land_title LIKE ? OR l.location LIKE ? OR l.owner_name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND l.status = ?";
                $params[] = $status;
            }

            $sql .= " GROUP BY l.id ORDER BY l.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT l.*, COUNT(p.id) as property_count, COALESCE(SUM(p.total_area), 0) as total_area", "SELECT COUNT(DISTINCT l.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $landRecords = $stmt->fetchAll();

            $data = [
                'page_title' => 'Land Records - APS Dream Home',
                'active_page' => 'land',
                'land_records' => $landRecords,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ]
            ];

            return $this->render('admin/land/index', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load land records');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new land record
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Add New Land Record - APS Dream Home',
                'active_page' => 'land'
            ];

            return $this->render('admin/land/create', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load land form');
            return $this->redirect('admin/land');
        }
    }

    /**
     * Store a newly created land record
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['land_title', 'location', 'owner_name', 'total_area', 'land_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate area
            $totalArea = (float)$data['total_area'];
            if ($totalArea <= 0) {
                return $this->jsonError('Total area must be greater than 0', 400);
            }

            // Validate coordinates if provided
            $latitude = null;
            $longitude = null;
            if (!empty($data['latitude']) && !empty($data['longitude'])) {
                $latitude = (float)$data['latitude'];
                $longitude = (float)$data['longitude'];

                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    return $this->jsonError('Invalid coordinates', 400);
                }
            }

            // Insert land record
            $tid = $this->tenantId();
            $sql = "INSERT INTO land_records
                    (land_title, location, owner_name, area, created_at, tenant_id)
                    VALUES (?, ?, ?, ?, NOW(), ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['land_title'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['location'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['owner_name'], 'string'),
                $totalArea,
                $tid
            ]);

            if ($result) {
                $landId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'land_record_created', [
                    'land_id' => $landId,
                    'land_title' => $data['land_title']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Land record created successfully',
                    'land_id' => $landId
                ]);
            }

            return $this->jsonError('Failed to create land record', 500);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create land record', 500);
        }
    }

    /**
     * Display the specified land record
     */
    public function show($id)
    {
        try {
            $landId = intval($id);
            if ($landId <= 0) {
                $this->setFlash('error', 'Invalid land record ID');
                return $this->redirect('admin/land');
            }

            // Get land record details
            $sql = "SELECT l.*, 
                           COUNT(p.id) as property_count,
                           COALESCE(SUM(p.total_area), 0) as developed_area
                    FROM land_records l
                    LEFT JOIN properties p ON l.id = p.land_id
                    WHERE l.id = ?
                    GROUP BY l.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $landRecord = $stmt->fetch();

            if (!$landRecord) {
                $this->setFlash('error', 'Land record not found');
                return $this->redirect('admin/land');
            }

            // Get properties on this land
            $sql = "SELECT p.*, 
                           b.booking_number,
                           c.name as customer_name
                    FROM properties p
                    LEFT JOIN bookings b ON p.id = b.property_id
                    LEFT JOIN users c ON b.customer_id = c.id
                    WHERE p.land_id = ?
                    ORDER BY p.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $properties = $stmt->fetchAll();

            // Get land transactions
            $sql = "SELECT * FROM land_transactions 
                    WHERE land_id = ?
                    ORDER BY transaction_date DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $transactions = $stmt->fetchAll();

            $data = [
                'page_title' => 'Land Record Details - APS Dream Home',
                'active_page' => 'land',
                'land_record' => $landRecord,
                'properties' => $properties,
                'transactions' => $transactions
            ];

            return $this->render('admin/land/show', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load land record details');
            return $this->redirect('admin/land');
        }
    }

    /**
     * Show the form for editing the specified land record
     */
    public function edit($id)
    {
        try {
            $landId = intval($id);
            if ($landId <= 0) {
                $this->setFlash('error', 'Invalid land record ID');
                return $this->redirect('admin/land');
            }

            // Get land record details
            $sql = "SELECT * FROM land_records WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $landRecord = $stmt->fetch();

            if (!$landRecord) {
                $this->setFlash('error', 'Land record not found');
                return $this->redirect('admin/land');
            }

            $data = [
                'page_title' => 'Edit Land Record - APS Dream Home',
                'active_page' => 'land',
                'land_record' => $landRecord
            ];

            return $this->render('admin/land/edit', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load land form');
            return $this->redirect('admin/land');
        }
    }

    /**
     * Update the specified land record
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $landId = intval($id);
            if ($landId <= 0) {
                return $this->jsonError('Invalid land record ID', 400);
            }

            $data = $_POST;

            // Check if land record exists
            $sql = "SELECT id FROM land_records WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            if (!$stmt->fetch()) {
                return $this->jsonError('Land record not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['land_title'])) {
                $updateFields[] = "land_title = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['land_title'], 'string');
            }

            if (!empty($data['location'])) {
                $updateFields[] = "location = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['location'], 'string');
            }

            if (!empty($data['owner_name'])) {
                $updateFields[] = "owner_name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['owner_name'], 'string');
            }

            if (!empty($data['total_area'])) {
                $totalArea = (float)$data['total_area'];
                if ($totalArea <= 0) {
                    return $this->jsonError('Total area must be greater than 0', 400);
                }
                $updateFields[] = "total_area = ?";
                $updateValues[] = $totalArea;
            }

            if (!empty($data['land_type'])) {
                $updateFields[] = "land_type = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['land_type'], 'string');
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            // Validate coordinates if provided
            if (!empty($data['latitude']) && !empty($data['longitude'])) {
                $latitude = (float)$data['latitude'];
                $longitude = (float)$data['longitude'];

                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    return $this->jsonError('Invalid coordinates', 400);
                }

                $updateFields[] = "latitude = ?";
                $updateValues[] = $latitude;
                $updateFields[] = "longitude = ?";
                $updateValues[] = $longitude;
            }

            if (isset($data['status'])) {
                $validStatuses = ['available', 'under_development', 'fully_developed', 'reserved'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $landId;

            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $sql = "UPDATE land_records SET " . implode(', ', $updateFields) . " WHERE id = ? $tenantSql";
            $updateValues = array_merge($updateValues, $tenantParams);
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'land_record_updated', [
                    'land_id' => $landId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Land record updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update land record', 500);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update land record', 500);
        }
    }

    /**
     * Remove the specified land record
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $landId = intval($id);
            if ($landId <= 0) {
                return $this->jsonError('Invalid land record ID', 400);
            }

            // Check if land record exists
            $sql = "SELECT * FROM land_records WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $landRecord = $stmt->fetch();

            if (!$landRecord) {
                return $this->jsonError('Land record not found', 404);
            }

            // Check if land has properties
            $sql = "SELECT COUNT(*) as property_count FROM properties WHERE land_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $propertyCount = $stmt->fetch()['property_count'];

            if ($propertyCount > 0) {
                return $this->jsonError('Cannot delete land record with existing properties', 400);
            }

            // Delete land record
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $sql = "DELETE FROM land_records WHERE id = ? $tenantSql";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(array_merge([$landId], $tenantParams));

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'land_record_deleted', [
                    'land_id' => $landId,
                    'land_title' => $landRecord['land_title']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Land record deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete land record', 500);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete land record', 500);
        }
    }

    /**
     * Store land transaction
     */
    public function storeTransaction($landId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $landId = intval($landId);
            if ($landId <= 0) {
                return $this->jsonError('Invalid land record ID', 400);
            }

            $data = $_POST;

            // Validate required fields
            $required = ['transaction_type', 'amount', 'transaction_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            $amount = (float)$data['amount'];
            if ($amount <= 0) {
                return $this->jsonError('Amount must be greater than 0', 400);
            }

            // Validate transaction type
            $validTypes = ['purchase', 'sale', 'development_cost', 'maintenance', 'other'];
            if (!in_array($data['transaction_type'], $validTypes)) {
                return $this->jsonError('Invalid transaction type', 400);
            }

            // Insert transaction
            $tid = $this->tenantId();
            $sql = "INSERT INTO land_transactions 
                    (land_id, transaction_type, amount, description, transaction_date, created_at, tenant_id)
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $landId,
                $data['transaction_type'],
                $amount,
                CoreFunctionsServiceCustom::validateInput($data['description'] ?? '', 'string'),
                $data['transaction_date'],
                $tid
            ]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'land_transaction_created', [
                    'land_id' => $landId,
                    'transaction_type' => $data['transaction_type'],
                    'amount' => $amount
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Land transaction recorded successfully'
                ]);
            }

            return $this->jsonError('Failed to record land transaction', 500);
        } catch (\Exception $e) {
            $this->loggingService->error("Store Land Transaction error: " . $e->getMessage());
            return $this->jsonError('Failed to record land transaction', 500);
        }
    }

    /**
     * Display land acquisitions
     */
    public function acquisitions()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $land_type = $_GET['land_type'] ?? '';
            $sql = "SELECT * FROM land_acquisitions WHERE 1=1";
            $params = [];
            if (!empty($status)) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $acquisitions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $totalAcq = count($acquisitions);
            $totalArea = 0;
            $totalCost = 0;
            foreach ($acquisitions as $a) {
                $totalArea += (float)($a['land_area'] ?? 0);
                $totalCost += (float)($a['acquisition_cost'] ?? 0);
            }
        } catch (\Exception $e) {
            $this->loggingService->error("Land Acquisitions error: " . $e->getMessage());
            $acquisitions = [];
            $totalAcq = 0;
            $totalArea = 0;
            $totalCost = 0;
        }
        return $this->render('admin/land/acquisitions', [
            'page_title' => 'Land Acquisitions',
            'acquisitions' => $acquisitions,
            'total_acquisitions' => $totalAcq,
            'total_area' => $totalArea,
            'total_cost' => $totalCost,
            'filters' => ['status' => $_GET['status'] ?? '', 'land_type' => $_GET['land_type'] ?? '']
        ]);
    }

    /**
     * Display acquisition detail
     */
    public function showAcquisition($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT a.* FROM land_acquisitions a WHERE a.id = ?");
            $stmt->execute([(int)$id]);
            $acquisition = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $acquisition = null;
        }
        if (!$acquisition) {
            $this->setFlash('error', 'Acquisition not found');
            $this->redirect('/admin/land/acquisitions');
        }
        return $this->render('admin/land/acquisition-show', [
            'page_title' => 'Acquisition: ' . ($acquisition['acquisition_number'] ?? ''),
            'acquisition' => $acquisition
        ]);
    }

    /**
     * Store a new acquisition
     */
    public function storeAcquisition()
    {
        $this->requireAdmin();
        $acquisition_number = $_POST['acquisition_number'] ?? ('ACQ' . date('Ymd') . rand(100, 999));
        $farmer_id = !empty($_POST['farmer_id']) ? (int)$_POST['farmer_id'] : null;
        $land_area = (float)($_POST['land_area'] ?? 0);
        $land_area_unit = $_POST['land_area_unit'] ?? 'sqft';
        $location = $_POST['location'] ?? '';
        $village = $_POST['village'] ?? '';
        $tehsil = $_POST['tehsil'] ?? '';
        $district = $_POST['district'] ?? '';
        $state = $_POST['state'] ?? '';
        $acquisition_date = $_POST['acquisition_date'] ?? date('Y-m-d');
        $acquisition_cost = (float)($_POST['acquisition_cost'] ?? 0);
        $payment_status = $_POST['payment_status'] ?? 'pending';
        $land_type = $_POST['land_type'] ?? '';
        $soil_type = $_POST['soil_type'] ?? '';
        $water_source = $_POST['water_source'] ?? '';
        $electricity_available = isset($_POST['electricity_available']) ? 1 : 0;
        $road_access = isset($_POST['road_access']) ? 1 : 0;
        $documents = $_POST['documents'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $created_by = $_SESSION['admin_id'] ?? ($_SESSION['user_id'] ?? null);
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO land_acquisitions (sale_agreement_number, total_area_sqft, sale_agreement_date, acquisition_cost, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$acquisition_number, $land_area, $acquisition_date, $acquisition_cost, $status]);
            $this->setFlash('success', 'Land acquisition recorded successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to record acquisition: ' . $e->getMessage());
        }
        $this->redirect('/admin/land/acquisitions');
    }

    /**
     * Display land records list
     */
    public function records()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("SELECT * FROM land_records ORDER BY created_at DESC");
            $landRecords = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->loggingService->error("Land Records error: " . $e->getMessage());
            $landRecords = [];
        }
        return $this->render('admin/land/records', [
            'page_title' => 'Land Records',
            'land_records' => $landRecords
        ]);
    }

    /**
     * Store a new land record
     */
    public function storeRecord()
    {
        $this->requireAdmin();
        $land_title = $_POST['land_title'] ?? '';
        $location = $_POST['location'] ?? '';
        $area = (float)($_POST['area'] ?? 0);
        $owner_name = $_POST['owner_name'] ?? '';
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO land_records (land_title, location, area, owner_name, created_at, tenant_id) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt->execute([$land_title, $location, $area, $owner_name, $tid]);
            $this->setFlash('success', 'Land record added successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add land record: ' . $e->getMessage());
        }
        $this->redirect('/admin/land/records');
    }

    /**
     * Get land statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total land records
            $sql = "SELECT COUNT(*) as total FROM land_records";
            $result = $this->db->fetchOne($sql);
            $stats['total_records'] = (int)($result['total'] ?? 0);

            // Total land area
            $sql = "SELECT COALESCE(SUM(area), 0) as total FROM land_records";
            $result = $this->db->fetchOne($sql);
            $stats['total_area'] = (float)($result['total'] ?? 0);

            // Land by owner
            $sql = "SELECT owner_name, COUNT(*) as count FROM land_records GROUP BY owner_name";
            $stats['by_owner'] = $this->db->fetchAll($sql) ?: [];

            // Land by location
            $sql = "SELECT location, COUNT(*) as count FROM land_records GROUP BY location";
            $stats['by_location'] = $this->db->fetchAll($sql) ?: [];

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Get Land Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch land stats'
            ], 500);
        }
    }
}
