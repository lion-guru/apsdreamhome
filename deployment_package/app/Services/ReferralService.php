<?php

namespace App\Services;

use App\Core\Database;
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

        $auditStmt = $this->conn->prepare("INSERT INTO mlm_import_audit (batch_reference, user_id, sponsor_user_id, referral_code, status, message, payload, processed_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $updateProfileStmt = $this->conn->prepare("UPDATE mlm_profiles SET sponsor_user_id = ?, sponsor_code = ?, updated_at = NOW() WHERE user_id = ?");

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
                                    $userId
                                ]);

                                if ($referralCodeOverride) {
                                    $stmtOverride = $this->conn->prepare("UPDATE mlm_profiles SET referral_code = ? WHERE user_id = ?");
                                    $stmtOverride->execute([$referralCodeOverride, $userId]);
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
                    $processedAt
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
        $stmt = $this->conn->prepare("INSERT INTO mlm_referrals (referrer_user_id, referred_user_id, referral_type, channel, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$referrer_user_id, $referred_user_id, $referral_type, $channel]);
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

    private function userExists(int $userId): bool
    {
        $stmt = $this->conn->prepare('SELECT 1 FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
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
}
