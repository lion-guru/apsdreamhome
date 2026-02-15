<?php
/**
 * System Status Check
 * Comprehensive test of all components
 */

echo "<h1>🔍 APS Dream Homes - System Status Check</h1>";

$status = [];
$errors = [];
$warnings = [];

// Test 1: XAMPP Status
echo "<h3>1. Testing XAMPP Services...</h3>";
if (isset($_SERVER['SERVER_NAME'])) {
    echo "<div style='color: green;'>✅ Web server is running</div>";
    $status['xampp'] = 'running';
} else {
    echo "<div style='color: red;'>❌ Web server not accessible</div>";
    $errors[] = 'XAMPP may not be running';
    $status['xampp'] = 'down';
}

// Test 2: Database Connection
echo "<h3>2. Testing Database Connection...</h3>";
try {
    define('INCLUDED_FROM_MAIN', true);
    require_once 'includes/db_connection.php';

    if ($pdo) {
        echo "<div style='color: green;'>✅ Database connection successful</div>";
        $status['database'] = 'connected';

        // Test query
        try {
            $stmt = $pdo->query("SELECT DATABASE()");
            $db_name = $stmt->fetchColumn();
            echo "<div style='color: green;'>✅ Database accessible: $db_name</div>";

            // Check if tables exist
            $tables = ['company_settings', 'properties', 'property_types', 'users'];
            $missing_tables = [];

            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
                    $result = $stmt->fetch();
                    echo "<div style='color: green;'>✅ Table $table exists</div>";
                } catch (Exception $e) {
                    echo "<div style='color: red;'>❌ Table $table missing</div>";
                    $missing_tables[] = $table;
                }
            }

            if (empty($missing_tables)) {
                echo "<div style='color: green;'>✅ All required tables exist</div>";
                $status['tables'] = 'complete';
            } else {
                echo "<div style='color: red;'>❌ Missing tables: " . implode(', ', $missing_tables) . "</div>";
                $errors[] = 'Database tables missing - run setup';
                $status['tables'] = 'incomplete';
            }

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Database query failed: " . $e->getMessage() . "</div>";
            $errors[] = 'Database query failed';
            $status['database'] = 'error';
        }

    } else {
        echo "<div style='color: red;'>❌ Database connection failed</div>";
        $errors[] = 'Database connection failed';
        $status['database'] = 'failed';
    }

} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</div>";
    $errors[] = 'Database connection error';
    $status['database'] = 'error';
}

// Test 3: File System
echo "<h3>3. Testing File System...</h3>";
$files_to_check = [
    'includes/enhanced_universal_template.php',
    'includes/Database.php',
    'about_template.php',
    'contact_template.php',
    'properties_template.php',
    'index_template.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<div style='color: green;'>✅ $file exists ($size bytes)</div>";
        $status['files'][$file] = 'exists';
    } else {
        echo "<div style='color: red;'>❌ $file missing</div>";
        $errors[] = "File missing: $file";
        $status['files'][$file] = 'missing';
    }
}

// Test 4: Template System
echo "<h3>4. Testing Template System...</h3>";
try {
    require_once 'includes/enhanced_universal_template.php';
    echo "<div style='color: green;'>✅ Enhanced template system loaded</div>";
    $status['templates'] = 'working';
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Template system error: " . $e->getMessage() . "</div>";
    $errors[] = 'Template system error';
    $status['templates'] = 'error';
}

// Summary
echo "<hr>";
echo "<h2>📊 SYSTEM STATUS SUMMARY</h2>";

if (empty($errors)) {
    echo "<div style='color: green; font-size: 20px; padding: 20px; background: #d4edda; border-radius: 5px;'>";
    echo "🎉 SYSTEM READY!<br><br>";
    echo "✅ All components working<br>";
    echo "✅ Database connected<br>";
    echo "✅ Files accessible<br>";
    echo "✅ Template system active<br><br>";
    echo "<strong>Your APS Dream Homes website is fully functional!</strong>";
    echo "</div>";
} else {
    echo "<div style='color: red; font-size: 18px; padding: 20px; background: #f8d7da; border-radius: 5px;'>";
    echo "⚠️ ISSUES FOUND:<br><br>";
    foreach ($errors as $error) {
        echo "• $error<br>";
    }
    echo "<br><strong>Next step: Run database setup</strong>";
    echo "</div>";
}

echo "<div style='margin-top: 30px;'>";
echo "<h3>🚀 RECOMMENDED ACTIONS:</h3>";

// Action buttons based on status
if ($status['database'] === 'connected' && $status['tables'] === 'incomplete') {
    echo "<a href='auto_database_setup.php' style='color: green; text-decoration: none; font-size: 18px; margin: 0 15px;'>⚙️ Run Database Setup</a>";
}

if ($status['xampp'] === 'running') {
    echo "<a href='db_test.php' style='color: blue; text-decoration: none; font-size: 18px; margin: 0 15px;'>🧪 Test Database</a>";
    echo "<a href='about_template.php' style='color: purple; text-decoration: none; font-size: 18px; margin: 0 15px;'>📄 Test About Page</a>";
}

echo "</div>";

echo "<div style='margin-top: 20px; font-size: 12px; color: #666;'>";
echo "Generated by APS Dream Homes System Status Check";
echo "</div>";
?>
