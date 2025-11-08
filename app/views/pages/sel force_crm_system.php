<?php
/**
 * APS Dream Home - SelForce Style CRM System Integration
 * Complete integration with existing real estate and colonizer systems
 */

require_once 'Database.php';
require_once 'AuthManager.php';
require_once 'PropertyManager.php';
require_once 'PropertyAI.php';
require_once 'EmailTemplateManager.php';
require_once 'ApiKeyManager.php';
require_once 'AsyncTaskManager.php';
require_once 'AdminDashboard.php';
require_once 'PlottingManager.php';
require_once 'MLMCommissionManager.php';
require_once 'FarmerManager.php';
require_once 'SalaryManager.php';
require_once 'CRMManager.php';
require_once 'CRMAnalyticsManager.php';

class SelForceCRMSystem {
    private $conn;
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
    private $crmManager;
    private $crmAnalyticsManager;
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
        $this->db = new Database();
        $this->conn = $this->db->getConnection();

        // Initialize all managers
        $this->authManager = new AuthManager($this->conn, $this->logger);
        $this->propertyManager = new PropertyManager($this->conn, $this->logger);
        $this->propertyAI = new PropertyAI($this->conn);
        $this->emailManager = new EmailTemplateManager($this->conn, $this->logger);
        $this->apiManager = new ApiKeyManager($this->conn, $this->logger);
        $this->asyncTaskManager = new AsyncTaskManager($this->conn, $this->logger);
        $this->adminDashboard = new AdminDashboard($this->conn, $this->logger);

        // Initialize colonizer-specific managers
        $this->plottingManager = new PlottingManager($this->conn, $this->logger);
        $this->mlmCommissionManager = new MLMCommissionManager($this->conn, $this->logger);
        $this->farmerManager = new FarmerManager($this->conn, $this->logger);
        $this->salaryManager = new SalaryManager($this->conn, $this->logger);

        // Initialize CRM system
        $this->crmManager = new CRMManager($this->conn, $this->logger);
        $this->crmAnalyticsManager = new CRMAnalyticsManager($this->conn, $this->logger);

