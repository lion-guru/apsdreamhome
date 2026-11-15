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
    public const MODEL_OPENROUTER = 'openrouter-text-embedding-3-small';

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

    public function openRouterKey(): string
    {
        $s = $this->settings();
        $key = $this->cfgValue($s, 'openrouter_api_key', 'OPENROUTER_API_KEY');
        return $key;
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
            // Probe the chosen provider FIRST — never persist a switch the
            // corpus cannot answer against. hash-placeholder is exempt (it
            // needs no external call) but it still works as a veto: the
            // health desk already shows it as degraded.
            if ($model !== self::MODEL_HASH) {
                $probe = $this->probeModel($model);
                if (!$probe['ok']) {
                    $this->logOnce('set_model_probe', 'RAGEmbeddingService: refusing to persist model ' . $model . ' — provider probe failed: ' . $probe['reason']);
                    return false;
                }
            }
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

    /**
     * Probe a specific provider with a tiny string; return ok+reason.
     * Public so the admin "save and switch" path can show WHY a provider is not selectable.
     */
    public function probeModel(string $model): array
    {
        $model = trim($model);
        if ($model === self::MODEL_HASH) {
            return ['ok' => true, 'reason' => 'hash-placeholder is deterministic (no external call)'];
        }
        try {
            $vec = match ($model) {
                self::MODEL_GEMINI => $this->geminiEmbed('APS Dream Home RAG health probe'),
                self::MODEL_OPENROUTER => $this->openRouterEmbed('APS Dream Home RAG health probe'),
                self::MODEL_HF => $this->hfEmbed('APS Dream Home RAG health probe'),
                default => null,
            };
        } catch (\Throwable $e) {
            return ['ok' => false, 'reason' => $e->getMessage()];
        }
        if (is_array($vec) && count($vec) > 0) {
            return ['ok' => true, 'reason' => 'provider answered'];
        }
        $verdict = match ($model) {
            self::MODEL_GEMINI => $this->logGuards['gemini_err'] ?? 'unknown',
            self::MODEL_OPENROUTER => $this->logGuards['openrouter_err'] ?? 'unknown',
            self::MODEL_HF => $this->logGuards['hf_err'] ?? 'unknown',
            default => 'unknown model',
        };
        return ['ok' => false, 'reason' => (string) $verdict];
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
     * @param array $data ['title', 'content', 'source', 'tags', 'metadata', 'visibility']
     * @return int document ID
     */
    public function addDocument(array $data): int
    {
        $tid = $this->tenantId();
        $visibility = $data['visibility'] ?? 'admin';
        if (!in_array($visibility, ['public', 'staff', 'admin'], true)) {
            $visibility = 'admin';
        }

        $columns = ['title', 'content', 'source', 'tags', 'metadata', 'visibility', 'tenant_id', 'created_at', 'updated_at'];
        $values = [
            $data['title'] ?? '',
            $data['content'] ?? '',
            $data['source'] ?? 'manual',
            json_encode($data['tags'] ?? []),
            json_encode($data['metadata'] ?? []),
            $visibility,
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
            // Dual-write both provider vectors when both providers answer:
            // `embedding` holds the primary (gemini preferred, 3072-dim), `embedding_openrouter`
            // ALWAYS holds the openrouter vector (1536-dim) when available — so the corpus
            // stays searchable even when the primary provider's daily quota is dead.
            $geminiVec = $this->geminiEmbed($chunk);
            $openrouterVec = $this->openRouterEmbed($chunk);

            if ($geminiVec !== null) {
                $embedding = json_encode(['model' => self::MODEL_GEMINI, 'vector' => $geminiVec]);
            } elseif ($openrouterVec !== null) {
                $embedding = json_encode(['model' => self::MODEL_OPENROUTER, 'vector' => $openrouterVec]);
            } else {
                $embedding = $this->hashEmbed($chunk);
            }
            $embeddingOpenrouter = $openrouterVec !== null
                ? json_encode(['model' => self::MODEL_OPENROUTER, 'vector' => $openrouterVec])
                : null;

            $tid = $this->tenantId();
            $this->db->execute(
                "INSERT INTO ai_knowledge_chunks (knowledge_base_id, chunk_text, embedding, embedding_openrouter, chunk_index, metadata, tenant_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [$docId, $chunk, $embedding, $embeddingOpenrouter, $index, json_encode(['doc_id' => $docId]), $tid > 1 ? $tid : 1]
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
     * Chunk text with overlap — ALL offsets use multibyte-safe functions.
     * Byte-level substr() would slice a multibyte character in half and corrupt
     * the UTF-8 sequence (\x80\x94… → "Incorrect string value" on INSERT).
     */
    private function chunkText(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        $text = trim($text);
        $len = mb_strlen($text, 'UTF-8');
        if ($len <= $chunkSize) return [$text];

        $chunks = [];
        $start = 0;

        while ($start < $len) {
            $end = min($start + $chunkSize, $len);

            // Try to break at sentence boundary
            if ($end < $len) {
                $window = mb_substr($text, $start, $chunkSize, 'UTF-8');
                $lastPeriod = mb_strrpos($window, '.', 0, 'UTF-8');
                $lastNewline = mb_strrpos($window, "\n", 0, 'UTF-8');
                if ($lastPeriod === false && $lastNewline === false) {
                    // nothing helpful — break on whitespace as a fallback
                    $breakPoint = mb_strrpos($window, ' ', 0, 'UTF-8');
                } else {
                    $breakPoint = max($lastPeriod === false ? -1 : $lastPeriod, $lastNewline === false ? -1 : $lastNewline);
                }
                if ($breakPoint !== false && $breakPoint > $chunkSize * 0.5) {
                    $end = $start + $breakPoint + 1;
                }
            }

            // Guarantee progress — $end must always advance past the previous start.
            if ($end <= $start) {
                $end = min($start + $chunkSize, $len);
            }

            $chunks[] = trim(mb_substr($text, $start, $end - $start, 'UTF-8'));

            $nextStart = $end - $overlap;
            $start = max($nextStart, $start + 1);
            if ($start >= $len) break;
        }

        return array_filter($chunks, fn($c) => mb_strlen(trim($c), 'UTF-8') > 50);
    }

    /**
     * Search knowledge base for relevant chunks
     * @param string $query
     * @param int $limit
     * @param string $visibility 'public'|'staff'|'admin' — RBAC scope. Public visitors/staff
     *                            must never retrieve internal admin documents; admins see all.
     * @return array of matching chunks with scores
     */
    public function search(string $query, int $limit = 5, string $visibility = 'admin'): array
    {
        $tid = $this->tenantId();

        $visSql = match ($visibility) {
            'public' => "AND kb.visibility = 'public'",
            'staff'  => "AND kb.visibility IN ('public','staff')",
            default  => '', // admin/super_admin — no restriction
        };

        // Get all chunks for this tenant + visibility scope
        $chunks = $this->db->fetchAll(
            "SELECT kc.*, kb.title as doc_title, kb.source, kb.visibility
             FROM ai_knowledge_chunks kc
             JOIN ai_knowledge_base kb ON kc.knowledge_base_id = kb.id
             WHERE kb.tenant_id = " . ($tid > 1 ? $tid : 1) . " AND kb.is_active = 1 " . $visSql,
            []
        ) ?: [];

        if (empty($chunks)) return [];

        // Generate query embedding
        $queryEmbedding = json_decode($this->generateEmbedding($query), true);
        $queryVector = $queryEmbedding['vector'] ?? [];

        // Degraded mode: the query vector is hash-placeholder. It cannot honestly
        // rank the 949 Gemini 3072-dim chunks, and ranking the 3 remaining hash
        // chunks would still be meaningless (any two hash vectors score high
        // against each other). Prefer lexical ranking — at least it is evidence-based.
        if ((string)($queryEmbedding['model'] ?? '') === self::MODEL_HASH) {
            $kw = $this->keywordSearch($query, $limit, $visSql, $tid);
            if ($kw !== []) return $kw;
            // fall through to vector path if lexical found nothing
        }

        // Score chunks by cosine similarity. Provider routing: the column read
        // matches whichever provider answered the query embed — Gemini/HF/hash
        // vectors live in `embedding`, OpenRouter vectors live in
        // `embedding_openrouter` (during dual-write periods the two corpora may
        // coexist). Dimension guard still applies per column.
        $queryModel = (string)($queryEmbedding['model'] ?? '');
        $chunkColumn = ($queryModel === self::MODEL_OPENROUTER) ? 'embedding_openrouter' : 'embedding';

        $queryDim = count($queryVector);
        $results = [];
        $dimMismatch = 0;
        foreach ($chunks as $chunk) {
            $chunkEmbedding = json_decode((string)($chunk[$chunkColumn] ?? ''), true);
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

        // Sort by score
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        $out = array_slice($results, 0, $limit);

        // Keyword fallback — when every chunk was skipped by the dimension guard
        // (e.g. hash-placeholder query vector vs 3072-dim Gemini corpus), a vector
        // search returns literally nothing and every AI path degrades to an empty
        // answer. Recover with a bound-parameter lexical match instead of lying.
        if ($out === [] && $dimMismatch > 0) {
            $out = $this->keywordSearch($query, $limit, $visSql, $tid);
        }

        return $out;
    }

    /**
     * Lexical fallback used when vector search is unusable through no fault of
     * the caller — e.g. the provider probe fell back to hash-placeholder vectors
     * while the corpus stores Gemini 3072-dim vectors. Returns the same shape as
     * the vector path, marked with fallback='keyword'.
     */
    private function keywordSearch(string $query, int $limit, string $visSql, int $tid): array
    {
        $terms = preg_split('/[^\\p{L}\\p{N}]+/u', mb_strtolower($query)) ?: [];
        $stop  = ['the', 'and', 'for', 'with', 'this', 'that', 'you', 'your', 'from', 'into', 'what', 'how', 'why', 'when', 'kya', 'hai', 'hain', 'ka', 'ki', 'ke', 'ko', 'se'];
        $terms = array_values(array_unique(array_filter($terms, fn($t) => mb_strlen($t, 'UTF-8') >= 3 && !in_array($t, $stop, true))));
        if ($terms === []) return [];

        try {
            $where = [];
            $params = [];
            foreach ($terms as $t) {
                $where[] = '(kc.chunk_text LIKE ? OR kb.title LIKE ?)';
                $params[] = '%' . $t . '%';
                $params[] = '%' . $t . '%';
            }
            $sql = "SELECT kc.id AS chunk_id, kc.knowledge_base_id AS doc_id, kb.title AS doc_title, kb.source, kc.chunk_text AS text
                    FROM ai_knowledge_chunks kc
                    JOIN ai_knowledge_base kb ON kc.knowledge_base_id = kb.id
                    WHERE kb.tenant_id = " . ($tid > 1 ? $tid : 1) . " AND kb.is_active = 1 " . $visSql
                    . " AND (" . implode(' OR ', $where) . ") LIMIT 400";

            $rows = $this->db->fetchAll($sql, $params) ?: [];
            $scored = [];
            foreach ($rows as $r) {
                $hay = mb_strtolower((string)($r['text'] ?? '') . ' ' . (string)($r['doc_title'] ?? ''), 'UTF-8');
                $hits = 0;
                foreach ($terms as $t) { if (mb_strpos($hay, $t, 0, 'UTF-8') !== false) $hits++; }
                if ($hits === 0) continue;
                $r['hits'] = $hits;
                $r['score'] = round(0.3 + 0.15 * min($hits, 3), 4);
                $r['fallback'] = 'keyword';
                $docId = (int)($r['doc_id'] ?? 0);
                $key = "doc_$docId";
                if (!isset($scored[$key]) || $hits > $scored[$key]['hits']) {
                    $scored[$key] = $r;
                }
            }
            usort($scored, fn($a, $b) => $b['hits'] <=> $a['hits']);
            return array_slice(array_map(fn($r) => [
                'chunk_id' => $r['chunk_id'],
                'doc_id' => (int)$r['doc_id'],
                'doc_title' => $r['doc_title'],
                'source' => $r['source'],
                'text' => $r['text'],
                'score' => $r['score'],
                'metadata' => ['fallback' => 'keyword', 'matched_terms' => $r['hits']],
            ], $scored), 0, $limit);
        } catch (\Throwable $e) {
            error_log('RAGService::keywordSearch failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Agentic Q&A entry point — retrieves relevant knowledge-base chunks (RBAC-scoped)
     * and a matching live colony (if any colony is named), then asks a free AI engine
     * to compose a grounded answer. Never fabricates: if nothing is retrieved and no
     * colony matches, returns success=false so callers can fall through to the next layer.
     *
     * @param string $query    User's question
     * @param int    $userId   Authenticated user id (0 = guest) — used only for logging
     * @param string $visibility 'public'|'staff'|'admin' — passed through to search()
     * @return array {
     *   success, answer, confidence, sources: [{type,title,score}], session_id
     * }
     */
    public function answer(string $query, int $userId = 0, string $visibility = 'admin'): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['success' => false, 'answer' => '', 'confidence' => 0.0, 'sources' => [], 'session_id' => $userId ? 'u' . $userId : 'guest'];
        }

        $chunks = $this->search($query, 4, $visibility);
        $colony = null;
        try {
            $colony = $this->findRelevantColony($query);
        } catch (\Throwable $e) {
            error_log('RAGService::answer colony lookup failed: ' . $e->getMessage());
        }

        $sources = [];
        $contextBlocks = [];
        foreach ($chunks as $c) {
            $sources[] = [
                'type' => 'kb',
                'title' => 'Knowledge Base: ' . (string)($c['doc_title'] ?? ''),
                'score' => (float)($c['score'] ?? 0),
            ];
            $contextBlocks[] = "[KB — " . (string)($c['doc_title'] ?? 'doc') . "]\n" . mb_substr((string)($c['text'] ?? ''), 0, 800);
        }
        if ($colony !== null) {
            $sources[] = [
                'type' => 'colony',
                'title' => 'Colony: ' . $colony['name'],
                'score' => 0.95,
            ];
            $contextBlocks[] = "[Live colony data]\n" . $colony['context_text'];
        }

        if (empty($contextBlocks)) {
            return [
                'success' => false,
                'answer' => '',
                'confidence' => 0.0,
                'sources' => [],
                'session_id' => $userId ? 'u' . $userId : 'guest',
            ];
        }

        $context = implode("\n\n", $contextBlocks);
        $prompt = "You are the APS Dream Home AI assistant (RAG grounded). Answer the user's question using ONLY the context below. "
            . "Reply in Hinglish. If the answer is not in the context, say you don't know and suggest contacting the official office.\n\n"
            . "CONTEXT:\n" . $context . "\n\nUSER QUESTION:\n" . $query . "\n\nANSWER:";

        try {
            $aiResp = $this->freeEngines->generate($prompt, ['max_tokens' => 450, 'temperature' => 0.4], 'chat');
            $text = trim((string)($aiResp['text'] ?? ''));
        } catch (\Throwable $e) {
            error_log('RAGService::answer AI call failed: ' . $e->getMessage());
            $text = '';
        }

        if ($text === '') {
            // Degrade to a clean, sourced summary instead of silence.
            $text = "I found these relevant documents in the APS knowledge base:\n\n";
            foreach ($sources as $s) {
                $text .= "• " . $s['title'] . "\n";
            }
            $text .= "\nFor the full details, please contact us at +91 92771 21112.";
        }

        return [
            'success' => true,
            'answer' => $text,
            'confidence' => $colony !== null ? 0.85 : 0.65,
            'sources' => $sources,
            'session_id' => $userId ? 'u' . $userId : 'guest',
        ];
    }

    /**
     * Find a colony referenced by name in the query and return its live snapshot.
     */
    private function findRelevantColony(string $query): ?array
    {
        $q = mb_strtolower($query);
        $colonies = [
            'suryoday colony' => ['key' => 'suryoday', 'name' => 'Suryoday Colony'],
            'suryoday heights' => ['key' => 'suryoday', 'name' => 'Suryoday Heights'],
            'raghunath' => ['key' => 'raghunath', 'name' => 'Raghunath Nagri'],
            'braj radha' => ['key' => 'braj', 'name' => 'Braj Radha Nagri'],
            'budh bihar' => ['key' => 'budh', 'name' => 'Budh Bihar Colony'],
            'budha city' => ['key' => 'budh', 'name' => 'Budha City Kushinagar'],
            'motiram' => ['key' => 'motiram', 'name' => 'Motiram Township'],
            'lakhewsari' => ['key' => 'lakeview', 'name' => 'Lakeview Residency'],
            'lakeview' => ['key' => 'lakeview', 'name' => 'Lakeview Residency'],
        ];
        $match = null;
        foreach ($colonies as $needle => $meta) {
            if (str_contains($q, $needle)) { $match = $meta; break; }
        }
        if ($match === null) {
            return null;
        }

        try {
            $row = $this->db->fetch(
                "SELECT id, name, status, total_plots, available_plots, total_area_acres, starting_price, city, state, rera_number
                 FROM colonies
                 WHERE LOWER(name) LIKE ? OR LOWER(slug) LIKE ?
                 LIMIT 1",
                ['%' . str_replace(' ', '%', $match['key']) . '%', '%' . str_replace(' ', '%', $match['key']) . '%']
            );
        } catch (\Throwable $e) {
            return null;
        }

        if (!$row) {
            return null;
        }

        $plotSummary = '';
        try {
            $p = $this->db->fetchAll(
                "SELECT status, COUNT(*) n FROM plots WHERE colony_id = ? GROUP BY status",
                [(int)$row['id']]
            );
            $parts = [];
            foreach ($p as $r) {
                $parts[] = ($r['n'] ?? 0) . ' ' . ($r['status'] ?? '');
            }
            $plotSummary = "Plot inventory: " . implode(', ', $parts);
        } catch (\Throwable $e) {
            $plotSummary = 'Plot inventory unavailable';
        }

        $context = "- Colony: " . ($row['name'] ?? $match['name']) . "\n"
            . "- Status: " . ($row['status'] ?? 'active') . "\n"
            . "- Location: " . trim(($row['city'] ?? '') . ', ' . ($row['state'] ?? 'Uttar Pradesh'), ", ") . "\n"
            . "- RERA number: " . ($row['rera_number'] ?? 'Not registered/available') . "\n"
            . "- Total plots: " . ($row['total_plots'] ?? 'N/A') . "\n"
            . "- Available plots: " . ($row['available_plots'] ?? 'N/A') . "\n"
            . "- Starting price/sqft: ₹" . number_format((float)($row['starting_price'] ?? 0), 2) . "\n"
            . "- " . $plotSummary;

        return ['name' => (string)($row['name'] ?? $match['name']), 'context_text' => $context];
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
     * RAG engine health stats (documents, chunks, providers)
     */
    public function getStats(): array
    {
        try {
            $tid = $this->tenantId();
            $docs = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM ai_knowledge_base WHERE tenant_id = " . ($tid > 1 ? $tid : 1))['c'] ?? 0);
            $chunks = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM ai_knowledge_chunks WHERE tenant_id = " . ($tid > 1 ? $tid : 1))['c'] ?? 0);
            $degraded = 0;
            try {
                $rows = $this->db->fetchAll("SELECT embedding FROM ai_knowledge_chunks LIMIT 50") ?: [];
                foreach ($rows as $r) {
                    $dec = json_decode((string)($r['embedding'] ?? ''), true);
                    if ((string)($dec['model'] ?? '') === self::MODEL_HASH) $degraded++;
                }
            } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            $docs = 0; $chunks = 0; $degraded = 0;
        }

        return [
            'documents' => $docs,
            'chunks' => $chunks,
            'degraded_sample' => $degraded,
            'active_provider' => $this->lastProvider,
        ];
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
        // Gemini first (free); OpenRouter second (paid pennies, live today);
        // HF needs credits. If the admin explicitly selects a provider, honour it first.
        $order = [self::MODEL_GEMINI, self::MODEL_OPENROUTER, self::MODEL_HF];
        if ($model === self::MODEL_HF) {
            $order = [self::MODEL_HF, self::MODEL_GEMINI, self::MODEL_OPENROUTER];
        } elseif ($model === self::MODEL_OPENROUTER) {
            $order = [self::MODEL_OPENROUTER, self::MODEL_GEMINI, self::MODEL_HF];
        }

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
            } elseif ($candidate === self::MODEL_OPENROUTER) {
                $vec = $this->openRouterEmbed($text);
                if ($vec !== null) {
                    $this->lastProvider = 'openrouter';
                    return json_encode(['model' => self::MODEL_OPENROUTER, 'vector' => $vec]);
                }
            }
        }

        // Last resort — ALWAYS announce the degradation.
        $this->logOnce(
            'hash_fallback',
            'RAGEmbeddingService: DEGRADED to hash-placeholder-v2 - no embedding provider '
            . 'succeeded (gemini: ' . ($this->logGuards['gemini_err'] ?? 'n/a')
            . ' | openrouter: ' . ($this->logGuards['openrouter_err'] ?? 'n/a')
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
     * OpenRouter embeddings (model openai/text-embedding-3-small, dim 1536).
     * Pennies per million tokens; verified live from this environment (200 on
     * the real key). Returns null on any failure.
     */
    public function openRouterEmbed(string $text): ?array
    {
        $key = $this->openRouterKey();
        if ($key === '') {
            $this->logGuards['openrouter_err'] = 'no key';
            return null;
        }

        $url = 'https://openrouter.ai/api/v1/embeddings';
        $payload = json_encode([
            'model' => 'openai/text-embedding-3-small',
            'input' => mb_substr($text, 0, 8000),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $key],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = (string) curl_error($ch);
        curl_close($ch);

        if ($resp === false || $code !== 200) {
            $this->logGuards['openrouter_err'] = 'HTTP ' . $code . ' ' . $err . (is_string($resp) ? ': ' . substr($resp, 0, 160) : '');
            error_log('RAGEmbeddingService: OpenRouter embedding HTTP ' . $code . ' ' . $err);
            return null;
        }

        $decoded = json_decode($resp, true);
        $vec = $decoded['data'][0]['embedding'] ?? null;
        if (!is_array($vec) || count($vec) !== 1536) {
            $this->logGuards['openrouter_err'] = 'bad response dim=' . (is_array($vec) ? count($vec) : 'n/a');
            return null;
        }

        $this->logGuards['openrouter_err'] = null;
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
        $dims = [];
        $totalChunks = 0;
        $onDim = 0;
        $orFilled = 0;
        $orMissing = 0;
        try {
            $rows = $this->db->fetchAll("SELECT embedding, embedding_openrouter FROM ai_knowledge_chunks") ?: [];
            foreach ($rows as $r) {
                $dec = json_decode((string) ($r['embedding'] ?? ''), true);
                $m = (string) ($dec['model'] ?? 'unknown');
                $stored[$m] = ($stored[$m] ?? 0) + 1;
                $v = $dec['vector'] ?? null;
                $d = is_array($v) ? count($v) : 0;
                $dims[$d] = ($dims[$d] ?? 0) + 1;
                $totalChunks++;
                if ($d === $probeDim) { $onDim++; }

                $or = json_decode((string) ($r['embedding_openrouter'] ?? ''), true);
                $orVec = $or['vector'] ?? null;
                if (is_array($orVec) && count($orVec) === 1536) { $orFilled++; } else { $orMissing++; }
            }
        } catch (\Throwable $e) {
            $stored = [];
            $dims = [];
            $totalChunks = 0;
            $onDim = 0;
            $orFilled = 0;
            $orMissing = 0;
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
            'openrouter_key_set' => ($this->openRouterKey() !== ''),
            'openrouter_last_error' => $this->logGuards['openrouter_err'] ?? null,
            'openrouter_filled' => $orFilled,
            'openrouter_missing' => $orMissing,
            'openrouter_coverage_pct' => ($orFilled + $orMissing) > 0 ? round(($orFilled / ($orFilled + $orMissing)) * 100, 1) : 0.0,
            'stored_models' => $stored,
            'stale_chunks' => ($stored[self::MODEL_HASH] ?? 0),
            'total_chunks' => array_sum($stored),
            'stored_dimensions' => $dims,
            'corpus_dim_checked' => $totalChunks,
            'corpus_dim_searchable' => $onDim,
            'corpus_dim_coverage_pct' => $totalChunks > 0 ? round(($onDim / $totalChunks) * 100, 1) : 0.0,
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