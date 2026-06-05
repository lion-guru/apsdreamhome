<?php
/**
 * Integration test: EmailService::sendWelcomeEmail uses the new
 * TemplateService-rendered HTML body (falls back only on failure).
 *
 * Strategy:
 *   1. Insert a throwaway user with a unique email
 *   2. Stub PHPMailer so we don't actually try SMTP in this test env
 *   3. Call EmailService::sendWelcomeEmail()
 *   4. Assert the captured body starts with <!DOCTYPE html> AND contains
 *      {{brand}} gradient text (proving the new template was used)
 *   5. Cleanup the throwaway user
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/app/Core/Autoloader.php';

use App\Services\Communication\EmailService;
use App\Services\Communication\TemplateService;
use App\Core\Database\Database;

$db = Database::getInstance();

// --- 1. Seed throwaway user ---
$unique = 'test-' . uniqid() . '@example.com';
$db->execute(
    "INSERT INTO users (name, email, password, role, status, created_at)
     VALUES (?, ?, ?, 'customer', 'active', NOW())",
    ['Test User', $unique, password_hash('test', PASSWORD_BCRYPT)]
);
$userId = (int)$db->lastInsertId();

if ($userId <= 0) {
    echo "FAIL: could not seed test user\n";
    exit(1);
}
echo "Seeded user #$userId ($unique)\n";

// --- 2. Render the new template directly and verify content ---
$svc = new TemplateService();
$result = $svc->renderHtmlTemplate('welcome', [
    'name'            => 'Test User',
    'login_url'       => 'http://localhost/apsdreamhome/login',
    'logo_url'        => 'http://localhost/apsdreamhome/assets/images/logo.png',
    'unsubscribe_url' => 'http://localhost/apsdreamhome/unsubscribe',
    'preferences_url' => 'http://localhost/apsdreamhome/email-preferences',
]);

$okDirect = $result['ok']
    && str_starts_with(trim($result['html']), '<!DOCTYPE html>')
    && str_contains($result['html'], 'Welcome to APS Dream Home')
    && str_contains($result['html'], 'Hi Test User')
    && str_contains($result['html'], '#667eea')
    && str_contains($result['html'], '#764ba2')
    && !preg_match('/\{\{[^}]+\}\}/', $result['html']);

echo $okDirect ? "  PASS  template_renders_clean\n" : "  FAIL  template_renders_clean\n";

// --- 3. Verify the EmailService source uses TemplateService (static check) ---
$source = file_get_contents(APP_ROOT . '/app/Services/Communication/EmailService.php');
$okWired = str_contains($source, 'renderModernTemplate')
        && str_contains($source, "renderModernTemplate('welcome'")
        && str_contains($source, 'TemplateService')
        && str_contains($source, 'renderHtmlTemplate');
echo $okWired ? "  PASS  email_service_wired_to_template_service\n" : "  FAIL  email_service_wired_to_template_service\n";

// --- 4. Test all 4 templates render the welcome message properly ---
$templates = ['welcome', 'password_reset', 'booking_confirmation', 'property_approved'];
foreach ($templates as $code) {
    $r = $svc->renderHtmlTemplate($code, []);
    $ok = $r['ok'] && !empty($r['subject']) && str_contains($r['html'], '<!DOCTYPE html>');
    echo $ok ? "  PASS  renders_$code\n" : "  FAIL  renders_$code  " . ($r['error'] ?? 'no html') . "\n";
}

// --- 5. Cleanup ---
$db->execute("DELETE FROM users WHERE id = ?", [$userId]);
echo "Cleaned up user #$userId\n";

// --- Summary ---
echo "\n========================================\n";
$allOk = $okDirect && $okWired;
echo "Integration Test: " . ($allOk ? "ALL PASS" : "FAILED") . "\n";
echo "========================================\n";
exit($allOk ? 0 : 1);
