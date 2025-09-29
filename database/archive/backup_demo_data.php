<?php
/**
 * APS Dream Home Database Backup Script
 * 
 * This script creates a backup of the demo data in the database
 * and provides options to restore it if needed.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Start output buffering
ob_start();

// HTML header
echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home Database Backup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #2c3e50; }
        h2 { color: #3498db; margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .section { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin: 10px 5px 10px 0;
        }
        .btn-warning {
            background-color: #f39c12;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .backup-list {
            list-style: none;
            padding: 0;
        }
        .backup-item {
            background-color: white;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .backup-info {
            flex-grow: 1;
        }
        .backup-actions {
            display: flex;
        }
    </style>
</head>
<body>
    <h1>APS Dream Home Database Backup</h1>";

// Function to get backup directory
function getBackupDir() {
    $dir = __DIR__ . '/backups';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir;
}

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo "<div class='section error'>
        <h2>Error</h2>
        <p>Connection failed: " . $conn->connect_error . "</p>
        <a href='index.php' class='btn'>Return to Database Management Hub</a>
    </div>";
    exit;
}

// Check if action is specified
$action = isset($_GET['action']) ? $_GET['action'] : '';
$backupFile = isset($_GET['file']) ? $_GET['file'] : '';

// Validate backup file to prevent directory traversal
if ($backupFile && (strpos($backupFile, '/') !== false || strpos($backupFile, '\\') !== false)) {
    echo "<div class='section error'>
        <h2>Error</h2>
        <p>Invalid backup file specified.</p>
        <a href='backup_demo_data.php' class='btn'>Back to Backup Management</a>
    </div>";
    exit;
}

// Process actions
$message = '';
$messageClass = '';

if ($action === 'backup') {
    // Create backup
    $backupDir = getBackupDir();
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . "/apsdreamhome_demo_" . $timestamp . ".sql";
    
    // Get all tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    }
    
    // Start backup file
    $output = "-- APS Dream Home Demo Data Backup\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- Database: " . $dbname . "\n\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    // Process each table
    foreach ($tables as $table) {
        // Get create table statement
        $result = $conn->query("SHOW CREATE TABLE `$" . $table . "`");
        if ($result) {
            $row = $result->fetch_row();
            $output .= $row[1] . ";\n\n";
            
            // Get table data
            $result = $conn->query("SELECT * FROM `$" . $SELECT * FROM . "`");
            if ($result && $result->num_rows > 0) {
                $output .= "-- Dumping data for table `$table`\n";
                $output .= "INSERT INTO `$table` VALUES\n";
                
                $rowCount = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($rowCount > 0) {
                        $output .= ",\n";
                    }
                    
                    $output .= "(";
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . $value. "'";
                        }
                    }
                    $output .= implode(", ", $values);
                    $output .= ")";
                    
                    $rowCount++;
                }
                
                $output .= ";\n\n";
            }
        }
    }
    
    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Write backup file
    if (file_put_contents($backupFile, $output)) {
        $message = "Backup created successfully: " . basename($backupFile);
        $messageClass = "success";
    } else {
        $message = "Error creating backup file";
        $messageClass = "error";
    }
} elseif ($action === 'restore' && $backupFile) {
    // Restore backup
    $backupPath = getBackupDir() . '/' . $backupFile;
    if (file_exists($backupPath)) {
        // Read backup file
        $sql = file_get_contents($backupPath);
        
        // Execute SQL
        if ($conn->multi_query($sql)) {
            $message = "Backup restored successfully";
            $messageClass = "success";
            
            // Clear results
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        } else {
            $message = "Error restoring backup: " . $conn->error;
            $messageClass = "error";
        }
    } else {
        $message = "Backup file not found";
        $messageClass = "error";
    }
} elseif ($action === 'download' && $backupFile) {
    // Download backup
    $backupPath = getBackupDir() . '/' . $backupFile;
    if (file_exists($backupPath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($backupPath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backupPath));
        ob_clean();
        flush();
        readfile($backupPath);
        exit;
    } else {
        $message = "Backup file not found";
        $messageClass = "error";
    }
} elseif ($action === 'delete' && $backupFile) {
    // Delete backup
    $backupPath = getBackupDir() . '/' . $backupFile;
    if (file_exists($backupPath)) {
        if (unlink($backupPath)) {
            $message = "Backup deleted successfully";
            $messageClass = "success";
        } else {
            $message = "Error deleting backup file";
            $messageClass = "error";
        }
    } else {
        $message = "Backup file not found";
        $messageClass = "error";
    }
}

// Display message if any
if ($message) {
    echo "<div class='section $messageClass'>
        <h2>" . ($messageClass === 'success' ? 'Success' : 'Error') . "</h2>
        <p>$message</p>
    </div>";
}

// Display database info
echo "<div class='section'>
    <h2>Database Information</h2>
    <p><strong>Database:</strong> $dbname</p>";

// Get table counts
$tableCount = 0;
$recordCount = 0;
$result = $conn->query("SHOW TABLES");
if ($result) {
    $tableCount = $result->num_rows;
    
    while ($row = $result->fetch_row()) {
        $table = $row[0];
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `$" . $table . "`");
        if ($countResult) {
            $countRow = $countResult->fetch_assoc();
            $recordCount += $countRow['count'];
        }
    }
}

echo "<p><strong>Tables:</strong> $tableCount</p>
    <p><strong>Total Records:</strong> $recordCount</p>
</div>";

// Display backup actions
echo "<div class='section'>
    <h2>Backup Actions</h2>
    <a href='backup_demo_data.php?action=backup' class='btn'>Create New Backup</a>
    <a href='index.php' class='btn'>Return to Database Management Hub</a>
</div>";

// Display existing backups
echo "<div class='section'>
    <h2>Existing Backups</h2>";

$backupDir = getBackupDir();
$backups = glob($backupDir . "/*.sql");

if (empty($backups)) {
    echo "<p>No backups found.</p>";
} else {
    echo "<ul class='backup-list'>";
    
    foreach ($backups as $backup) {
        $filename = basename($backup);
        $filesize = round(filesize($backup) / 1024, 2);
        $timestamp = filemtime($backup);
        
        echo "<li class='backup-item'>
            <div class='backup-info'>
                <strong>$filename</strong><br>
                Size: $filesize KB | Date: " . date('Y-m-d H:i:s', $timestamp) . "
            </div>
            <div class='backup-actions'>
                <a href='backup_demo_data.php?action=download&file=$filename' class='btn'>Download</a>
                <a href='backup_demo_data.php?action=restore&file=$filename' class='btn btn-warning' onclick='return confirm(\"Are you sure you want to restore this backup? Current data will be overwritten.\")'>Restore</a>
                <a href='backup_demo_data.php?action=delete&file=$filename' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this backup?\")'>Delete</a>
            </div>
        </li>";
    }
    
    echo "</ul>";
}

echo "</div>

<div class='section'>
    <h2>Backup Instructions</h2>
    <p>Use this tool to create and manage backups of your demo data:</p>
    <ol>
        <li><strong>Create Backup:</strong> Click 'Create New Backup' to generate a SQL file with all your demo data.</li>
        <li><strong>Download:</strong> Download backups to keep them safe or transfer to another installation.</li>
        <li><strong>Restore:</strong> Restore a previous backup if your data becomes corrupted or you need to reset to a known state.</li>
        <li><strong>Delete:</strong> Remove old backups that are no longer needed.</li>
    </ol>
    <p><strong>Note:</strong> Restoring a backup will overwrite all current data in the database.</p>
</div>

</body>
</html>";

// Close connection
$conn->close();

ob_end_flush();
?>
