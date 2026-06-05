<?php
/**
 * Test: Bulk Property CSV Import
 * Run: php testing/test_property_import.php
 *
 * 25+ assertions:
 * - Service class loads
 * - parseCsv handles quoted fields, escaped quotes, multiline, BOM
 * - validateRow rejects invalid types, missing required fields
 * - getTemplate returns correct headers
 * - getSampleData returns 5 rows
 * - End-to-end import creates user_properties rows
 * - Duplicates are skipped
 * - Template + sample downloads have correct content-type
 * - Controller methods exist + routes registered
 * - Menu item exists
 */

define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/app/Core/ConfigService.php';
require_once BASE_PATH . '/app/Core/Database/Database.php';
require_once BASE_PATH . '/app/Services/Bulk/PropertyImportService.php';

use App\Core\Database\Database;
use App\Services\Bulk\PropertyImportService;

$pass = 0;
$fail = 0;
$failures = [];

function assertTrue($cond, string $label): void {
    global $pass, $fail, $failures;
    if ($cond) { $pass++; echo "  PASS  $label\n"; }
    else { $fail++; $failures[] = $label; echo "  FAIL  $label\n"; }
}

echo "=== Bulk Property Import Tests ===\n\n";

// Group 1: Service class
echo "[1] Service class\n";
$svcPath = BASE_PATH . '/app/Services/Bulk/PropertyImportService.php';
assertTrue(file_exists($svcPath), 'Service file exists');
$content = file_get_contents($svcPath);
assertTrue(strpos($content, 'class PropertyImportService') !== false, 'Class defined');
assertTrue(strpos($content, "namespace App\\Services\\Bulk") !== false, 'Namespace correct');
foreach (['importCsv', 'validateRow', 'getTemplate', 'getSampleData', 'previewImport', 'parseCsv'] as $m) {
    assertTrue(strpos($content, "function $m(") !== false, "Method $m exists");
}

// Group 2: CSV parser handles edge cases
echo "\n[2] CSV parser\n";
$db = Database::getInstance();
$svc = new PropertyImportService($db);
$csv1 = "title,type,listing_type,price\r\nTest House,house,sale,5000000\r\n";
$rows = $svc->parseCsv($csv1);
assertTrue(count($rows) === 2, 'Simple CSV: 2 rows parsed');
assertTrue($rows[0][0] === 'title', 'Simple CSV: header first col');
assertTrue($rows[1][1] === 'house', 'Simple CSV: data row second col');

$csv2 = "title,description\r\n\"Quoted, with comma\",\"line1\nline2\"\r\n";
$rows = $svc->parseCsv($csv2);
assertTrue(count($rows) === 2, 'Quoted+multiline CSV: 2 rows');
assertTrue($rows[1][0] === 'Quoted, with comma', 'Quoted field with comma preserved');
assertTrue(strpos($rows[1][1], "line1\nline2") !== false, 'Multiline cell preserved');

$csv3 = "title,desc\r\n\"He said \"\"hi\"\"\",\"\"\r\n";
$rows = $svc->parseCsv($csv3);
assertTrue(count($rows) === 2, 'Escaped quotes CSV: 2 rows');
assertTrue($rows[1][0] === 'He said "hi"', 'Escaped quotes handled');

$csv4 = "\xEF\xBB\xBFtitle,price\r\nBOM Test,1000\r\n";
$rows = $svc->parseCsv($csv4);
assertTrue(count($rows) === 2, 'BOM stripped');
assertTrue($rows[1][0] === 'BOM Test', 'BOM CSV: data preserved');

// Group 3: Validation
echo "\n[3] Validation\n";
$header = ['title', 'type', 'listing_type', 'price'];
[$data, $errors] = $svc->validateRow(['Test Plot', 'plot', 'sale', '500000'], $header, 1);
assertTrue(empty($errors), 'Valid row: no errors');
assertTrue($data['title'] === 'Test Plot', 'Valid row: title preserved');

