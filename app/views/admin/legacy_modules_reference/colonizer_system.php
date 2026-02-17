<?php
/**
 * APS Dream Home - Colonizer Company Management System
 * Complete integration of all systems for colonizer/plotting company
 *
 * Features:
 * - Land acquisition and farmer management
 * - Plot subdivision and numbering system
 * - MLM commission management for associates
 * - Employee salary and payroll management
 * - Integration with existing real estate system
 */

require_once __DIR__ . '/core/init.php';
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

class ColonizerManagementSystem {
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
    private $logger;

    public function __construct($logger = null) {
        $this->logger = $logger;
        $this->initializeSystem();
    }

    /**
     * Initialize all system components
     */
    private function initializeSystem() {
        // Initialize database connection using ORM
        $this->db = \App\Core\App::database();

        // Initialize all managers with ORM
        $this->authManager = new AuthManager($this->db, $this->logger);
        $this->propertyManager = new PropertyManager($this->db, $this->logger);
        $this->propertyAI = new PropertyAI($this->db);
        $this->emailManager = new EmailTemplateManager($this->db, $this->logger);
        $this->apiManager = new ApiKeyManager($this->db, $this->logger);
        $this->asyncTaskManager = new AsyncTaskManager($this->db, $this->logger);
        $this->adminDashboard = new AdminDashboard($this->db, $this->logger);

        // Initialize colonizer-specific managers
        $this->plottingManager = new PlottingManager($this->db, $this->logger);
        $this->mlmCommissionManager = new MLMCommissionManager($this->db, $this->logger);
        $this->farmerManager = new FarmerManager($this->db, $this->logger);
        $this->salaryManager = new SalaryManager($this->db, $this->logger);

        if ($this->logger) {
            $this->logger->log("Colonizer Management System initialized successfully", 'info', 'system');
        }
    }

    // ==================== LAND ACQUISITION & FARMER MANAGEMENT ====================

    /**
     * Add new farmer/kisan
     */
    public function addFarmer($farmerData) {
        return $this->farmerManager->addFarmer($farmerData);
    }

    /**
     * Get all farmers with filters
     */
    public function getFarmers($filters = [], $limit = 50, $offset = 0) {
        return $this->farmerManager->getFarmers($filters, $limit, $offset);
    }

    /**
     * Get farmer details
     */
    public function getFarmer($farmerId) {
        return $this->farmerManager->getFarmer($farmerId);
    }

    /**
     * Add land acquisition from farmer
     */
    public function addLandAcquisition($acquisitionData) {
        $acquisitionId = $this->plottingManager->addLandAcquisition($acquisitionData);

        if ($acquisitionId && !empty($acquisitionData['land_holdings'])) {
            // Add land holdings to farmer
            foreach ($acquisitionData['land_holdings'] as $holding) {
                $this->farmerManager->addLandHolding($acquisitionData['farmer_id'], $holding);
            }
        }

        return $acquisitionId;
    }

    /**
     * Get farmer dashboard
     */
    public function getFarmerDashboard($farmerId) {
        return $this->farmerManager->getFarmerDashboard($farmerId);
    }

    /**
     * Record farmer transaction
     */
    public function recordFarmerTransaction($transactionData) {
        return $this->farmerManager->recordTransaction($transactionData);
    }

    // ==================== PLOTTING & PLOT MANAGEMENT ====================

    /**
     * Create plots from land acquisition
     */
    public function createPlots($landAcquisitionId, $plotsData) {
        return $this->plottingManager->createPlots($landAcquisitionId, $plotsData);
    }

    /**
     * Get all plots with filters
     */
    public function getPlots($filters = [], $limit = 50, $offset = 0) {
        return $this->plottingManager->getPlots($filters, $limit, $offset);
    }

    /**
     * Book a plot
     */
    public function bookPlot($bookingData) {
        $bookingId = $this->plottingManager->bookPlot($bookingData);

        if ($bookingId) {
            // Calculate and create commission records
            $this->mlmCommissionManager->calculateBookingCommission($bookingId);

            // Send confirmation email
            $this->sendBookingConfirmation($bookingId);
        }

        return $bookingId;
    }

    /**
     * Get plot booking details
     */
    public function getPlotBooking($bookingId) {
        return $this->plottingManager->getPlotBooking($bookingId);
    }

    /**
     * Add payment to plot booking
     */
    public function addPlotBookingPayment($bookingId, $paymentData) {
        return $this->plottingManager->addBookingPayment($bookingId, $paymentData);
    }

    /**
     * Get plotting statistics
     */
    public function getPlottingStats() {
        return $this->plottingManager->getPlottingStats();
    }

    // ==================== MLM COMMISSION MANAGEMENT ====================

    /**
     * Get associate commission summary
     */
    public function getAssociateCommissionSummary($associateId, $startDate = null, $endDate = null) {
        return $this->mlmCommissionManager->getCommissionSummary($associateId, $startDate, $endDate);
    }

    /**
     * Process commission payout for associate
     */
    public function processCommissionPayout($associateId, $periodStart, $periodEnd) {
        return $this->mlmCommissionManager->processCommissionPayouts($associateId, $periodStart, $periodEnd);
    }

    /**
     * Get associate dashboard
     */
    public function getAssociateDashboard($associateId) {
        return $this->mlmCommissionManager->getAssociateDashboard($associateId);
    }

    /**
     * Generate commission report
     */
    public function generateCommissionReport($associateId, $startDate, $endDate) {
        return $this->mlmCommissionManager->generateCommissionReport($associateId, $startDate, $endDate);
    }

    // ==================== EMPLOYEE SALARY MANAGEMENT ====================

