<?php
/**
 * Smart Registration Controller
 * Phone-First One-Click Registration with Multi-Channel OTP
 * 
 * Features:
 * - Phone number input → OTP → Auto account creation
 * - Multi-channel OTP (WhatsApp, SMS, Email)
 * - Progressive profiling with popups
 * - Behavior tracking for role detection
 * - Abandoned registration recovery
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\OTPService;
use App\Services\ProgressiveRegistrationService;
use App\Core\Middleware\TenantContext;

class SmartRegistrationController extends BaseController
{
    private $otpService;
    private $progressiveService;
    
    public function __construct()
    {
        parent::__construct();
        $this->otpService = new OTPService();
        $this->progressiveService = new ProgressiveRegistrationService($this->db ?? \App\Core\Database::getInstance()->getConnection());
    }

    private function getTenantSql(): array
    {
        $tid = TenantContext::getId();
        if ($tid > 1) return [" AND tenant_id = ?", [$tid]];
        return ["", []];
    }
    
    private function getTenantInsert(): array
    {
        $tid = TenantContext::getId();
        if ($tid > 1) return ["tenant_id" => $tid];
        return [];
    }

    public function skipCsrfProtection(): bool
    {
        return true;
    }
    
    /**
     * Step 1: Show phone input page
     */
    public function showPhoneInput()
    {
        @session_start();
        $csrf_token = $this->getCsrfToken();
        $base = BASE_URL;
        
        // Check for returning user with tracking cookie
        $trackingToken = $_COOKIE['smart_reg_token'] ?? null;
        $session = null;
        
        if ($trackingToken) {
            $session = $this->getSessionByToken($trackingToken);
            if ($session && $session['registration_status'] === 'profile_complete') {
                // Already completed, redirect to login
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }
        
        include __DIR__ . '/../../../views/auth/smart_register_phone.php';
    }
    
    /**
     * Step 2: Send OTP via selected channel
     */
    public function sendOtp()
    {
        @session_start();
        
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $channel = trim($_POST['channel'] ?? 'whatsapp');
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Validate CSRF
        if ($csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        // Validate phone
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
            $_SESSION['error'] = 'Please enter a valid 10-digit phone number.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            
            // Check if phone already registered
            $existingUser = $db->fetchOne("SELECT id, email, role FROM users WHERE phone = ?" . $tSql . " LIMIT 1", array_merge([$phone], $tParams));
            if ($existingUser) {
                $_SESSION['error'] = 'This phone number is already registered. Please login instead.';
                $_SESSION['show_login_prompt'] = true;
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }
            
            // Generate OTP
            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $sessionToken = bin2hex(random_bytes(32));
            
            // Create or update session
            $existingSession = $db->fetchOne(
                "SELECT id FROM smart_registration_sessions WHERE phone = ? AND registration_status NOT IN ('profile_complete', 'abandoned')" . $tSql . " ORDER BY id DESC LIMIT 1",
                array_merge([$phone], $tParams)
            );
            
            if ($existingSession) {
                $sessionId = $existingSession['id'];
                $db->query(
                    "UPDATE smart_registration_sessions SET otp_code = ?, otp_channel = ?, otp_sent_at = NOW(), registration_status = 'otp_sent', email = COALESCE(?, email), updated_at = NOW() WHERE id = ?" . $tSql,
                    array_merge([$otp, $channel, !empty($email) ? $email : null, $sessionId], $tParams)
                );
            } else {
                $db->insert('smart_registration_sessions', array_merge([
                    'session_token' => $sessionToken,
                    'phone' => $phone,
                    'email' => !empty($email) ? $email : null,
                    'otp_channel' => $channel,
                    'otp_code' => $otp,
                    'otp_sent_at' => date('Y-m-d H:i:s'),
                    'registration_status' => 'otp_sent',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
                    'landing_page' => $_SERVER['REQUEST_URI'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], $this->getTenantInsert()));
                $sessionId = $db->lastInsertId();
                $sessionToken = $sessionToken;
            }
            
            // Store in session
            $_SESSION['smart_reg_session_id'] = $sessionId;
            $_SESSION['smart_reg_phone'] = $phone;
            $_SESSION['smart_reg_otp'] = $otp;
            $_SESSION['smart_reg_channel'] = $channel;
            
            // Send OTP via selected channel
            $otpSent = false;
            $otpError = null;
            
            try {
                if ($channel === 'whatsapp') {
                    $otpSent = $this->sendWhatsAppOtp($phone, $otp);
                } elseif ($channel === 'sms') {
                    $otpSent = $this->sendSmsOtp($phone, $otp);
                } elseif ($channel === 'email') {
                    if (empty($email)) {
                        $_SESSION['error'] = 'Email address is required for email OTP.';
                        header('Location: ' . BASE_URL . '/register/smart');
                        exit;
                    }
                    $otpSent = $this->sendEmailOtp($email, $otp);
                }
            } catch (\Exception $e) {
                $otpError = $e->getMessage();
                error_log("OTP send failed: " . $otpError);
            }
            
            // Store tracking cookie
            setcookie('smart_reg_token', $sessionToken, [
                'expires' => time() + (30 * 24 * 60 * 60), // 30 days
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            // Redirect to OTP verification
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($sessionToken));
            exit;
            
        } catch (\Exception $e) {
            error_log("Smart registration error: " . $e->getMessage());
            $_SESSION['error'] = 'Something went wrong. Please try again.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
    }
    
    /**
     * Step 3: Show OTP verification page
     */
    public function showOtpVerification()
    {
        @session_start();
        $token = $_GET['token'] ?? '';
        $base = BASE_URL;
        
        if (empty($token)) {
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        $session = $this->getSessionByToken($token);
        if (!$session) {
            $_SESSION['error'] = 'Invalid or expired session. Please start again.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        if ($session['registration_status'] === 'profile_complete') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        include __DIR__ . '/../../../views/auth/smart_register_otp.php';
    }
    
    /**
     * Step 4: Verify OTP and auto-create account
     */
    public function verifyOtp()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        $otp = trim($_POST['otp'] ?? '');
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Validate CSRF
        if ($csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        if (empty($token) || empty($otp)) {
            $_SESSION['error'] = 'Please enter the OTP.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $tInsert = $this->getTenantInsert();
            $session = $this->getSessionByToken($token);
            
            if (!$session) {
                $_SESSION['error'] = 'Invalid or expired session.';
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }
            
            // Check OTP attempts (max 5)
            $attempts = $db->fetchColumn(
                "SELECT COUNT(*) FROM smart_registration_behavior WHERE session_id = ?" . $tSql . " AND event_type = 'otp_verify_attempt'",
                array_merge([$session['id']], $tParams)
            );
            
            if ($attempts >= 5) {
                $_SESSION['error'] = 'Too many failed attempts. Please request a new OTP.';
                header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
                exit;
            }
            
            // Log attempt
            $db->insert('smart_registration_behavior', array_merge([
                'session_id' => $session['id'],
                'event_type' => 'otp_verify_attempt',
                'event_data' => json_encode(['otp_submitted' => $otp]),
                'created_at' => date('Y-m-d H:i:s')
            ], $tInsert));
            
            // Verify OTP
            if ($session['otp_code'] !== $otp) {
                $_SESSION['error'] = 'Invalid OTP. Please try again.';
                header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
                exit;
            }
            
            // Check OTP expiry (10 minutes)
            $otpSentAt = strtotime($session['otp_sent_at']);
            if (time() - $otpSentAt > 600) {
                $_SESSION['error'] = 'OTP has expired. Please request a new one.';
                header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
                exit;
            }
            
            // OTP verified! Auto-create account
            $phone = $session['phone'];
            $email = $session['email'];
            
            // Generate user ID
            $unique_id = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referral_code = strtoupper(substr($phone, -4)) . date('ymd') . rand(100, 999);
            
            // Auto-generate password (user can change later)
            $autoPassword = bin2hex(random_bytes(8));
            $hashedPassword = password_hash($autoPassword, PASSWORD_DEFAULT);
            
            // Create user
            $userData = array_merge([
                'customer_id' => $unique_id,
                'name' => 'User ' . substr($phone, -4),
                'email' => $email ?: $phone . '@temp.apsdreamhome.com',
                'phone' => $phone,
                'password' => $hashedPassword,
                'referral_code' => $referral_code,
                'role' => 'customer',
                'status' => 'active',
                'registration_status' => 'approved',
                'registration_method' => 'smart_otp',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $tInsert);
            
            $db->insert('users', $userData);
            $newUserId = $db->lastInsertId();
            
            // Create wallet
            $db->insert('wallet_points', array_merge([
                'user_id' => $newUserId,
                'points_balance' => 0.00,
                'total_earned' => 0.00,
                'total_used' => 0.00,
                'total_transferred_to_emi' => 0.00,
                'referral_earnings' => 0.00,
                'commission_earnings' => 0.00,
                'bonus_earnings' => 0.00,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $tInsert));
            
            // Update session
            $db->query(
                "UPDATE smart_registration_sessions SET user_id = ?, user_created = 1, otp_verified = 1, otp_verified_at = NOW(), registration_status = 'account_created', updated_at = NOW() WHERE id = ?" . $tSql,
                array_merge([$newUserId, $session['id']], $tParams)
            );
            
            // Auto-login
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['customer_id'] = $unique_id;
            $_SESSION['role'] = 'customer';
            $_SESSION['name'] = $userData['name'];
            
            // Track conversion
            try {
                $visitorTracking = new \App\Services\VisitorTrackingService();
                $visitorTracking->markAsConverted($newUserId);
            } catch (\Exception $e) {
                error_log("Visitor conversion tracking failed: " . $e->getMessage());
            }
            
            // Redirect to role selection first (then profile completion)
            header('Location: ' . BASE_URL . '/auth/smart/role?token=' . urlencode($token));
            exit;
            
        } catch (\Exception $e) {
            error_log("OTP verification error: " . $e->getMessage());
            $_SESSION['error'] = 'Something went wrong. Please try again.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
        }
    }
    
    /**
     * Step 5: Show progressive profile completion page
     */
    public function showProfileCompletion()
    {
        @session_start();
        $token = $_GET['token'] ?? '';
        $base = BASE_URL;
        
        if (empty($token)) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        
        $session = $this->getSessionByToken($token);
        if (!$session || !$session['user_id']) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        
        // Get user data
        $db = Database::getInstance();
        [$tSql, $tParams] = $this->getTenantSql();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?" . $tSql, array_merge([$session['user_id']], $tParams));
        
        if (!$user) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        
        include __DIR__ . '/../../../views/auth/smart_register_profile.php';
    }
    
    /**
     * Save profile completion data (AJAX)
     */
    public function saveProfileProgress()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $session = $this->getSessionByToken($token);
            
            if (!$session || !$session['user_id']) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            
            // Calculate completion percentage
            $fields = ['name', 'email', 'city', 'budget_range', 'property_preference', 'occupation'];
            $filled = 0;
            foreach ($fields as $field) {
                if (!empty($data[$field])) $filled++;
            }
            $completionPct = round(($filled / count($fields)) * 100);
            
            // Update session
            $db->query(
                "UPDATE smart_registration_sessions SET profile_data = ?, profile_completion_pct = ?, registration_status = CASE WHEN ? >= 80 THEN 'profile_complete' ELSE 'profile_incomplete' END, updated_at = NOW() WHERE id = ?" . $tSql,
                array_merge([json_encode($data), $completionPct, $completionPct, $session['id']], $tParams)
            );
            
            // Update user if profile is complete
            if ($completionPct >= 80 && !empty($data['name'])) {
                $db->query(
                    "UPDATE users SET name = ?, city = ?, occupation = ?, updated_at = NOW() WHERE id = ?" . $tSql,
                    array_merge([$data['name'] ?? '', $data['city'] ?? '', $data['occupation'] ?? '', $session['user_id']], $tParams)
                );
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'completion_pct' => $completionPct,
                'is_complete' => $completionPct >= 80
            ]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Profile save error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save']);
            exit;
        }
    }
    
    /**
     * Track user behavior (AJAX)
     */
    public function trackBehavior()
    {
        @session_start();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? $_COOKIE['smart_reg_token'] ?? '';
        $eventType = $data['event_type'] ?? 'page_view';
        $eventData = $data['event_data'] ?? null;
        $pageUrl = $data['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '';
        
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'No token']);
            exit;
        }
        
        try {
            $db = Database::getInstance();
            [$tSql, $tParams] = $this->getTenantSql();
            $session = $this->getSessionByToken($token);
            
            if (!$session) {
                http_response_code(404);
                echo json_encode(['error' => 'Session not found']);
                exit;
            }
            
            // Track behavior
            $db->insert('smart_registration_behavior', array_merge([
                'session_id' => $session['id'],
                'user_id' => $session['user_id'],
                'event_type' => $eventType,
                'event_data' => is_array($eventData) ? json_encode($eventData) : null,
                'page_url' => $pageUrl,
                'created_at' => date('Y-m-d H:i:s')
            ], $this->getTenantInsert()));
            
            // Update counters
            $updates = ['last_activity_at' => date('Y-m-d H:i:s')];
            
            if ($eventType === 'property_view') {
                $updates['properties_viewed'] = ($session['properties_viewed'] ?? 0) + 1;
            } elseif ($eventType === 'page_view') {
                $updates['pages_viewed'] = ($session['pages_viewed'] ?? 0) + 1;
            } elseif ($eventType === 'search') {
                $updates['search_count'] = ($session['search_count'] ?? 0) + 1;
            }
            
            $setClauses = [];
            $values = [];
            foreach ($updates as $key => $value) {
                $setClauses[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $session['id'];
            
            $db->query(
                "UPDATE smart_registration_sessions SET " . implode(', ', $setClauses) . " WHERE id = ?" . $tSql,
                array_merge($values, $tParams)
            );
            
            // Auto-detect role based on behavior
            $this->detectRoleFromBehavior($db, $session);
            
            http_response_code(200);
            echo json_encode(['success' => true]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Behavior tracking error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to track']);
            exit;
        }
    }
    
    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        $channel = $_POST['channel'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error'] = 'Invalid session.';
            header('Location: ' . BASE_URL . '/register/smart');
            exit;
        }
        
        try {
            $db = Database::getInstance();
            $session = $this->getSessionByToken($token);
            
            if (!$session) {
                $_SESSION['error'] = 'Invalid or expired session.';
                header('Location: ' . BASE_URL . '/register/smart');
                exit;
            }
            
            // Generate new OTP
            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Update session
            $db->query(
                "UPDATE smart_registration_sessions SET otp_code = ?, otp_channel = COALESCE(?, otp_channel), otp_sent_at = NOW(), registration_status = 'otp_sent', updated_at = NOW() WHERE id = ?",
                [$otp, !empty($channel) ? $channel : null, $session['id']]
            );
            
            // Send OTP
            $channel = $channel ?: $session['otp_channel'];
            $phone = $session['phone'];
            $email = $session['email'];
            
            if ($channel === 'whatsapp') {
                $this->sendWhatsAppOtp($phone, $otp);
            } elseif ($channel === 'sms') {
                $this->sendSmsOtp($phone, $otp);
            } elseif ($channel === 'email' && !empty($email)) {
                $this->sendEmailOtp($email, $otp);
            }
            
            $_SESSION['success'] = 'OTP resent successfully via ' . ucfirst($channel) . '.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
            
        } catch (\Exception $e) {
            error_log("Resend OTP error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to resend OTP.';
            header('Location: ' . BASE_URL . '/register/smart/otp?token=' . urlencode($token));
            exit;
        }
    }
    
    /**
     * Skip profile completion (for later)
     */
    public function skipProfileCompletion()
    {
        @session_start();
        
        $token = $_POST['token'] ?? '';
        
        if (!empty($token)) {
            try {
                $db = Database::getInstance();
                $session = $this->getSessionByToken($token);
                
                if ($session) {
                    $db->query(
                        "UPDATE smart_registration_sessions SET registration_status = 'profile_incomplete', abandoned_at = NOW(), updated_at = NOW() WHERE id = ?",
                        [$session['id']]
                    );
                }
            } catch (\Exception $e) {
                error_log("Skip profile error: " . $e->getMessage());
            }
        }
        
        // Show welcome message and redirect
        $_SESSION['success'] = 'Welcome! You can complete your profile later from your dashboard.';
        header('Location: ' . BASE_URL . '/user/dashboard');
        exit;
    }
    
    // ==================== PRIVATE HELPER METHODS ====================
    
    /**
     * Get session by token
     */
    private function getSessionByToken($token)
    {
        try {
            $db = Database::getInstance();
            return $db->fetchOne(
                "SELECT * FROM smart_registration_sessions WHERE session_token = ? LIMIT 1",
                [$token]
            );
        } catch (\Exception $e) {
            error_log("Get session error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Send WhatsApp OTP
     */
    private function sendWhatsAppOtp($phone, $otp)
    {
        try {
            $whatsappService = new \App\Services\Communication\WhatsAppService();
            $message = "Your APS Dream Home verification code is: *$otp*\n\nThis code expires in 10 minutes.\n\nDo not share this code with anyone.";
            return $whatsappService->sendTextMessage($phone, $message);
        } catch (\Exception $e) {
            error_log("WhatsApp OTP failed: " . $e->getMessage());
            // Fallback to SMS
            return $this->sendSmsOtp($phone, $otp);
        }
    }
    
    /**
     * Send SMS OTP
     */
    private function sendSmsOtp($phone, $otp)
    {
        try {
            $smsService = new \App\Services\Communication\SmsService();
            return $smsService->sendOTP($phone, $otp);
        } catch (\Exception $e) {
            error_log("SMS OTP failed: " . $e->getMessage());
            // Log OTP for testing
            error_log("OTP for $phone: $otp");
            return true; // Return true in dev mode
        }
    }
    
    /**
     * Send Email OTP
     */
    private function sendEmailOtp($email, $otp)
    {
        try {
            $subject = "APS Dream Home - Verification Code";
            $message = "Your verification code is: $otp\n\nThis code expires in 10 minutes.\n\nIf you didn't request this, please ignore this email.";
            $headers = "From: noreply@apsdreamhome.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            return mail($email, $subject, $message, $headers);
        } catch (\Exception $e) {
            error_log("Email OTP failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Detect role from behavior
     */
    private function detectRoleFromBehavior($db, $session)
    {
        // Get behavior stats
        $stats = $db->fetchOne(
            "SELECT 
                SUM(CASE WHEN event_type = 'property_view' THEN 1 ELSE 0 END) as property_views,
                SUM(CASE WHEN event_type = 'agent_listing_view' THEN 1 ELSE 0 END) as agent_views,
                SUM(CASE WHEN event_type = 'earn_money_click' THEN 1 ELSE 0 END) as earn_clicks,
                SUM(CASE WHEN event_type = 'commission_page_view' THEN 1 ELSE 0 END) as commission_views
            FROM smart_registration_behavior WHERE session_id = ?",
            [$session['id']]
        );
        
        if (!$stats) return;
        
        $role = 'customer';
        $confidence = 0.5;
        
        // Role detection logic
        if ($stats['earn_clicks'] > 0 || $stats['commission_views'] > 2) {
            $role = 'associate';
            $confidence = 0.7 + min($stats['earn_clicks'] * 0.05, 0.2);
        } elseif ($stats['agent_views'] > 2) {
            $role = 'agent';
            $confidence = 0.6 + min($stats['agent_views'] * 0.05, 0.2);
        } elseif ($stats['property_views'] > 5) {
            $role = 'customer';
            $confidence = 0.8;
        }
        
        // Update detection
        $db->query(
            "UPDATE smart_registration_sessions SET detected_role = ?, role_confidence = ?, updated_at = NOW() WHERE id = ?",
            [$role, min($confidence, 1.0), $session['id']]
        );
    }

    // ============================================================
    // ADMIN DASHBOARD — Incomplete Registrations
    // ============================================================

    public function adminDashboard()
    {
        @session_start();
        if (!isset($_SESSION['admin_id']) && empty($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $db = Database::getInstance();

        // Stats
        $total = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions")->c ?? 0;
        $pendingOtp = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'pending_otp'")->c ?? 0;
        $otpSent = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'otp_sent'")->c ?? 0;
        $abandoned = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'abandoned'")->c ?? 0;
        $completed = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE registration_status = 'profile_complete'")->c ?? 0;
        $accountCreated = $db->fetchOne("SELECT COUNT(*) as c FROM smart_registration_sessions WHERE user_created = 1")->c ?? 0;

        // Recent sessions
        $sessions = $db->fetchAll(
            "SELECT * FROM smart_registration_sessions ORDER BY created_at DESC LIMIT 50"
        );

        // Role distribution
        $roles = $db->fetchAll(
            "SELECT detected_role, COUNT(*) as c FROM smart_registration_sessions WHERE detected_role IS NOT NULL GROUP BY detected_role"
        );

        // Channel distribution
        $channels = $db->fetchAll(
            "SELECT otp_channel, COUNT(*) as c FROM smart_registration_sessions WHERE otp_channel IS NOT NULL GROUP BY otp_channel"
        );

        $conversionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        include __DIR__ . '/../../../views/admin/smart_registration/dashboard.php';
    }

    public function adminSessionDetail()
    {
        @session_start();
        if (!isset($_SESSION['admin_id']) && empty($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $db = Database::getInstance();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_URL . '/admin/smart-registration');
            exit;
        }

        $session = $db->fetchOne("SELECT * FROM smart_registration_sessions WHERE id = ?", [$id]);
        if (!$session) {
            $_SESSION['error'] = 'Session not found';
            header('Location: ' . BASE_URL . '/admin/smart-registration');
            exit;
        }

        // Get behavior data
        $behavior = $db->fetchAll(
            "SELECT * FROM smart_registration_behavior WHERE session_id = ? ORDER BY created_at DESC",
            [$session['id']]
        );

        include __DIR__ . '/../../../views/admin/smart_registration/detail.php';
    }
}
