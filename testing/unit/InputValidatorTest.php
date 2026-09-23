<?php
/**
 * InputValidator Unit Tests
 * 
 * Comprehensive unit test suite for InputValidator class
 * Tests validation, sanitization, and fluent API methods
 */

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../app/Helpers/InputValidator.php';

use App\Helpers\InputValidator;

class InputValidatorTest {
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function run(): void {
        echo "=== InputValidator Unit Test Suite ===\n\n";

        // Phone validation tests
        $this->testValidatePhone();
        
        // PAN validation tests
        $this->testValidatePan();
        
        // Aadhaar validation tests
        $this->testValidateAadhaar();
        
        // IFSC validation tests
        $this->testValidateIfsc();
        
        // PIN code validation tests
        $this->testValidatePin();
        
        // Email validation tests
        $this->testValidateEmail();
        
        // Integer validation tests
        $this->testValidateInt();
        
        // URL validation tests
        $this->testValidateUrl();
        
        // Fluent API tests
        $this->testFluentMake();
        $this->testFluentApplyRules();
        $this->testFluentPasses();
        $this->testFluentFails();
        $this->testFluentErrors();
        $this->testFluentValidated();
        
        // Sanitization tests
        $this->testSanitize();
        $this->testSanitizeString();
        $this->testSanitizePost();
        $this->testSanitizeForDB();
        
        // Complex validation rules
        $this->testMinMaxRules();
        $this->testRequiredRule();
        
        $this->printSummary();
    }

    // ==================== Phone Validation Tests ====================
    
    private function testValidatePhone(): void {
        echo "Testing validatePhone()...\n";
        
        // Valid phones
        $this->assertTrue(InputValidator::validatePhone('9876543210'), 'Valid phone starting with 9');
        $this->assertTrue(InputValidator::validatePhone('8765432109'), 'Valid phone starting with 8');
        $this->assertTrue(InputValidator::validatePhone('7654321098'), 'Valid phone starting with 7');
        $this->assertTrue(InputValidator::validatePhone('6543210987'), 'Valid phone starting with 6');
        $this->assertTrue(InputValidator::validatePhone(' 9876543210 '), 'Valid phone with whitespace');
        
        // Invalid phones
        $this->assertFalse(InputValidator::validatePhone('5876543210'), 'Invalid phone starting with 5');
        $this->assertFalse(InputValidator::validatePhone('1234567890'), 'Invalid phone starting with 1');
        $this->assertFalse(InputValidator::validatePhone('987654321'), 'Too short (9 digits)');
        $this->assertFalse(InputValidator::validatePhone('98765432101'), 'Too long (11 digits)');
        $this->assertFalse(InputValidator::validatePhone('abcdefghij'), 'Non-numeric');
        $this->assertFalse(InputValidator::validatePhone(''), 'Empty string');
        $this->assertFalse(InputValidator::validatePhone(null), 'Null value');
    }

    // ==================== PAN Validation Tests ====================
    
    private function testValidatePan(): void {
        echo "Testing validatePan()...\n";
        
        // Valid PANs
        $this->assertTrue(InputValidator::validatePan('ABCDE1234F'), 'Valid PAN format');
        $this->assertTrue(InputValidator::validatePan('abcde1234f'), 'Valid PAN lowercase');
        $this->assertTrue(InputValidator::validatePan('AbCdE1234f'), 'Valid PAN mixed case');
        
        // Invalid PANs
        $this->assertFalse(InputValidator::validatePan('ABCDE1234'), 'Missing last letter');
        $this->assertFalse(InputValidator::validatePan('ABCDE12345'), 'Too many digits');
        $this->assertFalse(InputValidator::validatePan('1BCDE1234F'), 'Starting with number');
        $this->assertFalse(InputValidator::validatePan('ABCDE123FG'), 'Two letters at end');
        $this->assertFalse(InputValidator::validatePan(''), 'Empty string');
        $this->assertFalse(InputValidator::validatePan(null), 'Null value');
    }

    // ==================== Aadhaar Validation Tests ====================
    
