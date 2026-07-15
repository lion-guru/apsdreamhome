<?php
/**
 * Phase 1c: Fix colony_id type mismatches - make referencing tables signed to match colonies.id
 */
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "========================================\n";
echo " PHASE 1c: FIX COLONY_ID TYPE MISMATCHES\n";
echo "========================================\n\n";

$results = ['done' => [], 'failed' => []];

function run($db, $sql, $desc, &$results) {
    try {
        $db->execute($sql);
        echo "[OK] $desc\n";
        $results['done'][] = $desc;
        return true;
    } catch (\Throwable $e) {
        echo "[FAIL] $desc: " . $e->getMessage() . "\n";
        $results['failed'][] = "$desc: " . $e->getMessage();
        return false;
    }
}

// Change referencing tables' colony_id from unsigned to signed to match colonies.id (which can't be changed due to construction_progress FK)
run($db, "ALTER TABLE land_deals MODIFY colony_id INT(11) NULL", "Fix land_deals.colony_id to signed INT", $results);
run($db, "ALTER TABLE colony_development_costs MODIFY colony_id INT(11) NOT NULL", "Fix colony_development_costs.colony_id to signed INT", $results);
run($db, "ALTER TABLE colony_layouts MODIFY colony_id INT(11) NOT NULL", "Fix colony_layouts.colony_id to signed INT", $results);

// Now add the FKs
run($db, "ALTER TABLE land_deals ADD CONSTRAINT fk_land_deals_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE SET NULL", "FK: land_deals.colony_id → colonies.id", $results);
run($db, "ALTER TABLE colony_development_costs ADD CONSTRAINT fk_cdc_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE CASCADE", "FK: colony_development_costs.colony_id → colonies.id", $results);
run($db, "ALTER TABLE colony_layouts ADD CONSTRAINT fk_layouts_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE CASCADE", "FK: colony_layouts.colony_id → colonies.id", $results);

echo "\n========================================\n";
echo " SUMMARY\n";
echo "========================================\n";
echo "Done: " . count($results['done']) . "\n";
foreach ($results['done'] as $d) echo "  - $d\n";
echo "Failed: " . count($results['failed']) . "\n";
foreach ($results['failed'] as $f) echo "  - $f\n";