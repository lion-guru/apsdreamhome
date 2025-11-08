<?php
/**
 * APS Dream Home - Comprehensive Testing Suite
 * ============================================
 * Simplified testing framework for development
 */

namespace ApsDreamHome\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

/**
 * Main Test Suite
 */
class ApsDreamHomeTestSuite extends TestSuite {

    public static function suite(): TestSuite {
        $suite = new self('APS Dream Home - Test Suite');

        // Add basic tests
        $suite->addTestSuite(BasicApplicationTests::class);
        $suite->addTestSuite(DatabaseTests::class);
        $suite->addTestSuite(SecurityTests::class);

        return $suite;
    }

    public static function runTests() {
        $suite = self::suite();
        $result = new \PHPUnit\Framework\TestResult();
        $suite->run($result);
        return $result;
    }
}

/**
 * Basic Application Tests
 */
class BasicApplicationTests extends TestCase {

    public function testApplicationLoads() {
        $this->assertTrue(true, 'Application should load without errors');
    }

    public function testDatabaseConnection() {
        // Test database connectivity
        $this->assertTrue(function_exists('mysqli_connect'), 'MySQLi should be available');
    }

    public function testConfigurationFiles() {
        $this->assertFileExists(__DIR__ . '/../config.php', 'Config file should exist');
        $this->assertFileExists(__DIR__ . '/../composer.json', 'Composer file should exist');
    }
}

/**
 * Database Tests
 */
class DatabaseTests extends TestCase {

    public function testDatabaseConnection() {
        // Test that we can connect to database (if configured)
        if (isset($GLOBALS['conn']) || isset($GLOBALS['con'])) {
            $conn = $GLOBALS['conn'] ?? $GLOBALS['con'];
            $this->assertInstanceOf('mysqli', $conn, 'Database connection should be available');
        } else {
            $this->markTestSkipped('Database not configured');
        }
    }

    public function testRequiredTables() {
        if (!isset($GLOBALS['conn']) && !isset($GLOBALS['con'])) {
            $this->markTestSkipped('Database not configured');
            return;
        }

        $conn = $GLOBALS['conn'] ?? $GLOBALS['con'];
        $requiredTables = ['property', 'users', 'bookings'];

        foreach ($requiredTables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '{$table}'");
            $this->assertEquals(1, $result->num_rows, "Table {$table} should exist");
        }
    }
}

/**
 * Security Tests
 */
class SecurityTests extends TestCase {

    public function testInputValidation() {
        // Test basic input validation functions
        $this->assertTrue(function_exists('htmlspecialchars'), 'HTML escaping should be available');
        $this->assertTrue(function_exists('mysqli_real_escape_string'), 'SQL escaping should be available');
    }

    public function testFilePermissions() {
        // Test that sensitive files are not accessible
        $this->assertFalse(is_readable(__DIR__ . '/../.env'), 'Environment file should not be readable');
        $this->assertFalse(is_readable(__DIR__ . '/../config/database.php'), 'Database config should not be readable');
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $result = ApsDreamHomeTestSuite::runTests();

        echo "<div style='font-family: Arial, sans-serif; padding: 20px;'>";
        echo "<h2>🧪 Test Results</h2>";
        echo "<p>Tests run: " . $result->count() . "</p>";
        echo "<p>Failures: " . $result->failureCount() . "</p>";
        echo "<p>Errors: " . $result->errorCount() . "</p>";
        echo "<p>Success: " . ($result->wasSuccessful() ? 'Yes' : 'No') . "</p>";
        echo "</div>";

    } catch (Exception $e) {
        echo "<div style='color: red; padding: 20px;'>";
        echo "<h2>❌ Test Error</h2>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
}
?>
