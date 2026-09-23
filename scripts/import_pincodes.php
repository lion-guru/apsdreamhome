<?php
/**
 * Import India Post pincode data into `pincodes` table.
 *
 * Usage: php scripts/import_pincodes.php path/to/pincodes.csv
 *
 * Expected CSV header (standard India Post format):
 *   officename,pincode,officeType,Deliverystatus,divisionname,regionname,
 *   circlename,Taluk,Districtname,Statename,Telephone,Related Suboffice,
 *   Related Headoffice,longitude,latitude
 *
 * Only INDIAN rows are imported. Existing pincodes are skipped (INSERT IGNORE).
 */
if ($argc < 2) { echo "Usage: php scripts/import_pincodes.php <csv-file>\n"; exit(1); }
$file = $argv[1];
if (!is_readable($file)) { echo "Cannot read: $file\n"; exit(1); }

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$handle = fopen($file, 'r');
if (!$handle) { echo "Could not open file\n"; exit(1); }
$header = array_map(fn($h) => strtolower(trim($h)), fgetcsv($handle));
$idx = array_flip($header);
$need = ['officename', 'pincode', 'officetype', 'deliverystatus', 'divisionname', 'regionname', 'circlename', 'taluk', 'districtname', 'statename'];
foreach ($need as $col) {
    if (!isset($idx[$col])) { echo "Missing column: $col\n"; exit(1); }
}
$latI = $idx['latitude'] ?? null; $lngI = $idx['longitude'] ?? null;

$stmt = $db->prepare(
    "INSERT IGNORE INTO pincodes (pincode, office_name, office_type, delivery_status, division_name, region_name, circle_name, taluk, district_name, state_name, latitude, longitude, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
);
$imported = 0; $skipped = 0;
while (($row = fgetcsv($handle)) !== false) {
    $pin = preg_replace('/\D/', '', $row[$idx['pincode']] ?? '');
    if (strlen($pin) !== 6) { $skipped++; continue; }
    $office = trim($row[$idx['officename']] ?? '');
    $district = trim($row[$idx['districtname']] ?? '');
    $state = trim($row[$idx['statename']] ?? '');
    if ($office === '' || $district === '' || $state === '') { $skipped++; continue; }
    try {
        $stmt->execute([
            $pin, $office, $row[$idx['officetype']] ?? '', $row[$idx['deliverystatus']] ?? '',
            $row[$idx['divisionname']] ?? null, $row[$idx['regionname']] ?? null,
            $row[$idx['circlename']] ?? null, $row[$idx['taluk']] ?? null,
            $district, $state,
            ($latI !== null && is_numeric($row[$latI] ?? null)) ? $row[$latI] : null,
            ($lngI !== null && is_numeric($row[$lngI] ?? null)) ? $row[$lngI] : null,
        ]);
        $stmt->rowCount() > 0 ? $imported++ : $skipped++;
    } catch (\Throwable $e) { $skipped++; }
}
fclose($handle);
echo "Imported: $imported, Skipped: $skipped\n";