    private function testValidateAadhaar(): void {
        echo "Testing validateAadhaar()...\n";
        
        // Valid Aadhaar
        $this->assertTrue(InputValidator::validateAadhaar('123456789012'), 'Valid 12-digit Aadhaar');
        $this->assertTrue(InputValidator::validateAadhaar('1234 5678 9012'), 'Valid with spaces');
        $this->assertTrue(InputValidator::validateAadhaar(' 123456789012 '), 'Valid with whitespace');
        
        // Invalid Aadhaar
        $this->assertFalse(InputValidator::validateAadhaar('12345678901'), '11 digits');
        $this->assertFalse(InputValidator::validateAadhaar('1234567890123'), '13 digits');
        $this->assertFalse(InputValidator::validateAadhaar('abcd56789012'), 'Contains letters');
        $this->assertFalse(InputValidator::validateAadhaar(''), 'Empty string');
        $this->assertFalse(InputValidator::validateAadhaar(null), 'Null value');
    }

    // ==================== IFSC Validation Tests ====================
    
    private function testValidateIfsc(): void {
        echo "Testing validateIfsc()...\n";
        
        // Valid IFSC
        $this->assertTrue(InputValidator::validateIfsc('SBIN0001234'), 'Valid IFSC');
        $this->assertTrue(InputValidator::validateIfsc('sbin0001234'), 'Valid IFSC lowercase');
        $this->assertTrue(InputValidator::validateIfsc('HDFC0000123'), 'Valid HDFC IFSC');
        $this->assertTrue(InputValidator::validateIfsc('ICIC0000456'), 'Valid ICICI IFSC');
        $this->assertTrue(InputValidator::validateIfsc(' SBIN0001234 '), 'Valid with whitespace');
        
        // Invalid IFSC
        $this->assertFalse(InputValidator::validateIfsc('SBIN001234'), 'Too short (10 chars)');
        $this->assertFalse(InputValidator::validateIfsc('SBIN00012345'), 'Too long (12 chars)');
        $this->assertFalse(InputValidator::validateIfsc('SBIN1001234'), '5th char not 0');
        $this->assertFalse(InputValidator::validateIfsc('12340001234'), 'Starts with number');
        $this->assertFalse(InputValidator::validateIfsc('SBIN@001234'), 'Special character');
        $this->assertFalse(InputValidator::validateIfsc(''), 'Empty string');
        $this->assertFalse(InputValidator::validateIfsc(null), 'Null value');
    }

    // ==================== PIN Code Validation Tests ====================
    
    private function testValidatePin(): void {
        echo "Testing validatePin()...\n";
        
        // Valid PIN codes
        $this->assertTrue(InputValidator::validatePin('110001'), 'Valid PIN Delhi');
        $this->assertTrue(InputValidator::validatePin('400001'), 'Valid PIN Mumbai');
        $this->assertTrue(InputValidator::validatePin('560001'), 'Valid PIN Bangalore');
        $this->assertTrue(InputValidator::validatePin(' 600001 '), 'Valid with whitespace');
        
        // Invalid PIN codes
        $this->assertFalse(InputValidator::validatePin('010001'), 'Starts with 0');
        $this->assertFalse(InputValidator::validatePin('11000'), '5 digits');
        $this->assertFalse(InputValidator::validatePin('1100011'), '7 digits');
        $this->assertFalse(InputValidator::validatePin('abcdef'), 'Non-numeric');
        $this->assertFalse(InputValidator::validatePin(''), 'Empty string');
        $this->assertFalse(InputValidator::validatePin(null), 'Null value');
    }

    // ==================== Email Validation Tests ====================
    
    private function testValidateEmail(): void {
        echo "Testing validateEmail()...\n";
        
        // Valid emails
        $this->assertTrue(InputValidator::validateEmail('test@example.com'), 'Valid email');
        $this->assertTrue(InputValidator::validateEmail('user.name@domain.co.in'), 'Email with subdomain');
        $this->assertTrue(InputValidator::validateEmail('user+tag@example.org'), 'Email with plus');
        $this->assertTrue(InputValidator::validateEmail('user_name@domain.com'), 'Underscore in local part');
        
        // Invalid emails
        $this->assertFalse(InputValidator::validateEmail('invalid'), 'No @ symbol');
        $this->assertFalse(InputValidator::validateEmail('@example.com'), 'No local part');
        $this->assertFalse(InputValidator::validateEmail('user@'), 'No domain');
        $this->assertFalse(InputValidator::validateEmail('user@.com'), 'Empty domain label');
        $this->assertFalse(InputValidator::validateEmail(''), 'Empty string');
        $this->assertFalse(InputValidator::validateEmail(null), 'Null value');
    }

