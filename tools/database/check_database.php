<?php
// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// HTML header
echo '<!DOCTYPE html>
<html>
<head>
    <title>डेटाबेस जांच रिपोर्ट</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>डेटाबेस जांच रिपोर्ट</h1>';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<p class="success">✅ डेटाबेस कनेक्शन सफल!</p>';
    
    // Get list of tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo '<h2>डेटाबेस टेबल्स:</h2>';
    echo '<ul>';
    foreach ($tables as $table) {
        $rowCount = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $status = $rowCount > 0 ? 'success' : 'warning';
        echo "<li class='$status'>$table - $rowCount रिकॉर्ड्स</li>";
    }
    echo '</ul>';
    
    // Check for views
    echo "<h2>डेटाबेस व्यूज़</h2>";
    
    $views = $pdo->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($views) > 0) {
        echo "<p>व्यूज़ की संख्या: " . count($views) . "</p>";
        echo "<ul>";
        foreach ($views as $view) {
            echo "<li>$view</li>";
        }
        echo "</ul>";
        
        // Check for problematic views
        echo "<h3>व्यू स्ट्रक्चर जांच</h3>";
        
        foreach ($views as $view) {
            try {
                // Try to select from view
                $pdo->query("SELECT * FROM `$view` LIMIT 1");
                echo "<p>✅ व्यू '$view' सही है</p>";
            } catch (PDOException $e) {
                echo "<p>❌ व्यू '$view' में समस्या है: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p>कोई व्यू नहीं मिला</p>";
    }
    
    // Check for stored procedures
    echo "<h2>स्टोर्ड प्रोसीजर्स</h2>";
    
    $procedures = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = '$dbname'")->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (count($procedures) > 0) {
        echo "<p>स्टोर्ड प्रोसीजर्स की संख्या: " . count($procedures) . "</p>";
        echo "<ul>";
        foreach ($procedures as $procedure) {
            echo "<li>$procedure</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>कोई स्टोर्ड प्रोसीजर स्टोर नहीं मिला</p>";
    }
    
    // Check for triggers
    echo "<h2>ट्रिगर्स</h2>";
    
    $triggers = $pdo->query("SHOW TRIGGERS")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($triggers) > 0) {
        echo "<p>ट्रिगर्स की संख्या: " . count($triggers) . "</p>";
        echo "<ul>";
        foreach ($triggers as $trigger) {
            echo "<li>$trigger</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>कोई ट्रिगर नहीं मिला</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ डेटाबेस कनेक्शन विफल: " . $e->getMessage() . "</p>";
}
?>