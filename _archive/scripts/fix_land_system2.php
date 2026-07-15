<?php
/**
 * Phase 1b: Fix column types for FKs
 */
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "========================================\n";
echo " PHASE 1b: FIX COLUMN TYPES FOR FKs\n";
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

// 1. Fix users.id referenced columns - change land_leads.assigned_to to bigint unsigned
run($db, "ALTER TABLE land_leads MODIFY assigned_to BIGINT(20) UNSIGNED NULL", "Fix land_leads.assigned_to type to match users.id", $results);

// 2. Fix colonies.id - make it unsigned to match referencing tables
run($db, "ALTER TABLE colonies MODIFY id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT", "Fix colonies.id to UNSIGNED", $results);

// 3. Fix district_id in colonies to match districts.id (check districts first)
$distId = $db->fetch("SHOW COLUMNS FROM districts LIKE 'id'");
echo "districts.id: " . $distId['Type'] . "\n";
$colDistId = $db->fetch("SHOW COLUMNS FROM colonies LIKE 'district_id'");
echo "colonies.district_id: " . $colDistId['Type'] . "\n";

// 4. Rename land_documents.land_acquisition_id to land_deal_id
run($db, "ALTER TABLE land_documents CHANGE land_acquisition_id land_deal_id INT(11) UNSIGNED NULL", "Rename land_documents.land_acquisition_id → land_deal_id", $results);

// 5. Now add the FKs that failed
run($db, "ALTER TABLE land_deals ADD CONSTRAINT fk_land_deals_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE SET NULL", "FK: land_deals.colony_id → colonies.id", $results);
run($db, "ALTER TABLE land_leads ADD CONSTRAINT fk_land_leads_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL", "FK: land_leads.assigned_to → users.id", $results);
run($db, "ALTER TABLE colony_development_costs ADD CONSTRAINT fk_cdc_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE CASCADE", "FK: colony_development_costs.colony_id → colonies.id", $results);
run($db, "ALTER TABLE colony_layouts ADD CONSTRAINT fk_layouts_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE CASCADE", "FK: colony_layouts.colony_id → colonies.id", $results);
run($db, "ALTER TABLE land_documents ADD CONSTRAINT fk_ld_deal FOREIGN KEY (land_deal_id) REFERENCES land_deals(id) ON DELETE SET NULL", "FK: land_documents.land_deal_id → land_deals.id", $results);

// 6. Add broker FK
run($db, "ALTER TABLE land_leads ADD CONSTRAINT fk_land_leads_broker FOREIGN KEY (broker_id) REFERENCES land_brokers(id) ON DELETE SET NULL", "FK: land_leads.broker_id → land_brokers.id", $results);

echo "\n========================================\n";
echo " SUMMARY\n";
echo "========================================\n";
echo "Done: " . count($results['done']) . "\n";
foreach ($results['done'] as $d) echo "  - $d\n";
echo "Failed: " . count($results['failed']) . "\n";
foreach ($results['failed'] as $f) echo "  - $f\n";