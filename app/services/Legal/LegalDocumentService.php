<?php

namespace App\Services\Legal;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\DocumentService as UnifiedDocumentService;
use PDO;

/**
 * LegalDocumentService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\DocumentService instead
 * Legal document service merged into unified DocumentService.
 *
 * The API layer (LegalApiController) calls getDocumentById/getUploads/getDocuments;
 * the unified service only exposes getLegalDocumentById()/getDocuments() with
 * different semantics. These three methods close the gap honestly, reading the
 * LIVE legal_documents schema - no fabricated fields, no rewrites of the parents.
 */
class LegalDocumentService extends UnifiedDocumentService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getDocumentById(int $id): ?array
    {
        return $this->getLegalDocumentById($id);
    }

    /**
     * List legal documents, honoring the filters the API advertises.
     * The table is small (templates + company docs); filter in PHP so we do
     * not fork the parent's query logic a second time. Signature must stay
     * compatible with the parent (LSP): all args honored.
     */
    public function getDocuments(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $docs = $this->getLegalDocuments();
        if (!empty($filters['status'])) {
            $s = strtolower((string)$filters['status']);
            $docs = array_values(array_filter($docs, fn($d) => strtolower((string)($d['status'] ?? '')) === $s));
        }
        if (!empty($filters['customer_id'])) {
            $cid = (int)$filters['customer_id'];
            $docs = array_values(array_filter($docs, fn($d) => (int)($d['customer_id'] ?? 0) === $cid));
        }
        $offset = max(0, (int)$offset);
        $limit = max(1, (int)$limit);
        return array_slice($docs, $offset, $limit);
    }

    /**
     * Uploads attached to a legal document (kyc copies, signed agreements...).
     */
    public function getUploads(int $id): array
    {
        try {
            $tid = 1;
            try { $tid = (int)TenantContext::getId(); } catch (\Throwable) {}
            $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = [$id];
            if ($tid > 1) { $params[] = $tid; }
            $stmt = Database::getInstance()->getConnection()->prepare(
                'SELECT * FROM legal_document_uploads WHERE document_id = ?' . $tSql . ' ORDER BY created_at DESC'
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[LegalDocumentService] getUploads error: ' . $e->getMessage());
            return [];
        }
    }
}
