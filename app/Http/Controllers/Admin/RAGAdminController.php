<?php

namespace App\Http\Controllers\Admin;

use App\Services\AI\RAGEmbeddingService;

/**
 * RAGAdminController (Session 341)
 *
 * Admin surface for the RAG knowledge base:
 *   - health card (provider, dimension, stale-chunk count, key presence)
 *   - embedding-model switch (admin-configurable instead of hardcoded)
 *   - idempotent re-embed trigger
 *   - searchable chunk listing so the owner can see what the AI actually knows
 */
class RAGAdminController extends \App\Http\Controllers\Admin\AdminController
{
    public function index()
    {
        $this->requireAdmin();

        $svc = RAGEmbeddingService::getInstance();
        $health = $svc->getHealth();
        $documents = [];

        try {
            // ai_knowledge_base has no `category` column (verified via DESCRIBE in S341):
            // real columns are id, tenant_id, title, content, source, tags, metadata,
            // is_active, created_at, updated_at. `tags` is the classification field.
            $documents = $this->db->fetchAll(
                "SELECT kb.id, kb.title, kb.tags, kb.source, kb.is_active,
                        COUNT(kc.id) AS chunk_count
                 FROM ai_knowledge_base kb
                 LEFT JOIN ai_knowledge_chunks kc ON kc.knowledge_base_id = kb.id
                 WHERE kb.tenant_id = 1
                 GROUP BY kb.id, kb.title, kb.tags, kb.source, kb.is_active
                 ORDER BY kb.id DESC"
            ) ?: [];
        } catch (\Throwable $e) {
            $this->loggingService->error('RAG admin list failed: ' . $e->getMessage());
            $documents = [];
        }

        return $this->render('admin/rag/index', [
            'page_title' => 'RAG Knowledge Base - APS Dream Home',
            'active_page' => 'rag',
            'health' => $health,
            'documents' => $documents,
            'csrf' => $_SESSION['csrf_token'] ?? '',
        ]);
    }

    /**
     * Switch the embedding provider/model and (optionally) re-embed immediately.
     */
    public function setModel()
    {
        $this->requireAdmin();

        try {
            $this->validateCsrfOrFail();
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF failed'], 419);
            return;
        }

        $model = trim((string) ($_POST['model'] ?? ''));
        $svc = RAGEmbeddingService::getInstance();

        if (!$svc->setEmbeddingModel($model)) {
            $this->jsonResponse(['success' => false, 'message' => 'Could not persist model'], 500);
            return;
        }

        $result = null;
        if (!empty($_POST['reembed'])) {
            $result = $svc->reembedAllChunks((string) ($_POST['mode'] ?? '') === 'force');
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Embedding model set to ' . $model,
            'reembed' => $result,
            'health' => $svc->getHealth(),
        ]);
    }

    /**
     * Trigger a re-embed without changing the model.
     */
    public function reembed()
    {
        $this->requireAdmin();

        try {
            $this->validateCsrfOrFail();
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'CSRF failed'], 419);
            return;
        }

        $force = ($_POST['mode'] ?? '') === 'force';
        $svc = RAGEmbeddingService::getInstance();
        $result = $svc->reembedAllChunks($force);

        $this->jsonResponse([
            'success' => $result['failed'] === 0,
            'message' => sprintf(
                'Re-embedded %d of %d chunk(s), %d skipped, %d failed (provider: %s)',
                $result['reembedded'],
                $result['total'],
                $result['skipped'],
                $result['failed'],
                $result['active_provider']
            ),
            'result' => $result,
            'health' => $svc->getHealth(),
        ]);
    }
}