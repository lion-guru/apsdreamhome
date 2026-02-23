<?php
/**
 * Fixed Employee System Setup - APS Dream Homes
 * Creates necessary tables and default data for the employee management system
 */

require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/views/layouts/config.php';
use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Employee System Setup - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .log-entry { padding: 10px; margin: 5px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #cce5ff; color: #004085; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Employee System Setup</h1>
";

function log_message($message, $type = 'info') {
    echo "<div class='log-entry $type'>$message</div>";
}

// 1. Create Employees Table
try {
    $sql = "CREATE TABLE IF NOT EXISTS employees (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        department VARCHAR(50),
        role ENUM('employee', 'manager', 'supervisor', 'executive') DEFAULT 'employee',
        status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        created_by VARCHAR(100),
        updated_by VARCHAR(100),
        deleted_at TIMESTAMP NULL,
        deleted_by VARCHAR(100),
        last_login TIMESTAMP NULL,
        password_reset_at TIMESTAMP NULL,
        password_reset_by VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        notes TEXT,
        INDEX (email),
        INDEX (status),
        INDEX (department),
        INDEX (role)
    )";
    $conn->exec($sql);
    log_message("✅ Employees table created/verified successfully", "success");
} catch (PDOException $e) {
    log_message("❌ Error creating employees table: " . $e->getMessage(), "error");
}

// 2. Create Employee Tasks Table
try {
    $sql = "CREATE TABLE IF NOT EXISTS employee_tasks (
        id INT PRIMARY KEY AUTO_INCREMENT,
        employee_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
        due_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        created_by VARCHAR(100),
        completed_at TIMESTAMP NULL,
        completed_by VARCHAR(100),
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        INDEX (employee_id),
        INDEX (status),
        INDEX (priority)
    )";
    $conn->exec($sql);
    log_message("✅ Employee Tasks table created/verified successfully", "success");
} catch (PDOException $e) {
    log_message("❌ Error creating employee_tasks table: " . $e->getMessage(), "error");
}

// 3. Create Employee Activities Table
try {
    $sql = "CREATE TABLE IF NOT EXISTS employee_activities (
        id INT PRIMARY KEY AUTO_INCREMENT,
        employee_id INT NOT NULL,
        activity TEXT NOT NULL,
        activity_type ENUM('login', 'logout', 'task_created', 'task_completed', 'profile_update', 'password_change') DEFAULT 'login',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        user_agent TEXT,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        INDEX (employee_id),
        INDEX (activity_type)
    )";
    $conn->exec($sql);
    log_message("✅ Employee Activities table created/verified successfully", "success");
} catch (PDOException $e) {
    log_message("❌ Error creating employee_activities table: " . $e->getMessage(), "error");
}

// 4. Create Default Admin User (if not exists)
try {
    // Check if admin table exists first
    $stmt = $conn->query("SHOW TABLES LIKE 'admin'");
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admin");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin (username, email, password, role, status) VALUES 
                    ('admin', 'admin@apsdreamhome.com', :password, 'super_admin', 'active')";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['password' => $password]);
            log_message("✅ Default admin user created (admin / admin123)", "success");
        } else {
            log_message("ℹ️ Admin users already exist", "info");
        }
    } else {
        log_message("⚠️ Admin table does not exist, skipping admin creation", "info");
    }
} catch (PDOException $e) {
    log_message("❌ Error checking/creating admin user: " . $e->getMessage(), "error");
}

// 5. Create Sample Employee (if no employees exist)
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM employees");
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('employee123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO employees (name, email, password, department, role, status) VALUES 
                ('Test Employee', 'employee@apsdreamhome.com', :password, 'Sales', 'employee', 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['password' => $password]);
        log_message("✅ Sample employee created (employee@apsdreamhome.com / employee123)", "success");
    } else {
        log_message("ℹ️ Employees already exist", "info");
    }
} catch (PDOException $e) {
    log_message("❌ Error creating sample employee: " . $e->getMessage(), "error");
}

echo "
        <div class='mt-4'>
            <a href='tools/diagnostics/production_checklist.php' class='btn btn-primary'>Run Production Checklist</a>
        </div>
    </div>
</body>
</html>";
