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

            // Build query using real land_records columns
            $sql = "SELECT l.id, l.survey_number, l.land_area, l.land_type, l.location, l.owner_name, l.owner_contact, l.acquisition_status, l.acquisition_cost, l.colony_id, l.created_at
                    FROM land_records l
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (l.survey_number LIKE ? OR l.location LIKE ? OR l.owner_name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND l.acquisition_status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY l.created_at DESC";

            // Count total
            $countSql = "SELECT COUNT(DISTINCT l.id) as total FROM land_records l WHERE 1=1";
            if (!empty($search)) {
                $countSql .= " AND (l.survey_number LIKE ? OR l.location LIKE ? OR l.owner_name LIKE ?)";
            }
            if (!empty($status)) {
                $countSql .= " AND l.acquisition_status = ?";
            }
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

            // Validate required fields using real land_records columns
            $required = ['survey_number', 'location', 'owner_name', 'land_area', 'land_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate area
            $landArea = (float)$data['land_area'];
            if ($landArea <= 0) {
                return $this->jsonError('Land area must be greater than 0', 400);
            }

            // Validate acquisition_status if provided
            $acquisitionStatus = $data['acquisition_status'] ?? 'identified';
            $validStatuses = ['identified', 'negotiation', 'acquired', 'disputed'];
            if (!in_array($acquisitionStatus, $validStatuses)) {
                return $this->jsonError('Invalid acquisition status', 400);
            }

            // Validate acquisition_cost if provided
            $acquisitionCost = isset($data['acquisition_cost']) ? (float)$data['acquisition_cost'] : 0.00;

            // Validate colony_id if provided
            $colonyId = !empty($data['colony_id']) ? (int)$data['colony_id'] : null;

            // Insert land record using real columns
            $tid = $this->tenantId();
            $sql = "INSERT INTO land_records
                    (survey_number, land_area, land_type, location, owner_name, owner_contact, acquisition_status, acquisition_cost, colony_id, created_at, tenant_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['survey_number'], 'string'),
                $landArea,
                CoreFunctionsServiceCustom::validateInput($data['land_type'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['location'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['owner_name'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['owner_contact'] ?? '', 'string'),
                $acquisitionStatus,
                $acquisitionCost,
                $colonyId,
                $tid
            ]);

            if ($result) {
                $landId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'land_record_created', [
                    'land_id' => $landId,
                    'survey_number' => $data['survey_number']
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

            // Get land record details using real columns
            $sql = "SELECT l.id, l.survey_number, l.land_area, l.land_type, l.location, l.owner_name, l.owner_contact, l.acquisition_status, l.acquisition_cost, l.colony_id, l.created_at, l.updated_at
                    FROM land_records l
                    WHERE l.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $landRecord = $stmt->fetch();

            if (!$landRecord) {
                $this->setFlash('error', 'Land record not found');
                return $this->redirect('admin/land');
            }

            // Get plots on this land (legacy land_records has no FK to plots; use land_parcel_id best-effort)
            $sql = "SELECT p.* FROM plots p WHERE p.land_parcel_id = ? ORDER BY p.created_at DESC";
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

            // Build update query using real land_records columns
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['survey_number'])) {
                $updateFields[] = "survey_number = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['survey_number'], 'string');
            }

            if (!empty($data['location'])) {
                $updateFields[] = "location = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['location'], 'string');
            }

            if (!empty($data['owner_name'])) {
                $updateFields[] = "owner_name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['owner_name'], 'string');
            }

            if (!empty($data['owner_contact'])) {
                $updateFields[] = "owner_contact = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['owner_contact'], 'string');
            }

            if (!empty($data['land_area'])) {
                $landArea = (float)$data['land_area'];
                if ($landArea <= 0) {
                    return $this->jsonError('Land area must be greater than 0', 400);
                }
                $updateFields[] = "land_area = ?";
                $updateValues[] = $landArea;
            }

            if (!empty($data['land_type'])) {
                $updateFields[] = "land_type = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['land_type'], 'string');
            }

            if (!empty($data['acquisition_status'])) {
                $validStatuses = ['identified', 'negotiation', 'acquired', 'disputed'];
                if (in_array($data['acquisition_status'], $validStatuses)) {
                    $updateFields[] = "acquisition_status = ?";
                    $updateValues[] = $data['acquisition_status'];
                }
            }

            if (isset($data['acquisition_cost'])) {
                $acquisitionCost = (float)$data['acquisition_cost'];
                if ($acquisitionCost < 0) {
                    return $this->jsonError('Acquisition cost cannot be negative', 400);
                }
                $updateFields[] = "acquisition_cost = ?";
                $updateValues[] = $acquisitionCost;
            }

            if (!empty($data['colony_id'])) {
                $updateFields[] = "colony_id = ?";
                $updateValues[] = (int)$data['colony_id'];
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

            // Check if land record exists using real columns
            $sql = "SELECT id, survey_number FROM land_records WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $landRecord = $stmt->fetch();

            if (!$landRecord) {
                return $this->jsonError('Land record not found', 404);
            }

            // Check if land has plots (legacy check; land_records has no direct plot FK)
            $sql = "SELECT COUNT(*) as property_count FROM plots WHERE land_parcel_id = ?";
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
                    'survey_number' => $landRecord['survey_number']
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
            $sql = "SELECT a.* FROM land_acquisitions a WHERE 1=1";
            $params = [];
            if (!empty($status)) {
                $sql .= " AND a.status = ?";
                $params[] = $status;
            }
            // Note: land_type column doesn't exist in land_acquisitions, use mutation_status instead if needed
            $sql .= " ORDER BY a.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $acquisitions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $totalAcq = count($acquisitions);
            $totalArea = 0;
            $totalCost = 0;
            foreach ($acquisitions as $a) {
                $totalArea += (float)($a['total_area_sqft'] ?? 0);
                $totalCost += (float)($a['acquisition_cost'] ?? $a['total_consideration'] ?? 0);
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
        $land_lead_id = !empty($_POST['land_lead_id']) ? (int)$_POST['land_lead_id'] : null;
        $colony_id = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
        $total_area_sqft = (float)($_POST['total_area_sqft'] ?? 0);
        $acquired_area_sqft = (float)($_POST['acquired_area_sqft'] ?? 0);
        $acquisition_cost = (float)($_POST['acquisition_cost'] ?? 0);
        $total_consideration = (float)($_POST['total_consideration'] ?? 0);
        $advance_paid = (float)($_POST['advance_paid'] ?? 0);
        $balance_amount = (float)($_POST['balance_amount'] ?? 0);
        $sale_agreement_date = $_POST['sale_agreement_date'] ?? null;
        $sale_agreement_number = $_POST['sale_agreement_number'] ?? '';
        $registration_date = $_POST['registration_date'] ?? null;
        $registration_number = $_POST['registration_number'] ?? '';
        $sub_registrar_office = $_POST['sub_registrar_office'] ?? '';
        $stamp_duty_amount = (float)($_POST['stamp_duty_amount'] ?? 0);
        $registration_fee = (float)($_POST['registration_fee'] ?? 0);
        $mutation_status = $_POST['mutation_status'] ?? 'not_started';
        $mutation_number = $_POST['mutation_number'] ?? '';
        $mutation_date = $_POST['mutation_date'] ?? null;
        $status = $_POST['status'] ?? 'in_progress';
        try {
            $tid = $this->tenantId();
            $validStatuses = ['in_progress', 'registered', 'mutated', 'closed', 'cancelled'];
            $dealStatus = in_array($status, $validStatuses) ? $status : 'in_progress';
            $validMutationStatuses = ['not_started', 'applied', 'in_progress', 'completed', 'rejected'];
            $mutStatus = in_array($mutation_status, $validMutationStatuses) ? $mutation_status : 'not_started';

            $stmt = $this->db->prepare("INSERT INTO land_acquisitions (land_lead_id, colony_id, total_area_sqft, acquired_area_sqft, acquisition_cost, total_consideration, advance_paid, balance_amount, sale_agreement_date, sale_agreement_number, registration_date, registration_number, sub_registrar_office, stamp_duty_amount, registration_fee, mutation_status, mutation_number, mutation_date, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$land_lead_id, $colony_id, $total_area_sqft, $acquired_area_sqft, $acquisition_cost, $total_consideration, $advance_paid, $balance_amount, $sale_agreement_date, $sale_agreement_number, $registration_date, $registration_number, $sub_registrar_office, $stamp_duty_amount, $registration_fee, $mutStatus, $mutation_number, $mutation_date, $dealStatus, $tid]);
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
        $survey_number = $_POST['survey_number'] ?? '';
        $location = $_POST['location'] ?? '';
        $land_area = (float)($_POST['land_area'] ?? 0);
        $land_type = $_POST['land_type'] ?? '';
        $owner_name = $_POST['owner_name'] ?? '';
        $owner_contact = $_POST['owner_contact'] ?? '';
        $acquisition_status = $_POST['acquisition_status'] ?? 'identified';
        $acquisition_cost = (float)($_POST['acquisition_cost'] ?? 0);
        $colony_id = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO land_records (survey_number, land_area, land_type, location, owner_name, owner_contact, acquisition_status, acquisition_cost, colony_id, created_at, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            $stmt->execute([$survey_number, $land_area, $land_type, $location, $owner_name, $owner_contact, $acquisition_status, $acquisition_cost, $colony_id, $tid]);
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
            $sql = "SELECT COALESCE(SUM(land_area), 0) as total FROM land_records";
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
