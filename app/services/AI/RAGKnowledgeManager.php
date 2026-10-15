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
            $embedding = $this->generateEmbedding($chunk);
            
            $tid = $this->tenantId();
            $this->db->execute(
                "INSERT INTO ai_knowledge_chunks (knowledge_base_id, chunk_text, embedding, chunk_index, metadata, tenant_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$docId, $chunk, $embedding, $index, json_encode(['doc_id' => $docId]), $tid > 1 ? $tid : 1]
            );
        }
    }

    /**
     * Generate embedding for text using free AI engines
     */
    private function generateEmbedding(string $text): string
    {
        // Try to use a free embedding model via HuggingFace or Together.ai
        // For now, return a simple hash-based placeholder
        // In production, use: sentence-transformers/all-MiniLM-L6-v2 via HF Inference API
        return json_encode([
            'model' => 'hash-placeholder',
            'vector' => array_map(fn($c) => ord($c) / 255.0, str_split(substr(md5($text), 0, 32)))
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
        $results = [];
        foreach ($chunks as $chunk) {
            $chunkEmbedding = json_decode($chunk['embedding'], true);
            $chunkVector = $chunkEmbedding['vector'] ?? [];
            
            $similarity = $this->cosineSimilarity($queryVector, $chunkVector);
            
            if ($similarity > 0.3) { // Threshold
                $results[] = [
                    'chunk_id' => $chunk['id'],
                    'doc_id' => $chunk['knowledge_base_id'],
                    'doc_title' => $chunk['doc_title'],
                    'source' => $chunk['source'],
                    'text' => $chunk['chunk_text'],
                    'score' => $similarity,
                    'metadata' => json_decode($chunk['metadata'] ?? '{}', true),
                ];
            }
        }
        
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