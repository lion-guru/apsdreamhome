<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\FarmerService;
use App\Http\Controllers\FarmerController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FarmerServiceTest extends TestCase
{
    use RefreshDatabase;

    private FarmerService $farmerService;
    private FarmerController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmerService = app(FarmerService::class);
        $this->controller = new FarmerController($this->farmerService);
        
        // Create necessary tables for testing
        $this->createTestTables();
    }

    private function createTestTables(): void
    {
        // Create farmer_profiles table
        DB::statement("
            CREATE TABLE IF NOT EXISTS farmer_profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_number VARCHAR(50) NOT NULL UNIQUE,
                full_name VARCHAR(100) NOT NULL,
                father_name VARCHAR(100),
                phone VARCHAR(15) NOT NULL,
                village VARCHAR(100),
                district VARCHAR(100),
                state VARCHAR(100),
                total_land_holding DECIMAL(10,2) DEFAULT 0,
                status ENUM('active','inactive','blacklisted','under_review') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create farmer_land_holdings table
        DB::statement("
            CREATE TABLE IF NOT EXISTS farmer_land_holdings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                land_area DECIMAL(10,2) NOT NULL,
                village VARCHAR(100),
                district VARCHAR(100),
                state VARCHAR(100),
                acquisition_status ENUM('not_acquired','under_negotiation','acquired','rejected') DEFAULT 'not_acquired',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create farmer_transactions table
        DB::statement("
            CREATE TABLE IF NOT EXISTS farmer_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                transaction_type ENUM('land_acquisition','payment','loan','commission','refund','penalty') NOT NULL,
                transaction_number VARCHAR(50) NOT NULL UNIQUE,
                amount DECIMAL(15,2) NOT NULL,
                transaction_date DATE NOT NULL,
                status ENUM('pending','completed','failed','cancelled') DEFAULT 'completed',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create farmer_support_requests table
        DB::statement("
            CREATE TABLE IF NOT EXISTS farmer_support_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                request_number VARCHAR(50) NOT NULL UNIQUE,
                request_type ENUM('technical','financial','legal','infrastructure','other') NOT NULL,
                subject VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                status ENUM('open','in_progress','resolved','closed','rejected') DEFAULT 'open',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /** @test */
    public function it_can_create_farmer()
    {
        $farmerData = [
            'full_name' => 'Test Farmer',
            'father_name' => 'Father Name',
            'phone' => '9876543210',
            'village' => 'Test Village',
            'district' => 'Test District',
            'state' => 'Haryana',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'ifsc_code' => 'TEST0001234',
            'total_land_holding' => 10.5,
            'created_by' => 1
        ];

        $farmerId = $this->farmerService->createFarmer($farmerData);

        $this->assertIsInt($farmerId);
        $this->assertGreaterThan(0, $farmerId);

        $farmer = $this->farmerService->getFarmer($farmerId);
        $this->assertEquals('Test Farmer', $farmer['full_name']);
        $this->assertEquals('9876543210', $farmer['phone']);
    }

    /** @test */
    public function it_can_get_all_farmers()
    {
        // Create test farmers
        $this->createTestFarmer('Farmer 1', '9876543210');
        $this->createTestFarmer('Farmer 2', '9876543211');

        $farmers = $this->farmerService->getAllFarmers();

        $this->assertIsArray($farmers);
        $this->assertArrayHasKey('data', $farmers);
        $this->assertGreaterThanOrEqual(2, count($farmers['data']));
    }

    /** @test */
    public function it_can_get_farmer_by_id()
    {
        $farmerId = $this->createTestFarmer('Test Farmer', '9876543210');

        $farmer = $this->farmerService->getFarmer($farmerId);

        $this->assertIsArray($farmer);
        $this->assertEquals('Test Farmer', $farmer['full_name']);
        $this->assertEquals('9876543210', $farmer['phone']);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_farmer()
    {
        $farmer = $this->farmerService->getFarmer(99999);

        $this->assertNull($farmer);
    }

    /** @test */
    public function it_can_update_farmer()
    {
        $farmerId = $this->createTestFarmer('Original Name', '9876543210');

        $updateData = [
            'full_name' => 'Updated Name',
            'village' => 'Updated Village'
        ];

        $result = $this->farmerService->updateFarmer($farmerId, $updateData);

        $this->assertTrue($result);

        $farmer = $this->farmerService->getFarmer($farmerId);
        $this->assertEquals('Updated Name', $farmer['full_name']);
        $this->assertEquals('Updated Village', $farmer['village']);
    }

    /** @test */
    public function it_can_add_land_holding()
    {
        $farmerId = $this->createTestFarmer('Test Farmer', '9876543210');

        $landData = [
            'land_area' => 5.5,
            'village' => 'Land Village',
            'district' => 'Land District',
            'state' => 'Haryana',
            'acquisition_status' => 'not_acquired'
        ];

        $holdingId = $this->farmerService->addLandHolding($farmerId, $landData);

        $this->assertIsInt($holdingId);
        $this->assertGreaterThan(0, $holdingId);

        $holdings = $this->farmerService->getFarmerLandHoldings($farmerId);
        $this->assertCount(1, $holdings);
        $this->assertEquals(5.5, $holdings[0]['land_area']);
    }

    /** @test */
    public function it_can_update_acquisition_status()
    {
        $farmerId = $this->createTestFarmer('Test Farmer', '9876543210');
        $holdingId = $this->farmerService->addLandHolding($farmerId, [
            'land_area' => 5.5,
            'village' => 'Land Village',
            'district' => 'Land District',
            'state' => 'Haryana',
            'acquisition_status' => 'not_acquired'
        ]);

        $result = $this->farmerService->updateAcquisitionStatus($holdingId, 'acquired', 100000);

        $this->assertTrue($result);

        $holdings = $this->farmerService->getFarmerLandHoldings($farmerId);
        $this->assertEquals('acquired', $holdings[0]['acquisition_status']);
        $this->assertEquals(100000, $holdings[0]['acquisition_amount']);
    }

    /** @test */
    public function it_can_add_transaction()
    {
        $farmerId = $this->createTestFarmer('Test Farmer', '9876543210');

        $transactionData = [
            'transaction_type' => 'payment',
            'amount' => 50000,
            'transaction_date' => '2023-12-25',
            'description' => 'Test payment',
            'created_by' => 1
        ];

        $transactionId = $this->farmerService->addTransaction($farmerId, $transactionData);

        $this->assertIsInt($transactionId);
        $this->assertGreaterThan(0, $transactionId);

        $transactions = $this->farmerService->getFarmerTransactions($farmerId);
        $this->assertCount(1, $transactions);
        $this->assertEquals(50000, $transactions[0]['amount']);
    }

    /** @test */
    public function it_can_create_support_request()
    {
        $farmerId = $this->createTestFarmer('Test Farmer', '9876543210');

        $supportData = [
            'request_type' => 'technical',
            'subject' => 'Test Support Request',
            'description' => 'This is a test support request description',
            'created_by' => 1
        ];

        $requestId = $this->farmerService->createSupportRequest($farmerId, $supportData);

        $this->assertIsInt($requestId);
        $this->assertGreaterThan(0, $requestId);

        $requests = $this->farmerService->getFarmerSupportRequests($farmerId);
        $this->assertCount(1, $requests);
        $this->assertEquals('Test Support Request', $requests[0]['subject']);
    }

    /** @test */
    public function it_can_get_farmer_dashboard()
    {
        $farmerId = $this->createTestFarmer('Test Farmer', '9876543210');

        // Add some test data
        $this->farmerService->addLandHolding($farmerId, [
            'land_area' => 5.5,
            'village' => 'Land Village',
            'district' => 'Land District',
            'state' => 'Haryana',
            'acquisition_status' => 'acquired'
        ]);

        $this->farmerService->addTransaction($farmerId, [
            'transaction_type' => 'payment',
            'amount' => 50000,
            'transaction_date' => '2023-12-25',
            'created_by' => 1
        ]);

        $dashboard = $this->farmerService->getFarmerDashboard($farmerId);

        $this->assertIsArray($dashboard);
        $this->assertArrayHasKey('farmer_info', $dashboard);
        $this->assertArrayHasKey('land_summary', $dashboard);
        $this->assertArrayHasKey('transaction_summary', $dashboard);
        $this->assertEquals('Test Farmer', $dashboard['farmer_info']['full_name']);
    }

    /** @test */
    public function it_can_get_farmer_stats()
    {
        // Create test farmers
        $this->createTestFarmer('Farmer 1', '9876543210');
        $this->createTestFarmer('Farmer 2', '9876543211');

        $stats = $this->farmerService->getFarmerStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_farmers', $stats);
        $this->assertArrayHasKey('active_farmers', $stats);
        $this->assertArrayHasKey('total_land_area', $stats);
        $this->assertGreaterThanOrEqual(2, $stats['total_farmers']);
    }

    /** @test */
    public function it_can_search_farmers()
    {
        $this->createTestFarmer('Search Test Farmer', '9876543210');
        $this->createTestFarmer('Another Farmer', '9876543211');

        $results = $this->farmerService->searchFarmers('Search');

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, $results);
        $this->assertEquals('Search Test Farmer', $results[0]['full_name']);
    }

    /** @test */
    public function farmer_api_endpoints_work()
    {
        // Test index endpoint
        $response = $this->getJson('/api/farmers');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test stats endpoint
        $response = $this->getJson('/api/farmers/stats');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test summary endpoint
        $response = $this->getJson('/api/farmers/summary');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test search endpoint
        $response = $this->getJson('/api/farmers/search?query=Test');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function it_can_create_farmer_via_api()
    {
        $farmerData = [
            'full_name' => 'API Test Farmer',
            'father_name' => 'API Father',
            'phone' => '9999999999',
            'village' => 'API Village',
            'district' => 'API District',
            'state' => 'Haryana',
            'bank_account_number' => '9999999999',
            'bank_name' => 'API Bank',
            'ifsc_code' => 'API0001234',
            'created_by' => 1
        ];

        $response = $this->postJson('/api/farmers', $farmerData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Farmer created successfully'
        ]);
        $response->assertJsonStructure(['success', 'message', 'data.farmer_id']);
    }

    /** @test */
    public function it_validates_required_farmer_fields()
    {
        $response = $this->postJson('/api/farmers', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'full_name', 'phone', 'village', 'district', 'state', 
            'bank_account_number', 'bank_name', 'ifsc_code', 'created_by'
        ]);
    }

    /** @test */
    public function it_can_get_farmer_via_api()
    {
        $farmerId = $this->createTestFarmer('API Get Test', '8888888888');

        $response = $this->getJson("/api/farmers/{$farmerId}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'data']);
        $this->assertEquals('API Get Test', $response->json('data.full_name'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_farmer_via_api()
    {
        $response = $this->getJson('/api/farmers/99999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Farmer not found'
        ]);
    }

    /** @test */
    public function it_can_update_farmer_via_api()
    {
        $farmerId = $this->createTestFarmer('Update Test', '7777777777');

        $updateData = [
            'full_name' => 'Updated Name',
            'village' => 'Updated Village'
        ];

        $response = $this->putJson("/api/farmers/{$farmerId}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Farmer updated successfully'
        ]);
    }

    /** @test */
    public function it_can_add_land_holding_via_api()
    {
        $farmerId = $this->createTestFarmer('Land Test', '6666666666');

        $landData = [
            'land_area' => 3.5,
            'village' => 'Land Test Village',
            'district' => 'Land Test District',
            'state' => 'Haryana'
        ];

        $response = $this->postJson("/api/farmers/{$farmerId}/land-holdings", $landData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Land holding added successfully'
        ]);
        $response->assertJsonStructure(['success', 'message', 'data.holding_id']);
    }

    /** @test */
    public function it_can_get_farmer_dashboard_via_api()
    {
        $farmerId = $this->createTestFarmer('Dashboard Test', '5555555555');

        $response = $this->getJson("/api/farmers/{$farmerId}/dashboard");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'farmer_info',
                'land_summary',
                'transaction_summary',
                'recent_transactions',
                'active_loans',
                'support_summary'
            ]
        ]);
    }

    private function createTestFarmer(string $name, string $phone): int
    {
        return $this->farmerService->createFarmer([
            'full_name' => $name,
            'phone' => $phone,
            'village' => 'Test Village',
            'district' => 'Test District',
            'state' => 'Haryana',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'ifsc_code' => 'TEST0001234',
            'created_by' => 1
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        DB::statement('DROP TABLE IF EXISTS farmer_support_requests');
        DB::statement('DROP TABLE IF EXISTS farmer_transactions');
        DB::statement('DROP TABLE IF EXISTS farmer_land_holdings');
        DB::statement('DROP TABLE IF EXISTS farmer_profiles');
        
        Cache::flush();
        parent::tearDown();
    }
}
