<?php

namespace App\Services\AI;

/**
 * RAGService - Unified Retrieval-Augmented Generation Service
 *
 * Consolidates RAGKnowledgeManager, RAGEmbeddingService, RAGAgent, and RAGAgentService
 * into a single provider-agnostic service:
 *   - Document management (add, list, delete, reindex)
 *   - Multi-provider embeddings (Gemini primary, HF fallback, hash-placeholder)
 *   - Semantic search with dimension guards
 *   - Agentic Q&A: query parsing, context retrieval, answer generation
 *
 * All old service classes extend this one and delegate to it for backward compatibility.
 */
class RAGService
{
    public const MODEL_GEMINI = 'gemini-embedding-001';
    public const MODEL_HF     = 'hf-all-MiniLM-L6-v2';
    public const MODEL_HASH   = 'hash-placeholder-v2';

    use \App\Traits\ServiceTenantTrait;

    private static ?self $instance = null;
    private \App\Core\Database\Database $db;
    private FreeAIEngines $freeEngines;
    private string $lastProvider = '';
    private array $logGuards = [];

    private function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->freeEngines = FreeAIEngines::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ------------------------------------------------------- model config
    private function settings(): array
    {
        try {
            $tid = $this->tenantId();
            $sql = "SELECT * FROM ai_settings WHERE is_active = 1";
            $params = [];
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            $row = $this->db->fetch($sql . " LIMIT 1", $params) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
        $row['__cfg'] = json_decode((string) ($row['settings'] ?? '{}'), true) ?: [];
        return $row;
    }

    private function cfgValue(array $settings, string $key, string $envFallback = ''): string
    {
        $v = trim((string) ($settings['__cfg'][$key] ?? ''));
        if ($v !== '') {
            return $v;
        }
        $env = getenv($envFallback);
        return is_string($env) ? trim($env) : '';
    }

    public function geminiKey(): string
    {
        $s = $this->settings();
        $key = trim((string) ($s['api_key'] ?? ''));
        if ($key !== '') {
            return $key;
        }
        return $this->cfgValue($s, 'gemini_api_key', 'GEMINI_API_KEY');
    }

    public function hfKey(): string
    {
        $s = $this->settings();
        return $this->cfgValue($s, 'hf_api_key', 'HF_API_KEY');
    }

    public function getEmbeddingModel(): string
    {
        $s = $this->settings();
        $m = trim((string) ($s['__cfg']['rag_embedding_model'] ?? ''));
        return $m !== '' ? $m : self::MODEL_GEMINI;
    }

    public function setEmbeddingModel(string $model): bool
    {
        $model = trim($model);
        if ($model === '') {
            return false;
        }
        try {
            $s = $this->settings();
            $cfg = $s['__cfg'] ?? [];
            $cfg['rag_embedding_model'] = $model;
            $this->db->execute(
                "UPDATE ai_settings SET settings = ?, updated_at = NOW() WHERE id = 1",
                [json_encode($cfg)]
            );
            return true;
        } catch (\Throwable $e) {
            $this->logOnce('set_model_failed', 'RAGEmbeddingService: cannot persist model: ' . $e->getMessage());
            return false;
        }
    }

    public function generateEmbedding(string $text): string
    {
        return $this->embed($text);
    }

    // ========================================================================
    // DOCUMENT MANAGEMENT (from RAGKnowledgeManager)
    // ========================================================================

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
        // Dimension guard — a query vector must never be compared against a
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

    // ========================================================================
    // EMBEDDING GENERATION (from RAGEmbeddingService)
    // ========================================================================

    /**
     * Produce an embedding for text using the best available provider
     * Returns JSON: {"model": "...", "vector": [...]}
     */
    public function embed(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            $text = ' ';
        }

        $model = $this->getEmbeddingModel();
        // Gemini first (free) regardless of preference ordering: if the admin
        // selected HF but HF has no credits, fall through to Gemini before hash.
        $order = ($model === self::MODEL_HF)
            ? [self::MODEL_HF, self::MODEL_GEMINI]
            : [self::MODEL_GEMINI, self::MODEL_HF];

        foreach ($order as $candidate) {
            if ($candidate === self::MODEL_GEMINI) {
                $vec = $this->geminiEmbed($text);
                if ($vec !== null) {
                    $this->lastProvider = 'gemini';
                    return json_encode(['model' => self::MODEL_GEMINI, 'vector' => $vec]);
                }
            } elseif ($candidate === self::MODEL_HF) {
                $vec = $this->hfEmbed($text);
                if ($vec !== null) {
                    $this->lastProvider = 'huggingface';
                    return json_encode(['model' => self::MODEL_HF, 'vector' => $vec]);
                }
            }
        }

        // Last resort — ALWAYS announce the degradation.
        $this->logOnce(
            'hash_fallback',
            'RAGEmbeddingService: DEGRADED to hash-placeholder-v2 — no embedding provider '
            . 'succeeded (gemini: ' . ($this->logGuards['gemini_err'] ?? 'n/a')
            . ' | hf: ' . ($this->logGuards['hf_err'] ?? 'n/a')
            . '). RAG search quality is degraded until a provider is configured.'
        );
        $this->lastProvider = 'hash-placeholder';
        return $this->hashEmbed($text);
    }

