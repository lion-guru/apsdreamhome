<?php
/**
 * Unit Tests: InputValidator & SimpleCaptcha Helpers
 *
 * Tests for:
 *  - Indian identity formats (Phone, PAN, Aadhaar, PIN, IFSC)
 *  - Standard validations (Email, Int, URL, Sanitization)
 *  - Fluent validation builder (make, applyRules, passes, fails, validated)
 *  - SimpleCaptcha (generation, session storage, case-insensitive verification, test bypass)
 *
 * Run: php testing/unit/test_input_validator_and_captcha.php
 */

define('APS_ROOT', dirname(__DIR__, 2));
require_once APS_ROOT . '/config/bootstrap.php';

use App\Helpers\InputValidator;
use App\Helpers\SimpleCaptcha;

// ── Assertion helpers ──
$pass = 0;
$fail = 0;

function assert_true(bool $condition, string $msg): void {
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo "  ✅ $msg\n";
    } else {
        $fail++;
        echo "  ❌ $msg\n";
    }
}

function assert_false(bool $condition, string $msg): void {
    assert_true(!$condition, $msg);
}

function assert_equals($expected, $actual, string $msg): void {
    global $pass, $fail;
    if ($expected === $actual) {
        $pass++;
        echo "  ✅ $msg\n";
    } else {
        $fail++;
        echo "  ❌ $msg — expected " . var_export($expected, true) . ", got " . var_export($actual, true) . "\n";
    }
}

function assert_greater($min, $value, string $msg): void {
    global $pass, $fail;
    if ($value > $min) {
        $pass++;
        echo "  ✅ $msg\n";
    } else {
        $fail++;
        echo "  ❌ $msg — expected > $min, got $value\n";
    }
}

function section(string $title): void {
    echo "\n── $title ──\n";
}

echo "========================================================\n";
echo "  RUNNING UNIT TESTS: InputValidator & SimpleCaptcha\n";
echo "========================================================\n";

// ── 1. Phone Number Validation ──
section("1. Phone Number Validation (Indian 10-digit, 6-9 prefix)");
assert_true(InputValidator::validatePhone('9876543210'), "Valid 9-series phone passes");
assert_true(InputValidator::validatePhone('8123456789'), "Valid 8-series phone passes");
assert_true(InputValidator::validatePhone('7000000000'), "Valid 7-series phone passes");
assert_true(InputValidator::validatePhone('6999999999'), "Valid 6-series phone passes");
assert_false(InputValidator::validatePhone('5123456789'), "Invalid 5-series phone rejected");
assert_false(InputValidator::validatePhone('1234567890'), "Invalid 1-series phone rejected");
assert_false(InputValidator::validatePhone('987654321'), "9 digits phone rejected");
assert_false(InputValidator::validatePhone('98765432100'), "11 digits phone rejected");
assert_false(InputValidator::validatePhone('98765abcde'), "Alpha characters rejected");
assert_false(InputValidator::validatePhone(''), "Empty phone rejected");
assert_false(InputValidator::validatePhone(null), "Null phone rejected");

// ── 2. PAN Card Validation ──
section("2. PAN Card Validation (ABCDE1234F)");
assert_true(InputValidator::validatePan('ABCDE1234F'), "Valid uppercase PAN passes");
assert_true(InputValidator::validatePan('abcde1234f'), "Valid lowercase PAN passes");
assert_true(InputValidator::validatePan('BKZPK9876M'), "Valid random PAN passes");
assert_false(InputValidator::validatePan('ABCD1234F'), "Short PAN 4-alpha rejected");
assert_false(InputValidator::validatePan('ABCDEF1234'), "No trailing alpha rejected");
assert_false(InputValidator::validatePan('12345ABCDE'), "Digits first rejected");
assert_false(InputValidator::validatePan('ABCDE12345'), "Trailing digit rejected");
assert_false(InputValidator::validatePan(''), "Empty PAN rejected");

// ── 3. Aadhaar Number Validation ──
section("3. Aadhaar Number Validation (12 digits)");
assert_true(InputValidator::validateAadhaar('123456789012'), "Valid continuous 12-digit Aadhaar passes");
assert_true(InputValidator::validateAadhaar('1234 5678 9012'), "Valid space-separated Aadhaar passes");
assert_false(InputValidator::validateAadhaar('12345678901'), "11 digits rejected");
assert_false(InputValidator::validateAadhaar('1234567890123'), "13 digits rejected");
assert_false(InputValidator::validateAadhaar('12345678901A'), "Alphanumeric Aadhaar rejected");
assert_false(InputValidator::validateAadhaar(''), "Empty Aadhaar rejected");

// ── 4. PIN Code Validation ──
section("4. Postal PIN Code Validation (6 digits, non-zero start)");
assert_true(InputValidator::validatePin('110001'), "Valid Delhi PIN passes");
assert_true(InputValidator::validatePin('226001'), "Valid Lucknow PIN passes");
assert_true(InputValidator::validatePin('800001'), "Valid Patna PIN passes");
assert_false(InputValidator::validatePin('012345'), "Zero-starting PIN rejected");
assert_false(InputValidator::validatePin('12345'), "5-digit PIN rejected");
assert_false(InputValidator::validatePin('1234567'), "7-digit PIN rejected");
assert_false(InputValidator::validatePin('22600A'), "Alphanumeric PIN rejected");

