<?php
session_start();
require_once '../config.php';
// require_role('Admin'); // Temporarily disabled for testing

// Database configuration
$host = 'localhost';
$database = 'apsdreamhomefinal';
$username = 'root';
$password = '';

$date = date('Y-m-d_H-i-s');
$backup_dir = __DIR__ . '/../backups';
if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);

$backup_file = $backup_dir . "/apsdreamhome_backup_$date.sql";
$backup_success = false;
$backup_size = 0;

try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create backup using PHP (doesn't require mysqldump)
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
    
    // Backup each table
    foreach ($tables as $table) {
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
    }
    
    $backup_content .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Write backup file
    if (file_put_contents($backup_file, $backup_content)) {
        $backup_success = true;
        $backup_size = filesize($backup_file);
        $msg = "✅ Backup successful: apsdreamhome_backup_$date.sql (" . number_format($backup_size / 1024 / 1024, 2) . " MB)";
    } else {
        $msg = "❌ Failed to write backup file.";
    }
    
} catch (Exception $e) {
    $msg = "❌ Backup failed: " . $e->getMessage();
}
?><!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Backup - APS Dream Home</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body>
<div class='container py-4'>
    <div class='text-center mb-4'>
        <h2><i class='fas fa-database'></i> Database Backup</h2>
        <p class='text-muted'>APS Dream Home System Backup</p>
    </div>
    
    <?php if ($backup_success): ?>
        <div class='alert alert-success'>
            <h5><i class='fas fa-check-circle'></i> <?= $msg ?></h5>
            <p class='mb-2'><strong>Tables backed up:</strong> <?= count($tables) ?></p>
            <p class='mb-0'><strong>File location:</strong> <?= str_replace(__DIR__ . '/../', '', $backup_file) ?></p>
        </div>
        
        <div class='text-center mb-4'>
            <a href='../backups/apsdreamhome_backup_<?= $date ?>.sql' class='btn btn-success btn-lg me-2' download>
                <i class='fas fa-download'></i> Download Backup
            </a>
            <button onclick='window.location.reload()' class='btn btn-primary btn-lg'>
                <i class='fas fa-redo'></i> Create New Backup
            </button>
        </div>
    <?php else: ?>
        <div class='alert alert-danger'>
            <h5><i class='fas fa-times-circle'></i> <?= $msg ?></h5>
        </div>
        
        <div class='text-center'>
            <button onclick='window.location.reload()' class='btn btn-warning btn-lg'>
                <i class='fas fa-redo'></i> Try Again
            </button>
        </div>
    <?php endif; ?>
    
    <?php 
    // Show existing backups
    $existing_backups = glob($backup_dir . '/*.sql');
    if (count($existing_backups) > 0): 
    ?>
    <div class='card mt-4'>
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
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Sort by date (newest first)
                        usort($existing_backups, function($a, $b) {
                            return filemtime($b) - filemtime($a);
                        });
                        
                        foreach ($existing_backups as $backup): 
                            $filename = basename($backup);
                            $size = number_format(filesize($backup) / 1024 / 1024, 2);
                            $backup_date = date('Y-m-d H:i:s', filemtime($backup));
                        ?>
                        <tr>
                            <td><code><?= $filename ?></code></td>
                            <td><?= $size ?> MB</td>
                            <td><?= $backup_date ?></td>
                            <td>
                                <a href='../backups/<?= $filename ?>' class='btn btn-sm btn-success' download>
                                    <i class='fas fa-download'></i> Download
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class='text-center mt-4'>
        <a href='dashboard.php' class='btn btn-secondary'>
            <i class='fas fa-arrow-left'></i> Back to Dashboard
        </a>
    </div>
</div>
</body>
</html>
