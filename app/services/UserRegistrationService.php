<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use PDO;

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
        'ceo' => 'CEO', 'cfo' => 'CFO', 'cto' => 'CTO', 'coo' => 'COO',
        'cmo' => 'CMO', 'chro' => 'CHR', 'sales_director' => 'SDI',
        'marketing_director' => 'MDI', 'construction_director' => 'CDI',
        'finance_director' => 'FDI', 'hr_director' => 'HDI', 'operations_director' => 'ODI',
        'legal_head' => 'LGH', 'finance_head' => 'FNH', 'hr_head' => 'HRH',
        'operations_head' => 'OPH', 'department_manager' => 'DPM',
        'project_manager' => 'PJM', 'sales_manager' => 'SLM', 'hr_manager' => 'HRM',
        'marketing_manager' => 'MKM', 'finance_manager' => 'FNM',
        'property_manager' => 'PRM', 'it_manager' => 'ITM', 'operations_manager' => 'OPM',
        'legal_advisor' => 'LGA', 'chartered_accountant' => 'CAA',
        'senior_developer' => 'SDE', 'accountant' => 'ACT',
        'developer' => 'DEV', 'content_writer' => 'CRW', 'graphic_designer' => 'GRD',
        'data_entry_operator' => 'DEO', 'backoffice_staff' => 'BKS',
        'telecalling_executive' => 'TCE', 'support_executive' => 'SUE',
        'senior_associate' => 'SAS', 'associate_team_lead' => 'ATL',
        'senior_agent' => 'SAG', 'franchise_owner' => 'FRO',
        'premium_customer' => 'PRC', 'verified_customer' => 'VFC', 'guest_customer' => 'GSC',
        'farmer' => 'FAR',
    ];

    private const MLM_ROLES = ['associate', 'agent'];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
        $this->referralService = new ReferralService();
    }

    /**
     * Get current tenant ID for multi-tenant scoping.
     */
    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
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

        // Tenant enforcement: check user limit before creating
        if (class_exists('\App\Core\Middleware\TenantContext') && class_exists('\App\Services\TenantEnforcement')) {
            try {
                $tenantId = \App\Core\Middleware\TenantContext::getId();
                if ($tenantId > 1) {
                    $enforcement = \App\Services\TenantEnforcement::getInstance();
                    $check = $enforcement->canPerform($tenantId, 'add_user');
                    if (!$check['allowed']) {
                        return ['success' => false, 'message' => $check['reason']];
                    }
                }
            } catch (\Throwable $e) {
                error_log('UserRegistrationService: tenant enforcement check failed: ' . $e->getMessage());
            }
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
        $agentType = trim($data['agent_type'] ?? '');
        // Agent experience (form sends fresher|1-2|3-5|5+; column is INT years, nullable)
        $agentExpYears = null;
        if ($role === 'agent') {
            $expMap = ['fresher' => 0, '1-2' => 1, '3-5' => 3, '5+' => 5];
            $expKey = trim($data['experience'] ?? '');
            if (array_key_exists($expKey, $expMap)) $agentExpYears = $expMap[$expKey];
        }

        if (empty($name)) {
            return ['success' => false, 'message' => 'Name is required'];
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Valid email is required'];
        }
        if (empty($phone) || !preg_match('/^\d{10}$/', $phone)) {
            return ['success' => false, 'message' => 'Valid 10-digit phone is required'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        }

        try {
            $tid = $this->getTenantId();
            $exists = $this->db->fetchOne("SELECT id, role FROM users WHERE email = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1", $tid > 1 ? [$email, $tid] : [$email]);
            if ($exists) {
                return ['success' => false, 'message' => 'Email already registered', 'existing_user_id' => (int)$exists['id']];
            }
            $existsPhone = $this->db->fetchOne("SELECT id FROM users WHERE phone = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1", $tid > 1 ? [$phone, $tid] : [$phone]);
            if ($existsPhone) {
                return ['success' => false, 'message' => 'Phone number already registered', 'existing_user_id' => (int)$existsPhone['id']];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Registration check failed'];
        }

        $prefix = self::ROLE_PREFIXES[$role];
        $displayId = $prefix . date('Y') . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $refCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)) . date('ymd') . random_int(100, 999);

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
            $tid = $this->getTenantId();
            while ($this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE referral_code = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$finalRefCode, $tid] : [$finalRefCode]) > 0) {
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
                'agent_experience_years' => $agentExpYears,
                'status' => $userStatus,
                'registration_status' => $regStatus,
                'registration_method' => $regMethod,
                'approved_at' => $hasValidSponsor ? date('Y-m-d H:i:s') : null,
                'mlm_rank' => 'associate',
                'commission_rate' => 5.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'tenant_id' => $tid,
            ]);

            // Create wallet
            $this->walletService->ensureWallet($userId);

            // MLM role setup
            if ($isMlmRole) {
                $this->createMlmProfile($userId, $finalRefCode, $resolvedSponsorId, $role);
                $this->createNetworkTreeEntry($userId, $resolvedSponsorId, $mlmPosition);
                $this->createAssociatesRecord($userId, $name, $email, $phone, $finalRefCode, $resolvedSponsorId, $role, $agentType);

                // Update referrer's direct_referrals count
                if ($resolvedSponsorId) {
                    $this->db->query(
                        "UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1, total_team_size = total_team_size + 1, updated_at = NOW() WHERE user_id = ?",
                        [$resolvedSponsorId]
                    );
                }

                // NOTE: no money moves at registration (anti fake-reg). The Rs.200
                // sponsor wallet credit + tier signup bonus are deferred to the
                // referred user's first booking via
                // ReferralService::processSignupRewardsOnFirstBooking().
            }

            // Linkage + tracking stay at registration (no money).
            if ($resolvedSponsorId) {
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

            // Track usage for tenant
            if (class_exists('\App\Core\Middleware\TenantContext') && class_exists('\App\Services\TenantService')) {
                try {
                    $tenantId = \App\Core\Middleware\TenantContext::getId();
                    if ($tenantId > 1) {
                        // Record in tenant_users pivot table
                        try {
                            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
                            $stmt = $pdo->prepare("INSERT IGNORE INTO tenant_users (tenant_id, user_id, role, is_primary) VALUES (?, ?, ?, 0)");
                            $stmt->execute([$tenantId, $userId, $role]);
                        } catch (\Throwable $e) {
                            error_log('UserRegistrationService: tenant_users insert failed: ' . $e->getMessage());
                        }
                        \App\Services\TenantService::getInstance()->incrementUsage($tenantId, 'users');
                    }
                } catch (\Throwable $e) {
                    error_log('UserRegistrationService: incrementUsage failed: ' . $e->getMessage());
                }
            }

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

        // Check if sponsor (associate/agent) gets activation bonus for first team member
        if ($sponsorId) {
            $this->checkSponsorTeamActivation($sponsorId);
        }
    }

    private function createAssociatesRecord(int $userId, string $name, string $email, string $phone, string $referralCode, ?int $sponsorId, string $role, string $agentType = ''): void
    {
        $validType = in_array($agentType, ['mlm_company', 'freelancer', 'independent'], true)
            ? $agentType
            : ($role === 'agent' ? 'freelancer' : 'mlm_company');

        $this->db->insert('associates', [
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'referral_code' => $referralCode,
            'sponsor_id' => $sponsorId,
            'level' => 'associate',
            'agent_type' => $validType,
            'agent_track' => ($role === 'agent' && $validType !== 'mlm_company') ? 'independent' : 'mlm',
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
                if ($field === 'phone' && !empty($val) && !preg_match('/^\d{10}$/', $val)) {
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
            $tid = $this->getTenantId();
            $this->db->query(
                "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? array_merge($params, [$tid]) : $params
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
            $tid = $this->getTenantId();
            $row = $this->db->fetchOne("SELECT password FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$userId, $tid] : [$userId]);
            if (!$row) {
                return ['success' => false, 'message' => 'User not found'];
            }
            if (!password_verify($currentPassword, $row['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $tid = $this->getTenantId();
            $this->db->query("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$hashed, $userId, $tid] : [$hashed, $userId]);
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

    /**
     * Check if sponsor gets activation bonus for first team member
     * Called after a new team member is added to the network tree
     */
    private function checkSponsorTeamActivation(int $sponsorId): void
    {
        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // Get sponsor info
            $stmt = $this->db->prepare("SELECT id, role, referred_by FROM users WHERE id = ?" . $tenantWhere . " LIMIT 1");
            $params = array_merge([$sponsorId], $tenantParams);
            $stmt->execute($params);
            $sponsor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sponsor || !in_array($sponsor['role'] ?? '', ['associate', 'agent'], true)) {
                return; // Only for associate/agent sponsors
            }

            // Count existing team members (downline) for this sponsor
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ?" . $tenantWhere);
            $params = array_merge([$sponsorId], $tenantParams);
            $stmt->execute($params);
            $teamCount = (int)$stmt->fetchColumn();

            // If this is the first team member (count was 0 before, now 1)
            if ($teamCount === 1) {
                // Trigger associate activation bonus for first team member
                $referralSvc = new \App\Services\ReferralService();
                $result = $referralSvc->processAssociateActivationBonus($sponsorId, 'first_team_member');
                if ($result['success']) {
                    error_log("[UserRegistrationService] Sponsor #{$sponsorId} activation bonus processed for first team member: " . json_encode($result));
                }
            }
        } catch (\Throwable $e) {
            error_log("[UserRegistrationService] Sponsor team activation check failed: " . $e->getMessage());
        }
    }
}
