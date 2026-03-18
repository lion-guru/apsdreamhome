<?php
// Include the autoloader
require_once 'app/Core/Autoloader.php';
require_once 'config/bootstrap.php';

// Register autoloader
App\Core\Autoloader::getInstance()->register();

use App\Core\Database;

try {
    $db = Database::getInstance();

    echo "=== DATABASE CONNECTION TEST ===\n";
    echo "Database connection: SUCCESS\n\n";

    echo "=== TABLES IN DATABASE ===\n";
    $tables = $db->fetchAll('SHOW TABLES');

    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "- $tableName\n";
    }

    echo "\n=== CHECKING ADMINS TABLE ===\n";
    $adminsTableExists = false;
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        if ($tableName === 'admins') {
            $adminsTableExists = true;
            break;
        }
    }

    if ($adminsTableExists) {
        echo "Admins table: EXISTS\n";

        // Check if there are any admin users
        $adminCount = $db->fetch("SELECT COUNT(*) as count FROM admins");
        echo "Admin users count: " . $adminCount['count'] . "\n";

        if ($adminCount['count'] > 0) {
            echo "Existing admin users:\n";
            $admins = $db->fetchAll("SELECT id, username, email, role, status FROM admins");
            foreach ($admins as $admin) {
                echo "- ID: {$admin['id']}, Username: {$admin['username']}, Email: {$admin['email']}, Role: {$admin['role']}, Status: {$admin['status']}\n";
            }
        } else {
            echo "No admin users found. Creating default admin...\n";

            // Create default admin user
            $defaultAdmin = [
                'username' => 'admin',
                'email' => 'admin@apsdreamhome.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'super_admin',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertResult = $db->insert('admins', $defaultAdmin);
            if ($insertResult) {
                echo "Default admin created successfully!\n";
                echo "Username: admin\n";
                echo "Password: admin123\n";
                echo "Role: super_admin\n";
            } else {
                echo "Failed to create default admin\n";
            }
        }
    } else {
        echo "Admins table: NOT EXISTS\n";
        echo "Creating admins table...\n";

        $createTableSQL = "
        CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'admin',
            status VARCHAR(20) DEFAULT 'active',
            last_login DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        $db->exec($createTableSQL);
        echo "Admins table created successfully!\n";

        // Create default admin user
        $defaultAdmin = [
            'username' => 'admin',
            'email' => 'admin@apsdreamhome.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'super_admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $insertResult = $db->insert('admins', $defaultAdmin);
        if ($insertResult) {
            echo "Default admin created successfully!\n";
            echo "Username: admin\n";
            echo "Password: admin123\n";
            echo "Role: super_admin\n";
        } else {
            echo "Failed to create default admin\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
