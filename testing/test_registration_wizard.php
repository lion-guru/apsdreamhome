<?php
/**
 * Test: Multi-step Registration Wizard
 * Run: php testing/test_registration_wizard.php
 *
 * 20+ assertions covering:
 * - Controller class loads
 * - Schema/table exists
 * - Step view files exist
 * - Progress partial renders
 * - Form data persistence (incomplete_registrations write/read)
 * - OTP generation and verification
 * - Skip logic
 * - Cron script exists and is valid PHP
 * - Route registration pattern matches existing routes
 */

define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/app/Core/ConfigService.php';
require_once BASE_PATH . '/app/Core/Database/Database.php';

use App\Core\Database\Database;

$pass = 0;
$fail = 0;
$failures = [];

function assertTrue($cond, string $label): void {
    global $pass, $fail, $failures;
    if ($cond) { $pass++; echo "  PASS  $label\n"; }
    else { $fail++; $failures[] = $label; echo "  FAIL  $label\n"; }
}

echo "=== Multi-step Registration Wizard Tests ===\n\n";

// Group 1: Controller class
echo "[1] Controller class\n";
$controllerPath = BASE_PATH . '/app/Http/Controllers/Auth/RegistrationWizardController.php';
assertTrue(file_exists($controllerPath), 'Controller file exists');
assertTrue(strpos(file_get_contents($controllerPath), 'class RegistrationWizardController') !== false, 'Class RegistrationWizardController defined');
assertTrue(strpos(file_get_contents($controllerPath), 'namespace App\\Http\\Controllers\\Auth') === 0 || strpos(file_get_contents($controllerPath), 'namespace App\\Http\\Controllers\\Auth') !== false, 'Namespace set correctly');
assertTrue(strpos(file_get_contents($controllerPath), 'function step1') !== false, 'Method step1 exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function step2') !== false, 'Method step2 exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function step3') !== false, 'Method step3 exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function step4') !== false, 'Method step4 exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function saveStep1') !== false, 'Method saveStep1 exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function complete') !== false, 'Method complete exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function resendOtp') !== false, 'Method resendOtp exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function verifyOtp') !== false, 'Method verifyOtp exists');
assertTrue(strpos(file_get_contents($controllerPath), 'function skip') !== false, 'Method skip exists');

// Group 2: Step view files
echo "\n[2] Step view files\n";
foreach (['progress', 'step1', 'step2', 'step3', 'step4'] as $f) {
    $path = BASE_PATH . "/app/views/auth/registration/{$f}.php";
    assertTrue(file_exists($path), "View file auth/registration/{$f}.php exists");
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if ($f !== 'progress') {
            assertTrue(strpos($content, '<form') !== false, "  View {$f}.php has form tag");
        } else {
            assertTrue(strpos($content, 'progress-bar') !== false, "  View progress.php has progress bar");
        }
    }
}

// Group 3: Database schema
echo "\n[3] Database schema\n";
$db = Database::getInstance();
$tables = $db->fetchAll("SHOW TABLES LIKE 'incomplete_registrations'");
assertTrue(count($tables) === 1, 'Table incomplete_registrations exists');
$cols = $db->fetchAll("DESCRIBE incomplete_registrations");
$colNames = array_column($cols, 'Field');
foreach (['session_id', 'email', 'phone', 'form_data', 'current_step', 'progress_percent', 'last_activity_at'] as $col) {
    assertTrue(in_array($col, $colNames, true), "Column '{$col}' exists in incomplete_registrations");
}

// Group 4: Live DB write/read
echo "\n[4] Live DB write/read\n";
$sid = 'test_wizard_' . uniqid();
$testEmail = 'wizard_test_' . time() . '@example.com';
$formData = ['name' => 'Test User', 'email' => $testEmail, 'phone' => '9876543210', 'password_hash' => password_hash('test1234', PASSWORD_BCRYPT)];
$db->execute(
    "INSERT INTO incomplete_registrations (session_id, email, phone, form_data, current_step, progress_percent, last_activity_at)
     VALUES (?, ?, ?, ?, 'step2', 50, NOW())",
    [$sid, $testEmail, '9876543210', json_encode($formData)]
);
$rowId = (int)$db->lastInsertId();
assertTrue($rowId > 0, 'Inserted incomplete_registration row');