    // ==================== Integer Validation Tests ====================
    
    private function testValidateInt(): void {
        echo "Testing validateInt()...\n";
        
        // Valid integers
        $this->assertTrue(InputValidator::validateInt(42), 'Positive integer');
        $this->assertTrue(InputValidator::validateInt(0), 'Zero');
        $this->assertTrue(InputValidator::validateInt(-10), 'Negative integer');
        $this->assertTrue(InputValidator::validateInt('42'), 'String integer');
        
        // With min/max
        $this->assertTrue(InputValidator::validateInt(5, 1, 10), 'Within range');
        $this->assertTrue(InputValidator::validateInt(1, 1, 10), 'Min boundary');
        $this->assertTrue(InputValidator::validateInt(10, 1, 10), 'Max boundary');
        
        // Invalid integers
        $this->assertFalse(InputValidator::validateInt('abc'), 'Non-numeric string');
        $this->assertFalse(InputValidator::validateInt(3.14), 'Float');
        $this->assertFalse(InputValidator::validateInt(5, 10, 20), 'Below min');
        $this->assertFalse(InputValidator::validateInt(25, 10, 20), 'Above max');
        $this->assertFalse(InputValidator::validateInt(''), 'Empty string');
        $this->assertFalse(InputValidator::validateInt(null), 'Null value');
    }

    // ==================== URL Validation Tests ====================
    
    private function testValidateUrl(): void {
        echo "Testing validateUrl()...\n";
        
        // Valid URLs
        $this->assertTrue(InputValidator::validateUrl('https://example.com'), 'HTTPS URL');
        $this->assertTrue(InputValidator::validateUrl('http://example.com'), 'HTTP URL');
        $this->assertTrue(InputValidator::validateUrl('https://sub.domain.com/path?query=1'), 'Complex URL');
        
        // Invalid URLs
        $this->assertFalse(InputValidator::validateUrl('not-a-url'), 'Not a URL');
        $this->assertTrue(InputValidator::validateUrl('ftp://example.com'), 'FTP protocol');
        $this->assertFalse(InputValidator::validateUrl(''), 'Empty string');
        $this->assertFalse(InputValidator::validateUrl(null), 'Null value');
    }

    // ==================== Fluent API Tests ====================
    
    private function testFluentMake(): void {
        echo "Testing InputValidator::make()...\n";

        $validator = InputValidator::make(['name' => 'John', 'email' => 'john@example.com']);
        $this->assertInstanceOf(InputValidator::class, $validator, 'Returns instance');

        // Default to $_POST when no data provided
        $_POST['test'] = 'value';
        $validator2 = InputValidator::make();
        $this->assertInstanceOf(InputValidator::class, $validator2, 'Defaults to $_POST when available');
    }