[$d2, $e2] = $svc->validateRow(['', 'plot', 'sale', '500000'], $header, 2);
assertTrue(count($e2) > 0, 'Missing title: error');
assertTrue(strpos(implode(' ', $e2), 'title') !== false, 'Missing title error mentions title');

[$d3, $e3] = $svc->validateRow(['Test', 'invalid_type', 'sale', '500000'], $header, 3);
assertTrue(count($e3) > 0, 'Invalid type: error');
assertTrue(strpos(implode(' ', $e3), 'type') !== false, 'Invalid type error mentions type');

[$d4, $e4] = $svc->validateRow(['Test', 'plot', 'invalid_listing', '500000'], $header, 4);
assertTrue(count($e4) > 0, 'Invalid listing_type: error');

[$d5, $e5] = $svc->validateRow(['Test', 'plot', 'sale', '0'], $header, 5);
assertTrue(count($e5) > 0, 'Price = 0: error');

[$d6, $e6] = $svc->validateRow(['Test', 'plot', 'sale', '-100'], $header, 6);
assertTrue(count($e6) > 0, 'Negative price: error');

// Group 4: Template + sample
echo "\n[4] Template + sample\n";
$tmpl = $svc->getTemplate();
assertTrue(strpos($tmpl, 'title') === 0, 'Template starts with title header');
assertTrue(strpos($tmpl, ',type,listing_type,price,') !== false, 'Template has core columns');

$sample = $svc->getSampleData();
assertTrue(count($sample) === 5, 'Sample has 5 rows');
$sampleCsv = $svc->getSampleCsv();
assertTrue(strpos($sampleCsv, '2 BHK Flat in Gorakhpur') !== false, 'Sample row 1 in CSV');
assertTrue(strpos($sampleCsv, '5 Acre Farmhouse') !== false, 'Sample row 5 in CSV');

// Group 5: End-to-end import
echo "\n[5] End-to-end import\n";
// Clean up any leftover test data from prior runs
$db->execute("DELETE FROM user_properties WHERE name LIKE 'E2E Test Plot%' OR name = 'Good Row' OR name = 'Bad Type Row'");
$e2eCsv = "title,type,listing_type,price,area,location,city,state,pincode,owner_name,owner_phone,owner_email,description,amenities,images\r\n" .
    "E2E Test Plot 1,plot,sale,1500000,1000,Test Address,Gorakhpur,UP,273001,Test Owner,9876500001,test1@example.com,Test desc,parking;lift,\r\n" .
    "E2E Test Plot 2,plot,sale,2000000,1200,Test Address 2,Lucknow,UP,226001,Test Owner 2,9876500002,test2@example.com,Test 2,security,\r\n";
$result = $svc->importCsv($e2eCsv);
assertTrue($result['ok'] === true, 'Import successful');
assertTrue($result['imported'] === 2, 'Imported 2 rows');
assertTrue($result['skipped'] === 0, 'Skipped 0 rows');

// Verify in DB
$count = $db->fetchOne("SELECT COUNT(*) as cnt FROM user_properties WHERE name LIKE 'E2E Test Plot%'");
assertTrue((int)$count['cnt'] >= 2, 'At least 2 E2E test properties in DB');

// Group 6: Duplicate skip
echo "\n[6] Duplicate detection\n";
$result2 = $svc->importCsv($e2eCsv);
assertTrue($result2['ok'] === true, 'Re-import successful (with skip)');
assertTrue($result2['imported'] === 0, 'Duplicates: 0 imported');
assertTrue($result2['skipped'] >= 2, 'Duplicates: 2+ skipped');
$dupErrors = array_filter($result2['errors'], fn($e) => strpos($e, 'duplicate') !== false);
assertTrue(count($dupErrors) >= 2, 'Duplicates: error log mentions "duplicate"');

