<?php
/**
 * RAGKnowledgeManager - Manage knowledge base for RAG (Retrieval-Augmented Generation)
 *
 * @deprecated Use App\Services\AI\RAGService instead
 * This class extends RAGService and delegates all method calls to it.
 */

namespace App\Services\AI;

class RAGKnowledgeManager extends RAGService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
