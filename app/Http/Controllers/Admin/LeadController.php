<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CleanLeadService;
use App\Services\EmailService;

class LeadController extends AdminController
{
    private $leadService;
    private $emailService;

    public function __construct()
    {
        parent::__construct();

        $this->leadService = new CleanLeadService();
        $this->emailService = new EmailService();
    }

    /**
     * Display a listing of leads
     */
    public function index()
    {
        $filters = [
            'search' => $this->request->get('search'),
            'status' => $this->request->get('status'),
            'source' => $this->request->get('source'),
            'priority' => $this->request->get('priority'),
            'assigned_to' => $this->request->get('assigned_to'),
            'date_from' => $this->request->get('date_from'),
            'date_to' => $this->request->get('date_to'),
            'page' => (int)($this->request->get('page') ?? 1),
            'per_page' => (int)($this->request->get('per_page') ?? 20)
        ];

        $leads = $this->leadService->getLeads($filters);
        $leadStats = $this->leadService->getLeadStats();
        $sources = $this->leadService->getSources();
        $statuses = $this->leadService->getStatuses();

        $this->data['title'] = $this->mlSupport->translate('Lead Management');
        $this->data['leads'] = $leads;
        $this->data['filters'] = $filters;
        $this->data['leadStats'] = $leadStats;
        $this->data['sources'] = $sources;
        $this->data['statuses'] = $statuses;

        $this->render('admin/leads/index');
    }

    /**
     * Display the specified lead
     */
    public function show($id)
    {
        $lead = $this->leadService->getLeadById($id);

        if (!$lead) {
            $this->notFound();
            return;
        }

        $activities = $this->leadService->getLeadActivities($id);
        $notes = $this->leadService->getLeadNotes($id);
        $files = $this->leadService->getLeadFiles($id);

        $this->data['title'] = $this->mlSupport->translate('Lead') . ': ' . $lead['name'];
        $this->data['lead'] = $lead;
        $this->data['activities'] = $activities;
        $this->data['notes'] = $notes;
        $this->data['files'] = $files;

        $this->render('admin/leads/show');
    }

    /**
     * Show the form for creating a new lead
     */
    public function create()
    {
        $sources = $this->leadService->getSources();
        $statuses = $this->leadService->getStatuses();
        $users = $this->leadService->getAssignableUsers();

        $this->data['title'] = $this->mlSupport->translate('Create New Lead');
        $this->data['sources'] = $sources;
        $this->data['statuses'] = $statuses;
        $this->data['users'] = $users;

        $this->render('admin/leads/create');
    }

