<?php
/**
 * APS Dream Home - Manual Database Import Script
 * Run this after starting MySQL in XAMPP Control Panel
 */

echo "<h1>🗄️ APS Dream Home - Manual Database Import</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Instructions
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h3>⚠️ IMPORTANT: Start MySQL First</h3>";
echo "<p>Please follow these steps:</p>";
echo "<ol>";
echo "<li>Open XAMPP Control Panel</li>";
echo "<li>Click 'Start' button next to MySQL</li>";
echo "<li>Wait for MySQL to turn green</li>";
echo "<li>Then click the button below to import database</li>";
echo "</ol>";
echo "<p><strong>Note:</strong> Keep XAMPP Control Panel open and MySQL running.</p>";
echo "</div>";

// Check MySQL status
echo "<h2>🔍 MySQL Status Check</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $conn = new mysqli('localhost', 'root', '');
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ MySQL not running. Please start MySQL in XAMPP Control Panel.</p>";
        echo "<p style='color: orange;'>Error: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ MySQL is running!</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ MySQL Connection Failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Import button and process
echo "<h2>📥 Database Import</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

if (isset($_POST['import_database'])) {
    try {
        // Check MySQL connection
        $conn = new mysqli('localhost', 'root', '');
        if ($conn->connect_error) {
            throw new Exception("MySQL not running: " . $conn->connect_error);
        }

        echo "<p style='color: green;'>✅ Connected to MySQL</p>";

        // Create database
        $databaseName = 'apsdreamhomefinal';
        $conn->query("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db($databaseName);

        echo "<p style='color: green;'>✅ Database '{$databaseName}' ready</p>";

        // Import main database file
        $mainDbFile = 'database/apsdreamhomes.sql';
        if (file_exists($mainDbFile)) {
            echo "<p style='color: blue;'>📥 Reading database file...</p>";

            $sqlContent = file_get_contents($mainDbFile);
            if ($sqlContent) {
                echo "<p style='color: blue;'>📥 File size: " . round(strlen($sqlContent) / 1024 / 1024, 2) . " MB</p>";

                // Split into statements (simplified approach)
                $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

                $importedCount = 0;
                $totalStatements = count($statements);

                echo "<p style='color: blue;'>📥 Importing {$totalStatements} statements...</p>";

                foreach ($statements as $i => $statement) {
                    if (!empty($statement) && strlen($statement) > 10) {
                        // Skip CREATE DATABASE and USE statements
                        if (stripos($statement, 'CREATE DATABASE') === false &&
                            stripos($statement, 'USE ') === false) {

                            if ($conn->query($statement)) {
                                $importedCount++;
                            } else {
                                echo "<p style='color: orange;'>⚠️ Statement {$i} failed: " . substr($statement, 0, 100) . "...</p>";
                                echo "<p style='color: orange;'>Error: " . $conn->error . "</p>";
                            }
                        }
                    }
                }

                echo "<p style='color: green;'>✅ Successfully imported {$importedCount} statements</p>";

                // Verify import
                $result = $conn->query("SHOW TABLES");
                $tableCount = $result ? $result->num_rows : 0;
                echo "<p style='color: green;'>✅ Database now has {$tableCount} tables</p>";

                // Test sample data
                $result = $conn->query("SELECT COUNT(*) as count FROM users");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<p style='color: green;'>✅ Users in database: {$row['count']}</p>";
                }

            } else {
                echo "<p style='color: red;'>❌ Could not read database file</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Database file not found: {$mainDbFile}</p>";
        }

        $conn->close();
        echo "<h3 style='color: green;'>🎉 Database Import Complete!</h3>";

    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Import Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: blue;'>Click the button below to import the database after starting MySQL.</p>";
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<button type='submit' name='import_database' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
    echo "📥 Import APS Dream Home Database";
    echo "</button>";
    echo "</form>";
}
echo "</div>";

// System Status
echo "<h2>🎯 System Status</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhomefinal');

    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database Connection: Failed</p>";
        echo "<p style='color: orange;'>💡 Start MySQL in XAMPP Control Panel</p>";
    } else {
        $result = $conn->query("SHOW TABLES");
        $tableCount = $result ? $result->num_rows : 0;

        echo "<p style='color: green;'>✅ Database Connection: Working</p>";
        echo "<p style='color: green;'>✅ Tables Found: {$tableCount}</p>";

        if ($tableCount > 0) {
            echo "<p style='color: green;'>✅ System Status: Ready to use!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ System Status: Import needed</p>";
        }

        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Status: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Quick Test Links
echo "<h2>🧪 Quick Tests</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>🏠 Test Website</a>";
echo "<a href='aps_crm_system.php' style='background: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📞 Test CRM</a>";
echo "<a href='whatsapp_demo.php' style='background: #25d366; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📱 Test WhatsApp</a>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #007bff; color: white; border-radius: 8px;'>";
echo "<h3>🚀 APS Dream Home Database Setup</h3>";
echo "<p>Follow the instructions above to get your system connected to the database!</p>";
echo "</div>";

echo "</div>";
?>
