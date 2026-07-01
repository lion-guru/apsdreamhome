<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "================================================================\n";
echo "  DEEP AUDIT: All 4 Rank Naming Systems\n";
echo "================================================================\n";

echo "\n--- 1. mlm_levels (10 rows) ---\n";
$rows = $pdo->query('SELECT level_number, level_name FROM mlm_levels ORDER BY level_number ASC')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['level_number']}: {$r['level_name']}\n";

echo "\n--- 2. mlm_rank_benefits (7 rows) ---\n";
$rows = $pdo->query('SELECT rank_name, rank_order, min_leg_count, min_qualifying_volume FROM mlm_rank_benefits ORDER BY rank_order ASC')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  order={$r['rank_order']}: {$r['rank_name']} (legs>={$r['min_leg_count']}, vol>={$r['min_qualifying_volume']})\n";

echo "\n--- 3. mlm_commission_levels (7 rows) ---\n";
$rows = $pdo->query('SELECT level, name FROM mlm_commission_levels ORDER BY level ASC')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['level']}: {$r['name']}\n";

echo "\n--- 4. MLMCommissionEngine::RANK_ORDER (hardcoded) ---\n";
echo "  0: associate\n  1: bronze\n  2: silver\n  3: gold\n  4: platinum\n  5: diamond\n";

echo "\n--- 5. MobileApiController rank thresholds (hardcoded) ---\n";
echo "  Bronze, Silver, Gold, Platinum, Diamond, Crown\n";

echo "\n--- 6. mlm_profiles.current_level (actual data) ---\n";
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  '{$r['current_level']}' = {$r['cnt']} profiles\n";

echo "\n--- 7. associates.level (actual data) ---\n";
$rows = $pdo->query("SELECT level, COUNT(*) as cnt FROM associates GROUP BY level ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  '{$r['level']}' = {$r['cnt']} associates\n";

echo "\n--- 8. users.current_level (Gamification) ---\n";
try {
    $rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM users GROUP BY current_level ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) echo "  '{$r['current_level']}' = {$r['cnt']} users\n";
} catch (Exception $e) {
    echo "  Column may not exist: " . $e->getMessage() . "\n";
}

echo "\n================================================================\n";
echo "  CODE REFERENCE MAP\n";
echo "================================================================\n";

echo "\n--- WRITES to mlm_profiles.current_level ---\n";
echo "1. MLMCommissionEngine.php:334 → writes \$newRank from RANK_ORDER (lowercase)\n";
echo "2. RankEvaluationService.php:65 → writes from mlm_levels.level_name (title case)\n";
echo "3. AssociateAuthController.php:125 → writes integer 1\n";

echo "\n--- READS current_level and compares to rank names ---\n";
echo "1. DifferentialCommissionCalculator.php:130 → (int) cast → BROKEN with non-numeric\n";
echo "2. RankEvaluationService.php:102 → === mlm_levels.level_name\n";
echo "3. MobileApiController.php:1987 → 'Associate' default, searches in hardcoded array\n";
echo "4. MLMCommissionEngine.php:290 → array_search in RANK_ORDER (lowercase)\n";
echo "5. AssociateController.php:103 → 'Associate' default, display only\n";

echo "\n--- READS current_level for display (no comparison) ---\n";
echo "6. EngagementService.php:35,81 → SELECT only\n";
echo "7. NetworkTree.php:18,44 → SELECT only\n";
echo "8. ApiAuthService.php:49 → COALESCE default 'Customer'\n";
echo "9. MLMController.php:101,206,226,250,252,257 → display + GROUP BY\n";
echo "10. MLMGrowthReportController.php:126,131,132 → GROUP BY + display\n";
echo "11. MLMSettingsController.php:121,126 → display + ORDER BY\n";
echo "12. views/mlm/genealogy.php:33,133,168,196 → strtolower for CSS class\n";
echo "13. views/mlm/tree.php:33,148,201 → strtolower for CSS class\n";
echo "14. views/mlm/ranks.php:210 → display\n";
echo "15. views/mlm-settings/evaluate.php:58 → display\n";
echo "16. views/mlm-settings/associate_progress.php:29 → display\n";

echo "\n--- WRITES current_level (from Gamification on users table) ---\n";
echo "17. GamificationService.php:105,113,115,120,207 → users.current_level (INTEGER, different table!)\n";

echo "\n--- HARDWRITES specific rank names ---\n";
echo "18. AssociateAuthController.php:125 → 'current_level' => 1 (INTEGER)\n";
echo "19. Agent/MainController.php:52 → 'current_level' => 'Agent' (not in any rank system)\n";
echo "20. Customer.php:1368 → 'current_level' => 'Bronze' (title case)\n";
echo "21. MLMController.php:274 → 'current_level' => 'Gold' (title case)\n";
echo "22. Gamification.php:454 → 'current_level' => 1 (INTEGER, different table)\n";
