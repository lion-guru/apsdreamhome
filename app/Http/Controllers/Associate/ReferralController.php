<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateReferralController
 * Handles referral management
 */
class ReferralController extends BaseController
{
    use TenantAwareTrait;

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Referral dashboard
     */
    public function referral()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) {
                $params[] = TenantContext::getId();
            }

            // Get user's referral code
            $user = $db->fetchOne("SELECT referral_code FROM users WHERE id = ?{$tidSql} LIMIT 1", [$userId]);
            $referralCode = $user['referral_code'] ?? '';

            // Direct referrals
            $direct = $db->fetchAll("
                SELECT u.*, a.status as associate_status, mp.current_level
                FROM users u
                LEFT JOIN associates a ON a.user_id = u.id
                LEFT JOIN mlm_profiles mp ON mp.user_id = u.id
                WHERE u.referred_by = ?{$tidSql}
                ORDER BY u.created_at DESC
            ", $params) ?: [];

            // Referral stats
            $totalReferrals = count($direct);
            $activeReferrals = count(array_filter($direct, fn($d) => ($d['status'] ?? '') === 'active'));

            // Commission from referrals
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'direct_sale'{$tidSql}");
            $stmt->execute($params);
            $referralCommissions = (float)($stmt->fetchColumn() ?? 0);

            // Tier info
            $tier = $this->getReferralTier($totalReferrals);
            $nextTier = $this->getNextTier($totalReferrals);

            // Generate in-memory QR code data URI for the associate's share link (zero disk bloat)
            $qrDataUri = '';
            if ($referralCode !== '') {
                $shareUrl = (defined('BASE_URL') ? BASE_URL : '') . '/register?ref=' . urlencode($referralCode);
                $qrDataUri = $this->buildReferralQrDataUri($shareUrl);
            }

            $this->render('associate/referral', [
                'page_title' => 'My Referrals - Associate Portal',
                'page_description' => 'Manage your referral network',
                'referral_code' => $referralCode,
                'referral_count' => $totalReferrals,
                'referral_earnings' => $referralCommissions,
                'referred_users' => $direct,
                'direct_referrals' => $direct,
                'total_referrals' => $totalReferrals,
                'active_referrals' => $activeReferrals,
                'referral_commissions' => $referralCommissions,
                'qr_data_uri' => $qrDataUri,
                'tier' => $tier,
                'next_tier' => $nextTier,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateReferralController error: ' . $e->getMessage());
        }
    }

    /**
     * Get referral tier based on count
     */
    private function getReferralTier(int $count): array
    {
        $tier = ['name' => 'Bronze', 'bonus' => 500, 'per_booking' => 100, 'color' => '#cd7f32'];
        if ($count >= 50) {
            $tier = ['name' => 'Platinum', 'bonus' => 5000, 'per_booking' => 1000, 'color' => '#e5e7eb'];
        } elseif ($count >= 20) {
            $tier = ['name' => 'Gold', 'bonus' => 2500, 'per_booking' => 500, 'color' => '#fbbf24'];
        } elseif ($count >= 10) {
            $tier = ['name' => 'Silver', 'bonus' => 1000, 'per_booking' => 200, 'color' => '#9ca3af'];
        }
        return $tier;
    }

    /**
     * Build an in-memory PNG data URI QR code for a referral URL using BaconQrCode + GD.
     */
    private function buildReferralQrDataUri(string $text, int $size = 220): string
    {
        try {
            $encoderClass = 'BaconQrCode\\Encoder\\Encoder';
            $ecLevelClass = 'BaconQrCode\\Common\\ErrorCorrectionLevel';
            if (!class_exists($encoderClass)) {
                $autoload = (defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 4)) . '/vendor/autoload.php';
                if (file_exists($autoload)) {
                    require_once $autoload;
                }
            }
            if (!class_exists($encoderClass) || !class_exists($ecLevelClass) || !function_exists('imagecreatetruecolor')) {
                return '';
            }
            $qr = $encoderClass::encode($text, $ecLevelClass::M());
            $matrix = $qr->getMatrix();
            $modules = $matrix->getWidth();
            $margin = 2;
            $scale = max(2, (int)($size / ($modules + $margin * 2)));
            $imgSize = ($modules + $margin * 2) * $scale;
            $img = imagecreatetruecolor($imgSize, $imgSize);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 15, 23, 42);
            imagefill($img, 0, 0, $white);
            for ($y = 0; $y < $modules; $y++) {
                for ($x = 0; $x < $modules; $x++) {
                    if ($matrix->get($x, $y) === 1) {
                        imagefilledrectangle(
                            $img,
                            ($x + $margin) * $scale,
                            ($y + $margin) * $scale,
                            ($x + $margin + 1) * $scale - 1,
                            ($y + $margin + 1) * $scale - 1,
                            $black
                        );
                    }
                }
            }
            ob_start();
            imagepng($img);
            $png = ob_get_clean();
            imagedestroy($img);
            return $png ? ('data:image/png;base64,' . base64_encode($png)) : '';
        } catch (\Throwable $e) {
            error_log('AssociateReferralController QR error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get next tier info
     */
    private function getNextTier(int $count): ?array
    {
        $next = null;
        if ($count < 10) {
            $next = ['name' => 'Silver', 'need' => 10 - $count];
        } elseif ($count < 20) {
            $next = ['name' => 'Gold', 'need' => 20 - $count];
        } elseif ($count < 50) {
            $next = ['name' => 'Platinum', 'need' => 50 - $count];
        }
        return $next;
    }
}

