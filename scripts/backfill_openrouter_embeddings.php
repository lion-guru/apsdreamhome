<?php
/**
 * Backfill `ai_knowledge_chunks.embedding_openrouter` (1536-dim) for rows that
 * predate dual-embedding storage. Idempotent: rows already carrying an
 * OpenRouter vector are SKIPPED. Per-chunk transactions; stops after 10
 * consecutive provider failures.
 *
 * Usage:
 *   php scripts/backfill_openrouter_embeddings.php --dry-run
 *   php scripts/backfill_openrouter_embeddings.php
 */
require __DIR__ . '/../config/bootstrap.php';

use App\Services\AI\RAGService;

$dryRun = in_array('--dry-run', $argv ?? [], true);
$svc = RAGService::getInstance();
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$rows = $pdo->query("SELECT id, chunk_text FROM ai_knowledge_chunks WHERE embedding_openrouter IS NULL OR embedding_openrouter = '' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "rows needing backfill: " . count($rows) . "\n";
if ($dryRun) {
    echo "dry-run: would embed " . count($rows) . " rows via OpenRouter (openai/text-embedding-3-small)\n";
    return;
}

$done = 0;
$failed = 0;
$consecutiveFailure = 0;
$start = microtime(true);

foreach ($rows as $i => $row) {
    $vec = $svc->openRouterEmbed($row['chunk_text']);
    if (!is_array($vec) || count($vec) !== 1536) {
        $failed++;
        $consecutiveFailure++;
        echo "chunk {$row['id']}: embed failed (consecutive=$consecutiveFailure)\n";
        if ($consecutiveFailure >= 10) {
            echo "ABORT: 10 consecutive provider failures — stopping to avoid burning quota.\n";
            break;
        }
        continue;
    }
    $consecutiveFailure = 0;
    $json = json_encode(['model' => 'openrouter-text-embedding-3-small', 'vector' => $vec]);
    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare("UPDATE ai_knowledge_chunks SET embedding_openrouter = ? WHERE id = ? AND (embedding_openrouter IS NULL OR embedding_openrouter = '')");
        $upd->execute([$json, (int)$row['id']]);
        $pdo->commit();
        $done++;
    } catch (\Throwable $e) {
        $pdo->rollBack();
        $failed++;
        echo "chunk {$row['id']}: write failed: " . $e->getMessage() . "\n";
    }
    if (($i + 1) % 50 === 0) {
        $elapsed = round(microtime(true) - $start, 1);
        echo "progress: " . ($i + 1) . "/" . count($rows) . " done=$done failed=$failed ({$elapsed}s)\n";
    }
}

echo "\nbackfill complete: done=$done failed=$failed of " . count($rows) . " in " . round(microtime(true) - $start, 1) . "s\n";
$hist = $pdo->query("SELECT (embedding_openrouter IS NULL OR embedding_openrouter = '') AS missing, COUNT(*) c FROM ai_knowledge_chunks GROUP BY missing")->fetchAll(PDO::FETCH_ASSOC);
foreach ($hist as $h) { printf("embedding_openrouter missing=%d rows=%d\n", (int)$h['missing'], (int)$h['c']); }
