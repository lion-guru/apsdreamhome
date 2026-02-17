<?php
/**
 * APS Dream Home - SelForce Style CRM System Integration
 * Complete integration with existing real estate and colonizer systems
 */

require_once __DIR__ . '/core/init.php';

use App\Services\LeadService;

class SelForceCRMSystem {
    private $db;
    private $authManager;
    private $propertyManager;
    private $propertyAI;
    private $emailManager;
    private $apiManager;
    private $asyncTaskManager;
    private $adminDashboard;
    private $plottingManager;
    private $mlmCommissionManager;
    private $farmerManager;
    private $salaryManager;
    private $leadService;
    private $crmAnalyticsManager;
    private $notificationManager;
    private $logger;

    public function __construct($logger = null) {
        $this->logger = $logger;
        $this->initializeSystem();
    }

    /**
     * Initialize all system components
     */
    private function initializeSystem() {
        // Initialize database connection
        $this->db = \App\Core\App::database();

        // Initialize all managers using the singleton (many classes now support null or singleton internally)
        $this->authManager = new AuthManager(null, $this->logger);
        $this->propertyManager = new PropertyManager(null, $this->logger);
        $this->propertyAI = new PropertyAI(null);
        $this->emailManager = new EmailService();
        $this->apiManager = new ApiKeyManager(null, $this->logger);
        $this->asyncTaskManager = new AsyncTaskManager(null, $this->logger);
        $this->adminDashboard = new AdminDashboard(null, $this->logger);

        // Initialize colonizer-specific managers
        $this->plottingManager = new PlottingManager(null, $this->logger);
        $this->mlmCommissionManager = new MLMCommissionManager(null, $this->logger);
        $this->farmerManager = new FarmerManager(null, $this->logger);
        $this->salaryManager = new SalaryManager(null, $this->logger);

        // Initialize CRM system
        $this->leadService = new LeadService();
        $this->crmAnalyticsManager = new CRMAnalyticsManager(null, $this->logger);
        $this->notificationManager = new NotificationManager(null, $this->emailManager);

        if ($this->logger) {
            $this->logger->log("SelForce CRM System initialized successfully", 'info', 'crm');
        }
    }

    // ==================== CRM LEAD MANAGEMENT ====================

    /**
     * Add new lead with automatic integration
     */
    public function addLeadWithIntegration($leadData) {
        // Handle name if first_name/last_name are provided instead of name
        if (empty($leadData['name'])) {
            $firstName = $leadData['first_name'] ?? '';
            $lastName = $leadData['last_name'] ?? '';
            $leadData['name'] = trim($firstName . ' ' . $lastName);
            if (empty($leadData['name'])) {
                $leadData['name'] = 'New Lead';
            }
        }

        // Add lead to CRM
        $leadId = $this->leadService->createLead($leadData);

        if ($leadId) {
            $leadName = $leadData['name'];
            
            // Check if this lead is interested in plots
            if (!empty($leadData['property_interest']) && stripos($leadData['property_interest'], 'plot') !== false) {
                // Get available plots matching criteria
                $availablePlots = $this->plottingManager->getPlots([
                    'plot_status' => 'available',
                    'colony_name' => $leadData['preferred_location'] ?? ''
                ], 5);

                if (!empty($availablePlots)) {
                    // Create opportunity for plot sale
                    $opportunityData = [
                        'lead_id' => $leadId,
                        'opportunity_title' => 'Plot Purchase - ' . $leadName,
                        'opportunity_type' => 'plot_sale',
                        'expected_value' => $availablePlots[0]['current_price'] ?? 0,
                        'assigned_to' => $leadData['assigned_to'] ?? 1,
                        'created_by' => $leadData['created_by'] ?? 1
                    ];

                    $this->leadService->createOpportunity($opportunityData);
                }
            }

            // Check if this lead is interested in properties
            if (!empty($leadData['property_interest']) && stripos($leadData['property_interest'], 'property') !== false) {
                // Get available properties matching criteria
                $availableProperties = $this->propertyManager->getProperties([
                    'status' => 'available',
                    'location' => $leadData['preferred_location'] ?? '',
                    'price_min' => $leadData['budget_min'] ?? 0,
                    'price_max' => $leadData['budget_max'] ?? 999999999
                ], 5);

                if (!empty($availableProperties)) {
                    // Create opportunity for property sale
                    $opportunityData = [
                        'lead_id' => $leadId,
                        'opportunity_title' => 'Property Purchase - ' . $leadName,
                        'opportunity_type' => 'property_sale',
                        'expected_value' => $availableProperties[0]['price'] ?? 0,
                        'assigned_to' => $leadData['assigned_to'] ?? 1,
                        'created_by' => $leadData['created_by'] ?? 1
                    ];

                    $this->leadService->createOpportunity($opportunityData);
                }
            }

            // Schedule follow-up
            $followUpData = [
                'lead_id' => $leadId,
                'activity_type' => 'call',
                'activity_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
                'subject' => 'Initial Follow-up Call',
                'description' => 'Follow up with lead regarding their inquiry',
                'created_by' => $leadData['created_by'] ?? 1
            ];

            $this->leadService->addLeadActivity($followUpData);
        }

        return $leadId;
    }

