<?php
/**
 * CommissionReconciliationService
 *
 * Daily audit service that detects orphaned entries, discrepancies,
 * and integrity issues in mlm_commission_ledger (single source of truth).
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
            'orphaned_ledger' => [],
            'orphaned_ledger_no_booking' => [],
            'missing_beneficiary' => [],
            'negative_entries' => [],
            'double_counted_bookings' => [],
            'orphaned_legacy' => [],
            'amount_mismatches' => [],
            'summary' => [],
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        try {
            // 1. Row counts
            $findings['ledger_total'] = $this->countRows('mlm_commission_ledger');
            $findings['booking_comm_total'] = (int)$this->db->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE booking_id IS NOT NULL")->fetchColumn();
            $findings['legacy_comm_total'] = 0;

            // 2. Find ledger entries with no matching booking
            $findings['orphaned_ledger_no_booking'] = $this->findOrphanedLedgerNoBooking();

            // 3. Missing beneficiary users
            $findings['missing_beneficiary'] = $this->findMissingBeneficiaries();

            // 4. Negative amount entries (potential clawbacks without positive counterpart)
            $findings['negative_entries'] = $this->findNegativeEntries();

            // 5. Double-counted bookings (same commission written twice for same booking+beneficiary)
            $findings['double_counted_bookings'] = $this->findDoubleCountedBookings();

            // 6. Amount mismatches (negative ledger entries with no booking_id)
            $findings['amount_mismatches'] = $this->findAmountMismatches();

            // 7. Build summary
            $findings['summary'] = $this->buildSummary($findings);
            $findings['summary']['status'] = $findings['summary']['health'];

        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] reconcile() failed: ' . $e->getMessage());
            $findings['error'] = $e->getMessage();
        }

        return $findings;
    }

    /**
     * Find ledger entries with no matching plot_booking.
     */
    private function findOrphanedLedgerNoBooking(): array
    {
        $sql = "
            SELECT l.id, l.booking_id, l.beneficiary_user_id, l.commission_type, l.amount, l.status
            FROM mlm_commission_ledger l
            LEFT JOIN plot_bookings pb ON pb.id = l.booking_id
            WHERE l.booking_id IS NOT NULL AND pb.id IS NULL
            ORDER BY l.created_at DESC
            LIMIT 50
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findOrphanedLedgerNoBooking: ' . $e->getMessage());
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
     * Auto-heal: no-op now that legacy table is removed.
     * Kept for backward compatibility.
     */
    public function autoHealStatusMismatches(): int
    {
        return 0;
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
            $ledger = $this->countRows('mlm_commission_ledger');

            $pending = (int)$this->db->query(
                "SELECT COUNT(*) FROM mlm_commission_ledger WHERE status = 'pending'"
            )->fetchColumn();

            $ledgerAmount = (float)$this->db->query(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE status = 'paid'"
            )->fetchColumn();

            $clawbacks = (int)$this->db->query(
                "SELECT COUNT(*) FROM mlm_commission_ledger WHERE commission_type = 'clawback'"
            )->fetchColumn();

            return [
                'healthy' => true,
                'ledger_count' => $ledger,
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

        if (!empty($f['orphaned_ledger_no_booking'])) {
            $issues += count($f['orphaned_ledger_no_booking']);
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

    /**
     * Detect bookings where commissions were written more than once.
     * This catches scenarios where both HybridCommissionEngine and MLMCommissionEngine
     * wrote entries for the same booking_id (should be prevented by CommissionManager idempotency).
     */
    private function findDoubleCountedBookings(): array
    {
        $sql = "
            SELECT 
                booking_id,
                COUNT(*) as total_entries,
                COUNT(DISTINCT commission_type) as distinct_types,
                SUM(amount) as total_amount
            FROM mlm_commission_ledger
            WHERE booking_id IS NOT NULL
              AND status NOT IN ('cancelled', 'clawed_back', 'clawback')
            GROUP BY booking_id
            HAVING COUNT(*) > 10
            ORDER BY total_entries DESC
            LIMIT 20
        ";
        try {
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Flag only bookings that appear to have been processed twice
            // (same commission_type appears more than once for same booking+beneficiary)
            $doubles = [];
            $dupSql = "
                SELECT booking_id, beneficiary_user_id, commission_type, COUNT(*) as cnt
                FROM mlm_commission_ledger
                WHERE booking_id IS NOT NULL
                  AND status NOT IN ('cancelled', 'clawed_back', 'clawback')
                GROUP BY booking_id, beneficiary_user_id, commission_type
                HAVING COUNT(*) > 1
                LIMIT 20
            ";
            $dupStmt = $this->db->query($dupSql);
            $doubles = $dupStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            return $doubles;
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findDoubleCountedBookings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find ledger entries that have no booking_id — these are bonus/salary/etc rows
     * that aren't tied to a specific booking. Not necessarily errors, but worth auditing.
     */
    private function findAmountMismatches(): array
    {
        $sql = "
            SELECT l.id, l.beneficiary_user_id, l.commission_type, l.amount, l.status, l.created_at
            FROM mlm_commission_ledger l
            WHERE l.booking_id IS NULL
              AND l.amount < 0
            ORDER BY l.created_at DESC
            LIMIT 50
        ";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[CommissionReconciliationService] findAmountMismatches: ' . $e->getMessage());
            return [];
        }
    }
}
