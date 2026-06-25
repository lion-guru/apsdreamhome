<?php
/**
 * CommissionReconciliationService
 *
 * Daily audit service that detects double-counting, orphaned entries,
 * and discrepancies across the 3 commission tables:
 *   1. mlm_commission_ledger  (PRIMARY — HybridCommissionEngine + MLMCommissionEngine)
 *   2. booking_commissions    (LEGACY — BookingLifecycleService backward-compat)
 *   3. commissions            (OLDEST — CommissionService V1)
 *
 * Run via: daily cron or admin action
 */

namespace App\Services\MLM;

use PDO;
use Exception;

class CommissionReconciliationService
{
    protected $db;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                error_log('[CommissionReconciliationService] DB init failed: ' . $e->getMessage());
                $pdo = null;
            }
        }
        $this->db = $pdo;
    }

    /**
     * Run full reconciliation. Returns array with all findings.
     */
    public function reconcile(): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        $findings = [
            'ledger_total' => 0,
            'booking_comm_total' => 0,
            'legacy_comm_total' => 0,
            'orphaned_legacy' => [],
            'orphaned_ledger' => [],
            'amount_mismatches' => [],
            'double_counted_bookings' => [],
            'status_mismatches' => [],
            'missing_beneficiary' => [],
            'negative_entries' => [],
            'summary' => [],
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        try {
            // 1. Row counts
            $findings['ledger_total'] = $this->countRows('mlm_commission_ledger');
            $findings['booking_comm_total'] = $this->countRows('booking_commissions');
            $findings['legacy_comm_total'] = $this->countRows('commissions');

            // 2. Find bookings that have entries in BOTH mlm_commission_ledger AND booking_commissions
            $findings['double_counted_bookings'] = $this->findDoubleCountedBookings();

            // 3. Find orphaned booking_commissions (no matching ledger entry)
            $findings['orphaned_legacy'] = $this->findOrphanedLegacy();

            // 4. Find orphaned ledger entries (no matching booking)
            $findings['orphaned_ledger'] = $this->findOrphanedLedger();

            // 5. Amount mismatches between ledger and booking_commissions for same booking
            $findings['amount_mismatches'] = $this->findAmountMismatches();

            // 6. Status mismatches (e.g., ledger=paid but booking_comm=pending)
            $findings['status_mismatches'] = $this->findStatusMismatches();

            // 7. Missing beneficiary users
            $findings['missing_beneficiary'] = $this->findMissingBeneficiaries();

            // 8. Negative amount entries (potential clawbacks without positive counterpart)
            $findings['negative_entries'] = $this->findNegativeEntries();

            // 9. Build summary
            $findings['summary'] = $this->buildSummary($findings);

        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] reconcile() failed: ' . $e->getMessage());
            $findings['error'] = $e->getMessage();
        }

        return $findings;
    }

    /**
     * Find bookings that have entries in both mlm_commission_ledger and booking_commissions.
     * These are the "dual-written" bookings that need reconciliation.
     */
    private function findDoubleCountedBookings(): array
    {
        $sql = "
            SELECT 
                l.booking_id,
                COUNT(DISTINCT l.id) AS ledger_entries,
                COUNT(DISTINCT bc.id) AS legacy_entries,
                SUM(l.amount) AS ledger_amount,
                SUM(bc.amount) AS legacy_amount
            FROM mlm_commission_ledger l
            INNER JOIN booking_commissions bc 
                ON bc.booking_id = l.booking_id 
                AND bc.beneficiary_user_id = l.beneficiary_user_id
            WHERE l.booking_id IS NOT NULL
            GROUP BY l.booking_id
            HAVING ledger_entries > 0 AND legacy_entries > 0
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findDoubleCountedBookings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find booking_commissions rows that have no matching mlm_commission_ledger entry.
     */
    private function findOrphanedLegacy(): array
    {
        $sql = "
            SELECT bc.id, bc.booking_id, bc.beneficiary_user_id, bc.commission_type, bc.amount, bc.status
            FROM booking_commissions bc
            LEFT JOIN mlm_commission_ledger l 
                ON l.booking_id = bc.booking_id 
                AND l.beneficiary_user_id = bc.beneficiary_user_id
                AND l.commission_type = bc.commission_type
            WHERE l.id IS NULL
            ORDER BY bc.created_at DESC
            LIMIT 50
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findOrphanedLegacy: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find mlm_commission_ledger entries with no matching booking_commissions row.
     * These are engine-written entries without legacy backward-compat rows.
     */
    private function findOrphanedLedger(): array
    {
        $sql = "
            SELECT l.id, l.booking_id, l.beneficiary_user_id, l.commission_type, l.amount, l.status
            FROM mlm_commission_ledger l
            LEFT JOIN booking_commissions bc 
                ON bc.booking_id = l.booking_id 
                AND bc.beneficiary_user_id = l.beneficiary_user_id
                AND bc.commission_type = l.commission_type
            WHERE l.booking_id IS NOT NULL AND bc.id IS NULL
            ORDER BY l.created_at DESC
            LIMIT 50
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findOrphanedLedger: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find bookings where total amounts differ between ledger and booking_commissions.
     */
    private function findAmountMismatches(): array
    {
        $sql = "
            SELECT 
                l.booking_id,
                ROUND(SUM(l.amount), 2) AS ledger_total,
                ROUND(SUM(bc.amount), 2) AS legacy_total,
                ROUND(SUM(l.amount) - SUM(bc.amount), 2) AS difference
            FROM mlm_commission_ledger l
            INNER JOIN booking_commissions bc 
                ON bc.booking_id = l.booking_id
            WHERE l.booking_id IS NOT NULL
            GROUP BY l.booking_id
            HAVING ABS(ledger_total - legacy_total) > 0.01
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findAmountMismatches: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find entries where status differs between tables for the same booking+user.
     */
    private function findStatusMismatches(): array
    {
        $sql = "
            SELECT 
                l.booking_id,
                l.beneficiary_user_id,
                l.status AS ledger_status,
                bc.status AS legacy_status,
                l.amount AS ledger_amount,
                bc.amount AS legacy_amount
            FROM mlm_commission_ledger l
            INNER JOIN booking_commissions bc 
                ON bc.booking_id = l.booking_id 
                AND bc.beneficiary_user_id = l.beneficiary_user_id
            WHERE l.status != bc.status
            ORDER BY l.created_at DESC
            LIMIT 50
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findStatusMismatches: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find commission entries pointing to non-existent users.
     */
    private function findMissingBeneficiaries(): array
    {
        $sql = "
            SELECT l.id, l.beneficiary_user_id, l.commission_type, l.amount, l.booking_id
            FROM mlm_commission_ledger l
            LEFT JOIN users u ON u.id = l.beneficiary_user_id
            WHERE u.id IS NULL
            LIMIT 50
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findMissingBeneficiaries: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find negative entries (clawbacks) and check they have corresponding positive entries.
     */
    private function findNegativeEntries(): array
    {
        $sql = "
            SELECT l.id, l.booking_id, l.beneficiary_user_id, l.commission_type, l.amount, l.notes
            FROM mlm_commission_ledger l
            WHERE l.amount < 0
            ORDER BY l.created_at DESC
            LIMIT 50
        ";
        try {
            $stmt = $this->db->query($sql);
            $negatives = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // For each negative, check if there's a matching positive
            $results = [];
            $posStmt = $this->db->prepare("
                SELECT SUM(amount) AS positive_total 
                FROM mlm_commission_ledger 
                WHERE booking_id = ? AND beneficiary_user_id = ? AND commission_type = ? AND amount > 0
            ");
            foreach ($negatives as $neg) {
                $posStmt->execute([$neg['booking_id'], $neg['beneficiary_user_id'], $neg['commission_type']]);
                $pos = $posStmt->fetch(PDO::FETCH_ASSOC);
                $positiveTotal = (float)($pos['positive_total'] ?? 0);
                $results[] = [
                    'id' => $neg['id'],
                    'booking_id' => $neg['booking_id'],
                    'beneficiary_user_id' => $neg['beneficiary_user_id'],
                    'negative_amount' => $neg['amount'],
                    'positive_total' => $positiveTotal,
                    'fully_reversed' => abs($neg['amount']) <= $positiveTotal,
                    'notes' => $neg['notes'],
                ];
            }
            return $results;
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findNegativeEntries: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Auto-heal orphaned legacy entries by syncing status from ledger.
     * Returns count of healed entries.
     */
    public function autoHealStatusMismatches(): int
    {
        if (!$this->db) {
            return 0;
        }

        $sql = "
            UPDATE booking_commissions bc
            INNER JOIN mlm_commission_ledger l 
                ON l.booking_id = bc.booking_id 
                AND l.beneficiary_user_id = bc.beneficiary_user_id
                AND l.commission_type = bc.commission_type
            SET bc.status = l.status,
                bc.updated_at = NOW()
            WHERE l.status != bc.status
        ";
        try {
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
            if ($count > 0) {
                error_log("[CommissionReconciliationService] Auto-healed {$count} status mismatches");
            }
            return $count;
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] autoHealStatusMismatches: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get summary stats for admin dashboard.
     */
    public function getQuickStats(): array
    {
        if (!$this->db) {
            return ['healthy' => false];
        }

        try {
            // Count total in each table
            $ledger = $this->countRows('mlm_commission_ledger');
            $legacy = $this->countRows('booking_commissions');
            $oldest = $this->countRows('commissions');

            // Count pending in ledger
            $pending = (int)$this->db->query(
                "SELECT COUNT(*) FROM mlm_commission_ledger WHERE status = 'pending'"
            )->fetchColumn();

            // Total amounts
            $ledgerAmount = (float)$this->db->query(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE status = 'paid'"
            )->fetchColumn();

            // Clawbacks
            $clawbacks = (int)$this->db->query(
                "SELECT COUNT(*) FROM mlm_commission_ledger WHERE commission_type = 'clawback'"
            )->fetchColumn();

            return [
                'healthy' => true,
                'ledger_count' => $ledger,
                'legacy_count' => $legacy,
                'oldest_count' => $oldest,
                'pending_count' => $pending,
                'total_paid_amount' => round($ledgerAmount, 2),
                'clawback_count' => $clawbacks,
            ];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] getQuickStats: ' . $e->getMessage());
            return ['healthy' => false, 'error' => $e->getMessage()];
        }
    }

    private function countRows(string $table): int
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function buildSummary(array $f): array
    {
        $issues = 0;
        $warnings = 0;

        if (!empty($f['double_counted_bookings'])) {
            $warnings += count($f['double_counted_bookings']);
        }
        if (!empty($f['orphaned_legacy'])) {
            $issues += count($f['orphaned_legacy']);
        }
        if (!empty($f['amount_mismatches'])) {
            $issues += count($f['amount_mismatches']);
        }
        if (!empty($f['status_mismatches'])) {
            $warnings += count($f['status_mismatches']);
        }
        if (!empty($f['missing_beneficiary'])) {
            $issues += count($f['missing_beneficiary']);
        }

        $unreversedClawbacks = 0;
        foreach ($f['negative_entries'] as $ne) {
            if (!$ne['fully_reversed']) {
                $unreversedClawbacks++;
            }
        }
        $issues += $unreversedClawbacks;

        $health = 'healthy';
        if ($issues > 0) {
            $health = 'critical';
        } elseif ($warnings > 0) {
            $health = 'warning';
        }

        return [
            'health' => $health,
            'critical_issues' => $issues,
            'warnings' => $warnings,
            'unreversed_clawbacks' => $unreversedClawbacks,
        ];
    }
}