    /**
     * Store a newly created lead
     */
    public function store()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/leads/create');
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            $this->redirect('admin/leads/create');
            return;
        }

        try {
            $data = [
                'name' => $this->request->post('name') ?? '',
                'email' => $this->request->post('email') ?? '',
                'phone' => $this->request->post('phone') ?? '',
                'source' => $this->request->post('source') ?? '',
                'status' => $this->request->post('status') ?? 'new',
                'priority' => $this->request->post('priority') ?? 'medium',
                'budget' => !empty($this->request->post('budget')) ? (float)$this->request->post('budget') : null,
                'property_type' => $this->request->post('property_type') ?? '',
                'location_preference' => $this->request->post('location_preference') ?? '',
                'notes' => $this->request->post('notes') ?? '',
                'assigned_to' => $this->request->post('assigned_to') ?? null,
                'created_by' => $this->session->get('user_id'),
                'company' => $this->request->post('company') ?? null
            ];

            $leadId = $this->leadService->createLead($data);

            if ($leadId) {
                // Send welcome email
                $this->emailService->sendLeadWelcomeEmail($data['email'], $data['name']);

                $this->setFlash('success', 'Lead created successfully!');
                $this->redirect('admin/leads/' . $leadId);
                return;
            }

            throw new \Exception('Failed to create lead');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->session->set('form_data', $this->request->all());
            $this->redirect('admin/leads/create');
        }
    }

    /**
     * Show the form for editing a lead
     */
    public function edit($id)
    {
        $lead = $this->leadService->getLeadById($id);

        if (!$lead) {
            $this->notFound();
            return;
        }

        // Check permissions
        if (!$this->canEditLead($lead)) {
            $this->forbidden();
            return;
        }

        $sources = $this->leadService->getSources();
        $statuses = $this->leadService->getStatuses();
        $users = $this->leadService->getAssignableUsers();

        $this->data['title'] = 'Edit Lead: ' . $lead['name'];
        $this->data['lead'] = $lead;
        $this->data['sources'] = $sources;
        $this->data['statuses'] = $statuses;
        $this->data['users'] = $users;

        $this->render('admin/leads/edit');
    }

    /**
     * Update the specified lead
     */
    public function update($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect("admin/leads/edit/$id");
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            $this->redirect("admin/leads/edit/$id");
            return;
        }

        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            // Check permissions
            if (!$this->canEditLead($lead)) {
                $this->forbidden();
                return;
            }

            $data = [
                'name' => $this->request->post('name') ?? $lead['name'],
                'email' => $this->request->post('email') ?? $lead['email'],
                'phone' => $this->request->post('phone') ?? $lead['phone'],
                'source' => $this->request->post('source') ?? $lead['source'],
                'status' => $this->request->post('status') ?? $lead['status'],
                'priority' => $this->request->post('priority') ?? $lead['priority'],
                'budget' => !empty($this->request->post('budget')) ? (float)$this->request->post('budget') : $lead['budget'],
                'property_type' => $this->request->post('property_type') ?? $lead['property_type'], // property_interest
                'location_preference' => $this->request->post('location_preference') ?? $lead['location_preference'],
                'notes' => $this->request->post('notes') ?? $lead['notes'],
                'assigned_to' => $this->request->post('assigned_to') ?? $lead['assigned_to'],
                'company' => $this->request->post('company') ?? $lead['company']
            ];

            $result = $this->leadService->updateLead($id, $data);

            if ($result) {
                $this->setFlash('success', 'Lead updated successfully!');
                $this->redirect('admin/leads/' . $id);
                return;
            }

            throw new \Exception('Failed to update lead');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->session->set('form_data', $this->request->all());
            $this->redirect("admin/leads/edit/$id");
        }
    }

    /**
     * Add activity to lead
     */
    public function addActivity($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $data = [
                'lead_id' => $id,
                'activity_type' => $this->request->post('activity_type') ?? '',
                'description' => $this->request->post('description') ?? '',
                'created_by' => $this->session->get('user_id'),
                'metadata' => !empty($this->request->post('metadata')) ? json_encode($this->request->post('metadata')) : null
            ];

            $activityId = $this->leadService->addActivity($data);

            if ($activityId) {
                $this->setFlash('success', 'Activity added successfully!');
            } else {
                $this->setFlash('error', 'Failed to add activity');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('admin/leads/' . $id);
    }

    /**
     * Delete lead
     */
    public function destroy($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/leads');
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            $this->redirect('admin/leads');
            return;
        }

        try {
            $result = $this->leadService->deleteLead($id);

            if ($result) {
                $this->setFlash('success', 'Lead deleted successfully!');
            } else {
                $this->setFlash('error', 'Failed to delete lead');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('admin/leads');
    }

    /**
     * Add note to lead
     */
    public function addNote($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $data = [
                'lead_id' => $id,
                'note' => $this->request->post('note') ?? '',
                'created_by' => $this->session->get('user_id')
            ];

            $noteId = $this->leadService->addNote($data);

            if ($noteId) {
                $this->setFlash('success', 'Note added successfully!');
            } else {
                $this->setFlash('error', 'Failed to add note');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('admin/leads/' . $id);
    }

    /**
     * Assign lead to user
     */
    public function assign($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $assignedTo = $this->request->post('assigned_to') ?? null;

            if (!$assignedTo) {
                throw new \Exception('Please select a user to assign the lead to');
            }

            $result = $this->leadService->assignLead($id, $assignedTo);

            if ($result) {
                $this->setFlash('success', 'Lead assigned successfully!');
            } else {
                $this->setFlash('error', 'Failed to assign lead');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('admin/leads/' . $id);
    }

    /**
     * Convert lead to customer
     */
    public function convert($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            $this->redirect('admin/leads/' . $id);
            return;
        }

        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $customerId = $this->leadService->convertToCustomer($id);

            if ($customerId) {
                $this->setFlash('success', 'Lead converted to customer successfully!');
                $this->redirect('admin/customers');
                return;
            }

            throw new \Exception('Failed to convert lead');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('admin/leads/' . $id);
        }
    }

    /**
     * Display lead reports
     */
    public function reports()
    {
        $reportType = $this->request->get('type') ?? 'summary';
        $dateRange = [
            'start' => $this->request->get('start_date') ?? date('Y-m-d', strtotime('-30 days')),
            'end' => $this->request->get('end_date') ?? date('Y-m-d')
        ];

        $report = $this->leadService->generateReport($reportType, $dateRange);

        $this->data['title'] = 'Lead Reports';
        $this->data['report'] = $report;
        $this->data['reportType'] = $reportType;
        $this->data['dateRange'] = $dateRange;

        $this->render('admin/leads/reports');
    }

    /**
     * Check if user can edit lead
     */
    private function canEditLead($lead)
    {
        // Admin can edit all leads
        if ($this->isAdmin()) {
            return true;
        }

        $userId = $this->session->get('user_id');

        // Lead creator can edit
        if ($lead['created_by'] == $userId) {
            return true;
        }

        // Assigned user can edit
        if ($lead['assigned_to'] == $userId) {
            return true;
        }

        return false;
    }

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
                'description' => Security::sanitize($_POST['description']) ?? '',
                'is_private' => isset(Security::sanitize($_POST['is_private'])) ? Security::sanitize($_POST['is_private']) === 'true' : false,
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
                                'created_by' => $currentUser->id,
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
                } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            $this->jsonError('Failed to bulk assign leads: ' . $e->getMessage(), 500);
        }
    }

    public function getStats()
    {
        try {
            // Get total leads count
            $totalLeads = count(Lead::all());

            // Get leads by status
            $leadsByStatus = [];
            $statuses = LeadStatus::active();
            foreach ($statuses as $status) {
                $count = count(array_filter(Lead::all(), function ($lead) use ($status) {
                    return $lead->status === $status->name;
                }));
                if ($count > 0) {
                    $leadsByStatus[$status->name] = $count;
                }
            }

            // Get leads by source
            $leadsBySource = [];
            $sources = LeadSource::active();
            foreach ($sources as $source) {
                $count = count(array_filter(Lead::all(), function ($lead) use ($source) {
                    return $lead->source === $source->name;
                }));
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

    public function getNotes($id)
    {
        $lead = Lead::findOrFail($id);
        $notes = $lead->notes()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    public function updateNote(Request $request, $leadId, $noteId)
    {
        $note = LeadNote::where('lead_id', $leadId)
            ->findOrFail($noteId);

        // Check if user has permission to update this note
        $user = auth()->user();
        if ($note->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this note',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'is_private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $note->update([
                'content' => $request->content,
                'is_private' => $request->is_private ?? $note->is_private,
            ]);

            // Log the activity
            $this->leadService->logActivity($note->lead, 'note_updated', [
                'title' => 'Note Updated',
                'description' => 'A note was updated',
                'note_id' => $note->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully',
                'data' => $note->load('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteNote($leadId, $noteId)
    {
        $note = LeadNote::where('lead_id', $leadId)
            ->findOrFail($noteId);

        // Check if user has permission to delete this note
        $user = auth()->user();
        if ($note->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this note',
            ], 403);
        }

        try {
            // Log the activity before deleting
            $this->leadService->logActivity($note->lead, 'note_deleted', [
                'title' => 'Note Deleted',
                'description' => 'A note was deleted',
                'note_id' => $note->id,
            ]);

            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getFiles($id)
    {
        $lead = Lead::findOrFail($id);
        $files = $lead->files()
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    public function deleteFile($leadId, $fileId)
    {
        $file = LeadFile::where('lead_id', $leadId)
            ->findOrFail($fileId);

        // Check if user has permission to delete this file
        $user = auth()->user();
        if ($file->uploaded_by !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this file',
            ], 403);
        }

        try {
            $filePath = $file->file_path;
            
            // Log the activity before deleting
            $this->leadService->logActivity($file->lead, 'file_deleted', [
                'title' => 'File Deleted',
                'description' => 'A file was deleted from the lead',
                'file_id' => $file->id,
                'file_name' => $file->file_name,
            ]);

            // Delete the file record
            $file->delete();

            // Delete the actual file
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadFile($leadId, $fileId)
    {
        $file = LeadFile::where('lead_id', $leadId)
            ->findOrFail($fileId);

        // Check if user has permission to download this file
        $user = auth()->user();
        if ($file->is_private && $file->uploaded_by !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to download this file',
            ], 403);
        }

        $filePath = storage_path('app/public/' . $file->file_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        // Log the download activity
        $this->leadService->logActivity($file->lead, 'file_downloaded', [
            'title' => 'File Downloaded',
            'description' => 'A file was downloaded from the lead',
            'file_id' => $file->id,
            'file_name' => $file->file_name,
        ]);

        return response()->download($filePath, $file->file_name);
    }

    public function getActivities($id)
    {
        $lead = Lead::findOrFail($id);
        $activities = $lead->activities()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function getTags($id)
    {
        $lead = Lead::findOrFail($id);
        $tags = $lead->tags;

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    public function addTag(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find or create the tag
            $tag = LeadTag::firstOrCreate(
                ['name' => $request->name],
                [
                    'color' => $this->generateRandomColor(),
                    'created_by' => auth()->id(),
                ]
            );

            // Attach the tag to the lead if not already attached
            if (!$lead->tags->contains($tag->id)) {
                $lead->tags()->attach($tag->id, ['created_by' => auth()->id()]);

                // Log the activity
                $this->leadService->logActivity($lead, 'tag_added', [
                    'title' => 'Tag Added',
                    'description' => 'A tag was added to the lead',
                    'tag_id' => $tag->id,
                    'tag_name' => $tag->name,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tag added successfully',
                'data' => $tag,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeTag($leadId, $tagId)
    {
        $lead = Lead::findOrFail($leadId);
        $tag = $lead->tags()->findOrFail($tagId);

        try {
            // Log the activity before detaching
            $this->leadService->logActivity($lead, 'tag_removed', [
                'title' => 'Tag Removed',
                'description' => 'A tag was removed from the lead',
                'tag_id' => $tag->id,
                'tag_name' => $tag->name,
            ]);

            // Detach the tag
            $lead->tags()->detach($tag->id);

            return response()->json([
                'success' => true,
                'message' => 'Tag removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomFields($id)
    {
        $lead = Lead::findOrFail($id);
        $customFields = LeadCustomField::with(['values' => function ($query) use ($id) {
            $query->where('lead_id', $id);
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $customFields,
        ]);
    }

    public function updateCustomFields(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $customFields = $request->all();

        try {
            $this->leadService->updateCustomFields($lead, $customFields);

            return response()->json([
                'success' => true,
                'message' => 'Custom fields updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update custom fields',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDeals($id)
    {
        $lead = Lead::findOrFail($id);
        $deals = $lead->deals()
            ->with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deals,
        ]);
    }

    public function createDeal(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'deal_name' => 'required|string|max:255',
            'deal_value' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'expected_close_date' => 'required|date',
            'probability' => 'nullable|integer|min:0|max:100',
            'deal_stage' => 'required|string|in:prospect,qualification,needs_analysis,proposal,negotiation,won,lost',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,on_hold,closed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deal = $this->leadService->createDeal($lead, array_merge(
                $request->all(),
                ['created_by' => auth()->id()]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Deal created successfully',
                'data' => $deal->load(['createdBy', 'updatedBy']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateDeal(Request $request, $leadId, $dealId)
    {
        $deal = LeadDeal::where('lead_id', $leadId)
            ->findOrFail($dealId);

        $validator = Validator::make($request->all(), [
            'deal_name' => 'sometimes|required|string|max:255',
            'deal_value' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|required|string|size:3',
            'expected_close_date' => 'sometimes|required|date',
            'probability' => 'nullable|integer|min:0|max:100',
            'deal_stage' => 'sometimes|required|string|in:prospect,qualification,needs_analysis,proposal,negotiation,won,lost',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,on_hold,closed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $originalData = $deal->getOriginal();
            $deal->fill($request->all());
            $deal->updated_by = auth()->id();
            $deal->save();

            // Log the activity if any changes were made
            if ($deal->wasChanged()) {
                $changes = $deal->getChanges();
                
                $this->leadService->logActivity($deal->lead, 'deal_updated', [
                    'title' => 'Deal Updated',
                    'description' => 'A deal was updated',
                    'deal_id' => $deal->id,
                    'changes' => $this->formatDealChanges($originalData, $changes),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Deal updated successfully',
                'data' => $deal->load(['createdBy', 'updatedBy']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDeal($leadId, $dealId)
    {
        $deal = LeadDeal::where('lead_id', $leadId)
            ->findOrFail($dealId);

        // Check if user has permission to delete this deal
        $user = auth()->user();
        if ($deal->created_by !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this deal',
            ], 403);
        }

        try {
            // Log the activity before deleting
            $this->leadService->logActivity($deal->lead, 'deal_deleted', [
                'title' => 'Deal Deleted',
                'description' => 'A deal was deleted',
                'deal_id' => $deal->id,
                'deal_name' => $deal->deal_name,
            ]);

            $deal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deal deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOverviewStats()
    {
        $user = auth()->user();
        
        $query = Lead::query();
        
        // If user is not admin, only show their assigned leads
        if (!$user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }
        
        $totalLeads = $query->count();
        $newLeads = $query->where('created_at', '>=', now()->subDays(7))->count();
        $convertedLeads = $query->whereHas('deals', function ($q) {
            $q->where('deal_stage', 'won');
        })->count();
        
        $conversionRate = $totalLeads > 0 
            ? round(($convertedLeads / $totalLeads) * 100, 2) 
            : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_leads' => $totalLeads,
                'new_leads' => $newLeads,
                'converted_leads' => $convertedLeads,
                'conversion_rate' => $conversionRate,
            ],
        ]);
    }

    public function getStatusStats()
    {
        $user = auth()->user();
        
        $query = LeadStatus::withCount(['leads' => function ($q) use ($user) {
            if (!$user->hasRole('admin')) {
                $q->where('assigned_to', $user->id);
            }
        }])->where('is_active', true);
        
        $statuses = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    public function getSourceStats()
    {
        $user = auth()->user();
        
        $query = LeadSource::withCount(['leads' => function ($q) use ($user) {
            if (!$user->hasRole('admin')) {
                $q->where('assigned_to', $user->id);
            }
        }])->where('is_active', true);
        
        $sources = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $sources,
        ]);
    }

    public function getAssignedToStats()
    {
        $user = auth()->user();
        
        // Only admins can see stats for all users
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $users = User::withCount('assignedLeads')
            ->whereHas('assignedLeads')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function getCreatedByStats()
    {
        $user = auth()->user();
        
        // Only admins can see stats for all users
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $users = User::withCount('createdLeads')
            ->whereHas('createdLeads')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function getTimelineStats()
    {
        $user = auth()->user();
        
        $query = Lead::query();
        
        // If user is not admin, only show their assigned leads
        if (!$user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }
        
        // Get leads created in the last 12 months by month
        $leadsByMonth = $query->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();
        
        // Format the data for the chart
        $labels = [];
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $monthName = $date->format('M Y');
            
            $count = $leadsByMonth->first(function ($item) use ($year, $month) {
                return $item->year == $year && $item->month == $month;
            });
            
            $labels[] = $monthName;
            $data[] = $count ? $count->count : 0;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Leads',
                        'data' => $data,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1,
                    ],
                ],
            ],
        ]);
    }

    public function getAllTags()
    {
        $tags = LeadTag::orderBy('name')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    public function getUsers()
    {
        $users = User::orderBy('name')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function getCustomFieldDefinitions()
    {
        $fields = LeadCustomField::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $fields,
        ]);
    }

    public function getDealStages()
    {
        $stages = LeadDeal::getDealStages();
            
        return response()->json([
            'success' => true,
            'data' => $stages,
        ]);
    }

    public function storeQuick()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method'], 405);
            return;
        }

        $name = trim(Security::sanitize($_POST['name']) ?? '');
        $phone = trim(Security::sanitize($_POST['phone']) ?? '');
        $source = trim(Security::sanitize($_POST['source']) ?? 'website_quick_register');

        if (empty($name) || empty($phone)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Name and Phone are required'], 400);
            return;
        }

        // Check if lead already exists
        $existingLead = Lead::where('phone', $phone)->first();

        if ($existingLead) {
            // Update source if needed or just return success
            // We might want to update the 'last_contacted' or similar
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Welcome back! We have your details.',
                'lead_id' => $existingLead['id'],
                'is_new' => false
            ]);
            return;
        }

        try {
            // Create new lead
            $leadId = Lead::create([
                'first_name' => $name,
                'last_name' => '', // Split name if needed
                'phone' => $phone,
                'source' => $source,
                'status' => 'new',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Set a cookie to track this lead
            setcookie('visitor_lead_id', $leadId, time() + (86400 * 30), "/"); // 30 days

            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Thank you! We will contact you shortly.',
                'lead_id' => $leadId,
                'is_new' => true
            ]);
        } catch (\Exception $e) {
            error_log("Error creating quick lead: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    public function updateProgressive()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method'], 405);
            return;
        }

        $leadId = Security::sanitize($_POST['lead_id']) ?? $_COOKIE['visitor_lead_id'] ?? null;

        if (!$leadId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Lead not identified'], 400);
            return;
        }

        $data = [];
        if (!empty(Security::sanitize($_POST['email']))) $data['email'] = Security::sanitize($_POST['email']);
        if (!empty(Security::sanitize($_POST['property_type']))) $data['property_interest'] = Security::sanitize($_POST['property_type']);
        if (!empty(Security::sanitize($_POST['budget']))) $data['budget_range'] = Security::sanitize($_POST['budget']);
        if (!empty(Security::sanitize($_POST['location']))) $data['preferred_location'] = Security::sanitize($_POST['location']);
        if (!empty(Security::sanitize($_POST['role']))) $data['type'] = Security::sanitize($_POST['role']); // Buyer/Seller

        if (empty($data)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'No new data to update']);
            return;
        }

        try {
            Lead::update($leadId, $data);

            // If email is provided and they want to register as User
            if (!empty(Security::sanitize($_POST['create_account'])) && !empty(Security::sanitize($_POST['email']))) {
                // Check if user exists
                $existingUser = User::where('email', Security::sanitize($_POST['email']))->orWhere('phone', Security::sanitize($_POST['phone']))->first();
                if (!$existingUser) {
                    // We can't create a user without password easily in standard auth, 
                    // but we can prompt them to complete registration on the next screen.
                    // For now, just return success.
                }
            }

            $this->jsonResponse(['status' => 'success', 'message' => 'Details updated successfully']);
        } catch (\Exception $e) {
            logger()->error("Error updating lead: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Update failed'], 500);
        }
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\Api\LeadController.php

function getSources()
    {
        $sources = LeadSource::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $sources,
        ]);
    }
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 1599 lines. Consider optimizations:
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