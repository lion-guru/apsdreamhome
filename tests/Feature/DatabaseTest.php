<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class DatabaseTest extends TestCase
{
    /** @var PDO */
    private $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create PDO connection for each test
        try {
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
        } catch (PDOException $e) {
            $this->markTestSkipped("Database connection failed: " . $e->getMessage());
        }
    }

    public function test_database_connection()
    {
        $this->assertNotNull($this->pdo, 'Database connection should be established');
        $this->assertInstanceOf(PDO::class, $this->pdo, 'Should be a valid PDO instance');
    }

    public function test_can_execute_queries()
    {
        $result = $this->pdo->query('SELECT 1 as test_value')->fetch();
        $this->assertEquals(1, $result['test_value'], 'Should be able to execute a simple query');
    }

    /**
     * @dataProvider requiredTablesProvider
     */
    public function test_required_tables_exist($tableName)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_schema = :database 
            AND table_name = :table
        ");
        
        $stmt->execute([
            'database' => DB_NAME,
            'table' => $tableName
        ]);
        
        $result = $stmt->fetch();
        $this->assertEquals(1, $result['count'], "Table '{$tableName}' should exist");
    }

    public function requiredTablesProvider()
    {
        return [
            ['users'],
            ['properties'],
            ['projects'],
            ['inquiries'],
            ['bookings']
        ];
    }

    public function test_properties_table_has_required_columns()
    {
        $stmt = $this->pdo->prepare("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = :database 
            AND TABLE_NAME = 'properties'
            AND COLUMN_NAME IN ('id', 'title', 'price', 'status')
        ");
        
        $stmt->execute(['database' => DB_NAME]);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->assertContains('id', $columns, "Properties table should have 'id' column");
        $this->assertContains('title', $columns, "Properties table should have 'title' column");
        $this->assertContains('price', $columns, "Properties table should have 'price' column");
        $this->assertContains('status', $columns, "Properties table should have 'status' column");
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }
}
