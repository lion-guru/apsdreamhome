<?php

namespace App\Http\Controllers;

use App\Core\App;
use App\Core\Auth;
// use App\Http\BaseController; // Removed incorrect namespace import
use App\Models\Associate;
use App\Models\Admin;
use App\Models\Lead;
use App\Models\Expense;
use App\Services\AdminService;

/**
 * Associate Controller
 * Handles all associate panel operations including login, dashboard, registration, expenses, etc.
 * Merged from App\Http\Controllers\Associate\AssociateController.php
 */
class AssociateController extends BaseController
{
    private $associateModel;
    private $adminModel;
    private $adminService;
    private $leadModel;
    private $expenseModel;

    public function __construct()
    {
        parent::__construct();

        // Initialize models safely
        if (class_exists('App\Models\Associate')) {
            $this->associateModel = new Associate();
        }
        if (class_exists('App\Models\Admin')) {
            $this->adminModel = new Admin();
        }
        if (class_exists('App\Services\AdminService')) {
            $this->adminService = new AdminService();
        }
        if (class_exists('App\Models\Lead')) {
            $this->leadModel = new Lead();
        }
        if (class_exists('App\Models\Expense') && $this->db) {
            $this->expenseModel = new Expense($this->db->getConnection());
        }
    }

    /**
     * Dashboard - Main Associate View
     * Uses the modern layout and mlm_profiles data
     */
    public function dashboard()
    {
        $this->checkAssociateLogin();

        $associate_id = $_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? 0;
        $associate_name = $_SESSION['user_name'] ?? $_SESSION['associate_name'] ?? 'Associate';
        // Try to get level from session, default to Associate
        $associate_level = $_SESSION['level'] ?? $_SESSION['associate_level'] ?? 'Associate';

        $db = App::database();
        $associate_data = [];

        // Get comprehensive associate data from mlm_profiles + users
        try {
            // Join users and mlm_profiles
            // Adjust query to handle potential missing mlm_profiles record
            $query = "
                SELECT u.name, u.email, u.phone, mp.* 
                FROM users u 
                LEFT JOIN mlm_profiles mp ON u.id = mp.user_id 
                WHERE u.id = :uid
            ";
            $associate_data = $db->fetch($query, ['uid' => $associate_id]);

            if (!$associate_data || empty($associate_data['referral_code'])) {
                // Fallback: Try fetching from associates table if mlm_profiles is missing
                $query_fallback = "
                    SELECT u.name, u.email, u.phone, a.*, a.associate_code as referral_code 
                    FROM users u 
                    JOIN associates a ON u.id = a.user_id 
                    WHERE u.id = :uid
                ";
                $associate_data_fallback = $db->fetch($query_fallback, ['uid' => $associate_id]);

                if ($associate_data_fallback) {
                    $associate_data = $associate_data_fallback;
                    // Ensure essential fields are set
                    $associate_data['current_level'] = $associate_data['current_level'] ?? 'Associate';
                    $associate_data['lifetime_sales'] = $associate_data['total_business'] ?? 0;
                    $associate_data['total_commission'] = 0; // commissions might be in another table
                    $associate_data['total_team_size'] = 0;
                    $associate_data['direct_referrals'] = 0;
                } else {
                    // Fallback if no profile found at all
                    $associate_data = array_merge($associate_data ?? [], [
                        'referral_code' => $_SESSION['associate_code'] ?? 'N/A',
                        'current_level' => 'Associate',
                        'lifetime_sales' => 0,
                        'total_commission' => 0,
                        'total_team_size' => 0,
                        'direct_referrals' => 0,
                        'profile_image' => null
                    ]);
                }
            } else {
                // Update session data with latest from DB
                $associate_level = $associate_data['current_level'];
                $_SESSION['level'] = $associate_level;
                $_SESSION['associate_level'] = $associate_level;
            }
        } catch (\Exception $e) {
            error_log("Error fetching associate data: " . $e->getMessage());
            $associate_data = [];
        }

        // Get dashboard statistics
        $stats = [
            'total_business' => $associate_data['lifetime_sales'] ?? 0,
            'total_commission' => $associate_data['total_commission'] ?? 0,
            'direct_team' => $associate_data['direct_referrals'] ?? 0,
            'total_team' => $associate_data['total_team_size'] ?? 0
        ];

        // Level targets and progress
        $level_targets = [
            'Associate' => ['min' => 0, 'max' => 1000000, 'commission' => 5, 'reward' => 'Mobile'],
            'Sr. Associate' => ['min' => 1000000, 'max' => 3500000, 'commission' => 7, 'reward' => 'Tablet'],
            'BDM' => ['min' => 3500000, 'max' => 7000000, 'commission' => 10, 'reward' => 'Laptop'],
            'Sr. BDM' => ['min' => 7000000, 'max' => 15000000, 'commission' => 12, 'reward' => 'Tour'],
            'Vice President' => ['min' => 15000000, 'max' => 30000000, 'commission' => 15, 'reward' => 'Bike'],
            'President' => ['min' => 30000000, 'max' => 50000000, 'commission' => 18, 'reward' => 'Bullet'],
            'Site Manager' => ['min' => 50000000, 'max' => 999999999, 'commission' => 20, 'reward' => 'Car']
        ];

        $current_level_info = $level_targets[$associate_level] ?? $level_targets['Associate'];
        $progress_percentage = 0;
        if ($current_level_info['max'] > $current_level_info['min']) {
            $val = $stats['total_business'] - $current_level_info['min'];
            $range = $current_level_info['max'] - $current_level_info['min'];
            if ($range > 0) {
                $progress_percentage = min(100, ($val / $range) * 100);
            }
        }

        $data = [
            'page_title' => 'Associate Portal | APS Dream Homes',
            'layout' => 'layouts/associate',
            'associate_name' => $associate_name,
            'associate_level' => $associate_level,
            'associate_data' => $associate_data,
            'stats' => $stats,
            'progress_percentage' => $progress_percentage,
            'current_level_info' => $current_level_info
        ];

        // Use the singular view 'associate/dashboard' as it appears to be the modern one
        return $this->render('associates/dashboard', $data, 'layouts/associate');
    }