// ── 5. IFSC Code Validation ──
section("5. Bank IFSC Code Validation (4 letters, 0, 6 alphanumerics)");
assert_true(InputValidator::validateIfsc('SBIN0001234'), "Valid SBI IFSC passes");
assert_true(InputValidator::validateIfsc('HDFC0000001'), "Valid HDFC IFSC passes");
assert_true(InputValidator::validateIfsc('sbin0001234'), "Case-insensitive IFSC passes");
assert_true(InputValidator::validateIfsc('PUNB0123400'), "Valid PNB IFSC passes");
assert_false(InputValidator::validateIfsc('SBIN1001234'), "5th char non-zero rejected");
assert_false(InputValidator::validateIfsc('SBI0001234'), "3-letter bank code rejected");
assert_false(InputValidator::validateIfsc('SBIN00012345'), "12-char IFSC rejected");

// ── 6. Email, Int & URL Standard Validations ──
section("6. Email, Integer & URL Validations");
assert_true(InputValidator::validateEmail('test@apsdreamhome.com'), "Valid standard email passes");
assert_true(InputValidator::validateEmail('user.name+tag@sub.domain.co.in'), "Complex valid email passes");
assert_false(InputValidator::validateEmail('not-an-email'), "Plain string rejected as email");
assert_false(InputValidator::validateEmail('@domain.com'), "Missing local part rejected");
assert_true(InputValidator::validateInt(10, 1, 100), "Integer in range passes");
assert_true(InputValidator::validateInt('42', 0, 50), "String integer in range passes");
assert_false(InputValidator::validateInt(150, 1, 100), "Integer above max rejected");
assert_false(InputValidator::validateInt(-5, 0, 100), "Integer below min rejected");
assert_false(InputValidator::validateInt('abc'), "Non-numeric string rejected as int");
assert_true(InputValidator::validateUrl('https://apsdreamhome.com/properties'), "Valid HTTPS URL passes");
assert_true(InputValidator::validateUrl('http://localhost:8000/api'), "Valid HTTP URL passes");
assert_false(InputValidator::validateUrl('invalid-url-format'), "Invalid URL string rejected");

// ── 7. Sanitization Helpers ──
section("7. Sanitization Helpers");
$rawXss = '<script>alert("XSS")</script><b>Hello</b>';
$sanitized = InputValidator::sanitizeString($rawXss);
assert_equals('Hello', $sanitized, "sanitizeString strips dangerous HTML tags and scripts");

// ── 8. Fluent Form Validator Execution ──
section("8. Fluent Form Validator Rules");
$validPayload = [
    'name' => 'Rajesh Sharma',
    'email' => 'rajesh@example.com',
    'phone' => '9876543210',
    'pan' => 'ABCDE1234F',
    'aadhaar' => '123456789012',
    'pin' => '226001',
    'ifsc' => 'SBIN0001234',
];

$vPass = InputValidator::make($validPayload)->applyRules([
    'name' => 'required|min:3',
    'email' => 'required|email',
    'phone' => 'required|phone',
    'pan' => 'required|pan',
    'aadhaar' => 'required|aadhaar',
    'pin' => 'required|pin',
    'ifsc' => 'required|ifsc',
]);

assert_true($vPass->passes(), "Valid form payload passes all rules");
assert_false($vPass->fails(), "Valid form has no failures");
assert_equals(0, count($vPass->errors()), "Zero validation error messages");

$invalidPayload = [
    'name' => 'R',
    'email' => 'bad-email',
    'phone' => '12345',
    'pan' => 'INVALID',
    'pin' => '00',
];

$vFail = InputValidator::make($invalidPayload)->applyRules([
    'name' => 'required|min:3',
    'email' => 'required|email',
    'phone' => 'required|phone',
    'pan' => 'required|pan',
    'pin' => 'required|pin',
]);

assert_true($vFail->fails(), "Invalid form payload triggers validation failures");
assert_false($vFail->passes(), "Invalid form passes() returns false");
assert_greater(3, count($vFail->errors()), "Multiple validation errors recorded");

// ── 9. SimpleCaptcha Validation ──
section("9. SimpleCaptcha Generation & Verification");
$code = SimpleCaptcha::generate(6);
assert_equals(6, strlen($code), "SimpleCaptcha generates 6-character code");
assert_true(SimpleCaptcha::validate($code), "SimpleCaptcha validates exact generated code");
assert_false(SimpleCaptcha::validate('WRONG1'), "SimpleCaptcha rejects wrong code");

// Test dev bypass
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}
assert_true(SimpleCaptcha::validate('123456'), "SimpleCaptcha accepts dev bypass code 123456");
assert_true(SimpleCaptcha::validate('TEST'), "SimpleCaptcha accepts dev bypass code TEST");

echo "\n========================================================\n";
echo "  SUMMARY: {$pass} PASSED, {$fail} FAILED\n";
echo "========================================================\n";

exit($fail > 0 ? 1 : 0);