    /**
     * Convert lead to customer with booking integration
     */
    public function convertLeadToCustomerWithBooking($leadId, $bookingData) {
        // Convert lead to customer
        $customerId = $this->leadService->convertToCustomer($leadId);

        if ($customerId) {
            // Create booking based on opportunity type
            if ($bookingData['booking_type'] === 'plot_booking') {
                $bookingId = $this->plottingManager->bookPlot($bookingData);
                if ($bookingId) {
                    // Calculate commissions
                    $this->mlmCommissionManager->calculateBookingCommission($bookingId);

                    // Send confirmation email
                    $this->sendBookingConfirmation($bookingId);
                }
            } elseif ($bookingData['booking_type'] === 'property_booking') {
                // Handle property booking
                $propertyBookingData = [
                    'property_id' => $bookingData['property_id'],
                    'user_id' => $customerId,
                    'booking_type' => 'purchase',
                    'message' => $bookingData['message'] ?? 'Property booking from lead conversion'
                ];

                // Use existing booking system
                $propertyBookingId = $this->createPropertyBooking($propertyBookingData);
            }

            if ($this->logger) {
                $this->logger->log("Lead converted to customer with booking: Lead ID $leadId, Customer ID $customerId", 'info', 'crm');
            }
        }

        return $customerId;
    }

    /**
     * Create property booking
     */
    private function createPropertyBooking($bookingData) {
        return $this->leadService->createPropertyBooking($bookingData);
    }

    // ==================== CRM ANALYTICS INTEGRATION ====================

    /**
     * Get comprehensive CRM dashboard
     */
    public function getCRMDashboard() {
        $dashboard = [];

        // CRM Analytics
        $dashboard['crm_analytics'] = $this->crmAnalyticsManager->getComprehensiveAnalytics();

        // Integration with existing systems
        $dashboard['property_stats'] = $this->propertyManager->getProperties(['status' => 'available'], 0, 0);
        $dashboard['plot_stats'] = $this->plottingManager->getPlottingStats();
        $dashboard['farmer_stats'] = $this->farmerManager->getFarmerStats();

        // Lead to Customer Conversion
        $dashboard['conversion_metrics'] = [
            'lead_to_customer_rate' => $this->getLeadToCustomerConversionRate(),
            'opportunity_to_sale_rate' => $this->getOpportunityToSaleConversionRate(),
            'average_sales_cycle' => $this->getAverageSalesCycle()
        ];

        return $dashboard;
    }

    /**
     * Get lead to customer conversion rate
     */
    private function getLeadToCustomerConversionRate() {
        return $this->leadService->getLeadToCustomerConversionRate();
    }

    /**
     * Get opportunity to sale conversion rate
     */
    private function getOpportunityToSaleConversionRate() {
        return $this->leadService->getOpportunityToSaleConversionRate();
    }

