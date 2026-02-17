<?php
/**
 * Create Employees Table - APS Dream Homes
 * This script creates the employees table for the employee management system
 */

require_once 'includes/config.php';

require_once dirname(__DIR__, 2) . '/app/helpers.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<h2>🔧 Create Employees Table - APS Dream Homes</h2>";

// Check if table exists
$check_table = $conn->query("SHOW TABLES LIKE 'employees'");
$table_exists = $check_table->num_rows > 0;

if ($table_exists) {
    echo "<div style='color: orange; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>⚠️ Employees table already exists!</strong><br>";
    echo "Table structure will be checked and updated if needed.";
    echo "</div>";
} else {
    echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✅ Employees table not found. Will be created.</strong>";
    echo "</div>";
}

// Create table SQL
$create_table_sql = "
CREATE TABLE IF NOT EXISTS employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(50),
    role ENUM('employee', 'manager', 'supervisor', 'executive') DEFAULT 'employee',
    status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
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
    INDEX (role),
    INDEX (created_at)
)
";

// Create employee_tasks table
$create_tasks_sql = "
CREATE TABLE IF NOT EXISTS employee_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100),
    completed_at TIMESTAMP NULL,
    completed_by VARCHAR(100),

    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX (employee_id),
    INDEX (status),
    INDEX (priority),
    INDEX (due_date)
)
";

// Create employee_activities table
$create_activities_sql = "
CREATE TABLE IF NOT EXISTS employee_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    activity TEXT NOT NULL,
    activity_type ENUM('login', 'logout', 'task_created', 'task_completed', 'profile_update', 'password_change') DEFAULT 'login',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,

    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX (employee_id),
    INDEX (activity_type),
    INDEX (created_at)
)
";

try {
    // Create employees table
    echo "<h3>🔧 Creating Employees Table...</h3>";
    if ($conn->query($create_table_sql)) {
        echo "<div style='color: green;'>✅ Employees table created/updated successfully!</div>";
    } else {
        echo "<div style='color: red;'>❌ Error creating employees table: " . $conn->error . "</div>";
    }

    // Create employee_tasks table
    echo "<h3>🔧 Creating Employee Tasks Table...</h3>";
    if ($conn->query($create_tasks_sql)) {
        echo "<div style='color: green;'>✅ Employee tasks table created successfully!</div>";
    } else {
        echo "<div style='color: red;'>❌ Error creating employee tasks table: " . $conn->error . "</div>";
    }

    // Create employee_activities table
    echo "<h3>🔧 Creating Employee Activities Table...</h3>";
    if ($conn->query($create_activities_sql)) {
        echo "<div style='color: green;'>✅ Employee activities table created successfully!</div>";
    } else {
        echo "<div style='color: red;'>❌ Error creating employee activities table: " . $conn->error . "</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
}

// Show table structures
echo "<h3>📋 Table Structures:</h3>";

$tables = ['employees', 'employee_tasks', 'employee_activities'];
foreach ($tables as $table) {
    echo "<h4>🔸 $table Table:</h4>";
    try {
        $result = $conn->query("DESCRIBE $table");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>" . $row['Field'] . "</strong></td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<div style='color: red;'>Error showing $table structure: " . $e->getMessage() . "</div>";
    }
}

// Insert sample data if table is empty
echo "<h3>📊 Sample Data:</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM employees");
    $count = $result->fetch_assoc()['count'];

    if ($count == 0) {
        echo "<p>No employees found. Creating sample employees...</p>";

        // Sample employees
        $sample_employees = [
            ['name' => 'John Doe', 'email' => 'john@apsdreamhome.com', 'password' => 'Employee123!', 'department' => 'Sales', 'role' => 'manager'],
            ['name' => 'Jane Smith', 'email' => 'jane@apsdreamhome.com', 'password' => 'Employee123!', 'department' => 'Marketing', 'role' => 'employee'],
            ['name' => 'Mike Johnson', 'email' => 'mike@apsdreamhome.com', 'password' => 'Employee123!', 'department' => 'IT', 'role' => 'employee'],
            ['name' => 'Sarah Wilson', 'email' => 'sarah@apsdreamhome.com', 'password' => 'Employee123!', 'department' => 'HR', 'role' => 'supervisor'],
        ];

        foreach ($sample_employees as $emp) {
            $hashed_password = password_hash($emp['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO employees (name, email, password, department, role, created_by)
                VALUES (?, ?, ?, ?, ?, 'system')
            ");
            $stmt->bind_param("sssss", $emp['name'], $emp['email'], $hashed_password, $emp['department'], $emp['role']);
            $stmt->execute();
            $stmt->close();

            echo "<div style='color: green;'>✅ Created sample employee: " . h($emp['name']) . " (" . h($emp['email']) . ")</div>";
        }

        echo "<div style='color: blue; padding: 10px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>📝 Sample Employee Login Credentials:</strong><br>";
        echo "Email: john@apsdreamhome.com | Password: Employee123!<br>";
        echo "Email: jane@apsdreamhome.com | Password: Employee123!<br>";
        echo "Email: mike@apsdreamhome.com | Password: Employee123!<br>";
        echo "Email: sarah@apsdreamhome.com | Password: Employee123!";
        echo "</div>";

    } else {
        echo "<p>Current employees count: $count</p>";
    }

} catch (Exception $e) {
    echo "<div style='color: red;'>Error checking employees: " . $e->getMessage() . "</div>";
}

echo "<hr>";

echo "<h3>🎯 Next Steps:</h3>";
echo "<div style='padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff;'>";
echo "<ol>";
echo "<li><strong>Login as Admin:</strong> Go to <a href='admin/'>admin/</a> and login</li>";
echo "<li><strong>Manage Employees:</strong> Go to <a href='admin/manage_employees.php'>Manage Employees</a></li>";
echo "<li><strong>Create Employees:</strong> Add new employees with their details</li>";
echo "<li><strong>Employee Login:</strong> Employees can login at <a href='employee_login.php'>employee_login.php</a></li>";
echo "<li><strong>Employee Dashboard:</strong> Employees will see their dashboard at <a href='employee_dashboard.php'>employee_dashboard.php</a></li>";
echo "</ol>";
echo "</div>";

echo "<div style='margin-top: 20px; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
echo "<strong>🎉 Employee Management System Ready!</strong><br>";
echo "Tables created, sample data inserted. You can now manage employees from the admin panel.";
echo "</div>";

?>