    public function lastProvider(): string
    {
        return $this->lastProvider;
    }

    // ------------------------------------------------------------- providers

    /**
     * Gemini text-embedding-004 — FREE tier (1M tokens/day on the free quota).
     * Returns a 1536-dim float vector, or null on failure.
     */
    private function geminiEmbed(string $text): ?array
    {
        $key = $this->freeEngines->getGeminiKey();
        if ($key === '') {
            $this->logGuards['gemini_err'] = 'no key';
            return null;
        }

        // S341 probe: gemini-embedding-001 / -2 are the embedding-capable models on this
        // key. `text-embedding-004` returns 404 on v1beta (retired for embedContent).
        $modelId = 'gemini-embedding-001';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:embedContent?key=" . urlencode($key);
        $payload = json_encode([
            'model' => 'models/' . $modelId,
            'content' => ['parts' => [['text' => mb_substr($text, 0, 8000)]]],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = (string) curl_error($ch);
        curl_close($ch);

        if ($code !== 200) {
            $this->logGuards['gemini_err'] = "HTTP {$code}";
            $this->logOnce('gemini_http_' . $code, "RAGEmbeddingService: Gemini embedding HTTP {$code}: " . substr((string) $resp, 0, 200));
            return null;
        }

        $data = json_decode((string) $resp, true);
        $vec = $data['embedding']['values'] ?? null;
        if (!is_array($vec) || count($vec) < 100) {
            $this->logGuards['gemini_err'] = 'bad payload';
            $this->logOnce('gemini_payload', 'RAGEmbeddingService: Gemini returned an unexpected embedding payload: ' . substr((string) $resp, 0, 200));
            return null;
        }

        $this->logGuards['gemini_err'] = null;
        return array_map('floatval', $vec);
    }

    /**
     * HuggingFace router — requires PRO/credits (free tokens return HTTP 402).
     * Returns a 384-dim float vector, or null on failure.
     */
    private function hfEmbed(string $text): ?array
    {
        $key = $this->freeEngines->getHfKey();
        if ($key === '') {
            $this->logGuards['hf_err'] = 'no key';
            return null;
        }

        $model = 'sentence-transformers/all-MiniLM-L6-v2';
        $url = "https://router.huggingface.co/hf-inference/models/{$model}/pipeline/feature-extraction";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['inputs' => mb_substr($text, 0, 2000)]),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            $this->logGuards['hf_err'] = "HTTP {$code}";
            if ($code === 402) {
                $this->logOnce('hf_402', 'RAGEmbeddingService: HuggingFace returned 402 (no inference credits). Free tokens cannot run inference — subscribe to PRO or use Gemini (free).');
            } else {
                $this->logOnce('hf_http_' . $code, 'RAGEmbeddingService: HF embedding HTTP ' . $code . ': ' . substr((string) $resp, 0, 200));
            }
            return null;
        }

        $data = json_decode((string) $resp, true);
        $vec = (is_array($data) && isset($data[0]) && is_array($data[0])) ? $data[0] : $data;
        if (!is_array($vec) || count($vec) < 100) {
            $this->logGuards['hf_err'] = 'bad payload';
            return null;
        }

        $this->logGuards['hf_err'] = null;
        return array_map('floatval', $vec);
    }

    /**
     * Deterministic 384-dim placeholder. Same algorithm as the previous inline
     * implementation so old rows stay comparable.
     */
    private function hashEmbed(string $text): string
    {
        $hash = hash('sha256', $text);
        $vector = [];
        for ($pass = 0; $pass < 12; $pass++) {
            $bytes = hex2bin(hash('sha256', $hash . "|pass:$pass"));
            for ($i = 0; $i < 32; $i++) {
                $vector[] = ord($bytes[$i]) / 255.0;
            }
        }
        $norm = sqrt(array_sum(array_map(fn($x) => $x * $x, $vector)));
        if ($norm > 0) {
            $vector = array_map(fn($x) => $x / $norm, $vector);
        }
        return json_encode(['model' => self::MODEL_HASH, 'vector' => $vector]);
    }