    /**
     * Get average sales cycle
     */
    private function getAverageSalesCycle() {
        return $this->leadService->getAverageSalesCycle();
    }

    // ==================== AUTOMATED WORKFLOWS ====================

    /**
     * Automated lead nurturing workflow
     */
    public function processLeadNurturing($leadId) {
        $lead = $this->leadService->getLead($leadId);

        if (!$lead) return false;

        // Determine nurturing sequence based on lead score and status
        $nurturingSequence = $this->getNurturingSequence($lead);

        foreach ($nurturingSequence as $step) {
            $this->scheduleNurturingActivity($leadId, $step);
        }

        return true;
    }

    /**
     * Get nurturing sequence based on lead data
     */
    private function getNurturingSequence($lead) {
        $sequence = [];

        // Day 1: Welcome email
        $sequence[] = [
            'type' => 'email',
            'template' => 'LEAD_WELCOME_CUSTOMER',
            'delay_days' => 0,
            'subject' => 'Welcome to APS Dream Home'
        ];

        // Day 2: Follow-up call
        $sequence[] = [
            'type' => 'call',
            'template' => 'follow_up_call',
            'delay_days' => 2,
            'subject' => 'Follow-up Call'
        ];

        // Day 7: Property/Plot recommendations
        if ($lead['lead_score'] >= 50) {
            $sequence[] = [
                'type' => 'email',
                'template' => 'PROPERTY_RECOMMENDATIONS',
                'delay_days' => 7,
                'subject' => 'Recommended Properties for You'
            ];
        }

        // Day 14: Special offer
        $sequence[] = [
            'type' => 'email',
            'template' => 'SPECIAL_OFFER',
            'delay_days' => 14,
            'subject' => 'Exclusive Offer Just for You'
        ];

        return $sequence;
    }

    /**
     * Schedule nurturing activity
     */
    private function scheduleNurturingActivity($leadId, $activity) {
        $activityDate = date('Y-m-d H:i:s', strtotime("+{$activity['delay_days']} days"));

        $activityData = [
            'lead_id' => $leadId,
            'activity_type' => $activity['type'] === 'email' ? 'email' : 'call',
            'activity_date' => $activityDate,
            'subject' => $activity['subject'],
            'description' => "Automated {$activity['type']} from nurturing sequence",
            'created_by' => 1 // System user
        ];

        $this->leadService->addLeadActivity($activityData);

        // Schedule actual email sending if it's an email
        if ($activity['type'] === 'email') {
            $this->asyncTaskManager->createTask(
                'send_nurturing_email',
                'email',
                [
                    'lead_id' => $leadId,
                    'template' => $activity['template'],
                    'subject' => $activity['subject']
                ]
            );
        }
    }

    /**
     * Process automated email sending
     */
    public function processNurturingEmail($taskData) {
        $lead = $this->leadService->getLead($taskData['lead_id']);

        if ($lead && $lead['email']) {
            $emailData = [
                'name' => $lead['first_name'],
                'email' => $lead['email'],
                'property_interest' => $lead['property_interest'],
                'budget_range' => ($lead['budget_min'] ?? 'N/A') . ' - ' . ($lead['budget_max'] ?? 'N/A')
            ];

            $this->notificationManager->send([
                'email' => $lead['email'],
                'template' => $taskData['template'],
                'data' => $emailData,
                'channels' => ['email']
            ]);

            // Mark activity as completed
            $this->leadService->addLeadActivity([
                'lead_id' => $taskData['lead_id'],
                'activity_type' => 'email',
                'activity_date' => date('Y-m-d H:i:s'),
                'subject' => $taskData['subject'],
                'description' => 'Automated nurturing email sent',
                'created_by' => 1
            ]);
        }
    }

    // ==================== CUSTOMER SUPPORT INTEGRATION ====================

