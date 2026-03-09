<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SecurityServiceNew;
use App\Http\Controllers\SecurityController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SecurityServiceTest extends TestCase
{
    use RefreshDatabase;

    private SecurityServiceNew $securityService;
    private SecurityController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityService = app(SecurityServiceNew::class);
        $this->controller = new SecurityController($this->securityService);
    }

    /** @test */
    public function it_can_run_security_tests()
    {
        $results = $this->securityService->runSecurityTests();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('status', $results);
        $this->assertArrayHasKey('passed', $results);
        $this->assertArrayHasKey('failed', $results);
        $this->assertArrayHasKey('total', $results);
        $this->assertArrayHasKey('score', $results);
        $this->assertArrayHasKey('results', $results);
        $this->assertArrayHasKey('duration', $results);
        $this->assertArrayHasKey('timestamp', $results);
    }

    /** @test */
    public function it_can_get_security_score()
    {
        $score = $this->securityService->getSecurityScore();

        $this->assertIsArray($score);
        $this->assertArrayHasKey('score', $score);
        $this->assertArrayHasKey('total_tests', $score);
        $this->assertArrayHasKey('passed_tests', $score);
        $this->assertArrayHasKey('failed_tests', $score);
        $this->assertArrayHasKey('status', $score);
        $this->assertArrayHasKey('last_tested', $score);
        $this->assertIsInt($score['score']);
        $this->assertGreaterThanOrEqual(0, $score['score']);
        $this->assertLessThanOrEqual(100, $score['score']);
    }

    /** @test */
    public function it_can_generate_html_report()
    {
        $htmlReport = $this->securityService->generateHtmlReport();

        $this->assertIsString($htmlReport);
        $this->assertStringContains('<!DOCTYPE html>', $htmlReport);
        $this->assertStringContains('<title>Security Test Report', $htmlReport);
        $this->assertStringContains('Security Test Report', $htmlReport);
        $this->assertStringContains('Test Summary', $htmlReport);
    }

    /** @test */
    public function it_can_sanitize_input()
    {
        $xssInput = "<script>alert('XSS')</script>";
        $sanitized = $this->securityService->sanitizeInput($xssInput);

        $this->assertNotEquals($xssInput, $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized);
    }

    /** @test */
    public function it_can_validate_email()
    {
        $validEmail = 'test@example.com';
        $invalidEmail = 'invalid-email';

        $this->assertTrue($this->securityService->validateEmail($validEmail));
        $this->assertFalse($this->securityService->validateEmail($invalidEmail));
    }

    /** @test */
    public function it_can_validate_phone()
    {
        $validPhone = '9876543210';
        $invalidPhone = '123';

        $this->assertTrue($this->securityService->validatePhone($validPhone));
        $this->assertFalse($this->securityService->validatePhone($invalidPhone));
    }

    /** @test */
    public function it_can_hash_and_verify_password()
    {
        $password = 'test_password_123';
        $hashedPassword = $this->securityService->hashPassword($password);

        $this->assertIsString($hashedPassword);
        $this->assertNotEquals($password, $hashedPassword);
        $this->assertStringStartsWith('$argon2', $hashedPassword);

        $this->assertTrue($this->securityService->verifyPassword($password, $hashedPassword));
        $this->assertFalse($this->securityService->verifyPassword('wrong_password', $hashedPassword));
    }

    /** @test */
    public function it_can_generate_and_validate_csrf_token()
    {
        $token = $this->securityService->generateCsrfToken();

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        $this->assertTrue($this->securityService->validateCsrfToken($token));
        $this->assertFalse($this->securityService->validateCsrfToken('invalid_token'));
    }

    /** @test */
    public function it_can_check_rate_limit()
    {
        $key = 'test_key_' . time();
        
        // First check should pass
        $this->assertTrue($this->securityService->checkRateLimit($key, 5, 60));
        
        // Multiple checks within limit should pass
        for ($i = 0; $i < 4; $i++) {
            $this->assertTrue($this->securityService->checkRateLimit($key, 5, 60));
        }
    }

    /** @test */
    public function it_can_detect_suspicious_activity()
    {
        $request = new Request([
            'normal_input' => 'safe data',
            'xss_attempt' => '<script>alert("xss")</script>',
            'sql_injection' => "'; DROP TABLE users; --",
            'javascript_injection' => 'javascript:alert(1)'
        ]);

        $suspicious = $this->securityService->detectSuspiciousActivity($request);

        $this->assertIsArray($suspicious);
        $this->assertNotEmpty($suspicious);
        $this->assertContains('XSS attempt', $suspicious);
        $this->assertContains('SQL injection attempt', $suspicious);
        $this->assertContains('JavaScript injection', $suspicious);
    }

    /** @test */
    public function it_can_get_security_recommendations()
    {
        $recommendations = $this->securityService->getSecurityRecommendations();

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
        $this->assertArrayHasKey('Enable HTTPS', $recommendations);
        $this->assertArrayHasKey('Security Headers', $recommendations);
        $this->assertArrayHasKey('Input Validation', $recommendations);
        $this->assertArrayHasKey('Session Security', $recommendations);
        $this->assertArrayHasKey('Rate Limiting', $recommendations);
        $this->assertArrayHasKey('CSRF Protection', $recommendations);
        $this->assertArrayHasKey('File Upload Security', $recommendations);
        $this->assertArrayHasKey('Database Security', $recommendations);
        $this->assertArrayHasKey('Regular Audits', $recommendations);
    }

    /** @test */
    public function security_api_endpoints_work()
    {
        // Test run tests endpoint
        $response = $this->getJson('/api/security/run-tests');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);

        // Test score endpoint
        $response = $this->getJson('/api/security/score');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test recommendations endpoint
        $response = $this->getJson('/api/security/recommendations');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test dashboard endpoint
        $response = $this->getJson('/api/security/dashboard');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test CSRF token endpoint
        $response = $this->getJson('/api/security/csrf-token');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.csrf_token']);
    }

    /** @test */
    public function it_can_validate_input_via_api()
    {
        $response = $this->postJson('/api/security/validate-input', [
            'input' => '<script>alert("test")</script>',
            'type' => 'general'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $response->assertJson([
            'success' => true
        ]);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('original', $data);
        $this->assertArrayHasKey('sanitized', $data);
        $this->assertArrayHasKey('is_valid', $data);
        $this->assertStringNotContainsString('<script>', $data['sanitized']);
    }

    /** @test */
    public function it_can_validate_email_via_api()
    {
        $response = $this->postJson('/api/security/validate-input', [
            'input' => 'test@example.com',
            'type' => 'email'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data.is_valid' => true
        ]);
    }

    /** @test */
    public function it_can_hash_password_via_api()
    {
        $response = $this->postJson('/api/security/hash-password', [
            'password' => 'test_password_123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data.hashed_password']);
        $response->assertJson([
            'success' => true,
            'message' => 'Password hashed successfully'
        ]);
        
        $hashedPassword = $response->json('data.hashed_password');
        $this->assertStringStartsWith('$argon2', $hashedPassword);
    }

    /** @test */
    public function it_can_verify_password_via_api()
    {
        $password = 'test_password_123';
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $response = $this->postJson('/api/security/verify-password', [
            'password' => $password,
            'hash' => $hashedPassword
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data.is_valid' => true
        ]);
    }

    /** @test */
    public function it_can_detect_suspicious_activity_via_api()
    {
        $response = $this->postJson('/api/security/detect-suspicious', [
            'malicious_input' => '<script>alert("xss")</script>',
            'sql_injection' => "'; DROP TABLE users; --"
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $response->assertJson([
            'success' => true,
            'data.is_suspicious' => true
        ]);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('suspicious_activity', $data);
        $this->assertArrayHasKey('risk_level', $data);
        $this->assertNotEmpty($data['suspicious_activity']);
    }

    /** @test */
    public function it_can_log_security_event_via_api()
    {
        $response = $this->postJson('/api/security/log-event', [
            'event' => 'Test security event',
            'context' => [
                'ip' => '127.0.0.1',
                'user_agent' => 'Test Browser'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Security event logged successfully'
        ]);
    }

    /** @test */
    public function it_can_test_component_via_api()
    {
        $response = $this->postJson('/api/security/test-component', [
            'component' => 'input'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->assertJson([
            'success' => true,
            'data.component' => 'input',
            'data.status' => 'tested'
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test input validation without required fields
        $response = $this->postJson('/api/security/validate-input', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['input']);

        // Test password hashing without password
        $response = $this->postJson('/api/security/hash-password', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        // Test component testing with invalid component
        $response = $this->postJson('/api/security/test-component', [
            'component' => 'invalid_component'
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['component']);
    }

    /** @test */
    public function it_handles_invalid_password_verification()
    {
        $response = $this->postJson('/api/security/verify-password', [
            'password' => 'wrong_password',
            'hash' => '$argon2$hash$invalid'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data.is_valid' => false
        ]);
    }

    /** @test */
    public function it_generates_security_report_via_api()
    {
        $response = $this->postJson('/api/security/generate-report');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->assertJson([
            'success' => true,
            'message' => 'Security report generated successfully'
        ]);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('report_path', $data);
        $this->assertArrayHasKey('download_url', $data);
        $this->assertStringContains('security_test_report_', $data['report_path']);
    }

    /** @test */
    public function it_can_check_rate_limit_via_api()
    {
        $response = $this->postJson('/api/security/check-rate-limit', [
            'key' => 'test_key_' . time(),
            'max_attempts' => 5,
            'time_window' => 60
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $response->assertJson([
            'success' => true
        ]);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('is_allowed', $data);
        $this->assertArrayHasKey('key', $data);
        $this->assertArrayHasKey('max_attempts', $data);
        $this->assertArrayHasKey('time_window', $data);
        $this->assertEquals(5, $data['max_attempts']);
        $this->assertEquals(60, $data['time_window']);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
