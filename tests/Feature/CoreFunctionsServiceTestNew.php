<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CoreFunctionsServiceNew;
use App\Http\Controllers\CoreFunctionsControllerNew;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class CoreFunctionsServiceTestNew extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_log_admin_action()
    {
        $actionData = [
            'action' => 'test_action',
            'details' => ['test' => 'data'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser'
        ];

        $result = CoreFunctionsServiceNew::logAdminAction($actionData);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_validate_username_input()
    {
        $result = CoreFunctionsServiceNew::validateInput('testuser123', 'username');
        $this->assertNotFalse($result);
        $this->assertIsString($result);

        // Test invalid username
        $invalidResult = CoreFunctionsServiceNew::validateInput('ab', 'username');
        $this->assertFalse($invalidResult);
    }

    /** @test */
    public function it_can_validate_email_input()
    {
        $result = CoreFunctionsServiceNew::validateInput('test@example.com', 'email');
        $this->assertNotFalse($result);
        $this->assertEquals('test@example.com', $result);

        // Test invalid email
        $invalidResult = CoreFunctionsServiceNew::validateInput('invalid-email', 'email');
        $this->assertFalse($invalidResult);
    }

    /** @test */
    public function it_can_validate_password_input()
    {
        $password = 'testPassword123!';
        $result = CoreFunctionsServiceNew::validateInput($password, 'password');
        $this->assertEquals($password, $result);
    }

    /** @test */
    public function it_can_validate_phone_input()
    {
        $phone = '9876543210';
        $result = CoreFunctionsServiceNew::validateInput($phone, 'phone');
        $this->assertNotFalse($result);
        $this->assertEquals($phone, $result);
    }

    /** @test */
    public function it_can_validate_string_input()
    {
        $result = CoreFunctionsServiceNew::validateInput('test string', 'string', 50);
        $this->assertNotFalse($result);
        $this->assertIsString($result);

        // Test with max length
        $longString = str_repeat('a', 100);
        $invalidResult = CoreFunctionsServiceNew::validateInput($longString, 'string', 50);
        $this->assertFalse($invalidResult);
    }

    /** @test */
    public function it_can_validate_request_headers()
    {
        $result = CoreFunctionsServiceNew::validateRequestHeaders();
        $this->assertIsBool($result);
    }

    /** @test */
    public function it_can_send_security_response()
    {
        $response = CoreFunctionsServiceNew::sendSecurityResponse(400, 'Test error', ['data' => 'test']);

        $this->assertEquals(400, $response->getStatusCode());
        $response->assertJson([
            'success' => false,
            'status' => 'error',
            'message' => 'Test error',
            'data' => 'test'
        ]);
    }

    /** @test */
    public function it_can_get_current_url()
    {
        $url = CoreFunctionsServiceNew::getCurrentUrl();
        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }

    /** @test */
    public function it_can_generate_random_string()
    {
        $randomString = CoreFunctionsServiceNew::generateRandomString(16);
        $this->assertEquals(16, strlen($randomString));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $randomString);
    }

    /** @test */
    public function it_can_check_authentication_status()
    {
        $isAuthenticated = CoreFunctionsServiceNew::isAuthenticated();
        $this->assertIsBool($isAuthenticated);
    }

    /** @test */
    public function it_can_get_user_role()
    {
        $role = CoreFunctionsServiceNew::getUserRole();
        $this->assertIsString($role);
    }

    /** @test */
    public function it_can_format_currency()
    {
        $formatted = CoreFunctionsServiceNew::formatCurrency(1234.56, '$');
        $this->assertEquals('$1,234.56', $formatted);
    }

    /** @test */
    public function it_can_format_date()
    {
        $date = '2023-01-01';
        $formatted = CoreFunctionsServiceNew::formatDate($date, 'd/m/Y');
        $this->assertEquals('01/01/2023', $formatted);
    }

    /** @test */
    public function it_can_sanitize_filename()
    {
        $filename = '../../../etc/passwd';
        $sanitized = CoreFunctionsServiceNew::sanitizeFilename($filename);
        $this->assertStringNotContainsString('..', $sanitized);
        $this->assertStringNotContainsString('/', $sanitized);
        $this->assertStringNotContainsString('\\', $sanitized);
    }

    /** @test */
    public function it_can_get_file_extension()
    {
        $extension = CoreFunctionsServiceNew::getFileExtension('test.jpg');
        $this->assertEquals('jpg', $extension);
    }

    /** @test */
    public function it_can_check_if_file_is_image()
    {
        $isImage = CoreFunctionsServiceNew::isImageFile('test.jpg');
        $this->assertTrue($isImage);

        $isNotImage = CoreFunctionsServiceNew::isImageFile('test.txt');
        $this->assertFalse($isNotImage);
    }

    /** @test */
    public function it_can_generate_slug()
    {
        $slug = CoreFunctionsServiceNew::generateSlug('Test String With Spaces');
        $this->assertEquals('test-string-with-spaces', $slug);
    }

    /** @test */
    public function it_can_truncate_text()
    {
        $text = 'This is a long text that should be truncated';
        $truncated = CoreFunctionsServiceNew::truncateText($text, 20, '...');
        $this->assertEquals(23, strlen($truncated)); // 20 + 3 for ...
        $this->assertStringEndsWith('...', $truncated);
    }

    /** @test */
    public function it_can_get_client_ip()
    {
        $ip = CoreFunctionsServiceNew::getClientIp();
        $this->assertIsString($ip);
        $this->assertNotEmpty($ip);
    }

    /** @test */
    public function it_can_check_rate_limit()
    {
        $key = 'test_key_' . time();
        
        // First attempt should pass
        $result1 = CoreFunctionsServiceNew::checkRateLimit($key, 2, 60);
        $this->assertTrue($result1);
        
        // Second attempt should pass
        $result2 = CoreFunctionsServiceNew::checkRateLimit($key, 2, 60);
        $this->assertTrue($result2);
        
        // Third attempt should fail (exceeds limit of 2)
        $result3 = CoreFunctionsServiceNew::checkRateLimit($key, 2, 60);
        $this->assertFalse($result3);
    }

    /** @test */
    public function it_can_send_json_response()
    {
        $data = ['test' => 'data'];
        $response = CoreFunctionsServiceNew::sendJsonResponse($data, 200);
        
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson($data);
    }

    /** @test */
    public function it_can_check_ajax_request()
    {
        $isAjax = CoreFunctionsServiceNew::isAjaxRequest();
        $this->assertIsBool($isAjax);
    }

    /** @test */
    public function it_can_get_whatsapp_templates()
    {
        $templates = CoreFunctionsServiceNew::getWhatsAppTemplates();
        $this->assertIsArray($templates);
    }

    /** @test */
    public function it_can_hash_password()
    {
        $password = 'testPassword123';
        $hashed = CoreFunctionsServiceNew::hashPassword($password);
        
        $this->assertNotEquals($password, $hashed);
        $this->assertIsString($hashed);
        $this->assertNotEmpty($hashed);
    }

    /** @test */
    public function it_can_verify_password_hash()
    {
        $password = 'testPassword123';
        $hashed = CoreFunctionsServiceNew::hashPassword($password);
        
        $isValid = CoreFunctionsServiceNew::verifyPasswordHash($password, $hashed);
        $this->assertTrue($isValid);
        
        $isInvalid = CoreFunctionsServiceNew::verifyPasswordHash('wrongPassword', $hashed);
        $this->assertFalse($isInvalid);
    }

    /** @test */
    public function it_can_format_phone_number()
    {
        $phone = '9876543210';
        $formatted = CoreFunctionsServiceNew::formatPhoneNumber($phone);
        $this->assertEquals('919876543210', $formatted); // Should add India country code
    }

    /** @test */
    public function it_can_validate_phone_number()
    {
        $validPhone = '919876543210';
        $this->assertTrue(CoreFunctionsServiceNew::isValidPhoneNumber($validPhone));
        
        $invalidPhone = '123';
        $this->assertFalse(CoreFunctionsServiceNew::isValidPhoneNumber($invalidPhone));
    }

    /** @test */
    public function core_functions_api_endpoints_work()
    {
        // Test validate input endpoint
        $response = $this->postJson('/api/core-functions/validate-input', [
            'input' => 'test@example.com',
            'type' => 'email'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test generate random string endpoint
        $response = $this->postJson('/api/core-functions/generate-random-string', [
            'length' => 10
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.random_string']);

        // Test format currency endpoint
        $response = $this->postJson('/api/core-functions/format-currency', [
            'amount' => 1234.56,
            'currency' => '$'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.formatted']);

        // Test format date endpoint
        $response = $this->postJson('/api/core-functions/format-date', [
            'date' => '2023-01-01',
            'format' => 'd/m/Y'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.formatted']);

        // Test generate slug endpoint
        $response = $this->postJson('/api/core-functions/generate-slug', [
            'string' => 'Test String With Spaces'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.slug']);

        // Test truncate text endpoint
        $response = $this->postJson('/api/core-functions/truncate-text', [
            'text' => 'This is a long text that should be truncated',
            'length' => 20
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.truncated']);

        // Test get client IP endpoint
        $response = $this->getJson('/api/core-functions/get-client-ip');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.ip']);

        // Test hash password endpoint
        $response = $this->postJson('/api/core-functions/hash-password', [
            'password' => 'testPassword123'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.hashed_password']);

        // Test verify password hash endpoint
        $response = $this->postJson('/api/core-functions/verify-password-hash', [
            'password' => 'testPassword123',
            'hash' => password_hash('testPassword123', PASSWORD_DEFAULT)
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.is_valid']);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test validate input without required fields
        $response = $this->postJson('/api/core-functions/validate-input', [
            'input' => 'test'
            // Missing 'type' field
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);

        // Test format currency without amount
        $response = $this->postJson('/api/core-functions/format-currency', [
            'currency' => '$'
            // Missing 'amount' field
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);

        // Test format date without date
        $response = $this->postJson('/api/core-functions/format-date', [
            'format' => 'd/m/Y'
            // Missing 'date' field
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function it_handles_invalid_data_gracefully()
    {
        // Test invalid email
        $response = $this->postJson('/api/core-functions/validate-input', [
            'input' => 'invalid-email',
            'type' => 'email'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'is_valid' => false
            ]
        ]);

        // Test invalid phone
        $response = $this->postJson('/api/core-functions/validate-input', [
            'input' => '123',
            'type' => 'phone'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'is_valid' => false
            ]
        ]);

        // Test too long string
        $response = $this->postJson('/api/core-functions/validate-input', [
            'input' => str_repeat('a', 100),
            'type' => 'string',
            'max_length' => 50
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'is_valid' => false
            ]
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up any test data
        parent::tearDown();
    }
}
