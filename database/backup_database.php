<?php
/**
 * Database Backup and Export Script for APS Dream Home
 * This script creates a complete backup of your current database
 * including structure and data for disaster recovery
 * 
 * Author: AI Assistant for Abhay Singh
 * Date: 2025-09-25
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once '../includes/config.php';

// Backup filename with timestamp
$backup_filename = 'apsdreamhome_backup_' . date('Y_m_d_H_i_s') . '.sql';
$backup_path = __DIR__ . '/backups/' . $backup_filename;

// Create backups directory if it doesn't exist
if (!is_dir(__DIR__ . '/backups/')) {
    mkdir(__DIR__ . '/backups/', 0755, true);
}

echo "<h1>APS Dream Home - Database Backup</h1>";
echo "<p>Creating backup: <strong>$backup_filename</strong></p>";

// Start backup content
$backup_content = "-- APS Dream Home Database Backup\n";
$backup_content .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
$backup_content .= "-- Database: apsdreamhomefinal\n\n";
$backup_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$backup_content .= "START TRANSACTION;\n";
$backup_content .= "SET time_zone = \"+00:00\";\n\n";

try {
    // Get all tables
    $tables_result = $conn->query("SHOW TABLES");
    $tables = [];
    
    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<p>Found " . count($tables) . " tables to backup...</p>";
    
    // Backup each table
    foreach ($tables as $table) {
        echo "<p>Backing up table: <strong>$table</strong></p>";
        
        // Get table structure
        $create_result = $conn->query("SHOW CREATE TABLE `$table`");
        $create_row = $create_result->fetch_array();
        
        $backup_content .= "\n-- --------------------------------------------------------\n";
        $backup_content .= "-- Table structure for table `$table`\n";
        $backup_content .= "-- --------------------------------------------------------\n\n";
        $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
        $backup_content .= $create_row[1] . ";\n\n";
        
        // Get table data
        $data_result = $conn->query("SELECT * FROM `$table`");
        
        if ($data_result && $data_result->num_rows > 0) {
            $backup_content .= "-- Dumping data for table `$table`\n\n";
            
            // Get column names
            $columns_result = $conn->query("SHOW COLUMNS FROM `$table`");
            $columns = [];
            while ($col = $columns_result->fetch_assoc()) {
                $columns[] = "`" . $col['Field'] . "`";
            }
            
            $backup_content .= "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES\n";
            
            $first_row = true;
            while ($row = $data_result->fetch_array(MYSQLI_NUM)) {
                if (!$first_row) {
                    $backup_content .= ",\n";
                }
                
                $backup_content .= "(";
                $first_value = true;
                foreach ($row as $value) {
                    if (!$first_value) {
                        $backup_content .= ", ";
                    }
                    
                    if ($value === null) {
                        $backup_content .= "NULL";
                    } else {
                        $backup_content .= "'" . $conn->real_escape_string($value) . "'";
                    }
                    $first_value = false;
                }
                $backup_content .= ")";
                $first_row = false;
            }
            $backup_content .= ";\n\n";
        } else {
            $backup_content .= "-- No data for table `$table`\n\n";
        }
    }
    
    $backup_content .= "COMMIT;\n";
    $backup_content .= "-- Backup completed successfully\n";
    
    // Write backup to file
    if (file_put_contents($backup_path, $backup_content)) {
        $file_size = filesize($backup_path);
        $file_size_mb = round($file_size / 1024 / 1024, 2);
        
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h2>✅ Backup Created Successfully!</h2>";
        echo "<p><strong>File:</strong> $backup_filename</p>";
        echo "<p><strong>Size:</strong> $file_size_mb MB</p>";
        echo "<p><strong>Tables backed up:</strong> " . count($tables) . "</p>";
        echo "<p><strong>Path:</strong> $backup_path</p>";
        echo "</div>";
        
        // Create a download link
        echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>📥 Download Backup</h3>";
        echo "<p><a href='backups/$backup_filename' download style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Download Backup File</a></p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h2>❌ Backup Failed!</h2>";
        echo "<p>Could not write backup file to: $backup_path</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2>❌ Backup Error!</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// List existing backups
echo "<h2>📁 Existing Backups</h2>";
$backup_dir = __DIR__ . '/backups/';
if (is_dir($backup_dir)) {
    $backup_files = glob($backup_dir . '*.sql');
    if (!empty($backup_files)) {
        echo "<table style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f1f1f1;'>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Filename</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Size</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Created</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Actions</th>";
        echo "</tr>";
        
        foreach ($backup_files as $backup_file) {
            $filename = basename($backup_file);
            $file_size = filesize($backup_file);
            $file_size_mb = round($file_size / 1024 / 1024, 2);
            $created_time = date('Y-m-d H:i:s', filemtime($backup_file));
            
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$filename</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$file_size_mb} MB</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>$created_time</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>";
            echo "<a href='backups/$filename' download style='color: #007bff; text-decoration: none;'>Download</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No backup files found.</p>";
    }
} else {
    echo "<p>Backup directory not found.</p>";
}

// Instructions for restoration
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔧 How to Restore from Backup</h3>";
echo "<ol>";
echo "<li>Download the backup file you want to restore</li>";
echo "<li>Access phpMyAdmin or MySQL command line</li>";
echo "<li>Drop the existing database (if needed): <code>DROP DATABASE apsdreamhomefinal;</code></li>";
echo "<li>Create new database: <code>CREATE DATABASE apsdreamhomefinal;</code></li>";
echo "<li>Import the backup file: <code>mysql -u username -p apsdreamhomefinal < backup_file.sql</code></li>";
echo "<li>Or use phpMyAdmin's Import feature</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
h2 { color: #555; margin-top: 30px; }
p { margin: 5px 0; }
table { margin-top: 10px; }
code { background: #f1f1f1; padding: 2px 4px; border-radius: 3px; }
</style>