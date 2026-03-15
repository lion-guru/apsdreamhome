<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;
use App\Services\Marketing\MarketingAutomationService;
use App\Models\MarketingLead;
use App\Services\SystemLogger as Logger;

class MarketingController extends BaseController
{
    private MarketingAutomationService $marketingService;
    private $logger;

    public function __construct(MarketingAutomationService $marketingService, Logger $logger)
    {
        parent::__construct();
        $this->marketingService = $marketingService;
        $this->logger = $logger;
    }

    /**
     * Display marketing dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->marketingService->getDashboardData();

            return $this->view('marketing.dashboard', [
                'stats' => $stats,
                'page_title' => 'Marketing Dashboard - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to load marketing dashboard", ['error' => $e->getMessage()]);
            return $this->view('errors.500');
        }
    }

    /**
     * Create marketing campaign
     */
    public function createCampaign()
    {
        try {
            $data = $_REQUEST;

            $campaign = $this->marketingService->createEmailCampaign($data['name'], $data['subject'], $data['content'], $data['target_audience'], $data['schedule_at'] ?? null);

            if ($campaign) {
                return $this->response([
                    'success' => true,
                    'message' => 'Campaign created successfully',
                    'campaign' => $campaign
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Failed to create campaign'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to create campaign", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to create campaign'
            ], 500);
        }
    }

    /**
     * Execute marketing campaign
     */
    public function executeCampaign($id)
    {
        try {
            $data = $_REQUEST;

            $result = $this->marketingService->triggerAutomation('campaign_execute', $id);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message'],
                    'results' => $result['results']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to execute campaign", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to execute campaign'
            ], 500);
        }
    }

    /**
     * Add lead to marketing system
     */
    public function addLead()
    {
        try {
            $data = $_REQUEST;
            $tags = isset($_REQUEST['tags']) ? $_REQUEST['tags'] : [];

            $result = $this->marketingService->captureLead($data['name'], $data['email'], $data['phone'], $data['source'] ?? 'website', $data['campaign'] ?? '');

            if ($result) {
                return $this->response([
                    'success' => true,
                    'message' => 'Lead added successfully',
                    'lead_id' => $result,
                    'score' => 0
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Failed to add lead'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to add lead", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to add lead'
            ], 500);
        }
    }

    /**
     * Get lead details
     */
    public function getLead($id)
    {
        try {
            $lead = $this->marketingService->getLead($id);

            if ($lead) {
                return $this->response([
                    'success' => true,
                    'lead' => $lead
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to get lead", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get lead'
            ], 500);
        }
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($id)
    {
        try {
            $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;
            $reason = isset($_REQUEST['reason']) ? $_REQUEST['reason'] : '';

            $result = $this->marketingService->updateLeadStatus($id, $status, $reason);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to update lead status", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Process marketing workflows
     */
    public function processWorkflows()
    {
        try {
            $data = $_REQUEST;
            $result = $this->marketingService->triggerAutomation('manual_workflow', $data['lead_id']);

            if ($result['success']) {
                return $this->response([
                    'success' => true,
                    'message' => $result['message'],
                    'processed' => $result['processed'],
                    'triggered' => $result['triggered'],
                    'errors' => $result['errors']
                ]);
            } else {
                return $this->response([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to process workflows", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to process workflows'
            ], 500);
        }
    }

    /**
     * Get marketing analytics
     */
    public function getAnalytics()
    {
        try {
            $filters = $_REQUEST;
            $stats = $this->marketingService->getDashboardData($filters);

            return $this->response([
                'success' => true,
                'analytics' => $stats,
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get analytics", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get analytics'
            ], 500);
        }
    }

    /**
     * Get leads list
     */
    public function getLeads()
    {
        try {
            $filters = $_REQUEST;
            $leads = MarketingLead::query();

            // Apply filters
            if (!empty($filters['status'])) {
                $leads->where('status', $filters['status']);
            }

            if (!empty($filters['source'])) {
                $leads->where('source', $filters['source']);
            }

            if (!empty($filters['score_min'])) {
                $leads->where('score', '>=', $filters['score_min']);
            }

            if (!empty($filters['score_max'])) {
                $leads->where('score', '<=', $filters['score_max']);
            }

            if (!empty($filters['date_from'])) {
                $leads->where('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $leads->where('created_at', '<=', $filters['date_to']);
            }

            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $leads->where(function ($query) use ($searchTerm) {
                    $query->where('first_name', 'LIKE', $searchTerm)
                        ->orWhere('last_name', 'LIKE', $searchTerm)
                        ->orWhere('email', 'LIKE', $searchTerm)
                        ->orWhere('company', 'LIKE', $searchTerm);
                });
            }

            // Order and paginate
            $leads->orderBy('created_at', 'desc');

            if (!empty($filters['limit'])) {
                $leads->limit((int)$filters['limit']);
            }

            $results = $leads->get();

            // Transform to include summary data
            $leadData = $results->map(function ($lead) {
                return $lead->getSummary();
            });

            return $this->response([
                'success' => true,
                'leads' => $leadData,
                'total' => count($leadData)
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get leads", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get leads'
            ], 500);
        }
    }

    /**
     * Get lead scoring insights
     */
    public function getLeadScoring()
    {
        try {
            // Get all leads using database query
            $leads = Database::getInstance()->fetchAll("SELECT * FROM marketing_leads");

            $totalLeads = count($leads);
            $totalScore = array_sum(array_column($leads, 'score'));
            $averageScore = $totalLeads > 0 ? $totalScore / $totalLeads : 0;

            // Count score distributions
            $highScore = 0;
            $mediumScore = 0;
            $lowScore = 0;

            foreach ($leads as $lead) {
                if ($lead['score'] >= 80) $highScore++;
                elseif ($lead['score'] >= 60) $mediumScore++;
                else $lowScore++;
            }

            // Group by status
            $statusDistribution = [];
            foreach ($leads as $lead) {
                $status = $lead['status'];
                $statusDistribution[$status] = ($statusDistribution[$status] ?? 0) + 1;
            }

            $scoringData = [
                'total_leads' => $totalLeads,
                'average_score' => $averageScore,
                'score_distribution' => [
                    'high' => $highScore,
                    'medium' => $mediumScore,
                    'low' => $lowScore
                ],
                'status_distribution' => $statusDistribution,
                'conversion_probability' => [
                    'high' => $highScore, // Simplified logic
                    'medium' => $mediumScore,
                    'low' => $lowScore
                ]
            ];

            return $this->response([
                'success' => true,
                'scoring_data' => $scoringData
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get lead scoring", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get lead scoring'
            ], 500);
        }
    }

    /**
     * Export leads to CSV
     */
    public function exportLeads()
    {
        try {
            $filters = $_REQUEST;

            // Get leads with same logic as getLeads
            $sql = "SELECT * FROM marketing_leads WHERE 1=1";
            $params = [];

            // Apply same filters as getLeads method
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND created_at <= ?";
                $params[] = $filters['date_to'];
            }

            if (!empty($filters['source'])) {
                $sql .= " AND source = ?";
                $params[] = $filters['source'];
            }

            $sql .= " ORDER BY created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $results = Database::getInstance()->fetchAll($sql, $params);

            $csvData = [];
            $csvData[] = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Position', 'Source', 'Status', 'Score', 'Created Date'];

            foreach ($results as $lead) {
                $csvData[] = [
                    $lead['id'],
                    $lead['first_name'],
                    $lead['last_name'],
                    $lead['email'],
                    $lead['phone'] ?? '',
                    $lead['company'] ?? '',
                    $lead['position'] ?? '',
                    $lead['source'] ?? '',
                    $lead['status'],
                    $lead['score'],
                    $lead['created_at']
                ];
            }

            $filename = 'leads_export_' . date('Y-m-d_H-i-s') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        } catch (\Exception $e) {
            $this->logger->error("Failed to export leads", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to export leads'
            ], 500);
        }
    }

    /**
     * Get campaign performance
     */
    public function getCampaignPerformance()
    {
        try {
            $campaignId = isset($_REQUEST['campaign_id']) ? $_REQUEST['campaign_id'] : null;

            if ($campaignId) {
                // Get specific campaign performance
                $sql = "SELECT cl.*, l.first_name, l.last_name, l.email 
                        FROM campaign_leads cl 
                        JOIN marketing_leads l ON cl.lead_id = l.id 
                        WHERE cl.campaign_id = ? 
                        ORDER BY cl.created_at DESC";

                $performance = Database::getInstance()->fetchAll($sql, [$campaignId]);
            } else {
                // Get overall performance
                $sql = "SELECT c.name, c.type, 
                               COUNT(cl.id) as total_leads,
                               SUM(CASE WHEN cl.status = 'sent' THEN 1 ELSE 0 END) as sent,
                               SUM(CASE WHEN cl.status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                               SUM(CASE WHEN cl.status = 'opened' THEN 1 ELSE 0 END) as opened,
                               SUM(CASE WHEN cl.status = 'clicked' THEN 1 ELSE 0 END) as clicked,
                               SUM(CASE WHEN cl.status = 'converted' THEN 1 ELSE 0 END) as converted
                        FROM marketing_campaigns c 
                        LEFT JOIN campaign_leads cl ON c.id = cl.campaign_id 
                        GROUP BY c.id, c.name, c.type 
                        ORDER BY c.created_at DESC";

                $performance = Database::getInstance()->fetchAll($sql);
            }

            return $this->response([
                'success' => true,
                'performance' => $performance
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get campaign performance", ['error' => $e->getMessage()]);
            return $this->response([
                'success' => false,
                'message' => 'Failed to get campaign performance'
            ], 500);
        }
    }

    /**
     * Marketing automation settings page
     */
    public function settings()
    {
        try {
            return $this->view('marketing.settings', [
                'page_title' => 'Marketing Settings - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to load marketing settings", ['error' => $e->getMessage()]);
            return $this->view('errors.500');
        }
    }
}
