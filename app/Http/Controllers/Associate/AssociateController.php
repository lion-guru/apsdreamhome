<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Models\Associate;
use App\Models\Admin;
use App\Models\Lead;
use App\Models\Expense;
use App\Services\AdminService;

/**
 * Associate Controller
 * Handles all associate panel operations including login, team management, business view, and payouts
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

        $this->associateModel = new Associate();
        $this->adminModel = new Admin();
        $this->adminService = new AdminService();
        $this->leadModel = new Lead();
        $this->expenseModel = new Expense($this->db->getConnection());
    }

    /**
     * Lookup Pincode details
     */
    public function lookupPincode()
    {
        $this->checkAssociateLogin();

        $pincode = $_GET['pincode'] ?? '';
        if (empty($pincode) || strlen($pincode) !== 6) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid Pincode']);
            exit;
        }

        $url = "https://api.postalpincode.in/pincode/" . $pincode;

        // Suppress warnings for file_get_contents
        $response = @file_get_contents($url);

        if ($response === FALSE) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'API Error']);
            exit;
        }

        $data = json_decode($response, true);

        if (isset($data[0]['Status']) && $data[0]['Status'] === 'Success') {
            $postOffice = $data[0]['PostOffice'][0];
            $result = [
                'status' => 'success',
                'city' => $postOffice['District'],
                'state' => $postOffice['State']
            ];
        } else {
            $result = ['status' => 'error', 'message' => 'Pincode not found'];
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Lookup IFSC details
     */
    public function lookupIFSC()
    {
        $this->checkAssociateLogin();

        $ifsc = $_GET['ifsc'] ?? '';
        if (empty($ifsc) || strlen($ifsc) !== 11) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid IFSC']);
            exit;
        }

        $url = "https://ifsc.razorpay.com/" . $ifsc;

        $response = @file_get_contents($url);

        if ($response === FALSE) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'IFSC Not Found']);
            exit;
        }

        $data = json_decode($response, true);

        if (isset($data['BANK'])) {
            $result = [
                'status' => 'success',
                'bank' => $data['BANK'],
                'branch' => $data['BRANCH']
            ];
        } else {
            $result = ['status' => 'error', 'message' => 'IFSC details not found'];
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
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

        if ($referrerCode) {
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

        $this->view('associates/register', $data);
    }

    /**
     * Expense Management
     */
    public function expenses()
    {
        $this->checkAssociateLogin();
        $associateId = $_SESSION['associate_id'];

        $expenses = $this->expenseModel->getByAssociateId($associateId);
        $stats = $this->expenseModel->getStats($associateId);

        $data = [
            'page_title' => 'My Expenses - APS Dream Home',
            'expenses' => $expenses,
            'stats' => $stats,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ];

        $this->view('associates/expenses', $data);
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
                'proof_file' => null // File upload handling to be added
            ];

            // Handle File Upload
            if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['proof_file']['tmp_name'];
                $fileName = $_FILES['proof_file']['name'];
                $fileSize = $_FILES['proof_file']['size'];
                $fileType = $_FILES['proof_file']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Allowed file extensions
                $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'pdf');

                // Max file size (5MB)
                $maxFileSize = 5 * 1024 * 1024;

                if (!in_array($fileExtension, $allowedfileExtensions)) {
                    $this->setFlash('error', 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions));
                    $this->redirect('/associate/expenses');
                }

                if ($fileSize > $maxFileSize) {
                    $this->setFlash('error', 'Upload failed. File size exceeds 5MB limit.');
                    $this->redirect('/associate/expenses');
                }

                // Determine upload directory relative to public/index.php
                $uploadDir = 'uploads/expenses/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = time() . '_' . $fileName;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $targetPath)) {
                    $data['proof_file'] = $newFileName;
                } else {
                    $this->setFlash('error', 'There was some error moving the file to upload directory.');
                    $this->redirect('/associate/expenses');
                }
            }

            if ($this->expenseModel->create($data)) {
                $this->setFlash('success', 'Expense submitted successfully!');
            } else {
                $this->setFlash('error', 'Failed to submit expense.');
            }

            $this->redirect('/associate/expenses');
        }
    }

    /**
     * Handle associate registration
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/register');
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
            'district' => trim($_POST['district'] ?? ''), // city/district
            'pincode' => trim($_POST['pin_code'] ?? ''),
            'aadhar_number' => trim($_POST['aadhar_number'] ?? ''),
            'pan_number' => trim($_POST['pan_number'] ?? ''),
        ];

        // Save old input
        $_SESSION['old_input'] = $data;

        // Validation
        if (empty($data['full_name']) || empty($data['mobile']) || empty($data['email']) || empty($data['password'])) {
            $this->setFlash('error', 'Please fill all required fields.');
            $this->redirect('/associate/register');
        }

        if (empty($data['referrer_code'])) {
            $this->setFlash('error', 'Referral code is mandatory for associate registration!');
            $this->redirect('/associate/register');
        }

        if ($data['password'] !== $data['confirm_password']) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->redirect('/associate/register');
        }

        if (strlen($data['mobile']) != 10) {
            $this->setFlash('error', 'Mobile number must be 10 digits.');
            $this->redirect('/associate/register');
        }

        // Check if user exists
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
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ];
            $userId = $this->associateModel->createAssociateUser($userData);

            // 2. Create Associate
            // Generate unique associate code
            $associateCode = 'APS' . strtoupper(substr($data['full_name'], 0, 2)) . rand(1000, 9999);

            $associateData = [
                'user_id' => $userId,
                'sponsor_id' => $referrer['associate_id'],
                'associate_code' => $associateCode,
                'current_level' => 1, // Associate level
                'status' => 'active' // Or pending based on requirements
            ];
            $associateId = $this->associateModel->createAssociate($associateData);

            // 3. Create Commission (Referral Bonus) - Legacy behavior
            // Inserting into new mlm_commissions table
            // Mapping: associate_id = referrer, level = 1, amount = 500, type = bonus
            $commissionSql = "INSERT INTO mlm_commissions (associate_id, level, commission_amount, status, created_at, commission_type, user_id, is_direct) 
                              VALUES (?, 1, 500.00, 'pending', NOW(), 'bonus', ?, 1)";
            $stmt = $this->db->prepare($commissionSql);
            $stmt->execute([$referrer['associate_id'], $userId]);

            $this->db->commit();

            // Clear old input
            unset($_SESSION['old_input']);

            $this->setFlash('success', "Registration successful! Your associate code is: <strong>{$associateCode}</strong>. Please login.");
            $this->redirect('/associate/login');
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Associate Registration Error: " . $e->getMessage());
            $this->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect('/associate/register');
        }
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
            'error' => $this->getFlash('login_error')
        ];

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

        // Support both 'email' (legacy) and 'login_id' (new) fields
        $loginId = $_POST['login_id'] ?? $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($loginId) || empty($password)) {
            $this->setFlash('login_error', 'Please enter both login ID (email/mobile) and password.');
            $this->redirect('/associate/login');
        }

        $associate = $this->associateModel->authenticateAssociate($loginId, $password);

        if ($associate) {
            // Set session variables
            $_SESSION['associate_id'] = $associate['associate_id'];
            $_SESSION['associate_code'] = $associate['associate_code'];
            $_SESSION['associate_name'] = $associate['name'] ?? $associate['user_name'] ?? 'Associate';
            $_SESSION['associate_level'] = $associate['level'];

            // Update last login
            $this->associateModel->updateAssociate($associate['associate_id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);

            $this->redirect('/associate/dashboard');
        } else {
            $this->setFlash('login_error', 'Invalid email/mobile or password.');
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
     * Display associate leads (CRM)
     */
    public function leads()
    {
        $this->checkAssociateLogin();

        $associateId = $_SESSION['associate_id'];

        // Get leads for this associate
        // Using direct query as Lead model might not have specific associate filter method yet
        $stmt = $this->db->prepare("SELECT * FROM leads WHERE associate_id = ? ORDER BY created_at DESC");
        $stmt->execute([$associateId]);
        $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get lead stats
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
                SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
                SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified,
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
            FROM leads 
            WHERE associate_id = ?
        ");
        $stmt->execute([$associateId]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Leads - Associate Panel',
            'leads' => $leads,
            'stats' => $stats,
            'associate' => $this->getAssociateData()
        ];

        $this->view('associates/leads', $data);
    }

    /**
     * Add a new lead
     */
    public function addLead()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            $data = [
                'associate_id' => $associateId,
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'status' => 'new',
                'source' => 'associate_panel',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Basic validation
            if (empty($data['first_name']) || empty($data['phone'])) {
                $this->setFlash('error', 'Name and Phone are required.');
                $this->redirect('/associate/leads');
            }

            try {
                // Insert lead
                $sql = "INSERT INTO leads (associate_id, first_name, last_name, email, phone, status, source, created_at) 
                        VALUES (:associate_id, :first_name, :last_name, :email, :phone, :status, :source, :created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Lead added successfully!');
            } catch (\Exception $e) {
                error_log("Error adding lead: " . $e->getMessage());
                $this->setFlash('error', 'Failed to add lead. Please try again.');
            }

            $this->redirect('/associate/leads');
        }
    }

    /**
     * Store a new lead (from CRM)
     */
    public function storeLead()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            $data = [
                'associate_id' => $associateId,
                'first_name' => $_POST['name'] ?? '', // CRM form uses 'name'
                'last_name' => '', // CRM form only has 'name'
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'budget' => $_POST['budget'] ?? null,
                'property_type' => $_POST['property_type'] ?? null,
                'location' => $_POST['location'] ?? '',
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'pincode' => $_POST['pincode'] ?? '',
                'account_name' => $_POST['account_name'] ?? null,
                'account_number' => $_POST['account_number'] ?? null,
                'ifsc_code' => $_POST['ifsc_code'] ?? null,
                'bank_name' => $_POST['bank_name'] ?? null,
                'branch_name' => $_POST['branch_name'] ?? null,
                'status' => 'new',
                'priority' => 'medium',
                'notes' => $_POST['notes'] ?? '',
                'source' => 'associate_crm',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Split name if possible
            $parts = explode(' ', $data['first_name'], 2);
            if (count($parts) > 1) {
                $data['first_name'] = $parts[0];
                $data['last_name'] = $parts[1];
            }

            // Basic validation
            if (empty($data['first_name']) || empty($data['phone'])) {
                $this->setFlash('error', 'Name and Phone are required.');
                $this->redirect('/associate/crm');
            }

            // Handle empty budget
            if ($data['budget'] === '') $data['budget'] = null;

            try {
                // Insert lead
                $sql = "INSERT INTO leads (associate_id, first_name, last_name, email, phone, budget, property_type, location, address, city, state, pincode, account_name, account_number, ifsc_code, bank_name, branch_name, status, priority, notes, source, created_at) 
                        VALUES (:associate_id, :first_name, :last_name, :email, :phone, :budget, :property_type, :location, :address, :city, :state, :pincode, :account_name, :account_number, :ifsc_code, :bank_name, :branch_name, :status, :priority, :notes, :source, :created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Lead added successfully!');
            } catch (\Exception $e) {
                error_log("Error adding lead: " . $e->getMessage());
                $this->setFlash('error', 'Failed to add lead. Please try again.');
            }

            $this->redirect('/associate/crm');
        }
    }

    /**
     * Helper to get associate data
     */
    private function getAssociateData()
    {
        return [
            'associate_id' => $_SESSION['associate_id'],
            'name' => $_SESSION['associate_name'],
            'associate_code' => $_SESSION['associate_code'],
            'level' => $_SESSION['associate_level']
        ];
    }

    /**
     * Helper to check login
     */
    private function checkAssociateLogin()
    {
        if (!$this->isAssociateLoggedIn()) {
            $this->redirect('/associate/login');
        }
    }

    /**
     * Display associate CRM
     */
    public function crm()
    {
        $this->checkAssociateLogin();
        $associateId = $_SESSION['associate_id'];

        $data = [];
        $data['page_title'] = 'Associate CRM - APS Dream Home';
        $data['associate'] = $this->getAssociateData();

        try {
            // Leads statistics
            $leads_query = "SELECT
                               COUNT(*) as total_leads,
                               SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
                               SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted_leads,
                               SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
                               SUM(CASE WHEN status = 'proposal' THEN 1 ELSE 0 END) as proposal_leads,
                               SUM(CASE WHEN status = 'negotiation' THEN 1 ELSE 0 END) as negotiation_leads,
                               SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_leads,
                               SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_leads
                            FROM leads WHERE associate_id = :associate_id";
            $stmt = $this->db->prepare($leads_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['leads_stats'] = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Recent leads
            $recent_leads_query = "SELECT * FROM leads WHERE associate_id = :associate_id
                                  ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->prepare($recent_leads_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['recent_leads'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Customers
            // Note: Customers table seems to be global in legacy schema, but linked via bookings.
            // We'll follow legacy pattern but try to filter if possible.
            // Legacy query: SELECT c.*, COUNT(b.id) as total_bookings, SUM(b.total_amount) as total_value
            // FROM customers c LEFT JOIN bookings b ON c.id = b.customer_id AND b.associate_id = ? ...
            $customers_query = "SELECT c.*, COUNT(b.id) as total_bookings,
                                       SUM(b.total_amount) as total_value
                                FROM customers c
                                LEFT JOIN bookings b ON c.id = b.customer_id AND b.associate_id = :associate_id
                                GROUP BY c.id
                                ORDER BY c.created_at DESC LIMIT 20";
            $stmt = $this->db->prepare($customers_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['customers'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Visits Stats
            $visit_stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM property_visits WHERE associate_id = :associate_id";
            $stmt = $this->db->prepare($visit_stats_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['visit_stats'] = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Recent Visits
            $visits_query = "SELECT pv.*, p.title as property_title, 
                                    COALESCE(CONCAT(l.first_name, ' ', l.last_name), c.full_name) as client_name,
                                    CASE WHEN pv.lead_id IS NOT NULL THEN 'Lead' ELSE 'Customer' END as client_type
                             FROM property_visits pv 
                             LEFT JOIN properties p ON pv.property_id = p.id
                             LEFT JOIN leads l ON pv.lead_id = l.id
                             LEFT JOIN customers c ON pv.customer_id = c.id
                             WHERE pv.associate_id = :associate_id 
                             ORDER BY pv.visit_date DESC, pv.visit_time DESC LIMIT 20";
            $stmt = $this->db->prepare($visits_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['visits'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Properties (for dropdowns)
            $properties_query = "SELECT id, title, project_name FROM properties ORDER BY created_at DESC";
            $stmt = $this->db->prepare($properties_query);
            $stmt->execute();
            $data['properties'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Customers (all customers for this associate)
            $all_customers_query = "SELECT id, full_name, phone FROM customers ORDER BY full_name ASC";
            $stmt = $this->db->prepare($all_customers_query);
            $stmt->execute();
            $data['all_customers'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Leads (all leads for this associate for dropdown)
            $all_leads_query = "SELECT id, first_name, last_name, phone FROM leads WHERE associate_id = :associate_id ORDER BY first_name ASC";
            $stmt = $this->db->prepare($all_leads_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['all_leads'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Activities
            $activities_query = "SELECT * FROM associate_activities
                                WHERE associate_id = :associate_id
                                ORDER BY created_at DESC LIMIT 15";
            $stmt = $this->db->prepare($activities_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['activities'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Appointments
            $appointments_query = "SELECT * FROM associate_appointments
                                  WHERE associate_id = :associate_id AND appointment_date >= CURDATE()
                                  ORDER BY appointment_date, appointment_time LIMIT 10";
            $stmt = $this->db->prepare($appointments_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['appointments'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Messages
            $messages_query = "SELECT * FROM associate_messages
                              WHERE associate_id = :associate_id
                              ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->prepare($messages_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['messages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Notes
            $notes_query = "SELECT * FROM associate_notes
                           WHERE associate_id = :associate_id
                           ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->prepare($notes_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['notes'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Conversion rates
            $conversion_query = "SELECT
                                COUNT(CASE WHEN status = 'closed' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0) as conversion_rate
                                FROM leads WHERE associate_id = :associate_id";

            $stmt = $this->db->prepare($conversion_query);
            $stmt->execute(['associate_id' => $associateId]);
            $data['conversion'] = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Expenses
            $data['expenses'] = $this->expenseModel->getByAssociateId($associateId);
            $data['expense_stats'] = $this->expenseModel->getStats($associateId);
        } catch (\Exception $e) {
            error_log("Error fetching CRM data: " . $e->getMessage());
        }

        // Add extra JS for LocationBankHelper
        $data['extra_js'] = '<script src="' . BASE_URL . 'public/js/location-bank-helper.js"></script>';

        $this->view('associates/crm', $data, 'layouts/base');
    }

    /**
     * Store a new customer
     */
    public function storeCustomer()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'pincode' => $_POST['pincode'] ?? '',
                'account_name' => $_POST['account_name'] ?? '',
                'account_number' => $_POST['account_number'] ?? '',
                'ifsc_code' => $_POST['ifsc_code'] ?? '',
                'bank_name' => $_POST['bank_name'] ?? '',
                'branch_name' => $_POST['branch_name'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (empty($data['name']) || empty($data['phone'])) {
                $this->setFlash('error', 'Name and Phone are required.');
                $this->redirect('/associate/crm');
            }

            try {
                $this->db->beginTransaction();

                // Insert customer
                $sql = "INSERT INTO customers (name, email, phone, address, city, state, pincode, account_name, account_number, ifsc_code, bank_name, branch_name, created_at)
                        VALUES (:name, :email, :phone, :address, :city, :state, :pincode, :account_name, :account_number, :ifsc_code, :bank_name, :branch_name, :created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                // Log activity
                $activitySql = "INSERT INTO associate_activities (associate_id, activity_type, description, created_at)
                                VALUES (:associate_id, 'customer_added', :description, NOW())";
                $stmt = $this->db->prepare($activitySql);
                $stmt->execute([
                    'associate_id' => $associateId,
                    'description' => "Added new customer: " . $data['name']
                ]);

                $this->db->commit();
                $this->setFlash('success', 'Customer added successfully!');
            } catch (\Exception $e) {
                $this->db->rollBack();
                error_log("Error adding customer: " . $e->getMessage());
                $this->setFlash('error', 'Failed to add customer. Please try again.');
            }

            $this->redirect('/associate/crm');
        }
    }

    /**
     * Schedule a visit (Appointment)
     */
    public function scheduleVisit()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            // Determine if it's a customer or lead
            $customerId = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
            $leadId = !empty($_POST['lead_id']) ? $_POST['lead_id'] : null;

            $data = [
                'associate_id' => $associateId,
                'customer_id' => $customerId,
                'lead_id' => $leadId,
                'property_id' => !empty($_POST['property_id']) ? $_POST['property_id'] : null,
                'visit_date' => $_POST['visit_date'] ?? '',
                'visit_time' => $_POST['visit_time'] ?? '',
                'visit_type' => $_POST['visit_type'] ?? 'site_visit',
                'location_address' => $_POST['location'] ?? '', // Map location to location_address
                'notes' => $_POST['notes'] ?? '',
                'status' => 'scheduled',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (empty($data['visit_date']) || empty($data['visit_time'])) {
                $this->setFlash('error', 'Date and time are required.');
                $this->redirect('/associate/crm');
            }

            try {
                // Insert into property_visits
                $sql = "INSERT INTO property_visits (associate_id, customer_id, lead_id, property_id, visit_date, visit_time, visit_type, location_address, notes, status, created_at)
                        VALUES (:associate_id, :customer_id, :lead_id, :property_id, :visit_date, :visit_time, :visit_type, :location_address, :notes, :status, :created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Visit scheduled successfully!');
            } catch (\Exception $e) {
                error_log("Error scheduling visit: " . $e->getMessage());
                $this->setFlash('error', 'Failed to schedule visit. Please try again.');
            }

            $this->redirect('/associate/crm');
        }
    }

    /**
     * Store a field visit (Location Tracking - Client Check-in)
     */
    public function storeClientVisitLocation()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            // Determine if it's a customer or lead
            $customerId = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
            $leadId = !empty($_POST['lead_id']) ? $_POST['lead_id'] : null;

            $data = [
                'associate_id' => $associateId,
                'customer_id' => $customerId,
                'lead_id' => $leadId,
                'property_id' => null, // Can be updated if property is selected in check-in
                'visit_date' => date('Y-m-d'),
                'visit_time' => date('H:i:s'),
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null,
                'location_address' => $_POST['location_address'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'visit_type' => 'site_visit', // Default for check-in
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (empty($data['latitude']) || empty($data['longitude'])) {
                $this->setFlash('error', 'Location data is missing. Please enable GPS.');
                $this->redirect('/associate/crm');
            }

            try {
                $sql = "INSERT INTO property_visits (associate_id, customer_id, lead_id, property_id, visit_date, visit_time, latitude, longitude, location_address, notes, visit_type, status, created_at)
                        VALUES (:associate_id, :customer_id, :lead_id, :property_id, :visit_date, :visit_time, :latitude, :longitude, :location_address, :notes, :visit_type, :status, :created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Visit marked successfully with location!');
            } catch (\Exception $e) {
                error_log("Error marking visit: " . $e->getMessage());
                $this->setFlash('error', 'Failed to mark visit. Please try again.');
            }

            $this->redirect('/associate/crm');
        }
    }



    /**
     * Update lead (extended)
     */
    public function updateLead()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];
            $leadId = $_POST['lead_id'] ?? 0;

            if (!$leadId) {
                $this->setFlash('error', 'Invalid lead ID.');
                $this->redirect('/associate/crm');
            }

            $data = [
                'lead_id' => $leadId,
                'associate_id' => $associateId,
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'budget' => $_POST['budget'] ?? null,
                'property_type' => $_POST['property_type'] ?? null,
                'location' => $_POST['location'] ?? '',
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'pincode' => $_POST['pincode'] ?? '',
                'account_name' => $_POST['account_name'] ?? null,
                'account_number' => $_POST['account_number'] ?? null,
                'ifsc_code' => $_POST['ifsc_code'] ?? null,
                'bank_name' => $_POST['bank_name'] ?? null,
                'branch_name' => $_POST['branch_name'] ?? null,
                'status' => $_POST['status'] ?? 'new',
                'priority' => $_POST['priority'] ?? 'medium',
                'notes' => $_POST['notes'] ?? ''
            ];

            // Handle empty budget
            if ($data['budget'] === '') $data['budget'] = null;

            try {
                $sql = "UPDATE leads 
                        SET first_name = :name, 
                            email = :email, 
                            phone = :phone, 
                            budget = :budget,
                            property_type = :property_type,
                            location = :location,
                            address = :address,
                            city = :city,
                            state = :state,
                            pincode = :pincode,
                            account_name = :account_name,
                            account_number = :account_number,
                            ifsc_code = :ifsc_code,
                            bank_name = :bank_name,
                            branch_name = :branch_name,
                            status = :status, 
                            priority = :priority, 
                            notes = :notes, 
                            updated_at = NOW() 
                        WHERE id = :lead_id AND associate_id = :associate_id";

                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                if ($stmt->rowCount() > 0) {
                    $this->setFlash('success', 'Lead updated successfully!');
                } else {
                    // Check if lead exists but no changes made
                    $this->setFlash('info', 'No changes made or lead not found.');
                }
            } catch (\Exception $e) {
                error_log("Error updating lead: " . $e->getMessage());
                $this->setFlash('error', 'Failed to update lead. Please try again.');
            }

            $this->redirect('/associate/crm');
        }
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

        // Get commission summary for total earnings
        $commissionSummary = $this->associateModel->getCommissionSummary($associateId);

        // Get recent bookings
        $recentBookings = $this->associateModel->getRecentBookings($associateId);

        // Get recent transactions
        $recentTransactions = $this->associateModel->getRecentTransactions($associateId);

        $data = [
            'associate' => $associate,
            'stats' => $stats,
            'recent_commissions' => $recentCommissions,
            'recent_bookings' => $recentBookings,
            'recent_transactions' => $recentTransactions,
            'rank_info' => $rankInfo,
            'pending_payouts' => $pendingPayouts,
            'commission_summary' => $commissionSummary,
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
        usort($topPerformers, function ($a, $b) {
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
            $this->setFlash('error', "Minimum payout amount is ₹{$minPayout}");
            $this->redirect('/associate/payouts');
        }

        if ($amount > $availableBalance) {
            $this->setFlash('error', "Insufficient balance. Available: ₹{$availableBalance}");
            $this->redirect('/associate/payouts');
        }

        // Request payout
        $success = $this->associateModel->requestPayout($associateId, $amount, $paymentMethod, $accountDetails);

        if ($success) {
            $this->setFlash('success', 'Payout request submitted successfully. You will be notified once processed.');
        } else {
            $this->setFlash('error', 'Failed to submit payout request. Please try again.');
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
            $this->setFlash('success', 'Profile updated successfully.');
        } else {
            $this->setFlash('error', 'Failed to update profile. Please try again.');
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
            $this->setFlash('success', 'KYC documents submitted successfully. Verification is pending.');
        } else {
            $this->setFlash('error', 'Failed to submit KYC documents. Please try again.');
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
     * Store a new note
     */
    public function storeNote()
    {
        $this->checkAssociateLogin();
        $associateId = $_SESSION['associate_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'associate_id' => $associateId,
                'note_type' => $_POST['note_type'],
                'related_id' => $_POST['related_id'],
                'note' => $_POST['note'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            try {
                $sql = "INSERT INTO associate_notes (associate_id, note_type, related_id, note, created_at) 
                        VALUES (:associate_id, :note_type, :related_id, :note, :created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Note added successfully!');
            } catch (\Exception $e) {
                error_log("Error adding note: " . $e->getMessage());
                $this->setFlash('error', 'Failed to add note.');
            }
            $this->redirect('/associate/crm');
        }
    }

    public function storeActivity()
    {
        $this->checkAssociateLogin();
        $associateId = $_SESSION['associate_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->logAssociateActivity($associateId, $_POST['activity_type'], $_POST['description']);
            $this->setFlash('success', 'Activity added successfully!');
            $this->redirect('/associate/crm');
        }
    }

    public function storeFieldVisit()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            $latitude = $_POST['latitude'] ?? null;
            $longitude = $_POST['longitude'] ?? null;

            if (empty($latitude) || empty($longitude)) {
                $this->setFlash('error', 'Location data is required to mark a field visit.');
                $this->redirect('/associate/crm');
            }

            $data = [
                'associate_id' => $associateId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_address' => $_POST['location_address'] ?? null,
                'notes' => $_POST['notes'] ?? '',
                'visit_date' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];

            try {
                // associate_field_visits is the new table for tracking employee location check-ins
                $sql = "INSERT INTO associate_field_visits (associate_id, latitude, longitude, location_address, notes, visit_date, status) 
                        VALUES (:associate_id, :latitude, :longitude, :location_address, :notes, :visit_date, :status)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Field visit check-in marked successfully!');
                // Log activity
                $this->logAssociateActivity($associateId, 'field_visit', 'Marked a field visit check-in');
            } catch (\Exception $e) {
                error_log("Error adding field visit: " . $e->getMessage());
                $this->setFlash('error', 'Failed to mark visit.');
            }

            $this->redirect('/associate/crm');
        }
    }

    /**
     * Schedule a client site visit (for leads or customers)
     */
    public function scheduleClientVisit()
    {
        $this->checkAssociateLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $associateId = $_SESSION['associate_id'];

            $data = [
                'associate_id' => $associateId,
                'customer_id' => !empty($_POST['customer_id']) ? $_POST['customer_id'] : null,
                'lead_id' => !empty($_POST['lead_id']) ? $_POST['lead_id'] : null,
                'property_id' => $_POST['property_id'] ?? null,
                'visit_date' => $_POST['visit_date'] ?? date('Y-m-d'),
                'visit_time' => $_POST['visit_time'] ?? date('H:i:s'),
                'visit_type' => $_POST['visit_type'] ?? 'site_visit',
                'notes' => $_POST['notes'] ?? '',
                'status' => 'scheduled',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (empty($data['customer_id']) && empty($data['lead_id'])) {
                $this->setFlash('error', 'Please select a customer or lead.');
                $this->redirect('/associate/crm');
            }

            if (empty($data['property_id'])) {
                $this->setFlash('error', 'Please select a property.');
                $this->redirect('/associate/crm');
            }

            try {
                // property_visits is the table for client site visits
                $sql = "INSERT INTO property_visits (associate_id, customer_id, lead_id, property_id, visit_date, visit_time, visit_type, notes, status, created_at, updated_at) 
                        VALUES (:associate_id, :customer_id, :lead_id, :property_id, :visit_date, :visit_time, :visit_type, :notes, :status, :created_at, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Client visit scheduled successfully!');
                $this->logAssociateActivity($associateId, 'schedule_visit', 'Scheduled a client site visit');
            } catch (\Exception $e) {
                error_log("Error scheduling client visit: " . $e->getMessage());
                $this->setFlash('error', 'Failed to schedule visit.');
            }

            $this->redirect('/associate/crm');
        }
    }

    public function sendMessage()
    {
        $this->checkAssociateLogin();
        $associateId = $_SESSION['associate_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'associate_id' => $associateId,
                'recipient_type' => $_POST['recipient_type'],
                'recipient_id' => $_POST['recipient_id'],
                'message_type' => $_POST['message_type'],
                'subject' => $_POST['subject'],
                'message' => $_POST['message'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            try {
                $sql = "INSERT INTO associate_messages (associate_id, recipient_type, recipient_id, message_type, subject, message, created_at) 
                        VALUES (:associate_id, :recipient_type, :recipient_id, :message_type, :subject, :message, :created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);

                $this->setFlash('success', 'Message sent successfully!');
            } catch (\Exception $e) {
                error_log("Error sending message: " . $e->getMessage());
                $this->setFlash('error', 'Failed to send message.');
            }
            $this->redirect('/associate/crm');
        }
    }

    private function logAssociateActivity($associateId, $type, $description)
    {
        try {
            $sql = "INSERT INTO associate_activities (associate_id, activity_type, description, created_at) 
                    VALUES (:associate_id, :activity_type, :description, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'associate_id' => $associateId,
                'activity_type' => $type,
                'description' => $description
            ]);
        } catch (\Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
        }
    }

    /**
     * Middleware to check associate authentication
     */
    protected function middleware($middleware, array $options = [])
    {
        if ($middleware === 'associate.auth' && !$this->isAssociateLoggedIn()) {
            $this->redirect('/associate/login');
        }
    }
}
