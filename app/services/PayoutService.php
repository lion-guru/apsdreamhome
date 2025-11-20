<?php
require_once __DIR__ . '/NotificationService.php';
/**
 * PayoutService
 * Handles creation and lifecycle of payout batches for MLM commissions.
 */

class PayoutService
{
    private mysqli $conn;
    private NotificationService $notifier;

    public function __construct()
    {
        $config = AppConfig::getInstance();
        $this->conn = $config->getDatabaseConnection();
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

        $this->conn->begin_transaction();

        try {
            $batchReference = $filters['batch_reference'] ?? ('MLM' . date('YmdHis'));
            $limit = isset($filters['max_items']) ? (int)$filters['max_items'] : null;
            $minAmount = isset($filters['min_amount']) ? (float)$filters['min_amount'] : 0;
            $requiredApprovals = max(1, (int) ($filters['required_approvals'] ?? 1));

            $sql = "SELECT id, beneficiary_user_id, amount
                    FROM mlm_commission_ledger
                    WHERE status = 'approved'";
            $types = '';
            $params = [];

            if (!empty($filters['date_from'])) {
                $sql .= ' AND created_at >= ?';
                $types .= 's';
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= ' AND created_at <= ?';
                $types .= 's';
                $params[] = $filters['date_to'];
            }

            $sql .= ' ORDER BY created_at ASC';

            if ($limit) {
                $sql .= ' LIMIT ?';
                $types .= 'i';
                $params[] = $limit;
            }

            $stmt = $this->prepare($sql, $types, $params);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (empty($rows)) {
                $this->conn->rollback();
                $summary['message'] = 'No approved commissions available for payout.';
                return $summary;
            }

            $totalAmount = array_reduce($rows, fn($carry, $row) => $carry + (float)$row['amount'], 0.0);
            $totalRecords = count($rows);

            if ($minAmount > 0 && $totalAmount < $minAmount) {
                $this->conn->rollback();
                $summary['message'] = 'Total amount below minimum threshold.';
                return $summary;
            }

            $insertBatch = $this->conn->prepare(
                "INSERT INTO mlm_payout_batches (batch_reference, status, total_amount, total_records, required_approvals)
                 VALUES (?, 'pending_approval', ?, ?, ?)"
            );
            $insertBatch->bind_param('sdii', $batchReference, $totalAmount, $totalRecords, $requiredApprovals);
            $insertBatch->execute();
            $batchId = $insertBatch->insert_id;
            $insertBatch->close();

            $insertItem = $this->conn->prepare(
                "INSERT INTO mlm_payout_batch_items (batch_id, commission_id, beneficiary_user_id, amount)
                 VALUES (?, ?, ?, ?)"
            );

            foreach ($rows as $row) {
                $commissionId = (int)$row['id'];
                $beneficiaryId = (int)$row['beneficiary_user_id'];
                $amount = (float)$row['amount'];

                $insertItem->bind_param('iiid', $batchId, $commissionId, $beneficiaryId, $amount);
                $insertItem->execute();
            }
            $insertItem->close();

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
            $this->conn->rollback();
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

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, status, required_approvals, approval_count, approved_by_user_id, processed_notes
                 FROM mlm_payout_batches WHERE id = ? FOR UPDATE"
            );
            $stmt->bind_param('i', $batchId);
            $stmt->execute();
            $batch = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$batch) {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Batch not found.'];
            }

            if ($batch['status'] !== 'pending_approval') {
                $this->conn->rollback();
                return ['success' => false, 'message' => 'Batch is not awaiting approvals.'];
            }

            $insert = $this->conn->prepare(
                "INSERT INTO mlm_payout_batch_approvals (batch_id, approver_user_id, status, notes)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), updated_at = CURRENT_TIMESTAMP"
            );
            $insert->bind_param('iiss', $batchId, $approverUserId, $decision, $notes);
            $insert->execute();
            $insert->close();

            $countStmt = $this->conn->prepare(
                "SELECT
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count
                 FROM mlm_payout_batch_approvals
                 WHERE batch_id = ?"
            );
            $countStmt->bind_param('i', $batchId);
            $countStmt->execute();
            $counts = $countStmt->get_result()->fetch_assoc();
            $countStmt->close();

