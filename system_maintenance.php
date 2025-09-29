<?php
/**
 * APS Dream Home - Automated Backup & Monitoring System
 * Complete system maintenance, backup, and health monitoring
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // No time limit for backup operations

// Database configuration
$host = 'localhost';
$database = 'apsdreamhomefinal';
$username = 'root';
$password = '';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>APS Dream Home - System Maintenance & Backup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .maintenance-card { margin-bottom: 20px; border-left: 4px solid #007bff; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        .backup-progress { height: 20px; }
        .system-status { padding: 15px; text-align: center; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class='text-center mb-4'>
        <h1><i class='fas fa-tools'></i> APS Dream Home - System Maintenance</h1>
        <p class='lead'>Automated Backup, Monitoring & Health Check System</p>
    </div>";

try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // System Health Check
    echo "<div class='card maintenance-card'>
        <div class='card-header bg-primary text-white'>
            <h5><i class='fas fa-heartbeat'></i> System Health Check</h5>
        </div>
        <div class='card-body'>";
    
    $health_checks = [];
    
    // Database Connection Test
    try {
        $pdo->query("SELECT 1");
        $health_checks['database'] = ['status' => 'healthy', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        $health_checks['database'] = ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
    
    // Table Count Check
    try {
        $table_count = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$database'")->fetch()['count'];
        $health_checks['tables'] = ['status' => 'healthy', 'message' => "$table_count tables found"];
    } catch (Exception $e) {
        $health_checks['tables'] = ['status' => 'error', 'message' => 'Table check failed'];
    }
    
    // Disk Space Check (for backup directory)
    $backup_dir = __DIR__ . '/backups';
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    $disk_free = disk_free_space($backup_dir);
    $disk_total = disk_total_space($backup_dir);
    $disk_used_percent = (($disk_total - $disk_free) / $disk_total) * 100;
    
    if ($disk_used_percent < 80) {
        $health_checks['disk_space'] = ['status' => 'healthy', 'message' => 'Disk usage: ' . number_format($disk_used_percent, 1) . '%'];
    } else {
        $health_checks['disk_space'] = ['status' => 'warning', 'message' => 'Disk usage high: ' . number_format($disk_used_percent, 1) . '%'];
    }
    
    // PHP Version Check
    $php_version = phpversion();
    if (version_compare($php_version, '8.0.0', '>=')) {
        $health_checks['php_version'] = ['status' => 'healthy', 'message' => "PHP version: $php_version"];
    } else {
        $health_checks['php_version'] = ['status' => 'warning', 'message' => "PHP version outdated: $php_version"];
    }
    
    // Display health check results
    echo "<div class='row'>";
    foreach ($health_checks as $check => $result) {
        $icon = $result['status'] == 'healthy' ? 'fa-check-circle text-success' : 
               ($result['status'] == 'warning' ? 'fa-exclamation-triangle text-warning' : 'fa-times-circle text-danger');
        
        echo "<div class='col-md-6 mb-3'>";
        echo "<div class='d-flex align-items-center'>";
        echo "<i class='fas $icon fa-2x me-3'></i>";
        echo "<div>";
        echo "<h6 class='mb-1'>" . ucfirst(str_replace('_', ' ', $check)) . "</h6>";
        echo "<small class='text-muted'>{$result['message']}</small>";
        echo "</div></div></div>";
    }
    echo "</div>";
    
    echo "</div></div>";
    
    // Database Backup
    echo "<div class='card maintenance-card'>
        <div class='card-header bg-success text-white'>
            <h5><i class='fas fa-database'></i> Database Backup</h5>
        </div>
        <div class='card-body'>";
    
    $backup_filename = 'apsdreamhome_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = $backup_dir . '/' . $backup_filename;
    
    echo "<p><strong>Creating database backup...</strong></p>";
    echo "<div class='progress mb-3'>";
    echo "<div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 0%'></div>";
    echo "</div>";
    
    // Generate backup using PHP (reliable solution)
    echo "<script>
        document.querySelector('.progress-bar').style.width = '20%';
        document.querySelector('.progress-bar').textContent = 'Getting table list...';
    </script>";
    
    // Create backup using PHP
    $backup_content = "-- APS Dream Home Database Backup\n";
    $backup_content .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $backup_content .= "-- Database: $database\n\n";
    $backup_content .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    // Get all tables
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    echo "<script>
        document.querySelector('.progress-bar').style.width = '30%';
        document.querySelector('.progress-bar').textContent = 'Found " . count($tables) . " tables';
    </script>";
    
    $total_tables = count($tables);
    $current_table = 0;
    
    // Backup each table
    foreach ($tables as $table) {
        $current_table++;
        $progress = 30 + (($current_table / $total_tables) * 60);
        
        echo "<script>
            document.querySelector('.progress-bar').style.width = '{$progress}%';
            document.querySelector('.progress-bar').textContent = 'Backing up: $table ($current_table/$total_tables)';
        </script>";
        
        // Get table structure
        $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
        $backup_content .= $create_table[1] . ";\n\n";
        
        // Get table data
        $data = $pdo->query("SELECT * FROM `$table`");
        if ($data->rowCount() > 0) {
            $backup_content .= "INSERT INTO `$table` VALUES ";
            $first_row = true;
            while ($row = $data->fetch(PDO::FETCH_NUM)) {
                if (!$first_row) $backup_content .= ",";
                $first_row = false;
                $backup_content .= "(";
                for ($i = 0; $i < count($row); $i++) {
                    if ($i > 0) $backup_content .= ",";
                    $backup_content .= $row[$i] === null ? "NULL" : "'" . addslashes($row[$i]) . "'";
                }
                $backup_content .= ")";
            }
            $backup_content .= ";\n\n";
        }
        
        // Flush output to show progress
        if (ob_get_level()) ob_flush();
        flush();
    }
    
    $backup_content .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Write backup file
    $backup_success = file_put_contents($backup_path, $backup_content);
    
    echo "<script>
        document.querySelector('.progress-bar').style.width = '100%';
        document.querySelector('.progress-bar').textContent = 'Backup completed!';
        document.querySelector('.progress-bar').classList.remove('progress-bar-animated');
    </script>";
    
    if ($backup_success && file_exists($backup_path)) {
        $backup_size = filesize($backup_path);
        echo "<script>
            document.querySelector('.progress-bar').style.width = '100%';
            document.querySelector('.progress-bar').textContent = 'Backup completed!';
            document.querySelector('.progress-bar').classList.remove('progress-bar-animated');
        </script>";
        
        echo "<div class='alert alert-success'>";
        echo "<i class='fas fa-check-circle'></i> <strong>Backup Successful!</strong><br>";
        echo "File: $backup_filename<br>";
        echo "Size: " . number_format($backup_size / 1024 / 1024, 2) . " MB<br>";
        echo "Location: $backup_path";
        echo "</div>";
        
        echo "<div class='text-center mb-3'>";
        echo "<a href='backups/$backup_filename' class='btn btn-success btn-lg' download>";
        echo "<i class='fas fa-download'></i> Download Backup File";
        echo "</a>";
        echo "</div>";
        
        // Compress backup for storage efficiency
        if (function_exists('gzopen')) {
            $gz_file = $backup_path . '.gz';
            $input = fopen($backup_path, 'rb');
            $output = gzopen($gz_file, 'wb9');
            
            while (!feof($input)) {
                gzwrite($output, fread($input, 4096));
            }
            
            fclose($input);
            gzclose($output);
            
            $gz_size = filesize($gz_file);
            $compression_ratio = (1 - ($gz_size / $backup_size)) * 100;
            
            echo "<div class='alert alert-info'>";
            echo "<i class='fas fa-compress'></i> <strong>Backup Compressed!</strong><br>";
            echo "Compressed file: {$backup_filename}.gz<br>";
            echo "Compressed size: " . number_format($gz_size / 1024 / 1024, 2) . " MB<br>";
            echo "Compression ratio: " . number_format($compression_ratio, 1) . "%";
            echo "</div>";
            
            // Remove uncompressed file to save space
            unlink($backup_path);
        }
        
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<i class='fas fa-times-circle'></i> <strong>Backup Failed!</strong><br>";
        echo "Unable to write backup file to: $backup_path<br>";
        echo "Please check file permissions and disk space.";
        echo "</div>";
    }
    
    echo "</div></div>";
    
    // System Statistics
    echo "<div class='card maintenance-card'>
        <div class='card-header bg-info text-white'>
            <h5><i class='fas fa-chart-bar'></i> System Statistics</h5>
        </div>
        <div class='card-body'>";
    
    $stats = [];
    
    // Database statistics
    $stats['tables'] = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$database'")->fetchColumn();
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['properties'] = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $stats['bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $stats['associates'] = $pdo->query("SELECT COUNT(*) FROM associates")->fetchColumn();
    $stats['commissions'] = $pdo->query("SELECT SUM(commission_amount) FROM commission_transactions")->fetchColumn() ?: 0;
    
    // File system statistics
    $stats['backup_files'] = count(glob($backup_dir . '/*.{sql,gz}', GLOB_BRACE));
    $stats['backup_size'] = 0;
    foreach (glob($backup_dir . '/*.{sql,gz}', GLOB_BRACE) as $file) {
        $stats['backup_size'] += filesize($file);
    }
    
    echo "<div class='row'>";
    echo "<div class='col-md-4 mb-3'>";
    echo "<div class='system-status bg-light rounded'>";
    echo "<h3 class='text-primary'>{$stats['tables']}</h3>";
    echo "<p class='mb-0'>Database Tables</p>";
    echo "</div></div>";
    
    echo "<div class='col-md-4 mb-3'>";
    echo "<div class='system-status bg-light rounded'>";
    echo "<h3 class='text-success'>{$stats['users']}</h3>";
    echo "<p class='mb-0'>Total Users</p>";
    echo "</div></div>";
    
    echo "<div class='col-md-4 mb-3'>";
    echo "<div class='system-status bg-light rounded'>";
    echo "<h3 class='text-info'>{$stats['properties']}</h3>";
    echo "<p class='mb-0'>Properties Listed</p>";
    echo "</div></div>";
    
    echo "<div class='col-md-4 mb-3'>";
    echo "<div class='system-status bg-light rounded'>";
    echo "<h3 class='text-warning'>{$stats['bookings']}</h3>";
    echo "<p class='mb-0'>Total Bookings</p>";
    echo "</div></div>";
    
    echo "<div class='col-md-4 mb-3'>";
    echo "<div class='system-status bg-light rounded'>";
    echo "<h3 class='text-danger'>{$stats['associates']}</h3>";
    echo "<p class='mb-0'>Associates</p>";
    echo "</div></div>";
    
    echo "<div class='col-md-4 mb-3'>";
    echo "<div class='system-status bg-light rounded'>";
    echo "<h3 class='text-success'>₹" . number_format($stats['commissions']) . "</h3>";
    echo "<p class='mb-0'>Total Commissions</p>";
    echo "</div></div>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h6>Backup Information</h6>";
    echo "<p><strong>Backup Files:</strong> {$stats['backup_files']} files</p>";
    echo "<p><strong>Total Backup Size:</strong> " . number_format($stats['backup_size'] / 1024 / 1024, 2) . " MB</p>";
    
    echo "</div></div>";
    
    // Backup File Management
    echo "<div class='card maintenance-card'>
        <div class='card-header bg-warning text-white'>
            <h5><i class='fas fa-archive'></i> Backup File Management</h5>
        </div>
        <div class='card-body'>";
    
    $backup_files = glob($backup_dir . '/*.{sql,gz}', GLOB_BRACE);
    
    if (!empty($backup_files)) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Filename</th><th>Size</th><th>Created</th><th>Actions</th></tr></thead>";
        echo "<tbody>";
        
        // Sort files by modification time (newest first)
        usort($backup_files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        foreach ($backup_files as $file) {
            $filename = basename($file);
            $size = number_format(filesize($file) / 1024 / 1024, 2) . ' MB';
            $created = date('Y-m-d H:i:s', filemtime($file));
            
            echo "<tr>";
            echo "<td><i class='fas fa-file-archive'></i> $filename</td>";
            echo "<td>$size</td>";
            echo "<td>$created</td>";
            echo "<td><a href='backups/$filename' class='btn btn-sm btn-primary' download><i class='fas fa-download'></i> Download</a></td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
        
        // Cleanup old backups (keep last 10)
        if (count($backup_files) > 10) {
            $files_to_delete = array_slice($backup_files, 10);
            foreach ($files_to_delete as $file) {
                unlink($file);
            }
            echo "<div class='alert alert-info'>";
            echo "<i class='fas fa-trash'></i> Cleaned up " . count($files_to_delete) . " old backup files (keeping last 10)";
            echo "</div>";
        }
        
    } else {
        echo "<p class='text-muted'>No backup files found.</p>";
    }
    
    echo "</div></div>";
    
    // Maintenance Recommendations
    echo "<div class='card maintenance-card'>
        <div class='card-header bg-secondary text-white'>
            <h5><i class='fas fa-lightbulb'></i> Maintenance Recommendations</h5>
        </div>
        <div class='card-body'>";
    
    $recommendations = [];
    
    // Check last backup age
    if (!empty($backup_files)) {
        $latest_backup = max(array_map('filemtime', $backup_files));
        $hours_since_backup = (time() - $latest_backup) / 3600;
        
        if ($hours_since_backup > 24) {
            $recommendations[] = "Consider running daily backups. Last backup was " . number_format($hours_since_backup, 1) . " hours ago.";
        }
    } else {
        $recommendations[] = "No backups found. Set up regular backup schedule.";
    }
    
    // Check database size
    $db_size_query = $pdo->query("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
        FROM information_schema.tables 
        WHERE table_schema = '$database'
    ");
    $db_size = $db_size_query->fetchColumn();
    
    if ($db_size > 1000) {
        $recommendations[] = "Database size is {$db_size}MB. Consider archiving old data.";
    }
    
    // Check user activity
    $inactive_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();
    if ($inactive_users > 0) {
        $recommendations[] = "Found $inactive_users inactive users. Consider cleanup.";
    }
    
    if (!empty($recommendations)) {
        echo "<ul class='list-group list-group-flush'>";
        foreach ($recommendations as $recommendation) {
            echo "<li class='list-group-item'><i class='fas fa-info-circle text-info'></i> $recommendation</li>";
        }
        echo "</ul>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<i class='fas fa-check-circle'></i> All maintenance checks passed! System is in good health.";
        echo "</div>";
    }
    
    echo "</div></div>";
    
    // System Information
    echo "<div class='card maintenance-card'>
        <div class='card-header bg-dark text-white'>
            <h5><i class='fas fa-info-circle'></i> System Information</h5>
        </div>
        <div class='card-body'>";
    
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<h6>Server Information</h6>";
    echo "<ul class='list-unstyled'>";
    echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
    echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>";
    echo "<li><strong>Operating System:</strong> " . php_uname('s r') . "</li>";
    echo "<li><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</li>";
    echo "<li><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "s</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='col-md-6'>";
    echo "<h6>Database Information</h6>";
    echo "<ul class='list-unstyled'>";
    echo "<li><strong>Database:</strong> $database</li>";
    echo "<li><strong>Host:</strong> $host</li>";
    echo "<li><strong>Size:</strong> {$db_size}MB</li>";
    echo "<li><strong>Tables:</strong> {$stats['tables']}</li>";
    echo "<li><strong>Character Set:</strong> utf8mb4</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
    echo "</div></div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Database Error: " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ System Error: " . $e->getMessage() . "</div>";
}

echo "
    <div class='text-center mt-4 mb-5'>
        <a href='admin/' class='btn btn-success btn-lg me-2'>
            <i class='fas fa-tachometer-alt'></i> Back to Admin
        </a>
        <a href='system_test_complete.php' class='btn btn-info btn-lg me-2'>
            <i class='fas fa-chart-line'></i> System Status
        </a>
        <button onclick='location.reload()' class='btn btn-primary btn-lg'>
            <i class='fas fa-sync-alt'></i> Refresh
        </button>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>