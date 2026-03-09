<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CoreFunctionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CoreFunctionsServiceTest extends TestCase
{
    use RefreshDatabase;

    private CoreFunctionsService $coreFunctions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coreFunctions = app(CoreFunctionsService::class);
        Storage::fake('public');
    }

    /** @test */
    public function it_validates_email_correctly()
    {
        $validEmail = 'test@example.com';
        $invalidEmail = 'invalid-email';

        $result = $this->coreFunctions->validateInput($validEmail, 'email');
        $this->assertEquals($validEmail, $result);

        $result = $this->coreFunctions->validateInput($invalidEmail, 'email');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_validates_phone_numbers()
    {
        $phone10 = '9876543210';
        $phone12 = '919876543210';
        $invalidPhone = '123';

        $result = $this->coreFunctions->validateInput($phone10, 'phone');
        $this->assertEquals('919876543210', $result);

        $result = $this->coreFunctions->validateInput($phone12, 'phone');
        $this->assertEquals('919876543210', $result);

        $result = $this->coreFunctions->validateInput($invalidPhone, 'phone');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_validates_usernames()
    {
        $validUsername = 'testuser123';
        $invalidUsername = 'ab'; // Too short
        $invalidChars = 'test@user#'; // Invalid chars

        $result = $this->coreFunctions->validateInput($validUsername, 'username');
        $this->assertEquals($validUsername, $result);

        $result = $this->coreFunctions->validateInput($invalidUsername, 'username');
        $this->assertFalse($result);

        $result = $this->coreFunctions->validateInput($invalidChars, 'username');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_formats_phone_numbers()
    {
        $phone10 = '9876543210';
        $phone12 = '919876543210';

        $result = $this->coreFunctions->formatPhoneNumber($phone10);
        $this->assertEquals('+91 98765 43210', $result);

        $result = $this->coreFunctions->formatPhoneNumber($phone12);
        $this->assertEquals('+91 98765 43210', $result);
    }

    /** @test */
    public function it_generates_random_strings()
    {
        $length = 16;
        $randomString = $this->coreFunctions->generateRandomString($length);

        $this->assertEquals($length, strlen($randomString));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $randomString);
    }

    /** @test */
    public function it_generates_slugs()
    {
        $text = 'Test String Here!';
        $slug = $this->coreFunctions->generateSlug($text);

        $this->assertEquals('test-string-here', $slug);
    }

    /** @test */
    public function it_truncates_text()
    {
        $text = 'This is a very long text that should be truncated';
        $truncated = $this->coreFunctions->truncateText($text, 20, '...');

        $this->assertEquals('This is a very lo...', $truncated);
        $this->assertLessThanOrEqual(23, strlen($truncated)); // 20 + 3 for suffix
    }

    /** @test */
    public function it_formats_currency()
    {
        $amount = 1234.56;
        $formatted = $this->coreFunctions->formatCurrency($amount);

        $this->assertEquals('₹1,234.56', $formatted);
    }

    /** @test */
    public function it_formats_dates()
    {
        $date = '2023-12-25';
        $formatted = $this->coreFunctions->formatDate($date, 'd/m/Y');

        $this->assertEquals('25/12/2023', $formatted);
    }

    /** @test */
    public function it_sanitizes_filenames()
    {
        $filename = 'test@file#name$.txt';
        $sanitized = $this->coreFunctions->sanitizeFilename($filename);

        $this->assertEquals('testfilename.txt', $sanitized);
    }

    /** @test */
    public function it_gets_file_extensions()
    {
        $filename = 'test.jpg';
        $extension = $this->coreFunctions->getFileExtension($filename);

        $this->assertEquals('jpg', $extension);
    }

    /** @test */
    public function it_checks_image_files()
    {
        $imageFile = 'test.jpg';
        $textFile = 'test.txt';

        $this->assertTrue($this->coreFunctions->isImageFile($imageFile));
        $this->assertFalse($this->coreFunctions->isImageFile($textFile));
    }

    /** @test */
    public function it_gets_client_ip()
    {
        $ip = $this->coreFunctions->getClientIp();
        $this->assertNotEmpty($ip);
    }

    /** @test */
    public function it_hashes_and_verifies_passwords()
    {
        $password = 'testpassword123';
        $hash = $this->coreFunctions->hashPassword($password);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue($this->coreFunctions->verifyPasswordHash($password, $hash));
        $this->assertFalse($this->coreFunctions->verifyPasswordHash('wrongpassword', $hash));
    }

    /** @test */
    public function it_formats_file_sizes()
    {
        $bytes = 1048576; // 1 MB
        $formatted = $this->coreFunctions->formatFileSize($bytes);

        $this->assertEquals('1 MB', $formatted);
    }

    /** @test */
    public function it_generates_unique_filenames()
    {
        $originalName = 'test.jpg';
        $filename1 = $this->coreFunctions->generateUniqueFilename($originalName);
        $filename2 = $this->coreFunctions->generateUniqueFilename($originalName);

        $this->assertNotEquals($filename1, $filename2);
        $this->assertStringEndsWith('.jpg', $filename1);
        $this->assertStringEndsWith('.jpg', $filename2);
    }

    /** @test */
    public function core_functions_api_endpoints_work()
    {
        // Test validation endpoint
        $response = $this->postJson('/api/core-functions/validate', [
            'input' => 'test@example.com',
            'type' => 'email'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test phone formatting endpoint
        $response = $this->postJson('/api/core-functions/format-phone', [
            'phone' => '9876543210'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'formatted' => '+91 98765 43210'
            ]
        ]);

        // Test slug generation endpoint
        $response = $this->postJson('/api/core-functions/generate-slug', [
            'text' => 'Test String Here'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'slug' => 'test-string-here'
            ]
        ]);

        // Test currency formatting endpoint
        $response = $this->postJson('/api/core-functions/format-currency', [
            'amount' => 1234.56
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'formatted' => '₹1,234.56'
            ]
        ]);

        // Test random string generation endpoint
        $response = $this->postJson('/api/core-functions/generate-random', [
            'length' => 16
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.random_string']);

        // Test text truncation endpoint
        $response = $this->postJson('/api/core-functions/truncate-text', [
            'text' => 'This is a very long text that should be truncated',
            'length' => 20
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.truncated']);

        // Test client info endpoint
        $response = $this->getJson('/api/core-functions/client-info');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test CSRF token endpoint
        $response = $this->getJson('/api/core-functions/csrf-token');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.csrf_token']);

        // Test endpoint
        $response = $this->getJson('/api/core-functions/test');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.tests']);
    }

    /** @test */
    public function it_handles_invalid_validation_requests()
    {
        // Missing required fields
        $response = $this->postJson('/api/core-functions/validate', [
            'input' => 'test@example.com'
            // missing 'type'
        ]);
        $response->assertStatus(422);

        // Invalid type
        $response = $this->postJson('/api/core-functions/validate', [
            'input' => 'test@example.com',
            'type' => 'invalid_type'
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function it_handles_file_upload()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 800);

        $response = $this->postJson('/api/core-functions/upload-image', [
            'image' => $file,
            'max_width' => 500,
            'max_height' => 400
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'original_name',
                'resized_url',
                'thumbnail_url',
                'size'
            ]
        ]);
    }

    /** @test */
    public function it_handles_file_info_requests()
    {
        // Create a test file
        Storage::disk('local')->put('test.txt', 'Test content');

        $response = $this->getJson('/api/core-functions/file-info', [
            'filepath' => storage_path('app/test.txt')
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'exists',
                'readable',
                'size',
                'extension'
            ]
        ]);
    }

    /** @test */
    public function it_handles_text_extraction()
    {
        // Create a test file
        Storage::disk('local')->put('test.txt', 'Test content for extraction');

        $response = $this->postJson('/api/core-functions/extract-text', [
            'filepath' => storage_path('app/test.txt')
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'text' => 'Test content for extraction'
            ]
        ]);
    }

    /** @test */
    public function it_handles_multiple_input_validation()
    {
        $response = $this->postJson('/api/core-functions/validate-multiple', [
            'inputs' => [
                'email' => 'test@example.com',
                'name' => 'Test User'
            ],
            'rules' => [
                'email' => 'required|email',
                'name' => 'required|string|min:3'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'valid' => true
            ]
        ]);
    }

    /** @test */
    public function it_handles_date_formatting()
    {
        $response = $this->postJson('/api/core-functions/format-date', [
            'date' => '2023-12-25',
            'format' => 'd/m/Y'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'formatted' => '25/12/2023'
            ]
        ]);
    }
}
