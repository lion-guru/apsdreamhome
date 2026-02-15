<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PDO;

class PropertySearchTest extends TestCase
{
    private PDO $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    
    public function test_can_search_properties_by_keyword()
    {
        // Create test properties
        $properties = [
            [
                'title' => 'Luxury Villa in Gorakhpur',
                'description' => 'Spacious villa with modern amenities',
                'type' => 'residential',
                'status' => 'available',
                'location' => 'Gorakhpur'
            ],
            [
                'title' => 'Commercial Space in Civil Lines',
                'description' => 'Prime commercial location',
                'type' => 'commercial',
                'status' => 'available',
                'location' => 'Civil Lines'
            ],
            [
                'title' => 'Agricultural Land in Rustampur',
                'description' => 'Fertile agricultural land',
                'type' => 'agricultural',
                'status' => 'available',
                'location' => 'Rustampur'
            ]
        ];
        
        $createdIds = [];
        
        foreach ($properties as $property) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $property['title'],
                $property['description'],
                10000000.00,
                $property['type'],
                $property['status'],
                $property['location'],
                1500,
                3,
                2,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test search by keyword in title
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE title LIKE ?");
        $stmt->execute(['%Luxury%']);
        $luxuryCount = $stmt->fetch()['count'];
        
        $this->assertEquals(1, $luxuryCount, 'Should find 1 property with "Luxury" in title');
        
        // Test search by keyword in description
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE description LIKE ?");
        $stmt->execute(['%modern%']);
        $modernCount = $stmt->fetch()['count'];
        
        $this->assertEquals(1, $modernCount, 'Should find 1 property with "modern" in description');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_properties_by_type()
    {
        // Create properties with different types
        $types = ['residential', 'commercial', 'agricultural'];
        $createdIds = [];
        
        foreach ($types as $type) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Test {$type} Property",
                "Test description for {$type}",
                10000000.00,
                $type,
                'available',
                'Test Location',
                1000,
                2,
                1,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by each type
        foreach ($types as $type) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE type = ?");
            $stmt->execute([$type]);
            $count = $stmt->fetch()['count'];
            
            $this->assertGreaterThan(0, $count, "Should find properties of type {$type}");
        }
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_properties_by_status()
    {
        // Create properties with different statuses
        $statuses = ['available', 'sold', 'booked'];
        $createdIds = [];
        
        foreach ($statuses as $status) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Test Property - {$status}",
                "Test description for {$status} property",
                10000000.00,
                'residential',
                $status,
                'Test Location',
                1000,
                2,
                1,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by each status
        foreach ($statuses as $status) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE status = ?");
            $stmt->execute([$status]);
            $count = $stmt->fetch()['count'];
            
            $this->assertGreaterThan(0, $count, "Should find properties with status {$status}");
        }
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_properties_by_price_range()
    {
        // Create properties with different prices
        $priceRanges = [
            ['min' => 5000000, 'max' => 10000000],
            ['min' => 10000000, 'max' => 20000000],
            ['min' => 20000000, 'max' => 50000000]
        ];
        $createdIds = [];
        
        foreach ($priceRanges as $range) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $price = ($range['min'] + $range['max']) / 2;
            $stmt->execute([
                "Test Property - Price " . $range['min'],
                "Test description for property priced at " . $price,
                $price,
                'residential',
                'available',
                'Test Location',
                1000,
                2,
                1,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by price range
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE price BETWEEN ? AND ?");
        $stmt->execute([5000000, 10000000]);
        $lowRangeCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $lowRangeCount, 'Should find properties in low price range');
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE price > ?");
        $stmt->execute([20000000]);
        $highRangeCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $highRangeCount, 'Should find properties in high price range');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_properties_by_location()
    {
        // Create properties in different locations
        $locations = ['Gorakhpur', 'Civil Lines', 'Rustampur', 'Mohaddipur'];
        $createdIds = [];
        
        foreach ($locations as $location) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Test Property in {$location}",
                "Test description for property in {$location}",
                10000000.00,
                'residential',
                'available',
                $location,
                1000,
                2,
                1,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by location
        foreach ($locations as $location) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE location = ?");
            $stmt->execute([$location]);
            $count = $stmt->fetch()['count'];
            
            $this->assertGreaterThan(0, $count, "Should find properties in {$location}");
        }
        
        // Test search by partial location
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE location LIKE ?");
        $stmt->execute(['%Gorakhpur%']);
        $gorakhpurCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $gorakhpurCount, 'Should find properties in Gorakhpur area');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_properties_by_bedrooms()
    {
        // Create properties with different bedroom counts
        $bedroomCounts = [1, 2, 3, 4];
        $createdIds = [];
        
        foreach ($bedroomCounts as $bedrooms) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Test Property - {$bedrooms} Bedrooms",
                "Test description for {$bedrooms} bedroom property",
                10000000.00,
                'residential',
                'available',
                'Test Location',
                1000 + ($bedrooms * 200),
                $bedrooms,
                $bedrooms,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by bedroom count
        foreach ($bedroomCounts as $bedrooms) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE bedrooms = ?");
            $stmt->execute([$bedrooms]);
            $count = $stmt->fetch()['count'];
            
            $this->assertGreaterThan(0, $count, "Should find properties with {$bedrooms} bedrooms");
        }
        
        // Test filtering by minimum bedrooms
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE bedrooms >= ?");
        $stmt->execute([3]);
        $threePlusCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $threePlusCount, 'Should find properties with 3+ bedrooms');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_properties_by_area()
    {
        // Create properties with different areas
        $areas = [500, 1000, 1500, 2000, 2500];
        $createdIds = [];
        
        foreach ($areas as $area) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Test Property - {$area} sqft",
                "Test description for {$area} sqft property",
                10000000.00,
                'residential',
                'available',
                'Test Location',
                $area,
                2,
                1,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by area range
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE area_sqft BETWEEN ? AND ?");
        $stmt->execute([1000, 2000]);
        $midRangeCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $midRangeCount, 'Should find properties in mid area range');
        
