<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\UserRegistrationService;

class QuickAuthController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function skipCsrfProtection(): bool
    {
        return true;
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

    /**
     * Normalize Indian mobile: strips spaces/+91/leading 0 -> 10 digits.
     * Returns '' when the number is not a valid Indian mobile.
     */
    private function normalizePhone(string $raw): string
    {
        $d = preg_replace('/\D/', '', $raw ?? '');
        if (strlen($d) === 12 && substr($d, 0, 2) === '91') $d = substr($d, 2);
        if (strlen($d) === 11 && $d[0] === '0') $d = substr($d, 1);
        if (!preg_match('/^[6-9]\d{9}$/', $d)) return '';
        return $d;
    }

    private function quickJson(array $payload): void
    {
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    /**
     * Quick registration for casual visitors (passwordless UX).
     * customer -> instant active account (legacy behaviour, preserved).
     * associate/agent -> canonical UserRegistrationService (wallets, MLM, associates row).
     * employee/telecaller -> HR onboarding only, redirected to careers.
     */
    public function quickRegister()
    {
        @session_start();

        // Rate limiting: 5 quick-registrations per minute per IP (fake-reg flood protection)
        require_once __DIR__ . '/../../../Middleware/RateLimiter.php';
        \App\Middleware\RateLimiter::check('quick_register_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 60);

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = $this->normalizePhone($_POST['phone'] ?? '');
        $referralCode = trim($_POST['referral_code'] ?? '');
        $role = strtolower(trim($_POST['role'] ?? 'customer'));
        if (!in_array($role, ['customer', 'associate', 'agent', 'employee', 'telecaller'], true)) $role = 'customer';

        try {
            // Validate inputs
            if (empty($name) || empty($email) || empty($phone)) {
                $this->quickJson(['success' => false, 'message' => 'Please fill name, valid email and 10-digit mobile number']);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->quickJson(['success' => false, 'message' => 'Please enter a valid email address']);
            }

            // CAPTCHA validation (quick modal is anonymous — must match main register)
            $captcha_code = trim($_POST['captcha_code'] ?? '');
            require_once __DIR__ . '/../../../Helpers/SimpleCaptcha.php';
            if (empty($captcha_code) || !\SimpleCaptcha::validate($captcha_code)) {
                $this->quickJson(['success' => false, 'message' => 'Invalid or expired security code. Please try again.']);
            }

            if ($role === 'employee' || $role === 'telecaller') {
                $this->quickJson(['success' => false, 'message' => 'Employee/Telecaller onboarding is done by HR. Please apply via the Careers page.']);
            }

            if ($role === 'associate' || $role === 'agent') {
                $this->quickRegisterMlm($role, $name, $email, $phone, $referralCode);
            }

            $this->quickRegisterCustomer($name, $email, $phone, $referralCode);

        } catch (\Exception $e) {
            error_log('QuickAuthController::quickRegister error: ' . $e->getMessage());
            $this->quickJson(['success' => false, 'message' => 'Request failed. Please try again.']);
        }
    }

    /**
     * Legacy customer path (active + approved instantly). Behaviour preserved.
     */
    private function quickRegisterCustomer(string $name, string $email, string $phone, string $referralCode): void
    {
        try {
            [$tSql, $tParams] = $this->getTenantSql();
            $tInsert = $this->getTenantInsert();

            [$tSql, $tParams] = $this->getTenantSql();
            $tInsert = $this->getTenantInsert();
            
            // Check if user already exists
            $existingUser = $this->db->fetchOne("SELECT id FROM users WHERE (email = ? OR phone = ?)" . $tSql . " LIMIT 1", array_merge([$email, $phone], $tParams));
            if ($existingUser) {
                $this->quickJson(['success' => false, 'message' => 'An account already exists with this email or phone. Please login to continue.', 'login_url' => (defined('BASE_URL') ? BASE_URL : '') . '/login']);
            }

            // Find referrer if referral code provided
            $referrerId = null;
            if (!empty($referralCode)) {
                $ref = $this->db->fetchOne("SELECT id FROM users WHERE referral_code = ?" . $tSql . " LIMIT 1", array_merge([$referralCode], $tParams));
                if ($ref) $referrerId = $ref['id'];
            }

            // Generate customer_id and referral code
            $customerId = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newReferralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            // Insert user (default to customer role)
            $userData = array_merge([
                'customer_id' => $customerId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $newReferralCode,
                'referred_by' => $referrerId,
                'role' => 'customer',
                'status' => 'active',
                'registration_status' => 'approved',
                'approved_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $tInsert);
            
            $this->db->insert('users', $userData);

            $newUserRow = $this->db->fetchOne("SELECT id FROM users WHERE email = ?" . $tSql . " LIMIT 1", array_merge([$email], $tParams));
            if (empty($newUserRow) || empty($newUserRow['id'])) {
                throw new \Exception('Account record not found after insert.');
            }
            $newUserId = (int)$newUserRow['id'];

            // Create wallet entry
            $this->db->insert('wallet_points', array_merge([
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

            // Set session
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['role'] = 'customer';
            $_SESSION['logged_in'] = true;
            $_SESSION['success'] = 'Account created successfully! Welcome to APS Dream Home.';

            $this->quickJson(['success' => true, 'redirect' => (defined('BASE_URL') ? BASE_URL : '') . '/user/dashboard', 'message' => 'Account created successfully! Welcome to APS Dream Home.']);

        } catch (\Exception $e) {
            error_log('QuickAuthController::quickRegisterCustomer error: ' . $e->getMessage());
            $this->quickJson(['success' => false, 'message' => 'Request failed. Please try again.']);
        }
    }

    /**
     * Associate/Agent path via the canonical registration service so wallets,
     * MLM profiles, network tree and associates rows are all created correctly.
     * Passwordless UX is preserved: a random password is generated server-side
     * (user can reset it later via forgot-password / OTP).
     */
    private function quickRegisterMlm(string $role, string $name, string $email, string $phone, string $referralCode): void
    {
        $service = new UserRegistrationService();
        $result = $service->createUser($role, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => bin2hex(random_bytes(12)),
            'referral_code' => $referralCode,
            'registration_method' => 'quick_modal',
        ]);

        if (empty($result['success'])) {
            $payload = ['success' => false, 'message' => $result['message'] ?? 'Registration failed'];
            if (!empty($result['existing_user_id'])) {
                $payload['message'] .= ' Please login to continue.';
                $payload['login_url'] = (defined('BASE_URL') ? BASE_URL : '') . '/login';
            }
            $this->quickJson($payload);
        }

        $newUserId = (int)($result['user_id'] ?? 0);

        // Resolve associates row id for portal sessions
        $assocId = 0;
        try {
            $row = $this->db->fetchOne("SELECT id FROM associates WHERE user_id = ? LIMIT 1", [$newUserId]);
            if ($row) $assocId = (int)$row['id'];
        } catch (\Throwable $e) { error_log('QuickAuthController: associates lookup failed: ' . $e->getMessage()); }

        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        if ($assocId) {
            $_SESSION['associate_id'] = $assocId;
            if ($role === 'agent') $_SESSION['agent_id'] = $assocId;
        }
        $_SESSION['success'] = $result['message'] ?? 'Account created successfully!';

        $dashboard = $role === 'agent' ? '/agent/dashboard' : '/associate/dashboard';
        $this->quickJson([
            'success' => true,
            'redirect' => (defined('BASE_URL') ? BASE_URL : '') . $dashboard,
            'message' => $result['message'] ?? 'Account created successfully!',
        ]);
    }

    /**
     * Request referral code by email or phone
     */
    public function requestReferralCode()
    {
        @session_start();

        // Throttle: enumeration protection — 10 lookups per minute per IP
        require_once __DIR__ . '/../../../Middleware/RateLimiter.php';
        \App\Middleware\RateLimiter::check('referral_lookup_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 60);

        $identifier = $_POST['email'] ?? $_POST['phone'] ?? '';

        try {
            if (empty($identifier)) {
                echo json_encode(['success' => false, 'message' => 'Email or phone is required']);
                exit;
            }

            [$tSql, $tParams] = $this->getTenantSql();

            $user = $this->db->fetchOne(
                "SELECT id, name, email, phone, referral_code FROM users WHERE (email = ? OR phone = ?)" . $tSql . " LIMIT 1",
                array_merge([$identifier, $identifier], $tParams)
            );

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'No account found with this email or phone']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'referral_code' => $user['referral_code'],
                'name' => $user['name']
            ]);
            exit;

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Request failed: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Auto-generate user during booking/lead conversion.
     * Requires a logged-in session (anti fake-reg: blocks anonymous
     * Rs.100 minting to arbitrary associate ids; no in-app caller exists
     * that runs logged-out, verified by grep).
     */
    public function autoGenerateUser()
    {
        @session_start();

        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }

        // Throttle: minting endpoint — 10 calls per minute per user
        require_once __DIR__ . '/../../../Middleware/RateLimiter.php';
        \App\Middleware\RateLimiter::check('auto_gen_user_' . (int)$_SESSION['user_id'], 10, 60);

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $associateId = $_POST['associate_id'] ?? 0;

        try {
            if (empty($name) || empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'Name and phone are required']);
                exit;
            }

            [$tSql, $tParams] = $this->getTenantSql();
            $tInsert = $this->getTenantInsert();

            // Check if user already exists by phone
            $existingUser = $this->db->fetchOne("SELECT * FROM users WHERE phone = ?" . $tSql . " LIMIT 1", array_merge([$phone], $tParams));

            if ($existingUser) {
                // Update lead with existing user
                echo json_encode([
                    'success' => true,
                    'user_id' => $existingUser['id'],
                    'customer_id' => $existingUser['customer_id'],
                    'message' => 'User already exists'
                ]);
                exit;
            }

            // Get associate referral code
            $associate = $this->db->fetchOne("SELECT referral_code FROM users WHERE id = ?" . $tSql . " LIMIT 1", array_merge([$associateId], $tParams));
            $referralCode = $associate ? $associate['referral_code'] : '';

            // Generate customer_id
            $customerId = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newReferralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            // Insert user
            $this->db->insert('users', array_merge([
                'customer_id' => $customerId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'referral_code' => $newReferralCode,
                'referred_by' => $associateId,
                'role' => 'customer',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $tInsert));

            $newUserId = $this->db->fetchOne("SELECT id FROM users WHERE phone = ?" . $tSql . " LIMIT 1", array_merge([$phone], $tParams))['id'];

            // Create wallet entry
            $this->db->insert('wallet_points', array_merge([
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

            // Credit referral to associate
            if ($associateId && $referralCode) {
                $associateWallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ?" . $tSql . " LIMIT 1", array_merge([$associateId], $tParams));

                if ($associateWallet) {
                    $rewardPoints = 100;
                    $newBalance = $associateWallet['points_balance'] + $rewardPoints;
                    $newTotalEarned = $associateWallet['total_earned'] + $rewardPoints;
                    $newReferralEarnings = $associateWallet['referral_earnings'] + $rewardPoints;

                    $this->db->query("UPDATE wallet_points SET points_balance = ?, total_earned = ?, referral_earnings = ?, updated_at = ? WHERE user_id = ?" . $tSql,
                        array_merge([$newBalance, $newTotalEarned, $newReferralEarnings, date('Y-m-d H:i:s'), $associateId], $tParams));

                    $this->db->insert('wallet_transactions', array_merge([
                        'user_id' => $associateId,
                        'transaction_type' => 'credit',
                        'transaction_category' => 'referral',
                        'amount' => $rewardPoints,
                        'balance_before' => $associateWallet['points_balance'],
                        'balance_after' => $newBalance,
                        'description' => "Booking referral reward for Customer: $name",
                        'reference_id' => $newUserId,
                        'reference_type' => 'user',
                        'related_user_id' => $newUserId,
                        'status' => 'completed',
                        'created_at' => date('Y-m-d H:i:s')
                    ], $tInsert));

                    $this->db->insert('referral_rewards', array_merge([
                        'referrer_id' => $associateId,
                        'referred_id' => $newUserId,
                        'reward_amount' => $rewardPoints,
                        'reward_type' => 'points',
                        'reward_percentage' => 0.00,
                        'referral_code' => $referralCode,
                        'status' => 'credited',
                        'credited_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ], $tInsert));
                }
            }

            echo json_encode([
                'success' => true,
                'user_id' => $newUserId,
                'customer_id' => $customerId,
                'message' => 'User created successfully'
            ]);
            exit;

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Auto-generation failed: ' . $e->getMessage()]);
            exit;
        }
    }
}
