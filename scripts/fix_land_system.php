<?php
/**
 * Phase 1: Fix Land System - Table cleanup, FKs, service updates
 * Run: php scripts/fix_land_system.php
 */
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "========================================\n";
echo " PHASE 1: LAND SYSTEM FIX\n";
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

// 1. Rename legacy table
run($db, "RENAME TABLE land_acquisitions TO land_acquisitions_legacy", "Rename land_acquisitions → land_acquisitions_legacy", $results);

// 2. Create view for backward compatibility
run($db, "CREATE VIEW land_acquisitions AS SELECT * FROM land_deals", "Create land_acquisitions view → land_deals", $results);

// 3. Add missing FKs
run($db, "ALTER TABLE land_deals ADD CONSTRAINT fk_land_deals_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE SET NULL", "FK: land_deals.colony_id → colonies.id", $results);
run($db, "ALTER TABLE land_leads ADD CONSTRAINT fk_land_leads_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL", "FK: land_leads.assigned_to → users.id", $results);
run($db, "ALTER TABLE land_deal_payments ADD CONSTRAINT fk_land_payments_deal FOREIGN KEY (land_deal_id) REFERENCES land_deals(id) ON DELETE CASCADE", "FK: land_deal_payments.land_deal_id → land_deals.id", $results);
run($db, "ALTER TABLE colony_development_costs ADD CONSTRAINT fk_cdc_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE CASCADE", "FK: colony_development_costs.colony_id → colonies.id", $results);
run($db, "ALTER TABLE colony_layouts ADD CONSTRAINT fk_layouts_colony FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE CASCADE", "FK: colony_layouts.colony_id → colonies.id", $results);
run($db, "ALTER TABLE land_site_visits ADD CONSTRAINT fk_lsv_lead FOREIGN KEY (land_lead_id) REFERENCES land_leads(id) ON DELETE CASCADE", "FK: land_site_visits.land_lead_id → land_leads.id", $results);
run($db, "ALTER TABLE land_legal_opinions ADD CONSTRAINT fk_llo_lead FOREIGN KEY (land_lead_id) REFERENCES land_leads(id) ON DELETE CASCADE", "FK: land_legal_opinions.land_lead_id → land_leads.id", $results);
run($db, "ALTER TABLE land_documents ADD CONSTRAINT fk_ld_lead FOREIGN KEY (land_lead_id) REFERENCES land_leads(id) ON DELETE CASCADE", "FK: land_documents.land_lead_id → land_leads.id", $results);
run($db, "ALTER TABLE land_documents ADD CONSTRAINT fk_ld_deal FOREIGN KEY (land_deal_id) REFERENCES land_deals(id) ON DELETE SET NULL", "FK: land_documents.land_deal_id → land_deals.id", $results);

// 4. Migrate data from legacy to new if needed
$legacyCount = $db->fetch("SELECT COUNT(*) as c FROM land_acquisitions_legacy")['c'];
$newCount = $db->fetch("SELECT COUNT(*) as c FROM land_deals")['c'];
echo "\nLegacy rows: $legacyCount, New rows: $newCount\n";

if ($legacyCount > 0 && $newCount == 0) {
    echo "Migrating legacy data to land_deals...\n";
    $rows = $db->fetchAll("SELECT * FROM land_acquisitions_legacy");
    foreach ($rows as $r) {
        $db->execute("
            INSERT INTO land_deals (land_lead_id, colony_id, total_area_sqft, acquired_area_sqft, total_consideration, advance_paid, balance_amount, sale_agreement_date, sale_agreement_number, registration_date, registration_number, sub_registrar_office, stamp_duty_amount, registration_fee, mutation_status, status, created_at, updated_at)
            VALUES (NULL, NULL, ?, ?, ?, 0, ?, NULL, NULL, NULL, NULL, NULL, 0, 0, 'not_started', 'in_progress', ?, ?)
        ", [
            $r['land_area'] * 43560, // acres to sqft
            $r['land_area'] * 43560,
            $r['acquisition_cost'],
            $r['acquisition_cost'],
            $r['created_at'],
            $r['updated_at']
        ]);
    }
    echo "Migrated $legacyCount rows\n";
    $results['done'][] = "Migrated $legacyCount legacy rows to land_deals";
}

echo "\n========================================\n";
echo " SUMMARY\n";
echo "========================================\n";
echo "Done: " . count($results['done']) . "\n";
foreach ($results['done'] as $d) echo "  - $d\n";
echo "Failed: " . count($results['failed']) . "\n";
foreach ($results['failed'] as $f) echo "  - $f\n";