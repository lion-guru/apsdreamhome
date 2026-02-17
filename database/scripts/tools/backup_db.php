<?php
/**
 * Database Backup Tool
 * 
 * This script creates a full backup of the database (structure + data)
 * and offers it as a download.
 */

// Load configuration
require_once __DIR__ . '/../../../app/config/database.php';

// Get database credentials
$config = require __DIR__ . '/../../../app/config/database.php';
$host = $config['connections']['mysql']['host'] ?? 'localhost';
$user = $config['connections']['mysql']['username'] ?? 'root';
$pass = $config['connections']['mysql']['password'] ?? '';
$name = $config['connections']['mysql']['database'] ?? 'apsdreamhome';

// Set filename
$filename = 'backup_' . $name . '_' . date('Y-m-d_H-i-s') . '.sql';

// Check for mysqldump in common XAMPP locations
$mysqldump_paths = [
    'mysqldump', // System path
    'C:/xampp/mysql/bin/mysqldump.exe',
    'D:/xampp/mysql/bin/mysqldump.exe',
    '/usr/bin/mysqldump',
    '/usr/local/bin/mysqldump'
];

$mysqldump = null;
foreach ($mysqldump_paths as $path) {
    if (file_exists($path) || (strpos($path, '/') === false && exec("where $path"))) {
        $mysqldump = $path;
        break;
    }
}

// Headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

if ($mysqldump) {
    // Use mysqldump if available
    $cmd = "\"$mysqldump\" --host=$host --user=$user";
    if (!empty($pass)) {
        $cmd .= " --password=$pass";
    }
    $cmd .= " $name";
    
    passthru($cmd);
} else {
    // PHP Fallback (Simplified)
    echo "-- Backup created by PHP Fallback Script\n";
    echo "-- Date: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: $name\n\n";
    
    $mysqli = new mysqli($host, $user, $pass, $name);
    if ($mysqli->connect_error) {
        die("-- Connection failed: " . $mysqli->connect_error);
    }
    
    $mysqli->set_charset("utf8mb4");
    
    // Get tables
    $tables = [];
    $result = $mysqli->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    foreach ($tables as $table) {
        echo "-- Table structure for table `$table`\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        $row = $mysqli->query("SHOW CREATE TABLE `$table`")->fetch_row();
        echo $row[1] . ";\n\n";
        
        echo "-- Dumping data for table `$table`\n";
        $data = $mysqli->query("SELECT * FROM `$table`");
        while ($row = $data->fetch_assoc()) {
            $keys = array_keys($row);
            $values = array_map(function($value) use ($mysqli) {
                if ($value === null) return "NULL";
                return "'" . $mysqli->real_escape_string($value) . "'";
            }, array_values($row));
            
            echo "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
        echo "\n";
    }
    
    $mysqli->close();
}
exit;
