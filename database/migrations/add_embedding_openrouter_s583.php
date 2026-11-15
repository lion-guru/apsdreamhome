<?php
/**
 * Adds `embedding_openrouter` (LONGTEXT NULL) to ai_knowledge_chunks so every
 * chunk can carry BOTH a Gemini vector (embedding, 3072-dim) and an OpenRouter
 * vector (embedding_openrouter, 1536-dim). Query-side picks the column that
 * matches whichever provider answers.
 *
 * Idempotent: existing column is skipped with SKIP output. No data migration;
 * the backfill is a separate script (scripts/backfill_openrouter_embeddings.php).
 */
$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$exists = $pdo->query("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'ai_knowledge_chunks' AND COLUMN_NAME = 'embedding_openrouter'")->fetch(PDO::FETCH_ASSOC);
if ((int)$exists['c'] === 1) {
    echo "SKIP: ai_knowledge_chunks.embedding_openrouter already exists\n";
    return;
}

$pdo->exec("ALTER TABLE ai_knowledge_chunks ADD COLUMN embedding_openrouter LONGTEXT NULL AFTER embedding");
echo "OK: ai_knowledge_chunks.embedding_openrouter added\n";
