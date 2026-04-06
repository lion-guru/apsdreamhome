<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Lead Controller - Custom MVC Implementation
 * Handles lead management operations in the Admin panel
 */
class LeadController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'addNote', 'updateStatus']]);
    }

    /**
     * Display leads list
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $source = $_GET['source'] ?? '';
            $assignedTo = $_GET['assigned_to'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT l.*, 
                           u.name as assigned_to_name,
                           ls.status_name,
                           COALESCE(ls.status_description, 'new') as status_color,
                           src.name as source_name,
                           COUNT(la.id) as activity_count
                    FROM leads l
                    LEFT JOIN users u ON l.assigned_to = u.id
                    LEFT JOIN lead_statuses ls ON l.status = ls.status_name COLLATE utf8mb4_general_ci
                    LEFT JOIN lead_sources src ON l.source_id = src.id
                    LEFT JOIN lead_activities la ON l.id = la.lead_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ? OR l.company LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND l.status = ?";
                $params[] = $status;
            }

            if (!empty($source)) {
                $sql .= " AND l.source_id = ?";
                $params[] = $source;
            }

            if (!empty($assignedTo)) {
                $sql .= " AND l.assigned_to = ?";
                $params[] = $assignedTo;
            }

            $sql .= " GROUP BY l.id ORDER BY l.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT l.*, u.name as assigned_to_name, ls.status_name, ls.color as status_color, src.name as source_name, COUNT(la.id) as activity_count", "SELECT COUNT(DISTINCT l.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $leads = $stmt->fetchAll();

            // Get filter options
            $statuses = $this->db->fetchAll("SELECT * FROM lead_statuses ORDER BY status_name");
            $sources = $this->db->fetchAll("SELECT * FROM lead_sources ORDER BY name");
            $assignees = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('associate', 'manager') ORDER BY name");

            $data = [
                'page_title' => 'Leads - APS Dream Home',
                'active_page' => 'leads',
                'leads' => $leads,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'source' => $source,
                    'assigned_to' => $assignedTo
                ],
                'filter_options' => [
                    'statuses' => $statuses,
                    'sources' => $sources,
                    'assignees' => $assignees
                ]
            ];

            return $this->render('admin/leads/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Lead Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load leads');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new lead
     */
    public function create()
    {
        try {
            // Get dropdown options
            $statuses = $this->db->fetchAll("SELECT * FROM lead_statuses ORDER BY status_name");
            $sources = $this->db->fetchAll("SELECT * FROM lead_sources ORDER BY name");
            $assignees = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('associate', 'manager') ORDER BY name");

            $data = [
                'page_title' => 'Create Lead - APS Dream Home',
                'active_page' => 'leads',
                'statuses' => $statuses,
                'sources' => $sources,
                'assignees' => $assignees
            ];

            return $this->render('admin/leads/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Lead Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load lead form');
            return $this->redirect('admin/leads');
        }
    }

    /**
     * Store a newly created lead
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['name', 'email', 'phone'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst($field) . ' is required', 400);
                }
            }

            // Validate email
            $email = CoreFunctionsServiceCustom::validateInput($data['email'], 'email');
            if (!$email) {
                return $this->jsonError('Invalid email address', 400);
            }

            // Check if email already exists
            $sql = "SELECT id FROM leads WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return $this->jsonError('Email already exists', 400);
            }

            // Validate phone
            $phone = CoreFunctionsServiceCustom::validateInput($data['phone'], 'phone');
            if ($phone === false) {
                return $this->jsonError('Invalid phone number', 400);
            }

            // Get default status
            $defaultStatus = $this->db->fetchOne("SELECT id FROM lead_statuses WHERE is_default = 1 LIMIT 1");
            $statusId = $defaultStatus['id'] ?? 1;

            // Insert lead
            $sql = "INSERT INTO leads 
                    (name, email, phone, company, position, source_id, status, assigned_to, 
                     budget, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['name'], 'string'),
                $email,
                $phone,
                CoreFunctionsServiceCustom::validateInput($data['company'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['position'] ?? '', 'string'),
                (int)($data['source_id'] ?? 0),
                (int)($data['status'] ?? $statusId),
                (int)($data['assigned_to'] ?? 0),
                (float)($data['budget'] ?? 0),
                CoreFunctionsServiceCustom::validateInput($data['notes'] ?? '', 'string')
            ]);

            if ($result) {
                $leadId = $this->db->lastInsertId();

                // Create initial activity
                $sql = "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                        VALUES (?, 'created', 'Lead created', ?, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$leadId, $_SESSION['user_id'] ?? 0]);

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'lead_created', [
                    'lead_id' => $leadId,
                    'email' => $email
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Lead created successfully',
                    'lead_id' => $leadId
                ]);
            }

            return $this->jsonError('Failed to create lead', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Lead Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create lead', 500);
        }
    }

    /**
     * Display the specified lead
     */
    public function show($id)
    {
        try {
            $leadId = intval($id);
            if ($leadId <= 0) {
                $this->setFlash('error', 'Invalid lead ID');
                return $this->redirect('admin/leads');
            }

            // Get lead details
            $sql = "SELECT l.*, 
                           u.name as assigned_to_name,
                           u.email as assigned_to_email,
                           ls.status_name,
                           COALESCE(ls.status_description, 'new') as status_color,
                           src.name as source_name
                    FROM leads l
                    LEFT JOIN users u ON l.assigned_to = u.id
                    LEFT JOIN lead_statuses ls ON l.status = ls.status_name COLLATE utf8mb4_general_ci
                    LEFT JOIN lead_sources src ON l.source_id = src.id
                    WHERE l.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch();

            if (!$lead) {
                $this->setFlash('error', 'Lead not found');
                return $this->redirect('admin/leads');
            }

            // Get lead activities
            $sql = "SELECT la.*, u.name as created_by_name
                    FROM lead_activities la
                    LEFT JOIN users u ON la.created_by = u.id
                    WHERE la.lead_id = ?
                    ORDER BY la.created_at DESC
                    LIMIT 20";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $activities = $stmt->fetchAll();

            // Get lead notes
            $sql = "SELECT ln.*, u.name as created_by_name
                    FROM lead_notes ln
                    LEFT JOIN users u ON ln.created_by = u.id
                    WHERE ln.lead_id = ?
                    ORDER BY ln.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $notes = $stmt->fetchAll();

            $data = [
                'page_title' => 'Lead Details - APS Dream Home',
                'active_page' => 'leads',
                'lead' => $lead,
                'activities' => $activities,
                'notes' => $notes
            ];

            return $this->render('admin/leads/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Lead Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load lead details');
            return $this->redirect('admin/leads');
        }
    }

    /**
     * Show the form for editing the specified lead
     */
    public function edit($id)
    {
        try {
            $leadId = intval($id);
            if ($leadId <= 0) {
                $this->setFlash('error', 'Invalid lead ID');
                return $this->redirect('admin/leads');
            }

            // Get lead details
            $sql = "SELECT * FROM leads WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch();

            if (!$lead) {
                $this->setFlash('error', 'Lead not found');
                return $this->redirect('admin/leads');
            }

            // Get dropdown options
            $statuses = $this->db->fetchAll("SELECT * FROM lead_statuses ORDER BY status_name");
            $sources = $this->db->fetchAll("SELECT * FROM lead_sources ORDER BY name");
            $assignees = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('associate', 'manager') ORDER BY name");

            $data = [
                'page_title' => 'Edit Lead - APS Dream Home',
                'active_page' => 'leads',
                'lead' => $lead,
                'statuses' => $statuses,
                'sources' => $sources,
                'assignees' => $assignees
            ];

            return $this->render('admin/leads/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Lead Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load lead form');
            return $this->redirect('admin/leads');
        }
    }

    /**
     * Update the specified lead
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $leadId = intval($id);
            if ($leadId <= 0) {
                return $this->jsonError('Invalid lead ID', 400);
            }

            $data = $_POST;

            // Check if lead exists
            $sql = "SELECT * FROM leads WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch();

            if (!$lead) {
                return $this->jsonError('Lead not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['name'])) {
                $updateFields[] = "name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['name'], 'string');
            }

            if (!empty($data['email'])) {
                $email = CoreFunctionsServiceCustom::validateInput($data['email'], 'email');
                if (!$email) {
                    return $this->jsonError('Invalid email address', 400);
                }

                // Check if email already exists for another lead
                $sql = "SELECT id FROM leads WHERE email = ? AND id != ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email, $leadId]);
                if ($stmt->fetch()) {
                    return $this->jsonError('Email already exists', 400);
                }

                $updateFields[] = "email = ?";
                $updateValues[] = $email;
            }

            if (!empty($data['phone'])) {
                $phone = CoreFunctionsServiceCustom::validateInput($data['phone'], 'phone');
                if ($phone === false) {
                    return $this->jsonError('Invalid phone number', 400);
                }
                $updateFields[] = "phone = ?";
                $updateValues[] = $phone;
            }

            if (isset($data['company'])) {
                $updateFields[] = "company = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['company'], 'string');
            }

            if (isset($data['position'])) {
                $updateFields[] = "position = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['position'], 'string');
            }

            if (isset($data['source_id'])) {
                $updateFields[] = "source_id = ?";
                $updateValues[] = (int)$data['source_id'];
            }

            if (isset($data['status'])) {
                $updateFields[] = "status = ?";
                $updateValues[] = (int)$data['status'];
            }

            if (isset($data['assigned_to'])) {
                $updateFields[] = "assigned_to = ?";
                $updateValues[] = (int)$data['assigned_to'];
            }

            if (isset($data['budget'])) {
                $updateFields[] = "budget = ?";
                $updateValues[] = (float)$data['budget'];
            }

            if (isset($data['notes'])) {
                $updateFields[] = "notes = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['notes'], 'string');
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $leadId;

            $sql = "UPDATE leads SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'lead_updated', [
                    'lead_id' => $leadId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Lead updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update lead', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Lead Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update lead', 500);
        }
    }

    /**
     * Remove the specified lead
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $leadId = intval($id);
            if ($leadId <= 0) {
                return $this->jsonError('Invalid lead ID', 400);
            }

            // Check if lead exists
            $sql = "SELECT * FROM leads WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch();

            if (!$lead) {
                return $this->jsonError('Lead not found', 404);
            }

            // Delete lead and related data
            $this->db->beginTransaction();

            try {
                // Delete lead activities
                $sql = "DELETE FROM lead_activities WHERE lead_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$leadId]);

                // Delete lead notes
                $sql = "DELETE FROM lead_notes WHERE lead_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$leadId]);

                // Delete lead
                $sql = "DELETE FROM leads WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$leadId]);

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'lead_deleted', [
                    'lead_id' => $leadId,
                    'lead_email' => $lead['email']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Lead deleted successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Lead Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete lead', 500);
        }
    }

    /**
     * Add note to lead
     */
    public function addNote($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $leadId = intval($id);
            if ($leadId <= 0) {
                return $this->jsonError('Invalid lead ID', 400);
            }

            $note = $_POST['note'] ?? '';
            if (empty($note)) {
                return $this->jsonError('Note content is required', 400);
            }

            // Check if lead exists
            $sql = "SELECT id FROM leads WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            if (!$stmt->fetch()) {
                return $this->jsonError('Lead not found', 404);
            }

            // Insert note
            $sql = "INSERT INTO lead_notes (lead_id, note, created_by, created_at)
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $leadId,
                CoreFunctionsServiceCustom::validateInput($note, 'string'),
                $_SESSION['user_id'] ?? 0
            ]);

            if ($result) {
                // Create activity
                $sql = "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                        VALUES (?, 'note_added', 'Note added', ?, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$leadId, $_SESSION['user_id'] ?? 0]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Note added successfully'
                ]);
            }

            return $this->jsonError('Failed to add note', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Add Note error: " . $e->getMessage());
            return $this->jsonError('Failed to add note', 500);
        }
    }

    /**
     * Update lead status
     */
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $leadId = intval($id);
            $statusId = (int)($_POST['status_id'] ?? 0);

            if ($leadId <= 0 || $statusId <= 0) {
                return $this->jsonError('Invalid parameters', 400);
            }

            // Check if lead exists and status is valid
            $sql = "SELECT l.*, ls.status_name
                    FROM leads l
                    LEFT JOIN lead_statuses ls ON l.status = ls.status_name COLLATE utf8mb4_general_ci
                    WHERE l.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch();

            if (!$lead) {
                return $this->jsonError('Lead not found', 404);
            }

            // Check if new status exists
            $sql = "SELECT name FROM lead_statuses WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$statusId]);
            $newStatus = $stmt->fetch();

            if (!$newStatus) {
                return $this->jsonError('Invalid status', 400);
            }

            // Update lead status
            $sql = "UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$statusId, $leadId]);

            if ($result) {
                // Create activity
                $sql = "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                        VALUES (?, 'status_changed', ?, ?, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $leadId,
                    "Status changed from '{$lead['status_name']}' to '{$newStatus['name']}'",
                    $_SESSION['user_id'] ?? 0
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Lead status updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update status', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Update Status error: " . $e->getMessage());
            return $this->jsonError('Failed to update status', 500);
        }
    }

    /**
     * Get lead documents/files
     */
    public function getDocuments($leadId)
    {
        try {
            $sql = "SELECT * FROM lead_files WHERE lead_id = ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$leadId]);
            $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'documents' => $documents
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Documents error: " . $e->getMessage());
            return $this->jsonError('Failed to get documents', 500);
        }
    }

    /**
     * Upload document for lead
     */
    public function uploadDocument($leadId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $leadId = intval($leadId);

            if ($leadId <= 0 || !isset($_FILES['document'])) {
                return $this->jsonError('Invalid parameters', 400);
            }

            $file = $_FILES['document'];
            $docType = $_POST['document_type'] ?? 'other';
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes)) {
                return $this->jsonError('Invalid file type. Only PDF, JPG, PNG allowed', 400);
            }

            if ($file['size'] > $maxSize) {
                return $this->jsonError('File too large. Max 5MB', 400);
            }

            // Create upload directory
            $uploadDir = 'storage/app/lead_files/' . $leadId;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid() . '_' . basename($file['name']);
            $filePath = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Save to database
                $sql = "INSERT INTO lead_files (lead_id, file_name, file_path, file_type, document_type, file_size, uploaded_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $leadId,
                    $file['name'],
                    $filePath,
                    $file['type'],
                    $docType,
                    $file['size'],
                    $_SESSION['user_id'] ?? 0
                ]);

                // Create activity
                $sql = "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                        VALUES (?, 'document_added', 'Document uploaded: ' . ?, ?, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$leadId, $file['name'], $_SESSION['user_id'] ?? 0]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Document uploaded successfully'
                ]);
            }

            return $this->jsonError('Failed to upload file', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Upload Document error: " . $e->getMessage());
            return $this->jsonError('Failed to upload document', 500);
        }
    }

    /**
     * Delete lead document
     */
    public function deleteDocument($documentId)
    {
        try {
            $sql = "SELECT * FROM lead_files WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$documentId]);
            $document = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$document) {
                return $this->jsonError('Document not found', 404);
            }

            // Delete file
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }

            // Delete from database
            $sql = "DELETE FROM lead_files WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$documentId]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Delete Document error: " . $e->getMessage());
            return $this->jsonError('Failed to delete document', 500);
        }
    }

    /**
     * Get lead statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total leads
            $sql = "SELECT COUNT(*) as total FROM leads";
            $result = $this->db->fetchOne($sql);
            $stats['total_leads'] = (int)($result['total'] ?? 0);

            // Leads by status
            $sql = "SELECT ls.status_name, COALESCE(ls.status_description, 'new') as color, COUNT(l.id) as count
                    FROM lead_statuses ls
                    LEFT JOIN leads l ON ls.status_name = l.status COLLATE utf8mb4_general_ci
                    GROUP BY ls.id, ls.status_name, ls.status_description
                    ORDER BY ls.status_name";
            $stats['by_status'] = $this->db->fetchAll($sql) ?: [];

            // Leads by source
            $sql = "SELECT src.name, COUNT(l.id) as count
                    FROM lead_sources src
                    LEFT JOIN leads l ON src.id = l.source_id
                    GROUP BY src.id, src.name
                    ORDER BY count DESC
                    LIMIT 10";
            $stats['by_source'] = $this->db->fetchAll($sql) ?: [];

            // This month's leads
            $sql = "SELECT COUNT(*) as total FROM leads 
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_leads'] = (int)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Lead Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch lead stats'
            ], 500);
        }
    }
}
