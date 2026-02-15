<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PDO;

class ProjectTest extends TestCase
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
    
    public function test_can_create_project()
    {
        // Create a test project
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (title, description, developer, location, total_units, price_range_min, price_range_max, status, launch_date, completion_date, featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $title = 'Test Project Unit';
        $description = 'Test project description';
        $developer = 'Test Developer';
        $location = 'Test Location';
        $total_units = 100;
        $price_range_min = 2000000.00;
        $price_range_max = 8000000.00;
        $status = 'planning';
        $launch_date = '2024-01-01';
        $completion_date = '2025-12-31';
        $featured = 0;
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        
        $stmt->execute([$title, $description, $developer, $location, $total_units, $price_range_min, $price_range_max, $status, $launch_date, $completion_date, $featured, $created_at, $updated_at]);
        
        $projectId = $this->pdo->lastInsertId();
        $this->assertTrue($projectId > 0, 'Project should be created with valid ID');
        
        // Verify project exists
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        $this->assertEquals($title, $project['title'], 'Project title should match');
        $this->assertEquals($developer, $project['developer'], 'Project developer should match');
        $this->assertEquals($location, $project['location'], 'Project location should match');
        $this->assertEquals($status, $project['status'], 'Project status should match');
        
        // Clean up
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
    }
    
    public function test_can_update_project()
    {
        // Create a project first
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (title, description, developer, location, total_units, price_range_min, price_range_max, status, launch_date, completion_date, featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Test Project for Update',
            'Original description',
            'Original Developer',
            'Original Location',
            50,
            1500000.00,
            5000000.00,
            'planning',
            '2024-01-01',
            '2025-12-31',
            0,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        
        $projectId = $this->pdo->lastInsertId();
        
        // Update the project
        $newTitle = 'Updated Project Title';
        $newStatus = 'under_construction';
        $newTotalUnits = 150;
        
        $stmt = $this->pdo->prepare("
            UPDATE projects 
            SET title = ?, status = ?, total_units = ?, updated_at = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$newTitle, $newStatus, $newTotalUnits, date('Y-m-d H:i:s'), $projectId]);
        
        // Verify update
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        $this->assertEquals($newTitle, $project['title'], 'Project title should be updated');
        $this->assertEquals($newStatus, $project['status'], 'Project status should be updated');
        $this->assertEquals($newTotalUnits, $project['total_units'], 'Project total units should be updated');
        
        // Clean up
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
    }
    
    public function test_can_delete_project()
    {
        // Create a project first
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (title, description, developer, location, total_units, price_range_min, price_range_max, status, launch_date, completion_date, featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Test Project for Deletion',
            'Test description',
            'Test Developer',
            'Test Location',
            75,
            2000000.00,
            6000000.00,
            'planning',
            '2024-01-01',
            '2025-12-31',
            0,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        
        $projectId = $this->pdo->lastInsertId();
        
        // Verify project exists
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $countBefore = $stmt->fetch()['count'];
        $this->assertEquals(1, $countBefore, 'Project should exist before deletion');
        
        // Delete the project
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        
        // Verify project is deleted
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $countAfter = $stmt->fetch()['count'];
        $this->assertEquals(0, $countAfter, 'Project should be deleted');
    }
    
    public function test_can_filter_projects_by_status()
    {
        // Create projects with different statuses
        $statuses = ['planning', 'under_construction', 'completed'];
        $createdIds = [];
        
        foreach ($statuses as $status) {
            $stmt = $this->pdo->prepare("
                INSERT INTO projects (title, description, developer, location, total_units, price_range_min, price_range_max, status, launch_date, completion_date, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $title = "Test Project - {$status}";
            $stmt->execute([
                $title,
                'Test description',
                'Test Developer',
                'Test Location',
                100,
                2000000.00,
                8000000.00,
                $status,
                '2024-01-01',
                '2025-12-31',
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by status
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE status = ?");
        $stmt->execute(['completed']);
        $completedCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $completedCount, 'Should find completed projects');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_projects_by_developer()
    {
        // Create projects with different developers
        $developers = ['APS Dream Homes Pvt Ltd', 'Green Valley Developers', 'Urban Infrastructure Ltd'];
        $createdIds = [];
        
        foreach ($developers as $developer) {
            $stmt = $this->pdo->prepare("
                INSERT INTO projects (title, description, developer, location, total_units, price_range_min, price_range_max, status, launch_date, completion_date, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $title = "Test Project - {$developer}";
            $stmt->execute([
                $title,
                'Test description',
                $developer,
                'Test Location',
                100,
                2000000.00,
                8000000.00,
                'planning',
                '2024-01-01',
                '2025-12-31',
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by developer
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE developer = ?");
        $stmt->execute(['APS Dream Homes Pvt Ltd']);
        $apsCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $apsCount, 'Should find projects by APS Dream Homes');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_search_projects_by_location()
    {
        // Create projects with different locations
        $locations = ['Gorakhpur', 'Civil Lines', 'Test Location'];
        $createdIds = [];
        
        foreach ($locations as $location) {
            $stmt = $this->pdo->prepare("
                INSERT INTO projects (title, description, developer, location, total_units, price_range_min, price_range_max, status, launch_date, completion_date, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $title = "Test Project - {$location}";
            $stmt->execute([
                $title,
                'Test description',
                'Test Developer',
                $location,
                100,
                2000000.00,
                8000000.00,
                'planning',
                '2024-01-01',
                '2025-12-31',
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test searching by location
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE location LIKE ?");
        $stmt->execute(['%Gorakhpur%']);
        $gorakhpurCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $gorakhpurCount, 'Should find projects in Gorakhpur');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_can_filter_projects_by_price_range()
    {
        // Create projects with different price ranges
        $priceRanges = [
            ['min' => 1000000, 'max' => 5000000],
            ['min' => 5000000, 'max' => 10000000],
            ['min' => 10000000, 'max' => 20000000]
        ];
        $createdIds = [];
        
        foreach ($priceRanges as $range) {
            $stmt = $this->pdo->prepare("
                INSERT INTO projects (title, description, developer, location, total_units, price_range_min, price_range_max, status, launch_date, completion_date, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $title = "Test Project - Price " . $range['min'];
            $stmt->execute([
                $title,
                'Test description',
                'Test Developer',
                'Test Location',
                100,
                $range['min'],
                $range['max'],
                'planning',
                '2024-01-01',
                '2025-12-31',
                0,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test filtering by price range
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE price_range_min >= ? AND price_range_max <= ?");
        $stmt->execute([5000000, 10000000]);
        $midRangeCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $midRangeCount, 'Should find projects in mid price range');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }
}
