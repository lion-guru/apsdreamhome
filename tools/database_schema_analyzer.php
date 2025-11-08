<?php
/**
 * APS Dream Home - Database Schema Analyzer
 * Analyzes the current database structure and provides recommendations
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'includes/db_connection.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Schema Analyzer - APS Dream Home</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        .schema-info { margin: 10px 0; padding: 15px; border-radius: 8px; }
        .success { background-color: #d4edda; border-left: 4px solid #28a745; }
        .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; }
        .info { background-color: #d1ecf1; border-left: 4px solid #17a2b8; }
        .error { background-color: #f8d7da; border-left: 4px solid #dc3545; }
        table { font-size: 0.9em; }
        .btn { border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body>
    <nav class='navbar navbar-expand-lg navbar-dark bg-primary'>
        <div class='container'>
            <a class='navbar-brand' href='index.php'>
                <i class='fas fa-home me-2'></i>APS Dream Home
            </a>
        </div>
    </nav>

    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-lg-10'>
                <div class='card shadow-lg'>
                    <div class='card-header bg-primary text-white'>
                        <h1 class='card-title mb-0'>
                            <i class='fas fa-database me-2'></i>Database Schema Analyzer
                        </h1>
                        <p class='mb-0 mt-2'>Analyzing your database structure and providing recommendations</p>
                    </div>
                    <div class='card-body'>";

try {
    $conn = getDbConnection();

    echo "<div class='alert alert-success'>
        <i class='fas fa-check-circle me-2'></i>
        <strong>Database Connection:</strong> ✅ Successfully connected to database
    </div>";

    // Get all tables
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>📋 Database Tables (" . count($tables) . " found)</h3>";

    foreach ($tables as $table) {
        echo "<div class='schema-info info'>";
        echo "<h5><i class='fas fa-table me-2'></i>$table</h5>";

        // Get table structure
        $result = $conn->query("DESCRIBE $table");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);

        echo "<div class='table-responsive'>
            <table class='table table-sm table-striped'>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($columns as $col) {
            echo "<tr>
                <td><strong>{$col['Field']}</strong></td>
                <td>{$col['Type']}</td>
                <td>{$col['Null']}</td>
                <td>{$col['Key']}</td>
                <td>{$col['Default']}</td>
                <td>{$col['Extra']}</td>
            </tr>";
        }

        echo "</tbody>
            </table>
        </div>";

        // Get row count
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<small class='text-muted'>Total rows: <strong>$count</strong></small>";

        echo "</div>";
    }

    // Recommendations
    echo "<h3 class='mt-5'>💡 Recommendations</h3>";

    $recommendations = [];

    if (in_array('users', $tables)) {
        $recommendations[] = "✅ Users table exists - ready for user management";
    } else {
        $recommendations[] = "❌ Users table missing - user management won't work";
    }

    if (in_array('properties', $tables)) {
        $recommendations[] = "✅ Properties table exists - property listings ready";
    } else {
        $recommendations[] = "❌ Properties table missing - property features won't work";
    }

    if (in_array('property_types', $tables)) {
        $recommendations[] = "✅ Property types table exists - categorization ready";
    } else {
        $recommendations[] = "⚠️ Property types table missing - consider adding for better organization";
    }

    if (in_array('contacts', $tables)) {
        $recommendations[] = "✅ Contacts table exists - contact forms ready";
    } else {
        $recommendations[] = "⚠️ Contacts table missing - contact functionality limited";
    }

    if (in_array('testimonials', $tables)) {
        $recommendations[] = "✅ Testimonials table exists - customer reviews ready";
    } else {
        $recommendations[] = "⚠️ Testimonials table missing - review system not available";
    }

    foreach ($recommendations as $rec) {
        echo "<div class='alert alert-info'>
            <i class='fas fa-lightbulb me-2'></i>$rec
        </div>";
    }

    // Demo Data Setup
    echo "<h3 class='mt-5'>🚀 Quick Actions</h3>";
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<div class='card border-primary'>";
    echo "<div class='card-body text-center'>";
    echo "<i class='fas fa-database fa-3x text-primary mb-3'></i>";
    echo "<h5>Setup Demo Data</h5>";
    echo "<p class='text-muted'>Add sample data for testing</p>";
    echo "<a href='setup_demo_data_fixed.php' class='btn btn-primary'>
        <i class='fas fa-play me-2'></i>Setup Demo Data
    </a>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-6'>";
    echo "<div class='card border-success'>";
    echo "<div class='card-body text-center'>";
    echo "<i class='fas fa-home fa-3x text-success mb-3'></i>";
    echo "<h5>View Homepage</h5>";
    echo "<p class='text-muted'>See your website in action</p>";
    echo "<a href='index.php' class='btn btn-success'>
        <i class='fas fa-home me-2'></i>Go to Homepage
    </a>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<div class='mt-4 text-center'>";
    echo "<a href='system_verification.php' class='btn btn-info btn-lg me-3'>
        <i class='fas fa-cog me-2'></i>System Verification
    </a>";
    echo "<a href='comprehensive_test.php' class='btn btn-warning btn-lg'>
        <i class='fas fa-vial me-2'></i>Full Test Suite
    </a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Database Connection Error</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p>Please check your database configuration in includes/config.php</p>";
    echo "</div>";
}

echo "
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";

?>
