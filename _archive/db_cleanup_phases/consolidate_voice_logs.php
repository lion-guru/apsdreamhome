<?php
/**
 * VOICE AI CONSOLIDATION PHASE 1: Merge ai_call_logs (3 rows) into ai_call_sessions
 *
 * ai_call_logs columns: call_sid, transcript, summary, sentiment, follow_up_needed, follow_up_date
 * ai_call_sessions already has: call_transcript, ai_summary, sentiment_score, follow_up_required, follow_up_date
 *
 * Plan:
 * 1. Add call_sid + sentiment columns to ai_call_sessions
 * 2. Migrate data from ai_call_logs (3 rows)
 * 3. Update 3 code references to read from sessions instead
 * 4. Drop ai_call_logs
 *
 * Rollback: We keep a backup of ai_call_logs in ai_call_logs_backup_20260603
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== VOICE AI CONSOLIDATION PHASE 1: ai_call_logs -> ai_call_sessions ===\n\n";

// Step 1: Backup ai_call_logs
echo "1. Backing up ai_call_logs...\n";
$pdo->exec("DROP TABLE IF EXISTS ai_call_logs_backup_20260603");
$pdo->exec("CREATE TABLE ai_call_logs_backup_20260603 AS SELECT * FROM ai_call_logs");
$backupRows = $pdo->query("SELECT COUNT(*) FROM ai_call_logs_backup_20260603")->fetchColumn();
echo "   âœ“ Backed up $backupRows rows to ai_call_logs_backup_20260603\n\n";

// Step 2: Add missing columns to ai_call_sessions
echo "2. Adding columns to ai_call_sessions...\n";
$existingCols = [];
foreach ($pdo->query("DESCRIBE ai_call_sessions")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    $existingCols[] = $c['Field'];
}

if (!in_array('call_sid', $existingCols)) {
    $pdo->exec("ALTER TABLE ai_call_sessions ADD COLUMN call_sid VARCHAR(100) NULL");
    echo "   âœ“ Added call_sid\n";
} else {
    echo "   - call_sid already exists\n";
}

if (!in_array('sentiment', $existingCols)) {
    // Need to handle the case where sentiment_score exists - add sentiment as separate column
    $pdo->exec("ALTER TABLE ai_call_sessions ADD COLUMN sentiment ENUM('positive','neutral','negative') NULL");
    echo "   âœ“ Added sentiment\n";
} else {
    echo "   - sentiment already exists\n";
}
echo "\n";

// Step 3: Migrate data
echo "3. Migrating data from ai_call_logs to ai_call_sessions...\n";
$logs = $pdo->query("SELECT * FROM ai_call_logs")->fetchAll(PDO::FETCH_ASSOC);
foreach ($logs as $log) {
    // Find matching session by lead_id + agent_id
    $session = $pdo->prepare("SELECT id FROM ai_call_sessions WHERE lead_id = ? AND ai_agent_id = ? LIMIT 1");
    $session->execute([$log['lead_id'], $log['agent_id']]);
    $sessionId = $session->fetchColumn();

    if ($sessionId) {
        $stmt = $pdo->prepare("UPDATE ai_call_sessions SET call_sid = ?, sentiment = ? WHERE id = ?");
        $stmt->execute([$log['call_sid'], $log['sentiment'], $sessionId]);
        echo "   âœ“ Migrated log id={$log['id']} -> session id=$sessionId\n";
    } else {
        echo "   âš  No matching session for log id={$log['id']} (lead_id={$log['lead_id']}, agent_id={$log['agent_id']})\n";
    }
}
echo "\n";

// Step 4: Verify
echo "4. Verifying migration...\n";
$migrated = $pdo->query("SELECT COUNT(*) FROM ai_call_sessions WHERE call_sid IS NOT NULL")->fetchColumn();
echo "   - $migrated sessions have call_sid\n";
$migratedSentiment = $pdo->query("SELECT COUNT(*) FROM ai_call_sessions WHERE sentiment IS NOT NULL")->fetchColumn();
echo "   - $migratedSentiment sessions have sentiment\n\n";

echo "5. To complete consolidation:\n";
echo "   a. Update 3 code references in: AICallingAgent.php, LeadFollowUpAgent.php, VoiceCallService.php\n";
echo "   b. Run: DROP TABLE ai_call_logs; (after verifying E2E)\n";
echo "   c. To rollback: DROP TABLE ai_call_sessions_sentiment_added; RENAME TABLE ai_call_logs_backup_20260603 TO ai_call_logs;\n";
echo "   d. ALTER TABLE ai_call_sessions DROP COLUMN call_sid, DROP COLUMN sentiment;\n";?>