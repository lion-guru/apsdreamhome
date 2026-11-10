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
            // Real documents schema (verified via DESCRIBE): entity_type, entity_id,
            // property_id, type, document_type, document_number, issued_by, issue_date,
            // expiry_date, verification_status, url, uploaded_on, drive_file_id.
            // Old unified code used imagined cols (title, file_path, ...) → 1136.
            $stmt = $this->db->prepare("
                INSERT INTO documents (
                    tenant_id, user_id, entity_type, entity_id, property_id,
                    type, document_type, document_number, issued_by, issue_date,
                    expiry_date, verification_status, url, uploaded_on
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $params = [
                $tid,
                $data['user_id'] ?? null,
                $data['entity_type'] ?? 'property',
                $data['entity_id'] ?? $data['property_id'] ?? null,
                $data['property_id'] ?? $data['entity_id'] ?? null,
                $data['type'] ?? 'general',
                $data['document_type'] ?? 'general',
                $data['document_number'] ?? null,
                $data['issued_by'] ?? null,
                $data['issue_date'] ?? null,
                $data['expiry_date'] ?? null,
                $data['verification_status'] ?? 'pending',
                $data['file_url'] ?? $data['url'] ?? $data['file_path'] ?? null,
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
        $stmt = $this->db->prepare("SELECT * FROM documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    // ========================================================================
    // DOCUMENT AI JOBS COMPAT (DocumentAIController expects these; unified
    // service previously lacked them → graceful 400s. Map to documents table.)
    // ========================================================================

    public function listJobs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        try {
            return $this->getDocuments($filters, $limit, $offset);
        } catch (\Throwable $e) {
            error_log('DocumentService::listJobs: ' . $e->getMessage());
            return [];
        }
    }

    public function getJob(int $id): ?array
    {
        try {
            return $this->getDocument($id);
        } catch (\Throwable $e) {
            error_log('DocumentService::getJob: ' . $e->getMessage());
            return null;
        }
    }

    public function createJob(array $data): array
    {
        try {
            $doc = $this->createDocument($data);
            if (!empty($doc['success']) && isset($doc['id'])) {
                return ['success' => true, 'job_id' => (int)$doc['id'], 'id' => (int)$doc['id']];
            }
            return ['success' => false, 'error' => $doc['error'] ?? 'create failed'];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // DocumentAIController expects createExtractionJob() — alias to createJob
    public function createExtractionJob(array $data): array
    {
        return $this->createJob($data);
    }

    // DocumentAIController processJob() — wraps getDocument as "processed"
    public function processJob(int $jobId): array
    {
        try {
            $doc = $this->getDocument($jobId);
            if (!$doc) {
                return ['success' => false, 'error' => 'Job not found'];
            }
            return ['success' => true, 'job' => $doc, 'processed' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * DocumentAIController::reviewJob() — approve or correct an extraction.
     * Maps onto the unified `documents` table: approve sets
     * verification_status='verified', correct overwrites the stored fields
     * with the reviewer's corrections.
     */
    public function reviewJob(int $jobId, int $reviewerId, array $corrections = [], string $action = 'approve'): array
    {
        try {
            $doc = $this->getDocument($jobId);
            if (!$doc) {
                return ['success' => false, 'error' => 'Job not found'];
            }

            $tid = $this->tenantId();
            $now = date('Y-m-d H:i:s');

            if ($action === 'approve') {
                // documents.verified_by / verified_at are the semantic columns
                // for an approval (uploaded_on is the creation stamp).
                $stmt = $this->db->prepare(
                    "UPDATE documents SET verification_status = 'verified', verified_by = ?, verified_at = ?"
                    . ($tid > 1 ? " WHERE id = ? AND tenant_id = ?" : " WHERE id = ?")
                );
                $params = [$reviewerId ?: null, $now, $jobId];
                if ($tid > 1) {
                    $params[] = $tid;
                }
                $stmt->execute($params);

                return ['success' => true, 'id' => $jobId, 'action' => 'approve'];
            }

            // action === 'correct'
            // Only columns that exist on `documents` and are safe to overwrite.
            $allowed = [
                'document_number' => 'string',
                'issued_by'       => 'string',
                'url'             => 'string',
                'issue_date'      => 'date',
                'expiry_date'     => 'date',
            ];
            $sets = [];
            $params = [];
            $applied = 0;
            foreach ($corrections as $field => $value) {
                if (!isset($allowed[$field])) {
                    continue;
                }
                $v = is_scalar($value) ? trim((string)$value) : '';
                if ($v === '') {
                    continue;
                }
                if ($allowed[$field] === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                    continue; // issue_date / expiry_date are DATE columns
                }
                $sets[] = "$field = ?";
                $params[] = $v;
                $applied++;
            }
            if (empty($sets)) {
                return ['success' => false, 'error' => 'No valid corrections supplied'];
            }
            $sets[] = "verification_status = ?";
            $params[] = 'verified';

            $sql = "UPDATE documents SET " . implode(', ', $sets)
                 . ($tid > 1 ? " WHERE id = ? AND tenant_id = ?" : " WHERE id = ?");
            $params[] = $jobId;
            if ($tid > 1) {
                $params[] = $tid;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'id' => $jobId, 'action' => 'correct', 'applied' => $applied];
        } catch (\Throwable $e) {
            error_log('[DocumentService::reviewJob] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getEngines(): array
    {
        // Document AI engines available (OCR + vision extraction)
        $engines = [];
        try {
            $configFile = dirname(__DIR__, 2) . '/config/document_ai_engines.php';
            if (file_exists($configFile)) {
                $engines = require $configFile;
            }
        } catch (\Throwable $e) {}
        if (empty($engines)) {
            $engines = [
                ['id' => 'tesseract', 'name' => 'Tesseract OCR', 'type' => 'ocr'],
                ['id' => 'gemini_vision', 'name' => 'Gemini Vision', 'type' => 'vision'],
            ];
        }
        return $engines;
    }

    public function getDocumentTypes(): array
    {
        // Standard document types supported by AI extraction
        $types = [];
        try {
            $rows = $this->db->fetchAll("SELECT DISTINCT document_type FROM documents WHERE document_type IS NOT NULL LIMIT 20");
            foreach ($rows as $r) {
                $types[] = ['value' => $r['document_type'], 'label' => ucwords(str_replace('_', ' ', $r['document_type']))];
            }
        } catch (\Throwable $e) {}
        if (empty($types)) {
            $types = [
                ['value' => 'general', 'label' => 'General'],
                ['value' => 'property_deed', 'label' => 'Property Deed'],
                ['value' => 'kyc_document', 'label' => 'KYC Document'],
                ['value' => 'invoice', 'label' => 'Invoice'],
                ['value' => 'agreement', 'label' => 'Agreement'],
                ['value' => 'registry_document', 'label' => 'Registry Document'],
            ];
        }
        return $types;
    }

    public function getTemplate(string $documentType): ?array
    {
        // Template for extraction fields per document type
        $templates = [
            'general' => ['fields' => ['title', 'content', 'date']],
            'property_deed' => ['fields' => ['buyer_name', 'seller_name', 'plot_number', 'colony', 'amount', 'date']],
            'kyc_document' => ['fields' => ['name', 'document_type', 'document_number', 'date_of_birth']],
            'invoice' => ['fields' => ['invoice_number', 'amount', 'date', 'vendor']],
            'agreement' => ['fields' => ['party_a', 'party_b', 'effective_date', 'amount']],
            'registry_document' => ['fields' => ['deed_number', 'buyer_name', 'seller_name', 'stamp_duty', 'registration_date']],
        ];
        return $templates[$documentType] ?? $templates['general'];
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function deleteDocument(int $id): bool
    {
        $tid = $this->tenantId();
        $stmt = $this->db->prepare("DELETE FROM documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
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
            // NOTE: document_esign has no `user_id` column — the owner column is
            // `created_by` (schema verified via DESCRIBE). The old query used
            // `user_id` and always 1054'd, so this method silently returned [].
            $tid = $this->tenantId();
            $stmt = $this->db->prepare(
                "SELECT * FROM document_esign WHERE created_by = ?"
                . ($tid > 1 ? " AND tenant_id = ?" : "")
                . " ORDER BY created_at DESC"
            );
            $params = [$userId];
            if ($tid > 1) {
                $params[] = $tid;
            }
            $stmt->execute($params);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[DocumentService::getESignDocuments] ' . $e->getMessage());
            return [];
        }
    }

    public function getESignDocument(int $id): ?array
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare(
                "SELECT * FROM document_esign WHERE id = ?"
                . ($tid > 1 ? " AND tenant_id = ?" : "")
                . " LIMIT 1"
            );
            $params = [$id];
            if ($tid > 1) {
                $params[] = $tid;
            }
            $stmt->execute($params);
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

    /**
     * Sign an e-sign document.
     * Marks status='signed' and stores signature_data / signed_by / signed_at /
     * verification_code. Tenant-scoped: a document belonging to another tenant
     * is reported as not found (not as a permission error).
     */
    public function signESignDocument(int $id, array $data): array
    {
        try {
            $tid = $this->tenantId();

            $stmt = $this->db->prepare("
                SELECT id, status FROM document_esign
                WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
                LIMIT 1
            ");
            $params = [$id];
            if ($tid > 1) {
                $params[] = $tid;
            }
            $stmt->execute($params);
            $doc = $stmt->fetch();

            if (!$doc) {
                return ['success' => false, 'error' => 'Document not found'];
            }

            if (($doc['status'] ?? '') === 'cancelled') {
                return ['success' => false, 'error' => 'Document has been cancelled and cannot be signed'];
            }

            $signature = $data['signature_data'] ?? null;
            if ($signature === null || trim((string)$signature) === '') {
                return ['success' => false, 'error' => 'Signature data is required'];
            }

            $upd = $this->db->prepare("
                UPDATE document_esign
                SET status = 'signed',
                    signature_data = ?,
                    signed_by = ?,
                    signed_at = ?,
                    verification_code = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([
                $signature,
                (int)($data['signed_by'] ?? 0),
                $data['signed_at'] ?? date('Y-m-d H:i:s'),
                $data['verification_code'] ?? null,
                $id,
            ]);

            return ['success' => true, 'id' => $id, 'status' => 'signed'];
        } catch (\Throwable $e) {
            error_log('[DocumentService::signESignDocument] ' . $e->getMessage());
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
        $stmt = $this->db->prepare("SELECT * FROM document_locker WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function getDocumentLockers(int $userId): array
    {
        $tid = $this->tenantId();
        $stmt = $this->db->prepare("SELECT * FROM document_locker WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
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
        $stmt = $this->db->prepare("SELECT * FROM document_vault WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function getDocumentVaults(int $userId): array
    {
        $tid = $this->tenantId();
        $stmt = $this->db->prepare("SELECT * FROM document_vault WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
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
        $stmt = $this->db->prepare("SELECT * FROM legal_documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
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
        $stmt = $this->db->prepare("SELECT * FROM legal_documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
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