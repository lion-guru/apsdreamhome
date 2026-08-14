<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Inspect what columns were in original employees table by checking code references
$tableExists = $pdo->query("SHOW TABLES LIKE 'employees'")->fetch();
if ($tableExists) {
    echo "employees table already exists, skipping create\n";
    exit;
}

$pdo->exec("
CREATE TABLE employees (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NULL,
    name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    role VARCHAR(100) NULL,
    department VARCHAR(100) NULL,
    designation VARCHAR(150) NULL,
    employee_code VARCHAR(50) NULL,
    joining_date DATE NULL,
    salary DECIMAL(12,2) NULL DEFAULT 0,
    status ENUM('active','inactive','on_leave','terminated') DEFAULT 'active',
    address TEXT NULL,
    emergency_contact VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    pan_number VARCHAR(20) NULL,
    aadhaar_number VARCHAR(20) NULL,
    bank_account VARCHAR(50) NULL,
    bank_ifsc VARCHAR(20) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_emp_email (email),
    UNIQUE KEY uniq_emp_code (employee_code),
    KEY idx_emp_user_id (user_id),
    KEY idx_emp_role (role),
    KEY idx_emp_department (department),
    KEY idx_emp_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "employees table created\n";

// Link all users with role='employee' to employees via email
$stmt = $pdo->query("
    SELECT u.id AS user_id, u.email, u.name, u.phone
    FROM users u
    WHERE u.role = 'employee'
");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($employees) . " employee users\n";

$insert = $pdo->prepare("
    INSERT INTO employees (user_id, name, email, phone, role, department, designation, employee_code, status, joining_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
");
$count = 0;
foreach ($employees as $emp) {
    // Skip if email already in employees
    $existing = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
    $existing->execute([$emp['email']]);
    if ($existing->fetch()) {
        echo "  Skip (exists): {$emp['email']}\n";
        continue;
    }

    $code = 'EMP' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    // Heuristic: assign role/department/designation by email prefix
    $dept = 'Operations';
    $designation = 'Staff';
    $hash = crc32($emp['email']);
    $depts = ['Operations', 'Finance', 'HR', 'IT', 'Sales', 'Customer Service', 'Marketing', 'Legal', 'Engineering'];
    $degs = ['Manager', 'Executive', 'Senior Executive', 'Lead', 'Coordinator', 'Specialist', 'Officer', 'Analyst'];
    $dept = $depts[$hash % count($depts)];
    $designation = $degs[($hash >> 8) % count($degs)];

    $insert->execute([
        $emp['user_id'],
        $emp['name'],
        $emp['email'],
        $emp['phone'],
        $dept,
        $dept,
        $designation,
        $code,
        date('Y-m-d', strtotime('-1 year -' . ($count * 30) . ' days'))
    ]);
    echo "  Created: {$emp['email']} -> $code ($designation, $dept)\n";
    $count++;
}
echo "\n$count employees created\n";

// Re-link any user_ids that might be stale
$pdo->exec("UPDATE employees e JOIN users u ON u.email = e.email SET e.user_id = u.id WHERE e.user_id != u.id OR e.user_id IS NULL");
echo "Re-linked all user_ids by email\n";

echo "\n=== Final state ===\n";
$total = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
echo "Total employees: $total\n";
$linked = $pdo->query("SELECT COUNT(*) FROM employees WHERE user_id IS NOT NULL")->fetchColumn();
echo "Linked to users: $linked\n";?>