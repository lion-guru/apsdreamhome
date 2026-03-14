<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;

/**
 * Customer Authentication Controller
 * Handles customer registration and login
 */
class CustomerAuthController extends BaseController
{
    /**
     * Show customer registration form
     */
    public function register()
    {
        $data = [
            'page_title' => 'Customer Registration - APS Dream Home',
            'page_description' => 'Register as a customer to buy/sell properties'
        ];
        
        $this->render('auth/customer_register', $data);
    }
    
    /**
     * Handle customer registration
     */
    public function handleRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }
        
        // Get form data
        $name = $this->sanitizeInput($_POST['name'] ?? '');
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $phone = $this->sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
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
            $this->redirect('/register');
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
                $this->redirect('/register');
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
            
            // Generate customer ID and referral code
            $customer_id = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $user_referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            
            // Insert customer
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    customer_id, name, email, phone, password, 
                    referral_code, referrer_id, user_type, status, 
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'customer', 'active', NOW(), NOW())
            ");
            
            $stmt->execute([
                $customer_id, $name, $email, $phone, $hashed_password,
                $user_referral_code, $referrer_id
            ]);
            
            $_SESSION['success'] = "Registration successful! Your Customer ID: $customer_id";
            $this->redirect('/login');
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed. Please try again."];
            $this->redirect('/register');
        }
    }
    
    /**
     * Show customer login form
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isLoggedIn() && ($_SESSION['user_type'] ?? '') === 'customer') {
            $this->redirect('/dashboard');
            return;
        }
        
        $data = [
            'page_title' => 'Customer Login - APS Dream Home',
            'page_description' => 'Login to your customer dashboard'
        ];
        
        $this->render('auth/customer_login', $data);
    }
    
    /**
     * Handle customer authentication
     */
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            $this->redirect('/login');
            return;
        }
        
        try {
            $pdo = $this->getDatabase();
            $stmt = $pdo->prepare("
                SELECT * FROM users 
                WHERE email = ? AND user_type = 'customer' AND status = 'active' 
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['customer_id'] = $user['customer_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'customer';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                $this->redirect('/dashboard');
            } else {
                $_SESSION['errors'] = ["Invalid email or password"];
                $this->redirect('/login');
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['errors'] = ["Login failed. Please try again."];
            $this->redirect('/login');
        }
    }
}
