<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\RankService;
use Exception;
use PDO;

/**
 * Referral Service
 * Handles referral code generation, tracking, and MLM network management
 */

class ReferralService
{
    private PDO $conn;
    private RankService $rankService;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
        $this->rankService = new RankService();
    }

    /**
     * Bulk assign sponsors to existing MLM profiles.
     *
     * Expected record keys:
     *  - user_id (required)
     *  - sponsor_user_id (optional)
     *  - referral_code (optional override)
     *  - notes / message (optional)
     */
    public function bulkAssignSponsors(array $records, ?string $batchReference = null, array $options = []): array
    {
        $summary = [
            'processed' => 0,
            'success' => 0,
            'skipped' => 0,
            'errors' => 0,
            'batch_reference' => $batchReference ?: 'import_' . date('Ymd_His')
        ];

        if (empty($records)) {
            return $summary;
        }

        $dryRun = !empty($options['dry_run']);
        $batchReference = $summary['batch_reference'];

        try {
            $auditStmt = $this->conn->prepare("INSERT INTO mlm_import_audit (batch_reference, user_id, sponsor_user_id, referral_code, status, message, payload, processed_at, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $updateProfileStmt = $this->conn->prepare("UPDATE mlm_profiles SET sponsor_user_id = ?, sponsor_code = ?, updated_at = NOW() WHERE user_id = ? AND tenant_id = ?");

        foreach ($records as $record) {
            $summary['processed']++;

            $userId = isset($record['user_id']) ? (int) $record['user_id'] : 0;
            $sponsorId = isset($record['sponsor_user_id']) && $record['sponsor_user_id'] !== '' ? (int) $record['sponsor_user_id'] : null;
            $referralCodeOverride = isset($record['referral_code']) && $record['referral_code'] !== '' ? trim($record['referral_code']) : null;
            $notes = isset($record['notes']) ? trim($record['notes']) : null;
            $payload = json_encode($record, JSON_UNESCAPED_UNICODE);
            $status = 'pending';
            $message = '';
            $processedAt = null;

            if ($userId <= 0) {
                $status = 'error';
                $message = 'Missing or invalid user_id';
                $summary['errors']++;
            } elseif (!$this->userExists($userId)) {
                $status = 'error';
                $message = 'User not found';
                $summary['errors']++;
            } elseif ($sponsorId && !$this->userExists($sponsorId)) {
                $status = 'error';
                $message = 'Sponsor user not found';
                $summary['errors']++;
            } else {
                $profile = $this->getProfile($userId);

                if (!$profile) {
                    $status = 'error';
                    $message = 'MLM profile missing for user';
                    $summary['errors']++;
                } elseif ($sponsorId && $userId === $sponsorId) {
                    $status = 'error';
                    $message = 'User cannot sponsor themselves';
                    $summary['errors']++;
                } else {
                    $currentSponsorId = $profile['sponsor_user_id'] ?? null;

                    if ($sponsorId && $this->introducesCircularReference($userId, $sponsorId)) {
                        $status = 'error';
                        $message = 'Circular sponsor relationship detected';
                        $summary['errors']++;
                    } elseif ($sponsorId === $currentSponsorId) {
                        $status = 'skipped';
                        $message = 'Sponsor unchanged';
                        $summary['skipped']++;
                    } else {
                        $sponsorCode = null;

                        if ($sponsorId) {
                            $sponsorProfile = $this->getProfile($sponsorId);
                            if (!$sponsorProfile) {
                                $status = 'error';
                                $message = 'Sponsor MLM profile missing';
                                $summary['errors']++;
                            } else {
                                $sponsorCode = $sponsorProfile['referral_code'];
                                $status = 'success';
                            }
                        } else {
                            $status = 'success';
                        }

                        if ($status === 'success' && !$dryRun) {
                            $this->conn->beginTransaction();
                            try {
                                $updateProfileStmt->execute([
                                    $sponsorId,
                                    $sponsorCode,
                                    $userId,
                                    $this->getTenantId()
                                ]);

                                if ($referralCodeOverride) {
                                    $stmtOverride = $this->conn->prepare("UPDATE mlm_profiles SET referral_code = ? WHERE user_id = ? AND tenant_id = ?");
                                    $stmtOverride->execute([$referralCodeOverride, $userId, $this->getTenantId()]);
                                }

                                $this->conn->commit();
                                $summary['success']++;
                                $message = $notes ?: 'Sponsor updated';
                                $processedAt = date('Y-m-d H:i:s');
                            } catch (Exception $e) {
                                if ($this->conn->inTransaction()) {
                                    $this->conn->rollBack();
                                }
                                $status = 'error';
                                $message = 'DB error: ' . $e->getMessage();
                                $summary['errors']++;
                            }
                        } elseif ($status === 'success' && $dryRun) {
                            $message = '[dry-run] Sponsor would be updated';
                            $summary['success']++;
                        }
                    }
                }
            }

            if ($dryRun) {
                $status = $status === 'success' ? 'pending' : $status;
            }

            if (!$dryRun || !empty($options['log_dry_run'])) {
                $auditStmt->execute([
                    $batchReference,
                    $userId,
                    $sponsorId,
                    $referralCodeOverride,
                    $status,
                    $message,
                    $payload,
                    $processedAt,
                    $this->getTenantId()
                ]);
            }
        }

        return $summary;
    }

    /**
     * Generate unique referral code
     */
    public function generateReferralCode($name, $email, $user_type = null)
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $suffix = strtoupper(substr(md5($email . microtime()), 0, 4));
        $type_prefix = strtoupper(substr($user_type ?? 'U', 0, 1));

        $code = $prefix . $type_prefix . $suffix;

        // Ensure uniqueness
        $counter = 1;
        while ($this->codeExists($code)) {
            $code = $prefix . $type_prefix . strtoupper(substr(md5($email . microtime() . $counter), 0, 4));
            $counter++;
        }

        return $code;
    }

    /**
     * Check if code exists
     */
    private function codeExists($code)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM mlm_profiles WHERE referral_code = ?");
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['count'] ?? 0) > 0;
    }

    /**
     * Get referral link for user
     */
    public function getReferralLink($user_id, $role = null)
    {
        $stmt = $this->conn->prepare("SELECT referral_code FROM mlm_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $params = ['ref' => $result['referral_code']];
            if ($role) {
                $params['type'] = $role;
            }
            return (defined('BASE_URL') ? BASE_URL : '/') . 'register?' . http_build_query($params);
        }
        return null;
    }

    /**
     * Get QR code for referral
     */
    public function getQRCode($referral_link)
    {
        // Use Google Charts API for QR code
        $qr_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($referral_link);
        return $qr_url;
    }

    /**
     * Track referral
     */
    public function trackReferral($referrer_user_id, $referred_user_id, $referral_type, $channel = 'direct_link')
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO mlm_referrals (referrer_user_id, referred_user_id, referral_type, channel, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        return $stmt->execute([$referrer_user_id, $referred_user_id, $referral_type, $channel, $this->getTenantId()]);
    }

    /**
     * Get user's network tree
     */
    public function getNetworkTree($user_id, $max_depth = 5, array $options = [])
    {
        $stmt = $this->conn->prepare("
            SELECT 
                u.id, u.name, u.email, u.type,
                mp.referral_code, mp.current_level, mp.total_team_size,
                mp.direct_referrals, mp.total_commission, mp.plan_mode,
                mp.lifetime_sales,
                nt.level, nt.created_at
            FROM mlm_network_tree nt
            JOIN users u ON nt.descendant_user_id = u.id
            JOIN mlm_profiles mp ON u.id = mp.user_id
            WHERE nt.ancestor_user_id = ? AND nt.level <= ?
            ORDER BY nt.level, nt.created_at
        ");
        $stmt->execute([$user_id, $max_depth]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = isset($options['query']) ? strtolower($options['query']) : null;
        $rankFilter = $options['rank'] ?? null;

        $members = array_filter($members, function ($member) use ($query, $rankFilter) {
            if ($query) {
                $haystack = strtolower(($member['name'] ?? '') . ' ' . ($member['email'] ?? ''));
                if (strpos($haystack, $query) === false) {
                    return false;
                }
            }

            if ($rankFilter && strcasecmp($member['current_level'] ?? '', $rankFilter) !== 0) {
                return false;
            }

            return true;
        });

        return array_map(function ($member) {
            $rankInfo = $this->rankService->getRankInfo((float) ($member['lifetime_sales'] ?? 0));
            $member['rank_label'] = $rankInfo['current_label'];
            $member['rank_color'] = $rankInfo['color'];
            $member['rank_reward'] = $rankInfo['reward'];
            $member['rank_progress'] = $rankInfo['progress_percent'];
            unset($member['lifetime_sales']);
            return $member;
        }, $members);
    }

    /**
     * Get direct referrals
     */
    public function getDirectReferrals($user_id)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    u.id, u.name, u.email, u.type, u.created_at,
                    mp.referral_code, mp.total_commission
                FROM mlm_referrals r
                JOIN users u ON r.referred_user_id = u.id
                JOIN mlm_profiles mp ON u.id = mp.user_id
                WHERE r.referrer_user_id = ?
                ORDER BY r.created_at DESC
            ");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get network statistics
     */
    public function getNetworkStats($user_id)
    {
        $profile = $this->getProfile($user_id);

        $direct_referrals = $this->getDirectReferrals($user_id);
        $total_team = $this->countTeamMembers($user_id);
        $rankInfo = $profile ? $this->rankService->getRankInfo((float) $profile['lifetime_sales']) : null;

        $stmt = $this->conn->prepare('
            SELECT level, COUNT(*) AS member_count
            FROM mlm_network_tree
            WHERE ancestor_user_id = ?
            GROUP BY level
            ORDER BY level
        ');
        $stmt->execute([$user_id]);
        $level_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $level_breakdown = array_map(function ($row) {
            return [
                'level' => (int) $row['level'],
                'count' => (int) $row['member_count'],
            ];
        }, $level_breakdown);

        return [
            'profile' => $profile,
            'direct_referrals' => count($direct_referrals),
            'total_team' => $total_team,
            'total_commission' => (float) ($profile['total_commission'] ?? 0),
            'pending_commission' => (float) ($profile['pending_commission'] ?? 0),
            'level_breakdown' => $level_breakdown,
            'rank' => $rankInfo,
            'plan_mode' => $profile['plan_mode'] ?? 'rank',
        ];
    }

    public function getReferralAnalytics($user_id, $days = 30)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as referrals,
                    referral_type
                FROM mlm_referrals
                WHERE referrer_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at), referral_type
                ORDER BY date DESC
            ");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt->execute([$user_id, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top referrers
     */
    public function getTopReferrers($limit = 10)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                u.id, u.name, u.email, u.type,
                mp.referral_code, mp.direct_referrals,
                mp.total_commission
            FROM mlm_profiles mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.status = 'active'
            ORDER BY mp.direct_referrals DESC, mp.total_commission DESC
            LIMIT ?
        ");
        // PDO limit requires integer binding
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate referral code
     */
    public function validateReferralCode($code)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                u.id, u.name, u.email, u.type,
                mp.referral_code, mp.current_level
            FROM mlm_profiles mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.referral_code = ? AND mp.status = 'active'
        ");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get referral dashboard data
     */
    public function getReferralDashboard($user_id)
    {
        $stats = $this->getNetworkStats($user_id);
        $direct_referrals = $this->getDirectReferrals($user_id);
        $network_tree = $this->getNetworkTree($user_id);
        $analytics = $this->getReferralAnalytics($user_id);

        return [
            'stats' => $stats,
            'direct_referrals' => $direct_referrals,
            'network_tree' => $network_tree,
            'analytics' => $analytics
        ];
    }

    // ── Customer Referral Program Methods ──────────────────────────

    /**
     * Get or create referral code for a user (format: APS-{FIRST3NAME}{LAST4PHONE})
     */
    public function getReferralCode(int $userId): string
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $stmt = $this->conn->prepare("SELECT referral_code, name, phone FROM users WHERE id = ?$tenantWhere LIMIT 1");
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return '';
        }

        if (!empty($row['referral_code'])) {
            return $row['referral_code'];
        }

        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $row['name'] ?? 'USR'), 0, 3));
        if (empty($prefix)) $prefix = 'USR';
        $last4 = substr(preg_replace('/[^0-9]/', '', $row['phone'] ?? ''), -4);
        if (strlen($last4) < 4) $last4 = str_pad($last4, 4, '0', STR_PAD_LEFT);
        $code = 'APS-' . $prefix . $last4;

        // Ensure uniqueness
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $check = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE referral_code = ? AND id != ?$tenantWhere");
        $checkParams = [$code, $userId];
        if ($tid > 1) $checkParams[] = $tid;
        $check->execute($checkParams);
        if ((int)$check->fetchColumn() > 0) {
            $code = $code . strtoupper(substr(uniqid(), -3));
        }

        $tenantUpdWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $stmt = $this->conn->prepare("UPDATE users SET referral_code = ? WHERE id = ?$tenantUpdWhere AND (referral_code IS NULL OR referral_code = '')");
        $updParams = [$code, $userId];
        if ($tid > 1) $updParams[] = $tid;
        $stmt->execute($updParams);

        return $code;
    }

    /**
     * Get referral stats for a user
     */
    public function getReferralStats(int $userId): array
    {
        $stats = [
            'total_referrals' => 0,
            'successful_referrals' => 0,
            'total_earned' => 0.0,
            'pending_earned' => 0.0,
        ];

        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?$tenantWhere");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $stats['total_referrals'] = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT u.id) 
                FROM users u 
                INNER JOIN plot_bookings pb ON pb.customer_id = u.id 
                WHERE u.referred_by = ? AND pb.status NOT IN ('cancelled')
            ");
            $stmt->execute([$userId]);
            $stats['successful_referrals'] = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        try {
            $stmt = $this->conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'referral' AND status = 'paid'");
            $stmt->execute([$userId]);
            $stats['total_earned'] = (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        try {
            $stmt = $this->conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'referral' AND status = 'pending'");
            $stmt->execute([$userId]);
            $stats['pending_earned'] = (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        return $stats;
    }

    /**
     * Get list of referred users
     */
    public function getReferredUsers(int $userId): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.id, u.name, u.email, u.phone, u.created_at,
                       CASE WHEN pb.id IS NOT NULL THEN 1 ELSE 0 END AS has_booking,
                       COALESCE(ml.amount, 0) AS commission_earned
                FROM users u
                LEFT JOIN plot_bookings pb ON pb.customer_id = u.id AND pb.status NOT IN ('cancelled')
                LEFT JOIN mlm_commission_ledger ml ON ml.source_user_id = u.id AND ml.beneficiary_user_id = ? AND ml.commission_type = 'referral'
                WHERE u.referred_by = ?
                ORDER BY u.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Process referral commission on booking
     */
    public function processReferralCommission(int $referredUserId, int $bookingId, float $bookingAmount): array
    {
        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->conn->prepare("SELECT referred_by FROM users WHERE id = ?$tenantWhere LIMIT 1");
            $params = [$referredUserId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row['referred_by'])) {
                return ['success' => false, 'message' => 'No referrer found'];
            }

            $referrerId = (int)$row['referred_by'];
            if ($referrerId === $referredUserId) {
                return ['success' => false, 'message' => 'Cannot refer yourself'];
            }

            // Check if commission already processed for this booking
            $stmt = $this->conn->prepare("SELECT id FROM mlm_commission_ledger WHERE source_user_id = ? AND booking_id = ? AND commission_type = 'referral' LIMIT 1");
            $stmt->execute([$referredUserId, $bookingId]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Commission already processed'];
            }

            // 1% flat referral commission
            $commissionAmount = round($bookingAmount * 0.01, 2);

            // Get booking number for notes
            $stmt = $this->conn->prepare("SELECT booking_number FROM plot_bookings WHERE id = ? LIMIT 1");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            $bookingNumber = $booking['booking_number'] ?? "#{$bookingId}";

            // Get referrer name
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->conn->prepare("SELECT name FROM users WHERE id = ?$tenantWhere LIMIT 1");
            $params = [$referrerId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->conn->prepare("
                INSERT INTO mlm_commission_ledger 
                    (beneficiary_user_id, source_user_id, commission_type, amount, status, booking_id, notes, tenant_id, created_at)
                VALUES (?, ?, 'referral', ?, 'pending', ?, ?, ?, NOW())
            ")->execute([
                $referrerId,
                $referredUserId,
                $commissionAmount,
                $bookingId,
                "Referral commission for booking {$bookingNumber}",
                $this->getTenantId()
            ]);

            // Send referral commission email to referrer
            try {
                $emailSvc = new \App\Services\EmailTemplateService();
                $emailSvc->sendReferralCommission($referrerId, [
                    'referrer_name' => $referrer['name'] ?? 'User',
                    'commission_amount' => number_format($commissionAmount, 2),
                    'booking_number' => $bookingNumber,
                    'booking_amount' => number_format($bookingAmount, 2),
                ]);
            } catch (\Throwable $e) {
                error_log("[ReferralService] commission email failed: " . $e->getMessage());
            }

            return [
                'success' => true,
                'referrer_name' => $referrer['name'] ?? 'User',
                'commission_amount' => $commissionAmount,
            ];
        } catch (\Throwable $e) {
            error_log("[ReferralService] processReferralCommission failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Commission processing failed'];
        }
    }

    /**
     * Generate referral share URL
     */
    public function getShareUrl(string $code): string
    {
        return (defined('BASE_URL') ? BASE_URL : '') . '/register?ref=' . urlencode($code);
    }

    /**
     * Validate referral code (queries users table)
     */
    public function validateUserReferralCode(string $code): ?array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $stmt = $this->conn->prepare("SELECT id, name FROM users WHERE referral_code = ?$tenantWhere LIMIT 1");
        $params = [$code];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Apply referral on registration
     */
    public function applyReferral(int $newUserId, string $code): bool
    {
        $referrer = $this->validateUserReferralCode($code);
        if (!$referrer || (int)$referrer['id'] === $newUserId) {
            return false;
        }

        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $stmt = $this->conn->prepare("UPDATE users SET referred_by = ? WHERE id = ?$tenantWhere AND (referred_by IS NULL OR referred_by = 0)");
        $params = [(int)$referrer['id'], $newUserId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    private function userExists(int $userId): bool
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE id = ?$tenantWhere LIMIT 1");
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return (bool) $stmt->fetch(PDO::FETCH_NUM);
    }

    private function getProfile(int $userId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_profiles WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    private function introducesCircularReference(int $userId, int $potentialSponsorId): bool
    {
        if ($userId === $potentialSponsorId) {
            return true;
        }

        $stmt = $this->conn->prepare('SELECT ancestor_user_id FROM mlm_network_tree WHERE descendant_user_id = ?');
        $stmt->execute([$potentialSponsorId]);
        $ancestors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ancestors as $row) {
            if ((int) $row['ancestor_user_id'] === $userId) {
                return true;
            }
        }

        return false;
    }

    private function countTeamMembers(int $ancestorId): int
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) AS team_size FROM mlm_network_tree WHERE ancestor_user_id = ?');
        $stmt->execute([$ancestorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['team_size'] ?? 0);
    }

    // ── Referral Leaderboard ──────────────────────────────────────

    /**
     * Get referral leaderboard — top referrers ranked by referral count + earnings
     */
    public function getLeaderboard(int $limit = 20, string $period = 'all'): array
    {
        $dateFilter = '';
        $params = [];
        if ($period === 'monthly') {
            $dateFilter = 'AND cr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        } elseif ($period === 'weekly') {
            $dateFilter = 'AND cr.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        } elseif ($period === 'yearly') {
            $dateFilter = 'AND cr.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
        }

        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    u.id, u.name, u.referral_code,
                    COUNT(DISTINCT cr.referred_user_id) AS referral_count,
                    COUNT(DISTINCT CASE WHEN cr.status = 'booked' THEN cr.referred_user_id END) AS booked_count,
                    COALESCE(SUM(CASE WHEN cr.status = 'booked' THEN 1 ELSE 0 END), 0) AS bookings_made,
                    (SELECT COUNT(DISTINCT u2.referred_by) FROM users u2 WHERE u2.referred_by = u.id) AS total_signups
                FROM users u
                LEFT JOIN customer_referrals cr ON cr.referrer_user_id = u.id {$dateFilter}
                WHERE u.referral_code IS NOT NULL AND u.referral_code != ''
                GROUP BY u.id
                HAVING referral_count > 0 OR total_signups > 0
                ORDER BY referral_count DESC, booked_count DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Add rank + tier info
            foreach ($rows as &$row) {
                $row['rank'] = 0; // will be set below
                $tier = $this->getUserTier((int)$row['id']);
                $row['tier'] = $tier['tier'];
                $row['tier_color'] = $tier['color'];
                $row['tier_icon'] = $tier['icon'];
            }
            unset($row);

            // Assign ranks
            foreach ($rows as $i => &$row) {
                $row['rank'] = $i + 1;
            }
            unset($row);

            return $rows;
        } catch (\Throwable $e) {
            error_log("[ReferralService] getLeaderboard error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a user's position on the leaderboard
     */
    public function getUserRank(int $userId): array
    {
        $leaderboard = $this->getLeaderboard(100);
        foreach ($leaderboard as $entry) {
            if ((int)$entry['id'] === $userId) {
                return [
                    'rank' => $entry['rank'],
                    'total' => count($leaderboard),
                    'referral_count' => $entry['referral_count'],
                    'tier' => $entry['tier'],
                ];
            }
        }
        return ['rank' => 0, 'total' => count($leaderboard), 'referral_count' => 0, 'tier' => 'bronze'];
    }

    // ── Tiered Referral Bonuses ───────────────────────────────────

    /**
     * Referral tier definitions
     */
    public static function getTiers(): array
    {
        return [
            [
                'tier' => 'bronze',
                'label' => 'Bronze',
                'min_referrals' => 0,
                'bonus_per_referral' => 100,
                'bonus_on_booking' => 500,
                'color' => '#CD7F32',
                'icon' => 'fas fa-medal',
                'perks' => ['₹100 per signup', '₹500 on booking', 'Basic referral badge'],
            ],
            [
                'tier' => 'silver',
                'label' => 'Silver',
                'min_referrals' => 5,
                'bonus_per_referral' => 200,
                'bonus_on_booking' => 1000,
                'color' => '#94a3b8',
                'icon' => 'fas fa-medal',
                'perks' => ['₹200 per signup', '₹1,000 on booking', 'Silver badge', 'Priority support'],
            ],
            [
                'tier' => 'gold',
                'label' => 'Gold',
                'min_referrals' => 15,
                'bonus_per_referral' => 500,
                'bonus_on_booking' => 2500,
                'color' => '#f59e0b',
                'icon' => 'fas fa-crown',
                'perks' => ['₹500 per signup', '₹2,500 on booking', 'Gold badge', 'Priority support', 'Exclusive offers'],
            ],
            [
                'tier' => 'platinum',
                'label' => 'Platinum',
                'min_referrals' => 30,
                'bonus_per_referral' => 1000,
                'bonus_on_booking' => 5000,
                'color' => '#6366f1',
                'icon' => 'fas fa-gem',
                'perks' => ['₹1,000 per signup', '₹5,000 on booking', 'Platinum badge', 'Dedicated manager', 'VIP events'],
            ],
        ];
    }

    /**
     * Get user's current tier based on referral count
     */
    public function getUserTier(int $userId): array
    {
        $tiers = self::getTiers();

        // Count total referrals (signups + customer_referrals)
        $signupCount = 0;
        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?$tenantWhere");
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $signupCount = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        $crCount = 0;
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM customer_referrals WHERE referrer_user_id = ?");
            $stmt->execute([$userId]);
            $crCount = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        $totalReferrals = max($signupCount, $crCount);

        // Find current tier (highest one user qualifies for)
        $currentTier = $tiers[0];
        $nextTier = null;
        foreach ($tiers as $i => $tier) {
            if ($totalReferrals >= $tier['min_referrals']) {
                $currentTier = $tier;
                $nextTier = $tiers[$i + 1] ?? null;
            }
        }

        // Progress to next tier
        $progress = 100;
        $referralsNeeded = 0;
        if ($nextTier) {
            $progress = min(100, round(($totalReferrals / $nextTier['min_referrals']) * 100));
            $referralsNeeded = max(0, $nextTier['min_referrals'] - $totalReferrals);
        }

        return [
            'tier' => $currentTier['tier'],
            'label' => $currentTier['label'],
            'color' => $currentTier['color'],
            'icon' => $currentTier['icon'],
            'perks' => $currentTier['perks'],
            'bonus_per_referral' => $currentTier['bonus_per_referral'],
            'bonus_on_booking' => $currentTier['bonus_on_booking'],
            'total_referrals' => $totalReferrals,
            'next_tier' => $nextTier ? $nextTier['label'] : null,
            'next_tier_min' => $nextTier ? $nextTier['min_referrals'] : null,
            'progress' => $progress,
            'referrals_needed' => $referralsNeeded,
        ];
    }

    /**
     * Process tiered bonus on referral signup
     */
    public function processTieredSignupBonus(int $referrerId, int $referredUserId): array
    {
        $tier = $this->getUserTier($referrerId);
        $bonusAmount = $tier['bonus_per_referral'];

        if ($bonusAmount <= 0) {
            return ['success' => false, 'message' => 'No bonus for current tier'];
        }

        // Check if bonus already paid for this referred user
        try {
            $stmt = $this->conn->prepare(
                "SELECT id FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND source_user_id = ? AND commission_type = 'referral_signup' LIMIT 1"
            );
            $stmt->execute([$referrerId, $referredUserId]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Signup bonus already processed'];
            }
        } catch (\Throwable $e) {}

        try {
             $referrerName = '';
             $tid = $this->getTenantId();
             $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
             $stmt = $this->conn->prepare("SELECT name FROM users WHERE id = ?$tenantWhere");
             $params = [$referrerId];
             if ($tid > 1) $params[] = $tid;
             $stmt->execute($params);
             $row = $stmt->fetch(PDO::FETCH_ASSOC);
             $referrerName = $row['name'] ?? 'User';

             $referredName = '';
             $stmt = $this->conn->prepare("SELECT name FROM users WHERE id = ?$tenantWhere");
             $params2 = [$referredUserId];
             if ($tid > 1) $params2[] = $tid;
             $stmt->execute($params2);
             $row = $stmt->fetch(PDO::FETCH_ASSOC);
             $referredName = $row['name'] ?? 'User';

             $this->conn->prepare("
                 INSERT INTO mlm_commission_ledger 
                     (beneficiary_user_id, source_user_id, commission_type, amount, status, notes, tenant_id, created_at)
                 VALUES (?, ?, 'referral_signup', ?, 'approved', ?, ?, NOW())
             ")->execute([
                $referrerId,
                $referredUserId,
                $bonusAmount,
                "Tiered signup bonus ({$tier['label']}) for referral: {$referredName}",
                $this->getTenantId()
            ]);

            return [
                'success' => true,
                'amount' => $bonusAmount,
                'tier' => $tier['label'],
                'message' => "₹{$bonusAmount} {$tier['label']} signup bonus credited",
            ];
        } catch (\Throwable $e) {
            error_log("[ReferralService] processTieredSignupBonus error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Bonus processing failed'];
        }
    }

    /**
     * Process tiered bonus on referral booking
     */
    public function processTieredBookingBonus(int $referrerId, int $referredUserId, int $bookingId, float $bookingAmount): array
    {
        $tier = $this->getUserTier($referrerId);
        $bonusAmount = $tier['bonus_on_booking'];

        if ($bonusAmount <= 0) {
            return ['success' => false, 'message' => 'No booking bonus for current tier'];
        }

        // Check if already processed
        try {
            $stmt = $this->conn->prepare(
                "SELECT id FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND source_user_id = ? AND commission_type = 'referral_booking' AND booking_id = ? LIMIT 1"
            );
            $stmt->execute([$referrerId, $referredUserId, $bookingId]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Booking bonus already processed'];
            }
        } catch (\Throwable $e) {}

         try {
             $referredName = '';
             $tid = $this->getTenantId();
             $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
             $stmt = $this->conn->prepare("SELECT name FROM users WHERE id = ?$tenantWhere");
             $params = [$referredUserId];
             if ($tid > 1) $params[] = $tid;
             $stmt->execute($params);
             $row = $stmt->fetch(PDO::FETCH_ASSOC);
             $referredName = $row['name'] ?? 'User';

             $bookingNumber = "#{$bookingId}";
            $stmt = $this->conn->prepare("SELECT booking_number FROM plot_bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) $bookingNumber = $row['booking_number'] ?? "#{$bookingId}";

            $this->conn->prepare("
                INSERT INTO mlm_commission_ledger 
                    (beneficiary_user_id, source_user_id, commission_type, amount, status, booking_id, notes, tenant_id, created_at)
                VALUES (?, ?, 'referral_booking', ?, 'approved', ?, ?, ?, NOW())
            ")->execute([
                $referrerId,
                $referredUserId,
                $bonusAmount,
                $bookingId,
                "Tiered booking bonus ({$tier['label']}) for {$referredName}'s booking {$bookingNumber}",
                $this->getTenantId()
            ]);

            return [
                'success' => true,
                'amount' => $bonusAmount,
                'tier' => $tier['label'],
                'message' => "₹{$bonusAmount} {$tier['label']} booking bonus credited",
            ];
        } catch (\Throwable $e) {
            error_log("[ReferralService] processTieredBookingBonus error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Booking bonus processing failed'];
        }
    }

    // ── Share Conversion Funnel ───────────────────────────────────

    /**
     * Get share conversion funnel stats
     * shares → signups → bookings
     */
    public function getShareConversionFunnel(): array
    {
        $funnel = [
            'total_shares' => 0,
            'total_signups' => 0,
            'total_bookings' => 0,
            'conversion_rate' => 0,
            'booking_rate' => 0,
            'by_platform' => [],
        ];

        try {
            // Total shares from users.share_clicks
            $users = $this->conn->query("SELECT share_clicks FROM users WHERE share_clicks IS NOT NULL AND share_clicks != '{}' AND share_clicks != ''")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($users as $u) {
                $clicks = json_decode($u['share_clicks'] ?? '{}', true);
                if (is_array($clicks)) {
                    $funnel['total_shares'] += array_sum($clicks);
                    foreach ($clicks as $platform => $count) {
                        $funnel['by_platform'][$platform] = ($funnel['by_platform'][$platform] ?? 0) + $count;
                    }
                }
            }

            // Total signups from customer_referrals
            $stmt = $this->conn->query("SELECT COUNT(*) FROM customer_referrals WHERE status IN ('registered', 'booked')");
            $funnel['total_signups'] = (int)$stmt->fetchColumn();

            // Total bookings
            $stmt = $this->conn->query("SELECT COUNT(*) FROM customer_referrals WHERE status = 'booked'");
            $funnel['total_bookings'] = (int)$stmt->fetchColumn();

            // Conversion rates
            if ($funnel['total_shares'] > 0) {
                $funnel['conversion_rate'] = round(($funnel['total_signups'] / $funnel['total_shares']) * 100, 1);
            }
            if ($funnel['total_signups'] > 0) {
                $funnel['booking_rate'] = round(($funnel['total_bookings'] / $funnel['total_signups']) * 100, 1);
            }

            // Sort platforms by count
            arsort($funnel['by_platform']);
        } catch (\Throwable $e) {
            error_log("[ReferralService] getShareConversionFunnel error: " . $e->getMessage());
        }

        return $funnel;
    }

    /**
     * Get top sharers
     */
    public function getTopSharers(int $limit = 10): array
    {
        try {
            $users = $this->conn->query("
                SELECT id, name, share_clicks 
                FROM users 
                WHERE share_clicks IS NOT NULL AND share_clicks != '{}' AND share_clicks != ''
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $sharers = [];
            foreach ($users as $u) {
                $clicks = json_decode($u['share_clicks'] ?? '{}', true);
                $total = is_array($clicks) ? array_sum($clicks) : 0;
                if ($total > 0) {
                    $sharers[] = [
                        'id' => $u['id'],
                        'name' => $u['name'],
                        'total_shares' => $total,
                        'platforms' => $clicks,
                    ];
                }
            }

            usort($sharers, fn($a, $b) => $b['total_shares'] - $a['total_shares']);
            return array_slice($sharers, 0, $limit);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getTenantId(): int
    {
        if (class_exists('\App\Core\Middleware\TenantContext')) {
            try {
                return \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
                return 1;
            }
        }
        return 1;
    }
}