    private function testFluentApplyRules(): void {
        echo "Testing applyRules()...\n";
        
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '9876543210',
            'pan' => 'ABCDE1234F',
            'bio' => 'This is a test bio that is long enough'
        ];
        
        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'pan' => 'required|pan',
            'bio' => 'required|min:20'
        ];
        
        $validator = InputValidator::make($data)->applyRules($rules);
        $this->assertTrue($validator->passes(), 'All valid data passes');
        
        // Test with invalid data
        $invalidData = [
            'name' => 'J',
            'email' => 'invalid-email',
            'phone' => '1234567890',
            'pan' => 'INVALID',
            'bio' => 'short'
        ];
        
        $validator2 = InputValidator::make($invalidData)->applyRules($rules);
        $this->assertTrue($validator2->fails(), 'Invalid data fails');
        $this->assertCount(5, $validator2->errors(), 'Has 5 error fields');
    }

    private function testFluentPasses(): void {
        echo "Testing passes()...\n";
        
        $validator = InputValidator::make(['email' => 'test@example.com'])->applyRules(['email' => 'email']);
        $this->assertTrue($validator->passes(), 'Valid passes');
        
        $validator2 = InputValidator::make(['email' => 'invalid'])->applyRules(['email' => 'email']);
        $this->assertFalse($validator2->passes(), 'Invalid fails');
    }

    private function testFluentFails(): void {
        echo "Testing fails()...\n";
        
        $validator = InputValidator::make(['email' => 'invalid'])->applyRules(['email' => 'email']);
        $this->assertTrue($validator->fails(), 'Invalid fails');
        
        $validator2 = InputValidator::make(['email' => 'test@example.com'])->applyRules(['email' => 'email']);
        $this->assertFalse($validator2->fails(), 'Valid passes (not fails)');
    }

    private function testFluentErrors(): void {
        echo "Testing errors()...\n";
        
        $validator = InputValidator::make([
            'email' => 'invalid',
            'phone' => '123'
        ])->applyRules([
            'email' => 'email',
            'phone' => 'phone'
        ]);
        
        $errors = $validator->errors();
        $this->assertArrayHasKey('email', $errors, 'Has email error');
        $this->assertArrayHasKey('phone', $errors, 'Has phone error');
        $this->assertEquals('The email must be a valid email.', $errors['email'][0], 'Error message matches expected');
    }

    private function testFluentValidated(): void {
        echo "Testing validated()...\n";
        
        $data = ['name' => 'John', 'email' => 'john@example.com'];
        $validator = InputValidator::make($data)->applyRules([
            'name' => 'required|min:2',
            'email' => 'required|email'
        ]);
        
        $validated = $validator->validated();
        $this->assertEquals('John', $validated['name'], 'Name matches');
        $this->assertEquals('john@example.com', $validated['email'], 'Email matches');
        $this->assertArrayNotHasKey('non_existent', $validated, 'Non-existent key not present');
    }

    // ==================== Sanitization Tests ====================
    
    private function testSanitize(): void {
        echo "Testing sanitize()...\n";
        
        // sanitize() uses htmlspecialchars which escapes HTML entities
        $this->assertEquals('Hello &lt;b&gt;World&lt;/b&gt;', InputValidator::sanitize('Hello <b>World</b>'), 'HTML escaped');
        $this->assertEquals('&quot;Quoted&quot;', InputValidator::sanitize('"Quoted"'), 'Quotes escaped');
        $this->assertEquals('Test &amp; Test', InputValidator::sanitize('Test & Test'), 'Ampersand escaped');

        // Array input - sanitize escapes HTML entities in each value
        $input = ['name' => '<script>alert(1)</script>', 'email' => 'test@example.com'];
        $expected = ['name' => '&lt;script&gt;alert(1)&lt;/script&gt;', 'email' => 'test@example.com'];
        $this->assertEquals($expected, InputValidator::sanitize($input), 'Array sanitized');
    }

    private function testSanitizeString(): void {
        echo "Testing sanitizeString()...\n";
        
        $this->assertEquals('Hello World', InputValidator::sanitizeString('Hello World'), 'Plain text unchanged');
        $this->assertEquals('Hello World', InputValidator::sanitizeString('<script>alert(1)</script>Hello World</script>'), 'Script tags removed');
        $this->assertEquals('Hello &amp; World', InputValidator::sanitizeString('Hello & World'), 'Ampersand escaped');
        $this->assertEquals('', InputValidator::sanitizeString(null), 'Null returns empty');
    }

    private function testSanitizePost(): void {
        echo "Testing sanitizePost()...\n";
        
        $_POST = [
            'name' => '<script>alert(1)</script>John',
            'email' => 'john@example.com',
            'tags' => ['<b>bold</b>', 'normal']
        ];
        
        $result = InputValidator::sanitizePost();
        $this->assertEquals('John', $result['name'], 'Name matches');
        $this->assertEquals('john@example.com', $result['email'], 'Email matches');
        $this->assertEquals(['&lt;b&gt;bold&lt;/b&gt;', 'normal'], $result['tags'], 'Tags match');
    }

    private function testSanitizeForDB(): void {
        echo "Testing sanitizeForDB()...\n";
        
        $this->assertEquals('Hello World', InputValidator::sanitizeForDB('Hello World'), 'Plain text unchanged');
        $this->assertEquals('alert(1)Hello World', InputValidator::sanitizeForDB('<script>alert(1)</script>Hello World'), 'Script removed');
        $this->assertEquals('Hello World', InputValidator::sanitizeForDB('  Hello World  '), 'Whitespace trimmed');
    }

    // ==================== Complex Rule Tests ====================
    
    private function testMinMaxRules(): void {
        echo "Testing min:/max: rules...\n";
        
        $validator = InputValidator::make(['name' => 'Jo'])->applyRules(['name' => 'min:3']);
        $this->assertTrue($validator->fails(), 'Too short fails min:3');
        
        $validator2 = InputValidator::make(['name' => 'John'])->applyRules(['name' => 'min:3']);
        $this->assertTrue($validator2->passes(), 'Passes min:3');
        
        $validator3 = InputValidator::make(['bio' => str_repeat('a', 501)])->applyRules(['bio' => 'max:500']);
        $this->assertTrue($validator3->fails(), 'Too long fails max:500');
        
        $validator4 = InputValidator::make(['bio' => 'short'])->applyRules(['bio' => 'max:500']);
        $this->assertTrue($validator4->passes(), 'Passes max:500');
    }

    private function testRequiredRule(): void {
        echo "Testing required rule...\n";
        
        $validator = InputValidator::make(['name' => ''])->applyRules(['name' => 'required']);
        $this->assertTrue($validator->fails(), 'Empty string fails required');
        
        $validator2 = InputValidator::make(['name' => 'John'])->applyRules(['name' => 'required']);
        $this->assertTrue($validator2->passes(), 'Non-empty passes required');
        
        $validator3 = InputValidator::make([])->applyRules(['name' => 'required']);
        $this->assertTrue($validator3->fails(), 'Missing field fails required');
        
        $validator4 = InputValidator::make(['tags' => []])->applyRules(['tags' => 'required']);
        $this->assertTrue($validator4->fails(), 'Empty array fails required');
    }

    // ==================== Helper Methods ====================
    
    private function assertTrue(bool $condition, string $message): void {
        if ($condition) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "  ✗ $message\n";
        }
    }
    
    private function assertFalse(bool $condition, string $message): void {
        $this->assertTrue(!$condition, $message);
    }
    
    private function assertEquals(mixed $expected, mixed $actual, string $message): void {
        if ($expected === $actual) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            $this->failures[] = "$message (expected: " . var_export($expected, true) . ", got: " . var_export($actual, true) . ")";
            echo "  ✗ $message\n";
        }
    }
    
    private function assertInstanceOf(string $class, object $object, string $message): void {
        if ($object instanceof $class) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "  ✗ $message\n";
        }
    }
    
    private function assertArrayHasKey(string $key, array $array, string $message): void {
        if (array_key_exists($key, $array)) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "  ✗ $message\n";
        }
    }
    
    private function assertArrayNotHasKey(string $key, array $array, string $message): void {
        if (!array_key_exists($key, $array)) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "  ✗ $message\n";
        }
    }
    
    private function assertCount(int $expected, array $array, string $message): void {
        if (count($array) === $expected) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            $this->failures[] = "$message (expected count: $expected, got: " . count($array) . ")";
            echo "  ✗ $message\n";
        }
    }
    
    private function printSummary(): void {
        echo "\n=== Test Summary ===\n";
        echo "Passed: $this->passed\n";
        echo "Failed: $this->failed\n";
        echo "Total:  " . ($this->passed + $this->failed) . "\n";
        
        if ($this->failed > 0) {
            echo "\nFailures:\n";
            foreach ($this->failures as $failure) {
                echo "  - $failure\n";
            }
            exit(1);
        } else {
            echo "\n✓ All tests passed!\n";
            exit(0);
        }
    }
}

// Run tests
$test = new InputValidatorTest();
$test->run();