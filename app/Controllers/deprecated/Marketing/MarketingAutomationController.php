<?php

namespace App\Controllers\Marketing;

use App\Services\Marketing\MarketingAutomationService;
use App\Services\Auth\AuthenticationService;
use App\Core\ViewRenderer;

/**
 * Marketing Automation Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class MarketingAutomationController
{
    private $marketingService;
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        $this->marketingService = new MarketingAutomationService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new ViewRenderer();
    }

    /**
     * Show marketing dashboard
     */
    public function dashboard($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access marketing dashboard'];
            $this->redirect('/login');
            return;
        }

        // Get dashboard data
        $dashboardResult = $this->marketingService->getDashboardData();

        $data = [
            'title' => 'Marketing Dashboard - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'dashboard' => $dashboardResult['success'] ? $dashboardResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('marketing/dashboard', $data);
    }

    /**
     * Show leads list
     */
    public function leads($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access leads'];
            $this->redirect('/login');
            return;
        }

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'source' => $request['get']['source'] ?? null,
            'campaign' => $request['get']['campaign'] ?? null,
            'score_min' => $request['get']['score_min'] ?? null
        ];

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->marketingService->getLeads($filters, $limit, $offset);

        $data = [
            'title' => 'Marketing Leads - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'leads' => $result['success'] ? $result['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('marketing/leads', $data);
    }

    /**
     * Show lead details
     */
    public function leadDetails($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to view lead details'];
            $this->redirect('/login');
            return;
        }

        $leadId = $request['params']['id'] ?? null;

        if (!$leadId) {
            $_SESSION['errors'] = ['Lead ID is required'];
            $this->redirect('/marketing/leads');
            return;
        }

        $lead = $this->marketingService->getLead($leadId);

        if (!$lead) {
            $_SESSION['errors'] = ['Lead not found'];
            $this->redirect('/marketing/leads');
            return;
        }

        $data = [
            'title' => 'Lead Details - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'lead' => $lead,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('marketing/lead_details', $data);
    }

    /**
     * Show capture lead form
     */
    public function captureLead($request)
    {
        $data = [
            'title' => 'Capture Lead - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('marketing/capture_lead', $data);
    }

    /**
     * Handle capture lead
     */
    public function handleCaptureLead($request)
    {
        $name = trim($request['post']['name'] ?? '');
        $email = trim($request['post']['email'] ?? '');
        $phone = trim($request['post']['phone'] ?? '');
        $source = trim($request['post']['source'] ?? 'website');
        $campaign = trim($request['post']['campaign'] ?? '');

        // Validate required fields
        if (empty($name) || empty($email)) {
            $_SESSION['errors'] = ['Name and email are required'];
            $_SESSION['old_input'] = $request['post'];
            $this->redirect('/marketing/lead/capture');
            return;
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['Invalid email address'];
            $_SESSION['old_input'] = $request['post'];
            $this->redirect('/marketing/lead/capture');
            return;
        }

        $result = $this->marketingService->captureLead($name, $email, $phone, $source, $campaign);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/marketing/leads');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $request['post'];
            $this->redirect('/marketing/lead/capture');
        }

        return $result;
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $leadId = $request['params']['id'] ?? null;
        $status = $request['post']['status'] ?? null;

        if (!$leadId || !$status) {
            return [
                'success' => false,
                'message' => 'Lead ID and status are required'
            ];
        }

        $result = $this->marketingService->updateLeadStatus($leadId, $status);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect("/marketing/lead/$leadId");

        return $result;
    }

    /**
     * Assign lead score
     */
    public function assignLeadScore($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $leadId = $request['params']['id'] ?? null;
        $score = intval($request['post']['score'] ?? 0);

        if (!$leadId || $score <= 0) {
            return [
                'success' => false,
                'message' => 'Lead ID and valid score are required'
            ];
        }

        $result = $this->marketingService->assignLeadScore($leadId, $score);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect("/marketing/lead/$leadId");

        return $result;
    }

    /**
     * Show campaigns list
     */
    public function campaigns($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access campaigns'];
            $this->redirect('/login');
            return;
        }

        $filters = [
            'type' => $request['get']['type'] ?? null,
            'status' => $request['get']['status'] ?? null
        ];

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->marketingService->getCampaigns($filters, $limit, $offset);

        $data = [
            'title' => 'Marketing Campaigns - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'campaigns' => $result['success'] ? $result['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('marketing/campaigns', $data);
    }

    /**
     * Show create campaign form
     */
    public function createCampaign($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to create campaigns'];
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'Create Campaign - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'campaign_types' => ['email', 'sms', 'social', 'google', 'facebook'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('marketing/create_campaign', $data);
    }

    /**
     * Handle create campaign
     */
    public function handleCreateCampaign($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $name = trim($request['post']['name'] ?? '');
        $subject = trim($request['post']['subject'] ?? '');
        $content = trim($request['post']['content'] ?? '');
        $targetAudience = json_decode($request['post']['target_audience'] ?? '{}', true) ?? [];
        $scheduleAt = $request['post']['schedule_at'] ?? null;

        // Validate required fields
        if (empty($name) || empty($subject) || empty($content)) {
            $_SESSION['errors'] = ['Name, subject, and content are required'];
            $_SESSION['old_input'] = $request['post'];
            $this->redirect('/marketing/campaign/create');
            return;
        }

        $result = $this->marketingService->createEmailCampaign($name, $subject, $content, $targetAudience, $scheduleAt);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/marketing/campaigns');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $request['post'];
            $this->redirect('/marketing/campaign/create');
        }

        return $result;
    }

    /**
     * Get leads (AJAX)
     */
    public function getLeads($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'source' => $request['get']['source'] ?? null,
            'campaign' => $request['get']['campaign'] ?? null,
            'score_min' => $request['get']['score_min'] ?? null
        ];

        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $offset = max(0, intval($request['get']['offset'] ?? 0));

        return $this->marketingService->getLeads($filters, $limit, $offset);
    }

    /**
     * Get lead details (AJAX)
     */
    public function getLead($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $leadId = $request['get']['id'] ?? null;

        if (!$leadId) {
            return [
                'success' => false,
                'message' => 'Lead ID is required'
            ];
        }

        $lead = $this->marketingService->getLead($leadId);

        if ($lead) {
            return [
                'success' => true,
                'data' => $lead
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Lead not found'
            ];
        }
    }

    /**
     * Get campaigns (AJAX)
     */
    public function getCampaigns($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $filters = [
            'type' => $request['get']['type'] ?? null,
            'status' => $request['get']['status'] ?? null
        ];

        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $offset = max(0, intval($request['get']['offset'] ?? 0));

        return $this->marketingService->getCampaigns($filters, $limit, $offset);
    }

    /**
     * Get dashboard data (AJAX)
     */
    public function getDashboardData($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->marketingService->getDashboardData();
    }

    /**
     * Get lead statistics (AJAX)
     */
    public function getLeadStats($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->marketingService->getLeadStats();
    }

    /**
     * Capture lead (AJAX)
     */
    public function captureLeadAjax($request)
    {
        $name = trim($request['post']['name'] ?? '');
        $email = trim($request['post']['email'] ?? '');
        $phone = trim($request['post']['phone'] ?? '');
        $source = trim($request['post']['source'] ?? 'website');
        $campaign = trim($request['post']['campaign'] ?? '');

        // Validate required fields
        if (empty($name) || empty($email)) {
            return [
                'success' => false,
                'message' => 'Name and email are required'
            ];
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email address'
            ];
        }

        return $this->marketingService->captureLead($name, $email, $phone, $source, $campaign);
    }

    /**
     * Update lead status (AJAX)
     */
    public function updateLeadStatusAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $leadId = $request['post']['lead_id'] ?? null;
        $status = $request['post']['status'] ?? null;

        if (!$leadId || !$status) {
            return [
                'success' => false,
                'message' => 'Lead ID and status are required'
            ];
        }

        return $this->marketingService->updateLeadStatus($leadId, $status);
    }

    /**
     * Assign lead score (AJAX)
     */
    public function assignLeadScoreAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $leadId = $request['post']['lead_id'] ?? null;
        $score = intval($request['post']['score'] ?? 0);

        if (!$leadId || $score <= 0) {
            return [
                'success' => false,
                'message' => 'Lead ID and valid score are required'
            ];
        }

        return $this->marketingService->assignLeadScore($leadId, $score);
    }

    /**
     * Create campaign (AJAX)
     */
    public function createCampaignAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $name = trim($request['post']['name'] ?? '');
        $subject = trim($request['post']['subject'] ?? '');
        $content = trim($request['post']['content'] ?? '');
        $targetAudience = json_decode($request['post']['target_audience'] ?? '{}', true) ?? [];
        $scheduleAt = $request['post']['schedule_at'] ?? null;

        // Validate required fields
        if (empty($name) || empty($subject) || empty($content)) {
            return [
                'success' => false,
                'message' => 'Name, subject, and content are required'
            ];
        }

        return $this->marketingService->createEmailCampaign($name, $subject, $content, $targetAudience, $scheduleAt);
    }

    /**
     * Trigger automation (AJAX)
     */
    public function triggerAutomation($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $triggerType = $request['post']['trigger_type'] ?? null;
        $leadId = $request['post']['lead_id'] ?? null;

        if (!$triggerType || !$leadId) {
            return [
                'success' => false,
                'message' => 'Trigger type and lead ID are required'
            ];
        }

        return $this->marketingService->triggerAutomation($triggerType, $leadId);
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}
