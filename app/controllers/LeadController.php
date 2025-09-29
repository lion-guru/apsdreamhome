<?php

namespace App\Controllers;

use App\Services\CleanLeadService;
use App\Services\EmailService;

class LeadController extends Controller {
    private $leadService;
    private $emailService;

    public function __construct() {
        parent::__construct();
        $this->leadService = new CleanLeadService();
        $this->emailService = new EmailService();
    }

    /**
     * Display a listing of leads
     */
    public function index() {
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

        $this->view('leads/index', [
            'title' => 'Lead Management',
            'leads' => $leads,
            'filters' => $filters,
            'leadStats' => $leadStats,
            'sources' => $sources,
            'statuses' => $statuses
        ]);
    }

    /**
     * Display the specified lead
     */
    public function show($id) {
        $lead = $this->leadService->getLeadById($id);

        if (!$lead) {
            $this->notFound();
            return;
        }

        $activities = $this->leadService->getLeadActivities($id);
        $notes = $this->leadService->getLeadNotes($id);
        $files = $this->leadService->getLeadFiles($id);

        $this->view('leads/show', [
            'title' => 'Lead: ' . $lead['name'],
            'lead' => $lead,
            'activities' => $activities,
            'notes' => $notes,
            'files' => $files
        ]);
    }

    /**
     * Show the form for creating a new lead
     */
    public function create() {
        $sources = $this->leadService->getSources();
        $statuses = $this->leadService->getStatuses();
        $users = $this->leadService->getAssignableUsers();

        $this->view('leads/create', [
            'title' => 'Create New Lead',
            'sources' => $sources,
            'statuses' => $statuses,
            'users' => $users
        ]);
    }

    /**
     * Store a newly created lead
     */
    public function store() {
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
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            $leadId = $this->leadService->createLead($data);

            if ($leadId) {
                // Send welcome email
                $this->emailService->sendLeadWelcomeEmail($data['email'], $data['name']);

                $_SESSION['success'] = 'Lead created successfully!';
                $this->redirect('/leads/' . $leadId);
                return;
            }

            throw new \Exception('Failed to create lead');

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/leads/create');
        }
    }

    /**
     * Show the form for editing a lead
     */
    public function edit($id) {
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

        $this->view('leads/edit', [
            'title' => 'Edit Lead: ' . $lead['name'],
            'lead' => $lead,
            'sources' => $sources,
            'statuses' => $statuses,
            'users' => $users
        ]);
    }

    /**
     * Update the specified lead
     */
    public function update($id) {
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
                'property_type' => $_POST['property_type'] ?? $lead['property_type'],
                'location_preference' => $_POST['location_preference'] ?? $lead['location_preference'],
                'notes' => $_POST['notes'] ?? $lead['notes'],
                'assigned_to' => $_POST['assigned_to'] ?? $lead['assigned_to']
            ];

            $result = $this->leadService->updateLead($id, $data);

            if ($result) {
                $_SESSION['success'] = 'Lead updated successfully!';
                $this->redirect('/leads/' . $id);
                return;
            }

            throw new \Exception('Failed to update lead');

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            $this->redirect("/leads/$id/edit");
        }
    }

    /**
     * Add activity to lead
     */
    public function addActivity($id) {
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
                $_SESSION['success'] = 'Activity added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add activity';
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/leads/' . $id);
    }

    /**
     * Add note to lead
     */
    public function addNote($id) {
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
                $_SESSION['success'] = 'Note added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add note';
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/leads/' . $id);
    }

    /**
     * Assign lead to user
     */
    public function assign($id) {
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
                $_SESSION['success'] = 'Lead assigned successfully!';
            } else {
                $_SESSION['error'] = 'Failed to assign lead';
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/leads/' . $id);
    }

    /**
     * Convert lead to customer
     */
    public function convert($id) {
        try {
            $lead = $this->leadService->getLeadById($id);

            if (!$lead) {
                $this->notFound();
                return;
            }

            $customerId = $this->leadService->convertToCustomer($id);

            if ($customerId) {
                $_SESSION['success'] = 'Lead converted to customer successfully!';
                $this->redirect('/customers/' . $customerId);
                return;
            }

            throw new \Exception('Failed to convert lead');

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/leads/' . $id);
        }
    }

    /**
     * Display lead reports
     */
    public function reports() {
        $reportType = $_GET['type'] ?? 'summary';
        $dateRange = [
            'start' => $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'end' => $_GET['end_date'] ?? date('Y-m-d')
        ];

        $report = $this->leadService->generateReport($reportType, $dateRange);

        $this->view('leads/reports', [
            'title' => 'Lead Reports',
            'report' => $report,
            'reportType' => $reportType,
            'dateRange' => $dateRange
        ]);
    }

    /**
     * Check if user can edit lead
     */
    private function canEditLead($lead) {
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
