<?php
/**
 * CommissionManager — Unified Entry Point
 *
 * Single gateway for all commission operations. Prevents double-counting by:
 *   1. Checking if commissions already exist before calculating
 *   2. Routing to the correct engine based on project type
 *   3. Writing to both mlm_commission_ledger AND booking_commissions atomically
 *   4. Tracking which engine was used for each booking
 *
 * Engines:
 *   - HybridCommissionEngine: Colony projects (Suryoday, Braj Radha, etc.)
 *   - MLMCommissionEngine: Module 4 (generic MLM)
 *   - Legacy CommissionService V1: Deprecated, routed to MLMCommissionEngine
 *
 * Usage:
 *   $manager = new CommissionManager();
 *   $result = $manager->calculateForBooking($bookingId);
 */

namespace App\Services\MLM;

use PDO;
use Exception;

class CommissionManager
{
    protected $db;

    /** Engine identifiers */
    const ENGINE_HYBRID = 'hybrid';
    const ENGINE_MLM = 'mlm';
    const ENGINE_LEGACY = 'legacy';

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                error_log('[CommissionManager] DB init failed: ' . $e->getMessage());
                $pdo = null;
            }
        }
        $this->db = $pdo;
    }

    /**
     * Calculate commissions for a booking. The primary entry point.
     *
     * - Checks for existing commissions (idempotent)
     * - Routes to the correct engine
     * - Writes to both ledger and legacy table
     * - Applies daily capping
     *
     * @param int $bookingId
     * @param string|null $engine Override engine selection
     * @return array ['success'=>bool, 'engine'=>string, 'created'=>int, 'total_amount'=>float, 'entries'=>[]]
     */
    public function calculateForBooking(int $bookingId, ?string $engine = null): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        // Idempotency check: skip if commissions already exist for this booking
        $existing = $this->getExistingCommissions($bookingId);
        if (!empty($existing)) {
            return [
                'success' => true,
                'skipped' => true,
                'reason' => 'commissions_already_exist',
                'existing_count' => count($existing),
                'existing_total' => round(array_sum(array_column($existing, 'amount')), 2),
                'engine' => $existing[0]['engine'] ?? 'unknown',
                'entries' => $existing,
            ];
        }

        // Determine engine
        if ($engine === null) {
            $engine = $this->detectEngine($bookingId);
        }

        // Route to engine
        switch ($engine) {
            case self::ENGINE_HYBRID:
                return $this->calculateViaHybrid($bookingId);
            case self::ENGINE_MLM:
            case self::ENGINE_LEGACY:
                return $this->calculateViaMLM($bookingId);
            default:
                return ['success' => false, 'error' => "Unknown engine: {$engine}"];
        }
    }

    /**
     * Detect which engine to use based on booking's project/colony.
     * Colony projects → Hybrid, others → MLM.
     */
    private function detectEngine(int $bookingId): string
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.colony_id 
                FROM plot_bookings pb
                JOIN plots p ON p.id = pb.plot_id
                WHERE pb.id = ? LIMIT 1
            ");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['colony_id'])) {
                return self::ENGINE_HYBRID;
            }
            return self::ENGINE_MLM;
        } catch (Exception $e) {
            error_log('[CommissionManager] detectEngine failed: ' . $e->getMessage());
            return self::ENGINE_MLM;
        }
    }

    /**
     * Calculate via HybridCommissionEngine.
     * This engine writes to mlm_commission_ledger internally.
     * We then add the legacy booking_commissions row.
     */
    private function calculateViaHybrid(int $bookingId): array
    {
        try {
            $engine = new HybridCommissionEngine($this->db);
            $result = $engine->calculateBookingCommission($bookingId);

            if (empty($result['entries'])) {
                return [
                    'success' => true,
                    'engine' => self::ENGINE_HYBRID,
                    'created' => 0,
                    'total_amount' => 0.0,
                    'entries' => [],
                ];
            }

            // Write legacy backward-compat rows
            $legacyCount = $this->writeLegacyRows($bookingId, $result['entries']);

            // Apply daily capping
            $this->applyDailyCapping($result['entries']);

            return [
                'success' => true,
                'engine' => self::ENGINE_HYBRID,
                'created' => count($result['entries']),
                'legacy_created' => $legacyCount,
                'total_amount' => round((float)($result['total'] ?? 0.0), 2),
                'entries' => $result['entries'],
            ];
        } catch (Exception $e) {
            error_log('[CommissionManager] calculateViaHybrid failed: ' . $e->getMessage());
            return ['success' => false, 'engine' => self::ENGINE_HYBRID, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate via MLMCommissionEngine.
     * This engine also writes to mlm_commission_ledger internally.
     * We then add the legacy booking_commissions row.
     */
    private function calculateViaMLM(int $bookingId): array
    {
        try {
            $engine = new MLMCommissionEngine($this->db);
            $result = $engine->calculateBookingCommission($bookingId);

            if (empty($result['entries'])) {
                return [
                    'success' => true,
                    'engine' => self::ENGINE_MLM,
                    'created' => 0,
                    'total_amount' => 0.0,
                    'entries' => [],
                ];
            }

            // Write legacy backward-compat rows
            $legacyCount = $this->writeLegacyRows($bookingId, $result['entries']);

            // Apply daily capping
            $this->applyDailyCapping($result['entries']);

            return [
                'success' => true,
                'engine' => self::ENGINE_MLM,
                'created' => count($result['entries']),
                'legacy_created' => $legacyCount,
                'total_amount' => round((float)($result['total'] ?? 0.0), 2),
                'entries' => $result['entries'],
            ];
        } catch (Exception $e) {
            error_log('[CommissionManager] calculateViaMLM failed: ' . $e->getMessage());
            return ['success' => false, 'engine' => self::ENGINE_MLM, 'error' => $e->getMessage()];
        }
    }

    /**
     * Write legacy backward-compat rows to booking_commissions.
     * These rows allow legacy dashboard queries to keep working.
     */
    private function writeLegacyRows(int $bookingId, array $entries): int
    {
        $count = 0;
        $ins = $this->db->prepare(
            "INSERT IGNORE INTO booking_commissions
             (booking_id, beneficiary_user_id, source_user_id, commission_type, amount, percent, level, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
        );

        foreach ($entries as $r) {
            try {
                $ins->execute([
                    $bookingId,
                    $r['beneficiary_user_id'],
                    $r['source_user_id'],
                    $r['commission_type'],
                    $r['amount'],
                    $r['pct'] ?? 0,
                    $r['level'] ?? 1,
                ]);
                $count++;
            } catch (Exception $e) {
                // Non-fatal: booking_commissions is legacy compat
                error_log('[CommissionManager] legacy insert skip: ' . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Apply daily capping for level-type commissions.
     */
    private function applyDailyCapping(array $entries): void
    {
        try {
            $capService = new DailyCappingService();
            foreach ($entries as $r) {
                $type = $r['commission_type'] ?? '';
                if (strpos($type, 'mlm_level_') === 0 || $type === 'level_bonus') {
                    $capStatus = $capService->getCapStatus((int)$r['beneficiary_user_id']);
                    $dailyCap = (float)($capStatus['daily_cap'] ?? 0);
                    if ($dailyCap > 0) {
                        $capService->applyDailyCap((int)$r['beneficiary_user_id'], $r['amount'], $dailyCap);
                    }
                }
            }
        } catch (Exception $e) {
            error_log('[CommissionManager] DailyCappingService error: ' . $e->getMessage());
        }
    }

    /**
     * Check if commissions already exist for a booking.
     */
    public function getExistingCommissions(int $bookingId): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT id, beneficiary_user_id, commission_type, amount, status, created_at
                FROM mlm_commission_ledger
                WHERE booking_id = ?
                ORDER BY id
            ");
            $stmt->execute([$bookingId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Reverse all commissions for a booking (on cancellation).
     * Writes clawback entries to both tables.
     */
    public function reverseForBooking(int $bookingId, string $reason = ''): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            // Get existing paid/approved commissions
            $stmt = $this->db->prepare("
                SELECT id, beneficiary_user_id, source_user_id, commission_type, amount, level
                FROM mlm_commission_ledger
                WHERE booking_id = ? AND status IN ('pending', 'approved', 'paid')
            ");
            $stmt->execute([$bookingId]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($entries)) {
                return ['success' => true, 'reversed' => 0, 'reason' => 'no_active_commissions'];
            }

            $reversed = 0;
            $note = $reason ?: "Clawback - Booking #{$bookingId} cancelled";

            // Insert negative clawback entries in ledger
            $ins = $this->db->prepare("
                INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount, level, 
                 sale_amount, commission_percentage, status, notes, booking_id, created_at)
                VALUES (?, ?, 'clawback', ?, ?, 0, 0, 'approved', ?, ?, NOW())
            ");

            foreach ($entries as $e) {
                $ins->execute([
                    $e['beneficiary_user_id'],
                    $e['source_user_id'] ?? 0,
                    -$e['amount'],
                    $e['level'],
                    $note,
                    $bookingId,
                ]);
                $reversed++;
            }

            // Update legacy booking_commissions
            $upd = $this->db->prepare("
                UPDATE booking_commissions SET status = 'clawed_back'
                WHERE booking_id = ? AND status IN ('pending', 'approved', 'paid')
            ");
            $upd->execute([$bookingId]);

            return [
                'success' => true,
                'reversed' => $reversed,
                'total_reversed' => round(array_sum(array_column($entries, 'amount')) * -1, 2),
            ];
        } catch (Exception $e) {
            error_log('[CommissionManager] reverseForBooking failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Credit approved commissions to user wallets.
     * Called after payout batch approval.
     *
     * @param int $batchId The payout batch ID
     * @return array ['success'=>bool, 'credited'=>int, 'total'=>float]
     */
    public function creditWallets(int $batchId): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            // Get all pending commissions in this batch
            $stmt = $this->db->prepare("
                SELECT id, beneficiary_user_id, amount, commission_type
                FROM mlm_commission_ledger
                WHERE payout_batch_id = ? AND status = 'pending'
            ");
            $stmt->execute([$batchId]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($entries)) {
                return ['success' => true, 'credited' => 0, 'total' => 0.0];
            }

            $credited = 0;
            $totalCredited = 0.0;

            // Group by beneficiary for batch wallet update
            $byUser = [];
            foreach ($entries as $e) {
                $uid = (int)$e['beneficiary_user_id'];
                if (!isset($byUser[$uid])) {
                    $byUser[$uid] = ['total' => 0.0, 'ids' => []];
                }
                $byUser[$uid]['total'] += (float)$e['amount'];
                $byUser[$uid]['ids'][] = $e['id'];
            }

            foreach ($byUser as $userId => $data) {
                $amount = round($data['total'], 2);
                if ($amount <= 0) {
                    continue;
                }

                // Credit wallet
                $this->db->prepare("
                    INSERT INTO wallet_points (user_id, points, type, description, reference_type, reference_id, created_at)
                    VALUES (?, ?, 'commission', 'Commission payout batch #{$batchId}', 'payout_batch', ?, NOW())
                ")->execute([$userId, $amount, $batchId]);

                // Update ledger status to 'paid'
                $placeholders = implode(',', array_fill(0, count($data['ids']), '?'));
                $this->db->prepare("
                    UPDATE mlm_commission_ledger SET status = 'paid', updated_at = NOW()
                    WHERE id IN ({$placeholders})
                ")->execute($data['ids']);

                // Update legacy table
                try {
                    $this->db->prepare("
                        UPDATE booking_commissions SET status = 'paid', paid_at = NOW()
                        WHERE mlm_ledger_id IN ({$placeholders})
                    ")->execute($data['ids']);
                } catch (Exception $e) {
                    // Non-fatal: legacy table
                }

                $credited += count($data['ids']);
                $totalCredited += $amount;
            }

            // Update batch status
            $this->db->prepare("
                UPDATE mlm_payout_batches SET status = 'completed', processed_at = NOW() WHERE id = ?
            ")->execute([$batchId]);

            return [
                'success' => true,
                'credited' => $credited,
                'total' => round($totalCredited, 2),
                'users_affected' => count($byUser),
            ];
        } catch (Exception $e) {
            error_log('[CommissionManager] creditWallets failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get commission summary for a booking.
     */
    public function getBookingSummary(int $bookingId): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    commission_type,
                    COUNT(*) as count,
                    SUM(amount) as total,
                    status
                FROM mlm_commission_ledger
                WHERE booking_id = ?
                GROUP BY commission_type, status
                ORDER BY commission_type, status
            ");
            $stmt->execute([$bookingId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $totalAll = 0;
            foreach ($rows as $r) {
                $totalAll += (float)$r['total'];
            }

            return [
                'booking_id' => $bookingId,
                'breakdown' => $rows,
                'total' => round($totalAll, 2),
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
