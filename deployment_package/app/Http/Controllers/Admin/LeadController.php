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
}