            $approvedCount = (int)($counts['approved_count'] ?? 0);
            $rejectedCount = (int)($counts['rejected_count'] ?? 0);

            $updateCount = $this->conn->prepare(
                'UPDATE mlm_payout_batches SET approval_count = ? WHERE id = ?'
            );
            $updateCount->bind_param('ii', $approvedCount, $batchId);
            $updateCount->execute();
            $updateCount->close();

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
                $updateStatus->bind_param('si', $processedNotes, $batchId);
                $updateStatus->execute();
                $updateStatus->close();
            } elseif ($approvedCount >= (int)$batch['required_approvals']) {
                $finalStatus = 'processing';
                $statusUpdate = $this->conn->prepare(
                    "UPDATE mlm_payout_batches
                     SET status = 'processing', approved_by_user_id = ?, approved_at = NOW()
                     WHERE id = ?"
                );
                $statusUpdate->bind_param('ii', $approverUserId, $batchId);
                $statusUpdate->execute();
                $statusUpdate->close();

                $itemUpdate = $this->conn->prepare(
                    "UPDATE mlm_payout_batch_items SET status = 'approved' WHERE batch_id = ?"
                );
                $itemUpdate->bind_param('i', $batchId);
                $itemUpdate->execute();
                $itemUpdate->close();
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
                    $summary,
                    'finance_batch_processing'
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
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Unable to record approval.'];
        }
    }

    public function recordDisbursement(int $batchId, int $processedByUserId, ?string $reference = null, ?string $notes = null): bool
    {
        $this->conn->begin_transaction();
        try {
            $updateBatch = $this->conn->prepare(
                "UPDATE mlm_payout_batches
                 SET status = 'completed', processed_by_user_id = ?, processed_at = NOW(), disbursement_reference = ?, processed_notes = ?
                 WHERE id = ? AND status IN ('processing','draft')"
            );
            $updateBatch->bind_param('isss', $processedByUserId, $reference, $notes, $batchId);
            $updateBatch->execute();

            if ($updateBatch->affected_rows === 0) {
                $this->conn->rollback();
                $updateBatch->close();
                return false;
            }
            $updateBatch->close();

            // Update commission ledger to paid
            $commUpdate = $this->conn->prepare(
                "UPDATE mlm_commission_ledger l
                 JOIN mlm_payout_batch_items bi ON l.id = bi.commission_id
                 SET l.status = 'paid', bi.status = 'paid', l.updated_at = NOW()
                 WHERE bi.batch_id = ?"
            );
            $commUpdate->bind_param('i', $batchId);
            $commUpdate->execute();
            $commUpdate->close();

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
                $this->notifyFinance(
                    'Payout batch disbursed',
                    array_merge($batch, [
                        'disbursement_reference' => $reference,
                        'processed_notes' => $notes,
                    ]),
                    'finance_batch_disbursed'
                );
            }

            $this->notifyBeneficiaries($items, $batch, $reference);

            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function cancelBatch(int $batchId, ?string $reason = null): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE mlm_payout_batches SET status = 'cancelled', processed_notes = ? WHERE id = ? AND status = 'draft'"
        );
        $stmt->bind_param('si', $reason, $batchId);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();

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
    }

    public function listBatches(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $types = '';
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $types .= 's';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $types .= 's';
            $params[] = $filters['date_to'];
        }

        $sql = 'SELECT * FROM mlm_payout_batches';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';

        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->prepare($sql, $types, $params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    public function getBatchSummary(int $batchId): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM mlm_payout_batches WHERE id = ?'
        );
        $stmt->bind_param('i', $batchId);
        $stmt->execute();
        $batch = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$batch) {
            return null;
        }

        $itemStmt = $this->conn->prepare(
            'SELECT status, COUNT(*) AS items, SUM(amount) AS amount FROM mlm_payout_batch_items WHERE batch_id = ? GROUP BY status'
        );
        $itemStmt->bind_param('i', $batchId);
        $itemStmt->execute();
        $batch['items_breakdown'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $itemStmt->close();

        $approvalStmt = $this->conn->prepare(
            'SELECT a.*, u.name AS approver_name, u.email AS approver_email
             FROM mlm_payout_batch_approvals a
             JOIN users u ON a.approver_user_id = u.id
             WHERE a.batch_id = ?
             ORDER BY a.created_at ASC'
        );
        $approvalStmt->bind_param('i', $batchId);
        $approvalStmt->execute();
        $batch['approvals'] = $approvalStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $approvalStmt->close();

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
        $stmt->bind_param('i', $batchId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
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

    private function prepare(string $sql, string $types = '', array $params = []): mysqli_stmt
    {
        $stmt = $this->conn->prepare($sql);
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        return $stmt;
    }

    private function notifyAdmin(string $subject, string $body, string $type, array $payload = []): void
    {
        $this->notifier->notifyAdmin($subject, $body, $type, $payload);
    }

    private function notifyBeneficiaries(array $items, ?array $batch, ?string $reference): void
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (empty($item['beneficiary_email'])) {
                continue;
            }

            $subject = 'Commission payout received - Batch ' . ($batch['batch_reference'] ?? '#'.$item['batch_id']);
            $body = $this->buildBeneficiaryEmailBody($item, $batch, $reference);
            $this->notifier->sendEmail(
                $item['beneficiary_email'],
                $subject,
                $body,
                'payout_disbursed',
                $item['beneficiary_user_id'],
                [
                    'batch_id' => $batch['id'] ?? null,
                    'commission_id' => $item['commission_id'],
                    'amount' => $item['amount'],
                ]
            );
        }
    }

    private function buildBatchEmailBody(string $event, array $data): string
    {
        $statusLabel = ucfirst($event);
        $amount = number_format((float) ($data['total_amount'] ?? 0), 2);
        $records = (int) ($data['total_records'] ?? 0);
        $reference = htmlspecialchars($data['batch_reference'] ?? ($data['id'] ?? ''));
        $notes = htmlspecialchars($data['processed_notes'] ?? '' ?: '—');
        $disbursementRef = htmlspecialchars($data['disbursement_reference'] ?? '—');
        $approvedAt = !empty($data['approved_at']) ? date('d M Y H:i', strtotime($data['approved_at'])) : '—';
        $processedAt = !empty($data['processed_at']) ? date('d M Y H:i', strtotime($data['processed_at'])) : '—';

        return "<h2>MLM Payout Batch {$statusLabel}</h2>
            <p><strong>Batch Reference:</strong> {$reference}</p>
            <p><strong>Total Amount:</strong> ₹{$amount}</p>
            <p><strong>Total Records:</strong> {$records}</p>
            <p><strong>Approved At:</strong> {$approvedAt}</p>
            <p><strong>Processed At:</strong> {$processedAt}</p>
            <p><strong>Disbursement Reference:</strong> {$disbursementRef}</p>
            <p><strong>Notes:</strong> {$notes}</p>
            <p>View batch in admin panel: <a href='" . BASE_URL . "admin/payouts'>Admin Payouts</a></p>";
    }

    private function buildBeneficiaryEmailBody(array $item, ?array $batch, ?string $reference): string
    {
        $name = htmlspecialchars($item['beneficiary_name'] ?? 'Associate');
        $amount = number_format((float) $item['amount'], 2);
        $batchRef = htmlspecialchars($batch['batch_reference'] ?? ($batch['id'] ?? '')); 
        $ref = htmlspecialchars($reference ?? ($batch['disbursement_reference'] ?? '—'));
        $date = !empty($batch['processed_at']) ? date('d M Y', strtotime($batch['processed_at'])) : date('d M Y');

        return "<h2>Commission Payout Received</h2>
            <p>Dear {$name},</p>
            <p>Your commission has been paid as part of payout batch <strong>{$batchRef}</strong>.</p>
            <ul>
                <li><strong>Amount:</strong> ₹{$amount}</li>
                <li><strong>Commission ID:</strong> {$item['commission_id']}</li>
                <li><strong>Payout Date:</strong> {$date}</li>
                <li><strong>Reference:</strong> {$ref}</li>
            </ul>
            <p>You can view your commission history in your referral dashboard.</p>
            <p>Thank you for partnering with APS Dream Home.</p>";
    }
}
