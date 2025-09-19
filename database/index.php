<?php
/**
 * APS Dream Home Database Management Hub
 * 
 * This script provides a central interface to access all database management tools
 * for the APS Dream Home system.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home Database Management Hub</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        h1 {
            margin: 0;
            padding: 0 20px;
            font-size: 28px;
        }
        h2 {
            color: #3498db;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            width: calc(33.333% - 20px);
            min-width: 300px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .card h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .card p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #95a5a6;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .btn-warning {
            background-color: #f39c12;
        }
        .btn-warning:hover {
            background-color: #e67e22;
        }
        .stats {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .success { color: #2ecc71; }
        .warning { color: #f39c12; }
        .error { color: #e74c3c; }
        footer {
            margin-top: 50px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>APS Dream Home Database Management Hub</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="stats">
            <h2>Database Status</h2>
            <?php
            // Connect to database
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "apsdreamhomefinal";
            
            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            
            // Check connection
            if ($conn->connect_error) {
                echo "<p class='error'>Connection failed: " . $conn->connect_error . "</p>";
            } else {
                echo "<p class='success'>Database connection successful</p>";
                
                // Get table counts
                $tables = [
                    'properties' => 'Properties',
                    'customers' => 'Customers',
                    'leads' => 'Leads/Inquiries',
                    'bookings' => 'Bookings',
                    'transactions' => 'Transactions',
                    'users' => 'Users'
                ];
                
                echo "<table>
                    <tr>
                        <th>Table</th>
                        <th>Record Count</th>
                        <th>Status</th>
                    </tr>";
                
                foreach ($tables as $table => $label) {
                    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        $count = $row['count'];
                        $status = $count > 0 ? 'OK' : 'Empty';
                        $statusClass = $count > 0 ? 'success' : 'error';
                        
                        echo "<tr>
                            <td>$label</td>
                            <td>$count</td>
                            <td class='$statusClass'>$status</td>
                        </tr>";
                    } else {
                        echo "<tr>
                            <td>$label</td>
                            <td>-</td>
                            <td class='error'>Error</td>
                        </tr>";
                    }
                }
                
                echo "</table>";
            }
            ?>
        </div>
        
        <h2>Database Management Tools</h2>
        <div class="card-container">
            <div class="card">
                <h3>Dashboard Data Manager</h3>
                <p>Check and refresh demo data for all dashboard widgets.</p>
                <a href="dashboard_data_manager.php" class="btn">Open Tool</a>
            </div>
            
            <div class="card">
                <h3>Dashboard Verification Report</h3>
                <p>Generate a comprehensive report on dashboard data status.</p>
                <a href="dashboard_verification_report.php" class="btn">View Report</a>
            </div>
            
            <div class="card">
                <h3>Final Dashboard Check</h3>
                <p>Verify and fix all dashboard widgets to ensure they display data.</p>
                <a href="final_dashboard_check.php" class="btn btn-success">Run Check</a>
            </div>
            
            <div class="card">
                <h3>Structure-Based Seed</h3>
                <p>Analyze table structures and seed appropriate demo data.</p>
                <a href="structure_based_seed.php" class="btn btn-warning">Run Seeder</a>
                <a href="seeder_enhancement.php" class="btn btn-success mt-2">Enhanced Seeder</a>
            </div>
            
            <div class="card">
                <h3>Database Optimizer</h3>
                <p>Clean and optimize database tables for better performance.</p>
                <a href="optimize_database.php" class="btn">Optimize Database</a>
            </div>
            
            <div class="card">
                <h3>Backup & Restore</h3>
                <p>Create and manage database backups.</p>
                <a href="backup_demo_data.php" class="btn">Manage Backups</a>
            </div>
            
            <div class="card">
                <h3>Date Refresher</h3>
                <p>Update date-based demo data to keep it current.</p>
                <a href="refresh_demo_dates.php" class="btn">Refresh Dates</a>
            </div>
            
            <div class="card">
                <h3>Documentation</h3>
                <p>View documentation about the demo data structure.</p>
                <a href="README_DEMO_DATA.md" class="btn btn-secondary">Demo Data Docs</a>
                <a href="DATABASE_TOOLS_GUIDE.md" class="btn btn-secondary mt-2">Tools Guide</a>
                <a href="UPDATE_LOG.md" class="btn btn-secondary mt-2">Update Log</a>
            </div>
            
            <div class="card">
                <h3>Admin Dashboard</h3>
                <p>Go to the admin dashboard to see the demo data in action.</p>
                <a href="../admin/dashboard.php" class="btn">Open Dashboard</a>
            </div>
        </div>
        
        <h2>Quick Actions</h2>
        <div class="card-container">
            <div class="card">
                <h3>API Documentation</h3>
                <p>Auto-generate comprehensive documentation for all system APIs and endpoints.</p>
                <a href="api_documentation.php" class="btn btn-primary">View API Docs</a>
            </div>

            <div class="card">
                <h3>Database Migration Manager</h3>
                <p>Manage database schema changes and versioning for smooth system updates.</p>
                <a href="migration_manager.php" class="btn btn-primary">Manage Migrations</a>
            </div>

            <div class="card">
                <h3>Data Export & Reporting</h3>
                <p>Generate business reports and export data in various formats for analysis.</p>
                <a href="data_export_tool.php" class="btn btn-primary">Open Reporting Tool</a>
            </div>
            
            <div class="card">
                <h3>System Health Check</h3>
                <p>Run a comprehensive health check of your entire APS Dream Home system.</p>
                <a href="system_health_check.php" class="btn btn-primary">Run Health Check</a>
            </div>
            <div class="card">
                <h3>Verify All Tables</h3>
                <p>Check all tables in the database for proper structure and data.</p>
                <a href="dashboard_verification_report.php" class="btn">Run Verification</a>
            </div>
            
            <div class="card">
                <h3>Refresh Demo Data</h3>
                <p>Refresh all demo data in the database for optimal dashboard display.</p>
                <a href="dashboard_data_manager.php?refresh=1" class="btn btn-warning">Refresh Data</a>
            </div>
            
            <div class="card">
                <h3>Fix MLM Commission Tables</h3>
                <p>Fix inconsistencies in MLM commission tables and ensure proper data.</p>
                <a href="fix_mlm_commission_tables.php" class="btn btn-danger">Fix Commission Tables</a>
            </div>
            
            <div class="card">
                <h3>Fix Leads Data</h3>
                <p>Fix missing or incomplete data in the leads table for dashboard widgets.</p>
                <a href="fix_leads_data.php" class="btn btn-danger">Fix Leads Data</a>
            </div>
            
            <div class="card">
                <h3>Create Backup</h3>
                <p>Create a backup of your current database state.</p>
                <a href="backup_demo_data.php?action=backup" class="btn btn-success">Create Backup</a>
            </div>
            
            <div class="card">
                <h3>Update Dates</h3>
                <p>Update all date-based demo data to keep it current.</p>
                <a href="refresh_demo_dates.php?refresh=1" class="btn">Update Dates</a>
            </div>
            
            <div class="card">
                <h3>View Database Structure</h3>
                <p>Explore the database structure and relationships.</p>
                <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=apsdreamhomefinal" target="_blank" class="btn btn-secondary">Open phpMyAdmin</a>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>APS Dream Home Database Management Hub &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
</body>
</html>
