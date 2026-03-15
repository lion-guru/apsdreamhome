<?php

/**
 * Unified Registration Controller with MLM Referral System
 * Modern implementation of legacy registration system
 */

namespace App\Http\Controllers;

use App\Core\Security;
use App\Core\Database;
use App\Services\ReferralService;
use App\Services\EmailService;

/**
 * Registration Controller
 * Handles user registration with MLM referral system
 */
class RegistrationController extends BaseController
{
    protected $mlmReferralService;
    protected $emailService;
    private $recaptchaSecret;

    public function __construct()
    {
        $this->mlmReferralService = new ReferralService();
        $this->emailService = new EmailService();
        $this->recaptchaSecret = getenv('RECAPTCHA_SECRET_KEY') ?: 'recaptcha_secret_placeholder';
    }

    /**
     * Show unified registration form
     */
    public function showRegistrationForm()
    {
        $referralCode = $_GET['ref'] ?? null;
        $referrerInfo = null;

        // Validate and get referrer information
        if ($referralCode) {
            $referrerInfo = $this->mlmReferralService->validateReferralCode($referralCode);
        }

        // Get form data for Indian states
        $indianStates = [
            'Andhra Pradesh',
            'Arunachal Pradesh',
            'Assam',
            'Bihar',
            'Chhattisgarh',
            'Goa',
            'Gujarat',
            'Haryana',
            'Himachal Pradesh',
            'Jharkhand',
            'Karnataka',
            'Kerala',
            'Madhya Pradesh',
            'Maharashtra',
            'Manipur',
            'Meghalaya',
            'Mizoram',
            'Nagaland',
            'Odisha',
            'Punjab',
            'Rajasthan',
            'Sikkim',
            'Tamil Nadu',
            'Telangana',
            'Tripura',
            'Uttar Pradesh',
            'Uttarakhand',
            'West Bengal',
            'Andaman and Nicobar Islands',
            'Chandigarh',
            'Dadra and Nagar Haveli and Daman and Diu',
            'Delhi',
            'Jammu and Kashmir',
            'Ladakh',
            'Lakshadweep',
            'Puducherry'
        ];

        return $this->render('registration/unified-form', compact('referralCode', 'referrerInfo', 'indianStates'));
    }

