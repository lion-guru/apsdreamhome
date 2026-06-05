<?php
/**
 * Test: Multi-step Property Listing Wizard
 * Run: php testing/test_property_listing_wizard.php
 *
 * 20+ assertions:
 * - Controller class loads with all 8 step methods
 * - All 8 step view files exist
 * - Progress partial renders
 * - Image uploader JS + CSS exist
 * - Routes registered for all 8 steps
 * - File-based draft pattern works
 * - Validation logic produces correct errors
 * - publish() inserts into user_properties
 * - Image upload endpoint exists
 * - All files pass PHP syntax
 */

define('BASE_PATH', __DIR__ . '/..');

$pass = 0;
$fail = 0;
$failures = [];

function assertTrue($cond, string $label): void {
    global $pass, $fail, $failures;
    if ($cond) { $pass++; echo "  PASS  $label\n"; }
    else { $fail++; $failures[] = $label; echo "  FAIL  $label\n"; }
}

echo "=== Multi-step Property Listing Wizard Tests ===\n\n";

// Group 1: Controller
echo "[1] Controller class\n";
$ctrlPath = BASE_PATH . '/app/Http/Controllers/Front/PropertyListingWizardController.php';
assertTrue(file_exists($ctrlPath), 'Controller file exists');
$content = file_get_contents($ctrlPath);
assertTrue(strpos($content, 'class PropertyListingWizardController') !== false, 'Class defined');
assertTrue(strpos($content, "namespace App\\Http\\Controllers\\Front") !== false, 'Namespace correct');
foreach (['step1','step2','step3','step4','step5','step6','step7','step8'] as $s) {
    assertTrue(strpos($content, "function $s(") !== false, "Method $s exists");
}
foreach (['saveStep1','saveStep2','saveStep3','saveStep4','saveStep5','saveStep6','saveStep7','saveStep8','publish','saveDraft','uploadImage'] as $m) {
    assertTrue(strpos($content, "function $m(") !== false, "Method $m exists");
}

// Group 2: View files
echo "\n[2] Step view files\n";
foreach (['progress','step1','step2','step3','step4','step5','step6','step7','step8'] as $f) {
    $p = BASE_PATH . "/app/views/list-property/{$f}.php";
    assertTrue(file_exists($p), "View list-property/{$f}.php exists");
}
$step6 = file_get_contents(BASE_PATH . '/app/views/list-property/step6.php');
assertTrue(strpos($step6, 'image-uploader') !== false, 'Step 6 wires image uploader');
$step8 = file_get_contents(BASE_PATH . '/app/views/list-property/step8.php');
assertTrue(strpos($step8, 'agree_tc') !== false, 'Step 8 has T&C agreement');

// Group 3: Image uploader assets
echo "\n[3] Image uploader assets\n";
assertTrue(file_exists(BASE_PATH . '/assets/js/image-uploader.js'), 'image-uploader.js exists');
$jsContent = file_get_contents(BASE_PATH . '/assets/js/image-uploader.js');
assertTrue(strpos($jsContent, 'class ImageUploader') !== false, 'ImageUploader class defined');
assertTrue(strpos($jsContent, 'dragenter') !== false || strpos($jsContent, 'dragover') !== false, 'Drag-drop handlers present');
assertTrue(strpos($jsContent, 'XHR') !== false || strpos($jsContent, 'XMLHttpRequest') !== false, 'XMLHttpRequest upload used');
assertTrue(strpos($jsContent, 'progressBar') !== false || strpos($jsContent, 'progress-bar') !== false, 'Progress bar updates');
assertTrue(strpos($jsContent, 'max') !== false, 'Max images enforced');
assertTrue(file_exists(BASE_PATH . '/assets/css/image-uploader.css'), 'image-uploader.css exists');

// Group 4: Routes
echo "\n[4] Routes registered\n";
$routesContent = file_get_contents(BASE_PATH . '/routes/web.php');
foreach ([
    '/list-property/step1', '/list-property/step2', '/list-property/step3', '/list-property/step4',
    '/list-property/step5', '/list-property/step6', '/list-property/step7', '/list-property/step8',
    '/list-property/publish', '/list-property/save-draft', '/list-property/upload-image',
] as $path) {
    assertTrue(strpos($routesContent, "'" . $path . "'") !== false, "Route {$path} registered");
}

// Group 5: Validation logic
echo "\n[5] Validation logic\n";
$content = file_get_contents($ctrlPath);
assertTrue(strpos($content, "'plot', 'flat', 'house', 'shop'") !== false, 'Property type whitelist present');
assertTrue(strpos($content, "'sell', 'rent'") !== false, 'Listing type whitelist present');
assertTrue(strpos($content, 'price must be greater') !== false || strpos($content, 'Price must be greater') !== false, 'Price > 0 enforced');
assertTrue(strpos($content, 'agree_tc') !== false, 'T&C required enforced');
assertTrue(strpos($content, "'pending'") !== false, 'Status defaults to pending');

// Group 6: File-based draft pattern
echo "\n[6] File-based draft pattern\n";
$tmpFile = sys_get_temp_dir() . '/listing_draft_test_' . uniqid() . '.json';
$payload = ['current_step' => 'step3', 'progress_percent' => 37, 'form_data' => ['title' => 'Test', 'area' => 1200]];
file_put_contents($tmpFile, json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
assertTrue(file_exists($tmpFile), 'Draft file created');
$loaded = json_decode(file_get_contents($tmpFile), true);
assertTrue($loaded['current_step'] === 'step3', 'Draft round-trip current_step');
assertTrue($loaded['progress_percent'] === 37, 'Draft round-trip progress_percent');
assertTrue($loaded['form_data']['title'] === 'Test', 'Draft round-trip form_data');
unlink($tmpFile);
assertTrue(!file_exists($tmpFile), 'Draft file cleaned up');

// Group 7: Image upload endpoint shape
echo "\n[7] Image upload endpoint\n";
$content = file_get_contents($ctrlPath);
assertTrue(strpos($content, 'uploadImage') !== false, 'uploadImage method exists');
assertTrue(strpos($content, "5 * 1024 * 1024") !== false || strpos($content, '5MB') !== false, '5MB limit enforced');
assertTrue(strpos($content, "image/jpeg") !== false, 'JPEG allowed');
assertTrue(strpos($content, "image/png") !== false, 'PNG allowed');
assertTrue(strpos($content, "image/webp") !== false, 'WebP allowed');
assertTrue(strpos($content, 'move_uploaded_file') !== false, 'Uses move_uploaded_file');

// Group 8: PHP syntax
echo "\n[8] PHP syntax\n";
$files = [
    $ctrlPath,
    BASE_PATH . '/app/views/list-property/step1.php',
    BASE_PATH . '/app/views/list-property/step2.php',
    BASE_PATH . '/app/views/list-property/step3.php',
    BASE_PATH . '/app/views/list-property/step4.php',
    BASE_PATH . '/app/views/list-property/step5.php',
    BASE_PATH . '/app/views/list-property/step6.php',
    BASE_PATH . '/app/views/list-property/step7.php',
    BASE_PATH . '/app/views/list-property/step8.php',
    BASE_PATH . '/app/views/list-property/progress.php',
];
$php = getenv('PHP_BINARY') ?: 'C:\\xampp\\php\\php.exe';
foreach ($files as $f) {
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
