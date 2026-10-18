<?php
/**
 * S341 — Re-embed the RAG knowledge base with a real provider.
 *
 * Idempotent: by default it only rewrites chunks still carrying a
 * hash-placeholder vector. Pass --force to rewrite everything.
 *
 * Usage:
 *   php scripts/reembed_rag_chunks.php            # only stale chunks
 *   php scripts/reembed_rag_chunks.php --force    # rewrite all chunks
 *   php scripts/reembed_rag_chunks.php --dry-run  # report only, no writes
 *
 * Why this exists: all 23 chunks were embedded with hash-placeholder-v2
 * (S337) because the HF key was never read and HF requires PRO credits.
 * Gemini text-embedding-004 is on the free tier and is now the default.
 */
require_once __DIR__ . '/../config/bootstrap.php';

$force  = in_array('--force', $argv, true);
$dryRun = in_array('--dry-run', $argv, true);

echo "=== RAG re-embed (S341) ===\n";
echo "mode: " . ($force ? 'FORCE (all chunks)' : 'incremental (stale only)')
   . ($dryRun ? ' + DRY-RUN (no writes)' : '') . "\n\n";

$svc = \App\Services\AI\RAGEmbeddingService::getInstance();

// Health first — shows whether a real provider is reachable at all.
$health = $svc->getHealth();
echo "provider probe   : {$health['probe_provider']} ({$health['probe_model']}, {$health['probe_dimension']}-dim)\n";
echo "configured model : {$health['configured_model']}\n";
echo "gemini key set   : " . ($health['gemini_key_set'] ? 'yes' : 'NO') . "\n";
echo "hf key set       : " . ($health['hf_key_set'] ? 'yes' : 'NO')
   . ($health['hf_last_error'] ? " (last error: {$health['hf_last_error']})" : '') . "\n";
echo "chunks total     : {$health['total_chunks']}\n";
echo "stale (hash)     : {$health['stale_chunks']}\n";
if ($health['stored_models']) {
    foreach ($health['stored_models'] as $m => $n) {
        echo "  stored model   : {$m} = {$n}\n";
    }
}
echo "\n";

if ($health['degraded'] && !$health['stale_chunks']) {
    echo "NOTHING TO DO: provider is degraded to hash-placeholder AND no stale chunks "
       . "(already consistent with the degraded provider).\n";
    exit(0);
}

if ($dryRun) {
    echo "DRY-RUN: would re-embed up to " . ($force ? $health['total_chunks'] : $health['stale_chunks']) . " chunk(s).\n";
    exit(0);
}

$t0 = microtime(true);
$result = $svc->reembedAllChunks($force);
$secs = round(microtime(true) - $t0, 2);

echo "--- re-embed result ({$secs}s) ---\n";
echo "total       : {$result['total']}\n";
echo "re-embedded : {$result['reembedded']}\n";
echo "skipped     : {$result['skipped']}\n";
echo "failed      : {$result['failed']}\n";
echo "provider    : {$result['active_provider']}\n";
if ($result['models_written']) {
    foreach ($result['models_written'] as $m => $n) {
        echo "  written    : {$m} = {$n}\n";
    }
}

// Verify final state.
echo "\n--- verify ---\n";
$after = $svc->getHealth();
echo "provider probe : {$after['probe_provider']} ({$after['probe_model']}, {$after['probe_dimension']}-dim)\n";
echo "stale (hash)   : {$after['stale_chunks']} / {$after['total_chunks']}\n";
if ($after['stored_models']) {
    foreach ($after['stored_models'] as $m => $n) {
        echo "  stored       : {$m} = {$n}\n";
    }
}

if ($after['stale_chunks'] === 0 && !$after['degraded']) {
    echo "\nDONE: knowledge base fully on a real embedding provider.\n";
    exit(0);
}

echo "\nPARTIAL: " . $after['stale_chunks'] . " chunk(s) still stale"
   . ($after['degraded'] ? ' and the provider is degraded' : '') . " — re-run after fixing provider access.\n";
exit(1);