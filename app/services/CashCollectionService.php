<?php

namespace App\Services;

/**
 * On-Field Cash Collection & Reconciliation Service
 * Manages field agent/associate cash collection receipts, verification, and reconciliation
 */
class CashCollectionService
{
    private $db;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = new \PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->db = $db;
    }

    /**
     * Submit a new cash collection receipt from field agent
     */
    public function submitCollection(array $data): array
    {
        $required = ['collector_id', 'customer_name', 'amount', 'collection_date'];
        foreach ($required as $r) {
            if (empty($data[$r])) return ['success' => false, 'error' => "Missing: $r"];
        }
        $amount = (float)$data['amount'];
        if ($amount <= 0) return ['success' => false, 'error' => 'Amount must be positive'];

        try {
            $collectionNumber = 'CC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("INSERT INTO cash_collections
                (collection_number, booking_id, installment_id, collector_id, customer_name, amount, collection_date,
                 payment_method, reference_number, receipt_photo, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted')");
            $stmt->execute([
                $collectionNumber,
                !empty($data['booking_id']) ? (int)$data['booking_id'] : null,
                !empty($data['installment_id']) ? (int)$data['installment_id'] : null,
                (int)$data['collector_id'],
                trim($data['customer_name']),
                $amount,
                $data['collection_date'],
                $data['payment_method'] ?? 'cash',
                $data['reference_number'] ?? null,
                $data['receipt_photo'] ?? null,
                $data['notes'] ?? null
            ]);
            $id = (int)$this->db->lastInsertId();

            // If linked to a booking, record payment
            if (!empty($data['booking_id']) && !empty($data['installment_id'])) {
                try {
                    $this->db->prepare("UPDATE booking_payment_schedules
                        SET paid_amount = paid_amount + ?, payment_date = ?, payment_method = ?
                        WHERE id = ? AND paid_amount < emi_amount")
                        ->execute([$amount, $data['collection_date'], $data['payment_method'] ?? 'cash', $data['installment_id']]);
                } catch (\Throwable $e) {
                    error_log("[CashCollection] Failed to update installment: " . $e->getMessage());
                }
            }

            return ['success' => true, 'collection_id' => $id, 'collection_number' => $collectionNumber];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get all collections with optional filters
     */
    public function getCollections(string $status = '', ?int $collectorId = null, string $fromDate = '', string $toDate = '', int $limit = 100): array
    {
        $sql = "SELECT c.*, u.name as collector_name, bv.booking_number, bv.plot_id
                FROM cash_collections c
                LEFT JOIN users u ON u.id = c.collector_id
                LEFT JOIN plot_bookings bv ON bv.id = c.booking_id";
        $params = [];
        $where = [];
        if ($status) { $where[] = "c.status = ?"; $params[] = $status; }
        if ($collectorId) { $where[] = "c.collector_id = ?"; $params[] = $collectorId; }
        if ($fromDate) { $where[] = "c.collection_date >= ?"; $params[] = $fromDate; }
        if ($toDate) { $where[] = "c.collection_date <= ?"; $params[] = $toDate; }
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY c.created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get single collection by ID
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT c.*, u.name as collector_name, bv.booking_number, bv.plot_id,
                vu.name as verified_by_name
                FROM cash_collections c
                LEFT JOIN users u ON u.id = c.collector_id
                LEFT JOIN plot_bookings bv ON bv.id = c.booking_id
                LEFT JOIN users vu ON vu.id = c.verified_by
                WHERE c.id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Admin verify a collection receipt
     */
    public function verifyCollection(int $id, int $verifiedBy): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE cash_collections
                SET status = 'verified', verified_by = ?, verified_at = NOW()
                WHERE id = ? AND status = 'submitted'");
            $stmt->execute([$verifiedBy, $id]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Admin reject a collection receipt
     */
    public function rejectCollection(int $id, int $verifiedBy, string $reason): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE cash_collections
                SET status = 'rejected', verified_by = ?, verified_at = NOW(), rejection_reason = ?
                WHERE id = ? AND status = 'submitted'");
            $stmt->execute([$verifiedBy, $reason, $id]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Bulk verify multiple collections
     */
    public function bulkVerify(array $ids, int $verifiedBy): int
    {
        $count = 0;
        foreach ($ids as $id) {
            if ($this->verifyCollection((int)$id, $verifiedBy)) $count++;
        }
        return $count;
    }

    /**
     * Create a reconciliation session for a collector on a date
     */
    public function createReconciliation(int $collectorId, string $sessionDate, string $notes = ''): array
    {
        try {
            $this->db->beginTransaction();

            // Get totals for this collector on this date
            $stmt = $this->db->prepare("SELECT
                SUM(CASE WHEN status IN ('submitted','verified') THEN amount ELSE 0 END) as total_submitted,
                SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END) as total_verified,
                SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END) as total_rejected
                FROM cash_collections WHERE collector_id = ? AND collection_date = ?");
            $stmt->execute([$collectorId, $sessionDate]);
            $totals = $stmt->fetch();

            $submitted = (float)($totals['total_submitted'] ?? 0);
            $verified = (float)($totals['total_verified'] ?? 0);
            $rejected = (float)($totals['total_rejected'] ?? 0);
            $discrepancy = $submitted - $verified - $rejected;

            $ins = $this->db->prepare("INSERT INTO reconciliation_collections
                (session_date, collector_id, total_submitted, total_verified, total_rejected,
                 discrepancy_amount, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([
                $sessionDate, $collectorId, $submitted, $verified, $rejected,
                $discrepancy,
                abs($discrepancy) > 0.01 ? 'discrepancy' : 'open',
                $notes
            ]);
            $reconId = (int)$this->db->lastInsertId();

            // Mark collections as reconciled
            $this->db->prepare("UPDATE cash_collections
                SET status = 'reconciled'
                WHERE collector_id = ? AND collection_date = ? AND status = 'verified'")
                ->execute([$collectorId, $sessionDate]);

            $this->db->commit();
            return ['success' => true, 'reconciliation_id' => $reconId, 'discrepancy' => $discrepancy];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['success' => false, 'error' => 'Reconciliation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Close a reconciliation session
     */
    public function closeReconciliation(int $reconId, int $closedBy): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE reconciliation_collections
                SET status = 'closed', closed_by = ?, closed_at = NOW()
                WHERE id = ? AND status IN ('open','discrepancy')");
            $stmt->execute([$closedBy, $reconId]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * List reconciliation sessions
     */
    public function getReconciliations(string $status = '', int $limit = 50): array
    {
        $sql = "SELECT r.*, u.name as collector_name, cb.name as closed_by_name
                FROM reconciliation_collections r
                LEFT JOIN users u ON u.id = r.collector_id
                LEFT JOIN users cb ON cb.id = r.closed_by";
        $params = [];
        if ($status) { $sql .= " WHERE r.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY r.session_date DESC, r.created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get collection dashboard stats
     */
    public function getStats(): array
    {
        $stats = [
            'total_submitted' => 0, 'total_verified' => 0, 'total_rejected' => 0,
            'total_amount' => 0, 'today_amount' => 0, 'today_count' => 0,
            'pending_count' => 0, 'pending_amount' => 0, 'active_collectors' => 0
        ];
        try {
            $stats['total_submitted'] = (int)$this->db->query("SELECT COUNT(*) FROM cash_collections")->fetchColumn();
            $stats['total_verified'] = (int)$this->db->query("SELECT COUNT(*) FROM cash_collections WHERE status='verified'")->fetchColumn();
            $stats['total_rejected'] = (int)$this->db->query("SELECT COUNT(*) FROM cash_collections WHERE status='rejected'")->fetchColumn();
            $stats['total_amount'] = (float)$this->db->query("SELECT COALESCE(SUM(amount),0) FROM cash_collections")->fetchColumn();
            $stats['today_amount'] = (float)$this->db->query("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE collection_date = CURDATE()")->fetchColumn();
            $stats['today_count'] = (int)$this->db->query("SELECT COUNT(*) FROM cash_collections WHERE collection_date = CURDATE()")->fetchColumn();
            $stats['pending_count'] = (int)$this->db->query("SELECT COUNT(*) FROM cash_collections WHERE status='submitted'")->fetchColumn();
            $stats['pending_amount'] = (float)$this->db->query("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE status='submitted'")->fetchColumn();
            $stats['active_collectors'] = (int)$this->db->query("SELECT COUNT(DISTINCT collector_id) FROM cash_collections WHERE collection_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
        } catch (\Throwable $e) {}
        return $stats;
    }

    /**
     * Get list of collectors (users who have submitted collections)
     */
    public function getCollectors(): array
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT c.collector_id, u.name, u.email,
                COUNT(c.id) as collection_count, COALESCE(SUM(c.amount),0) as total_amount
                FROM cash_collections c
                JOIN users u ON u.id = c.collector_id
                GROUP BY c.collector_id, u.name, u.email
                ORDER BY total_amount DESC");
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Upload receipt photo
     */
    public function uploadReceipt(array $file): array
    {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload failed'];
        }
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/heic'];
        if (!in_array($file['type'], $allowed)) {
            return ['success' => false, 'error' => 'Only JPG, PNG, WebP, HEIC allowed'];
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Max 10MB'];
        }
        $dir = STORAGE_PATH . '/uploads/cash_receipts/' . date('Y/m');
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'receipt_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = $dir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $path)) {
            return ['success' => true, 'path' => 'uploads/cash_receipts/' . date('Y/m') . '/' . $filename];
        }
        return ['success' => false, 'error' => 'Move failed'];
    }
}
