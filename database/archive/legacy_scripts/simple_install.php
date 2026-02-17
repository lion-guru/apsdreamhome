<?php
/**
 * Simple Accounting Database Installation Script
 */

require_once '../includes/config.php';

echo "<h2>Installing Comprehensive Accounting System...</h2>\n";

$sql_files = [
    'accounting_system_database.sql',
    'accounting_system_database_part2.sql', 
    'accounting_system_database_part3.sql'
];

$total_success = 0;
$total_errors = 0;

foreach ($sql_files as $file) {
    echo "<h3>Processing: $file</h3>\n";
    
    if (!file_exists($file)) {
        echo "<p style='color: red;'>❌ File not found: $file</p>\n";
        $total_errors++;
        continue;
    }
    
    $sql_content = file_get_contents($file);
    
    // Split by semicolon and clean up
    $statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    foreach ($statements as $sql) {
        if (trim($sql)) {
            try {
                $result = $conn->query($sql);
                if ($result) {
                    if (stripos($sql, 'CREATE TABLE') !== false) {
                        preg_match('/CREATE TABLE[^`]*`([^`]+)`/', $sql, $matches);
                        if (isset($matches[1])) {
                            echo "<p style='color: green;'>✅ Created table: {$matches[1]}</p>\n";
                        }
                    } elseif (stripos($sql, 'INSERT INTO') !== false) {
                        echo "<p style='color: blue;'>✅ Inserted default data</p>\n";
                    }
                    $total_success++;
                } else {
                    throw new Exception($conn->error);
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
                $total_errors++;
            }
        }
    }
    
    echo "<hr>\n";
}

echo "<h2>Installation Complete!</h2>\n";
echo "<p><strong>Total Successful Operations:</strong> $total_success</p>\n";
echo "<p><strong>Total Errors:</strong> $total_errors</p>\n";

if ($total_errors == 0) {
    echo "<p style='color: green; font-size: 18px;'>🎉 Accounting system installed successfully!</p>\n";
    echo "<p><a href='../admin/accounting_dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Open Accounting Dashboard</a></p>\n";
} else {
    echo "<p style='color: orange;'>⚠️ Installation completed with some errors. Please review above.</p>\n";
}

$conn->close();
?>