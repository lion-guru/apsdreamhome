<?php
/**
 * RankPromotionNotificationService
 *
 * Sends notifications to agents when they get promoted to a higher rank.
 * Supports multiple channels: email, SMS, push, in-app.
 *
 * Triggered by:
 *   - CommissionManager rank evaluation
 *   - MLMCommissionEngine daily cron
 *   - HybridCommissionEngine manual trigger
 */

namespace App\Services\MLM;

use PDO;
use Exception;

class RankPromotionNotificationService
{
    protected $db;

    /** Rank display names and benefits */
    const RANK_INFO = [
        'associate' => ['label' => 'Associate', 'icon' => 'fa-user', 'color' => '#6c757d', 'next' => 'Bronze'],
        'bronze'    => ['label' => 'Bronze', 'icon' => 'fa-medal', 'color' => '#cd7f32', 'next' => 'Silver'],
        'silver'    => ['label' => 'Silver', 'icon' => 'fa-medal', 'color' => '#c0c0c0', 'next' => 'Gold'],
        'gold'      => ['label' => 'Gold', 'icon' => 'fa-medal', 'color' => '#ffd700', 'next' => 'Platinum'],
        'platinum'  => ['label' => 'Platinum', 'icon' => 'fa-crown', 'color' => '#e5e4e2', 'next' => 'Diamond'],
        'diamond'   => ['label' => 'Diamond', 'icon' => 'fa-gem', 'color' => '#b9f2ff', 'next' => null],
        // HybridEngine ranks
        'site_manager' => ['label' => 'Site Manager', 'icon' => 'fa-building', 'color' => '#4f46e5', 'next' => null],
    ];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                error_log('[RankPromotionNotificationService] DB init failed: ' . $e->getMessage());
                $pdo = null;
            }
        }
        $this->db = $pdo;
    }

    /**
     * Notify an agent about their rank promotion.
     *
     * @param int $userId Agent's user ID
     * @param string $oldRank Previous rank
     * @param string $newRank New rank
     * @param array $metadata Additional data (gbv, team_size, etc.)
     * @return array ['success' => bool, 'channels' => []]
     */
    public function notifyPromotion(int $userId, string $oldRank, string $newRank, array $metadata = []): array
    {
        if (!$this->db || $userId <= 0) {
            return ['success' => false, 'error' => 'Invalid input'];
        }

        // Get user details
        $user = $this->getUser($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        $oldInfo = self::RANK_INFO[$oldRank] ?? ['label' => $oldRank];
        $newInfo = self::RANK_INFO[$newRank] ?? ['label' => $newRank];

        $channels = [];

        // 1. In-app notification (always)
        $channels['in_app'] = $this->sendInAppNotification($userId, $oldRank, $newRank, $oldInfo, $newInfo, $metadata);

        // 2. Email
        $channels['email'] = $this->sendEmailNotification($user, $oldRank, $newRank, $oldInfo, $newInfo, $metadata);

        // 3. SMS (if phone available)
        if (!empty($user['phone'])) {
            $channels['sms'] = $this->sendSmsNotification($user, $oldRank, $newRank, $newInfo);
        }

        // 4. Log to rank history
        $this->logRankHistory($userId, $oldRank, $newRank, $metadata);

        return [
            'success' => true,
            'user_id' => $userId,
            'old_rank' => $oldRank,
            'new_rank' => $newRank,
            'channels' => $channels,
        ];
    }

    /**
     * Send in-app notification.
     */
    private function sendInAppNotification(int $userId, string $oldRank, string $newRank, array $oldInfo, array $newInfo, array $metadata): bool
    {
        try {
            $title = "Congratulations! You've been promoted to {$newInfo['label']}!";
            $message = "You've achieved the rank of {$newInfo['label']}. ";
            if (!empty($metadata['gbv'])) {
                $message .= "Your GBV of ₹" . number_format($metadata['gbv']) . " has earned you this promotion. ";
            }
            if (!empty($newInfo['next'])) {
                $message .= "Keep going to reach {$newInfo['next']}!";
            }

            $this->db->prepare("
                INSERT INTO realtime_notifications 
                (channel_name, user_id, event_type, payload, created_at)
                VALUES ('user_{$userId}_notifications', ?, 'rank_promotion', ?, NOW())
            ")->execute([
                $userId,
                json_encode([
                    'title' => $title,
                    'message' => $message,
                    'old_rank' => $oldRank,
                    'new_rank' => $newRank,
                    'icon' => $newInfo['icon'],
                    'color' => $newInfo['color'],
                    'metadata' => $metadata,
                ]),
            ]);

            return true;
        } catch (Exception $e) {
            error_log('[RankPromotionNotificationService] in-app failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification.
     */
    private function sendEmailNotification(array $user, string $oldRank, string $newRank, array $oldInfo, array $newInfo, array $metadata): bool
    {
        try {
            $name = $user['name'] ?? 'Agent';
            $email = $user['email'] ?? '';
            if (empty($email)) {
                return false;
            }

            $subject = "APS Dream Home - Rank Promotion to {$newInfo['label']}!";

            $gbvLine = '';
            if (!empty($metadata['gbv'])) {
                $gbvLine = "<p style='font-size:16px;color:#555;'>Your Group Business Volume: <strong style='color:#4f46e5;'>₹" . number_format($metadata['gbv']) . "</strong></p>";
            }

            $nextLine = '';
            if (!empty($newInfo['next'])) {
                $nextLine = "<p style='color:#555;'>Keep up the great work! The next rank to achieve is <strong>{$newInfo['next']}</strong>.</p>";
            }

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;'>
                <div style='background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;padding:30px;border-radius:12px 12px 0 0;text-align:center;'>
                    <h1 style='margin:0;font-size:24px;'>🎉 Rank Promotion!</h1>
                </div>
                <div style='background:#f8fafc;padding:30px;border:1px solid #e2e8f0;border-radius:0 0 12px 12px;'>
                    <p style='font-size:16px;color:#333;'>Dear <strong>{$name}</strong>,</p>
                    <p style='font-size:16px;color:#333;'>We're thrilled to inform you that you've been promoted!</p>
                    <div style='text-align:center;margin:20px 0;'>
                        <div style='display:inline-block;background:{$oldInfo['color']};color:white;padding:8px 16px;border-radius:20px;font-size:14px;'>{$oldInfo['label']}</div>
                        <span style='margin:0 10px;font-size:20px;'>→</span>
                        <div style='display:inline-block;background:{$newInfo['color']};color:white;padding:8px 16px;border-radius:20px;font-size:14px;'>{$newInfo['label']}</div>
                    </div>
                    {$gbvLine}
                    {$nextLine}
                    <hr style='border:none;border-top:1px solid #e2e8f0;margin:20px 0;'>
                    <p style='font-size:12px;color:#94a3b8;text-align:center;'>APS Dream Home — Empowering Real Estate Excellence</p>
                </div>
            </div>";

            // Log to email queue
            $this->db->prepare("
                INSERT INTO email_queue 
                (recipient_email, subject, body_html, status, scheduled_at, created_at)
                VALUES (?, ?, ?, 'pending', NOW(), NOW())
            ")->execute([$email, $subject, $html]);

            return true;
        } catch (Exception $e) {
            error_log('[RankPromotionNotificationService] email failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS notification.
     */
    private function sendSmsNotification(array $user, string $oldRank, string $newRank, array $newInfo): bool
    {
        try {
            $phone = $user['phone'];
            $name = $user['name'] ?? 'Agent';
            $message = "Hi {$name}! Congratulations! You've been promoted to {$newInfo['label']} rank at APS Dream Home. Keep up the excellent work!";

            $this->db->prepare("
                INSERT INTO sms_queue
                (recipient_phone, message, status, scheduled_at, created_at)
                VALUES (?, ?, 'pending', NOW(), NOW())
            ")->execute([$phone, $message]);

            return true;
        } catch (Exception $e) {
            error_log('[RankPromotionNotificationService] sms failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log rank promotion to history table.
     */
    private function logRankHistory(int $userId, string $oldRank, string $newRank, array $metadata): void
    {
        try {
            $this->db->prepare("
                INSERT INTO mlm_rank_history
                (user_id, old_rank, new_rank, promoted_at, gbv_at_promotion, team_size, notes)
                VALUES (?, ?, ?, NOW(), ?, ?, ?)
            ")->execute([
                $userId,
                $oldRank,
                $newRank,
                $metadata['gbv'] ?? 0,
                $metadata['team_size'] ?? 0,
                json_encode($metadata),
            ]);

            // Update current rank in mlm_profiles
            $this->db->prepare("
                UPDATE mlm_profiles SET current_rank = ?, updated_at = NOW() WHERE user_id = ?
            ")->execute([$newRank, $userId]);

        } catch (Exception $e) {
            error_log('[RankPromotionNotificationService] logRankHistory: ' . $e->getMessage());
        }
    }

    /**
     * Get user details.
     */
    private function getUser(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, phone FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Batch notify multiple promotions.
     */
    public function notifyBatch(array $promotions): array
    {
        $results = [];
        foreach ($promotions as $p) {
            $userId = (int)($p['user_id'] ?? 0);
            $oldRank = $p['old_rank'] ?? 'associate';
            $newRank = $p['new_rank'] ?? '';
            if ($userId > 0 && !empty($newRank)) {
                $results[] = $this->notifyPromotion($userId, $oldRank, $newRank, $p['metadata'] ?? []);
            }
        }
        return $results;
    }
}
