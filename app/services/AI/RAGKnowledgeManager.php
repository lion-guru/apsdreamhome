<?php
/**
 * RAGKnowledgeManager - Manage knowledge base for RAG (Retrieval-Augmented Generation)
 * Handles document upload, chunking, embedding, and retrieval
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

class RAGKnowledgeManager
{
    use ServiceTenantTrait;

    private static $instance = null;
    private $db;
    private $freeEngines;

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->freeEngines = FreeAIEngines::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add a document to the knowledge base
     * @param array $data ['title', 'content', 'source', 'tags', 'metadata']
     * @return int document ID
     */
    public function addDocument(array $data): int
    {
        $tid = $this->tenantId();
        
        $columns = ['title', 'content', 'source', 'tags', 'metadata', 'tenant_id', 'created_at', 'updated_at'];
        $values = [
            $data['title'] ?? '',
            $data['content'] ?? '',
            $data['source'] ?? 'manual',
            json_encode($data['tags'] ?? []),
            json_encode($data['metadata'] ?? []),
            $tid > 1 ? $tid : 1,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ];
        
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        
        $this->db->execute("INSERT INTO ai_knowledge_base ($colStr) VALUES ($placeholders)", $values);
        $docId = (int)$this->db->lastInsertId();
        
        // Chunk and embed the document
        $this->chunkAndEmbedDocument($docId, $data['content'] ?? '');
        
        return $docId;
    }

    /**
     * Chunk document and create embeddings
     */
    private function chunkAndEmbedDocument(int $docId, string $content): void
    {
        $chunks = $this->chunkText($content, 1000, 200); // 1000 chars with 200 overlap
        
        foreach ($chunks as $index => $chunk) {
            $embedding = $this->generateEmbedding($chunk, $docId, $index);
            
            $tid = $this->tenantId();
            $this->db->execute(
                "INSERT INTO ai_knowledge_chunks (knowledge_base_id, chunk_text, embedding, chunk_index, metadata, tenant_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$docId, $chunk, $embedding, $index, json_encode(['doc_id' => $docId]), $tid > 1 ? $tid : 1]
            );
        }
    }

    /**
     * Generate embedding for text using HuggingFace Inference API (free tier: 30k tokens/day)
     * Uses sentence-transformers/all-MiniLM-L6-v2 (384-dim embeddings)
     */
    private function generateEmbedding(string $text, ?int $docId = null, ?int $chunkIndex = null): string
    {
        // S341: delegate to RAGEmbeddingService — a provider chain (Gemini free ->
        // HF PRO -> hash-placeholder) with logging, health reporting and re-embed.
        // The old inline HF-only implementation had the two S337 bugs and fell back
        // to hash-placeholder silently.
        return \App\Services\AI\RAGEmbeddingService::getInstance()->embed($text);
    }

    /**
     * Generate improved hash-placeholder embedding with unique vectors per document/chunk
     * Uses SHA256 with document/chunk context for unique 384-dim vectors
     */
    private function generateHashPlaceholderEmbedding(string $text, ?int $docId = null, ?int $chunkIndex = null): string
    {
        // Create unique seed per document/chunk for unique but deterministic vectors
        $seed = $text;
        if ($docId !== null) {
            $seed .= "|doc:$docId";
        }
        if ($chunkIndex !== null) {
            $seed .= "|chunk:$chunkIndex";
        }
        
        // Use SHA256 for better distribution (64 hex chars = 256 bits)
        $hash = hash('sha256', $seed);
        
        // Generate 384-dim vector from hash (expand to 384 dimensions)
        $vector = [];
        $hashBytes = hex2bin($hash); // 32 bytes = 256 bits
        
        // Expand 32 bytes to 384 dimensions using multiple hash passes
        for ($pass = 0; $pass < 12; $pass++) { // 32 bytes * 12 = 384 dimensions
            $passHash = hash('sha256', $hash . "|pass:$pass");
            $passBytes = hex2bin($passHash);
            for ($i = 0; $i < 32; $i++) {
                $vector[] = ord($passBytes[$i]) / 255.0;
            }
        }
        
        // Normalize vector to unit length
        $norm = sqrt(array_sum(array_map(fn($x) => $x * $x, $vector)));
        if ($norm > 0) {
            $vector = array_map(fn($x) => $x / $norm, $vector);
        }
        
        return json_encode([
            'model' => 'hash-placeholder-v2',
            'vector' => $vector
        ]);
    }

    /**
     * Chunk text with overlap
     */
    private function chunkText(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        $text = trim($text);
        if (strlen($text) <= $chunkSize) return [$text];
        
        $chunks = [];
        $start = 0;
        
        while ($start < strlen($text)) {
            $end = min($start + $chunkSize, strlen($text));
            
            // Try to break at sentence boundary
            if ($end < strlen($text)) {
                $lastPeriod = strrpos(substr($text, $start, $chunkSize), '.');
                $lastNewline = strrpos(substr($text, $start, $chunkSize), "\n");
                $breakPoint = max($lastPeriod, $lastNewline);
                if ($breakPoint !== false && $breakPoint > $chunkSize * 0.5) {
                    $end = $start + $breakPoint + 1;
                }
            }
            
            $chunks[] = trim(substr($text, $start, $end - $start));
            $start = $end - $overlap;
            if ($start >= strlen($text)) break;
        }
        
        return array_filter($chunks, fn($c) => strlen(trim($c)) > 50);
    }

    /**
     * Search knowledge base for relevant chunks
     * @param string $query
     * @param int $limit
     * @return array of matching chunks with scores
     */
    public function search(string $query, int $limit = 5): array
    {
        $tid = $this->tenantId();
        
        // Get all chunks for this tenant
        $chunks = $this->db->fetchAll(
            "SELECT kc.*, kb.title as doc_title, kb.source
             FROM ai_knowledge_chunks kc
             JOIN ai_knowledge_base kb ON kc.knowledge_base_id = kb.id
             WHERE kb.tenant_id = " . ($tid > 1 ? $tid : 1) . " AND kb.is_active = 1",
            []
        ) ?: [];
        
        if (empty($chunks)) return [];
        
        // Generate query embedding
        $queryEmbedding = json_decode($this->generateEmbedding($query), true);
        $queryVector = $queryEmbedding['vector'] ?? [];
        
        // Score chunks by cosine similarity
        // S341: dimension guard — a query vector must never be compared against a
        // chunk vector of a different length (e.g. Gemini 1536-dim query vs a stale
        // hash-placeholder 384-dim chunk). Skipping is correct; comparing would
        // produce meaningless scores and silently pollute results.
        $queryDim = count($queryVector);
        $results = [];
        $dimMismatch = 0;
        foreach ($chunks as $chunk) {
            $chunkEmbedding = json_decode((string)($chunk['embedding'] ?? ''), true);
            $chunkVector = $chunkEmbedding['vector'] ?? [];

            if (!is_array($chunkVector) || $queryDim === 0 || count($chunkVector) !== $queryDim) {
                $dimMismatch++;
                continue;
            }

            $similarity = $this->cosineSimilarity($queryVector, $chunkVector);
            
            if ($similarity > 0.3) { // Threshold
                $docId = $chunk['knowledge_base_id'];
                $key = "doc_$docId";
                
                // Keep only the best chunk per document
                if (!isset($results[$key]) || $similarity > $results[$key]['score']) {
                    $results[$key] = [
                        'chunk_id' => $chunk['id'],
                        'doc_id' => $docId,
                        'doc_title' => $chunk['doc_title'],
                        'source' => $chunk['source'],
                        'text' => $chunk['chunk_text'],
                        'score' => $similarity,
                        'metadata' => json_decode($chunk['metadata'] ?? '{}', true),
                    ];
                }
            }
        }
        
        // Convert back to array and sort by score
        $results = array_values($results);
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice($results, 0, $limit);
    }

    /**
     * Cosine similarity between two vectors
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) return 0.0;
        
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        
        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }
        
        if ($normA == 0 || $normB == 0) return 0.0;
        
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Get all documents for a tenant
     */
    public function getDocuments(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll(
            "SELECT * FROM ai_knowledge_base WHERE tenant_id = " . ($tid > 1 ? $tid : 1) . " AND is_active = 1 ORDER BY created_at DESC",
            []
        ) ?: [];
    }

    /**
     * Delete a document and its chunks
     */
    public function deleteDocument(int $docId): bool
    {
        $tid = $this->tenantId();
        $this->db->execute(
            "DELETE FROM ai_knowledge_chunks WHERE knowledge_base_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$docId], $tid > 1 ? [$tid] : [])
        );
        $this->db->execute(
            "DELETE FROM ai_knowledge_base WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$docId], $tid > 1 ? [$tid] : [])
        );
        return true;
    }

    /**
     * Re-index all documents (rebuild embeddings)
     */
    public function reindexAll(): int
    {
        $tid = $this->tenantId();
        $docs = $this->db->fetchAll(
            "SELECT id, content FROM ai_knowledge_base WHERE tenant_id = " . ($tid > 1 ? $tid : 1),
            []
        ) ?: [];
        
        $count = 0;
        foreach ($docs as $doc) {
            $this->db->execute(
                "DELETE FROM ai_knowledge_chunks WHERE knowledge_base_id = ?",
                [$doc['id']]
            );
            $this->chunkAndEmbedDocument($doc['id'], $doc['content']);
            $count++;
        }
        
        return $count;
    }
}