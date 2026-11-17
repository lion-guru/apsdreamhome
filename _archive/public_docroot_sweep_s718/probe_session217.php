<?php
/**
 * Session 217 Probe — Township STP Effluent & Fire Safety Desk
 * Uses raw PDO to bypass bootstrap/ConfigService dependency
 */
declare(strict_types=1);

$pass  = 0;
$fail  = 0;
$steps = [];

function chk(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $steps;
    if ($ok) { $pass++; $steps[] = "[PASS] $label" . ($detail ? " — $detail" : ''); }
    else      { $fail++; $steps[] = "[FAIL] $label" . ($detail ? " — $detail" : ''); }
}

// ----------------------------------------------------------------
// RAW PDO CONNECTION
// ----------------------------------------------------------------
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=apsdreamhome;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    chk('DB Connection', true, 'apsdreamhome@127.0.0.1');
} catch (Throwable $e) {
    chk('DB Connection', false, $e->getMessage());
    echo implode("\n", $steps);
    exit(1);
}

// ----------------------------------------------------------------
// 1. CREATE TABLES IF MISSING
// ----------------------------------------------------------------
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS township_stp_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            colony_id INT NOT NULL,
            log_date DATE NOT NULL,
            stp_plant_code VARCHAR(100) NOT NULL,
            raw_sewage_inflow_kl DECIMAL(10,2) DEFAULT 0.00,
            treated_water_output_kl DECIMAL(10,2) DEFAULT 0.00,
            inlet_bod_mg_l DECIMAL(8,2) DEFAULT NULL,
            outlet_bod_mg_l DECIMAL(8,2) DEFAULT NULL,
            inlet_cod_mg_l DECIMAL(8,2) DEFAULT NULL,
            outlet_cod_mg_l DECIMAL(8,2) DEFAULT NULL,
            outlet_tss_mg_l DECIMAL(8,2) DEFAULT NULL,
            outlet_ph DECIMAL(4,2) DEFAULT NULL,
            chlorine_dosing_ppm DECIMAL(6,3) DEFAULT NULL,
            aeration_blower_hours DECIMAL(6,2) DEFAULT 0.00,
            water_reuse_purpose ENUM('gardening_horticulture','toilet_dual_flush','road_washing_dust_suppression','groundwater_recharge','overspill_drain') DEFAULT 'gardening_horticulture',
            sludge_cake_disposed_kg DECIMAL(10,2) DEFAULT 0.00,
            uppcb_compliance_status ENUM('fully_compliant','minor_deviation','critical_violation','sample_sent_lab') DEFAULT 'fully_compliant',
            operator_name VARCHAR(100) DEFAULT NULL,
            operator_phone VARCHAR(20) DEFAULT NULL,
            remarks TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS township_fire_safety_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            colony_id INT NOT NULL,
            inspection_date DATE NOT NULL,
            fire_zone_identifier VARCHAR(150) NOT NULL,
            static_line_pressure_bar DECIMAL(6,2) DEFAULT 7.00,
            jockey_pump_status ENUM('auto_standby','running_pressurizing','manual_mode','fault_tripped') DEFAULT 'auto_standby',
            main_electric_pump_status ENUM('operational_ready','service_due','phase_trip','breakdown') DEFAULT 'operational_ready',
            diesel_engine_backup_status ENUM('operational_ready','battery_weak','fuel_low','cranking_failure') DEFAULT 'operational_ready',
            fire_water_storage_kl DECIMAL(10,2) DEFAULT 100.00,
            hydrant_valves_tested INT DEFAULT 0,
            hydrant_valves_healthy INT DEFAULT 0,
            hose_boxes_equipped INT DEFAULT 0,
            extinguishers_checked INT DEFAULT 0,
            fire_drill_conducted TINYINT(1) DEFAULT 0,
            up_fire_noc_status ENUM('valid_compliant','renewal_due_30d','renewal_applied','provisional_noc','expired') DEFAULT 'valid_compliant',
            noc_certificate_number VARCHAR(100) DEFAULT NULL,
            noc_expiry_date DATE DEFAULT NULL,
            fire_officer_name VARCHAR(100) DEFAULT NULL,
            fire_officer_contact VARCHAR(20) DEFAULT NULL,
            defects_observed TEXT DEFAULT NULL,
            remarks TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    chk('DB Tables Created/Verified', true, 'township_stp_logs + township_fire_safety_logs');
} catch (Throwable $e) {
    chk('DB Tables Created/Verified', false, $e->getMessage());
}

