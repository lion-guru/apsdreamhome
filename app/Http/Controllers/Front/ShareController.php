<?php

namespace App\Http\Controllers\Front;

use App\Core\Database\Database;

/**
 * Share Controller — Track referral share analytics
 */
class ShareController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Track a share event — increments platform counter in users.share_clicks
     * POST /share/track
     */
    public function trackShare()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }

        $platform = $_POST['platform'] ?? '';
        $referralCode = $_POST['referral_code'] ?? '';

        if (empty($platform) || empty($referralCode)) {
            echo json_encode(['success' => false, 'message' => 'Missing platform or referral_code']);
            exit;
        }

        $validPlatforms = ['whatsapp', 'facebook', 'twitter', 'telegram', 'linkedin', 'email', 'sms', 'copy', 'link'];
        if (!in_array($platform, $validPlatforms)) {
            $platform = 'other';
        }

        try {
            $db = Database::getInstance()->getConnection();
            $userId = $_SESSION['user_id'] ?? null;

            if ($userId) {
                $stmt = $db->prepare("SELECT share_clicks FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $clicks = $row ? json_decode($row['share_clicks'] ?? '{}', true) : [];
                $clicks[$platform] = ($clicks[$platform] ?? 0) + 1;
                $json = json_encode($clicks);
                $stmt = $db->prepare("UPDATE users SET share_clicks = ? WHERE id = ?");
                $stmt->execute([$json, $userId]);
            }

            echo json_encode(['success' => true, 'message' => 'Share tracked']);
        } catch (\Throwable $e) {
            error_log('ShareController trackShare error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Tracking failed']);
        }
        exit;
    }

    /**
     * Share analytics page — queries referral data from users.share_clicks + customer_referrals
     * GET /share/stats
     */
    public function shareStats()
    {
        $this->requireAdmin ?? null;

        try {
            $db = Database::getInstance()->getConnection();

            // Calculate total shares from users.share_clicks JSON
            $users = $db->query("SELECT id, name, share_clicks FROM users WHERE share_clicks IS NOT NULL AND share_clicks != '{}' AND share_clicks != ''")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $totalShares = 0;
            $platformTotals = [];
            $sharesByUser = [];
            foreach ($users as $u) {
                $clicks = json_decode($u['share_clicks'] ?? '{}', true);
                if (!is_array($clicks)) continue;
                $userTotal = array_sum($clicks);
                $totalShares += $userTotal;
                $sharesByUser[] = ['name' => $u['name'], 'share_count' => $userTotal];
                foreach ($clicks as $platform => $count) {
                    $platformTotals[$platform] = ($platformTotals[$platform] ?? 0) + $count;
                }
            }
            usort($sharesByUser, fn($a, $b) => $b['share_count'] - $a['share_count']);
            $sharesByUser = array_slice($sharesByUser, 0, 10);

            $sharesByPlatform = [];
            foreach ($platformTotals as $platform => $cnt) {
                $sharesByPlatform[] = ['platform' => $platform, 'cnt' => $cnt];
            }
            usort($sharesByPlatform, fn($a, $b) => $b['cnt'] - $a['cnt']);

            // Referral conversions from customer_referrals
            $referrals = $db->query("
                SELECT cr.*, u.name as referred_name, u.email as referred_email, u.phone as referred_phone
                FROM customer_referrals cr
                LEFT JOIN users u ON cr.referred_user_id = u.id
                ORDER BY cr.created_at DESC LIMIT 50
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            return $this->render('admin/crm/share_stats', [
                'total_shares' => $totalShares,
                'shares_by_platform' => $sharesByPlatform,
                'shares_by_user' => $sharesByUser,
                'recent_shares' => $referrals,
                'page_title' => 'Share Analytics',
                'current_page' => 'crm',
            ]);
        } catch (\Throwable $e) {
            error_log('ShareController shareStats error: ' . $e->getMessage());
            return $this->render('admin/crm/share_stats', [
                'total_shares' => 0,
                'shares_by_platform' => [],
                'shares_by_user' => [],
                'recent_shares' => [],
                'page_title' => 'Share Analytics',
                'current_page' => 'crm',
            ]);
        }
    }
}
