<?php

/**
 * RAGEmbeddingService (Session 341)
 *
 * @deprecated Use App\Services\AI\RAGService instead
 * This class extends RAGService and delegates all method calls to it.
 *
 * PURPOSE
 * A single, provider-agnostic embedding pipeline for the RAG knowledge base.
 * Replaces the single-provider (HF-only) logic that had two bugs:
 *   1. hf_api_key was read as a COLUMN (it lives inside the settings JSON blob)
 *   2. api-inference.huggingface.co is a retired host
 *
 * PROVIDER CHAIN (first success wins)
 *   1. Gemini text-embedding-004  -> FREE tier, 1536-dim, already keyed in DB.
 *      This is the default because it needs no paid subscription.
 *   2. HuggingFace router         -> 384-dim MiniLM. Requires PRO/credits (HTTP 402 otherwise).
 *   3. hash-placeholder-v2        -> last-resort only, ALWAYS logged as a degradation.
 *
 * DESIGN NOTES
 *   - Deterministic model choice per chunk is NOT required: cosineSimilarity()
  *     compares vectors of the same dimension only, and re-embedding normalises the
  *     whole table (see reembedAllChunks) whenever the active model changes.
 *   - Every fallback is logged once per provider per reason so a silent quality
  *     degradation can never hide again (the S337 root-cause lesson).
 *   - getHealth() powers the admin "RAG Health" card (S341 suggestion #3).
 *   - getEmbeddingModel()/setEmbeddingModel() make the model admin-configurable
  *     instead of hardcoded (S341 suggestion #5).
 */

namespace App\Services\AI;

class RAGEmbeddingService extends RAGService
{
    public const MODEL_GEMINI = 'gemini-embedding-001';
    public const MODEL_HF     = 'hf-all-MiniLM-L6-v2';
    public const MODEL_HASH   = 'hash-placeholder-v2';

    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }
}
