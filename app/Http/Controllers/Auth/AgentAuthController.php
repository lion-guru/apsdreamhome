<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;

/**
 * Agent Authentication Controller
 * Handles agent registration and login
 */
class AgentAuthController extends BaseController
{
    /**
     * Show agent registration form
     */
    public function register()
    {
        $data = [
            'page_title' => 'Agent Registration - APS Dream Home',
            'page_description' => 'Register as a property agent'
        ];
        
        $this->render('auth/agent_register', $data);
    }
    
    /**
     * Handle agent registration
     */
    public function handleRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/agent/register');
            return;
        }
        
        // Get form data
        $name = $this->sanitizeInput($_POST['name'] ?? '');
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $phone = $this->sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $experience = $this->sanitizeInput($_POST['experience'] ?? '');
        $license_no = $this->sanitizeInput($_POST['license_no'] ?? '');
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
        
        if (empty($license_no)) {
            $errors[] = "License number is required";
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/agent/register');
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
                $this->redirect('/agent/register');
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
                }
            }
            
            // Generate agent ID and referral code
            $agent_id = 'AGT' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $user_referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            
            // Insert agent
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    customer_id, name, email, phone, password, 
                    referral_code, referrer_id, user_type, experience, license_no,
                    status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'agent', ?, ?, 'pending', NOW(), NOW())
            ");
            
            $stmt->execute([
                $agent_id, $name, $email, $phone, $hashed_password,
                $user_referral_code, $referrer_id, $experience, $license_no
            ]);
            
            $_SESSION['success'] = "Registration successful! Your Agent ID: $agent_id. Your account is pending approval.";
            $this->redirect('/agent/login');
            
        } catch (Exception $e) {
            error_log("Agent Registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed. Please try again."];
            $this->redirect('/agent/register');
        }
    }
    
    /**
     * Show agent login form
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isLoggedIn() && ($_SESSION['user_type'] ?? '') === 'agent') {
            $this->redirect('/agents/dashboard');
            return;
        }
        
        $data = [
            'page_title' => 'Agent Login - APS Dream Home',
            'page_description' => 'Login to your agent dashboard'
        ];
        
        $this->render('auth/agent_login', $data);
    }
    
    /**
     * Handle agent authentication
     */
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/agent/login');
            return;
        }
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            $this->redirect('/agent/login');
            return;
        }
        
        try {
            $pdo = $this->getDatabase();
            $stmt = $pdo->prepare("
                SELECT * FROM users 
                WHERE email = ? AND user_type = 'agent' AND status = 'active' 
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['agent_id'] = $user['customer_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'agent';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                $this->redirect('/agents/dashboard');
            } else {
                $_SESSION['errors'] = ["Invalid email or password"];
                $this->redirect('/agent/login');
            }
            
        } catch (Exception $e) {
            error_log("Agent Login error: " . $e->getMessage());
            $_SESSION['errors'] = ["Login failed. Please try again."];
            $this->redirect('/agent/login');
        }
    }
}
