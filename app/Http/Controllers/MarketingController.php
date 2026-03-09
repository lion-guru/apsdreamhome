<?php

namespace App\Http\Controllers;

use App\Services\Marketing\AutomationService;
use App\Models\MarketingLead;
use Psr\Log\LoggerInterface;

class MarketingController
{
    private AutomationService $marketingService;
    private LoggerInterface $logger;

    public function __construct(AutomationService $marketingService, LoggerInterface $logger)
    {
        $this->marketingService = $marketingService;
        $this->logger = $logger;
    }

    /**
     * Display marketing dashboard
     */
    public function dashboard()
    {
        try {
            $analytics = $this->marketingService->getAnalytics();
            
            return view('marketing.dashboard', [
                'analytics' => $analytics,
                'page_title' => 'Marketing Dashboard - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load marketing dashboard", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Create marketing campaign
     */
    public function createCampaign()
    {
        try {
            $data = request()->all();
            
            $result = $this->marketingService->createCampaign(
                $data['name'],
                $data['type'],
                $data['config'] ?? [],
                $data['segments'] ?? []
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'campaign_id' => $result['campaign_id']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to create campaign", ['error' => $e->getMessage()]);
            return response()->json([
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
            $result = $this->marketingService->executeCampaign((int)$id);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'results' => $result['results']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to execute campaign", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
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
            $data = request()->all();
            $tags = request()->input('tags', []);

            $result = $this->marketingService->addLead($data, $tags);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'lead_id' => $result['lead_id'],
                    'score' => $result['score'] ?? 0
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to add lead", ['error' => $e->getMessage()]);
            return response()->json([
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
            $lead = $this->marketingService->getLead((int)$id);
            
            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'lead' => $lead
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get lead", ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
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
            $status = request()->input('status');
            $reason = request()->input('reason', '');

            $result = $this->marketingService->updateLeadStatus((int)$id, $status, $reason);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to update lead status", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
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
            $result = $this->marketingService->processWorkflows();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'processed' => $result['processed'],
                    'triggered' => $result['triggered'],
                    'errors' => $result['errors']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to process workflows", ['error' => $e->getMessage()]);
            return response()->json([
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
            $filters = request()->all();
            $analytics = $this->marketingService->getAnalytics($filters);

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get analytics", ['error' => $e->getMessage()]);
            return response()->json([
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
            $filters = request()->all();
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
                $leads->where(function($query) use ($searchTerm) {
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
            $leadData = $results->map(function($lead) {
                return $lead->getSummary();
            });

            return response()->json([
                'success' => true,
                'leads' => $leadData,
                'total' => count($leadData)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get leads", ['error' => $e->getMessage()]);
            return response()->json([
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
            $leads = MarketingLead::all();
            
            $scoringData = [
                'total_leads' => $leads->count(),
                'average_score' => $leads->avg('score'),
                'score_distribution' => [
                    'high' => $leads->where('score', '>=', 80)->count(),
                    'medium' => $leads->whereBetween('score', [60, 79])->count(),
                    'low' => $leads->where('score', '<', 60)->count()
                ],
                'status_distribution' => $leads->groupBy('status')->map->count(),
                'conversion_probability' => [
                    'high' => $leads->filter(fn($lead) => $lead->getConversionProbability() >= 0.7)->count(),
                    'medium' => $leads->filter(fn($lead) => $lead->getConversionProbability() >= 0.4 && $lead->getConversionProbability() < 0.7)->count(),
                    'low' => $leads->filter(fn($lead) => $lead->getConversionProbability() < 0.4)->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'scoring_data' => $scoringData
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get lead scoring", ['error' => $e->getMessage()]);
            return response()->json([
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
            $filters = request()->all();
            
            // Get leads with same logic as getLeads
            $leads = MarketingLead::query();

            // Apply same filters as getLeads method
            if (!empty($filters['status'])) {
                $leads->where('status', $filters['status']);
            }

            if (!empty($filters['date_from'])) {
                $leads->where('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $leads->where('created_at', '<=', $filters['date_to']);
            }

            $results = $leads->orderBy('created_at', 'desc')->get();

            $csvData = [];
            $csvData[] = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Position', 'Source', 'Status', 'Score', 'Created Date'];

            foreach ($results as $lead) {
                $csvData[] = [
                    $lead->id,
                    $lead->first_name,
                    $lead->last_name,
                    $lead->email,
                    $lead->phone ?? '',
                    $lead->company ?? '',
                    $lead->position ?? '',
                    $lead->source ?? '',
                    $lead->status,
                    $lead->score,
                    $lead->created_at
                ];
            }

            $filename = 'marketing_leads_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);

        } catch (\Exception $e) {
            $this->logger->error("Failed to export leads", ['error' => $e->getMessage()]);
            return response()->json([
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
            $campaignId = request()->input('campaign_id');
            
            if ($campaignId) {
                // Get specific campaign performance
                $sql = "SELECT cl.*, l.first_name, l.last_name, l.email 
                        FROM campaign_leads cl 
                        JOIN marketing_leads l ON cl.lead_id = l.id 
                        WHERE cl.campaign_id = ? 
                        ORDER BY cl.created_at DESC";
                
                $performance = $this->marketingService->getDb()->fetchAll($sql, [$campaignId]);
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
                
                $performance = $this->marketingService->getDb()->fetchAll($sql);
            }

            return response()->json([
                'success' => true,
                'performance' => $performance
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get campaign performance", ['error' => $e->getMessage()]);
            return response()->json([
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
            return view('marketing.settings', [
                'page_title' => 'Marketing Settings - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load marketing settings", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }
}