        // Test filtering by minimum area
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE area_sqft >= ?");
        $stmt->execute([1500]);
        $largeCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $largeCount, 'Should find large properties');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_sort_properties_by_price()
    {
        // Create properties with different prices
        $prices = [5000000, 10000000, 15000000, 20000000];
        $createdIds = [];
        
        foreach ($prices as $price) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Test Property - Price " . $price,
                "Test description for property priced at " . $price,
                $price,
                'residential',
                'available',
                'Test Location',
                1000,
                2,
                1,
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test sorting by price (low to high)
        $stmt = $this->pdo->prepare("SELECT price FROM properties ORDER BY price ASC");
        $stmt->execute();
        $sortedPrices = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->assertEquals(sort($prices), $sortedPrices, 'Properties should be sorted by price ascending');
        
        // Test sorting by price (high to low)
        $stmt = $this->pdo->prepare("SELECT price FROM properties ORDER BY price DESC");
        $stmt->execute();
        $sortedPricesDesc = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->assertEquals(array_reverse(sort($prices)), $sortedPricesDesc, 'Properties should be sorted by price descending');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_get_featured_properties()
    {
        // Create featured and non-featured properties
        $createdIds = [];
        
        for ($i = 0; $i < 5; $i++) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $featured = $i < 2 ? 1 : 0; // First 2 are featured
            $stmt->execute([
                "Test Property " . ($i + 1),
                "Test description for property " . ($i + 1),
                10000000.00,
                'residential',
                'available',
                'Test Location',
                1000,
                2,
                1,
                $featured,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test getting featured properties
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE featured = 1");
        $stmt->execute();
        $featuredCount = $stmt->fetch()['count'];
        
        $this->assertEquals(2, $featuredCount, 'Should find 2 featured properties');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }
}
