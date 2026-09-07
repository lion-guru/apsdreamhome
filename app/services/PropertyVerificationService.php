<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class PropertyVerificationService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ===== Verification Levels =====

    public function getAllLevels(): array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT * FROM property_verification_levels WHERE is_active = 1" . $tenantSql . " ORDER BY sort_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function getLevelById(int $id): ?array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [(int)$id];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT * FROM property_verification_levels WHERE id = ?" . $tenantSql . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    // ===== Verification Requests =====

    public function createRequest(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $params = [
                $data['property_id'],
                $data['property_type'],
                $data['verification_level_id'],
                $data['requested_by'],
                $data['status'] ?? 'draft',
                $data['priority'] ?? 'standard',
                $data['title_deed'] ?? null,
                $data['sale_deed'] ?? null,
                $data['tax_receipts'] ?? null,
                $data['encumbrance_certificate'] ?? null,
                $data['approved_building_plan'] ?? null,
                $data['identity_proof'] ?? null,
                $data['additional_documents'] ? json_encode($data['additional_documents']) : null,
                $data['amount_paid'] ?? 0,
                $data['payment_status'] ?? 'pending',
                $data['payment_id'] ?? null,
            ];
            if ($tid > 1) $params[] = $tid;

            $sql = "INSERT INTO property_verification_requests (
                property_id, property_type, verification_level_id, requested_by, status, priority,
                title_deed, sale_deed, tax_receipts, encumbrance_certificate, approved_building_plan,
                identity_proof, additional_documents, amount_paid, payment_status, payment_id{$tenantCol}
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?{$tenantVal})";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $id = (int)$this->db->lastInsertId();
                return ['success' => true, 'id' => $id];
            }

            return ['success' => false, 'error' => 'Failed to create verification request'];
        } catch (\Exception $e) {
            error_log('[PropertyVerificationService::createRequest] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getRequestById(int $id): ?array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [(int)$id];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT r.*, l.name as level_name, l.slug as level_slug, l.price as level_price, l.features as level_features 
                FROM property_verification_requests r
                LEFT JOIN property_verification_levels l ON r.verification_level_id = l.id
                WHERE r.id = ?" . $tenantSql . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $request = $stmt->fetch() ?: null;
        
        if ($request && !empty($request['level_features'])) {
            $request['level_features'] = json_decode($request['level_features'], true) ?: [];
        }
        return $request;
    }

    public function getRequestsByUser(int $userId, string $status = null): array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [(int)$userId];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT r.*, l.name as level_name, l.slug as level_slug, l.price as level_price 
                FROM property_verification_requests r
                LEFT JOIN property_verification_levels l ON r.verification_level_id = l.id
                WHERE r.requested_by = ?" . $tenantSql;
        
        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function getRequestsByProperty(int $propertyId): array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [(int)$propertyId];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT r.*, l.name as level_name, l.slug as level_slug 
                FROM property_verification_requests r
                LEFT JOIN property_verification_levels l ON r.verification_level_id = l.id
                WHERE r.property_id = ?" . $tenantSql . " ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function updateRequest(int $id, array $data): array
    {
        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$data['status'] ?? null, $data['priority'] ?? null, $data['title_deed'] ?? null,
                $data['sale_deed'] ?? null, $data['tax_receipts'] ?? null, $data['encumbrance_certificate'] ?? null,
                $data['approved_building_plan'] ?? null, $data['identity_proof'] ?? null,
                $data['additional_documents'] ? json_encode($data['additional_documents']) : null,
                $data['amount_paid'] ?? null, $data['payment_status'] ?? null, $data['payment_id'] ?? null,
                $data['assigned_to'] ?? null, $data['verification_notes'] ?? null,
                $data['inspection_scheduled_at'] ?? null, $data['inspection_completed_at'] ?? null,
                $data['inspector_id'] ?? null, $data['inspection_report'] ?? null,
                $data['inspection_photos'] ? json_encode($data['inspection_photos']) : null,
                $data['badge_verification_code'] ?? null, $data['badge_awarded_at'] ?? null,
                $data['badge_expires_at'] ?? null,
                (int)$id];
            if ($tid > 1) $params[] = $tid;

            $sql = "UPDATE property_verification_requests SET 
                status = COALESCE(?, status),
                priority = COALESCE(?, priority),
                title_deed = COALESCE(?, title_deed),
                sale_deed = COALESCE(?, sale_deed),
                tax_receipts = COALESCE(?, tax_receipts),
                encumbrance_certificate = COALESCE(?, encumbrance_certificate),
                approved_building_plan = COALESCE(?, approved_building_plan),
                identity_proof = COALESCE(?, identity_proof),
                additional_documents = COALESCE(?, additional_documents),
                amount_paid = COALESCE(?, amount_paid),
                payment_status = COALESCE(?, payment_status),
                payment_id = COALESCE(?, payment_id),
                assigned_to = COALESCE(?, assigned_to),
                verification_notes = COALESCE(?, verification_notes),
                inspection_scheduled_at = COALESCE(?, inspection_scheduled_at),
                inspection_completed_at = COALESCE(?, inspection_completed_at),
                inspector_id = COALESCE(?, inspector_id),
                inspection_report = COALESCE(?, inspection_report),
                inspection_photos = COALESCE(?, inspection_photos),
                badge_verification_code = COALESCE(?, badge_verification_code),
                badge_awarded_at = COALESCE(?, badge_awarded_at),
                badge_expires_at = COALESCE(?, badge_expires_at),
                updated_at = NOW() 
                WHERE id = ?" . $tenantSql;

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                return ['success' => true, 'message' => 'Request updated'];
            }
            return ['success' => false, 'error' => 'No changes made'];
        } catch (\Exception $e) {
            error_log('[PropertyVerificationService::updateRequest] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function submitRequest(int $id, int $userId): array
    {
        return $this->updateRequest($id, [
            'status' => 'submitted',
            'amount_paid' => 0, // Will be updated after payment
            'payment_status' => 'pending'
        ]);
    }

    public function approveRequest(int $id, int $reviewerId): array
    {
        $verificationCode = bin2hex(random_bytes(16));
        return $this->updateRequest($id, [
            'status' => 'approved',
            'reviewed_at' => date('Y-m-d H:i:s'),
            'approved_at' => date('Y-m-d H:i:s'),
            'assigned_to' => $reviewerId,
            'badge_verification_code' => $verificationCode,
            'badge_awarded_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function rejectRequest(int $id, int $reviewerId, string $reason): array
    {
        return $this->updateRequest($id, [
            'status' => 'rejected',
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejected_at' => date('Y-m-d H:i:s'),
            'assigned_to' => $reviewerId,
            'rejection_reason' => $reason,
        ]);
    }

    // ===== Documents =====

    public function addDocument(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $params = [
                $data['request_id'],
                $data['document_type'],
                $data['file_path'],
                $data['file_name'],
                $data['file_size'] ?? 0,
                $data['mime_type'] ?? null,
                $data['uploaded_by'],
            ];
            if ($tid > 1) $params[] = $tid;

            $sql = "INSERT INTO property_verification_documents (
                request_id, document_type, file_path, file_name, file_size, mime_type, uploaded_by{$tenantCol}
            ) VALUES (?, ?, ?, ?, ?, ?, ?{$tenantVal})";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $id = (int)$this->db->lastInsertId();
                return ['success' => true, 'id' => $id];
            }
            return ['success' => false, 'error' => 'Failed to add document'];
        } catch (\Exception $e) {
            error_log('[PropertyVerificationService::addDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getDocuments(int $requestId): array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [(int)$requestId];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT * FROM property_verification_documents WHERE request_id = ?" . $tenantSql . " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function verifyDocument(int $docId, int $verifierId, bool $verified, string $notes = ''): array
    {
        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$verified ? 1 : 0, $verifierId, date('Y-m-d H:i:s'), $notes, $docId];
            if ($tid > 1) $params[] = $tid;

            $sql = "UPDATE property_verification_documents SET 
                verified = ?, verified_by = ?, verified_at = ?, verification_notes = ?, updated_at = NOW()
                WHERE id = ?" . $tenantSql;
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                return ['success' => true, 'message' => $verified ? 'Document verified' : 'Document marked unverified'];
            }
            return ['success' => false, 'error' => 'Failed to update document'];
        } catch (\Exception $e) {
            error_log('[PropertyVerificationService::verifyDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    // ===== Badges =====

    public function issueBadge(int $requestId, int $propertyId, int $levelId, int $issuedBy): array
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $params = [
                $requestId,
                $propertyId,
                $levelId,
                'VER-' . bin2hex(random_bytes(12)),
                date('Y-m-d H:i:s'),
                $tid,
            ];

            $sql = "INSERT INTO property_verification_badges (
                request_id, property_id, verification_level_id, badge_code, issued_at, tenant_id
            ) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $id = (int)$this->db->lastInsertId();
                // Update request with badge info
                $this->updateRequest((int)$requestId, [
                    'status' => 'approved',
                    'badge_awarded_at' => date('Y-m-d H:i:s'),
                    'badge_verification_code' => 'VER-' . bin2hex(random_bytes(12)),
                ]);
                return ['success' => true, 'id' => $id, 'badge_code' => 'VER-' . bin2hex(random_bytes(12))];
            }
            return ['success' => false, 'error' => 'Failed to issue badge'];
        } catch (\Exception $e) {
            error_log('[PropertyVerificationService::issueBadge] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getBadgeByCode(string $code): ?array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$code];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT b.*, l.name as level_name, l.slug as level_slug, l.price as level_price 
                FROM property_verification_badges b
                LEFT JOIN property_verification_levels l ON b.verification_level_id = l.id
                WHERE b.badge_code = ?" . $tenantSql . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function revokeBadge(int $badgeId, int $revokedBy, string $reason): array
    {
        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = ['revoked', $revokedBy, $reason, date('Y-m-d H:i:s'), $badgeId];
            if ($tid > 1) $params[] = $tid;

            $sql = "UPDATE property_verification_badges SET 
                status = ?, revoked_by = ?, revocation_reason = ?, revoked_at = ?, updated_at = NOW()
                WHERE id = ?" . $tenantSql;
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                return ['success' => true, 'message' => 'Badge revoked'];
            }
            return ['success' => false, 'error' => 'Failed to revoke badge'];
        } catch (\Exception $e) {
            error_log('[PropertyVerificationService::revokeBadge] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    // ===== Stats =====

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT 
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review,
            SUM(amount_paid) as total_revenue
            FROM property_verification_requests" . $tenantSql;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }

    public function getLevelStats(): array
    {
        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND r.tenant_id = ?" : "";
        $params = [];
        if ($tid > 1) $params[] = $tid;

        $sql = "SELECT l.id, l.name, l.slug, l.price, COUNT(r.id) as request_count
                FROM property_verification_levels l
                LEFT JOIN property_verification_requests r ON r.verification_level_id = l.id" . $tenantSql . "
                WHERE l.is_active = 1
                GROUP BY l.id
                ORDER BY l.sort_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }
}