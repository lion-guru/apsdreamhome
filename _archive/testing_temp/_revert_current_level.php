<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== REVERT: mlm_profiles.current_level back to original casing ===\n";

// Check current state
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level")->fetchAll(PDO::FETCH_ASSOC);
echo "BEFORE:\n";
foreach ($rows as $r) echo "  '" . $r['current_level'] . "' = " . $r['cnt'] . "\n";

// The mlm_levels table uses title case: Associate, Bronze, Silver...
// RankEvaluationService writes these values on promotion
// MLMCommissionEngine writes lowercase on promotion
// Let's keep the most common values that won't break anything
// 'Ass.' = 26 profiles â†’ should be 'Associate' (from mlm_levels)
// 'Sr. Ass.' = 14 profiles â†’ should be 'Senior Associate' (from mlm_commission_levels)
// But wait â€” we need to check what mlm_commission_levels actually uses vs mlm_levels

echo "\n=== Check what rank names are used where ===\n";

// Who WRITES to current_level?
echo "\nWrites to mlm_profiles.current_level:\n";
echo "1. MLMCommissionEngine::evaluateAndPromote() - writes from RANK_ORDER (lowercase)\n";
echo "2. RankEvaluationService::evaluate() - writes from mlm_levels.level_name (title case)\n";
echo "3. AssociateAuthController - writes integer 1\n";
echo "4. HybridCommissionEngine - does NOT write current_level directly\n";

// For now, revert to lowercase since MLMCommissionEngine is the primary writer
// and mlm_levels has the most rows
echo "\nReverting to lowercase (what MLMCommissionEngine writes)...\n";

// Actually â€” let's just revert to the values that existed BEFORE our normalization
// We saved: assistantâ†’26, sr_assistantâ†’14
// Let's revert those
$pdo->exec("UPDATE mlm_profiles SET current_level = 'assistant' WHERE current_level = 'Ass.'");
echo "Reverted 'Ass.' â†’ 'assistant': " . $pdo->rowCount() . " rows\n";

$pdo->exec("UPDATE mlm_profiles SET current_level = 'sr_assistant' WHERE current_level = 'Sr. Ass.'");
echo "Reverted 'Sr. Ass.' â†’ 'sr_assistant': " . $pdo->rowCount() . " rows\n";

// Verify
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level")->fetchAll(PDO::FETCH_ASSOC);
echo "\nAFTER REVERT:\n";
foreach ($rows as $r) echo "  '" . $r['current_level'] . "' = " . $r['cnt'] . "\n";?>