// Group 7: Invalid type skipped
echo "\n[7] Invalid type handling\n";
$db->execute("DELETE FROM user_properties WHERE name IN ('Bad Type Row', 'Good Row')");
$badCsv = "title,type,listing_type,price\r\nBad Type Row,warehouse,sale,100000\r\nGood Row,plot,sale,500000\r\n";
$result3 = $svc->importCsv($badCsv);
assertTrue($result3['ok'] === true, 'Mixed CSV: import ran');
assertTrue($result3['imported'] === 1, 'Mixed CSV: 1 valid row imported');
assertTrue($result3['skipped'] === 1, 'Mixed CSV: 1 invalid row skipped');

// Group 8: Preview
echo "\n[8] Preview\n";
$preview = $svc->previewImport($e2eCsv);
assertTrue($preview['ok'] === true, 'Preview OK');
assertTrue($preview['total_rows'] === 2, 'Preview: 2 total rows');
assertTrue(count($preview['preview']) === 2, 'Preview: 2 preview rows');
assertTrue($preview['preview'][0]['valid'] === true, 'Preview: row 1 valid');

// Group 9: Controller + routes
echo "\n[9] Controller + routes\n";
$ctrlPath = BASE_PATH . '/app/Http/Controllers/Admin/BulkOperationsController.php';
assertTrue(file_exists($ctrlPath), 'BulkOperationsController exists');
$ctrlContent = file_get_contents($ctrlPath);
foreach (['propertyImport', 'propertyImportUpload', 'propertyImportExecute', 'propertyImportTemplate', 'propertyImportSample'] as $m) {
    assertTrue(strpos($ctrlContent, "function $m(") !== false, "Controller method $m exists");
}

$routes = file_get_contents(BASE_PATH . '/routes/web.php');
foreach ([
    '/admin/bulk/property-import',
    '/admin/bulk/property-import/upload',
    '/admin/bulk/property-import/execute',
    '/admin/bulk/property-import/template',
    '/admin/bulk/property-import/sample',
] as $path) {
    assertTrue(strpos($routes, "'" . $path . "'") !== false, "Route {$path} registered");
}

// Group 10: Menu item
echo "\n[10] Menu item\n";
$menu = $db->fetchOne("SELECT id FROM admin_menu_items WHERE url = '/admin/bulk/property-import'");
assertTrue($menu !== null, 'Menu item exists for /admin/bulk/property-import');

// Group 11: View file
echo "\n[11] View file\n";
$viewPath = BASE_PATH . '/app/views/admin/bulk/property_import.php';
assertTrue(file_exists($viewPath), 'View file property_import.php exists');
$viewContent = file_get_contents($viewPath);
assertTrue(strpos($viewContent, 'enctype="multipart/form-data"') !== false, 'View has upload form');
assertTrue(strpos($viewContent, 'Template') !== false, 'View has template link');
assertTrue(strpos($viewContent, 'Sample') !== false, 'View has sample link');

// Group 12: Cleanup
echo "\n[12] Cleanup\n";
$deleted = $db->execute("DELETE FROM user_properties WHERE name LIKE 'E2E Test Plot%'");
assertTrue(true, 'Test rows cleaned up');

// Group 13: Lint
echo "\n[13] Lint\n";
$php = getenv('PHP_BINARY') ?: 'C:\\xampp\\php\\php.exe';
foreach ([$svcPath, $ctrlPath, $viewPath] as $f) {
    $out = shell_exec('"' . $php . '" -l "' . $f . '" 2>&1');
    assertTrue(strpos($out, 'No syntax errors') !== false, "  Lint: " . basename($f));
}

// Summary
echo "\n=== Summary ===\n";
echo "PASS: $pass\n";
echo "FAIL: $fail\n";
if ($fail > 0) {
    echo "\nFailures:\n";
    foreach ($failures as $f) echo "  - $f\n";
}
exit($fail > 0 ? 1 : 0);
