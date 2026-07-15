<?php
namespace App\Services;

use App\Core\Database\Database;

class UserRegistrationService
{
    private Database $db;
    private WalletService $walletService;
    private ReferralService $referralService;

    private const ROLE_PREFIXES = [
        'customer' => 'CUS',
        'associate' => 'ASC',
        'agent' => 'AGT',
        'employee' => 'EMP',
        'admin' => 'ADM',
        'super_admin' => 'SUP',
        'manager' => 'MGR',
        'telecaller' => 'TEL',
    ];

    private const MLM_ROLES = ['associate', 'agent'];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
        $this->referralService = new ReferralService();
    }

    /**
     * Create a user with all associated records in a single transaction.
     *
     * @param string $role customer|associate|agent|employee|admin|manager|telecaller
     * @param array $data {
     *     @type string  $name            Required
     *     @type string  $email           Required
     *     @type string  $phone           Required
     *     @type string  $password        Required
     *     @type string  $referral_code   Optional — referrer's code
     *     @type int     $sponsor_id      Optional — alternative to referral_code
     *     @type string  $mlm_position    Optional — left|right (default: auto)
     *     @type string  $city            Optional
     *     @type string  $occupation      Optional
     *     @type string  $registration_method Optional — smart_otp|web|social
     * }
     * @param array &$user Populated with created user on success
     * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
     */
    public function createUser(string $role, array $data, ?array &$user = null): array
    {
        $role = strtolower(trim($role));
        if (!isset(self::ROLE_PREFIXES[$role])) {
            return ['success' => false, 'message' => "Invalid role: {$role}"];
        }

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $referralCode = trim($data['referral_code'] ?? $data['ref'] ?? '');
        $sponsorId = isset($data['sponsor_id']) ? (int)$data['sponsor_id'] : null;
        $mlmPosition = trim($data['mlm_position'] ?? '');
        $city = trim($data['city'] ?? '');
        $occupation = trim($data['occupation'] ?? '');
        $regMethod = trim($data['registration_method'] ?? 'web');

        if (empty($name)) return ['success' => false, 'message' => 'Name is required'];
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (empty($email)) return ['success' => false, 'message' => 'Email is required'];
        }
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
            return ['success' => false, 'message' => 'Valid 10-digit phone is required'];
        }
        if (strlen($password) < 6) return ['success' => false, 'message' => 'Password must be at least 6 characters'];

        try {
            $exists = $this->db->fetchOne("SELECT id, role FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($exists) {
                return ['success' => false, 'message' => 'Email already registered', 'existing_user_id' => (int)$exists['id']];
            }
            $existsPhone = $this->db->fetchOne("SELECT id FROM users WHERE phone = ? LIMIT 1", [$phone]);
            if ($existsPhone) {
                return ['success' => false, 'message' => 'Phone number already registered', 'existing_user_id' => (int)$existsPhone['id']];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Registration check failed'];
        }

        $prefix = self::ROLE_PREFIXES[$role];
        $displayId = $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $refCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)) . date('ymd') . rand(100, 999);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $isMlmRole = in_array($role, self::MLM_ROLES, true);

        // Resolve referrer/sponsor
        $resolvedSponsorId = null;
        if ($sponsorId) {
            $resolvedSponsorId = $sponsorId;
        } elseif (!empty($referralCode)) {
            $referrer = $this->referralService->validateUserReferralCode($referralCode);
            if ($referrer) {
                $resolvedSponsorId = (int)$referrer['id'];
            }
        }

        // Auto-approve if valid sponsor, else pending
        $hasValidSponsor = !empty($resolvedSponsorId);
        $regStatus = $hasValidSponsor ? 'approved' : 'pending';
        $userStatus = $hasValidSponsor ? 'active' : ($isMlmRole ? 'inactive' : 'active');

        $this->db->beginTransaction();
        try {
            // Generate unique referral code
            $finalRefCode = $refCode;
            $counter = 0;
            while ($this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE referral_code = ?", [$finalRefCode]) > 0) {
                $counter++;
                $finalRefCode = $refCode . $counter;
            }

            $userId = $this->db->insert('users', [
                'customer_id' => $displayId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashedPassword,
                'referral_code' => $finalRefCode,
                'referred_by' => $resolvedSponsorId,
                'role' => $role,
                'city' => $city ?: null,
                'occupation' => $occupation ?: null,
                'status' => $userStatus,
                'registration_status' => $regStatus,
                'registration_method' => $regMethod,
                'approved_at' => $hasValidSponsor ? date('Y-m-d H:i:s') : null,
                'mlm_rank' => 'associate',
                'commission_rate' => 5.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Create wallet
            $this->walletService->ensureWallet($userId);

            // MLM role setup
            if ($isMlmRole) {
                $this->createMlmProfile($userId, $finalRefCode, $resolvedSponsorId, $role);
                $this->createNetworkTreeEntry($userId, $resolvedSponsorId, $mlmPosition);
                $this->createAssociatesRecord($userId, $name, $email, $phone, $finalRefCode, $resolvedSponsorId, $role);

                // Update referrer's direct_referrals count
                if ($resolvedSponsorId) {
                    $this->db->query(
                        "UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1, total_team_size = total_team_size + 1, updated_at = NOW() WHERE user_id = ?",
                        [$resolvedSponsorId]
                    );
                }

                // Referral reward for sponsor (wallet credit, NOT referral_rewards table)
                if ($resolvedSponsorId) {
                    $rewardAmount = 200.00;
                    $this->walletService->credit(
                        $resolvedSponsorId,
                        $rewardAmount,
                        'referral',
                        "Referral reward for {$role}: {$name}",
                        $userId
                    );
                }
            }

            // Process tiered signup bonus via ReferralService
            if ($resolvedSponsorId) {
                try {
                    $this->referralService->processTieredSignupBonus($resolvedSponsorId, $userId);
                } catch (\Throwable $e) {
                    error_log("UserRegistrationService: tiered signup bonus failed: " . $e->getMessage());
                }

                // Apply referral link in users.referred_by
                try {
                    $this->referralService->applyReferral($userId, $referralCode);
                } catch (\Throwable $e) {
                    error_log("UserRegistrationService: applyReferral failed: " . $e->getMessage());
                }
            }

            // ReferralService trackReferral
            if ($resolvedSponsorId) {
                try {
                    $this->referralService->trackReferral($resolvedSponsorId, $userId, $role, 'direct_link');
                } catch (\Throwable $e) {
                    error_log("UserRegistrationService: trackReferral failed: " . $e->getMessage());
                }
            }

            $this->db->commit();

            $user = [
                'id' => $userId,
                'customer_id' => $displayId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'referral_code' => $finalRefCode,
                'status' => $userStatus,
                'registration_status' => $regStatus,
            ];

            return [
                'success' => true,
                'message' => $hasValidSponsor
                    ? "Registration successful! Your ID: {$displayId}"
                    : "Registration successful! Your ID: {$displayId}. Account pending approval.",
                'user_id' => $userId,
                'user' => $user,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("UserRegistrationService::createUser error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    private function createMlmProfile(int $userId, string $referralCode, ?int $sponsorId, string $role): void
    {
        $this->db->insert('mlm_profiles', [
            'user_id' => $userId,
            'referral_code' => $referralCode,
            'sponsor_user_id' => $sponsorId,
            'sponsor_code' => null,
            'user_type' => $role,
            'current_level' => 'associate',
            'total_team_size' => 0,
            'direct_referrals' => 0,
            'total_commission' => 0.00,
            'pending_commission' => 0.00,
            'lifetime_sales' => 0.00,
            'verification_status' => 'pending',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function createNetworkTreeEntry(int $userId, ?int $sponsorId, string $preferredPosition = ''): void
    {
        $rootId = $userId;
        $parentId = null;
        $level = 1;
        $position = 'left';

        if ($sponsorId) {
            // Check if sponsor has a network tree entry already
            $sponsorTree = $this->db->fetchOne(
                "SELECT id, root_id, level FROM network_tree WHERE associate_id = ? LIMIT 1",
                [$sponsorId]
            );
            if ($sponsorTree) {
                $rootId = (int)$sponsorTree['root_id'];
                $parentId = $sponsorId;
                $level = (int)$sponsorTree['level'] + 1;

                if (in_array($preferredPosition, ['left', 'right'], true)) {
                    $position = $preferredPosition;
                } else {
                    $leftCount = (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'left'",
                        [$sponsorId]
                    );
                    $rightCount = (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'right'",
                        [$sponsorId]
                    );
                    $position = $leftCount <= $rightCount ? 'left' : 'right';
                }
            }
        }

        $this->db->insert('network_tree', [
            'associate_id' => $userId,
            'root_id' => $rootId,
            'parent_id' => $parentId,
            'level' => $level,
            'position' => $position,
            'total_left_count' => 0,
            'total_right_count' => 0,
            'total_left_bv' => 0.00,
            'total_right_bv' => 0.00,
            'personal_bv' => 0.00,
            'is_active' => 1,
            'joined_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Also insert into mlm_network_tree (used by commission engines)
        $mlmParentId = $parentId ?? 1;
        $this->db->insert('mlm_network_tree', [
            'associate_id' => $userId,
            'sponsor_id' => $sponsorId,
            'parent_id' => $mlmParentId,
            'level' => $level,
        ]);
    }

    private function createAssociatesRecord(int $userId, string $name, string $email, string $phone, string $referralCode, ?int $sponsorId, string $role): void
    {
        $this->db->insert('associates', [
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'referral_code' => $referralCode,
            'sponsor_id' => $sponsorId,
            'level' => $role === 'agent' ? 'agent' : 'associate',
            'status' => 'active',
            'joining_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update user profile fields (name, phone, address, city, occupation).
     * Does NOT handle password changes (separate flow).
     *
     * @param int $userId
     * @param array $data Fields to update: name, phone, address, city, occupation
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateProfile(int $userId, array $data): array
    {
        $allowedFields = ['name', 'phone', 'address', 'city', 'occupation'];
        $updates = [];
        $params = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $val = trim((string)$data[$field]);
                if ($field === 'phone' && !empty($val) && !preg_match('/^[0-9]{10}$/', $val)) {
                    return ['success' => false, 'message' => 'Valid 10-digit phone is required'];
                }
                $updates[] = "`{$field}` = ?";
                $params[] = $val ?: null;
            }
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $params[] = $userId;

        try {
            $this->db->query(
                "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?",
                $params
            );
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } catch (\Exception $e) {
            error_log("UserRegistrationService::updateProfile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    }

    /**
     * Change user password with current password verification.
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array ['success' => bool, 'message' => string]
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'New password must be at least 6 characters'];
        }

        try {
            $row = $this->db->fetchOne("SELECT password FROM users WHERE id = ?", [$userId]);
            if (!$row) {
                return ['success' => false, 'message' => 'User not found'];
            }
            if (!password_verify($currentPassword, $row['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->query("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?", [$hashed, $userId]);
            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (\Exception $e) {
            error_log("UserRegistrationService::changePassword error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }

    /**
     * Get display ID prefix for a role
     */
    public static function getPrefixForRole(string $role): string
    {
        return self::ROLE_PREFIXES[strtolower($role)] ?? 'USR';
    }
}