    /**
     * Display associate login form
     */
    public function login()
    {
        if ($this->isAssociateLoggedIn()) {
            $this->redirect('/associate/dashboard');
        }

        $data = [
            'page_title' => 'Associate Login - APS Dream Home',
            'error' => $this->getFlash('login_error')
        ];

        // Use 'associates/login' from legacy folder if 'associate/login' doesn't exist
        // Assuming 'associates/login.php' exists based on file listing
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

        $loginId = $_POST['login_id'] ?? $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($loginId) || empty($password)) {
            $this->setFlash('login_error', 'Please enter both login ID (email/mobile) and password.');
            $this->redirect('/associate/login');
        }

        // Use associateModel to authenticate
        // Note: Make sure associateModel is initialized
        if (!$this->associateModel) {
            // Fallback if model not loaded
            $this->setFlash('login_error', 'System error: Associate model not loaded.');
            $this->redirect('/associate/login');
            return;
        }

        $associate = $this->associateModel->authenticateAssociate($loginId, $password);

        if ($associate) {
            // Set session variables
            $_SESSION['user_id'] = $associate['user_id'] ?? $associate['associate_id']; // Prefer user_id for consistency
            $_SESSION['associate_id'] = $associate['associate_id'];
            $_SESSION['associate_code'] = $associate['associate_code'];
            $_SESSION['user_name'] = $associate['name'] ?? $associate['user_name'] ?? 'Associate';
            $_SESSION['user_role'] = 'associate';
            $_SESSION['level'] = $associate['level'] ?? 'Associate';

            // Redirect to dashboard
            $this->redirect('/associate/dashboard');
        } else {
            $this->setFlash('login_error', 'Invalid credentials or account inactive.');
            $this->redirect('/associate/login');
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: /associate/login");
        exit();
    }

