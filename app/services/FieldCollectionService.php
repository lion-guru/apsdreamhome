<?php

namespace App\Services;

/**
 * On-Field Cash Collection Service for Associates & Agents
 *
 * Wraps CashCollectionService with role-aware filtering so field
 * collectors see only their own submissions. Handles receipt photo
 * uploads and bridges to the admin verification pipeline.
 */
class FieldCollectionService
{
    private $db;
    private $cashCollection;

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
        $this->cashCollection = new CashCollectionService($this->db);
    }

    /**
     * Submit a cash collection on behalf of the authenticated field collector.
     *
     * @param array $data  Form data (customer_name, amount, collection_date, etc.)
     * @param int   $collectorId  Authenticated user's ID
     * @param array|null $file  Optional $_FILES['receipt_photo'] entry
     * @return array  ['success' => bool, 'collection_id' => ?int, 'error' => ?string]
     */
    public function submitFieldCollection(array $data, int $collectorId, ?array $file = null): array
    {
        // Upload receipt photo if provided
        $photoPath = null;
        if ($file && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $upload = $this->cashCollection->uploadReceipt($file);
            if ($upload['success']) {
                $photoPath = $upload['path'];
            } else {
                return ['success' => false, 'error' => 'Photo upload failed: ' . ($upload['error'] ?? 'Unknown')];
            }
        }

        // Build submission payload
        $payload = [
            'collector_id'    => $collectorId,
            'customer_name'   => trim($data['customer_name'] ?? ''),
            'amount'          => (float)($data['amount'] ?? 0),
            'collection_date' => $data['collection_date'] ?? date('Y-m-d'),
            'payment_method'  => $data['payment_method'] ?? 'cash',
            'reference_number'=> $data['reference_number'] ?? null,
            'receipt_photo'   => $photoPath,
            'notes'           => trim($data['notes'] ?? ''),
            'booking_id'      => !empty($data['booking_id']) ? (int)$data['booking_id'] : null,
            'installment_id'  => !empty($data['installment_id']) ? (int)$data['installment_id'] : null,
        ];

        return $this->cashCollection->submitCollection($payload);
    }

    /**
     * Get all collections submitted by a specific collector.
     */
    public function getMyCollections(int $collectorId, string $status = '', string $fromDate = '', string $toDate = '', int $limit = 100): array
    {
        return $this->cashCollection->getCollections($status, $collectorId, $fromDate, $toDate, $limit);
    }

    /**
     * Get a single collection by ID, verifying it belongs to the given collector.
     */
    public function getMyCollectionById(int $id, int $collectorId): ?array
    {
        $col = $this->cashCollection->getById($id);
        if ($col && (int)($col['collector_id'] ?? 0) === $collectorId) {
            return $col;
        }
        return null;
    }

    /**
     * Get stats scoped to a single collector.
     */
    public function getMyStats(int $collectorId): array
    {
        $stats = [
            'today_amount'      => 0, 'today_count'    => 0,
            'total_amount'      => 0, 'total_count'    => 0,
            'pending_count'     => 0, 'pending_amount' => 0,
            'verified_count'    => 0, 'verified_amount'=> 0,
            'rejected_count'    => 0, 'rejected_amount'=> 0,
            'this_month_amount' => 0, 'this_month_count'=> 0,
        ];
        try {
            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE collector_id = ? AND collection_date = ?");
            $s->execute([$collectorId, date('Y-m-d')]); $stats['today_amount'] = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COUNT(*) FROM cash_collections WHERE collector_id = ? AND collection_date = ?");
            $s->execute([$collectorId, date('Y-m-d')]); $stats['today_count'] = (int)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE collector_id = ?");
            $s->execute([$collectorId]); $stats['total_amount'] = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COUNT(*) FROM cash_collections WHERE collector_id = ?");
            $s->execute([$collectorId]); $stats['total_count'] = (int)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COUNT(*) FROM cash_collections WHERE collector_id = ? AND status = 'submitted'");
            $s->execute([$collectorId]); $stats['pending_count'] = (int)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE collector_id = ? AND status = 'submitted'");
            $s->execute([$collectorId]); $stats['pending_amount'] = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COUNT(*) FROM cash_collections WHERE collector_id = ? AND status = 'verified'");
            $s->execute([$collectorId]); $stats['verified_count'] = (int)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE collector_id = ? AND status = 'verified'");
            $s->execute([$collectorId]); $stats['verified_amount'] = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COUNT(*) FROM cash_collections WHERE collector_id = ? AND status = 'rejected'");
            $s->execute([$collectorId]); $stats['rejected_count'] = (int)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE collector_id = ? AND status = 'rejected'");
            $s->execute([$collectorId]); $stats['rejected_amount'] = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM cash_collections WHERE collector_id = ? AND collection_date >= ?");
            $s->execute([$collectorId, date('Y-m-01')]); $stats['this_month_amount'] = (float)$s->fetchColumn();

            $s = $this->db->prepare("SELECT COUNT(*) FROM cash_collections WHERE collector_id = ? AND collection_date >= ?");
            $s->execute([$collectorId, date('Y-m-01')]); $stats['this_month_count'] = (int)$s->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[FieldCollectionService] getMyStats error: ' . $e->getMessage());
        }
        return $stats;
    }

    /**
     * Return the underlying CashCollectionService for advanced operations.
     */
    public function getCashCollectionService(): CashCollectionService
    {
        return $this->cashCollection;
    }
}
