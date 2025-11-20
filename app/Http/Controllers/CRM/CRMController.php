<?php
/**
 * CRM Lead Management Controller
 * Handles lead management and customer relationship operations
 */

namespace App\Controllers;

class CRMController extends BaseController {

    private $crmLead;

    public function __construct() {
        $this->crmLead = new \App\Models\CRMLead();
    }

    /**
     * Main CRM dashboard
     */
    public function index() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        // Check if user is admin or agent
        if (!$this->isAdmin() && !isset($_SESSION['user_role'])) {
            $this->setFlashMessage('error', 'Access denied');
            $this->redirect(BASE_URL . 'dashboard');
            return;
        }

        $stats = $this->crmLead->getLeadStats();
        $recent_leads = $this->crmLead->getLeads(['status' => 'new'], 1, 5);
        $follow_up_leads = $this->crmLead->getFollowUpLeads();

        $this->data['page_title'] = 'CRM Dashboard - ' . APP_NAME;
        $this->data['stats'] = $stats;
        $this->data['recent_leads'] = $recent_leads['leads'] ?? [];
        $this->data['follow_up_leads'] = $follow_up_leads;

        $this->render('crm/dashboard');
    }

    /**
     * Display all leads with filtering
     */
    public function leads() {
        if (!$this->isLoggedIn() || (!$this->isAdmin() && !isset($_SESSION['user_role']))) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? '',
            'source' => $_GET['source'] ?? '',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $page = (int)($_GET['page'] ?? 1);
        $leads_data = $this->crmLead->getLeads($filters, $page);

        // Get agents for assignment
        $agents = $this->getActiveAgents();

        $this->data['page_title'] = 'Lead Management - ' . APP_NAME;
        $this->data['leads_data'] = $leads_data;
        $this->data['filters'] = $filters;
        $this->data['agents'] = $agents;

        $this->render('crm/leads');
    }

    /**
     * Create new lead
     */
    public function createLead() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lead_data = [
                'customer_name' => $_POST['customer_name'] ?? '',
                'customer_email' => $_POST['customer_email'] ?? '',
                'customer_phone' => $_POST['customer_phone'] ?? '',
                'customer_city' => $_POST['customer_city'] ?? '',
                'lead_source' => $_POST['lead_source'] ?? 'website',
                'property_interest' => $_POST['property_interest'] ?? '',
                'budget_range' => $_POST['budget_range'] ?? '',
                'preferred_contact_time' => $_POST['preferred_contact_time'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'priority' => $_POST['priority'] ?? 'medium'
            ];

            // Validate required fields
            if (empty($lead_data['customer_name']) || empty($lead_data['customer_email'])) {
                $this->setFlashMessage('error', 'Customer name and email are required');
                $this->redirect(BASE_URL . 'crm/leads/create');
                return;
            }

            $lead_id = $this->crmLead->createLead($lead_data);

            if ($lead_id) {
                $this->setFlashMessage('success', 'Lead created successfully');

                // Redirect to lead details or leads list
                if (isset($_POST['save_and_continue'])) {
                    $this->redirect(BASE_URL . 'crm/leads/' . $lead_id . '/edit');
                } else {
                    $this->redirect(BASE_URL . 'crm/leads');
                }
            } else {
                $this->setFlashMessage('error', 'Failed to create lead');
                $this->redirect(BASE_URL . 'crm/leads/create');
            }
        }

        $this->data['page_title'] = 'Create Lead - ' . APP_NAME;
        $this->data['lead_sources'] = $this->getLeadSources();
        $this->data['budget_ranges'] = $this->getBudgetRanges();

        $this->render('crm/create_lead');
    }

    /**
     * View lead details
     */
    public function viewLead($lead_id) {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $lead = $this->crmLead->getLead($lead_id);
        if (!$lead) {
            $this->setFlashMessage('error', 'Lead not found');
            $this->redirect(BASE_URL . 'crm/leads');
            return;
        }

        $activities = $this->crmLead->getLeadActivities($lead_id);
        $lead_score = $this->crmLead->calculateLeadScore($lead_id);

        $this->data['page_title'] = 'Lead Details - ' . APP_NAME;
        $this->data['lead'] = $lead;
        $this->data['activities'] = $activities;
        $this->data['lead_score'] = $lead_score;

        $this->render('crm/lead_details');
    }

    /**
     * Edit lead
     */
    public function editLead($lead_id) {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $lead = $this->crmLead->getLead($lead_id);
        if (!$lead) {
            $this->setFlashMessage('error', 'Lead not found');
            $this->redirect(BASE_URL . 'crm/leads');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $update_data = [
                'customer_name' => $_POST['customer_name'] ?? '',
                'customer_email' => $_POST['customer_email'] ?? '',
                'customer_phone' => $_POST['customer_phone'] ?? '',
                'customer_city' => $_POST['customer_city'] ?? '',
                'lead_source' => $_POST['lead_source'] ?? '',
                'lead_status' => $_POST['lead_status'] ?? '',
                'property_interest' => $_POST['property_interest'] ?? '',
                'budget_range' => $_POST['budget_range'] ?? '',
                'preferred_contact_time' => $_POST['preferred_contact_time'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'priority' => $_POST['priority'] ?? 'medium'
            ];

            $success = $this->crmLead->updateLead($lead_id, $update_data);

            if ($success) {
                $this->setFlashMessage('success', 'Lead updated successfully');
                $this->redirect(BASE_URL . 'crm/leads/' . $lead_id);
            } else {
                $this->setFlashMessage('error', 'Failed to update lead');
            }
        }

        $this->data['page_title'] = 'Edit Lead - ' . APP_NAME;
        $this->data['lead'] = $lead;
        $this->data['lead_sources'] = $this->getLeadSources();
        $this->data['budget_ranges'] = $this->getBudgetRanges();
        $this->data['lead_statuses'] = $this->getLeadStatuses();

        $this->render('crm/edit_lead');
    }

    /**
     * Assign lead to agent
     */
    public function assignLead($lead_id) {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $agent_id = $_POST['agent_id'] ?? '';

            if (empty($agent_id)) {
                $this->setFlashMessage('error', 'Please select an agent');
                $this->redirect(BASE_URL . 'crm/leads/' . $lead_id . '/assign');
                return;
            }

            $success = $this->crmLead->assignLead($lead_id, $agent_id);

            if ($success) {
                $this->setFlashMessage('success', 'Lead assigned successfully');
                $this->redirect(BASE_URL . 'crm/leads/' . $lead_id);
            } else {
                $this->setFlashMessage('error', 'Failed to assign lead');
                $this->redirect(BASE_URL . 'crm/leads/' . $lead_id . '/assign');
            }
        }

        $lead = $this->crmLead->getLead($lead_id);
        $agents = $this->getActiveAgents();

        $this->data['page_title'] = 'Assign Lead - ' . APP_NAME;
        $this->data['lead'] = $lead;
        $this->data['agents'] = $agents;

        $this->render('crm/assign_lead');
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($lead_id) {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($new_status)) {
                $this->setFlashMessage('error', 'Please select a status');
                $this->redirect(BASE_URL . 'crm/leads/' . $lead_id . '/status');
                return;
            }

            $success = $this->crmLead->updateLeadStatus($lead_id, $new_status, $notes);

            if ($success) {
                $this->setFlashMessage('success', 'Lead status updated successfully');
                $this->redirect(BASE_URL . 'crm/leads/' . $lead_id);
            } else {
                $this->setFlashMessage('error', 'Failed to update lead status');
                $this->redirect(BASE_URL . 'crm/leads/' . $lead_id . '/status');
            }
        }

        $lead = $this->crmLead->getLead($lead_id);

        $this->data['page_title'] = 'Update Lead Status - ' . APP_NAME;
        $this->data['lead'] = $lead;
        $this->data['lead_statuses'] = $this->getLeadStatuses();

        $this->render('crm/update_status');
    }

    /**
     * Lead analytics dashboard
     */
    public function analytics() {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $stats = $this->crmLead->getLeadStats();
        $source_analysis = $this->crmLead->getSourceAnalysis();
        $conversion_funnel = $this->crmLead->getConversionFunnel();

        $this->data['page_title'] = 'CRM Analytics - ' . APP_NAME;
        $this->data['stats'] = $stats;
        $this->data['source_analysis'] = $source_analysis;
        $this->data['conversion_funnel'] = $conversion_funnel;

        $this->render('crm/analytics');
    }

    /**
     * Agent leads dashboard
     */
    public function myLeads() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];
        $status = $_GET['status'] ?? null;
        $leads = $this->crmLead->getAgentLeads($user_id, $status);

        $this->data['page_title'] = 'My Leads - ' . APP_NAME;
        $this->data['leads'] = $leads;
        $this->data['current_status'] = $status;

        $this->render('crm/my_leads');
    }

    /**
     * Bulk operations on leads
     */
    public function bulkActions() {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['bulk_action'] ?? '';
            $lead_ids = $_POST['lead_ids'] ?? [];

            if (empty($action) || empty($lead_ids)) {
                $this->setFlashMessage('error', 'Please select action and leads');
                $this->redirect(BASE_URL . 'crm/leads');
                return;
            }

            $success = false;

            switch ($action) {
                case 'assign':
                    $agent_id = $_POST['agent_id'] ?? '';
                    if (!empty($agent_id)) {
                        // Assign leads to agent
                        foreach ($lead_ids as $lead_id) {
                            $this->crmLead->assignLead($lead_id, $agent_id);
                        }
                        $success = true;
                    }
                    break;

                case 'update_status':
                    $new_status = $_POST['new_status'] ?? '';
                    if (!empty($new_status)) {
                        $success = $this->crmLead->bulkUpdateStatus($lead_ids, $new_status);
                    }
                    break;

                case 'delete':
                    // Soft delete leads
                    foreach ($lead_ids as $lead_id) {
                        $this->crmLead->deleteLead($lead_id);
                    }
                    $success = true;
                    break;
            }

            if ($success) {
                $this->setFlashMessage('success', 'Bulk operation completed successfully');
            } else {
                $this->setFlashMessage('error', 'Bulk operation failed');
            }

            $this->redirect(BASE_URL . 'crm/leads');
        }

        $this->data['page_title'] = 'Bulk Operations - ' . APP_NAME;
        $this->data['agents'] = $this->getActiveAgents();

        $this->render('crm/bulk_actions');
    }

    /**
     * Export leads
     */
    public function exportLeads() {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $format = $_GET['format'] ?? 'csv';
        $filters = [
            'status' => $_GET['status'] ?? '',
            'source' => $_GET['source'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $export_data = $this->crmLead->exportLeads($format, $filters);

        if ($export_data) {
            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');
                echo $export_data;
                exit;
            } elseif ($format === 'json') {
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.json"');
                echo $export_data;
                exit;
            }
        } else {
            $this->setFlashMessage('error', 'Export failed');
            $this->redirect(BASE_URL . 'crm/leads');
        }
    }

    /**
     * Get active agents for assignment
     */
    private function getActiveAgents() {
        try {
            global $pdo;
            $stmt = $pdo->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead sources
     */
    private function getLeadSources() {
        return [
            'website' => 'Website',
            'referral' => 'Referral',
            'social_media' => 'Social Media',
            'advertising' => 'Advertising',
            'direct' => 'Direct',
            'mlm' => 'MLM Network'
        ];
    }

    /**
     * Get budget ranges
     */
    private function getBudgetRanges() {
        return [
            'under_10L' => 'Under ₹10 Lakhs',
            '10L_25L' => '₹10L - ₹25L',
            '25L_50L' => '₹25L - ₹50L',
            '50L_1Cr' => '₹50L - ₹1Cr',
            '1Cr_2Cr' => '₹1Cr - ₹2Cr',
            'above_2Cr' => 'Above ₹2Cr'
        ];
    }

    /**
     * Get lead statuses
     */
    private function getLeadStatuses() {
        return [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal' => 'Proposal Sent',
            'negotiation' => 'In Negotiation',
            'closed_won' => 'Closed - Won',
            'closed_lost' => 'Closed - Lost'
        ];
    }
}
