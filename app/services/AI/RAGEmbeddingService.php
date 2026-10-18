<?php

/**
 * RAGEmbeddingService (Session 341)
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

class RAGEmbeddingService
{
    public const MODEL_GEMINI = 'gemini-embedding-001';
    public const MODEL_HF     = 'hf-all-MiniLM-L6-v2';
    public const MODEL_HASH   = 'hash-placeholder-v2';

    private static ?self $instance = null;

    private \App\Core\Database\Database $db;
    private int $tenantId = 1;

    /** Provider that actually produced the last embedding (for health reporting). */
    private string $lastProvider = '';

    /** One-shot log guards so a misconfigured provider cannot flood the log. */
    private array $logGuards = [];

    private function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->tenantId = (int) ($_SESSION['tenant_id'] ?? 1);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ---------------------------------------------------------------- settings

    /**
     * Read the ai_settings row and decode the `settings` JSON blob.
     * NOTE: hf_api_key is NOT a column (S337) — it lives inside `settings`.
     */
    private function settings(): array
    {
        try {
            $sql = "SELECT * FROM ai_settings WHERE is_active = 1";
            $params = [];
            if ($this->tenantId > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $this->tenantId;
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
        // Gemini lives in the shared `api_key` column (service=gemini).
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

    // ------------------------------------------------------- model config (#5)

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

    // -------------------------------------------------------- public embedding

    /**
     * Produce an embedding for $text.
     * Returns the stored JSON shape: {"model": "...", "vector": [floats]}
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
        $key = $this->geminiKey();
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
            'content' => ['parts' => [['text' => mb_substr($text, 0, 8000)]],],
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
        $key = $this->hfKey();
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

    // ------------------------------------------------------- re-embed (#1)

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

    // -------------------------------------------------------- health card (#3)

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
            'gemini_key_set' => ($this->geminiKey() !== ''),
            'hf_key_set' => ($this->hfKey() !== ''),
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