<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function requireCustomerLogin()
    {
        @session_start();

        // Check if user is logged in (user_id exists and user_type is customer or empty)
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        // Accept customer type OR default to customer if not specified
        $userType = $_SESSION['user_type'] ?? '';
        if ($userType !== '' && $userType !== 'customer') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    private function getUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/user/logout');
            exit;
        }

        return $user;
    }

    public function dashboard()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch bookings (purchased plots/properties)
        $bookings = $this->db->fetchAll("
            SELECT b.*, 
                   COALESCE(p.plot_number, p2.plot_number) as plot_number,
                   COALESCE(p.block, p2.block) as block,
                   COALESCE(p.area_sqft, p2.area_sqft) as area_sqft,
                   COALESCE(p.total_price, p2.total_price) as plot_price,
                   COALESCE(p.status, p2.status) as plot_status,
                   COALESCE(c.name, c2.name) as colony_name
            FROM bookings b
            LEFT JOIN plots p ON b.property_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN plots p2 ON b.plot_id = p2.id
            LEFT JOIN colonies c2 ON p2.colony_id = c2.id
            WHERE b.customer_id = ?
            ORDER BY b.created_at DESC
        ", [$user['id']]);

        // Recent payments
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM payment_transactions WHERE user_id = ? AND user_type = 'customer' ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$_SESSION['user_id'] ?? 0]);
            $recentPayments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $recentPayments = [];
        }

        $data = [
            'page_title' => 'My Dashboard - APS Dream Home',
            'page_description' => 'Manage your properties and inquiries',
            'user' => $user,
            'properties' => $properties,
            'inquiries' => $inquiries,
            'bookings' => $bookings,
            'stats' => [
                'total_properties' => count($properties),
                'active_inquiries' => count(array_filter($inquiries, fn($i) => ($i['status'] ?? '') !== 'closed')),
                'total_bookings' => count($bookings),
                'total_inquiries' => count($inquiries),
            ],
            'recentPayments' => $recentPayments,
            'registered' => isset($_GET['registered']),
            'loginSuccess' => isset($_GET['login']),
        ];

        $this->layout = 'layouts/customer';
        $this->render('pages/user_dashboard', $data);
    }

    public function myProperties()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Properties - APS Dream Home',
            'page_description' => 'View and manage your listed properties',
            'user' => $user,
            'properties' => $properties,
        ];

        $this->layout = 'layouts/customer';
        $this->render('pages/user_properties', $data);
    }

    public function myInquiries()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Inquiries - APS Dream Home',
            'page_description' => 'Track your property inquiries',
            'user' => $user,
            'inquiries' => $inquiries,
        ];

        $this->layout = 'layouts/customer';
        $this->render('pages/user_inquiries', $data);
    }

    public function profile()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $error = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($phone)) {
                $error = 'Please fill in required fields.';
            } elseif (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Passwords do not match.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $this->db->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
                    $stmt->execute([$name, $phone, $hashedPassword, $_SESSION['user_id']]);

                    $_SESSION['user_name'] = $name;
                    $success = true;
                    $user['name'] = $name;
                    $user['phone'] = $phone;
                }
            } else {
                $stmt = $this->db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $_SESSION['user_id']]);

                $_SESSION['user_name'] = $name;
                $success = true;
                $user['name'] = $name;
                $user['phone'] = $phone;
            }
        }

        // Define BASE_PATH for shared view
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 3));
        }

        // Set variables for shared view
        $userRole = $user['role'] ?? 'customer';
        $profileUrl = BASE_URL . '/user/profile';
        $securityUrl = null; // Front users don't have security page yet
        $canEdit = true;

        // Use customer profile view with customer layout
        $this->layout = 'layouts/customer';
        
        $this->render('pages/user_profile', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'current_page' => 'profile'
        ]);
    }

    /**
     * Bank Details Page
     */
    public function bankDetails()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/bank-details');
            exit;
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user_bank_details', []);
    }

    /**
     * Save Bank Details
     */
    public function saveBankDetails()
    {
        @session_start();

        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $accountHolder = trim($_POST['account_holder'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = trim(strtoupper($_POST['ifsc_code'] ?? ''));
        $bankName = trim($_POST['bank_name'] ?? '');
        $branchName = trim($_POST['branch_name'] ?? '');
        $accountType = $_POST['account_type'] ?? 'savings';
        $upiId = trim($_POST['upi_id'] ?? '');

        // Validation
        if (empty($accountHolder) || empty($accountNumber) || empty($ifscCode)) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            header('Location: ' . BASE_URL . '/user/bank-details');
            exit;
        }

        // Check if account already exists
        $existing = $this->db->fetch(
            "SELECT id FROM user_bank_accounts WHERE user_id = ?",
            [$userId]
        );

        if ($existing) {
            // Update existing
            $stmt = $this->db->prepare("
                UPDATE user_bank_accounts 
                SET account_holder = ?, account_number = ?, ifsc_code = ?, 
                    bank_name = ?, branch_name = ?, account_type = ?, upi_id = ?
                WHERE user_id = ? AND is_primary = 1
            ");
            $stmt->execute([$accountHolder, $accountNumber, $ifscCode, $bankName, $branchName, $accountType, $upiId, $userId]);
        } else {
            // Insert new
            $stmt = $this->db->prepare("
                INSERT INTO user_bank_accounts 
                (user_id, account_holder, account_number, ifsc_code, bank_name, branch_name, account_type, upi_id, is_primary)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$userId, $accountHolder, $accountNumber, $ifscCode, $bankName, $branchName, $accountType, $upiId]);
        }

        $_SESSION['flash_success'] = 'Bank details saved successfully!';
        header('Location: ' . BASE_URL . '/user/bank-details');
        exit;
    }

    public function bookSiteVisit()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $db->prepare("INSERT INTO site_visit_requests (user_id, user_name, user_email, user_phone, property_id, preferred_date, preferred_time, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $_SESSION['user_name'] ?? '',
                    $_SESSION['user_email'] ?? '',
                    $_POST['phone'] ?? $_SESSION['user_phone'] ?? '',
                    (int)($_POST['property_id'] ?? 0),
                    $_POST['visit_date'] ?? '',
                    $_POST['visit_time'] ?? '',
                    $_POST['notes'] ?? '',
                ]);
                $message = 'Your site visit request has been submitted. Our team will contact you within 24 hours.';
                $message_type = 'success';
            }
            
            // Get user's properties for dropdown
            $props = $db->prepare("SELECT id, CONCAT(property_type, ' - ', address) as name FROM user_properties WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
            $props->execute([$_SESSION['user_id']]);
            $properties = $props->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $message = $message ?? 'An error occurred. Please try again.';
            $message_type = $message_type ?? 'danger';
            $properties = $properties ?? [];
        }
        
        $this->render('pages/user_book_site_visit', [
            'page_title' => 'Book Site Visit',
            'message' => $message ?? '',
            'message_type' => $message_type ?? '',
            'properties' => $properties ?? [],
        ]);
    }

    public function updateProfile()
    {
        $this->profile();
    }
}
