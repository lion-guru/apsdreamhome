<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Associate;
use App\Models\Admin;
use App\Services\AdminService;

/**
 * Associate Controller
 * Handles all associate panel operations including login, team management, business view, and payouts
 */
class AssociateController extends Controller
{
    private $associateModel;
    private $adminModel;
    private $adminService;

    public function __construct()
    {
        parent::__construct();

        // Check if associate is logged in for protected routes
        $this->middleware('associate.auth');

        $this->associateModel = new Associate();
        $this->adminModel = new Admin();
        $this->adminService = new AdminService();
    }

    /**
     * Display associate login form
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if ($this->isAssociateLoggedIn()) {
            $this->redirect('/associate/dashboard');
        }

        $data = [
            'page_title' => 'Associate Login - APS Dream Home',
            'error' => $_SESSION['login_error'] ?? null
        ];

        unset($_SESSION['login_error']);
        $this->view('associates/login', $data);
    }

    /**
     * Handle associate login
     */
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/login');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter both email and password.';
            $this->redirect('/associate/login');
        }

        $associate = $this->associateModel->authenticateAssociate($email, $password);

        if ($associate) {
            // Set session variables
            $_SESSION['associate_id'] = $associate['associate_id'];
            $_SESSION['associate_code'] = $associate['associate_code'];
            $_SESSION['associate_name'] = $associate['name'];
            $_SESSION['associate_level'] = $associate['level'];

            // Update last login
            $this->associateModel->updateAssociate($associate['associate_id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);

            $this->redirect('/associate/dashboard');
        } else {
            $_SESSION['login_error'] = 'Invalid email or password.';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Associate logout
     */
    public function logout()
    {
        unset($_SESSION['associate_id']);
        unset($_SESSION['associate_code']);
        unset($_SESSION['associate_name']);
        unset($_SESSION['associate_level']);

        $this->redirect('/associate/login');
    }

    /**
     * Display associate dashboard
     */
    public function dashboard()
    {
        $associateId = $_SESSION['associate_id'];

        // Get associate details
        $associate = $this->associateModel->getAssociateById($associateId);

        if (!$associate) {
            $this->logout();
        }

        // Get dashboard statistics
        $stats = $this->associateModel->getBusinessStats($associateId);

        // Get recent commissions
        $recentCommissions = $this->associateModel->getCommissionDetails($associateId);
        $recentCommissions = array_slice($recentCommissions, 0, 5);

        // Get rank information
        $rankInfo = $this->associateModel->getAssociateRank($associateId);

        // Get pending payouts
        $pendingPayouts = $this->associateModel->getPendingPayouts($associateId);

        $data = [
            'associate' => $associate,
            'stats' => $stats,
            'recent_commissions' => $recentCommissions,
            'rank_info' => $rankInfo,
            'pending_payouts' => $pendingPayouts,
            'page_title' => 'Associate Dashboard - APS Dream Home'
        ];

        $this->view('associates/dashboard', $data);
    }

    /**
     * Display team management
     */
    public function team()
    {
        $associateId = $_SESSION['associate_id'];

        // Get direct team members
        $directMembers = $this->associateModel->getTeamMembers($associateId, 1);

        // Get complete hierarchy
        $hierarchy = $this->associateModel->getDownlineHierarchy($associateId);

        // Get team statistics
        $teamStats = $this->associateModel->getBusinessStats($associateId);

        $data = [
            'direct_members' => $directMembers,
            'hierarchy' => $hierarchy,
            'team_stats' => $teamStats['team'],
            'page_title' => 'Team Management - APS Dream Home'
        ];

        $this->view('associates/team', $data);
    }

    /**
     * Display business overview
     */
    public function business()
    {
        $associateId = $_SESSION['associate_id'];

        // Get comprehensive business statistics
        $businessStats = $this->associateModel->getBusinessStats($associateId);

        // Get commission summary
        $commissionSummary = $this->associateModel->getCommissionSummary($associateId);

        // Get monthly trends
        $monthlyTrends = $businessStats['monthly'];

        // Get top performing team members
        $topPerformers = $this->associateModel->getTeamMembers($associateId);
        usort($topPerformers, function($a, $b) {
            return $b['total_earnings'] <=> $a['total_earnings'];
        });
        $topPerformers = array_slice($topPerformers, 0, 10);

        $data = [
            'business_stats' => $businessStats,
            'commission_summary' => $commissionSummary,
            'monthly_trends' => $monthlyTrends,
            'top_performers' => $topPerformers,
            'page_title' => 'Business Overview - APS Dream Home'
        ];

        $this->view('associates/business', $data);
    }

    /**
     * Display earnings and commissions
     */
    public function earnings()
    {
        $associateId = $_SESSION['associate_id'];

        // Get commission details with filters
        $filters = [];
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $earnings = $this->associateModel->getAssociateEarnings($associateId, $filters);

        // Get commission summary
        $summary = $this->associateModel->getCommissionSummary($associateId);

        $data = [
            'earnings' => $earnings,
            'summary' => $summary,
            'filters' => $filters,
            'page_title' => 'Earnings & Commissions - APS Dream Home'
        ];

        $this->view('associates/earnings', $data);
    }

    /**
     * Display payout management
     */
    public function payouts()
    {
        $associateId = $_SESSION['associate_id'];

        // Get payout history
        $payoutHistory = $this->associateModel->getPayoutHistory($associateId);

        // Get available balance for payout
        $summary = $this->associateModel->getCommissionSummary($associateId);
        $availableBalance = $summary['total_commissions'] ?? 0;

        // Get minimum payout amount from settings
        $minPayout = 1000; // This should come from settings

        $data = [
            'payout_history' => $payoutHistory,
            'available_balance' => $availableBalance,
            'minimum_payout' => $minPayout,
            'page_title' => 'Payout Management - APS Dream Home'
        ];

        $this->view('associates/payouts', $data);
    }

    /**
     * Request payout
     */
    public function requestPayout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/payouts');
        }

        $associateId = $_SESSION['associate_id'];
        $amount = $_POST['amount'] ?? 0;
        $paymentMethod = $_POST['payment_method'] ?? '';
        $accountDetails = $_POST['account_details'] ?? '';

        // Validate amount
        $summary = $this->associateModel->getCommissionSummary($associateId);
        $availableBalance = $summary['total_commissions'] ?? 0;
        $minPayout = 1000;

        if ($amount < $minPayout) {
            $_SESSION['error'] = "Minimum payout amount is ₹{$minPayout}";
            $this->redirect('/associate/payouts');
        }

        if ($amount > $availableBalance) {
            $_SESSION['error'] = "Insufficient balance. Available: ₹{$availableBalance}";
            $this->redirect('/associate/payouts');
        }

        // Request payout
        $success = $this->associateModel->requestPayout($associateId, $amount, $paymentMethod, $accountDetails);

        if ($success) {
            $_SESSION['success'] = 'Payout request submitted successfully. You will be notified once processed.';
        } else {
            $_SESSION['error'] = 'Failed to submit payout request. Please try again.';
        }

        $this->redirect('/associate/payouts');
    }

    /**
     * Display profile management
     */
    public function profile()
    {
        $associateId = $_SESSION['associate_id'];
        $associate = $this->associateModel->getAssociateById($associateId);

        $data = [
            'associate' => $associate,
            'page_title' => 'Profile Management - APS Dream Home'
        ];

        $this->view('associates/profile', $data);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/profile');
        }

        $associateId = $_SESSION['associate_id'];

        // Handle profile update
        $data = [
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'pincode' => $_POST['pincode'] ?? ''
        ];

        $success = $this->associateModel->updateAssociate($associateId, $data);

        if ($success) {
            $_SESSION['success'] = 'Profile updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update profile. Please try again.';
        }

        $this->redirect('/associate/profile');
    }

    /**
     * Display KYC management
     */
    public function kyc()
    {
        $associateId = $_SESSION['associate_id'];
        $associate = $this->associateModel->getAssociateById($associateId);

        $data = [
            'associate' => $associate,
            'page_title' => 'KYC Management - APS Dream Home'
        ];

        $this->view('associates/kyc', $data);
    }

    /**
     * Submit KYC documents
     */
    public function submitKYC()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/kyc');
        }

        $associateId = $_SESSION['associate_id'];

        // Handle file uploads
        $kycDocuments = [];
        $uploadDir = ROOT . 'uploads/kyc/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($_FILES as $field => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . basename($file['name']);
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $kycDocuments[$field] = $filename;
                }
            }
        }

        $success = $this->associateModel->updateKYCStatus($associateId, 'pending', $kycDocuments);

        if ($success) {
            $_SESSION['success'] = 'KYC documents submitted successfully. Verification is pending.';
        } else {
            $_SESSION['error'] = 'Failed to submit KYC documents. Please try again.';
        }

        $this->redirect('/associate/kyc');
    }

    /**
     * Display rank and achievements
     */
    public function rank()
    {
        $associateId = $_SESSION['associate_id'];

        $rankInfo = $this->associateModel->getAssociateRank($associateId);
        $businessStats = $this->associateModel->getBusinessStats($associateId);

        $data = [
            'rank_info' => $rankInfo,
            'business_stats' => $businessStats,
            'page_title' => 'Rank & Achievements - APS Dream Home'
        ];

        $this->view('associates/rank', $data);
    }

    /**
     * Display support/tickets
     */
    public function support()
    {
        $associateId = $_SESSION['associate_id'];

        // Get support tickets (this would need a support_tickets table)
        $tickets = []; // Placeholder for support tickets

        $data = [
            'tickets' => $tickets,
            'page_title' => 'Support - APS Dream Home'
        ];

        $this->view('associates/support', $data);
    }

    /**
     * Display reports and analytics
     */
    public function reports()
    {
        $associateId = $_SESSION['associate_id'];

        // Get various reports
        $businessStats = $this->associateModel->getBusinessStats($associateId);
        $commissionDetails = $this->associateModel->getCommissionDetails($associateId);

        $data = [
            'business_stats' => $businessStats,
            'commission_details' => $commissionDetails,
            'page_title' => 'Reports & Analytics - APS Dream Home'
        ];

        $this->view('associates/reports', $data);
    }

    /**
     * Helper method to check if associate is logged in
     */
    private function isAssociateLoggedIn()
    {
        return isset($_SESSION['associate_id']);
    }

    /**
     * Middleware to check associate authentication
     */
    private function middleware($type)
    {
        if ($type === 'associate.auth' && !$this->isAssociateLoggedIn()) {
            $this->redirect('/associate/login');
        }
    }
}
