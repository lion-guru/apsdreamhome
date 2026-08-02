<?php
/**
 * CommissionManager — Unified Entry Point
 *
 * Single gateway for all commission operations. Prevents double-counting by:
 *   1. Checking if commissions already exist before calculating
 *   2. Routing to the correct engine based on project type
 *   3. Writing to mlm_commission_ledger (single source of truth)
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
use App\Services\HybridCommissionEngine;
use App\Traits\ServiceTenantTrait;

class CommissionManager
{
    use ServiceTenantTrait;

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
            WHERE pb.id = ?" . $this->tenantSql() . " LIMIT 1
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
     */
    private function calculateViaHybrid(int $bookingId): array
    {
        try {
            $engine = new HybridCommissionEngine($this->db);

            $bookingCtx = $this->resolveBookingContext($bookingId);
            if (!$bookingCtx) {
                return ['success' => false, 'engine' => self::ENGINE_HYBRID, 'error' => 'Booking not found or missing data'];
            }

            $result = $engine->processPipelineCommission(
                $bookingId,
                $bookingCtx['receipt_id'],
                $bookingCtx['amount'],
                $bookingCtx['agent_id']
            );

            $entries = $result['entries'] ?? [];

            if (empty($entries)) {
                return [
                    'success' => true,
                    'engine' => self::ENGINE_HYBRID,
                    'created' => 0,
                    'total_amount' => 0.0,
                    'entries' => [],
                ];
            }

            $legacyCount = $this->writeLegacyRows($bookingId, $entries);
            $this->applyDailyCapping($entries);

            return [
                'success' => true,
                'engine' => self::ENGINE_HYBRID,
                'created' => count($entries),
                'legacy_created' => $legacyCount,
                'total_amount' => round((float)($result['total'] ?? 0.0), 2),
                'entries' => $entries,
            ];
        } catch (Exception $e) {
            error_log('[CommissionManager] calculateViaHybrid failed: ' . $e->getMessage());
            return ['success' => false, 'engine' => self::ENGINE_HYBRID, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resolve booking context needed by HybridCommissionEngine::processPipelineCommission().
     *
     * @return array{receipt_id: int, amount: float, agent_id: int}|null
     */
    private function resolveBookingContext(int $bookingId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    pb.id,
                    pb.total_plot_value,
                    pb.booking_amount,
                    pb.agreement_value,
                    pb.associate_id,
                    a.user_id as agent_user_id
                FROM plot_bookings pb
                LEFT JOIN associates a ON a.id = pb.associate_id
                WHERE pb.id = ?" . $this->tenantSql() . " LIMIT 1
            ");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            $agentId = (int)($row['agent_user_id'] ?? $row['associate_id'] ?? 0);

            // ── FIX: Use ACTUAL payment received, not full plot value ──
            $paidStmt = $this->db->prepare("
                SELECT 
                    COALESCE((SELECT SUM(paid_amount) FROM booking_payment_schedules WHERE booking_id = ? AND status = 'paid'" . $this->tenantSql() . "), 0)
                    + ? AS total_received
            ");
            $paidStmt->execute([$bookingId, (float)$row['booking_amount']]);
            $amountReceived = (float)$paidStmt->fetchColumn();

            if ($amountReceived <= 0) {
                return null;
            }

            // Get the latest paid receipt_id for idempotency
            $receiptStmt = $this->db->prepare("
                SELECT id FROM booking_payment_schedules 
                WHERE booking_id = ? AND status = 'paid'" . $this->tenantSql() . " ORDER BY id DESC LIMIT 1
            ");
            $receiptStmt->execute([$bookingId]);
            $receiptRow = $receiptStmt->fetch(PDO::FETCH_ASSOC);
            $receiptId = $receiptRow ? (int)$receiptRow['id'] : 0;

            return [
                'receipt_id' => $receiptId,
                'amount'     => $amountReceived,
                'agent_id'   => $agentId,
            ];
        } catch (Exception $e) {
            error_log('[CommissionManager] resolveBookingContext failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate via MLMCommissionEngine.
     * This engine also writes to mlm_commission_ledger internally.
     * We then add the row to mlm_commission_ledger.
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
     * Legacy writeLegacyRows removed — mlm_commission_ledger is the single source of truth.
     */
    private function writeLegacyRows(int $bookingId, array $entries): int
    {
        return count($entries);
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
     * Only counts active commissions (not cancelled/clawed_back).
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
                WHERE booking_id = ? AND status NOT IN ('cancelled', 'clawed_back')" . $this->tenantSql() . "
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
                WHERE booking_id = ? AND status IN ('pending', 'approved', 'paid')" . $this->tenantSql() . "
            ");
            $stmt->execute([$bookingId]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($entries)) {
                return ['success' => true, 'reversed' => 0, 'reason' => 'no_active_commissions'];
            }

            $reversed = 0;
            $note = $reason ?: "Clawback - Booking #{$bookingId} cancelled";

            // Insert negative clawback entries in ledger
            $insertData = $this->tenantInsertData();
            $ins = $this->db->prepare("
                INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount, level, 
                 sale_amount, commission_percentage, status, notes, booking_id, created_at)
                VALUES (?, ?, 'clawback', ?, ?, 0, 0, 'approved', ?, ?, NOW())
            ");

            foreach ($entries as $e) {
                $params = [
                    $e['beneficiary_user_id'],
                    $e['source_user_id'] ?? 0,
                    -$e['amount'],
                    $e['level'],
                    $note,
                    $bookingId,
                ];
                $ins = $this->db->prepare("
                    INSERT INTO mlm_commission_ledger
                    (beneficiary_user_id, source_user_id, commission_type, amount, level, 
                     sale_amount, commission_percentage, status, notes, booking_id, created_at)
                    VALUES (?, ?, 'clawback', ?, ?, 0, 0, 'approved', ?, ?, NOW()" .
                    ($insertData ? ", " . implode(', ', array_keys($insertData)) : "") . ")
                ");
                $ins->execute(array_merge($params, array_values($insertData)));
                $reversed++;
            }

            // mlm_commission_ledger is the single source of truth — no legacy table to update

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
                WHERE payout_batch_id = ? AND status = 'pending'" . $this->tenantSql() . "
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

                // Credit user_wallets (monetary wallet, not gamification points)
                $walletExists = $this->db->prepare(
                    "SELECT id FROM user_wallets WHERE user_id = ?" . $this->tenantSql() . " LIMIT 1"
                );
                $walletExists->execute([$userId]);
                if ($walletExists->fetch()) {
                    $this->db->prepare("
                        UPDATE user_wallets SET balance = balance + ?, total_credited = total_credited + ? WHERE user_id = ?" . $this->tenantSql() . "
                    ")->execute([$amount, $amount, $userId]);
                } else {
                    $insertData = $this->tenantInsertData();
                    $this->db->prepare("
                        INSERT INTO user_wallets (user_id, user_type, balance, total_credited, created_at" .
                        ($insertData ? ", " . implode(', ', array_keys($insertData)) : "") . ")
                        VALUES (?, 'associate', ?, ?, NOW()" .
                        ($insertData ? ", " . implode(', ', array_fill(0, count($insertData), '?')) : "") . ")
                    ")->execute(array_merge([$userId, $amount, $amount], array_values($insertData)));
                }

                // Update ledger status to 'paid'
                $placeholders = implode(',', array_fill(0, count($data['ids']), '?'));
                $this->db->prepare("
                    UPDATE mlm_commission_ledger SET status = 'paid', updated_at = NOW()
                    WHERE id IN ({$placeholders})" . $this->tenantSql() . "
                ")->execute($data['ids']);

                // mlm_commission_ledger is the single source of truth — status already updated above

                $credited += count($data['ids']);
                $totalCredited += $amount;
            }

            // Update batch status
            $this->db->prepare("
                UPDATE mlm_payout_batches SET status = 'completed', processed_at = NOW() WHERE id = ?" . $this->tenantSql() . "
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
                WHERE booking_id = ?" . $this->tenantSql() . "
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
