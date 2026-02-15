<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PDO;

class PropertyTest extends TestCase
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
    
    public function test_can_create_property()
    {
        // Create a test property
        $stmt = $this->pdo->prepare("
            INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $title = 'Test Property Unit';
        $description = 'Test property description';
        $price = 15000000.00;
        $type = 'residential';
        $status = 'available';
        $location = 'Test Location';
        $area_sqft = 1500;
        $bedrooms = 3;
        $bathrooms = 2;
        $featured = 0;
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        
        $stmt->execute([$title, $description, $price, $type, $status, $location, $area_sqft, $bedrooms, $bathrooms, $featured, $created_at, $updated_at]);
        
        $propertyId = $this->pdo->lastInsertId();
        $this->assertTrue($propertyId > 0, 'Property should be created with valid ID');
        
        // Verify property exists
        $stmt = $this->pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        $property = $stmt->fetch();
        
        $this->assertEquals($title, $property['title'], 'Property title should match');
        $this->assertEquals($price, $property['price'], 'Property price should match');
        $this->assertEquals($type, $property['type'], 'Property type should match');
        $this->assertEquals($status, $property['status'], 'Property status should match');
        
        // Clean up
        $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
    }
    
    public function test_can_update_property()
    {
        // Create a property first
        $stmt = $this->pdo->prepare("
            INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Test Property for Update',
            'Original description',
            10000000.00,
            'residential',
            'available',
            'Test Location',
            1200,
            2,
            1,
            0,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        
        $propertyId = $this->pdo->lastInsertId();
        
        // Update the property
        $newTitle = 'Updated Property Title';
        $newPrice = 12000000.00;
        $newStatus = 'sold';
        
        $stmt = $this->pdo->prepare("
            UPDATE properties 
            SET title = ?, price = ?, status = ?, updated_at = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$newTitle, $newPrice, $newStatus, date('Y-m-d H:i:s'), $propertyId]);
        
        // Verify update
        $stmt = $this->pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        $property = $stmt->fetch();
        
        $this->assertEquals($newTitle, $property['title'], 'Property title should be updated');
        $this->assertEquals($newPrice, $property['price'], 'Property price should be updated');
        $this->assertEquals($newStatus, $property['status'], 'Property status should be updated');
        
        // Clean up
        $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
    }
    
    public function test_can_delete_property()
    {
        // Create a property first
        $stmt = $this->pdo->prepare("
            INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Test Property for Deletion',
            'Test description',
            8000000.00,
            'commercial',
            'available',
            'Test Location',
            800,
            1,
            1,
            0,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        
        $propertyId = $this->pdo->lastInsertId();
        
        // Verify property exists
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        $countBefore = $stmt->fetch()['count'];
        $this->assertEquals(1, $countBefore, 'Property should exist before deletion');
        
        // Delete the property
        $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        
        // Verify property is deleted
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        $countAfter = $stmt->fetch()['count'];
        $this->assertEquals(0, $countAfter, 'Property should be deleted');
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
            
            $title = "Test Property - {$status}";
            $stmt->execute([
                $title,
                'Test description',
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
        
        // Test filtering by status
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE status = ?");
        $stmt->execute(['available']);
        $availableCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $availableCount, 'Should find available properties');
        
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
            
            $title = "Test Property - {$type}";
            $stmt->execute([
                $title,
                'Test description',
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
        
        // Test filtering by type
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE type = ?");
        $stmt->execute(['residential']);
        $residentialCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $residentialCount, 'Should find residential properties');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_search_properties_by_location()
    {
        // Create properties with different locations
        $locations = ['Gorakhpur', 'Civil Lines', 'Test Location'];
        $createdIds = [];
        
        foreach ($locations as $location) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, area_sqft, bedrooms, bathrooms, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $title = "Test Property - {$location}";
            $stmt->execute([
                $title,
                'Test description',
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
        
        // Test searching by location
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE location LIKE ?");
        $stmt->execute(['%Gorakhpur%']);
        $gorakhpurCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $gorakhpurCount, 'Should find properties in Gorakhpur');
        
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
