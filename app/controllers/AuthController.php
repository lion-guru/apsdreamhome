<?php
/**
 * Unified Auth Controller
 * Handles registration, login, and MLM referral tracking
 */

use App\Core\View;

class AuthController {
    private $conn;
    private $view;
    
    public function __construct() {
        $config = AppConfig::getInstance();
        $this->conn = $config->getDatabaseConnection();
        $this->view = new View();
    }
    
    /**
     * Handle unified registration
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processRegistration();
        }
        
        // Use modern View system
        return $this->view->render('auth.register');
    }
    
    /**
     * Process registration with MLM integration
     */
    private function processRegistration() {
        // Validate input
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $user_type = $_POST['user_type'] ?? 'customer';
        $referrer_code = trim($_POST['referrer_code'] ?? '');
        
        // Validation
        $errors = [];
        
        if (empty($full_name)) $errors[] = "Full name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required";
        if (empty($mobile) || strlen($mobile) != 10) $errors[] = "Valid 10-digit mobile required";
        if (empty($password) || strlen($password) < 6) $errors[] = "Password must be 6+ characters";
        
        // Check existing user
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? OR mobile = ?");
        $stmt->bind_param("ss", $email, $mobile);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email or mobile already registered";
        }
        
        // Verify referrer
        $sponsor_id = null;
        if ($referrer_code) {
            $stmt = $this->conn->prepare("SELECT user_id FROM mlm_profiles WHERE referral_code = ? AND status = 'active'");
            $stmt->bind_param("s", $referrer_code);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result) {
                $sponsor_id = $result['user_id'];
            } else {
                $errors[] = "Invalid referrer code";
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Generate referral code
        $referral_code = $this->generateReferralCode($full_name, $email);
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Insert user
            $stmt = $this->conn->prepare("INSERT INTO users (name, email, mobile, password, type, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->bind_param("sssss", $full_name, $email, $mobile, $hashed_password, $user_type);
            $stmt->execute();
            $user_id = $this->conn->insert_id;
            
            // Create MLM profile
            $stmt = $this->conn->prepare("INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, sponsor_code, user_type, verification_status, status) VALUES (?, ?, ?, ?, ?, 'verified', 'active')");
            $stmt->bind_param("isiss", $user_id, $referral_code, $sponsor_id, $referrer_code, $user_type);
            $stmt->execute();
            
            // Create referral record if sponsor exists
            if ($sponsor_id) {
                $stmt = $this->conn->prepare("INSERT INTO mlm_referrals (referrer_user_id, referred_user_id, referral_type, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iis", $sponsor_id, $user_id, $user_type);
                $stmt->execute();
                
                // Update sponsor's direct referrals
                $stmt = $this->conn->prepare("UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1 WHERE user_id = ?");
                $stmt->bind_param("i", $sponsor_id);
                $stmt->execute();
                
                // Build network tree
                $this->buildNetworkTree($user_id, $sponsor_id);
            }
            
            // Handle role-specific fields
            $this->handleRoleSpecificFields($user_id, $user_type);
            
            $this->conn->commit();
            
            return ['success' => true, 'user_id' => $user_id, 'referral_code' => $referral_code];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'errors' => ["Registration failed: " . $e->getMessage()]];
        }
    }
    
    /**
     * Generate unique referral code
     */
    private function generateReferralCode($name, $email) {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $suffix = strtoupper(substr(md5($email . time()), 0, 4));
        return $prefix . $suffix;
    }
    
    /**
     * Build network tree for new user
     */
    private function buildNetworkTree($user_id, $sponsor_id) {
        $level = 1;
        $current = $sponsor_id;
        
        while ($current) {
            $stmt = $this->conn->prepare("INSERT INTO mlm_network_tree (ancestor_user_id, descendant_user_id, level, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iii", $current, $user_id, $level);
            $stmt->execute();
            
            // Get next ancestor
            $stmt = $this->conn->prepare("SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
            $stmt->bind_param("i", $current);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result && $result['sponsor_user_id']) {
                $current = $result['sponsor_user_id'];
                $level++;
            } else {
                break;
            }
        }
    }
    
    /**
     * Handle role-specific fields
     */
    private function handleRoleSpecificFields($user_id, $user_type) {
        switch ($user_type) {
            case 'agent':
                $license = $_POST['license_number'] ?? '';
                $experience = $_POST['experience'] ?? 0;
                
                $stmt = $this->conn->prepare("INSERT INTO agent_details (user_id, license_number, experience_years) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $user_id, $license, $experience);
                $stmt->execute();
                break;
                
            case 'associate':
                $pan = $_POST['pan_number'] ?? '';
                $aadhar = $_POST['aadhar_number'] ?? '';
                
                $stmt = $this->conn->prepare("INSERT INTO associate_details (user_id, pan_number, aadhar_number) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $pan, $aadhar);
                $stmt->execute();
                break;
                
            case 'builder':
                $company = $_POST['company_name'] ?? '';
                $rera = $_POST['rera_registration'] ?? '';
                
                $stmt = $this->conn->prepare("INSERT INTO builder_details (user_id, company_name, rera_registration) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $company, $rera);
                $stmt->execute();
                break;
                
            case 'investor':
                $range = $_POST['investment_range'] ?? '';
                $type = $_POST['investment_type'] ?? '';
                
                $stmt = $this->conn->prepare("INSERT INTO investor_details (user_id, investment_range, investment_type) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $range, $type);
                $stmt->execute();
                break;
        }
    }
    
    /**
     * Get referral link for user
     */
    public function getReferralLink($user_id) {
        $stmt = $this->conn->prepare("SELECT referral_code FROM mlm_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            return BASE_URL . 'register?ref=' . $result['referral_code'];
        }
        return null;
    }
    
    /**
     * Get user's network stats
     */
    public function getNetworkStats($user_id) {
        // Get profile
        $stmt = $this->conn->prepare("SELECT * FROM mlm_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc();
        
        // Get direct referrals
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM mlm_referrals WHERE referrer_user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $direct_referrals = $stmt->get_result()->fetch_assoc()['count'];
        
        // Get total team size
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM mlm_network_tree WHERE ancestor_user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $total_team = $stmt->get_result()->fetch_assoc()['count'];
        
        // Get total commission
        $stmt = $this->conn->prepare("SELECT SUM(amount) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status = 'paid'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $total_commission = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        
        return [
            'profile' => $profile,
            'direct_referrals' => $direct_referrals,
            'total_team' => $total_team,
            'total_commission' => $total_commission,
            'referral_link' => $this->getReferralLink($user_id)
        ];
    }
    
    /**
     * Show login form
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processLogin();
        }
        
        // Use modern View system
        return $this->view->render('auth.login');
    }
    
    /**
     * Process login
     */
    public function processLogin() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $errors = [];
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email required";
        }
        if (empty($password)) {
            $errors[] = "Password required";
        }
        
        if (!empty($errors)) {
            return $this->view->render('auth.login', ['errors' => $errors, 'email' => $email]);
        }
        
        // Check user credentials
        $stmt = $this->conn->prepare("SELECT id, full_name, email, password, user_type, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->view->render('auth.login', ['errors' => ['Invalid credentials'], 'email' => $email]);
        }
        
        if ($user['status'] !== 'active') {
            return $this->view->render('auth.login', ['errors' => ['Account is not active'], 'email' => $email]);
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        
        // Redirect to dashboard
        header('Location: /dashboard');
        exit();
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        header('Location: /login');
        exit();
    }
}

// Helper functions
function getRoleIcon($type) {
    $icons = [
        'customer' => 'user',
        'agent' => 'user-tie',
        'associate' => 'users',
        'builder' => 'building',
        'investor' => 'chart-line'
    ];
    return $icons[$type] ?? 'user';
}

function getRoleDescription($type) {
    $descriptions = [
        'customer' => 'Buy properties and earn referral rewards',
        'agent' => 'Sell properties and earn commissions',
        'associate' => 'Build network and earn multi-level commissions',
        'builder' => 'List properties and manage sales',
        'investor' => 'Invest in properties and earn returns'
    ];
    return $descriptions[$type] ?? 'Standard user';
}
?>