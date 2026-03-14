<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CleanLeadService;
use App\Services\EmailService;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadActivity;
use App\Models\Lead\LeadNote;
use App\Models\Lead\LeadFile;
use App\Models\Lead\LeadStatus;
use App\Models\Lead\LeadSource;
use App\Models\Lead\LeadTag;
use App\Models\User\User;
use App\Models\Lead\LeadDeal;
use App\Models\Lead\LeadCustomField;
use App\Core\Security;
use App\Core\Database\Database;

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
                'budget' => ($budget = $this->request->post('budget')) && !empty($budget) ? (float)$budget : null,
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
                'budget' => ($budget = $this->request->post('budget')) && !empty($budget) ? (float)$budget : $lead['budget'],
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
                'metadata' => ($metadata = $this->request->post('metadata')) && !empty($metadata) ? json_encode($metadata) : null
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
                return;
            }

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->jsonError('No file uploaded or upload error', 400);
                return;
            }

            $file = $_FILES['file'];
            $uploadDir = __DIR__ . '/../../uploads/leads/' . $lead->id;
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file['name']);
            $filePath = $uploadDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $leadFile = LeadFile::create([
                    'lead_id' => $lead->id,
                    'original_name' => $file['name'],
                    'file_path' => 'uploads/leads/' . $lead->id . '/' . $filename,
                    'file_type' => $file['type'],
                    'file_size' => $file['size'],
                    'uploaded_by' => $this->session->get('user_id')
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'data' => [
                        'id' => $leadFile->id,
                        'original_name' => $leadFile->original_name,
                        'file_path' => $leadFile->file_path
                    ]
                ]);
            } else {
                $this->jsonError('Failed to move uploaded file', 500);
            }
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    public function updateStatus($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['status'])) {
                $this->jsonError('Invalid status data', 400);
                return;
            }

            $lead = Lead::find($id);
            if (!$lead) {
                $this->jsonError('Lead not found', 404);
                return;
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

            $this->jsonResponse([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $this->formatLeadData($lead),
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Failed to update status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format lead data for JSON response
     */
    private function formatLeadData($lead)
    {
        return [
            'id' => $lead->id ?? $lead['id'],
            'name' => $lead->name ?? $lead['name'],
            'email' => $lead->email ?? $lead['email'],
            'status' => $lead->status ?? $lead['status'],
            'updated_at' => $lead->updated_at ?? $lead['updated_at'] ?? date('Y-m-d H:i:s')
        ];
    }

    public function bulkAssign()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['lead_ids']) || !is_array($data['lead_ids'])) {
                $this->jsonError('Lead IDs array is required', 422);
                return;
            }

            if (empty($data['user_id'])) {
                $this->jsonError('User ID is required', 422);
                return;
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

            $this->jsonResponse([
                'success' => true,
                'message' => "Successfully assigned $assignedCount out of " . count($leadIds) . " leads",
                'data' => [
                    'assigned_count' => $assignedCount,
                    'total_requested' => count($leadIds),
                    'errors' => $errors,
                ],
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Failed to bulk assign leads: ' . $e->getMessage(), 500);
        }
    }

    public function getStats()
    {
        try {
            // Get total leads count
            $totalLeads = count(Lead::all()->all());

            // Get leads by status
            $leadsByStatus = [];
            $statuses = LeadStatus::active();
            foreach ($statuses as $status) {
                $count = count(array_filter(Lead::all()->all(), function ($lead) use ($status) {
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
                $count = count(array_filter(Lead::all()->all(), function ($lead) use ($source) {
                    return $lead->source === $source->name;
                }));
                if ($count > 0) {
                    $leadsBySource[$source->name] = $count;
                }
            }

            // Get recent activities (last 10)
            $activities = LeadActivity::all()->all();
            usort($activities, function ($a, $b) {
                return strtotime($b->created_at ?? '2020-01-01') - strtotime($a->created_at ?? '2020-01-01');
            });
            $recentActivities = array_slice($activities, 0, 10);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'total_leads' => $totalLeads,
                    'leads_by_status' => $leadsByStatus,
                    'leads_by_source' => $leadsBySource,
                    'recent_activities' => $recentActivities,
                ],
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Failed to fetch statistics: ' . $e->getMessage(), 500);
        }
    }

    public function getLookupData()
    {
        try {
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'statuses' => LeadStatus::active(),
                    'sources' => LeadSource::active(),
                    'tags' => LeadTag::all(),
                    'users' => User::all(),
                ],
            ]);
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

        return $this->jsonResponse([
            'success' => true,
            'data' => $notes,
        ]);
    }

    public function updateNote($leadId, $noteId)
    {
        $note = LeadNote::where('lead_id', $leadId)
            ->findOrFail($noteId);

        // Check if user has permission to update this note
        $user = $this->getCurrentUser();
        if ($note->user_id !== $user->id && !$user->hasRole('admin')) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'You do not have permission to update this note',
            ], 403);
        }

        try {
            $note->content = $this->request->get('content');
            $note->is_private = $this->request->get('is_private') ?? $note->is_private;
            $note->save();

            // Log the activity
            $this->leadService->logActivity($note->lead, 'note_updated', [
                'title' => 'Note Updated',
                'description' => 'A note was updated',
                'note_id' => $note->id,
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Note updated successfully',
                'data' => $note->load('user'),
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
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
        $user = $this->getCurrentUser();
        if ($note->user_id !== $user->id && !$user->hasRole('admin')) {
            return $this->jsonResponse([
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

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Note deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
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

        return $this->jsonResponse([
            'success' => true,
            'data' => $files,
        ]);
    }

    public function deleteFile($leadId, $fileId)
    {
        $file = LeadFile::where('lead_id', $leadId)
            ->findOrFail($fileId);

        // Check if user has permission to delete this file
        $user = $this->getCurrentUser();
        if ($file->uploaded_by !== $user->id && !$user->hasRole('admin')) {
            return $this->jsonResponse([
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

            // Delete the actual file
            $fullPath = __DIR__ . '/../../' . $filePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Delete the file record
            $file->delete();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
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
        $user = $this->getCurrentUser();
        if ($file->is_private && $file->uploaded_by !== $user->id && !$user->hasRole('admin')) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'You do not have permission to download this file',
            ], 403);
        }

        $filePath = __DIR__ . '/../../' . $file->file_path;

        if (!file_exists($filePath)) {
            return $this->jsonResponse([
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

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file->original_name) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function getActivities($id)
    {
        $lead = Lead::findOrFail($id);
        $activities = $lead->activities()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->jsonResponse([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function getTags($id)
    {
        $lead = Lead::findOrFail($id);
        $tags = $lead->tags;

        return $this->jsonResponse([
            'success' => true,
            'data' => $tags,
        ]);
    }

    public function addTag($id)
    {
        $lead = Lead::findOrFail($id);
        $tagName = $this->request->get('name');

        if (empty($tagName)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Tag name is required',
            ], 422);
        }

        try {
            // Find or create the tag
            $tag = LeadTag::where('name', $tagName)->first();
            if (!$tag) {
                $tagId = LeadTag::insert([
                    'name' => $tagName,
                    'color' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                    'created_by' => $this->session->get('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $tag = LeadTag::find($tagId);
            }

            // Attach the tag to the lead (manual pivot insert since we don't have attach() yet)
            $this->db->query("INSERT INTO lead_tag_pivot (lead_id, tag_id, created_by, created_at) VALUES (?, ?, ?, ?)", [
                $lead->id,
                $tag->id,
                $this->session->get('user_id'),
                date('Y-m-d H:i:s')
            ]);

            // Log the activity
            $this->leadService->logActivity($lead, 'tag_added', [
                'title' => 'Tag Added',
                'description' => 'A tag was added to the lead',
                'tag_id' => $tag->id,
                'tag_name' => $tag->name,
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Tag added successfully',
                'data' => $tag,
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeTag($leadId, $tagId)
    {
        $lead = Lead::findOrFail($leadId);

        try {
            // Log the activity before detaching
            $this->db->query("DELETE FROM lead_tag_pivot WHERE lead_id = ? AND tag_id = ?", [$leadId, $tagId]);

            $this->leadService->logActivity($lead, 'tag_removed', [
                'title' => 'Tag Removed',
                'description' => 'A tag was removed from the lead',
                'tag_id' => $tagId,
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Tag removed successfully',
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to remove tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomFields($id)
    {
        $lead = Lead::findOrFail($id);
        $customFields = LeadCustomField::where('is_active', true)->get();
        // Manual mapping of values since we don't have with() working perfectly for all relations
        foreach ($customFields as &$field) {
            $value = $this->db->fetchOne("SELECT value FROM lead_custom_field_values WHERE lead_id = ? AND field_id = ?", [$id, $field->id]);
            $field->value = $value ? $value['value'] : null;
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $customFields,
        ]);
    }

    public function updateCustomFields($id)
    {
        $lead = Lead::findOrFail($id);
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            foreach ($data as $fieldId => $value) {
                $this->db->query("
                    INSERT INTO lead_custom_field_values (lead_id, field_id, value, updated_at) 
                    VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()
                ", [$id, $fieldId, $value]);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Custom fields updated successfully',
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update custom fields',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDeals($id)
    {
        $lead = Lead::findOrFail($id);
        $deals = LeadDeal::where('lead_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->jsonResponse([
            'success' => true,
            'data' => $deals,
        ]);
    }

    public function createDeal($id)
    {
        $lead = Lead::findOrFail($id);
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $dealData = array_merge($data, [
                'lead_id' => $id,
                'created_by' => $this->session->get('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $dealId = LeadDeal::insert($dealData);
            $deal = LeadDeal::find($dealId);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Deal created successfully',
                'data' => $deal,
            ], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateDeal($leadId, $dealId)
    {
        $deal = LeadDeal::where('lead_id', $leadId)->findOrFail($dealId);
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $deal->update($data);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Deal updated successfully',
                'data' => $deal,
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDeal($leadId, $dealId)
    {
        $deal = LeadDeal::where('lead_id', $leadId)->findOrFail($dealId);

        try {
            $deal->delete();
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Deal deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to delete deal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOverviewStats()
    {
        $user = $this->getCurrentUser();
        
        $query = Lead::query();
        if (!$user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }
        
        $totalLeads = $query->count();
        $newLeads = $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))->count();
        
        // Manual count for converted leads since we don't have whereHas fully working for count
        $sql = "SELECT COUNT(DISTINCT lead_id) as count FROM lead_deals WHERE deal_stage = 'won'";
        if (!$user->hasRole('admin')) {
            $sql .= " AND lead_id IN (SELECT id FROM leads WHERE assigned_to = " . (int)$user->id . ")";
        }
        $convertedLeads = $this->db->fetchOne($sql)['count'];
        
        $conversionRate = $totalLeads > 0 
            ? round(($convertedLeads / $totalLeads) * 100, 2) 
            : 0;
        
        return $this->jsonResponse([
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
        $user = $this->getCurrentUser();
        
        $statuses = $this->db->fetchAll("
            SELECT s.*, (SELECT COUNT(*) FROM leads l WHERE l.status_id = s.id" . ($user->hasRole('admin') ? "" : " AND l.assigned_to = " . (int)$user->id) . ") as leads_count
            FROM lead_statuses s
            WHERE s.is_active = 1
        ");
        
        return $this->jsonResponse([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    public function getSourceStats()
    {
        $user = $this->getCurrentUser();
        
        $sources = $this->db->fetchAll("
            SELECT s.*, (SELECT COUNT(*) FROM leads l WHERE l.source_id = s.id" . ($user->hasRole('admin') ? "" : " AND l.assigned_to = " . (int)$user->id) . ") as leads_count
            FROM lead_sources s
            WHERE s.is_active = 1
        ");
        
        return $this->jsonResponse([
            'success' => true,
            'data' => $sources,
        ]);
    }

    public function getTimelineStats()
    {
        $user = $this->getCurrentUser();
        
        $sql = "SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count FROM leads";
        $params = [];
        
        if (!$user->hasRole('admin')) {
            $sql .= " WHERE assigned_to = ?";
            $params[] = $user->id;
            $sql .= " AND created_at >= ?";
        } else {
            $sql .= " WHERE created_at >= ?";
        }
        $params[] = date('Y-m-d H:i:s', strtotime('-12 months'));
        $sql .= " GROUP BY year, month ORDER BY year, month";
        
        $leadsByMonth = $this->db->fetchAll($sql, $params);
        
        $labels = [];
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $timestamp = strtotime("-" . $i . " months");
            $year = date('Y', $timestamp);
            $month = date('n', $timestamp);
            $labels[] = date('M Y', $timestamp);
            
            $count = 0;
            foreach ($leadsByMonth as $item) {
                if ($item['year'] == $year && $item['month'] == $month) {
                    $count = (int)$item['count'];
                    break;
                }
            }
            $data[] = $count;
        }
        
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [['label' => 'Leads', 'data' => $data]]
            ],
        ]);
    }

    public function getUsers()
    {
        $users = User::orderBy('name')->get();
        return $this->jsonResponse(['success' => true, 'data' => $users]);
    }

    public function getCustomFieldDefinitions()
    {
        $fields = LeadCustomField::where('is_active', true)->orderBy('sort_order')->get();
        return $this->jsonResponse(['success' => true, 'data' => $fields]);
    }

    public function storeQuick()
    {
        $name = trim(Security::sanitize($_POST['name'] ?? ''));
        $phone = trim(Security::sanitize($_POST['phone'] ?? ''));
        $source = trim(Security::sanitize($_POST['source'] ?? 'website_quick_register'));

        if (empty($name) || empty($phone)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Name and Phone are required'], 400);
            return;
        }

        $existingLead = Lead::where('phone', $phone)->first();
        if ($existingLead) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Welcome back!', 'lead_id' => $existingLead['id'], 'is_new' => false]);
            return;
        }

        try {
            $leadId = Lead::insert([
                'first_name' => $name,
                'phone' => $phone,
                'source_id' => 1, // Default source or lookup
                'status_id' => 1, // Default status
                'created_at' => date('Y-m-d H:i:s')
            ]);

            setcookie('visitor_lead_id', $leadId, time() + (86400 * 30), "/");
            $this->jsonResponse(['status' => 'success', 'message' => 'Thank you!', 'lead_id' => $leadId, 'is_new' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }
}