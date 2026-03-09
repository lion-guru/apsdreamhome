<?php

namespace Tests\Feature\Utilities;

use App\Services\Utilities\UtilityService;
use PHPUnit\Framework\TestCase;

/**
 * Utility Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class UtilityServiceTest extends TestCase
{
    private $utilityService;
    
    protected function setUp(): void
    {
        $this->utilityService = new UtilityService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(UtilityService::class, $this->utilityService);
    }
    
    /** @test */
    public function it_can_generate_slug()
    {
        $result = $this->utilityService->generateSlug('Test String for Slug Generation');
        
        $this->assertEquals('test-string-for-slug-generation', $result);
        $this->assertIsString($result);
    }
    
    /** @test */
    public function it_handles_empty_slug_input()
    {
        $result = $this->utilityService->generateSlug('');
        
        $this->assertEquals('untitled', $result);
    }
    
    /** @test */
    public function it_can_format_currency()
    {
        // Test INR
        $inr = $this->utilityService->formatCurrency(1234567.89, 'INR');
        $this->assertEquals('₹1,234,567.89', $inr);
        
        // Test USD
        $usd = $this->utilityService->formatCurrency(1234567.89, 'USD');
        $this->assertEquals('$1,234,567.89', $usd);
        
        // Test EUR
        $eur = $this->utilityService->formatCurrency(1234567.89, 'EUR');
        $this->assertEquals('€1,234,567.89', $eur);
        
        // Test default currency
        $default = $this->utilityService->formatCurrency(1234.56);
        $this->assertEquals('₹1,234.56', $default);
    }
    
    /** @test */
    public function it_can_format_date()
    {
        $date = '2023-12-25 15:30:00';
        
        // Test default format
        $result = $this->utilityService->formatDate($date);
        $this->assertEquals('2023-12-25 15:30:00', $result);
        
        // Test custom format
        $custom = $this->utilityService->formatDate($date, 'd M Y');
        $this->assertEquals('25 Dec 2023', $custom);
        
        // Test empty date
        $empty = $this->utilityService->formatDate('');
        $this->assertEquals('', $empty);
    }
    
    /** @test */
    public function it_can_calculate_time_ago()
    {
        // Test recent time
        $recent = $this->utilityService->timeAgo(date('Y-m-d H:i:s', strtotime('-5 minutes')));
        $this->assertEquals('5 minutes ago', $recent);
        
        // Test hours ago
        $hours = $this->utilityService->timeAgo(date('Y-m-d H:i:s', strtotime('-2 hours')));
        $this->assertEquals('2 hours ago', $hours);
        
        // Test days ago
        $days = $this->utilityService->timeAgo(date('Y-m-d H:i:s', strtotime('-3 days')));
        $this->assertEquals('3 days ago', $days);
        
        // Test just now
        $justNow = $this->utilityService->timeAgo(date('Y-m-d H:i:s', strtotime('-30 seconds')));
        $this->assertEquals('Just now', $justNow);
        
        // Test empty datetime
        $empty = $this->utilityService->timeAgo('');
        $this->assertEquals('', $empty);
    }
    
    /** @test */
    public function it_can_truncate_text()
    {
        $text = 'This is a long text that should be truncated to demonstrate the functionality.';
        
        // Test basic truncation
        $result = $this->utilityService->truncateText($text, 50);
        $this->assertLessThanOrEqual(53, strlen($result)); // 50 + '...'
        $this->assertStringEndsWith('...', $result);
        
        // Test text shorter than limit
        $short = $this->utilityService->truncateText('Short text', 50);
        $this->assertEquals('Short text', $short);
        
        // Test custom suffix
        $customSuffix = $this->utilityService->truncateText($text, 30, ' [more]');
        $this->assertStringEndsWith(' [more]', $customSuffix);
    }
    
    /** @test */
    public function it_can_generate_random_string()
    {
        // Test alphanumeric
        $alnum = $this->utilityService->generateRandomString(16, 'alnum');
        $this->assertEquals(16, strlen($alnum));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $alnum);
        
        // Test alpha only
        $alpha = $this->utilityService->generateRandomString(10, 'alpha');
        $this->assertEquals(10, strlen($alpha));
        $this->assertMatchesRegularExpression('/^[a-zA-Z]+$/', $alpha);
        
        // Test numeric only
        $numeric = $this->utilityService->generateRandomString(8, 'numeric');
        $this->assertEquals(8, strlen($numeric));
        $this->assertMatchesRegularExpression('/^[0-9]+$/', $numeric);
        
        // Test default type
        $default = $this->utilityService->generateRandomString(12);
        $this->assertEquals(12, strlen($default));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $default);
    }
    
    /** @test */
    public function it_can_validate_email()
    {
        // Valid emails
        $this->assertTrue($this->utilityService->validateEmail('test@example.com'));
        $this->assertTrue($this->utilityService->validateEmail('user.name+tag@domain.co.uk'));
        
        // Invalid emails
        $this->assertFalse($this->utilityService->validateEmail('invalid-email'));
        $this->assertFalse($this->utilityService->validateEmail('test@'));
        $this->assertFalse($this->utilityService->validateEmail('@example.com'));
        $this->assertFalse($this->utilityService->validateEmail(''));
    }
    
    /** @test */
    public function it_can_validate_phone()
    {
        // Valid phones
        $this->assertTrue($this->utilityService->validatePhone('1234567890'));
        $this->assertTrue($this->utilityService->validatePhone('+1234567890'));
        $this->assertTrue($this->utilityService->validatePhone('(123) 456-7890'));
        $this->assertTrue($this->utilityService->validatePhone('123-456-7890'));
        
        // Invalid phones
        $this->assertFalse($this->utilityService->validatePhone('123'));
        $this->assertFalse($this->utilityService->validatePhone('123456789012345')); // Too long
        $this->assertFalse($this->utilityService->validatePhone(''));
    }
    
    /** @test */
    public function it_can_sanitize_input()
    {
        // Test string sanitization
        $string = $this->utilityService->sanitizeInput('<script>alert("xss")</script>Test', 'string');
        $this->assertStringNotContainsString('<script>', $string);
        $this->assertStringContainsString('Test', $string);
        
        // Test email sanitization
        $email = $this->utilityService->sanitizeInput('test@example.com', 'email');
        $this->assertEquals('test@example.com', $email);
        
        // Test integer sanitization
        $int = $this->utilityService->sanitizeInput('123abc456', 'int');
        $this->assertEquals('123456', $int);
        
        // Test float sanitization
        $float = $this->utilityService->sanitizeInput('123.45abc', 'float');
        $this->assertEquals('123.45', $float);
        
        // Test URL sanitization
        $url = $this->utilityService->sanitizeInput('https://example.com/path', 'url');
        $this->assertEquals('https://example.com/path', $url);
    }
    
    /** @test */
    public function it_can_get_client_ip()
    {
        $ip = $this->utilityService->getClientIp();
        
        $this->assertIsString($ip);
        $this->assertNotEmpty($ip);
        
        // Should be a valid IP format
        $this->assertTrue(
            filter_var($ip, FILTER_VALIDATE_IP) !== false ||
            $ip === '127.0.0.1'
        );
    }
    
    /** @test */
    public function it_can_get_user_agent()
    {
        $userAgent = $this->utilityService->getUserAgent();
        
        $this->assertIsString($userAgent);
        $this->assertNotEmpty($userAgent);
    }
    
    /** @test */
    public function it_can_check_ajax_request()
    {
        // This test might return false in CLI environment
        $isAjax = $this->utilityService->isAjaxRequest();
        
        $this->assertIsBool($isAjax);
    }
    
    /** @test */
    public function it_can_convert_bytes_to_human()
    {
        // Test bytes
        $bytes = $this->utilityService->bytesToHuman(512);
        $this->assertEquals('512 B', $bytes);
        
        // Test kilobytes
        $kb = $this->utilityService->bytesToHuman(1536);
        $this->assertEquals('1.5 KB', $kb);
        
        // Test megabytes
        $mb = $this->utilityService->bytesToHuman(1048576);
        $this->assertEquals('1 MB', $mb);
        
        // Test gigabytes
        $gb = $this->utilityService->bytesToHuman(1073741824);
        $this->assertEquals('1 GB', $gb);
        
        // Test precision
        $precise = $this->utilityService->bytesToHuman(1234567890, 3);
        $this->assertEquals('1.15 GB', $precise);
    }
    
    /** @test */
    public function it_can_create_pagination()
    {
        // Test basic pagination
        $pagination = $this->utilityService->createPagination(100, 10, 3);
        
        $this->assertEquals(100, $pagination['total_items']);
        $this->assertEquals(10, $pagination['items_per_page']);
        $this->assertEquals(10, $pagination['total_pages']);
        $this->assertEquals(3, $pagination['current_page']);
        $this->assertEquals(20, $pagination['offset']);
        $this->assertTrue($pagination['has_previous']);
        $this->assertTrue($pagination['has_next']);
        $this->assertEquals(2, $pagination['previous_page']);
        $this->assertEquals(4, $pagination['next_page']);
        
        // Test first page
        $first = $this->utilityService->createPagination(50, 10, 1);
        $this->assertFalse($first['has_previous']);
        $this->assertTrue($first['has_next']);
        
        // Test last page
        $last = $this->utilityService->createPagination(50, 10, 5);
        $this->assertTrue($last['has_previous']);
        $this->assertFalse($last['has_next']);
        
        // Test single page
        $single = $this->utilityService->createPagination(5, 10, 1);
        $this->assertEquals(1, $single['total_pages']);
        $this->assertFalse($single['has_previous']);
        $this->assertFalse($single['has_next']);
    }
    
    /** @test */
    public function it_can_generate_and_validate_csrf_token()
    {
        // Generate token
        $token = $this->utilityService->generateCsrfToken();
        
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
        
        // Validate correct token
        $isValid = $this->utilityService->validateCsrfToken($token);
        $this->assertTrue($isValid);
        
        // Validate incorrect token
        $isInvalid = $this->utilityService->validateCsrfToken('invalid_token');
        $this->assertFalse($isInvalid);
    }
    
    /** @test */
    public function it_can_create_response()
    {
        $response = $this->utilityService->createResponse(true, 'Success message', ['key' => 'value'], ['error' => 'none']);
        
        $this->assertTrue($response['success']);
        $this->assertEquals('Success message', $response['message']);
        $this->assertEquals(['key' => 'value'], $response['data']);
        $this->assertEquals(['error' => 'none'], $response['errors']);
        $this->assertArrayHasKey('timestamp', $response);
    }
    
    /** @test */
    public function it_can_get_base_url()
    {
        $baseUrl = $this->utilityService->getBaseUrl();
        
        $this->assertIsString($baseUrl);
        $this->assertNotEmpty($baseUrl);
        
        // Should start with http:// or https://
        $this->assertTrue(
            str_starts_with($baseUrl, 'http://') ||
            str_starts_with($baseUrl, 'https://')
        );
    }
    
    /** @test */
    public function it_handles_edge_cases()
    {
        // Test slug with special characters
        $specialChars = $this->utilityService->generateSlug('Test!@#$%^&*()String');
        $this->assertEquals('teststring', $specialChars);
        
        // Test currency with zero
        $zeroCurrency = $this->utilityService->formatCurrency(0);
        $this->assertEquals('₹0.00', $zeroCurrency);
        
        // Test currency with negative
        $negativeCurrency = $this->utilityService->formatCurrency(-123.45);
        $this->assertEquals('₹-123.45', $negativeCurrency);
        
        // Test random string with length 1
        $singleChar = $this->utilityService->generateRandomString(1);
        $this->assertEquals(1, strlen($singleChar));
        
        // Test truncate with very short limit
        $veryShort = $this->utilityService->truncateText('Long text', 1);
        $this->assertLessThanOrEqual(4, strlen($veryShort)); // 1 + '...'
    }
    
    /** @test */
    public function it_handles_invalid_gracefully()
    {
        // Test invalid date format
        $invalidDate = $this->utilityService->formatDate('invalid-date');
        $this->assertEquals('invalid-date', $invalidDate);
        
        // Test very large bytes
        $largeBytes = $this->utilityService->bytesToHuman(PHP_INT_MAX);
        $this->assertIsString($largeBytes);
        
        // Test pagination with zero items
        $zeroItems = $this->utilityService->createPagination(0, 10, 1);
        $this->assertEquals(0, $zeroItems['total_items']);
        $this->assertEquals(1, $zeroItems['total_pages']);
    }
}