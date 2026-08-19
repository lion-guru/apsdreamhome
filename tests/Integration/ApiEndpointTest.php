<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class ApiEndpointTest extends TestCase
{
    private static $baseUrl = 'http://localhost/apsdreamhome';
    private static $token = null;

    public static function setUpBeforeClass(): void
    {
        $response = self::makeRequest('POST', '/api/v2/mobile/auth/login', [
            'email' => 'admin@apsdreamhome.com',
            'password' => 'admin123'
        ]);
        
        if ($response['status'] === 200) {
            $data = json_decode($response['body'], true);
            self::$token = $data['data']['token'] ?? null;
        }
    }

    private static function makeRequest(string $method, string $path, array $data = null): array
    {
        $url = self::$baseUrl . $path;
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                self::$token ? 'Authorization: Bearer ' . self::$token : '',
            ],
        ]);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['status' => $status, 'body' => $body];
    }

    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . self::$token,
            'Content-Type' => 'application/json',
        ];
    }

    public function test_login_returns_token(): void
    {
        $this->assertNotNull(self::$token, 'Login should return a token');
    }

    public function test_properties_endpoint_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/properties');
        $this->assertEquals(200, $response['status']);
        
        $data = json_decode($response['body'], true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }

    public function test_featured_properties_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/properties/featured');
        $this->assertEquals(200, $response['status']);
        
        $data = json_decode($response['body'], true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }

    public function test_properties_search_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/properties/search?q=noida');
        $this->assertEquals(200, $response['status']);
        
        $data = json_decode($response['body'], true);
        $this->assertTrue($data['success']);
    }

    public function test_bookings_endpoint_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/bookings');
        $this->assertEquals(200, $response['status']);
        
        $data = json_decode($response['body'], true);
        $this->assertTrue($data['success']);
    }

    public function test_mlm_summary_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/mlm/summary');
        $this->assertEquals(200, $response['status']);
        
        $data = json_decode($response['body'], true);
        $this->assertTrue($data['success']);
    }

    public function test_mlm_payouts_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/mlm/payouts');
        $this->assertEquals(200, $response['status']);
    }

    public function test_mlm_incentives_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/mlm/incentives');
        $this->assertEquals(200, $response['status']);
    }

    public function test_mlm_genealogy_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/mlm/genealogy');
        $this->assertEquals(200, $response['status']);
    }

    public function test_mlm_business_breakdown_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/mlm/business-breakdown');
        $this->assertEquals(200, $response['status']);
    }

    public function test_user_profile_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/user/profile');
        $this->assertEquals(200, $response['status']);
        
        $data = json_decode($response['body'], true);
        $this->assertTrue($data['success']);
    }

    public function test_user_favorites_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/user/favorites');
        $this->assertEquals(200, $response['status']);
    }

    public function test_user_documents_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/user/documents');
        $this->assertEquals(200, $response['status']);
    }

    public function test_user_notifications_returns_200(): void
    {
        $response = self::makeRequest('GET', '/api/v2/mobile/user/notifications');
        $this->assertEquals(200, $response['status']);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $url = self::$baseUrl . '/api/v2/mobile/user/profile';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertContains($status, [401, 403]);
    }
}
