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
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Check if user is logged in (user_id exists and user_type is customer or empty)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Accept customer type OR default to customer if not specified
        $userType = $_SESSION['user_type'] ?? '';
        if ($userType !== '' && $userType !== 'customer') {
            header('Location: /login');
            exit;
        }
    }

    private function getUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: /user/logout');
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

        $data = [
            'page_title' => 'My Dashboard - APS Dream Home',
            'page_description' => 'Manage your properties and inquiries',
            'user' => $user,
            'properties' => $properties,
            'inquiries' => $inquiries,
            'registered' => isset($_GET['registered']),
            'loginSuccess' => isset($_GET['login']),
        ];

        $this->render('pages/user_dashboard', $data, 'layouts/customer');
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

        $this->render('pages/user_properties', $data, 'layouts/customer');
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

        $this->render('pages/user_inquiries', $data, 'layouts/customer');
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

        // Use unified shared profile view
        include __DIR__ . '/../../../views/shared/profile.php';
    }

    /**
     * Bank Details Page
     */
    public function bankDetails()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Please login first';
            header('Location: /login?redirect=/user/bank-details');
            exit;
        }

        $this->render('pages/user_bank_details', [], 'layouts/customer');
    }

    /**
     * Save Bank Details
     */
    public function saveBankDetails()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
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
            header('Location: /user/bank-details');
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
        header('Location: /user/bank-details');
        exit;
    }
}