    /**
     * Display associate registration form
     */
    public function register()
    {
        if ($this->isAssociateLoggedIn()) {
            $this->redirect('/associate/dashboard');
        }

        $referrerCode = $_GET['ref'] ?? '';
        $referrerInfo = null;

        if ($referrerCode && $this->associateModel) {
            $referrerInfo = $this->associateModel->getAssociateByReferralCode($referrerCode);
        }

        $data = [
            'page_title' => 'Associate Registration - APS Dream Home',
            'referrer_code' => $referrerCode,
            'referrer_info' => $referrerInfo,
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
            'old' => $_SESSION['old_input'] ?? []
        ];

        // Clear old input
        unset($_SESSION['old_input']);

        return $this->render('associates/register', $data, 'layouts/base');
    }

    /**
     * Handle associate registration
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/register');
        }

        if (!$this->associateModel) {
            $this->setFlash('error', 'System error: Model not loaded.');
            $this->redirect('/associate/register');
            return;
        }

        // Collect input
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'mobile' => trim($_POST['mobile'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'referrer_code' => trim($_POST['referrer_code'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'district' => trim($_POST['district'] ?? ''),
            'pincode' => trim($_POST['pin_code'] ?? ''),
            'aadhar_number' => trim($_POST['aadhar_number'] ?? ''),
            'pan_number' => trim($_POST['pan_number'] ?? ''),
        ];

        $_SESSION['old_input'] = $data;

        // Validation
        if (empty($data['full_name']) || empty($data['mobile']) || empty($data['email']) || empty($data['password'])) {
            $this->setFlash('error', 'Please fill all required fields.');
            $this->redirect('/associate/register');
        }

        if (empty($data['referrer_code'])) {
            $this->setFlash('error', 'Referral code is mandatory!');
            $this->redirect('/associate/register');
        }

        if ($data['password'] !== $data['confirm_password']) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->redirect('/associate/register');
        }

        if ($this->associateModel->isEmailExists($data['email'])) {
            $this->setFlash('error', 'Email already registered.');
            $this->redirect('/associate/register');
        }

        if ($this->associateModel->isMobileExists($data['mobile'])) {
            $this->setFlash('error', 'Mobile number already registered.');
            $this->redirect('/associate/register');
        }

        // Verify referrer
        $referrer = $this->associateModel->getAssociateByReferralCode($data['referrer_code']);
        if (!$referrer) {
            $this->setFlash('error', 'Invalid referrer code.');
            $this->redirect('/associate/register');
        }

        try {
            $this->db->beginTransaction();

            // 1. Create User
            $userData = [
                'name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['mobile'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => 'associate',
                'status' => 'active'
            ];
            // Use associateModel's createAssociateUser or direct DB
            // Assuming createAssociateUser exists as per legacy code
            $userId = $this->associateModel->createAssociateUser($userData);

            // 2. Create Associate
            $associateCode = 'APS' . strtoupper(substr($data['full_name'], 0, 2)) . rand(1000, 9999);
            $associateData = [
                'user_id' => $userId,
                'sponsor_id' => $referrer['associate_id'] ?? $referrer['id'], // Handle key difference
                'associate_code' => $associateCode,
                'current_level' => 'Associate',
                'status' => 'active'
            ];
            $this->associateModel->createAssociate($associateData);

            // 3. Create MLM Profile (Sync with associates)
            // Get sponsor user_id for mlm_profiles
            $sponsorUserId = $referrer['user_id'] ?? null;
            if (!$sponsorUserId && isset($referrer['sponsor_id'])) {
                // Try to fetch if not directly available
                // This is best effort to keep data clean
            }

            $mlmSql = "INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, sponsor_code, current_level, status, created_at, updated_at) 
                       VALUES (:user_id, :referral_code, :sponsor_user_id, :sponsor_code, :current_level, :status, NOW(), NOW())";

            $mlmParams = [
                'user_id' => $userId,
                'referral_code' => $associateCode,
                'sponsor_user_id' => $sponsorUserId, // Might be null, schema allows?
                'sponsor_code' => $data['referrer_code'],
                'current_level' => 'Associate',
                'status' => 'active'
            ];

            // Execute manually via DB instance to avoid Model dependency issues
            $this->db->query($mlmSql, $mlmParams);

            $this->db->commit();
            unset($_SESSION['old_input']);

            $this->setFlash('success', "Registration successful! Code: {$associateCode}");
            $this->redirect('/associate/login');
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger()->error("Register Error: " . $e->getMessage());
            $this->setFlash('error', 'Registration failed.');
            $this->redirect('/associate/register');
        }
    }

    /**
     * Expense Management
     */
    public function expenses()
    {
        $this->checkAssociateLogin();
        $associateId = $_SESSION['associate_id'];

        $expenses = $this->expenseModel ? $this->expenseModel->getByAssociateId($associateId) : [];
        $stats = $this->expenseModel ? $this->expenseModel->getStats($associateId) : [];

        $data = [
            'page_title' => 'My Expenses - APS Dream Home',
            'expenses' => $expenses,
            'stats' => $stats,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ];

        return $this->render('associates/expenses', $data, 'layouts/associate');
    }

