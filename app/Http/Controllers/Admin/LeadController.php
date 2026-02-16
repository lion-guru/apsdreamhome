<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\CleanLeadService;
use App\Services\EmailService;

class LeadController extends BaseController
{
    private $leadService;
    private $emailService;

    public function __construct()
    {
        parent::__construct();

        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->layout = 'layouts/admin';

        $this->leadService = new CleanLeadService();
        $this->emailService = new EmailService();
    }

    /**
     * Display a listing of leads
     */
    public function index()
    {
        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'source' => $_GET['source'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20)
        ];

        $leads = $this->leadService->getLeads($filters);
        $leadStats = $this->leadService->getLeadStats();
        $sources = $this->leadService->getSources();
        $statuses = $this->leadService->getStatuses();

        $this->data['title'] = 'Lead Management';
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

        $this->data['title'] = 'Lead: ' . $lead['name'];
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

        $this->data['title'] = 'Create New Lead';
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
        try {
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'source' => $_POST['source'] ?? '',
                'status' => $_POST['status'] ?? 'new',
                'priority' => $_POST['priority'] ?? 'medium',
                'budget' => !empty($_POST['budget']) ? (float)$_POST['budget'] : null,
                'property_type' => $_POST['property_type'] ?? '',
                'location_preference' => $_POST['location_preference'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'assigned_to' => $_POST['assigned_to'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? null,
                'company' => $_POST['company'] ?? null
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
            $_SESSION['form_data'] = $_POST;
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
                'name' => $_POST['name'] ?? $lead['name'],
                'email' => $_POST['email'] ?? $lead['email'],
                'phone' => $_POST['phone'] ?? $lead['phone'],
                'source' => $_POST['source'] ?? $lead['source'],
                'status' => $_POST['status'] ?? $lead['status'],
                'priority' => $_POST['priority'] ?? $lead['priority'],
                'budget' => isset($_POST['budget']) ? (float)$_POST['budget'] : $lead['budget'],
                'property_type' => $_POST['property_type'] ?? $lead['property_type'], // property_interest
                'location_preference' => $_POST['location_preference'] ?? $lead['location_preference'],
                'notes' => $_POST['notes'] ?? $lead['notes'],
                'assigned_to' => $_POST['assigned_to'] ?? $lead['assigned_to'],
                'company' => $_POST['company'] ?? $lead['company']
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
            $_SESSION['form_data'] = $_POST;
            $this->redirect("admin/leads/$id/edit");
        }
    }

    /**
     * Add activity to lead
     */
    public function addActivity($id)
    {
        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $data = [
                'lead_id' => $id,
                'activity_type' => $_POST['activity_type'] ?? '',
                'description' => $_POST['description'] ?? '',
                'created_by' => $_SESSION['user_id'] ?? null,
                'metadata' => !empty($_POST['metadata']) ? json_encode($_POST['metadata']) : null
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
        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $data = [
                'lead_id' => $id,
                'note' => $_POST['note'] ?? '',
                'created_by' => $_SESSION['user_id'] ?? null
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
        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $assignedTo = $_POST['assigned_to'] ?? null;

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
        $reportType = $_GET['type'] ?? 'summary';
        $dateRange = [
            'start' => $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'end' => $_GET['end_date'] ?? date('Y-m-d')
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

        // Lead creator can edit
        if ($lead['created_by'] == $_SESSION['user_id']) {
            return true;
        }

        // Assigned user can edit
        if ($lead['assigned_to'] == $_SESSION['user_id']) {
            return true;
        }

        return false;
    }
}
