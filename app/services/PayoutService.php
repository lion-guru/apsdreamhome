<?php

namespace App\Services;

use App\Core\Database;
use App\Services\NotificationService;
use Exception;
use PDO;
use PDOStatement;

/**
 * PayoutService
 * Handles creation and lifecycle of payout batches for MLM commissions.
 */

class PayoutService
{
    private PDO $conn;
    private NotificationService $notifier;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
        $this->notifier = new NotificationService();
    }

    /**
     * Generate a payout batch for approved commissions.
     *
     * $filters may include:
     *  - date_from, date_to (created_at window)
     *  - min_amount (minimum total commission per batch)
     *  - max_items (limit number of rows per batch)
     */
    public function createBatch(array $filters): array
    {
        $summary = [
            'success' => false,
            'message' => '',
            'batch_id' => null,
            'total_amount' => 0,
            'total_records' => 0,
        ];

        $this->conn->beginTransaction();

        try {
            $batchReference = $filters['batch_reference'] ?? ('MLM' . date('YmdHis'));
            $limit = isset($filters['max_items']) ? (int)$filters['max_items'] : null;
            $minAmount = isset($filters['min_amount']) ? (float)$filters['min_amount'] : 0;
            $requiredApprovals = max(1, (int) ($filters['required_approvals'] ?? 1));

            $sql = "SELECT id, beneficiary_user_id, amount
                    FROM mlm_commission_ledger
                    WHERE status = 'approved'";
            $params = [];

            if (!empty($filters['date_from'])) {
                $sql .= ' AND created_at >= ?';
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= ' AND created_at <= ?';
                $params[] = $filters['date_to'];
            }

            $sql .= ' ORDER BY created_at ASC';

            if ($limit) {
                $sql .= ' LIMIT ' . $limit;
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                $this->conn->rollBack();
                $summary['message'] = 'No approved commissions available for payout.';
                return $summary;
            }

            $totalAmount = array_reduce($rows, fn($carry, $row) => $carry + (float)$row['amount'], 0.0);
            $totalRecords = count($rows);

            if ($minAmount > 0 && $totalAmount < $minAmount) {
                $this->conn->rollBack();
                $summary['message'] = 'Total amount below minimum threshold.';
                return $summary;
            }

            $insertBatch = $this->conn->prepare(
                "INSERT INTO mlm_payout_batches (batch_reference, status, total_amount, total_records, required_approvals, created_at)
                 VALUES (?, 'pending_approval', ?, ?, ?, NOW())"
            );
            $insertBatch->execute([$batchReference, $totalAmount, $totalRecords, $requiredApprovals]);
            $batchId = (int)$this->conn->lastInsertId();

            $insertItem = $this->conn->prepare(
                "INSERT INTO mlm_payout_batch_items (batch_id, commission_id, beneficiary_user_id, amount, status, created_at)
                 VALUES (?, ?, ?, ?, 'pending', NOW())"
            );

            foreach ($rows as $row) {
                $insertItem->execute([
                    $batchId,
                    (int)$row['id'],
                    (int)$row['beneficiary_user_id'],
                    (float)$row['amount']
                ]);
            }

            $summary['success'] = true;
            $summary['message'] = 'Payout batch created successfully.';
            $summary['batch_id'] = $batchId;
            $summary['total_amount'] = $totalAmount;
            $summary['total_records'] = $totalRecords;
            $summary['required_approvals'] = $requiredApprovals;

            $this->conn->commit();

            $this->notifyAdmin(
                'New payout batch created',
                $this->buildBatchEmailBody('created', [
                    'batch_reference' => $batchReference,
                    'total_amount' => $totalAmount,
                    'total_records' => $totalRecords,
                ]),
                'payout_batch_created',
                [
                    'batch_id' => $batchId,
                    'total_amount' => $totalAmount,
                    'total_records' => $totalRecords,
                ]
            );
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $summary['message'] = 'Failed to create batch: ' . $e->getMessage();
        }

        return $summary;
    }

    public function approveBatch(int $batchId, int $approverUserId, string $decision = 'approved', ?string $notes = null): array
    {
        $decision = strtolower($decision);
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            return ['success' => false, 'message' => 'Invalid decision supplied.'];
        }

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, status, required_approvals, approval_count, approved_by_user_id, processed_notes
                 FROM mlm_payout_batches WHERE id = ? FOR UPDATE"
            );
            $stmt->execute([$batchId]);
            $batch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$batch) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Batch not found.'];
            }

            if ($batch['status'] !== 'pending_approval') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Batch is not awaiting approvals.'];
            }

            $insert = $this->conn->prepare(
                "INSERT INTO mlm_payout_batch_approvals (batch_id, approver_user_id, status, notes, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), updated_at = NOW()"
            );
            $insert->execute([$batchId, $approverUserId, $decision, $notes]);

            $countStmt = $this->conn->prepare(
                "SELECT
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count
                 FROM mlm_payout_batch_approvals
                 WHERE batch_id = ?"
            );
            $countStmt->execute([$batchId]);
            $counts = $countStmt->fetch(PDO::FETCH_ASSOC);

            $approvedCount = (int)($counts['approved_count'] ?? 0);

            $updateCount = $this->conn->prepare(
                'UPDATE mlm_payout_batches SET approval_count = ? WHERE id = ?'
            );
            $updateCount->execute([$approvedCount, $batchId]);

            $previousStatus = $batch['status'];
            $finalStatus = $previousStatus;

            if ($decision === 'rejected') {
                $finalStatus = 'cancelled';
                $reason = trim('Rejected by user #' . $approverUserId . ($notes ? ': ' . $notes : ''));
                $existingNotes = $batch['processed_notes'] ?? '';
                $processedNotes = trim($existingNotes ? ($existingNotes . PHP_EOL . $reason) : $reason);

                $updateStatus = $this->conn->prepare(
                    "UPDATE mlm_payout_batches SET status = 'cancelled', processed_notes = ? WHERE id = ?"
                );
                $updateStatus->execute([$processedNotes, $batchId]);
            } elseif ($approvedCount >= (int)$batch['required_approvals']) {
                $finalStatus = 'processing';
                $statusUpdate = $this->conn->prepare(
                    "UPDATE mlm_payout_batches
                     SET status = 'processing', approved_by_user_id = ?, approved_at = NOW()
                     WHERE id = ?"
                );
                $statusUpdate->execute([$approverUserId, $batchId]);

                $itemUpdate = $this->conn->prepare(
                    "UPDATE mlm_payout_batch_items SET status = 'approved' WHERE batch_id = ?"
                );
                $itemUpdate->execute([$batchId]);
            }

            $this->conn->commit();

            $summary = $this->getBatchSummary($batchId);
            $remaining = max(0, (int)($summary['required_approvals'] ?? 1) - (int)($summary['approval_count'] ?? 0));
            $message = 'Approval recorded.';

            if ($finalStatus === 'processing' && $previousStatus !== 'processing') {
                $message = 'Batch approved and moved to processing.';
                $this->notifyAdmin(
                    'Payout batch approved',
                    $this->buildBatchEmailBody('approved', $summary),
                    'payout_batch_approved',
                    ['batch_id' => $batchId]
                );
                $this->notifyFinance(
                    'Payout batch ready for disbursement',
                    $this->buildBatchEmailBody('processing', $summary ?? []),
                    'finance_batch_processing',
                    $summary ?? []
                );
            } elseif ($finalStatus === 'cancelled' && $previousStatus !== 'cancelled' && $decision === 'rejected') {
                $message = 'Batch rejected and marked as cancelled.';
                $this->notifyAdmin(
                    'Payout batch rejected',
                    $this->buildBatchEmailBody('cancelled', $summary),
                    'payout_batch_rejected',
                    ['batch_id' => $batchId]
                );
            } elseif ($remaining > 0) {
                $message = "Approval recorded. Waiting for {$remaining} more approval(s).";
            }

            return [
                'success' => true,
                'message' => $message,
                'batch' => $summary,
            ];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Unable to record approval: ' . $e->getMessage()];
        }
    }

    public function recordDisbursement(int $batchId, int $processedByUserId, ?string $reference = null, ?string $notes = null): bool
    {
        $this->conn->beginTransaction();
        try {
            $updateBatch = $this->conn->prepare(
                "UPDATE mlm_payout_batches
                 SET status = 'completed', processed_by_user_id = ?, processed_at = NOW(), disbursement_reference = ?, processed_notes = ?
                 WHERE id = ? AND status IN ('processing','draft')"
            );
            $updateBatch->execute([$processedByUserId, $reference, $notes, $batchId]);

            if ($updateBatch->rowCount() === 0) {
                $this->conn->rollBack();
                return false;
            }

            // Update commission ledger to paid
            $commUpdate = $this->conn->prepare(
                "UPDATE mlm_commission_ledger l
                 JOIN mlm_payout_batch_items bi ON l.id = bi.commission_id
                 SET l.status = 'paid', bi.status = 'paid', l.updated_at = NOW()
                 WHERE bi.batch_id = ?"
            );
            $commUpdate->execute([$batchId]);

            $this->conn->commit();

            $batch = $this->getBatchSummary($batchId);
            $items = $this->getBatchItems($batchId);

            if ($batch) {
                $this->notifyAdmin(
                    'Payout batch disbursed',
                    $this->buildBatchEmailBody('disbursed', array_merge($batch, [
                        'disbursement_reference' => $reference,
                        'processed_notes' => $notes,
                    ])),
                    'payout_batch_disbursed',
                    ['batch_id' => $batchId]
                );
                $data = array_merge($batch, [
                    'disbursement_reference' => $reference,
                    'processed_notes' => $notes,
                ]);
                $this->notifyFinance(
                    'Payout batch disbursed',
                    $this->buildBatchEmailBody('disbursed', $data),
                    'finance_batch_disbursed',
                    $data
                );
            }

            $this->notifyBeneficiaries($items, $batch, $reference);

            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function cancelBatch(int $batchId, ?string $reason = null): bool
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE mlm_payout_batches SET status = 'cancelled', processed_notes = ? WHERE id = ? AND status = 'draft'"
            );
            $stmt->execute([$reason, $batchId]);
            $success = $stmt->rowCount() > 0;

            if ($success) {
                $batch = $this->getBatchSummary($batchId);
                if ($batch) {
                    $this->notifyAdmin(
                        'Payout batch cancelled',
                        $this->buildBatchEmailBody('cancelled', array_merge($batch, ['processed_notes' => $reason])),
                        'payout_batch_cancelled',
                        ['batch_id' => $batchId]
                    );
                }
            }

            return $success;
        } catch (Exception $e) {
            return false;
        }
    }

    public function listBatches(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'];
        }

        $sql = 'SELECT * FROM mlm_payout_batches';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBatchSummary(int $batchId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_payout_batches WHERE id = ?');
        $stmt->execute([$batchId]);
        $batch = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$batch) {
            return null;
        }

        $itemStmt = $this->conn->prepare(
            'SELECT status, COUNT(*) AS items, SUM(amount) AS amount FROM mlm_payout_batch_items WHERE batch_id = ? GROUP BY status'
        );
        $itemStmt->execute([$batchId]);
        $batch['items_breakdown'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $approvalStmt = $this->conn->prepare(
            'SELECT a.*, u.name AS approver_name, u.email AS approver_email
             FROM mlm_payout_batch_approvals a
             JOIN users u ON a.approver_user_id = u.id
             WHERE a.batch_id = ?
             ORDER BY a.created_at ASC'
        );
        $approvalStmt->execute([$batchId]);
        $batch['approvals'] = $approvalStmt->fetchAll(PDO::FETCH_ASSOC);

        return $batch;
    }

    public function getBatchItems(int $batchId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT bi.*, u.name AS beneficiary_name, u.email AS beneficiary_email
             FROM mlm_payout_batch_items bi
             JOIN users u ON bi.beneficiary_user_id = u.id
             WHERE batch_id = ?
             ORDER BY bi.created_at ASC'
        );
        $stmt->execute([$batchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBatchExportData(int $batchId): ?array
    {
        $summary = $this->getBatchSummary($batchId);
        if (!$summary) {
            return null;
        }

        $items = $this->getBatchItems($batchId);

        return [
            'batch' => $summary,
            'items' => $items,
        ];
    }

    private function notifyAdmin(string $subject, string $body, string $type, array $payload = []): void
    {
        $this->notifier->notifyAdmin($subject, $body, $type, $payload);
    }

    private function notifyFinance(string $subject, string $body, string $type, array $payload = []): void
    {
        $this->notifier->notifyFinance($subject, $body, $type, $payload);
    }

    private function notifyBeneficiaries(array $items, ?array $batch, ?string $reference): void
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (!empty($item['beneficiary_email'])) {
                $this->notifier->sendEmail(
                    $item['beneficiary_email'],
                    'Payout Disbursed',
                    "Your payout of {$item['amount']} has been disbursed. Ref: {$reference}",
                    'payout_disbursed',
                    $item['beneficiary_user_id'],
                    ['batch_id' => $batch['id'] ?? null]
                );
            }
        }
    }

    private function buildBatchEmailBody(string $action, array $data): string
    {
        // Simple HTML builder
        $html = "<h1>Payout Batch " . ucfirst($action) . "</h1>";
        $html .= "<ul>";
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $html .= "<li><strong>{$key}:</strong> {$value}</li>";
            }
        }
        $html .= "</ul>";
        return $html;
    }
}
