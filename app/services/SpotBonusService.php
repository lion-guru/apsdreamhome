<?php

namespace App\Services;

/**
 * Sunday Spot-Booking Bonus
 *
 * When a customer's token booking lands within 48 hours of a COMPLETED
 * site visit driven by an associate, the associate's wallet gets an
 * automatic flat bonus. Idempotent per booking, best-effort (never fails
 * the booking save).
 */
class SpotBonusService
{
    public const BONUS_AMOUNT = 5000.0;
    public const WINDOW_HOURS = 48;

    /** @var \PDO */
    protected $db;

    public function __construct($db = null)
    {
        if ($db instanceof \PDO) {
            $this->db = $db;
        } else {
            $this->db = \App\Core\Database\Database::getInstance()->getConnection();
        }
    }

    private function tid(): ?int
    {
        try {
            if (class_exists('\App\Core\Middleware\TenantContext') && method_exists('\App\Core\Middleware\TenantContext', 'isolationEnabled')) {
                if (\App\Core\Middleware\TenantContext::isolationEnabled()) {
                    return \App\Core\Middleware\TenantContext::tenantId();
                }
            }
        } catch (\Throwable $e) { /* single-tenant default */ }
        return null;
    }

    /**
     * Award the bonus if eligible. Returns ['awarded'=>bool, ...].
     */
    public function maybeAwardSpotBonus(int $bookingId, int $customerId, int $associateId, string $bookingNumber = ''): array
    {
        try {
            if ($bookingId <= 0 || $customerId <= 0 || $associateId <= 0) {
                return ['awarded' => false, 'reason' => 'missing associate/customer/booking'];
            }
            $tid = $this->tid();
            $tSql = $tid ? " AND tenant_id = {$tid}" : "";

            // Idempotency: one bonus per booking
            $dup = $this->db->prepare(
                "SELECT id FROM wallet_transactions
                 WHERE reference_type = 'spot_booking_bonus' AND reference_id = ?{$tSql} LIMIT 1"
            );
            $dup->execute([$bookingId]);
            if ($dup->fetchColumn()) {
                return ['awarded' => false, 'reason' => 'already awarded'];
            }

            // Resolve associate user
            $au = $this->db->prepare("SELECT user_id FROM associates WHERE id = ? LIMIT 1");
            $au->execute([$associateId]);
            $assocUserId = (int)($au->fetchColumn() ?: 0);
            if ($assocUserId <= 0) {
                return ['awarded' => false, 'reason' => 'associate has no user'];
            }

            // Customer phone for visit matching
            $cu = $this->db->prepare("SELECT phone FROM users WHERE id = ? LIMIT 1");
            $cu->execute([$customerId]);
            $custPhone = preg_replace('/[^0-9]/', '', (string)($cu->fetchColumn() ?: ''));

            // Completed visit in the last 48h: driven by this associate AND tied to this customer
            $vs = $this->db->prepare(
                "SELECT id FROM site_visits
                 WHERE status = 'completed'
                   AND (assigned_to = ? OR agent_id = ?)
                   AND (user_id = ? OR visitor_phone LIKE ? OR lead_id IN (SELECT id FROM leads WHERE phone LIKE ?))
                   AND COALESCE(completed_at, created_at) >= DATE_SUB(NOW(), INTERVAL " . (int)self::WINDOW_HOURS . " HOUR)
                 ORDER BY id DESC LIMIT 1"
            );
            $likePhone = '%' . substr($custPhone, -10) . '%';
            $vs->execute([$assocUserId, $assocUserId, $customerId, $likePhone, $likePhone]);
            $visitId = (int)($vs->fetchColumn() ?: 0);
            if ($visitId <= 0) {
                return ['awarded' => false, 'reason' => 'no qualifying visit in 48h'];
            }

            $wallet = new \App\Services\WalletService();
            $ok = $wallet->credit(
                $assocUserId,
                self::BONUS_AMOUNT,
                'bonus',
                "Sunday spot-booking bonus: token {$bookingNumber} within 48h of site visit #{$visitId}",
                $bookingId,
                'spot_booking_bonus'
            );
            return $ok
                ? ['awarded' => true, 'amount' => self::BONUS_AMOUNT, 'associate_user_id' => $assocUserId, 'visit_id' => $visitId]
                : ['awarded' => false, 'reason' => 'wallet credit failed'];
        } catch (\Throwable $e) {
            error_log('[SpotBonusService] ' . $e->getMessage());
            return ['awarded' => false, 'reason' => 'exception'];
        }
    }
}
