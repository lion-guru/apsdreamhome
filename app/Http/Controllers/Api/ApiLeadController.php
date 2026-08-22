<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadActivity;
use App\Models\LeadNote;
use App\Models\LeadFile;
use App\Models\LeadTag;
use App\Models\LeadStatus;
use App\Models\LeadSource;
use App\Models\User\User;
use App\Core\Security;
use UploadValidator;
use \App\Traits\TenantAwareTrait;

/**
 * API Lead Controller - Custom Framework Version
 * Handles all lead-related API endpoints
 */
class ApiLeadController extends BaseController
{
    use TenantAwareTrait;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * API endpoints are token-authenticated, so skip CSRF protection.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Get all leads with filtering and pagination
     */
    public function index()
    {
        try {
            // Get query parameters
            $perPage = $_GET['per_page'] ?? 25;
            $page = $_GET['page'] ?? 1;
            $search = $_GET['search'] ?? null;
            $status = $_GET['status'] ?? null;
            $source = $_GET['source'] ?? null;
            $assignedTo = $_GET['assigned_to'] ?? null;
            $tag = $_GET['tag'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            $sortField = $_GET['sort_field'] ?? 'created_at';
            $sortDirection = $_GET['sort_direction'] ?? 'desc';

            // Get current user (assuming auth middleware sets this)
            $currentUser = $this->getCurrentUser();

            // Build query using custom Model pattern
            $leads = $this->buildLeadQuery($search, $status, $source, $assignedTo, $tag, $dateFrom, $dateTo, $sortField, $sortDirection, $currentUser);

            // Apply pagination manually (since we don't have Laravel's paginate)
            $offset = ($page - 1) * $perPage;
            $total = count($leads);
            $paginatedLeads = array_slice($leads, $offset, $perPage);

            // Format response
            $response = [
                'success' => true,
                'data' => array_map(function ($lead) {
                    return $this->formatLeadData($lead);
                }, $paginatedLeads),
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total),
                ],
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch leads: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new lead
     */
    public function store()
    {
        try {
            $guard = \App\Services\CRMGuard::getInstance();
            if (!$guard->isCrmEnabled()) {
                $this->jsonError('CRM is currently disabled by administrator', 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (empty($data['first_name']) && empty($data['name']) && empty($data['email']) && empty($data['phone'])) {
                $this->jsonError("At least one of name, first_name, email or phone is required", 422);
            }

            // Get current user
            $currentUser = $this->getCurrentUser();
            $createdBy = $currentUser && isset($currentUser->id) ? $currentUser->id : ($currentUser['id'] ?? null);

            // Build name from first/last name (leads table has a single `name` column)
            $firstName = $data['first_name'] ?? '';
            $lastName = $data['last_name'] ?? '';
            $name = $data['name'] ?? trim($firstName . ' ' . $lastName);
            if ($name === '' && !empty($data['email'])) {
                $name = $data['email'];
            }

            // Create lead data using real `leads` columns
            $leadData = [
                'name' => $name,
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'company' => $data['company'] ?? '',
                'source' => $data['source'] ?? 'website',
                'status' => $data['status'] ?? 'new',
                'assigned_to' => $data['assigned_to'] ?? null,
                'created_by' => $createdBy,
                'property_interest' => $data['property_interest'] ?? null,
                'budget' => $data['budget'] ?? null,
                'location_preference' => $data['location_preference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'message' => $data['message'] ?? null,
            ];

            // Create lead using custom Model
            $lead = new Lead($leadData);
            $lead->save();

            // Handle tags if provided
            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->syncTags($lead, $data['tags']);
            }

            // Log activity
            $this->logLeadActivity($lead->id, 'lead_created', 'Lead created', [
                'assigned_to' => $lead->assigned_to,
                'status' => $lead->status,
            ]);

            $response = [
                'success' => true,
                'message' => 'Lead created successfully',
                'data' => $this->formatLeadData($lead),
            ];

            $this->jsonResponse($response, 201);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to create lead: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single lead by ID
     */
    public function show($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            // Check permissions
            $this->authorize($lead);

            $response = [
                'success' => true,
                'data' => $this->formatLeadData($lead, true), // Include full details
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch lead: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a lead
     */
    public function update($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            // Check permissions
            $this->authorize($lead);

            $data = json_decode(file_get_contents('php://input'), true);

            // Update lead fields using real `leads` columns
            $allowedFields = ['name', 'email', 'phone', 'company', 'source', 'status', 'assigned_to', 'property_interest', 'budget', 'location_preference', 'notes', 'message'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $lead->$field = $data[$field];
                }
            }

            $lead->save();

            // Handle tags if provided
            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->syncTags($lead, $data['tags']);
            }

            // Log activity
            $this->logLeadActivity($lead->id, 'lead_updated', 'Lead updated');

            $response = [
                'success' => true,
                'message' => 'Lead updated successfully',
                'data' => $this->formatLeadData($lead),
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to update lead: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a lead
     */
    public function destroy($id)
    {
        try {
            $guard = \App\Services\CRMGuard::getInstance();
            if (!$guard->isCrmEnabled()) {
                $this->jsonError('CRM is currently disabled by administrator', 403);
            }

            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            $this->authorize($lead);

            $this->logLeadActivity($lead->id, 'lead_deleted', 'Lead soft-deleted');

            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
            $userRole = $_SESSION['role'] ?? 'admin';
            $crm = new \App\Services\CRMService();
            $result = $crm->deleteLead((int)$id, $userRole);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Lead deleted successfully (recoverable from trash)',
            ]);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to delete lead: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add a note to a lead
     */
    public function addNote($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['content'])) {
                $this->jsonError('Note content is required', 422);
            }

            $currentUser = $this->getCurrentUser();

            $noteData = [
                'lead_id' => $lead->id,
                'content' => $data['content'],
                'is_private' => $data['is_private'] ?? false,
                'created_by' => ($currentUser && isset($currentUser->id) ? $currentUser->id : ($currentUser['id'] ?? null)),
            ];

            $note = new LeadNote($noteData);
            $note->save();

            // Log activity
            $this->logLeadActivity($lead->id, 'note_added', 'Note added');

            $response = [
                'success' => true,
                'message' => 'Note added successfully',
                'data' => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'is_private' => $note->is_private,
                    'created_by' => $note->created_by,
                    'created_at' => $note->created_at ?? date('Y-m-d H:i:s'),
                ],
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to add note: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Upload a file for a lead
     */
    public function uploadFile($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            // Check if file was uploaded
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->jsonError('No file uploaded or upload error', 422);
            }

            $file = $_FILES['file'];

            $v = UploadValidator::validate($file, ['types' => 'documents']);
            if (empty($v['valid'])) {
                $this->jsonError($v['error'] ?? 'Invalid file', 422);
            }

            $currentUser = $this->getCurrentUser();

            // Generate unique filename
            $safeName = UploadValidator::safeFilename($file['name']);
            $filename = 'lead_' . $lead->id . '_' . time() . '_' . $safeName;

            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../uploads/leads/' . $lead->id;
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . '/' . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $this->jsonError('Failed to save file', 500);
            }

            // Create file record
            $fileData = [
                'lead_id' => $lead->id,
                'original_name' => $file['name'],
                'file_path' => 'leads/' . $lead->id . '/' . $filename,
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'description' => isset($_POST['description']) ? Security::sanitize($_POST['description']) : '',
                'is_private' => isset($_POST['is_private']) ? Security::sanitize($_POST['is_private']) === 'true' : false,
                'uploaded_by' => ($currentUser && isset($currentUser->id) ? $currentUser->id : ($currentUser['id'] ?? null)),
            ];

            $leadFile = new LeadFile($fileData);
            $leadFile->save();

            // Log activity
            $this->logLeadActivity($lead->id, 'file_uploaded', 'File uploaded');

            $response = [
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'id' => $leadFile->id,
                    'original_name' => $leadFile->original_name,
                    'file_path' => $leadFile->file_path,
                    'file_type' => $leadFile->file_type,
                    'file_size' => $leadFile->file_size,
                    'uploaded_by' => $leadFile->uploaded_by,
                ],
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to upload file: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update lead status
     */
    public function updateStatus($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['status'])) {
                $this->jsonError('Status is required', 422);
            }

            $oldStatus = $lead->status;
            $newStatus = $data['status'];

            if ($oldStatus !== $newStatus) {
                $lead->status = $newStatus;
                $lead->save();

                // Add note if provided
                if (!empty($data['notes'])) {
                    $currentUser = $this->getCurrentUser();
                    $noteData = [
                        'lead_id' => $lead->id,
                        'content' => "Status changed from {$oldStatus} to {$newStatus}. " . $data['notes'],
                        'is_private' => false,
                        'created_by' => ($currentUser && isset($currentUser->id) ? $currentUser->id : ($currentUser['id'] ?? null)),
                    ];

                    $note = new LeadNote($noteData);
                    $note->save();
                }

                // Log activity
                $this->logLeadActivity($lead->id, 'status_changed', 'Status changed', [
                    'from' => $oldStatus,
                    'to' => $newStatus,
                ]);
            }

            $response = [
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $this->formatLeadData($lead),
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to update status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Assign lead to a user
     */
    public function assign($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['user_id'])) {
                $this->jsonError('User ID is required', 422);
            }

            $oldUserId = $lead->assigned_to;
            $newUserId = $data['user_id'];

            if ($oldUserId != $newUserId) {
                $lead->assigned_to = $newUserId;
                $lead->save();

                // Add note if provided
                if (!empty($data['notes'])) {
                    $currentUser = $this->getCurrentUser();
                    $noteData = [
                        'lead_id' => $lead->id,
                        'content' => 'Assignment notes: ' . $data['notes'],
                        'is_private' => false,
                        'created_by' => ($currentUser && isset($currentUser->id) ? $currentUser->id : ($currentUser['id'] ?? null)),
                    ];

                    $note = new LeadNote($noteData);
                    $note->save();
                }

                // Log activity
                $this->logLeadActivity($lead->id, 'assigned', 'Lead assigned', [
                    'from' => $oldUserId,
                    'to' => $newUserId,
                ]);
            }

            $response = [
                'success' => true,
                'message' => 'Lead assigned successfully',
                'data' => $this->formatLeadData($lead),
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to assign lead: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk assign leads to a user
     */
    public function bulkAssign()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['lead_ids']) || !is_array($data['lead_ids'])) {
                $this->jsonError('Lead IDs array is required', 422);
            }

            if (empty($data['user_id'])) {
                $this->jsonError('User ID is required', 422);
            }

            $leadIds = $data['lead_ids'];
            $userId = $data['user_id'];
            $notes = $data['notes'] ?? null;

            $currentUser = $this->getCurrentUser();
            $assignedCount = 0;
            $errors = [];

            foreach ($leadIds as $leadId) {
                try {
                    $lead = Lead::find($leadId);

                    if (!$lead) {
                        $errors[] = "Lead $leadId not found";
                        continue;
                    }

                    $oldUserId = $lead->assigned_to;

                    // Only update if assignment is changing
                    if ($oldUserId != $userId) {
                        $lead->assigned_to = $userId;
                        $lead->save();
                        $assignedCount++;

                        // Add assignment note if provided
                        if ($notes) {
                            $noteData = [
                                'lead_id' => $lead->id,
                                'content' => 'Bulk assignment: ' . $notes,
                                'is_private' => false,
                                'created_by' => ($currentUser && isset($currentUser->id) ? $currentUser->id : ($currentUser['id'] ?? null)),
                            ];

                            $note = new LeadNote($noteData);
                            $note->save();
                        }

                        // Log activity
                        $this->logLeadActivity($lead->id, 'assigned', 'Lead bulk assigned', [
                            'from' => $oldUserId,
                            'to' => $userId,
                        ]);
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Failed to assign lead $leadId: " . $e->getMessage();
                }
            }

            $response = [
                'success' => true,
                'message' => "Successfully assigned $assignedCount out of " . count($leadIds) . " leads",
                'data' => [
                    'assigned_count' => $assignedCount,
                    'total_requested' => count($leadIds),
                    'errors' => $errors,
                ],
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to bulk assign leads: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lead statistics
     */
    public function getStats()
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $conn = $db->getConnection();

            // Get total leads count
            $stmt = $conn->query("SELECT COUNT(*) as cnt FROM leads");
            $totalLeads = $stmt->fetch(\PDO::FETCH_OBJ)->cnt ?? 0;

            // Get leads by status
            $leadsByStatus = [];
            $stmt = $conn->query("SELECT status, COUNT(*) as cnt FROM leads WHERE status IS NOT NULL AND status != '' GROUP BY status");
            foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $row) {
                $leadsByStatus[$row->status] = (int)$row->cnt;
            }

            // Get leads by source
            $leadsBySource = [];
            $stmt = $conn->query("SELECT source, COUNT(*) as cnt FROM leads WHERE source IS NOT NULL AND source != '' GROUP BY source");
            foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $row) {
                $leadsBySource[$row->source] = (int)$row->cnt;
            }

            // Get recent activities (last 10)
            $stmt = $conn->query("SELECT * FROM lead_activities ORDER BY created_at DESC LIMIT 10");
            $recentActivities = $stmt->fetchAll(\PDO::FETCH_OBJ);

            $response = [
                'success' => true,
                'data' => [
                    'total_leads' => $totalLeads,
                    'leads_by_status' => $leadsByStatus,
                    'leads_by_source' => $leadsBySource,
                    'recent_activities' => $recentActivities,
                ],
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lookup data for forms
     */
    public function getLookupData()
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $conn = $db->getConnection();

            $statuses = $conn->query("SELECT * FROM lead_statuses ORDER BY status_name ASC")->fetchAll(\PDO::FETCH_OBJ);
            $sources = $conn->query("SELECT * FROM lead_sources ORDER BY name ASC")->fetchAll(\PDO::FETCH_OBJ);
            $tags = $conn->query("SELECT * FROM lead_tags ORDER BY name ASC")->fetchAll(\PDO::FETCH_OBJ);
            $tid = (int)$this->tenantId();
            $tidWhere = $tid > 1 ? ' WHERE tenant_id = ?' : '';
            $tidParam = $tid > 1 ? [$tid] : [];
            $users = $conn->query("SELECT id, name, email, role FROM users{$tidWhere} ORDER BY name ASC", $tidParam)->fetchAll(\PDO::FETCH_OBJ);

            $response = [
                'success' => true,
                'data' => [
                    'statuses' => $statuses,
                    'sources' => $sources,
                    'tags' => $tags,
                    'users' => $users,
                ],
            ];

            $this->jsonResponse($response);
        } catch (\Throwable $e) {
            $this->jsonError('Failed to fetch lookup data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Build lead query based on filters
     */
    private function buildLeadQuery($search, $status, $source, $assignedTo, $tag, $dateFrom, $dateTo, $sortField, $sortDirection, $currentUser)
    {
        $db = \App\Core\Database\Database::getInstance();
        $conn = $db->getConnection();
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $s = "%$search%";
            $params = array_merge($params, [$s, $s, $s]);
        }
        if ($status) { $where[] = "status = ?"; $params[] = $status; }
        if ($source) { $where[] = "source = ?"; $params[] = $source; }
        if ($assignedTo) { $where[] = "assigned_to = ?"; $params[] = $assignedTo; }
        if ($dateFrom) { $where[] = "created_at >= ?"; $params[] = $dateFrom; }
        if ($dateTo) { $where[] = "created_at <= ?"; $params[] = $dateTo; }

        $sql = "SELECT * FROM leads";
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $allowedSort = ['created_at', 'updated_at', 'first_name', 'last_name', 'email', 'status', 'source'];
        if ($sortField && in_array($sortField, $allowedSort)) {
            $dir = ($sortDirection === 'desc') ? 'DESC' : 'ASC';
            $sql .= " ORDER BY $sortField $dir";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Format lead data for API response
     */
    private function formatLeadData($lead, $fullDetails = false)
    {
        $data = [
            'id' => $lead->id,
            'name' => $lead->name ?? '',
            'email' => $lead->email ?? '',
            'phone' => $lead->phone ?? '',
            'status' => $lead->status ?? '',
            'source' => $lead->source ?? '',
            'assigned_to' => $lead->assigned_to ?? '',
            'created_at' => $lead->created_at ?? '',
            'updated_at' => $lead->updated_at ?? '',
        ];

        if ($fullDetails) {
            $data['company'] = $lead->company ?? '';
            $data['address'] = $lead->address ?? '';
            $data['city'] = $lead->city ?? '';
            $data['state'] = $lead->state ?? '';
            $data['pincode'] = $lead->pincode ?? '';
            $data['notes'] = $lead->notes ?? '';
            $data['estimated_value'] = $lead->estimated_value ?? '';
            $data['budget'] = $lead->budget ?? '';
            $data['property_interest'] = $lead->property_interest ?? '';
            $data['location_preference'] = $lead->location_preference ?? '';
            $data['last_activity_date'] = $lead->last_activity_date ?? '';
            $data['next_activity_date'] = $lead->next_activity_date ?? '';
            $data['lead_score'] = $lead->lead_score ?? 0;
            $data['priority'] = $lead->priority ?? 'medium';
        }

        return $data;
    }

    /**
     * Sync tags for a lead
     */
    private function syncTags($lead, $tagNames)
    {
        // Implementation for tag synchronization
        // This is a simplified version
    }

    /**
     * Check authorization for lead access
     */
    private function authorize($lead)
    {
        // Implement authorization logic
        // For now, allow all access
        return true;
    }

    /**
     * Send JSON response
     */
    public function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send JSON error response
     */
    protected function jsonError($message, $statusCode = 400)
    {
        $this->jsonResponse([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 900 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//