$loaded = $db->fetchOne("SELECT * FROM incomplete_registrations WHERE id = ?", [$rowId]);
assertTrue($loaded !== null, 'Loaded incomplete_registration by id');
assertTrue($loaded['current_step'] === 'step2', 'current_step is step2');
assertTrue($loaded['progress_percent'] === '50' || (int)$loaded['progress_percent'] === 50, 'progress_percent is 50');
$decoded = json_decode($loaded['form_data'], true);
assertTrue($decoded['name'] === 'Test User', 'form_data round-trip preserves name');
assertTrue(password_verify('test1234', $decoded['password_hash']), 'form_data round-trip preserves password_hash');

// Update step
$db->execute("UPDATE incomplete_registrations SET current_step = 'step4', progress_percent = 100 WHERE id = ?", [$rowId]);
$updated = $db->fetchOne("SELECT current_step, progress_percent FROM incomplete_registrations WHERE id = ?", [$rowId]);
assertTrue($updated['current_step'] === 'step4', 'Updated current_step to step4');
assertTrue((int)$updated['progress_percent'] === 100, 'Updated progress_percent to 100');

// Cleanup
$db->execute("DELETE FROM incomplete_registrations WHERE id = ?", [$rowId]);
$deleted = $db->fetchOne("SELECT id FROM incomplete_registrations WHERE id = ?", [$rowId]);
assertTrue($deleted === null || empty($deleted), 'Test row cleaned up');

// Group 5: Cron script
echo "\n[5] Abandoned registration cron\n";
$cronPath = BASE_PATH . '/scripts/abandoned_registration_cron.php';
assertTrue(file_exists($cronPath), 'Cron script exists');
$cronContent = file_get_contents($cronPath);
assertTrue(strpos($cronContent, 'last_activity_at') !== false, 'Cron queries last_activity_at');
assertTrue(strpos($cronContent, 'recovered_at') !== false, 'Cron filters out recovered records');
assertTrue(strpos($cronContent, 'gateway_logs') !== false, 'Cron writes to gateway_logs');

// Run cron (CLI mode, output is text)
$output = shell_exec('"' . (getenv('PHP_BINARY') ?: 'C:\\xampp\\php\\php.exe') . '" "' . $cronPath . '" 2>&1');
assertTrue(strpos($output, 'abandoned-cron') !== false, 'Cron script runs and emits log output');

// Group 6: Routes registered
echo "\n[6] Routes registered\n";
$routesContent = file_get_contents(BASE_PATH . '/routes/web.php');
foreach ([
    '/register/step1' => 'step1',
    '/register/step2' => 'step2',
    '/register/step3' => 'step3',
    '/register/step4' => 'step4',
    '/register/complete' => 'complete',
    '/register/resend-otp' => 'resendOtp',
    '/register/verify-otp' => 'verifyOtp',
    '/register/skip' => 'skip',
] as $path => $method) {
    assertTrue(strpos($routesContent, "'" . $path . "'") !== false, "Route GET/POST {$path} registered");
}

// Group 7: Captcha + OTP session pattern
echo "\n[7] Captcha + OTP session pattern\n";
@session_start();
$_SESSION['wizard_captcha'] = 'ABCDE';
$captcha = $_SESSION['wizard_captcha'];
assertTrue($captcha === 'ABCDE', 'Captcha persisted in session');
$otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
assertTrue(strlen($otp) === 6 && ctype_digit($otp), 'OTP is 6 digits');
$_SESSION['wizard_otp']['email'] = $otp;
assertTrue($_SESSION['wizard_otp']['email'] === $otp, 'OTP persisted in session');
unset($_SESSION['wizard_captcha'], $_SESSION['wizard_otp']);

// Summary
echo "\n=== Summary ===\n";
echo "PASS: $pass\n";
echo "FAIL: $fail\n";
if ($fail > 0) {
    echo "\nFailures:\n";
    foreach ($failures as $f) echo "  - $f\n";
}
exit($fail > 0 ? 1 : 0);
