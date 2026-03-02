<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Core\App;
use App\Core\Database\Database;

abstract class TestCase extends BaseTestCase
{
    protected $app;
    protected $db;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize application
        $this->app = App::getInstance(BASE_PATH);
        
        // Initialize database
        $this->initializeDatabase();
        
        // Run migrations
        $this->runMigrations();
        
        // Seed database
        $this->seedDatabase();
    }
    
    protected function tearDown(): void
    {
        // Clean up database
        $this->cleanupDatabase();
        
        parent::tearDown();
    }
    
    /**
     * Initialize database connection
     */
    protected function initializeDatabase()
    {
        $config = [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ];
        
        $this->db = Database::getInstance($config);
    }
    
    /**
     * Run database migrations
     */
    protected function runMigrations()
    {
        // Create users table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'user',
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create properties table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS properties (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                price DECIMAL(10,2),
                location TEXT,
                property_type TEXT,
                status TEXT DEFAULT 'active',
                user_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Create payments table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                property_id INTEGER,
                amount DECIMAL(10,2),
                status TEXT DEFAULT 'pending',
                payment_method TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (property_id) REFERENCES properties(id)
            )
        ");
    }
    
    /**
     * Seed database with test data
     */
    protected function seedDatabase()
    {
        // Create test user
        $this->db->exec("
            INSERT INTO users (name, email, password, role, status) 
            VALUES ('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active')
        ");
        
        // Create test property
        $this->db->exec("
            INSERT INTO properties (title, description, price, location, property_type, status, user_id) 
            VALUES ('Test Property', 'A beautiful test property', 250000.00, 'Test Location', 'apartment', 'active', 1)
        ");
    }
    
    /**
     * Clean up database
     */
    protected function cleanupDatabase()
    {
        $this->db->exec("DELETE FROM payments");
        $this->db->exec("DELETE FROM properties");
        $this->db->exec("DELETE FROM users");
    }
    
    /**
     * Create test user
     */
    protected function createTestUser($data = [])
    {
        $defaultData = [
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'active'
        ];
        
        $data = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['name'], $data['email'], $data['password'], $data['role'], $data['status']]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Create test property
     */
    protected function createTestProperty($data = [])
    {
        $defaultData = [
            'title' => 'Test Property',
            'description' => 'A beautiful test property',
            'price' => 250000.00,
            'location' => 'Test Location',
            'property_type' => 'apartment',
            'status' => 'active',
            'user_id' => 1
        ];
        
        $data = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO properties (title, description, price, location, property_type, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['title'], $data['description'], $data['price'], $data['location'], $data['property_type'], $data['status'], $data['user_id']]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Create test payment
     */
    protected function createTestPayment($data = [])
    {
        $defaultData = [
            'user_id' => 1,
            'property_id' => 1,
            'amount' => 1000.00,
            'status' => 'pending',
            'payment_method' => 'credit_card'
        ];
        
        $data = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO payments (user_id, property_id, amount, status, payment_method) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['user_id'], $data['property_id'], $data['amount'], $data['status'], $data['payment_method']]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Assert database has record
     */
    protected function assertDatabaseHas($table, $data)
    {
        $conditions = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $conditions[] = "$column = ?";
            $values[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(' AND ', $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        
        $result = $stmt->fetch();
        $this->assertGreaterThan(0, $result['count'], "Record not found in table $table");
    }
    
    /**
     * Assert database missing record
     */
    protected function assertDatabaseMissing($table, $data)
    {
        $conditions = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $conditions[] = "$column = ?";
            $values[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(' AND ', $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        
        $result = $stmt->fetch();
        $this->assertEquals(0, $result['count'], "Record found in table $table");
    }
    
    /**
     * Assert response has JSON structure
     */
    protected function assertJsonStructure($response, $structure)
    {
        $data = json_decode($response, true);
        
        if ($data === null) {
            $this->fail('Response is not valid JSON');
        }
        
        $this->assertArrayHasKeyStructure($structure, $data);
    }
    
    /**
     * Assert array has key structure
     */
    protected function assertArrayHasKeyStructure($structure, $array)
    {
        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                $this->assertArrayHasKey($key, $array);
                $this->assertArrayHasKeyStructure($value, $array[$key]);
            } else {
                $this->assertArrayHasKey($value, $array);
            }
        }
    }
    
    /**
     * Mock service
     */
    protected function mockService($serviceName, $methods = [])
    {
        $mock = $this->createMock($serviceName);
        
        foreach ($methods as $method => $return) {
            $mock->method($method)->willReturn($return);
        }
        
        return $mock;
    }
    
    /**
     * Get private method
     */
    protected function getPrivateMethod($className, $methodName)
    {
        $reflection = new \ReflectionClass($className);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method;
    }
    
    /**
     * Call private method
     */
    protected function callPrivateMethod($object, $methodName, $args = [])
    {
        $method = $this->getPrivateMethod(get_class($object), $methodName);
        return $method->invokeArgs($object, $args);
    }
    
    /**
     * Get private property
     */
    protected function getPrivateProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        
        return $property->getValue($object);
    }
    
    /**
     * Set private property
     */
    protected function setPrivateProperty($object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        
        $property->setValue($object, $value);
    }
}