        if ($this->logger) {
            $this->logger->log("SelForce CRM System initialized successfully", 'info', 'crm');
        }
    }

    // ==================== CRM LEAD MANAGEMENT ====================

    /**
     * Add new lead with automatic integration
     */
    public function addLeadWithIntegration($leadData) {
        // Generate lead number
        $leadData['lead_number'] = $this->crmManager->generateLeadNumber();

        // Add lead to CRM
        $leadId = $this->crmManager->addLead($leadData);

        if ($leadId) {
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
                        'opportunity_number' => $this->crmManager->generateOpportunityNumber(),
                        'lead_id' => $leadId,
                        'opportunity_title' => 'Plot Purchase - ' . ($leadData['first_name'] ?? 'Lead'),
                        'opportunity_type' => 'plot_sale',
                        'expected_value' => $availablePlots[0]['current_price'] ?? 0,
                        'assigned_to' => $leadData['assigned_to'] ?? 1,
                        'created_by' => $leadData['created_by'] ?? 1
                    ];

                    $this->crmManager->createOpportunity($opportunityData);
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
                        'opportunity_number' => $this->crmManager->generateOpportunityNumber(),
                        'lead_id' => $leadId,
                        'opportunity_title' => 'Property Purchase - ' . ($leadData['first_name'] ?? 'Lead'),
                        'opportunity_type' => 'property_sale',
                        'expected_value' => $availableProperties[0]['price'] ?? 0,
                        'assigned_to' => $leadData['assigned_to'] ?? 1,
                        'created_by' => $leadData['created_by'] ?? 1
                    ];

                    $this->crmManager->createOpportunity($opportunityData);
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

            $this->crmManager->addLeadActivity($followUpData);
        }

        return $leadId;
    }

    /**
     * Convert lead to customer with booking integration
     */
    public function convertLeadToCustomerWithBooking($leadId, $bookingData) {
        // Convert lead to customer
        $customerId = $this->crmManager->convertLeadToCustomer($leadId);

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
        $sql = "INSERT INTO bookings (property_id, user_id, booking_type, message, status)
                VALUES (?, ?, ?, ?, 'pending')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $bookingData['property_id'], $bookingData['user_id'],
                         $bookingData['booking_type'], $bookingData['message']);

        $result = $stmt->execute();
        $bookingId = $stmt->insert_id;
        $stmt->close();

        return $result ? $bookingId : false;
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
        $sql = "SELECT
            COUNT(*) as total_leads,
            COUNT(DISTINCT cp.lead_id) as converted_customers
            FROM leads l
            LEFT JOIN customer_profiles cp ON l.id = cp.lead_id";

        $result = $this->conn->query($sql);
        $data = $result->fetch_assoc();

        return $data['total_leads'] > 0 ?
            round(($data['converted_customers'] / $data['total_leads']) * 100, 2) : 0;
    }

    /**
     * Get opportunity to sale conversion rate
     */
    private function getOpportunityToSaleConversionRate() {
        $sql = "SELECT
            COUNT(*) as total_opportunities,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN 1 ELSE 0 END) as won_opportunities
            FROM opportunities";

        $result = $this->conn->query($sql);
        $data = $result->fetch_assoc();

        return $data['total_opportunities'] > 0 ?
            round(($data['won_opportunities'] / $data['total_opportunities']) * 100, 2) : 0;
    }

    /**
     * Get average sales cycle
     */
    private function getAverageSalesCycle() {
        $sql = "SELECT AVG(DATEDIFF(closed_date, created_at)) as avg_cycle
                FROM (
                    SELECT created_at,
                           CASE WHEN pipeline_stage_id = 5 THEN updated_at
                                WHEN pipeline_stage_id = 6 THEN updated_at
                                ELSE NULL END as closed_date
                    FROM opportunities
                    WHERE pipeline_stage_id IN (5, 6)
                ) as closed_deals
                WHERE closed_date IS NOT NULL";

        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['avg_cycle'] ?? 0;
    }

    // ==================== AUTOMATED WORKFLOWS ====================

    /**
     * Automated lead nurturing workflow
     */
    public function processLeadNurturing($leadId) {
        $lead = $this->crmManager->getLead($leadId);

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
            'template' => 'welcome_lead',
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
                'template' => 'property_recommendations',
                'delay_days' => 7,
                'subject' => 'Recommended Properties for You'
            ];
        }

        // Day 14: Special offer
        $sequence[] = [
            'type' => 'email',
            'template' => 'special_offer',
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

        $this->crmManager->addLeadActivity($activityData);

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
        $lead = $this->crmManager->getLead($taskData['lead_id']);

        if ($lead && $lead['email']) {
            $emailData = [
                'first_name' => $lead['first_name'],
                'email' => $lead['email'],
                'property_interest' => $lead['property_interest'],
                'budget_range' => $lead['budget_min'] . ' - ' . $lead['budget_max']
            ];

            $this->emailManager->sendTemplateEmail(
                $taskData['template'],
                $emailData,
                $lead['email'],
                $lead['first_name']
            );

            // Mark activity as completed
            $this->crmManager->addLeadActivity([
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
        $ticketId = $this->crmManager->createSupportTicket($ticketData);

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
        $sql = "UPDATE support_tickets SET internal_notes = CONCAT(
                IFNULL(internal_notes, ''), '\nLinked to Property ID: $propertyId'
                ) WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Link ticket to plot
     */
    private function linkTicketToPlot($ticketId, $plotId) {
        $sql = "UPDATE support_tickets SET internal_notes = CONCAT(
                IFNULL(internal_notes, ''), '\nLinked to Plot ID: $plotId'
                ) WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Auto-assign ticket based on type
     */
    private function autoAssignTicket($ticketId, $ticketType) {
        $assignmentRules = [
            'property' => 'property_manager',
            'plot' => 'plot_manager',
            'booking' => 'sales_manager',
            'technical' => 'technical_support',
            'billing' => 'accounts_manager'
        ];

        if (isset($assignmentRules[$ticketType])) {
            // Find user with appropriate role
            $sql = "SELECT id FROM users WHERE role = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $role = $assignmentRules[$ticketType];
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $updateSql = "UPDATE support_tickets SET assigned_to = ? WHERE id = ?";
                $stmt = $this->conn->prepare($updateSql);
                $stmt->bind_param("ii", $user['id'], $ticketId);
                $stmt->execute();
                $stmt->close();
            }
        }
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
        $report = [];

        // Sales by source
        $sql = "SELECT ls.source_name,
                       COUNT(l.id) as leads,
                       COUNT(o.id) as opportunities,
                       COUNT(CASE WHEN o.pipeline_stage_id = 5 THEN 1 END) as sales,
                       SUM(CASE WHEN o.pipeline_stage_id = 5 THEN o.expected_value END) as revenue
                FROM lead_sources ls
                LEFT JOIN leads l ON ls.id = l.lead_source_id
                LEFT JOIN opportunities o ON l.id = o.lead_id
                GROUP BY ls.id, ls.source_name
                ORDER BY revenue DESC";

        $result = $this->conn->query($sql);
        $report['sales_by_source'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['sales_by_source'][] = $row;
        }

        // Monthly sales trend
        $sql = "SELECT
            DATE_FORMAT(o.created_at, '%Y-%m') as month,
            COUNT(*) as opportunities,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN 1 ELSE 0 END) as sales,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN o.expected_value END) as revenue
            FROM opportunities o
            GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12";

        $result = $this->conn->query($sql);
        $report['monthly_trend'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['monthly_trend'][] = $row;
        }

        return $report;
    }

    /**
     * Generate lead conversion report
     */
    private function generateLeadConversionReport($dateRange) {
        $report = [];

        // Conversion funnel
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN lead_status = 'contacted' THEN 1 ELSE 0 END) as contacted,
            SUM(CASE WHEN lead_status = 'qualified' THEN 1 ELSE 0 END) as qualified,
            SUM(CASE WHEN lead_status = 'proposal_sent' THEN 1 ELSE 0 END) as proposals,
            SUM(CASE WHEN lead_status = 'won' THEN 1 ELSE 0 END) as converted
            FROM leads";

        $result = $this->conn->query($sql);
        $funnel = $result->fetch_assoc();

        $report['conversion_funnel'] = [
            'contact_rate' => $funnel['total_leads'] > 0 ? round(($funnel['contacted'] / $funnel['total_leads']) * 100, 2) : 0,
            'qualification_rate' => $funnel['contacted'] > 0 ? round(($funnel['qualified'] / $funnel['contacted']) * 100, 2) : 0,
            'proposal_rate' => $funnel['qualified'] > 0 ? round(($funnel['proposals'] / $funnel['qualified']) * 100, 2) : 0,
            'conversion_rate' => $funnel['proposals'] > 0 ? round(($funnel['converted'] / $funnel['proposals']) * 100, 2) : 0
        ];

        // Lead source conversion
        $sql = "SELECT ls.source_name,
                       COUNT(l.id) as total_leads,
                       SUM(CASE WHEN l.lead_status = 'won' THEN 1 ELSE 0 END) as converted_leads
                FROM lead_sources ls
                LEFT JOIN leads l ON ls.id = l.lead_source_id
                GROUP BY ls.id, ls.source_name";

        $result = $this->conn->query($sql);
        $report['source_conversion'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['conversion_rate'] = $row['total_leads'] > 0 ? round(($row['converted_leads'] / $row['total_leads']) * 100, 2) : 0;
            $report['source_conversion'][] = $row;
        }

        return $report;
    }

    /**
     * Generate customer lifecycle report
     */
    private function generateCustomerLifecycleReport($dateRange) {
        $report = [];

        // Customer acquisition
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as new_customers,
            SUM(total_purchase_value) as total_value
            FROM customer_profiles
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC";

        $result = $this->conn->query($sql);
        $report['customer_acquisition'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['customer_acquisition'][] = $row;
        }

        // Customer retention
        $sql = "SELECT
            customer_type,
            COUNT(*) as total_customers,
            AVG(total_purchase_value) as avg_value,
            COUNT(CASE WHEN total_purchases > 1 THEN 1 END) as repeat_customers
            FROM customer_profiles
            GROUP BY customer_type";

        $result = $this->conn->query($sql);
        $report['customer_retention'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['retention_rate'] = $row['total_customers'] > 0 ?
                round(($row['repeat_customers'] / $row['total_customers']) * 100, 2) : 0;
            $report['customer_retention'][] = $row;
        }

        return $report;
    }

    /**
     * Generate revenue analysis report
     */
    private function generateRevenueAnalysisReport($dateRange) {
        $report = [];

        // Revenue by source
        $sql = "SELECT 'Property Sales' as source, SUM(p.price) as revenue
                FROM properties p
                WHERE p.status = 'sold'
                UNION ALL
                SELECT 'Plot Sales' as source, SUM(p.current_price) as revenue
                FROM plots p
                WHERE p.plot_status = 'sold'
                UNION ALL
                SELECT 'Commission Income' as source, SUM(ct.commission_amount) as revenue
                FROM commission_tracking ct
                WHERE ct.payment_status = 'paid'";

        $result = $this->conn->query($sql);
        $report['revenue_by_source'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['revenue_by_source'][] = $row;
        }

        // Monthly revenue trend
        $sql = "SELECT
            DATE_FORMAT(p.created_at, '%Y-%m') as month,
            SUM(p.price) as property_revenue
            FROM properties p
            WHERE p.status = 'sold'
            GROUP BY DATE_FORMAT(p.created_at, '%Y-%m')
            UNION ALL
            SELECT
            DATE_FORMAT(p.updated_at, '%Y-%m') as month,
            SUM(p.current_price) as plot_revenue
            FROM plots p
            WHERE p.plot_status = 'sold'
            GROUP BY DATE_FORMAT(p.updated_at, '%Y-%m')
            ORDER BY month DESC";

        $result = $this->conn->query($sql);
        $report['monthly_revenue'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['monthly_revenue'][] = $row;
        }

        return $report;
    }

    // ==================== UTILITY FUNCTIONS ====================

    /**
     * Send booking confirmation
     */
    private function sendBookingConfirmation($bookingId) {
        $booking = $this->plottingManager->getPlotBooking($bookingId);

        if ($booking && $booking['customer_email']) {
            $emailData = [
                'customer_name' => $booking['customer_name'],
                'plot_number' => $booking['plot_number'],
                'colony_name' => $booking['colony_name'],
                'booking_amount' => $booking['booking_amount'],
                'total_amount' => $booking['total_amount'],
                'booking_date' => $booking['booking_date']
            ];

            $this->emailManager->sendTemplateEmail('plot_booking_confirmation', $emailData, $booking['customer_email'], $booking['customer_name']);
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
        $sql = "SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['count'] ?? 0;
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
function addCRMLead($leadData) {
    $crm = new SelForceCRMSystem();
    return $crm->addLeadWithIntegration($leadData);
}
?>
