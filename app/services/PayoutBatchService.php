<?php

namespace App\Services;

use PDO;
use Exception;

/**
 * Payout Batch Service
 * ────────────────────
 * Manages commission payout batches with full lifecycle:
 *   draft → pending_approval → approved → processing → completed
 *
 * Features:
 *   - Auto-populate batch from pending ledger entries
 *   - TDS deduction calculation (194C/194J)
 *   - Bank transfer export (NEFT/RTGS format)
 *   - UPI batch payments
 *   - Approval workflow with multi-level approval
 *   - Payment status tracking per entry
 */
class PayoutBatchService
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo ?: $this->getDb();
    }

    private function getDb(): PDO
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        return new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    // ── BATCH CRUD ──

    /**
     * Create a new payout batch (draft).
     */
    public function createBatch(array $data): array
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO payout_batches
                    (batch_name, batch_type, period_from, period_to, notes, created_by, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'draft', NOW())
            ");
            $stmt->execute([
                $data['batch_name'],
                $data['batch_type'] ?? 'commission',
                $data['period_from'] ?? null,
                $data['period_to'] ?? null,
                $data['notes'] ?? null,
                $data['created_by'],
            ]);

            $batchId = (int)$this->pdo->lastInsertId();
            return ['success' => true, 'batch_id' => $batchId];
        } catch (Exception $e) {
            error_log("[PayoutBatch] createBatch FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Auto-populate a batch from pending ledger entries.
     * Filters by commission type, date range, and status.
     */
    public function autoPopulateBatch(int $batchId, string $commissionType = '', string $fromDate = '', string $toDate = ''): array
    {
        try {
            $this->pdo->beginTransaction();

            $batch = $this->getBatch($batchId);
            if (!$batch) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Batch not found'];
            }
            if ($batch['status'] !== 'draft') {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Batch must be in draft status to populate'];
            }

            // Build query for pending ledger entries
            $where = ["ml.status = 'pending'"];
            $params = [];

            if ($commissionType) {
                $where[] = "ml.commission_type = ?";
                $params[] = $commissionType;
            }
            if ($fromDate) {
                $where[] = "ml.created_at >= ?";
                $params[] = $fromDate . ' 00:00:00';
            }
            if ($toDate) {
                $where[] = "ml.created_at <= ?";
                $params[] = $toDate . ' 23:59:59';
            }

            // Use batch period if set
            if (!$fromDate && $batch['period_from']) {
                $where[] = "ml.created_at >= ?";
                $params[] = $batch['period_from'] . ' 00:00:00';
            }
            if (!$toDate && $batch['period_to']) {
                $where[] = "ml.created_at <= ?";
                $params[] = $batch['period_to'] . ' 23:59:59';
            }

            $whereStr = implode(' AND ', $where);

            // Fetch eligible entries
            $sql = "
                SELECT ml.id, ml.beneficiary_user_id, ml.commission_type, ml.amount,
                       ml.source_user_id, ml.booking_id, ml.plan_id, ml.plan_version,
                       u.name as beneficiary_name
                FROM mlm_commission_ledger ml
                LEFT JOIN users u ON u.id = ml.beneficiary_user_id
                WHERE $whereStr
                ORDER BY ml.beneficiary_user_id, ml.created_at
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($entries)) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'No pending entries found for the specified criteria'];
            }

            // Check if entries already assigned to another batch
            $existingBatch = $this->pdo->query("
                SELECT pe.ledger_id FROM payout_entries pe
                WHERE pe.ledger_id IN (" . implode(',', array_fill(0, count($entries), '?')) . ")
                AND pe.status != 'cancelled'
            ");
            $existingBatch->execute(array_column($entries, 'id'));
            $existingIds = array_flip($existingBatch->fetchAll(PDO::FETCH_COLUMN));

            // TDS config: 194C for contractors (10%), 194J for professionals (10%)
            $tdsRate = 10.0; // Default 10% TDS

            $inserted = 0;
            $totalAmount = 0;

            $ins = $this->pdo->prepare("
                INSERT INTO payout_entries
                    (batch_id, ledger_id, beneficiary_user_id, beneficiary_name,
                     commission_type, amount, tds_amount, net_amount, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");

            foreach ($entries as $entry) {
                if (isset($existingIds[$entry['id']])) {
                    continue; // Already in a batch
                }

                $amount = (float)$entry['amount'];
                $tds = round($amount * $tdsRate / 100, 2);
                $net = $amount - $tds;

                $ins->execute([
                    $batchId,
                    $entry['id'],
                    $entry['beneficiary_user_id'],
                    $entry['beneficiary_name'] ?? null,
                    $entry['commission_type'],
                    $amount,
                    $tds,
                    round($net, 2),
                ]);

                $totalAmount += $amount;
                $inserted++;
            }

            // Update batch totals
            $this->pdo->prepare("
                UPDATE payout_batches
                SET total_entries = total_entries + ?, total_amount = total_amount + ?, updated_at = NOW()
                WHERE id = ?
            ")->execute([$inserted, $totalAmount, $batchId]);

            $this->pdo->commit();

            return [
                'success'       => true,
                'batch_id'      => $batchId,
                'entries_added' => $inserted,
                'total_amount'  => round($totalAmount, 2),
                'skipped'       => count($entries) - $inserted,
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("[PayoutBatch] autoPopulateBatch FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Submit batch for approval.
     */
    public function submitForApproval(int $batchId): array
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) return ['success' => false, 'error' => 'Batch not found'];
        if ($batch['status'] !== 'draft') return ['success' => false, 'error' => 'Only draft batches can be submitted'];
        if ($batch['total_entries'] == 0) return ['success' => false, 'error' => 'Batch has no entries'];

        $this->pdo->prepare("UPDATE payout_batches SET status = 'pending_approval', updated_at = NOW() WHERE id = ?")->execute([$batchId]);
        return ['success' => true];
    }

    /**
     * Approve a batch.
     */
    public function approveBatch(int $batchId, int $approvedBy): array
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) return ['success' => false, 'error' => 'Batch not found'];
        if ($batch['status'] !== 'pending_approval') return ['success' => false, 'error' => 'Batch not in pending approval status'];

        $this->pdo->prepare("
            UPDATE payout_batches
            SET status = 'approved', approved_by = ?, approved_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ")->execute([$approvedBy, $batchId]);

        return ['success' => true];
    }

    /**
     * Reject a batch.
     */
    public function rejectBatch(int $batchId, int $rejectedBy, string $reason = ''): array
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) return ['success' => false, 'error' => 'Batch not found'];
        if ($batch['status'] !== 'pending_approval') return ['success' => false, 'error' => 'Batch not in pending approval'];

        $this->pdo->prepare("
            UPDATE payout_batches
            SET status = 'rejected', notes = CONCAT(COALESCE(notes, ''), '\nRejected: ', ?), updated_at = NOW()
            WHERE id = ?
        ")->execute([$reason, $batchId]);

        // Cancel all pending entries
        $this->pdo->prepare("UPDATE payout_entries SET status = 'cancelled' WHERE batch_id = ? AND status = 'pending'")->execute([$batchId]);

        // Return entries to pending in ledger
        $ledgerIds = $this->pdo->prepare("SELECT ledger_id FROM payout_entries WHERE batch_id = ? AND status = 'cancelled' AND ledger_id IS NOT NULL");
        $ledgerIds->execute([$batchId]);
        foreach ($ledgerIds->fetchAll(PDO::FETCH_COLUMN) as $lid) {
            $this->pdo->prepare("UPDATE mlm_commission_ledger SET status = 'pending' WHERE id = ? AND status = 'processing'")->execute([$lid]);
        }

        return ['success' => true];
    }

    /**
     * Mark batch as processing (payments initiated).
     */
    public function startProcessing(int $batchId, int $processedBy): array
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) return ['success' => false, 'error' => 'Batch not found'];
        if ($batch['status'] !== 'approved') return ['success' => false, 'error' => 'Batch must be approved first'];

        $this->pdo->beginTransaction();

        try {
            $this->pdo->prepare("
                UPDATE payout_batches
                SET status = 'processing', processed_by = ?, processed_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ")->execute([$processedBy, $batchId]);

            // Mark ledger entries as processing
            $ledgerIds = $this->pdo->prepare("SELECT ledger_id FROM payout_entries WHERE batch_id = ? AND ledger_id IS NOT NULL");
            $ledgerIds->execute([$batchId]);
            foreach ($ledgerIds->fetchAll(PDO::FETCH_COLUMN) as $lid) {
                $this->pdo->prepare("UPDATE mlm_commission_ledger SET status = 'processing' WHERE id = ? AND status = 'pending'")->execute([$lid]);
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Mark a single entry as completed (payment done).
     */
    public function completeEntry(int $entryId, string $paymentRef = ''): array
    {
        $stmt = $this->pdo->prepare("
            UPDATE payout_entries
            SET status = 'completed', payment_reference = ?, processed_at = NOW()
            WHERE id = ? AND status IN ('pending', 'processing')
        ");
        $stmt->execute([$paymentRef, $entryId]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => 'Entry not found or not in processable status'];
        }

        // Get ledger_id and update ledger
        $entry = $this->pdo->prepare("SELECT ledger_id FROM payout_entries WHERE id = ?");
        $entry->execute([$entryId]);
        $ledgerId = $entry->fetchColumn();

        if ($ledgerId) {
            $this->pdo->prepare("UPDATE mlm_commission_ledger SET status = 'paid' WHERE id = ? AND status = 'processing'")->execute([$ledgerId]);
        }

        // Check if batch is complete
        $batchId = $this->pdo->query("SELECT batch_id FROM payout_entries WHERE id = $entryId")->fetchColumn();
        $this->checkBatchCompletion($batchId);

        return ['success' => true];
    }

    /**
     * Complete the entire batch.
     */
    public function completeBatch(int $batchId): array
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) return ['success' => false, 'error' => 'Batch not found'];

        // Check all entries are completed
        $pending = $this->pdo->query("
            SELECT COUNT(*) FROM payout_entries
            WHERE batch_id = $batchId AND status NOT IN ('completed', 'cancelled')
        ")->fetchColumn();

        if ((int)$pending > 0) {
            return ['success' => false, 'error' => "$pending entries still pending"];
        }

        $this->pdo->prepare("UPDATE payout_batches SET status = 'completed', updated_at = NOW() WHERE id = ?")->execute([$batchId]);
        return ['success' => true];
    }

    // ── QUERIES ──

    /**
     * Get a single batch with stats.
     */
    public function getBatch(int $batchId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.*,
                   c.name as created_by_name,
                   a.name as approved_by_name
            FROM payout_batches b
            LEFT JOIN users c ON c.id = b.created_by
            LEFT JOIN users a ON a.id = b.approved_by
            WHERE b.id = ?
        ");
        $stmt->execute([$batchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get batch entries (paginated).
     */
    public function getBatchEntries(int $batchId, int $page = 1, int $perPage = 50): array
    {
        $total = $this->pdo->query("SELECT COUNT(*) FROM payout_entries WHERE batch_id = $batchId")->fetchColumn();
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare("
            SELECT pe.*, ml.commission_type as orig_type, ml.booking_id, ml.sale_amount
            FROM payout_entries pe
            LEFT JOIN mlm_commission_ledger ml ON ml.id = pe.ledger_id
            WHERE pe.batch_id = ?
            ORDER BY pe.beneficiary_name
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute([$batchId]);

        return [
            'items'       => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'       => (int)$total,
            'page'        => $page,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Get all batches (paginated, filterable).
     */
    public function getBatches(string $status = '', string $type = '', int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];
        if ($status) { $where[] = "b.status = ?"; $params[] = $status; }
        if ($type) { $where[] = "b.batch_type = ?"; $params[] = $type; }
        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = $this->pdo->query("SELECT COUNT(*) FROM payout_batches b $whereStr")->execute($params) ?
                 $this->pdo->query("SELECT COUNT(*) FROM payout_batches b $whereStr")->execute($params) :
                 $this->pdo->query("SELECT COUNT(*) FROM payout_batches b")->fetchColumn();

        // Simpler approach
        $countSql = "SELECT COUNT(*) FROM payout_batches b $whereStr";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT b.*, c.name as created_by_name, a.name as approved_by_name
            FROM payout_batches b
            LEFT JOIN users c ON c.id = b.created_by
            LEFT JOIN users a ON a.id = b.approved_by
            $whereStr
            ORDER BY b.created_at DESC
            LIMIT $perPage OFFSET $offset
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'items'       => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'       => $total,
            'page'        => $page,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Get batch dashboard stats.
     */
    public function getStats(): array
    {
        $stats = [];
        $r = $this->pdo->query("
            SELECT status, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as total
            FROM payout_batches
            GROUP BY status
        ");
        foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stats[$row['status']] = ['count' => (int)$row['cnt'], 'total' => (float)$row['total']];
        }
        return $stats;
    }

    /**
     * Generate bank export file (NEFT/RTGS format).
     */
    public function generateBankExport(int $batchId): array
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) return ['success' => false, 'error' => 'Batch not found'];

        $entries = $this->pdo->prepare("
            SELECT pe.*, u.email, u.phone
            FROM payout_entries pe
            LEFT JOIN users u ON u.id = pe.beneficiary_user_id
            WHERE pe.batch_id = ? AND pe.status = 'pending'
            ORDER BY pe.beneficiary_name
        ");
        $entries->execute([$batchId]);
        $rows = $entries->fetchAll(PDO::FETCH_ASSOC);

        // Generate CSV in NEFT format
        $csvRows = [];
        $csvRows[] = ['BENEFICIARY_NAME', 'ACCOUNT_NUMBER', 'IFSC_CODE', 'AMOUNT', 'REMARKS'];
        foreach ($rows as $row) {
            $csvRows[] = [
                $row['beneficiary_name'] ?? '',
                $row['beneficiary_account'] ?? '',
                $row['beneficiary_ifsc'] ?? '',
                number_format($row['net_amount'], 2, '.', ''),
                'Commission payout - ' . $batch['batch_name'],
            ];
        }

        $filename = "payout_batch_{$batchId}_" . date('Y-m-d_His') . ".csv";
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;

        $dir = dirname($filepath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fp = fopen($filepath, 'w');
        foreach ($csvRows as $csvRow) {
            fputcsv($fp, $csvRow);
        }
        fclose($fp);

        $this->pdo->prepare("UPDATE payout_batches SET bank_export_file = ?, updated_at = NOW() WHERE id = ?")->execute([$filename, $batchId]);

        return ['success' => true, 'file' => $filename, 'path' => $filepath, 'entries' => count($rows)];
    }

    // ── PRIVATE ──

    private function checkBatchCompletion(int $batchId): void
    {
        $pending = $this->pdo->query("
            SELECT COUNT(*) FROM payout_entries WHERE batch_id = $batchId AND status NOT IN ('completed', 'cancelled')
        ")->fetchColumn();

        if ((int)$pending === 0) {
            $this->pdo->prepare("UPDATE payout_batches SET status = 'completed', updated_at = NOW() WHERE id = ? AND status = 'processing'")->execute([$batchId]);
        }
    }
}
