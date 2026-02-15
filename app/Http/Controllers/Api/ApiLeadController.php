<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadNote;
use App\Models\LeadFile;
use App\Models\LeadTag;
use App\Models\LeadStatus;
use App\Models\LeadSource;
use App\Models\User;

/**
 * API Lead Controller - Custom Framework Version
 * Handles all lead-related API endpoints
 */
class ApiLeadController extends Controller
{
    public function __construct() {}

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
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch leads: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new lead
     */
    public function store()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $requiredFields = ['first_name'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $this->jsonError("Field '{$field}' is required", 422);
                }
            }

            // Get current user
            $currentUser = $this->getCurrentUser();

            // Create lead data
            $leadData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'company' => $data['company'] ?? '',
                'source' => $data['source'] ?? 'website',
                'status' => $data['status'] ?? 'new',
                'assigned_to' => $data['assigned_to'] ?? null,
                'created_by' => $currentUser->id,
                'custom_fields' => isset($data['custom_fields']) ? json_encode($data['custom_fields']) : null,
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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

            // Update lead fields
            $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'company', 'source', 'status', 'assigned_to', 'custom_fields'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $lead->$field = $data[$field];
                }
            }

            $currentUser = $this->getCurrentUser();
            $lead->updated_by = $currentUser->id;
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
        } catch (\Exception $e) {
            $this->jsonError('Failed to update lead: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a lead
     */
    public function destroy($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->jsonError('Lead not found', 404);
            }

            // Check permissions
            $this->authorize($lead);

            // Log activity before deleting
            $this->logLeadActivity($lead->id, 'lead_deleted', 'Lead deleted');

            // Delete lead
            $lead->delete();

            $response = [
                'success' => true,
                'message' => 'Lead deleted successfully',
            ];

            $this->jsonResponse($response);
        } catch (\Exception $e) {
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
                'created_by' => $currentUser->id,
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
        } catch (\Exception $e) {
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
            $currentUser = $this->getCurrentUser();

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'lead_' . $lead->id . '_' . time() . '_' . uniqid() . '.' . $extension;

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
                'description' => $_POST['description'] ?? '',
                'is_private' => isset($_POST['is_private']) ? $_POST['is_private'] === 'true' : false,
                'uploaded_by' => $currentUser->id,
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
        } catch (\Exception $e) {
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
                        'created_by' => $currentUser->id,
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
        } catch (\Exception $e) {
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
                        'created_by' => $currentUser->id,
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
        } catch (\Exception $e) {
            $this->jsonError('Failed to assign lead: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lead statistics
     */
    public function getStats()
    {
        try {
            // Get total leads count
            $totalLeads = count(Lead::all());

            // Get leads by status
            $leadsByStatus = [];
            $statuses = LeadStatus::active();
            foreach ($statuses as $status) {
                $count = count(Lead::where('status', '=', $status->name));
                if ($count > 0) {
                    $leadsByStatus[$status->name] = $count;
                }
            }

            // Get leads by source
            $leadsBySource = [];
            $sources = LeadSource::active();
            foreach ($sources as $source) {
                $count = count(Lead::where('source', '=', $source->name));
                if ($count > 0) {
                    $leadsBySource[$source->name] = $count;
                }
            }

            // Get recent activities (last 10)
            $recentActivities = [];
            $activities = LeadActivity::all();
            usort($activities, function ($a, $b) {
                return strtotime($b->created_at ?? '2020-01-01') - strtotime($a->created_at ?? '2020-01-01');
            });
            $recentActivities = array_slice($activities, 0, 10);

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
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lookup data (statuses, sources, tags, users)
     */
    public function getLookupData()
    {
        try {
            $response = [
                'success' => true,
                'data' => [
                    'statuses' => LeadStatus::active(),
                    'sources' => LeadSource::active(),
                    'tags' => LeadTag::all(),
                    'users' => User::all(),
                ],
            ];

            $this->jsonResponse($response);
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch lookup data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Build lead query based on filters
     */
    private function buildLeadQuery($search, $status, $source, $assignedTo, $tag, $dateFrom, $dateTo, $sortField, $sortDirection, $currentUser)
    {
        $leads = Lead::all();

        // Apply search filter
        if ($search) {
            $leads = array_filter($leads, function ($lead) use ($search) {
                return stripos($lead->first_name, $search) !== false ||
                    stripos($lead->last_name, $search) !== false ||
                    stripos($lead->email, $search) !== false ||
                    stripos($lead->phone, $search) !== false ||
                    stripos($lead->company, $search) !== false;
            });
        }

        // Apply status filter
        if ($status) {
            $leads = array_filter($leads, function ($lead) use ($status) {
                return $lead->status === $status;
            });
        }

        // Apply source filter
        if ($source) {
            $leads = array_filter($leads, function ($lead) use ($source) {
                return $lead->source === $source;
            });
        }

        // Apply assignment filter
        if ($assignedTo) {
            if ($assignedTo === 'me') {
                $leads = array_filter($leads, function ($lead) use ($currentUser) {
                    return $lead->assigned_to == $currentUser->id;
                });
            } elseif ($assignedTo === 'unassigned') {
                $leads = array_filter($leads, function ($lead) {
                    return empty($lead->assigned_to);
                });
            } else {
                $leads = array_filter($leads, function ($lead) use ($assignedTo) {
                    return $lead->assigned_to == $assignedTo;
                });
            }
        }

        // Apply tag filter
        if ($tag) {
            $leads = array_filter($leads, function ($lead) use ($tag) {
                // This would need a more complex query to check tags
                // For now, return all leads (simplified)
                return true;
            });
        }

        // Apply date filters
        if ($dateFrom) {
            $leads = array_filter($leads, function ($lead) use ($dateFrom) {
                return strtotime($lead->created_at ?? '2020-01-01') >= strtotime($dateFrom);
            });
        }

        if ($dateTo) {
            $leads = array_filter($leads, function ($lead) use ($dateTo) {
                return strtotime($lead->created_at ?? '2020-01-01') <= strtotime($dateTo);
            });
        }

        // Apply sorting
        usort($leads, function ($a, $b) use ($sortField, $sortDirection) {
            $valueA = $a->$sortField ?? '';
            $valueB = $b->$sortField ?? '';

            if ($sortDirection === 'desc') {
                return $valueB <=> $valueA;
            } else {
                return $valueA <=> $valueB;
            }
        });

        return $leads;
    }

    /**
     * Format lead data for API response
     */
    private function formatLeadData($lead, $includeDetails = false)
    {
        $data = [
            'id' => $lead->id,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'source' => $lead->source,
            'status' => $lead->status,
            'assigned_to' => $lead->assigned_to,
            'created_by' => $lead->created_by,
            'created_at' => $lead->created_at ?? date('Y-m-d H:i:s'),
            'updated_at' => $lead->updated_at ?? date('Y-m-d H:i:s'),
        ];

        if ($includeDetails) {
            // Add related data for detailed view
            $data['assigned_user'] = $lead->assignedTo() ? [
                'id' => $lead->assignedTo()->id,
                'name' => $lead->assignedTo()->first_name . ' ' . $lead->assignedTo()->last_name,
            ] : null;

            $data['created_user'] = $lead->createdBy() ? [
                'id' => $lead->createdBy()->id,
                'name' => $lead->createdBy()->first_name . ' ' . $lead->createdBy()->last_name,
            ] : null;

            $data['notes'] = $this->getLeadNotes($lead->id);
            $data['activities'] = $this->getLeadActivities($lead->id);
            $data['files'] = $this->getLeadFiles($lead->id);
            $data['tags'] = $this->getLeadTags($lead->id);
        }

        return $data;
    }

    /**
     * Get lead notes
     */
    private function getLeadNotes($leadId)
    {
        $stmt = $this->db->prepare("SELECT * FROM lead_notes WHERE lead_id = :lead_id ORDER BY created_at DESC");
        $stmt->execute(['lead_id' => $leadId]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $notes = [];
        foreach ($results as $result) {
            $notes[] = new LeadNote($result);
        }

        return $notes;
    }

    /**
     * Get lead activities
     */
    private function getLeadActivities($leadId)
    {
        $stmt = $this->db->prepare("SELECT * FROM lead_activities WHERE lead_id = :lead_id ORDER BY created_at DESC");
        $stmt->execute(['lead_id' => $leadId]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $activities = [];
        foreach ($results as $result) {
            $activities[] = new LeadActivity($result);
        }

        return $activities;
    }

    /**
     * Get lead files
     */
    private function getLeadFiles($leadId)
    {
        $stmt = $this->db->prepare("SELECT * FROM lead_files WHERE lead_id = :lead_id ORDER BY created_at DESC");
        $stmt->execute(['lead_id' => $leadId]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $files = [];
        foreach ($results as $result) {
            $files[] = new LeadFile($result);
        }

        return $files;
    }

    /**
     * Get lead tags
     */
    private function getLeadTags($leadId)
    {
        // This would need a more complex query to get tags for a lead
        return [];
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
     * Log activity for a lead
     */
    private function logLeadActivity($leadId, $activityType, $description, $metadata = [])
    {
        $currentUser = $this->getCurrentUser();

        $activityData = [
            'lead_id' => $leadId,
            'activity_type' => $activityType,
            'description' => $description,
            'metadata' => json_encode($metadata),
            'user_id' => $currentUser->id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ];

        $activity = new LeadActivity($activityData);
        $activity->save();
    }

    /**
     * Get current authenticated user
     */
    private function getCurrentUser()
    {
        // This should be implemented based on your auth system
        // For now, return a mock user
        return (object)['id' => 1, 'first_name' => 'System', 'last_name' => 'User'];
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
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send JSON error response
     */
    private function jsonError($message, $statusCode = 400)
    {
        $this->jsonResponse([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
