<?php
require_once __DIR__ . '/../config/bootstrap.php';
echo "Starting migration...\n";
$pdo = App\Core\Database\Database::getInstance()->getConnection();
$rows = $pdo->query("SELECT * FROM land_records")->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($rows) . " land records\n";
foreach($rows as $r){
  $tid = $r['tenant_id'] ?? 1;
  $locParts = array_map('trim', explode(',', $r['location']));
  $village = $locParts[0] ?? $r['location'];
  $district = $locParts[1] ?? 'Gorakhpur';
  $state = 'Uttar Pradesh';
  preg_match('/(\d+)/', $r['land_title'], $m);
  $survey = $m[1] ?? $r['id'];
  echo "Processing: {$r['land_title']} -> survey=$survey\n";
  try {
    $pdo->prepare("INSERT INTO land_leads (tenant_id, lead_source, land_owner_name, village, district, state, survey_number, area_sqft, expected_price, status, created_at, updated_at) VALUES (?, 'direct', ?, ?, ?, ?, ?, ?, 0, 'registered', ?, NOW())")
      ->execute([$tid, $r['owner_name'], $village, $district, $state, $survey, $r['area']*43560, $r['created_at']]);
    $lid = $pdo->lastInsertId();
    echo "Migrated land_records id={$r['id']} -> land_leads id=$lid ({$r['land_title']} -> survey=$survey)\n";
  } catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
  }
}
echo "Done.\n";