    /**
     * Create integrated support ticket
     */
    public function createIntegratedSupportTicket($ticketData) {
        $ticketId = $this->leadService->createSupportTicket($ticketData);

        if ($ticketId) {
            // Check if it's property/plot related
            if ($ticketData['ticket_type'] === 'property' && !empty($ticketData['property_id'])) {
                // Link to property
                $this->linkTicketToProperty($ticketId, $ticketData['property_id']);
            } elseif ($ticketData['ticket_type'] === 'plot' && !empty($ticketData['plot_id'])) {
                // Link to plot
                $this->linkTicketToPlot($ticketId, $ticketData['plot_id']);
            }

            // Auto-assign based on type
            $this->autoAssignTicket($ticketId, $ticketData['ticket_type']);
        }

        return $ticketId;
    }

    /**
     * Link ticket to property
     */
    private function linkTicketToProperty($ticketId, $propertyId) {
        return $this->leadService->linkTicketToProperty($ticketId, $propertyId);
    }

    /**
     * Link ticket to plot
     */
    private function linkTicketToPlot($ticketId, $plotId) {
        return $this->leadService->linkTicketToPlot($ticketId, $plotId);
    }

    /**
     * Auto-assign ticket based on type
     */
    private function autoAssignTicket($ticketId, $ticketType) {
        return $this->leadService->autoAssignTicket($ticketId, $ticketType);
    }

    // ==================== REPORTING & ANALYTICS ====================

    /**
     * Generate comprehensive business report
     */
    public function generateBusinessReport($reportType, $dateRange = []) {
        $report = [
            'title' => 'Comprehensive Business Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'report_type' => $reportType,
            'date_range' => $dateRange
        ];

        switch ($reportType) {
            case 'sales_performance':
                $report['data'] = $this->generateSalesPerformanceReport($dateRange);
                break;
            case 'lead_conversion':
                $report['data'] = $this->generateLeadConversionReport($dateRange);
                break;
            case 'customer_lifecycle':
                $report['data'] = $this->generateCustomerLifecycleReport($dateRange);
                break;
            case 'revenue_analysis':
                $report['data'] = $this->generateRevenueAnalysisReport($dateRange);
                break;
            default:
                $report['data'] = $this->crmAnalyticsManager->getComprehensiveAnalytics($dateRange);
        }

        return $report;
    }

    /**
     * Generate sales performance report
     */
    private function generateSalesPerformanceReport($dateRange) {
        $analytics = $this->crmAnalyticsManager->getComprehensiveAnalytics($dateRange);
        
        return [
            'sales_by_source' => $analytics['lead_analytics']['lead_sources_performance'] ?? [],
            'monthly_trend' => $analytics['sales_analytics']['monthly_trends'] ?? []
        ];
    }

    /**
     * Generate lead conversion report
     */
    private function generateLeadConversionReport($dateRange) {
        $analytics = $this->crmAnalyticsManager->getComprehensiveAnalytics($dateRange);
        
        return [
            'conversion_funnel' => $analytics['lead_analytics']['conversion_funnel'] ?? [],
            'source_conversion' => $analytics['lead_analytics']['lead_sources_performance'] ?? []
        ];
    }

    /**
     * Generate customer lifecycle report
     */
    private function generateCustomerLifecycleReport($dateRange) {
        $analytics = $this->crmAnalyticsManager->getComprehensiveAnalytics($dateRange);
        
        return [
            'customer_acquisition' => $analytics['customer_analytics']['customer_acquisition'] ?? [],
            'customer_retention' => $analytics['customer_analytics']['repeat_customers'] ?? []
        ];
    }

    /**
     * Generate revenue analysis report
     */
    private function generateRevenueAnalysisReport($dateRange) {
        $analytics = $this->crmAnalyticsManager->getComprehensiveAnalytics($dateRange);
        
        return [
            'revenue_by_source' => $analytics['sales_analytics']['pipeline_analysis'] ?? [],
            'monthly_revenue' => $analytics['sales_analytics']['monthly_trends'] ?? []
        ];
    }

    // ==================== UTILITY FUNCTIONS ====================