    public function storeExpense()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            $data = [
                'associate_id' => $associateId,
                'category' => $_POST['category'] ?? 'General',
                'amount' => $_POST['amount'] ?? 0,
                'description' => $_POST['description'] ?? '',
                'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
                'proof_file' => null
            ];

            // Handle File Upload (Simplified for brevity, ensure upload dir exists)
            if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/expenses/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = time() . '_' . $_FILES['proof_file']['name'];
                if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $uploadDir . $fileName)) {
                    $data['proof_file'] = $fileName;
                }
            }

            if ($this->expenseModel && $this->expenseModel->create($data)) {
                $this->setFlash('success', 'Expense submitted successfully!');
            } else {
                $this->setFlash('error', 'Failed to submit expense.');
            }

            $this->redirect('/associate/expenses');
        }
    }

    /**
     * Helper to check login status
     */
    private function checkAssociateLogin()
    {
        if (!$this->isAssociateLoggedIn()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                exit;
            }
            $this->redirect('/associate/login');
        }
    }



    /**
     * Lookup Pincode details
     */
    public function lookupPincode()
    {
        $this->checkAssociateLogin();
        $pincode = $_GET['pincode'] ?? '';

        if (empty($pincode) || strlen($pincode) !== 6) {
            $this->sendJsonAndExit(['status' => 'error', 'message' => 'Invalid Pincode']);
            return;
        }

        // Dummy data for now
        $pincodeData = [
            '274402' => ['city' => 'Kasia', 'state' => 'Uttar Pradesh', 'district' => 'Kushinagar'],
            '273001' => ['city' => 'Gorakhpur', 'state' => 'Uttar Pradesh', 'district' => 'Gorakhpur'],
            '226001' => ['city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'district' => 'Lucknow'],
        ];

        if (isset($pincodeData[$pincode])) {
            $this->sendJsonAndExit([
                'status' => 'success',
                'data' => $pincodeData[$pincode]
            ]);
        } else {
            $this->sendJsonAndExit(['status' => 'error', 'message' => 'Pincode not found']);
        }
    }

    /**
     * Lookup IFSC details
     */
    public function lookupIFSC()
    {
        $this->checkAssociateLogin();
        $ifsc = $_GET['ifsc'] ?? '';

        if (empty($ifsc) || strlen($ifsc) !== 11) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid IFSC']);
            return;
        }

        $url = "https://ifsc.razorpay.com/" . $ifsc;
        $response = @file_get_contents($url);

        if ($response && ($data = json_decode($response, true)) && isset($data['BANK'])) {
            $this->jsonResponse([
                'status' => 'success',
                'bank' => $data['BANK'],
                'branch' => $data['BRANCH']
            ]);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'IFSC details not found']);
        }
    }

    protected function jsonResponse($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