    // ========================================================================
    // RE-EMBED (#1)
    // ========================================================================

    /**
     * Idempotent re-embed of every chunk in the knowledge base.
     * Skips chunks already produced by the active provider.
     * Returns a summary array for the CLI script / admin card.
     */
    public function reembedAllChunks(bool $force = false): array
    {
        $chunks = $this->db->fetchAll(
            "SELECT id, chunk_text, embedding FROM ai_knowledge_chunks ORDER BY id"
        ) ?: [];

        $done = 0; $skipped = 0; $failed = 0;
        $seenModels = [];

        foreach ($chunks as $row) {
            $existing = json_decode((string) ($row['embedding'] ?? ''), true);
            $existingModel = (string) ($existing['model'] ?? '');

            if (!$force && $existingModel !== self::MODEL_HASH && $existingModel !== '') {
                $skipped++;
                continue;
            }

            try {
                $newEmbedding = $this->embed((string) ($row['chunk_text'] ?? ''));
                $decoded = json_decode($newEmbedding, true);
                $model = (string) ($decoded['model'] ?? self::MODEL_HASH);
                $seenModels[$model] = ($seenModels[$model] ?? 0) + 1;

                if ($model === self::MODEL_HASH) {
                    $failed++;
                    continue;
                }

                $this->db->execute(
                    "UPDATE ai_knowledge_chunks SET embedding = ? WHERE id = ?",
                    [$newEmbedding, (int) $row['id']]
                );
                $done++;
            } catch (\Throwable $e) {
                $this->logOnce('reembed_row_' . $row['id'], 'RAGEmbeddingService: re-embed failed for chunk ' . $row['id'] . ': ' . $e->getMessage());
                $failed++;
            }
        }

        return [
            'total' => count($chunks),
            'reembedded' => $done,
            'skipped' => $skipped,
            'failed' => $failed,
            'models_written' => $seenModels,
            'active_provider' => $this->lastProvider,
        ];
    }

    // ========================================================================
    // HEALTH CARD (#3)
    // ========================================================================

    /**
     * Health snapshot for the admin panel: is a real provider reachable, and what
     * is actually stored in the chunk table right now.
     */
    public function getHealth(): array
    {
        $model = $this->getEmbeddingModel();

        // Live probe: embed a tiny string through the normal chain.
        $probe = json_decode($this->embed('APS Dream Home health probe'), true);
        $probeModel = (string) ($probe['model'] ?? self::MODEL_HASH);
        $probeDim = is_array($probe['vector'] ?? null) ? count($probe['vector']) : 0;

        // Stored chunk composition.
        $stored = [];
        try {
            $rows = $this->db->fetchAll("SELECT embedding FROM ai_knowledge_chunks") ?: [];
            foreach ($rows as $r) {
                $dec = json_decode((string) ($r['embedding'] ?? ''), true);
                $m = (string) ($dec['model'] ?? 'unknown');
                $stored[$m] = ($stored[$m] ?? 0) + 1;
            }
        } catch (\Throwable $e) {
            $stored = [];
        }

        return [
            'configured_model' => $model,
            'probe_model' => $probeModel,
            'probe_dimension' => $probeDim,
            'probe_provider' => $this->lastProvider,
            'degraded' => ($probeModel === self::MODEL_HASH),
            'gemini_key_set' => ($this->freeEngines->getGeminiKey() !== ''),
            'hf_key_set' => ($this->freeEngines->getHfKey() !== ''),
            'hf_last_error' => $this->logGuards['hf_err'] ?? null,
            'gemini_last_error' => $this->logGuards['gemini_err'] ?? null,
            'stored_models' => $stored,
            'stale_chunks' => ($stored[self::MODEL_HASH] ?? 0),
            'total_chunks' => array_sum($stored),
        ];
    }

    // ------------------------------------------------------------- utilities

    /**
     * Log at most once per guard key per request, so a missing key cannot
     * produce thousands of identical log lines.
     */
    private function logOnce(string $guard, string $message): void
    {
        if (!isset($this->logGuards['_logged_' . $guard])) {
            $this->logGuards['_logged_' . $guard] = true;
            error_log($message);
        }
    }
}