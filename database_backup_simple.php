<?php
/**
 * Simple Database Backup Tool for Abhay Singh
 * Works without mysqldump dependencies
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

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
    <title>Database Backup - APS Dream Home</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .backup-card { margin-bottom: 20px; border-left: 4px solid #28a745; }
        .progress-custom { height: 25px; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class='text-center mb-4'>
        <h1><i class='fas fa-download'></i> Database Backup System</h1>
        <p class='lead'>Simple and reliable backup for APS Dream Home</p>
    </div>";

// Create backup directory
$backup_dir = __DIR__ . '/backups';
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='card backup-card'>
        <div class='card-header bg-success text-white'>
            <h5><i class='fas fa-database'></i> Creating Database Backup</h5>
        </div>
        <div class='card-body'>";
    
    $backup_filename = 'apsdreamhome_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = $backup_dir . '/' . $backup_filename;
    
    // Start backup progress
    echo "<div class='mb-3'>
        <div class='progress progress-custom'>
            <div class='progress-bar bg-success progress-bar-striped progress-bar-animated' id='backupProgress' role='progressbar' style='width: 10%'>
                Getting table list...
            </div>
        </div>
    </div>";
    
    // Open backup file
    $backup_file = fopen($backup_path, 'w');
    
    // Write backup header
    fwrite($backup_file, "-- APS Dream Home Database Backup\n");
    fwrite($backup_file, "-- Generated on: " . date('Y-m-d H:i:s') . "\n");
    fwrite($backup_file, "-- Database: $database\n");
    fwrite($backup_file, "-- Tables: ");
    
    // Get all tables
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    fwrite($backup_file, count($tables) . "\n\n");
    fwrite($backup_file, "SET FOREIGN_KEY_CHECKS=0;\n\n");
    
    $total_tables = count($tables);
    $current_table = 0;
    
    echo "<script>
        document.getElementById('backupProgress').style.width = '20%';
        document.getElementById('backupProgress').textContent = 'Found $total_tables tables';
    </script>";
    
    // Backup each table
    foreach ($tables as $table) {
        $current_table++;
        $progress = 20 + (($current_table / $total_tables) * 70);
        
        echo "<script>
            document.getElementById('backupProgress').style.width = '{$progress}%';
            document.getElementById('backupProgress').textContent = 'Backing up table: $table ($current_table/$total_tables)';
        </script>";
        
        // Get table structure
        $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        fwrite($backup_file, "-- Table structure for `$table`\n");
        fwrite($backup_file, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($backup_file, $create_table[1] . ";\n\n");
        
        // Get table data
        $data = $pdo->query("SELECT * FROM `$table`");
        $num_fields = $data->columnCount();
        
        if ($data->rowCount() > 0) {
            fwrite($backup_file, "-- Data for table `$table`\n");
            fwrite($backup_file, "INSERT INTO `$table` VALUES ");
            
            $first_row = true;
            while ($row = $data->fetch(PDO::FETCH_NUM)) {
                if (!$first_row) {
                    fwrite($backup_file, ",");
                }
                $first_row = false;
                
                fwrite($backup_file, "(");
                for ($i = 0; $i < $num_fields; $i++) {
                    if ($i > 0) {
                        fwrite($backup_file, ",");
                    }
                    if ($row[$i] === null) {
                        fwrite($backup_file, "NULL");
                    } else {
                        fwrite($backup_file, "'" . addslashes($row[$i]) . "'");
                    }
                }
                fwrite($backup_file, ")");
            }
            fwrite($backup_file, ";\n\n");
        }
        
        // Flush output to show progress
        if (ob_get_level()) ob_flush();
        flush();
    }
    
    fwrite($backup_file, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($backup_file);
    
    echo "<script>
        document.getElementById('backupProgress').style.width = '100%';
        document.getElementById('backupProgress').textContent = 'Backup completed successfully!';
        document.getElementById('backupProgress').classList.remove('progress-bar-animated');
    </script>";
    
    $backup_size = filesize($backup_path);
    
    echo "<div class='alert alert-success mt-3'>
        <h5><i class='fas fa-check-circle'></i> Backup Successful!</h5>
        <p><strong>File:</strong> $backup_filename</p>
        <p><strong>Size:</strong> " . number_format($backup_size / 1024 / 1024, 2) . " MB</p>
        <p><strong>Tables backed up:</strong> $total_tables</p>
        <p><strong>Location:</strong> " . str_replace(__DIR__, '', $backup_path) . "</p>
    </div>";
    
    // Create download link
    echo "<div class='text-center mt-4'>
        <a href='backups/$backup_filename' class='btn btn-success btn-lg me-3' download>
            <i class='fas fa-download'></i> Download Backup
        </a>
        <button class='btn btn-primary btn-lg' onclick='window.location.reload()'>
            <i class='fas fa-redo'></i> Create New Backup
        </button>
    </div>";
    
    echo "</div></div>";
    
    // Show existing backups
    $existing_backups = glob($backup_dir . '/*.sql');
    if (count($existing_backups) > 1) {
        echo "<div class='card backup-card'>
            <div class='card-header bg-info text-white'>
                <h5><i class='fas fa-history'></i> Previous Backups</h5>
            </div>
            <div class='card-body'>
                <div class='table-responsive'>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        // Sort by date (newest first)
        usort($existing_backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        foreach ($existing_backups as $backup) {
            $filename = basename($backup);
            $size = number_format(filesize($backup) / 1024 / 1024, 2);
            $date = date('Y-m-d H:i:s', filemtime($backup));
            
            echo "<tr>
                <td><code>$filename</code></td>
                <td>{$size} MB</td>
                <td>$date</td>
                <td>
                    <a href='backups/$filename' class='btn btn-sm btn-success' download>
                        <i class='fas fa-download'></i> Download
                    </a>
                </td>
            </tr>";
        }
        
        echo "</tbody>
                    </table>
                </div>
            </div>
        </div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>
        <h5><i class='fas fa-times-circle'></i> Backup Failed!</h5>
        <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        <p>Please check your database connection settings.</p>
    </div>";
}

echo "<div class='card backup-card'>
    <div class='card-header bg-warning text-white'>
        <h5><i class='fas fa-info-circle'></i> Backup Information</h5>
    </div>
    <div class='card-body'>
        <div class='row'>
            <div class='col-md-6'>
                <h6>✅ What's Included:</h6>
                <ul>
                    <li>All 120+ database tables</li>
                    <li>Table structures (CREATE statements)</li>
                    <li>All data (INSERT statements)</li>
                    <li>Foreign key relationships</li>
                </ul>
            </div>
            <div class='col-md-6'>
                <h6>💡 Usage Tips:</h6>
                <ul>
                    <li>Download backups for safekeeping</li>
                    <li>Create backups before major changes</li>
                    <li>Test restore process periodically</li>
                    <li>Keep multiple backup versions</li>
                </ul>
            </div>
        </div>
    </div>
</div>";

echo "<div class='text-center mt-4'>
    <a href='admin/' class='btn btn-primary me-2'>
        <i class='fas fa-tachometer-alt'></i> Back to Admin
    </a>
    <a href='final_system_showcase.php' class='btn btn-success'>
        <i class='fas fa-home'></i> System Dashboard
    </a>
</div>";

echo "</div>
</body>
</html>";
?>