    /**
     * Process unified registration
     */
    public function register()
    {
        try {
            // Validate input
            $validation = $this->validateRegistration($_POST);
            if (!$validation['valid']) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ], 400);
            }

            // Verify reCAPTCHA
            if (!$this->verifyRecaptcha($_POST['g-recaptcha-response'] ?? '')) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'reCAPTCHA verification failed'
                ], 400);
            }

            $db = Database::getInstance();

            // Check if email already exists
            $existingUser = $db->fetchOne(
                "SELECT id FROM users WHERE email = ?",
                [$_POST['email']]
            );

            if ($existingUser) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email already registered'
                ], 400);
            }

            // Check if phone already exists
            $existingPhone = $db->fetchOne(
                "SELECT id FROM users WHERE phone = ?",
                [$_POST['phone']]
            );

            if ($existingPhone) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Phone number already registered'
                ], 400);
            }

            // Start transaction
            $db->beginTransaction();

            try {
                // Create user account
                $userId = $db->execute(
                    "INSERT INTO users (name, email, phone, password, address, city, state, pincode, country, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        password_hash($_POST['password'], PASSWORD_ARGON2ID),
                        $_POST['address'] ?? '',
                        $_POST['city'] ?? '',
                        $_POST['state'] ?? '',
                        $_POST['pincode'] ?? '',
                        $_POST['country'] ?? 'India'
                    ]
                );

                // Create MLM profile
                $mlmProfileId = $db->execute(
                    "INSERT INTO mlm_profiles (user_id, sponsor_id, placement_id, position, level, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $userId,
                        $_POST['sponsor_id'] ?? null,
                        $_POST['placement_id'] ?? null,
                        $_POST['position'] ?? 'left',
                        1
                    ]
                );

                // Process referral if provided
                $referralCode = $_POST['referral_code'] ?? null;
                if ($referralCode) {
                    $this->processReferral($userId, $referralCode);
                }

                // Send welcome email
                $this->emailService->sendWelcomeEmail($_POST['email'], $_POST['name']);

                // Commit transaction
                $db->commit();

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Registration successful',
                    'user_id' => $userId,
                    'mlm_profile_id' => $mlmProfileId
                ]);
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate registration data
     */
    private function validateRegistration(array $data): array
    {
        $errors = [];

        // Name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 3) {
            $errors['name'] = 'Name must be at least 3 characters';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Phone validation
        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match('/^[6-9]\d{9}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid Indian phone number format';
        }

        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif ($data['password'] !== ($data['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        // Address validation
        if (empty($data['address'])) {
            $errors['address'] = 'Address is required';
        }

        // City validation
        if (empty($data['city'])) {
            $errors['city'] = 'City is required';
        }

        // State validation
        if (empty($data['state'])) {
            $errors['state'] = 'State is required';
        }

        // Pincode validation
        if (empty($data['pincode'])) {
            $errors['pincode'] = 'Pincode is required';
        } elseif (!preg_match('/^\d{6}$/', $data['pincode'])) {
            $errors['pincode'] = 'Invalid Indian pincode format';
        }

        // Terms validation
        if (!isset($data['terms']) || !$data['terms']) {
            $errors['terms'] = 'You must agree to the terms and conditions';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verify reCAPTCHA response
     */
    private function verifyRecaptcha(string $response): bool
    {
        if (empty($response)) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $this->recaptchaSecret,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $resultJson = json_decode($result, true);

        return $resultJson['success'] ?? false;
    }

    /**
     * Process referral
     */
    private function processReferral(int $userId, string $referralCode): void
    {
        $db = Database::getInstance();

        // Get referrer information
        $referrer = $db->fetchOne(
            "SELECT u.id, m.id as mlm_profile_id 
             FROM users u 
             JOIN mlm_profiles m ON u.id = m.user_id 
             WHERE u.referral_code = ?",
            [$referralCode]
        );

        if ($referrer) {
            // Create referral record
            $db->execute(
                "INSERT INTO mlm_referrals (referrer_id, referred_id, referral_code, status, created_at) 
                 VALUES (?, ?, ?, 'active', NOW())",
                [$referrer['id'], $userId, $referralCode]
            );

            // Update referrer's MLM profile
            $db->execute(
                "UPDATE mlm_profiles SET total_referrals = total_referrals + 1 WHERE id = ?",
                [$referrer['mlm_profile_id']]
            );

            // Add to network tree
            $this->addToNetworkTree($referrer['mlm_profile_id'], $userId);
        }
    }

    /**
     * Add user to MLM network tree
     */
    private function addToNetworkTree(int $sponsorId, int $userId): void
    {
        $db = Database::getInstance();

        // Get user's MLM profile
        $userProfile = $db->fetchOne(
            "SELECT id FROM mlm_profiles WHERE user_id = ?",
            [$userId]
        );

        if ($userProfile) {
            // Find appropriate position in sponsor's downline
            $position = $this->findAvailablePosition($sponsorId);

            // Add to network tree
            $db->execute(
                "INSERT INTO mlm_network_tree (sponsor_id, member_id, position, level, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$sponsorId, $userProfile['id'], $position, 1]
            );
        }
    }

    /**
     * Find available position in sponsor's downline
     */
    private function findAvailablePosition(int $sponsorId): string
    {
        $db = Database::getInstance();

        // Check left position first
        $leftPosition = $db->fetchOne(
            "SELECT id FROM mlm_network_tree WHERE sponsor_id = ? AND position = 'left'",
            [$sponsorId]
        );

        if (!$leftPosition) {
            return 'left';
        }

        // Check right position
        $rightPosition = $db->fetchOne(
            "SELECT id FROM mlm_network_tree WHERE sponsor_id = ? AND position = 'right'",
            [$sponsorId]
        );

        if (!$rightPosition) {
            return 'right';
        }

        // Both positions filled, find first available in downline
        return $this->findDeepAvailablePosition($sponsorId);
    }

    /**
     * Find deep available position in network tree
     */
    private function findDeepAvailablePosition(int $sponsorId): string
    {
        $db = Database::getInstance();

        // Get sponsor's left leg
        $leftLeg = $db->fetchAll(
            "SELECT member_id, position FROM mlm_network_tree 
             WHERE sponsor_id = ? AND position = 'left' 
             ORDER BY created_at ASC",
            [$sponsorId]
        );

        foreach ($leftLeg as $member) {
            $hasSpace = $this->checkMemberHasSpace($member['member_id']);
            if ($hasSpace['has_space']) {
                return $hasSpace['position'];
            }
        }

        // Get sponsor's right leg
        $rightLeg = $db->fetchAll(
            "SELECT member_id, position FROM mlm_network_tree 
             WHERE sponsor_id = ? AND position = 'right' 
             ORDER BY created_at ASC",
            [$sponsorId]
        );

        foreach ($rightLeg as $member) {
            $hasSpace = $this->checkMemberHasSpace($member['member_id']);
            if ($hasSpace['has_space']) {
                return $hasSpace['position'];
            }
        }

        return 'left'; // Default
    }

    /**
     * Check if member has space in their downline
     */
    private function checkMemberHasSpace(int $memberId): array
    {
        $db = Database::getInstance();

        $leftCount = $db->fetchOne(
            "SELECT COUNT(*) as count FROM mlm_network_tree WHERE sponsor_id = ? AND position = 'left'",
            [$memberId]
        );

        $rightCount = $db->fetchOne(
            "SELECT COUNT(*) as count FROM mlm_network_tree WHERE sponsor_id = ? AND position = 'right'",
            [$memberId]
        );

        if ($leftCount['count'] == 0) {
            return ['has_space' => true, 'position' => 'left'];
        }

        if ($rightCount['count'] == 0) {
            return ['has_space' => true, 'position' => 'right'];
        }

        return ['has_space' => false, 'position' => null];
    }

    /**
     * Check referral code validity
     */
    public function validateReferralCode()
    {
        $referralCode = $_POST['referral_code'] ?? '';

        if (empty($referralCode)) {
            return $this->jsonResponse([
                'valid' => false,
                'message' => 'Referral code is required'
            ]);
        }

        $db = Database::getInstance();
        $referrer = $db->fetchOne(
            "SELECT u.name, u.email, m.level, m.total_referrals 
             FROM users u 
             JOIN mlm_profiles m ON u.id = m.user_id 
             WHERE u.referral_code = ? AND u.status = 'active'",
            [$referralCode]
        );

        if ($referrer) {
            return $this->jsonResponse([
                'valid' => true,
                'referrer' => $referrer,
                'message' => 'Valid referral code'
            ]);
        }

        return $this->jsonResponse([
            'valid' => false,
            'message' => 'Invalid or expired referral code'
        ]);
    }

    /**
     * Get registration statistics
     */
    public function getRegistrationStats()
    {
        try {
            $db = Database::getInstance();

            // Total registrations
            $totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users");

            // Today's registrations
            $todayUsers = $db->fetchOne(
                "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()"
            );

            // This month's registrations
            $monthUsers = $db->fetchOne(
                "SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
            );

            // MLM statistics
            $totalProfiles = $db->fetchOne("SELECT COUNT(*) as count FROM mlm_profiles");

            $activeReferrals = $db->fetchOne(
                "SELECT COUNT(*) as count FROM mlm_referrals WHERE status = 'active'"
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers['count'],
                    'today_registrations' => $todayUsers['count'],
                    'month_registrations' => $monthUsers['count'],
                    'total_mlm_profiles' => $totalProfiles['count'],
                    'active_referrals' => $activeReferrals['count']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get registration stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export registrations data
     */
    public function exportRegistrations()
    {
        try {
            $db = Database::getInstance();

            $registrations = $db->fetchAll(
                "SELECT u.id, u.name, u.email, u.phone, u.city, u.state, u.created_at,
                        m.level, m.total_referrals
                 FROM users u 
                 LEFT JOIN mlm_profiles m ON u.id = m.user_id 
                 ORDER BY u.created_at DESC"
            );

            // Convert to CSV
            $csv = "ID,Name,Email,Phone,City,State,Registration Date,MLM Level,Total Referrals\n";

            foreach ($registrations as $reg) {
                $csv .= "{$reg['id']},\"{$reg['name']}\",\"{$reg['email']}\",\"{$reg['phone']}\",\"{$reg['city']}\",\"{$reg['state']}\",\"{$reg['created_at']}\",{$reg['level']},{$reg['total_referrals']}\n";
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="registrations.csv"');
            echo $csv;
            exit;
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
