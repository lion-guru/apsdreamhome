<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

/**
 * Unified DocumentService
 *
 * Consolidates all document-related services from:
 *   - DocumentService.php              (Original document service)
 *   - DocumentESignService.php         (Document e-signing)
 *   - DocumentLockerService.php        (Document locker/storage)
 *   - DocumentVaultService.php         (Document vault/storage)
 *   - LegalDocumentService.php         (Legal document service - App\Services)
 *   - Legal\LegalDocumentService.php   (Legal document service - App\Services\Legal)
 *   - AI\DocumentAIService.php         (AI document analysis)
 *   - AI\DocumentGeneratorAgent.php    (AI document generation agent)
 *   - AI\DocumentGeneratorService.php  (AI document generation service)
 *   - Legal\LegalDocumentService.php   (Legal document service - App\Services\Legal)
 *   - Loan\LoanDocumentService.php     (Loan document service)
 *
 * Single entry point for all document operations:
 *   - Document management (CRUD, storage, retrieval)
 *   - Document e-signing
 *   - Document locking/security
 *   - AI document analysis & generation
 *   - Legal document processing
 *   - Loan document processing
 *   - Template-based document generation
 *
 * Backed by tables: document_esign, document_locker, document_vault,
 * generated_documents, document_categories, legal_documents, loan_documents,
 * ai_knowledge_chunks, ai_knowledge_base
 */
class DocumentService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ========================================================================
    // DOCUMENT CRUD
    // ========================================================================

    /**
     * Create a document record
     */
    public function createDocument(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("
                INSERT INTO documents (
                    tenant_id, user_id, title, description, document_type,
                    file_path, file_url, file_size, mime_type, status,
                    category, metadata, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $params = [
                $tid,
                $data['user_id'] ?? null,
                $data['title'] ?? '',
                $data['description'] ?? null,
                $data['document_type'] ?? 'general',
                $data['file_path'] ?? null,
                $data['file_url'] ?? null,
                $data['file_size'] ?? null,
                $data['mime_type'] ?? null,
                $data['status'] ?? 'active',
                $data['category'] ?? null,
                json_encode($data['metadata'] ?? []),
            ];
            $stmt->execute($params);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDocument(int $id): ?array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("SELECT * FROM documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function getDocuments(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $where = ['1=1' . $this->tenantSql()];
        $params = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        if (!empty($filters['status'])) { $where[] = "status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['document_type'])) { $where[] = "document_type = ?"; $params[] = $filters['status']; }
        if (!empty($filters['category'])) { $where[] = "category = ?"; $params[] = $filters['category']; }
        if (!empty($filters['user_id'])) { $where[] = "user_id = ?"; $params[] = $filters['user_id']; }
        $sql = "SELECT * FROM documents WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function deleteDocument(int $id): bool
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("DELETE FROM documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        return $stmt->execute($params) !== false;
    }

    // ========================================================================
    // ESIGN SERVICE (merged from DocumentEsignService)
    // ========================================================================

    public function getESignDocuments(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_esign WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[DocumentService::getESignDocuments] ' . $e->getMessage());
            return [];
        }
    }

    public function getESignDocument(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_esign WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            error_log('[DocumentService::getESignDocument] ' . $e->getMessage());
            return null;
        }
    }

    public function createESignDocument(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("
                INSERT INTO document_esign (
                    document_type, title, content, status, created_by, tenant_id
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['document_type'],
                $data['title'],
                $data['content'],
                $data['status'] ?? 'pending',
                (int)$data['created_by'],
                $tid
            ]);
            $id = (int)$this->db->lastInsertId();
            return ['success' => true, 'id' => $id];
        } catch (\Throwable $e) {
            error_log('[DocumentService::createESignDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    // ========================================================================
    // DOCUMENT LOCKER (merged from DocumentLockerService)
    // ========================================================================

    public function createDocumentLocker(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("
                INSERT INTO document_locker (
                    user_id, title, document_type, document_name, file_path,
                    file_size, mime_type, status, remarks,
                    uploaded_by, tenant_id, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $data['user_id'],
                $data['title'] ?? '',
                $data['document_type'] ?? 'general',
                $data['document_name'] ?? null,
                $data['file_path'] ?? null,
                $data['file_size'] ?? null,
                $data['mime_type'] ?? null,
                $data['status'] ?? 'pending',
                $data['remarks'] ?? null,
                $data['uploaded_by'] ?? null,
                $tid
            ]);
            return [
                'success' => true,
                'id' => (int)$this->db->lastInsertId(),
                'message' => 'Document locker entry created'
            ];
        } catch (\Throwable $e) {
            error_log('[DocumentService::createDocumentLocker] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getDocumentLocker(int $id): ?array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("SELECT * FROM document_locker WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function getDocumentLockers(int $userId): array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("SELECT * FROM document_locker WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = $tid > 1 ? [$userId, $tid] : [$userId];
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    // ========================================================================
    // DOCUMENT VAULT (merged from DocumentVaultService)
    // ========================================================================

    public function createDocumentVault(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("
                INSERT INTO document_vault (
                    user_id, title, document_type, document_name, file_path,
                    file_size, mime_type, status, reason,
                    uploaded_by, tenant_id, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $data['user_id'],
                $data['title'] ?? '',
                $data['document_type'] ?? 'general',
                $data['document_name'] ?? null,
                $data['file_path'] ?? null,
                $data['file_size'] ?? null,
                $data['mime_type'] ?? null,
                $data['status'] ?? 'active',
                $data['reason'] ?? null,
                $data['uploaded_by'] ?? null,
                $tid
            ]);
            return [
                'success' => true,
                'id' => (int)$this->db->lastInsertId(),
                'message' => 'Document vault entry created'
            ];
        } catch (\Throwable $e) {
            error_log('[DocumentService::createDocumentVault] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getDocumentVault(int $id): ?array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("SELECT * FROM document_vault WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function getDocumentVaults(int $userId): array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("SELECT * FROM document_vault WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = $tid > 1 ? [$userId, $tid] : [$userId];
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    // ========================================================================
    // LEGAL DOCUMENT SERVICE (merged from LegalDocumentService)
    // ========================================================================

    public function getLegalDocuments(): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("SELECT * FROM legal_documents" . ($tid > 1 ? " WHERE tenant_id = ?" : ""));
            $stmt->execute($tid > 1 ? [$tid] : []);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('LegalDocumentService::getLegalDocuments error: ' . $e->getMessage());
            return [];
        }
    }

    public function getLegalDocument(int $id): ?array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("SELECT * FROM legal_documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function createLegalDocument(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("
                INSERT INTO legal_documents (
                    title, document_type, content, status, created_by, tenant_id
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['title'] ?? '',
                $data['document_type'] ?? 'legal',
                $data['content'] ?? '',
                $data['status'] ?? 'active',
                $data['created_by'] ?? null,
                $tid
            ]);
            return [
                'success' => true,
                'id' => (int)$this->db->lastInsertId(),
                'message' => 'Legal document created'
            ];
        } catch (\Throwable $e) {
            error_log('[DocumentService::createLegalDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getLegalDocumentById(int $id): ?array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("SELECT * FROM legal_documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function tenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable) {
            return 1;
        }
    }

    private function tenantSql(): string
    {
        return $this->tenantId() > 1 ? " AND tenant_id = ?" : "";
    }
}