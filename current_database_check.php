<?php
/**
 * APS Dream Home - Current Database Inspection
 * Connect to database and show what's actually in it
 */

echo "<h1>🗄️ APS Dream Home - Current Database Inspection</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1600px; margin: 0 auto; padding: 20px;'>";

// Database Connection Test
echo "<h2>🔌 Step 1: Database Connection Test</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhomefinal');

    if ($conn->connect_error) {
        echo "<p style='color: red; font-size: 18px;'>❌ Database Connection FAILED</p>";
        echo "<p style='color: red;'>Error: " . $conn->connect_error . "</p>";
        echo "<p style='color: orange;'>💡 Solution: Start MySQL in XAMPP Control Panel</p>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🔧 How to Start MySQL:</h4>";
        echo "<ol>";
        echo "<li>Open XAMPP Control Panel</li>";
        echo "<li>Click 'Start' button next to MySQL</li>";
        echo "<li>Wait until it turns green</li>";
        echo "<li>Refresh this page</li>";
        echo "</ol>";
        echo "</div>";
        exit();
    } else {
        echo "<p style='color: green; font-size: 18px;'>✅ Database Connection: SUCCESSFUL</p>";
        echo "<p style='color: green;'>✅ Database: apsdreamhomefinal</p>";

        // Get database info
        $result = $conn->query("SELECT @@version as mysql_version");
        $versionInfo = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ MySQL Version: " . substr($versionInfo['mysql_version'], 0, 50) . "</p>";

        // Get database size
        $result = $conn->query("SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = 'apsdreamhomefinal'");

        if ($result) {
            $dbInfo = $result->fetch_assoc();
            $dbSize = $dbInfo['size_mb'] ?? 0;
            echo "<p style='color: green;'>✅ Database Size: {$dbSize} MB</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ Database Connection Error</p>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    exit();
}
echo "</div>";

// Current Tables List
echo "<h2>📊 Step 2: Current Database Tables</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    // Get all tables
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    $totalTables = 0;

    echo "<h3>📋 All Tables in Database (";

    if ($result) {
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        $totalTables = count($tables);
        echo "{$totalTables} total):</h3>";

        // Sort tables alphabetically
        sort($tables);

        echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; margin: 20px 0;'>";
        foreach ($tables as $table) {
            // Get row count for each table
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $countResult->fetch_assoc();
            $rowCount = $count['count'];

            // Get table info
            $infoResult = $conn->query("SHOW CREATE TABLE `{$table}`");
            $info = $infoResult->fetch_assoc();
            $createStatement = $info['Create Table'] ?? '';

            // Determine category based on table name
            $category = 'Other';
            if (strpos($table, 'user') !== false || strpos($table, 'admin') !== false) {
                $category = '👥 Users';
            } elseif (strpos($table, 'propert') !== false || strpos($table, 'plot') !== false) {
                $category = '🏠 Properties';
            } elseif (strpos($table, 'customer') !== false || strpos($table, 'lead') !== false) {
                $category = '📞 CRM';
            } elseif (strpos($table, 'book') !== false || strpos($table, 'transact') !== false) {
                $category = '💰 Bookings';
            } elseif (strpos($table, 'whatsapp') !== false || strpos($table, 'message') !== false) {
                $category = '📱 WhatsApp';
            } elseif (strpos($table, 'mlm') !== false || strpos($table, 'commission') !== false || strpos($table, 'associate') !== false) {
                $category = '💰 MLM';
            } elseif (strpos($table, 'farmer') !== false || strpos($table, 'colonizer') !== false) {
                $category = '🌾 Farmer';
            }

            echo "<div style='background: #007bff; color: white; padding: 15px; border-radius: 8px;'>";
            echo "<div style='font-weight: bold; font-size: 16px;'>{$table}</div>";
            echo "<div style='font-size: 14px; margin: 5px 0;'>{$category}</div>";
            echo "<div style='font-size: 18px; font-weight: bold;'>{$rowCount} rows</div>";
            echo "<div style='font-size: 12px; opacity: 0.8;'>" . strlen($createStatement) . " chars</div>";
            echo "</div>";
        }
        echo "</div>";

        echo "<h3>📈 Table Summary:</h3>";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>";

        $categories = [
            '👥 Users' => 0,
            '🏠 Properties' => 0,
            '📞 CRM' => 0,
            '💰 Bookings' => 0,
            '📱 WhatsApp' => 0,
            '💰 MLM' => 0,
            '🌾 Farmer' => 0,
            'Other' => 0
        ];

        foreach ($tables as $table) {
            if (strpos($table, 'user') !== false || strpos($table, 'admin') !== false) {
                $categories['👥 Users']++;
            } elseif (strpos($table, 'propert') !== false || strpos($table, 'plot') !== false) {
                $categories['🏠 Properties']++;
            } elseif (strpos($table, 'customer') !== false || strpos($table, 'lead') !== false) {
                $categories['📞 CRM']++;
            } elseif (strpos($table, 'book') !== false || strpos($table, 'transact') !== false) {
                $categories['💰 Bookings']++;
            } elseif (strpos($table, 'whatsapp') !== false || strpos($table, 'message') !== false) {
                $categories['📱 WhatsApp']++;
            } elseif (strpos($table, 'mlm') !== false || strpos($table, 'commission') !== false || strpos($table, 'associate') !== false) {
                $categories['💰 MLM']++;
            } elseif (strpos($table, 'farmer') !== false || strpos($table, 'colonizer') !== false) {
                $categories['🌾 Farmer']++;
            } else {
                $categories['Other']++;
            }
        }

        foreach ($categories as $category => $count) {
            echo "<div style='background: #28a745; color: white; padding: 15px; border-radius: 8px; text-align: center;'>";
            echo "<h4>{$category}</h4>";
            echo "<div style='font-size: 24px; font-weight: bold;'>{$count}</div>";
            echo "<div style='font-size: 12px;'>tables</div>";
            echo "</div>";
        }
        echo "</div>";

    } else {
        echo "<p style='color: red;'>❌ No tables found in database</p>";
        echo "<p style='color: orange;'>💡 Database may be empty. Consider importing data.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error getting table list: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Important Tables Data Check
echo "<h2>🔍 Step 3: Important Tables Data Check</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$importantTables = [
    'users' => '👥 User accounts and authentication',
    'properties' => '🏠 Property listings and details',
    'customers' => '📞 Customer information',
    'leads' => '📞 Lead management',
    'bookings' => '💰 Property bookings',
    'transactions' => '💰 Payment transactions',
    'whatsapp_messages' => '📱 WhatsApp message history',
    'whatsapp_templates' => '📱 WhatsApp message templates',
    'mlm_commissions' => '💰 MLM commission data',
    'associates' => '💰 MLM associates',
    'farmers' => '🌾 Farmer information',
    'colonizers' => '🌾 Colonizer/land development'
];

echo "<h3>📋 Important Tables Status:</h3>";
foreach ($importantTables as $table => $description) {
    try {
        if (in_array($table, $tables)) {
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $countResult->fetch_assoc();
            $rowCount = $count['count'];

            // Get sample data if table has records
            $sampleData = "";
            if ($rowCount > 0) {
                $sampleResult = $conn->query("SELECT * FROM `{$table}` LIMIT 1");
                if ($sampleResult) {
                    $sample = $sampleResult->fetch_assoc();
                    $sampleKeys = array_keys($sample);
                    $sampleData = implode(', ', array_slice($sampleKeys, 0, 3));
                }
            }

            echo "<div style='background: #d4edda; padding: 10px; margin: 8px 0; border-radius: 5px; border-left: 4px solid #28a745;'>";
            echo "<strong style='color: green;'>✅ {$table}</strong> - {$description}<br>";
            echo "<span style='color: #495057;'>Records: {$rowCount} | Fields: {$sampleData}</span>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; margin: 8px 0; border-radius: 5px; border-left: 4px solid #dc3545;'>";
            echo "<strong style='color: red;'>❌ {$table}</strong> - {$description}<br>";
            echo "<span style='color: #721c24;'>TABLE MISSING</span>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: #fff3cd; padding: 10px; margin: 8px 0; border-radius: 5px; border-left: 4px solid #ffc107;'>";
        echo "<strong style='color: orange;'>⚠️ {$table}</strong> - {$description}<br>";
        echo "<span style='color: #856404;'>Error: " . $e->getMessage() . "</span>";
        echo "</div>";
    }
}
echo "</div>";

// Database Health Check
echo "<h2>💊 Step 4: Database Health Check</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $healthIssues = [];

    // Check for important tables
    $requiredTables = ['users', 'properties', 'customers', 'leads'];
    $missingRequired = array_diff($requiredTables, $tables);

    if (!empty($missingRequired)) {
        $healthIssues[] = "Missing required tables: " . implode(', ', $missingRequired);
    }

    // Check for data in key tables
    foreach (['users', 'properties'] as $table) {
        if (in_array($table, $tables)) {
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $countResult->fetch_assoc();
            if ($count['count'] == 0) {
                $healthIssues[] = "Empty table: {$table} (no records)";
            }
        }
    }

    // Check for database corruption
    $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'apsdreamhomefinal' AND table_type = 'BASE TABLE'");
    if ($result) {
        $actualTableCount = $result->fetch_assoc()['count'];
        if ($actualTableCount != $totalTables) {
            $healthIssues[] = "Table count mismatch: Expected {$totalTables}, found {$actualTableCount}";
        }
    }

    // Overall health status
    if (empty($healthIssues)) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;'>";
        echo "<h3 style='color: green; font-size: 24px;'>🎉 Database Health: EXCELLENT</h3>";
        echo "<p style='font-size: 18px;'>✅ All systems operational</p>";
        echo "<p>Tables: {$totalTables} | Size: " . ($dbSize ?? 0) . " MB</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px;'>";
        echo "<h3 style='color: red; font-size: 24px;'>⚠️ Database Health: ISSUES FOUND</h3>";
        echo "<ul style='font-size: 16px;'>";
        foreach ($healthIssues as $issue) {
            echo "<li style='color: #721c24;'>❌ {$issue}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px;'>";
    echo "<h3 style='color: orange; font-size: 24px;'>⚠️ Health Check Error</h3>";
    echo "<p style='color: #856404;'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</div>";

// System Components Status
echo "<h2>🧩 Step 5: System Components Status</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$components = [
    'Property Management' => ['properties', 'property_visits', 'property_types'],
    'CRM System' => ['customers', 'leads', 'bookings'],
    'User Management' => ['users', 'user_roles', 'admin_users'],
    'WhatsApp Integration' => ['whatsapp_messages', 'whatsapp_templates'],
    'MLM System' => ['mlm_commissions', 'associates', 'mlm_users'],
    'Booking System' => ['bookings', 'transactions', 'payments'],
    'Farmer System' => ['farmers', 'colonizers', 'land_plots']
];

echo "<h3>🧩 System Components:</h3>";
foreach ($components as $component => $compTables) {
    $presentCount = 0;
    $totalCount = count($compTables);

    foreach ($compTables as $table) {
        if (in_array($table, $tables)) {
            $presentCount++;
        }
    }

    $status = $presentCount == $totalCount ? '✅ Complete' : ($presentCount > 0 ? '⚠️ Partial' : '❌ Missing');
    $color = $presentCount == $totalCount ? 'green' : ($presentCount > 0 ? 'orange' : 'red');

    echo "<div style='background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 5px solid {$color}'>";
    echo "<h4>{$component}</h4>";
    echo "<p><strong>Status:</strong> <span style='color: {$color}; font-weight: bold;'>{$status}</span></p>";
    echo "<p><strong>Tables:</strong> {$presentCount}/{$totalCount} present</p>";
    echo "<p><strong>Missing:</strong> " . implode(', ', array_diff($compTables, $tables)) . "</p>";
    echo "</div>";
}
echo "</div>";

// Final Summary and Recommendations
echo "<h2>🎯 Step 6: Final Summary & Recommendations</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

echo "<h3>📊 Database Summary:</h3>";
echo "<ul style='font-size: 16px;'>";
echo "<li><strong>Database Name:</strong> apsdreamhomefinal</li>";
echo "<li><strong>Total Tables:</strong> {$totalTables}</li>";
echo "<li><strong>Database Size:</strong> " . ($dbSize ?? 0) . " MB</li>";
echo "<li><strong>Health Status:</strong> " . (empty($healthIssues) ? '✅ Excellent' : '⚠️ Needs Attention') . "</li>";
echo "<li><strong>Components Status:</strong> " . (count($components) > 0 ? '✅ Operational' : '❌ Setup Needed') . "</li>";
echo "</ul>";

echo "<h3>🎉 System Status:</h3>";
if ($totalTables > 50 && empty($healthIssues)) {
    echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h2>🎊 DATABASE IS FULLY OPERATIONAL! 🎊</h2>";
    echo "<p style='font-size: 18px;'>✅ All {$totalTables} tables present</p>";
    echo "<p style='font-size: 18px;'>✅ All systems ready to use</p>";
    echo "</div>";
} elseif ($totalTables > 0) {
    echo "<div style='background: #ffc107; color: black; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h2>⚠️ DATABASE NEEDS ATTENTION</h2>";
    echo "<p style='font-size: 18px;'>📋 {$totalTables} tables found</p>";
    echo "<p style='font-size: 18px;'>🔧 Some components may need setup</p>";
    echo "</div>";
} else {
    echo "<div style='background: #dc3545; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h2>❌ DATABASE IS EMPTY</h2>";
    echo "<p style='font-size: 18px;'>📥 Need to import database data</p>";
    echo "<p style='font-size: 18px;'>🔧 Use apsdreamhomes.sql file</p>";
    echo "</div>";
}

echo "<h3>🚀 Recommended Actions:</h3>";
echo "<div style='background: white; padding: 15px; border-radius: 8px;'>";
echo "<ol style='font-size: 16px;'>";

if ($totalTables == 0) {
    echo "<li><strong>Import Main Database:</strong> Use <code>apsdreamhomes.sql</code> (231 MB)</li>";
    echo "<li><strong>Command:</strong> <code>mysql -u root -p apsdreamhomefinal < database/apsdreamhomes.sql</code></li>";
    echo "<li><strong>Alternative:</strong> Use <code>database_import.php</code> script</li>";
} elseif (!empty($healthIssues)) {
    echo "<li><strong>Fix Issues:</strong> Address the health issues found above</li>";
    echo "<li><strong>Import Missing Tables:</strong> Use <code>database_fixes.sql</code> if needed</li>";
    echo "<li><strong>Verify Data:</strong> Check important tables have sufficient data</li>";
} else {
    echo "<li><strong>Test System:</strong> All components are ready!</li>";
    echo "<li><strong>Access Points:</strong> index.php, aps_crm_system.php, whatsapp_demo.php</li>";
}

echo "</ol>";
echo "</div>";
echo "</div>";

// Quick Test Links
echo "<h2>🧪 Quick System Tests</h2>";
echo "<div style='background: #007bff; color: white; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h3>🚀 Test Your System:</h3>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 15px; margin: 15px 0;'>";
echo "<a href='index.php' style='background: rgba(255,255,255,0.2); color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>🏠 Main Website</a>";
echo "<a href='aps_crm_system.php' style='background: rgba(255,255,255,0.2); color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📞 CRM System</a>";
echo "<a href='whatsapp_demo.php' style='background: rgba(255,255,255,0.2); color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📱 WhatsApp Demo</a>";
echo "<a href='database_test.php' style='background: rgba(255,255,255,0.2); color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>🧪 Database Test</a>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #6f42c1; color: white; border-radius: 8px;'>";
echo "<h3>🗄️ Current Database Inspection Complete!</h3>";
echo "<p>Database: " . ($conn ? "✅ Connected" : "❌ Not Connected") . " | Tables: {$totalTables} | Size: " . ($dbSize ?? 0) . " MB</p>";
echo "<p>Status: " . ($totalTables > 50 ? "✅ FULLY OPERATIONAL" : ($totalTables > 0 ? "⚠️ PARTIALLY SET UP" : "❌ NEEDS SETUP")) . "</p>";
echo "</div>";

echo "</div>";
?>
