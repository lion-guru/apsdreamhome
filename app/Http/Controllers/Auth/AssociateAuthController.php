<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;

/**
 * Associate Authentication Controller
 * Handles associate registration and login
 */
class AssociateAuthController extends BaseController
{
    /**
     * Show associate registration form
     */
    public function associateRegister()
    {
        $data = [
            'page_title' => 'Associate Registration - APS Dream Home',
            'page_description' => 'Register as an associate partner'
        ];

        $this->render('auth/associate_register', $data);
    }

    /**
     * Handle associate registration
     */
    public function handleAssociateRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/register');
            return;
        }

        // Get form data
        $name = $this->sanitizeInput($_POST['name'] ?? '');
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $phone = $this->sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $experience = $this->sanitizeInput($_POST['experience'] ?? '');
        $referral_code = $this->sanitizeInput($_POST['referral_code'] ?? '');

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors[] = "Name is required";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }

        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = "Valid 10-digit phone number is required";
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/associate/register');
            return;
        }

        // Check if email already exists
        try {
            $pdo = $this->getDatabase();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $_SESSION['errors'] = ["Email already registered"];
                $_SESSION['old_input'] = $_POST;
                $this->redirect('/associate/register');
                return;
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Verify referral code if provided
            $referrer_id = null;
            if (!empty($referral_code)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ? AND status = 'active' LIMIT 1");
                $stmt->execute([$referral_code]);
                $referrer = $stmt->fetch();
                if ($referrer) {
                    $referrer_id = $referrer['id'];
                } else {
                    // If invalid referral code, assign to company (ID = 1)
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE user_type = 'admin' AND status = 'active' LIMIT 1");
                    $stmt->execute();
                    $company_admin = $stmt->fetch();
                    $referrer_id = $company_admin ? $company_admin['id'] : 1;
                    $_SESSION['info'] = "Invalid referral code. You have been assigned to company referral.";
                }
            } else {
                // If no referral code provided, assign to company
                $stmt = $pdo->prepare("SELECT id FROM users WHERE user_type = 'admin' AND status = 'active' LIMIT 1");
                $stmt->execute();
                $company_admin = $stmt->fetch();
                $referrer_id = $company_admin ? $company_admin['id'] : 1;
            }

            // Generate associate ID and referral code
            $associate_id = 'ASC' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $user_referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);

            // Insert associate
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    customer_id, name, email, phone, password, 
                    referral_code, referrer_id, user_type, experience,
                    status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'associate', ?, 'pending', NOW(), NOW())
            ");

            $stmt->execute([
                $associate_id,
                $name,
                $email,
                $phone,
                $hashed_password,
                $user_referral_code,
                $referrer_id,
                $experience
            ]);

            $_SESSION['success'] = "Registration successful! Your Associate ID: $associate_id. Your account is pending approval.";
            $this->redirect('/associate/login');
        } catch (Exception $e) {
            error_log("Associate Registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed. Please try again."];
            $this->redirect('/associate/register');
        }
    }

    /**
     * Show associate login form
     */
    public function associateLogin()
    {
        // Redirect if already logged in
        if ($this->isLoggedIn() && ($_SESSION['user_type'] ?? '') === 'associate') {
            $this->redirect('/associate/dashboard');
            return;
        }

        $data = [
            'page_title' => 'Associate Login - APS Dream Home',
            'page_description' => 'Login to your associate dashboard'
        ];

        $this->render('auth/associate_login', $data);
    }

    /**
     * Handle associate authentication
     */
    public function authenticateAssociate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/login');
            return;
        }

        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            $this->redirect('/associate/login');
            return;
        }

        try {
            $pdo = $this->getDatabase();
            $stmt = $pdo->prepare("
                SELECT * FROM users 
                WHERE email = ? AND user_type = 'associate' AND status = 'active' 
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['associate_id'] = $user['customer_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'associate';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();

                $this->redirect('/associate/dashboard');
            } else {
                $_SESSION['errors'] = ["Invalid email or password"];
                $this->redirect('/associate/login');
            }
        } catch (Exception $e) {
            error_log("Associate Login error: " . $e->getMessage());
            $_SESSION['errors'] = ["Login failed. Please try again."];
            $this->redirect('/associate/login');
        }
    }
}
