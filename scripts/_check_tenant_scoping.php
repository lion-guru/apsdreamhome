<?php
chdir(dirname(__DIR__));
$content = file_get_contents($argv[1]);
// Find all SQL statements that don't have tenant_id in them
preg_match_all('/"(SELECT|INSERT|UPDATE|DELETE)[^"]*"(?:\s*;|(?:\s*\.\s*\$this->pdo)?)/i', $content, $matches);
$lines = explode("\n", $content);
foreach (explode("\n", $content) as $lineNum => $line) {
    if (preg_match('/prepare\(|pdo->query\(|->exec\(|INSERT INTO|UPDATE .* SET|DELETE FROM/', $line)) {
        // Check if this line or nearby lines have tenant_id
        $context = implode("\n", array_slice($lines, max(0, $lineNum - 3), 8));
        $lower = strtolower($context);
        if (strpos($lower, 'tenant_id') === false && strpos($lower, 'tid > 1') === false && strpos($lower, 'tenantSql') === false) {
            // Skip templates/subscribes (shared reference data)
            if (strpos($lower, 'legal_document_templates') !== false && strpos($lower, 'is_active = 1') !== false) continue;
            if (strpos($lower, 'legal_template_versions') !== false) continue;
            if (strpos($lower, 'legal_clause_library') !== false) continue;
            if (strpos($lower, 'legal_ai_prompts') !== false) continue;
            if (strpos($lower, 'ai_api_logs') !== false) continue;
            if (strpos($lower, 'system_') !== false) continue;
            if (strpos($lower, 'sessions') !== false) continue;
            if (strpos($lower, 'users u') !== false || strpos($lower, 'users u1') !== false) continue; // JOIN users for creator name
            echo ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}
