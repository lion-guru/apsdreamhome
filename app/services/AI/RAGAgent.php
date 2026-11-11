<?php
/**
 * RAGAgent - Retrieval-Augmented Generation for APS Dream Home
 *
 * @deprecated Use App\Services\AI\RAGService instead
 * This class extends RAGService and delegates all method calls to it.
 *
 * No external API needed. Uses:
 * - TF-IDF keyword matching for retrieval
 * - Live property/plot/colony data from DB
 * - Knowledge base from ai_knowledge_base
 * - Conversation context for follow-ups
 *
 * "Chota packet bada dhamaka" — small code, huge value
 */

namespace App\Services\AI;

class RAGAgent extends RAGService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
