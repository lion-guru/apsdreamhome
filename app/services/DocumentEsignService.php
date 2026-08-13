<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class DocumentEsignService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDocumentsByTenant(int $tenantId): array
    {
        try {
            $sql = "SELECT * FROM document_esign WHERE tenant_id = ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$tenantId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            error_log('[DocumentEsignService::getDocumentsByTenant] ' . $e->getMessage());
            return [];
        }
    }

    public function getDocumentById(int $id): ?array
    {
        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [(int)$id];
            if ($tid > 1) $params[] = $tid;

            $sql = "SELECT * FROM document_esign WHERE id = ?" . $tenantSql . " LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            error_log('[DocumentEsignService::getDocumentById] ' . $e->getMessage());
            return null;
        }
    }

    public function createDocument(array $data): array
    {
        try {
            $tid = $this->tenantId();

            $sql = "INSERT INTO document_esign (
                document_type, title, content, status, created_by, tenant_id
            ) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['document_type'],
                $data['title'],
                $data['content'],
                $data['status'] ?? 'pending',
                (int)$data['created_by'],
                $data['tenant_id'] ?? $tid
            ]);

            if ($result) {
                $id = (int)$this->db->lastInsertId();
                return ['success' => true, 'id' => $id];
            }

            return ['success' => false, 'error' => 'Failed to create document'];
        } catch (\Exception $e) {
            error_log('[DocumentEsignService::createDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function signDocument(int $id, array $data): array
    {
        try {
            $document = $this->getDocumentById($id);
            if (!$document) {
                return ['success' => false, 'error' => 'Document not found'];
            }

            if ($document['status'] === 'signed') {
                return ['success' => false, 'error' => 'Document already signed'];
            }

            $signatureData = $data['signature_data'] ?? null;
            $signedBy = $data['signed_by'] ?? $this->tenantId();
            $signedAt = $data['signed_at'] ?? date('Y-m-d H:i:s');
            $verificationCode = $data['verification_code'] ?? bin2hex(random_bytes(16));

            if (empty($signatureData)) {
                return ['success' => false, 'error' => 'Signature data is required'];
            }

            $sql = "UPDATE document_esign SET signature_data = ?, signed_by = ?, signed_at = ?, verification_code = ?, status = 'signed', updated_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $signatureData,
                $signedBy,
                $signedAt,
                $verificationCode,
                date('Y-m-d H:i:s'),
                $id
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Document signed successfully'];
            }

            return ['success' => false, 'error' => 'Failed to update document'];
        } catch (\Exception $e) {
            error_log('[DocumentEsignService::signDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function verifyDocument(int $id, string $verificationCode): array
    {
        try {
            $document = $this->getDocumentById($id);
            if (!$document) {
                return ['success' => false, 'error' => 'Document not found'];
            }

            $storedCode = $document['verification_code'] ?? null;

            if (empty($storedCode) || $storedCode !== $verificationCode) {
                return ['success' => false, 'error' => 'Invalid verification code'];
            }

            if ($document['status'] !== 'signed') {
                return ['success' => false, 'error' => 'Document is not signed'];
            }

            return [
                'success' => true,
                'document' => $document,
                'message' => 'Document verified successfully'
            ];
        } catch (\Exception $e) {
            error_log('[DocumentEsignService::verifyDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function cancelDocument(int $id, int $cancelledBy): array
    {
        try {
            $document = $this->getDocumentById($id);
            if (!$document) {
                return ['success' => false, 'error' => 'Document not found'];
            }

            if ($document['status'] === 'signed') {
                return ['success' => false, 'error' => 'Cannot cancel a signed document'];
            }

            $sql = "UPDATE document_esign SET status = 'cancelled', cancelled_by = ?, updated_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $cancelledBy,
                date('Y-m-d H:i:s'),
                $id
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Document cancelled successfully'];
            }

            return ['success' => false, 'error' => 'Failed to cancel document'];
        } catch (\Exception $e) {
            error_log('[DocumentEsignService::cancelDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
}