    /**
     * Create salary structure for employee
     */
    public function createSalaryStructure($salaryData) {
        return $this->salaryManager->createSalaryStructure($salaryData);
    }

    /**
     * Process monthly salary
     */
    public function processMonthlySalary($employeeId, $month, $year) {
        return $this->salaryManager->processMonthlySalary($employeeId, $month, $year);
    }

    /**
     * Mark salary as paid
     */
    public function markSalaryAsPaid($paymentId, $transactionId = null, $bankReference = null) {
        return $this->salaryManager->markSalaryAsPaid($paymentId, $transactionId, $bankReference);
    }

    /**
     * Record employee attendance
     */
    public function recordAttendance($attendanceData) {
        return $this->salaryManager->recordAttendance($attendanceData);
    }

    /**
     * Create employee advance
     */
    public function createEmployeeAdvance($advanceData) {
        return $this->salaryManager->createAdvance($advanceData);
    }

    /**
     * Create employee bonus
     */
    public function createEmployeeBonus($bonusData) {
        return $this->salaryManager->createBonus($bonusData);
    }

    /**
     * Get salary report for employee
     */
    public function getSalaryReport($employeeId, $startMonth, $startYear, $endMonth, $endYear) {
        return $this->salaryManager->getSalaryReport($employeeId, $startMonth, $startYear, $endMonth, $endYear);
    }

    /**
     * Get payroll dashboard
     */
    public function getPayrollDashboard() {
        return $this->salaryManager->getPayrollDashboard();
    }

    // ==================== INTEGRATED DASHBOARD ====================

    /**
     * Get comprehensive colonizer dashboard
     */
    public function getColonizerDashboard() {
        $dashboard = [];

        // Basic stats from admin dashboard
        $dashboard['basic_stats'] = $this->adminDashboard->getDashboardStats();

        // Plotting stats
        $dashboard['plotting_stats'] = $this->plottingManager->getPlottingStats();

        // Farmer stats
        $dashboard['farmer_stats'] = $this->farmerManager->getFarmerStats();

        // Payroll dashboard
        $dashboard['payroll_dashboard'] = $this->salaryManager->getPayrollDashboard();

        // Recent activities
        $dashboard['recent_activities'] = $this->adminDashboard->getRecentActivities(10);

        // System health
        $dashboard['system_health'] = $this->adminDashboard->getSystemHealth();

        return $dashboard;
    }

    // ==================== UTILITY FUNCTIONS ====================

    /**
     * Send booking confirmation email
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
     * Get property types
     */
    public function getPropertyTypes() {
        return $this->propertyManager->getPropertyTypes();
    }

    /**
     * Search properties
     */
    public function searchProperties($query, $limit = 20) {
        return $this->propertyManager->searchProperties($query, $limit);
    }

    /**
     * Get featured properties
     */
    public function getFeaturedProperties($limit = 6) {
        return $this->propertyManager->getFeaturedProperties($limit);
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        return $this->authManager->getCurrentUser();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin($userId = null) {
        return $this->adminDashboard->isAdmin($userId);
    }

    /**
     * Get admin menu
     */
    public function getAdminMenu($userRole) {
        return $this->adminDashboard->getAdminMenu($userRole);
    }

    // ==================== SETUP & MIGRATION ====================

    /**
     * Complete database setup for colonizer system
     */
    public function setupColonizerDatabase() {
        // The database tables are already created in individual managers
        // This function can be used for any additional setup

        $setup = [
            'message' => 'Colonizer Management System database setup completed successfully',
            'features' => [
                'Farmer Management' => 'Complete farmer/kisan management system',
                'Land Acquisition' => 'Land acquisition and holding management',
                'Plot Management' => 'Plot subdivision and numbering system',
                'MLM Commission' => 'Multi-level commission tracking system',
                'Salary Management' => 'Employee payroll and salary system',
                'Integration' => 'Full integration with existing real estate system'
            ]
        ];

        return $setup;
    }

    /**
     * Get system status
     */
    public function getSystemStatus() {
        $status = [
            'system_name' => 'APS Dream Home - Colonizer Management System',
            'version' => '1.0.0',
            'modules' => [
                'Authentication' => 'Active',
                'Property Management' => 'Active',
                'Plotting System' => 'Active',
                'Farmer Management' => 'Active',
                'MLM Commission' => 'Active',
                'Salary Management' => 'Active',
                'Email System' => 'Active',
                'API Management' => 'Active',
                'Admin Dashboard' => 'Active'
            ],
            'database_tables' => [
                'users', 'properties', 'property_types', 'property_images',
                'bookings', 'payments', 'associates', 'commission_transactions',
                'leads', 'email_templates', 'api_keys', 'async_tasks',
                'task_queue', 'site_settings', 'farmer_profiles', 'farmer_land_holdings',
                'farmer_transactions', 'farmer_loans', 'farmer_support_requests',
                'plots', 'plot_bookings', 'plot_payments', 'commission_tracking',
                'employee_salary_structure', 'salary_payments', 'employee_attendance',
                'employee_advances', 'employee_bonuses', 'associate_levels',
                'commission_payouts', 'associate_achievements'
            ]
        ];

        return $status;
    }
}

// ==================== UTILITY FUNCTIONS ====================

/**
 * Initialize the complete colonizer management system
 */
function initializeColonizerSystem() {
    return new ColonizerManagementSystem();
}

/**
 * Quick setup for colonizer system
 */
function setupColonizerSystem() {
    $system = new ColonizerManagementSystem();
    return $system->setupColonizerDatabase();
}

/**
 * Get colonizer dashboard
 */
function getColonizerDashboard() {
    $system = new ColonizerManagementSystem();
    return $system->getColonizerDashboard();
}
?>