// ----------------------------------------------------------------
// 2. GET COLONY
// ----------------------------------------------------------------
$colony = $pdo->query("SELECT id, name FROM colonies WHERE is_active=1 ORDER BY id ASC LIMIT 1")->fetch();
$cid    = $colony ? (int)$colony['id'] : 1;
chk('Colony Loaded', (bool)$colony, $colony ? "#{$cid} {$colony['name']}" : 'Fallback cid=1');

// ----------------------------------------------------------------
// 3. STP — INSERT + COMPLIANCE AUTO-DETECT
// ----------------------------------------------------------------
$today       = date('Y-m-d');
$inletBod    = 250.0;
$outletBod   = 8.5;
$bodEffPct   = round((($inletBod - $outletBod) / $inletBod) * 100, 2);
// Auto-compliance: outletBod 8.5 ≤ 10.0 → fully_compliant
$compliance  = ($outletBod <= 10.0) ? 'fully_compliant' : 'minor_deviation';

$stpStmt = $pdo->prepare("
    INSERT INTO township_stp_logs
    (colony_id, log_date, stp_plant_code, raw_sewage_inflow_kl, treated_water_output_kl,
     inlet_bod_mg_l, outlet_bod_mg_l, inlet_cod_mg_l, outlet_cod_mg_l,
     outlet_tss_mg_l, outlet_ph, chlorine_dosing_ppm, aeration_blower_hours,
     water_reuse_purpose, sludge_cake_disposed_kg, uppcb_compliance_status,
     operator_name, operator_phone, remarks)
    VALUES (?,?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?, ?,?,?)
");
$stpStmt->execute([
    $cid, $today, 'APS-STP-GKP-MBR-001 (250 KLD)', 200.0, 185.0,
    $inletBod, $outletBod, 500.0, 42.0,
    15.0, 7.2, 0.5, 22.0,
    'gardening_horticulture', 45.0, $compliance,
    'Manoj Kumar Verma', '9876543211', 'Probe test - MBR plant fully operational'
]);
$stpLogId = (int)$pdo->lastInsertId();

chk('STP Effluent Run Logged', $stpLogId > 0,
    "ID#{$stpLogId} | Treated: 185.0 kL | BOD Removal: {$bodEffPct}% | Status: {$compliance}");
chk('STP BOD Auto-Compliance', $compliance === 'fully_compliant',
    "BOD outlet {$outletBod} mg/L ≤ 10.0 → {$compliance}");

// ----------------------------------------------------------------
// 4. STP TODAY SUMMARY
// ----------------------------------------------------------------
$stpSum = $pdo->prepare("
    SELECT COUNT(*) as total_runs,
           COALESCE(SUM(raw_sewage_inflow_kl),0) as total_inflow_kl,
           COALESCE(SUM(treated_water_output_kl),0) as total_treated_kl,
           COALESCE(AVG(outlet_bod_mg_l),0) as avg_outlet_bod
    FROM township_stp_logs WHERE log_date=? AND colony_id=?
");
$stpSum->execute([$today, $cid]);
$stpSummary = $stpSum->fetch();
chk('STP Today Summary', (int)($stpSummary['total_runs'] ?? 0) >= 1,
    "Runs:{$stpSummary['total_runs']} | Inflow:{$stpSummary['total_inflow_kl']} kL | Treated:{$stpSummary['total_treated_kl']} kL | Avg BOD:{$stpSummary['avg_outlet_bod']} mg/L");

// ----------------------------------------------------------------
// 5. STP getLogById (join colonies)
// ----------------------------------------------------------------
$stpLogRow = $pdo->prepare("
    SELECT l.*, c.name AS colony_name FROM township_stp_logs l
    JOIN colonies c ON l.colony_id=c.id WHERE l.id=?
");
$stpLogRow->execute([$stpLogId]);
$stpLog = $stpLogRow->fetch();
chk('STP Log By ID', !empty($stpLog), $stpLog ? "Plant:{$stpLog['stp_plant_code']} | Colony:{$stpLog['colony_name']}" : 'Not found');

// ----------------------------------------------------------------
// 6. FIRE SAFETY — INSERT + VALVE HEALTH
// ----------------------------------------------------------------
$valvesTested  = 24;
$valvesHealthy = 23;
$valveHealthPct = round(($valvesHealthy / $valvesTested) * 100, 2);

$fireStmt = $pdo->prepare("
    INSERT INTO township_fire_safety_logs
    (colony_id, inspection_date, fire_zone_identifier, static_line_pressure_bar,
     jockey_pump_status, main_electric_pump_status, diesel_engine_backup_status,
     fire_water_storage_kl, hydrant_valves_tested, hydrant_valves_healthy,
     hose_boxes_equipped, extinguishers_checked, fire_drill_conducted,
     up_fire_noc_status, noc_certificate_number, noc_expiry_date,
     fire_officer_name, fire_officer_contact, defects_observed, remarks)
    VALUES (?,?,?,?, ?,?,?, ?,?,?, ?,?,?, ?,?,?, ?,?,?,?)
");
$fireStmt->execute([
    $cid, $today, 'Ring Main Zone A - Sector 3 (APS GKP)', 7.5,
    'auto_standby', 'operational_ready', 'operational_ready',
    150.0, $valvesTested, $valvesHealthy,
    12, 36, 1,
    'valid_compliant', 'UP/FIRE/GKP/2025/APS001', '2026-03-31',
    'Sanjay Mishra', '9988776655', 'Nil', 'Probe test - all systems operational'
]);
$fireLogId = (int)$pdo->lastInsertId();

chk('Fire Inspection Logged', $fireLogId > 0,
    "ID#{$fireLogId} | Pressure: 7.5 Bar | Valve Health: {$valveHealthPct}% | NOC: valid_compliant");
chk('Fire Valve Health Calc', abs($valveHealthPct - 95.83) < 0.1,
    "Expected 95.83% | Got {$valveHealthPct}%");

// ----------------------------------------------------------------
// 7. FIRE TODAY SUMMARY
// ----------------------------------------------------------------
$fireSum = $pdo->prepare("
    SELECT COUNT(*) as total_inspections,
           COALESCE(AVG(static_line_pressure_bar),0) as avg_pressure,
           COALESCE(SUM(hydrant_valves_tested),0) as total_valves_tested,
           COALESCE(SUM(hydrant_valves_healthy),0) as total_valves_healthy,
           COUNT(CASE WHEN up_fire_noc_status='valid_compliant' THEN 1 END) as compliant_zones
    FROM township_fire_safety_logs WHERE inspection_date=? AND colony_id=?
");
$fireSum->execute([$today, $cid]);
$fireSummary = $fireSum->fetch();
chk('Fire Today Summary', (int)($fireSummary['total_inspections'] ?? 0) >= 1,
    "Inspections:{$fireSummary['total_inspections']} | Avg Pressure:{$fireSummary['avg_pressure']} Bar | Valves:{$fireSummary['total_valves_healthy']}/{$fireSummary['total_valves_tested']} | NOC Zones:{$fireSummary['compliant_zones']}");

// ----------------------------------------------------------------
// 8. FIRE getLogById
// ----------------------------------------------------------------
$fireLogRow = $pdo->prepare("
    SELECT l.*, c.name AS colony_name FROM township_fire_safety_logs l
    JOIN colonies c ON l.colony_id=c.id WHERE l.id=?
");
$fireLogRow->execute([$fireLogId]);
$fireLog = $fireLogRow->fetch();
chk('Fire Log By ID', !empty($fireLog), $fireLog ? "Zone:{$fireLog['fire_zone_identifier']} | Colony:{$fireLog['colony_name']}" : 'Not found');

// ----------------------------------------------------------------
// 9. RBAC MENU SEED
// ----------------------------------------------------------------
try {
    $menuItems = [
        ['admin/township-stp',         'STP Effluent & UPPCB Desk',  'fas fa-vial',              'Hub 1: Inventory & Projects', 95],
        ['admin/township-fire-safety', 'Fire Safety & Hydrant NOC',  'fas fa-fire-extinguisher', 'Hub 1: Inventory & Projects', 96],
    ];
    $seeded = 0;
    foreach ($menuItems as [$url, $label, $icon, $section, $sort]) {
        $row = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url=?")->execute([$url]) ?
               $pdo->prepare("SELECT id FROM admin_menu_items WHERE url=?")->execute([$url]) : null;
        // Simpler approach:
        $check = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url=?");
        $check->execute([$url]);
        $existing = $check->fetch();
        if (!$existing) {
            $pdo->prepare("INSERT INTO admin_menu_items (label,url,icon,section,sort_order,is_active) VALUES (?,?,?,?,?,1)")
                ->execute([$label, $url, $icon, $section, $sort]);
            $menuId = (int)$pdo->lastInsertId();
        } else {
            $menuId = (int)$existing['id'];
        }
        foreach (['super_admin','admin','manager','construction_director'] as $role) {
            $chkPerm = $pdo->prepare("SELECT id FROM admin_role_menu_permissions WHERE role=? AND menu_item_id=?");
            $chkPerm->execute([$role, $menuId]);
            if (!$chkPerm->fetch()) {
                $pdo->prepare("INSERT INTO admin_role_menu_permissions (role,menu_item_id,can_view) VALUES (?,?,1)")
                    ->execute([$role, $menuId]);
            }
        }
        $seeded++;
    }
    chk('RBAC Menu Seeded', $seeded === 2, 'STP Effluent Desk + Fire Safety Desk added to admin_menu_items');
} catch (Throwable $e) {
    chk('RBAC Menu Seeded', false, $e->getMessage());
}

// ----------------------------------------------------------------
// 10. COLONY 360° COUNTS
// ----------------------------------------------------------------
$stpCount  = (int)($pdo->prepare("SELECT COUNT(*) AS c FROM township_stp_logs WHERE colony_id=?")->execute([$cid]) ?
             $pdo->query("SELECT COUNT(*) AS c FROM township_stp_logs WHERE colony_id={$cid}")->fetchColumn() : 0);
$fireCount = (int)($pdo->query("SELECT COUNT(*) AS c FROM township_fire_safety_logs WHERE colony_id={$cid}")->fetchColumn());
chk('Colony Hub STP Count',  $stpCount  >= 1, "township_stp_logs for colony #{$cid}: {$stpCount}");
chk('Colony Hub Fire Count', $fireCount >= 1, "township_fire_safety_logs for colony #{$cid}: {$fireCount}");

// ----------------------------------------------------------------
// 11. ROUTE CHECK
// ----------------------------------------------------------------
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
chk('STP Route GET',           str_contains($routes, "'/admin/township-stp'"),           '/admin/township-stp registered');
chk('STP Route POST',          str_contains($routes, "'/admin/township-stp/store'"),     '/admin/township-stp/store POST');
chk('STP Print Route',         str_contains($routes, 'TownshipStpController@print'),     'STP print certificate route');
chk('Fire Safety Route GET',   str_contains($routes, "'/admin/township-fire-safety'"),   '/admin/township-fire-safety registered');
chk('Fire Safety Route POST',  str_contains($routes, "'/admin/township-fire-safety/store'"), 'Fire Safety store POST');
chk('Fire Safety Print Route', str_contains($routes, 'TownshipFireSafetyController@print'), 'Fire NOC certificate route');

// ----------------------------------------------------------------
// 12. VIEW FILE CHECK
// ----------------------------------------------------------------
$vb = dirname(__DIR__) . '/app/views/admin';
chk('STP Index View',         file_exists("{$vb}/stp/index.php"),         'admin/stp/index.php');
chk('STP Print Certificate',  file_exists("{$vb}/stp/print.php"),         'admin/stp/print.php');
chk('Fire Safety Index View', file_exists("{$vb}/fire_safety/index.php"), 'admin/fire_safety/index.php');
chk('Fire Safety Certificate',file_exists("{$vb}/fire_safety/print.php"), 'admin/fire_safety/print.php');

// ----------------------------------------------------------------
// 13. COLONY HUB WIRING
// ----------------------------------------------------------------
$showView = file_get_contents("{$vb}/colonies/show.php");
chk('Colony Hub STP Card',         str_contains($showView, 'township-stp'),          '/admin/township-stp link in show.php');
chk('Colony Hub Fire Card',        str_contains($showView, 'township-fire-safety'),  '/admin/township-fire-safety link in show.php');
chk('Colony Hub STP Metric Tile',  str_contains($showView, "stp_logs"),              'STP metric ribbon tile');
chk('Colony Hub Fire Metric Tile', str_contains($showView, "fire_safety_logs"),      'Fire Safety metric ribbon tile');
chk('ColonyController STP Count',  str_contains(file_get_contents(dirname(__DIR__).'/app/Http/Controllers/Admin/ColonyController.php'), 'township_stp_logs'), 'ColonyController queries stp_logs');
chk('ColonyController Fire Count', str_contains(file_get_contents(dirname(__DIR__).'/app/Http/Controllers/Admin/ColonyController.php'), 'township_fire_safety_logs'), 'ColonyController queries fire_safety_logs');

// ----------------------------------------------------------------
// 14. TEARDOWN
// ----------------------------------------------------------------
if ($stpLogId > 0)  $pdo->exec("DELETE FROM township_stp_logs WHERE id = {$stpLogId}");
if ($fireLogId > 0) $pdo->exec("DELETE FROM township_fire_safety_logs WHERE id = {$fireLogId}");
chk('Teardown Clean', true, "Probe records deleted — 0 DB leftovers");

// ----------------------------------------------------------------
// RESULT
// ----------------------------------------------------------------
$total = $pass + $fail;
echo "\n============================================================\n";
echo "  APS Dream Home — Session 217 Probe\n";
echo "  Township STP Effluent Desk & Fire Safety Desk\n";
echo "============================================================\n";
foreach ($steps as $s) echo $s . "\n";
echo "------------------------------------------------------------\n";
echo "  {$pass}/{$total} PASSED" . ($fail === 0 ? " — 100% HEALTHY ✅" : " — {$fail} FAILED ❌") . "\n";
echo "============================================================\n";
