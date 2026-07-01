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
     * Track a share event
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
        $leadId = (int)($_POST['lead_id'] ?? 0);

        if (empty($platform) || empty($referralCode)) {
            echo json_encode(['success' => false, 'message' => 'Missing platform or referral_code']);
            exit;
        }

        $validPlatforms = ['whatsapp', 'facebook', 'twitter', 'telegram', 'linkedin', 'email', 'sms', 'copy'];
        if (!in_array($platform, $validPlatforms)) {
            $platform = 'other';
        }

        try {
            $db = Database::getInstance();
            $userId = $_SESSION['user_id'] ?? null;

            $shareData = [
                'lead_id'            => $leadId ?: null,
                'shared_by_user_id'  => $userId,
                'shared_to_phone'    => $_POST['phone'] ?? '',
                'share_method'       => in_array($platform, ['whatsapp', 'telegram']) ? 'direct' : 'broadcast',
                'message'            => $_POST['message'] ?? "Shared via {$platform}",
                'status'             => 'sent',
                'sent_at'            => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
            ];

            $db->insert('whatsapp_lead_shares', $shareData);

            echo json_encode(['success' => true, 'message' => 'Share tracked']);
        } catch (\Throwable $e) {
            error_log('ShareController trackShare error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Tracking failed']);
        }
        exit;
    }

    /**
     * Share analytics page
     * GET /share/stats
     */
    public function shareStats()
    {
        $this->requireAdmin ?? null;

        try {
            $db = Database::getInstance()->getConnection();

            $totalShares = (int)$db->query("SELECT COUNT(*) FROM whatsapp_lead_shares")->fetchColumn();
            $sharesByPlatform = $db->query("SELECT share_method as platform, COUNT(*) as cnt FROM whatsapp_lead_shares GROUP BY share_method ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $sharesByUser = $db->query("SELECT u.name, COUNT(*) as share_count FROM whatsapp_lead_shares ws LEFT JOIN users u ON ws.shared_by_user_id = u.id WHERE ws.shared_by_user_id IS NOT NULL GROUP BY ws.shared_by_user_id ORDER BY share_count DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $recentShares = $db->query("SELECT ws.*, l.name as lead_name, u.name as sharer_name FROM whatsapp_lead_shares ws LEFT JOIN leads l ON ws.lead_id = l.id LEFT JOIN users u ON ws.shared_by_user_id = u.id ORDER BY ws.created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            return $this->render('admin/crm/share_stats', [
                'total_shares' => $totalShares,
                'shares_by_platform' => $sharesByPlatform,
                'shares_by_user' => $sharesByUser,
                'recent_shares' => $recentShares,
                'page_title' => 'Share Analytics',
                'current_page' => 'crm',
            ]);
        } catch (\Throwable $e) {
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