    /**
     * Send booking confirmation
     */
    private function sendBookingConfirmation($bookingId) {
        $booking = $this->plottingManager->getPlotBooking($bookingId);

        if ($booking && $booking['customer_email']) {
            $data = [
                'name' => $booking['customer_name'],
                'plot_number' => $booking['plot_number'],
                'colony_name' => $booking['colony_name'],
                'booking_amount' => $booking['booking_amount'],
                'total_amount' => $booking['total_amount'],
                'booking_date' => $booking['booking_date']
            ];

            $this->notificationManager->send([
                'email' => $booking['customer_email'],
                'template' => 'BOOKING_CONFIRMATION',
                'data' => $data,
                'channels' => ['email', 'sms']
            ]);
        }
    }

    /**
     * Get system status
     */
    public function getSystemStatus() {
        return [
            'crm_system' => 'Active',
            'modules' => [
                'Lead Management' => 'Active',
                'Opportunity Tracking' => 'Active',
                'Customer Management' => 'Active',
                'Support System' => 'Active',
                'Analytics' => 'Active',
                'Integration' => 'Active'
            ],
            'integrations' => [
                'Property Management' => 'Connected',
                'Plot Management' => 'Connected',
                'Farmer Management' => 'Connected',
                'Commission System' => 'Connected',
                'Email System' => 'Connected'
            ]
        ];
    }

    /**
     * Get quick stats
     */
    public function getQuickStats() {
        return [
            'total_leads' => $this->crmAnalyticsManager->getTotalCount('leads'),
            'total_opportunities' => $this->crmAnalyticsManager->getTotalCount('opportunities'),
            'total_customers' => $this->crmAnalyticsManager->getTotalCount('customer_profiles'),
            'total_revenue' => $this->crmAnalyticsManager->getTotalRevenue(),
            'conversion_rate' => $this->crmAnalyticsManager->getConversionRate(),
            'active_campaigns' => $this->getActiveCampaignsCount()
        ];
    }

    /**
     * Get active campaigns count
     */
    private function getActiveCampaignsCount() {
        $row = $this->db->fetch("SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'");
        return $row['count'] ?? 0;
    }
}

// ==================== UTILITY FUNCTIONS ====================

/**
 * Initialize the SelForce CRM system
 */
function initializeSelForceCRM() {
    return new SelForceCRMSystem();
}

/**
 * Quick CRM dashboard
 */
function getCRMDashboard() {
    $crm = new SelForceCRMSystem();
    return $crm->getCRMDashboard();
}

/**
 * Add lead with integration
 */
// ==================== UI RENDERING (If accessed directly) ====================

if (basename($_SERVER['PHP_SELF']) == 'selforce_crm_system.php') {
    $crm = new SelForceCRMSystem();
    $status = $crm->getSystemStatus();
    
    // Simple HTML output
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>SelForce CRM System Status</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background: #f4f7f6; padding: 20px; }
            .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .status-active { color: #28a745; font-weight: bold; }
            .status-connected { color: #007bff; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1 class='mb-4'>SelForce CRM System Status</h1>
            
            <div class='row'>
                <div class='col-md-6'>
                    <div class='card'>
                        <div class='card-header'>Core Modules</div>
                        <div class='card-body'>
                            <ul class='list-group list-group-flush'>";
    foreach ($status['modules'] as $module => $modStatus) {
        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                $module
                <span class='status-active'>$modStatus</span>
              </li>";
    }
    echo "          </ul>
                        </div>
                    </div>
                </div>
                
                <div class='col-md-6'>
                    <div class='card'>
                        <div class='card-header'>Integrations</div>
                        <div class='card-body'>
                            <ul class='list-group list-group-flush'>";
    foreach ($status['integrations'] as $integration => $intStatus) {
        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                $integration
                <span class='status-connected'>$intStatus</span>
              </li>";
    }
    echo "          </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class='text-center mt-4'>
                <a href='index.php' class='btn btn-primary'>Back to Admin Dashboard</a>
            </div>
        </div>
    </body>
    </html>";
}
?>

