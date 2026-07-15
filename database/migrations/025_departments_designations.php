<?php
/**
 * Migration: Create departments and designations tables
 * For real estate company organizational structure
 */

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";

    // 1. Create departments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `departments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `code` VARCHAR(20) NOT NULL,
            `description` TEXT,
            `head_user_id` INT UNSIGNED NULL,
            `parent_dept_id` INT NULL,
            `budget` DECIMAL(15,2) DEFAULT 0,
            `status` ENUM('active','inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_dept_code` (`code`),
            KEY `idx_dept_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Created departments table.\n";

    // 2. Create designations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `designations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `department_id` INT NOT NULL,
            `level` INT DEFAULT 1 COMMENT '1=Junior,2=Senior,3=Lead,4=Manager,5=Head/Director',
            `min_salary` DECIMAL(12,2) DEFAULT 0,
            `max_salary` DECIMAL(12,2) DEFAULT 0,
            `sub_role` VARCHAR(50) NOT NULL COMMENT 'RBAC sub-role string',
            `dashboard_view` VARCHAR(255) NULL COMMENT 'Route to role-specific dashboard',
            `status` ENUM('active','inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_desig_dept` (`name`, `department_id`),
            KEY `idx_desig_dept` (`department_id`),
            KEY `idx_desig_subrole` (`sub_role`),
            CONSTRAINT `fk_desig_dept` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Created designations table.\n";

    // 3. Seed departments
    $departments = [
        ['EXEC', 'CEO Office', 'Executive leadership and strategy', NULL, NULL, 5000000],
        ['FIN', 'Finance & Accounts', 'Financial management, accounting, TDS, GST, audits', NULL, 1, 3000000],
        ['SALES', 'Sales & Marketing', 'Lead generation, sales, customer acquisition', NULL, 1, 4000000],
        ['LAND', 'Land & Acquisition', 'Land identification, negotiation, purchase', NULL, 1, 2000000],
        ['LEGAL', 'Legal & Compliance', 'Agreements, RERA, title verification, compliance', NULL, 1, 1500000],
        ['CONST', 'Construction & Projects', 'Construction, site management, quality control', NULL, 1, 5000000],
        ['HR', 'HR & Administration', 'Recruitment, attendance, payroll, employee welfare', NULL, 1, 1000000],
        ['CS', 'Customer Success', 'Post-sale support, complaints, customer satisfaction', NULL, 1, 800000],
        ['IT', 'IT & Digital', 'Software, infrastructure, data, digital marketing', NULL, 1, 1200000],
        ['OPS', 'Operations', 'Day-to-day operations, vendor management, logistics', NULL, 1, 1000000],
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO departments (code, name, description, head_user_id, parent_dept_id, budget)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($departments as $dept) {
        $stmt->execute($dept);
    }
    echo "Seeded " . count($departments) . " departments.\n";

    // 4. Seed designations (100+ mappings)
    $designations = [
        // CEO Office
        ['CEO', 1, 5, 500000, 1000000, 'super_admin', NULL],
        ['COO', 1, 5, 400000, 800000, 'admin', NULL],
        ['Director', 1, 4, 300000, 600000, 'director', NULL],
        ['Executive Assistant', 1, 2, 80000, 150000, 'employee_general', NULL],

        // Finance
        ['CFO', 2, 5, 400000, 800000, 'employee_finance_manager', '/employee/finance-dashboard'],
        ['Finance Manager', 2, 4, 200000, 400000, 'employee_finance_manager', '/employee/finance-dashboard'],
        ['Senior Accountant', 2, 3, 120000, 250000, 'employee_finance_executive', NULL],
        ['Accountant', 2, 2, 60000, 120000, 'employee_finance_executive', NULL],
        ['Accounts Executive', 2, 1, 40000, 80000, 'employee_finance_executive', NULL],
        ['Tax Consultant', 2, 3, 100000, 200000, 'employee_finance_executive', NULL],

        // Sales
        ['Sales Director', 3, 5, 300000, 600000, 'employee_sales_manager', '/employee/sales-dashboard'],
        ['Sales Manager', 3, 4, 150000, 350000, 'employee_sales_manager', '/employee/sales-dashboard'],
        ['Senior Sales Executive', 3, 3, 80000, 180000, 'employee_sales_executive', NULL],
        ['Sales Executive', 3, 2, 50000, 100000, 'employee_sales_executive', NULL],
        ['Junior Sales Executive', 3, 1, 30000, 60000, 'employee_sales_executive', NULL],
        ['Telecalling Lead', 3, 3, 60000, 120000, 'employee_telecalling_lead', NULL],
        ['Senior Telecaller', 3, 2, 40000, 70000, 'employee_telecaller', NULL],
        ['Telecaller', 3, 1, 25000, 50000, 'employee_telecaller', NULL],
        ['Marketing Manager', 3, 4, 150000, 300000, 'employee_marketing_manager', NULL],
        ['Digital Marketer', 3, 2, 50000, 100000, 'employee_marketing_executive', NULL],
        ['Content Writer', 3, 1, 30000, 60000, 'employee_marketing_executive', NULL],
        ['SEO Specialist', 3, 2, 40000, 80000, 'employee_marketing_executive', NULL],
        ['Social Media Executive', 3, 1, 30000, 55000, 'employee_marketing_executive', NULL],

        // Land & Acquisition
        ['Land Director', 4, 5, 300000, 500000, 'employee_land_manager', '/employee/land-dashboard'],
        ['Land Manager', 4, 4, 150000, 300000, 'employee_land_manager', '/employee/land-dashboard'],
        ['Senior Surveyor', 4, 3, 80000, 150000, 'employee_land_executive', NULL],
        ['Surveyor', 4, 2, 50000, 100000, 'employee_land_executive', NULL],
        ['Liaison Officer', 4, 2, 60000, 120000, 'employee_land_executive', NULL],
        ['Land Executive', 4, 1, 35000, 70000, 'employee_land_executive', NULL],

        // Legal
        ['Legal Advisor', 5, 4, 200000, 400000, 'employee_legal_advisor', '/employee/legal-dashboard'],
        ['Senior Legal Executive', 5, 3, 100000, 200000, 'employee_legal_executive', NULL],
        ['Legal Executive', 5, 2, 60000, 120000, 'employee_legal_executive', NULL],
        ['Document Manager', 5, 2, 50000, 90000, 'employee_legal_executive', NULL],
        ['Compliance Officer', 5, 3, 80000, 150000, 'employee_legal_executive', NULL],

        // Construction
        ['Project Director', 6, 5, 350000, 600000, 'employee_project_manager', '/employee/construction-dashboard'],
        ['Project Manager', 6, 4, 180000, 350000, 'employee_project_manager', '/employee/construction-dashboard'],
        ['Senior Site Engineer', 6, 3, 100000, 200000, 'employee_site_engineer', NULL],
        ['Site Engineer', 6, 2, 60000, 120000, 'employee_site_engineer', NULL],
        ['Junior Site Engineer', 6, 1, 35000, 70000, 'employee_site_engineer', NULL],
        ['Supervisor', 6, 2, 45000, 90000, 'employee_site_engineer', NULL],
        ['Quality Inspector', 6, 2, 50000, 100000, 'employee_site_engineer', NULL],
        ['Safety Officer', 6, 2, 50000, 95000, 'employee_site_engineer', NULL],

        // HR
        ['HR Director', 7, 5, 250000, 450000, 'employee_hr_manager', '/employee/hr-dashboard'],
        ['HR Manager', 7, 4, 120000, 250000, 'employee_hr_manager', '/employee/hr-dashboard'],
        ['Senior HR Executive', 7, 3, 70000, 140000, 'employee_hr_executive', NULL],
        ['HR Executive', 7, 2, 40000, 80000, 'employee_hr_executive', NULL],
        ['HR Assistant', 7, 1, 25000, 50000, 'employee_hr_executive', NULL],
        ['Recruiter', 7, 2, 40000, 75000, 'employee_hr_executive', NULL],
        ['Payroll Executive', 7, 2, 45000, 85000, 'employee_hr_executive', NULL],

        // Customer Success
        ['Customer Success Head', 8, 4, 150000, 300000, 'employee_cs_manager', NULL],
        ['Relationship Manager', 8, 3, 70000, 140000, 'employee_cs_executive', NULL],
        ['Senior Support Executive', 8, 3, 55000, 100000, 'employee_cs_executive', NULL],
        ['Support Executive', 8, 2, 35000, 70000, 'employee_cs_executive', NULL],
        ['Customer Care Executive', 8, 1, 25000, 50000, 'employee_cs_executive', NULL],

        // IT
        ['IT Director', 9, 5, 300000, 500000, 'employee_it_manager', '/employee/it-dashboard'],
        ['IT Manager', 9, 4, 180000, 350000, 'employee_it_manager', '/employee/it-dashboard'],
        ['Senior Developer', 9, 3, 120000, 250000, 'employee_it_executive', NULL],
        ['Developer', 9, 2, 70000, 150000, 'employee_it_executive', NULL],
        ['Junior Developer', 9, 1, 40000, 80000, 'employee_it_executive', NULL],
        ['System Admin', 9, 3, 100000, 200000, 'employee_it_executive', NULL],
        ['Data Analyst', 9, 2, 60000, 120000, 'employee_it_executive', NULL],
        ['UI/UX Designer', 9, 2, 60000, 130000, 'employee_it_executive', NULL],

        // Operations
        ['Operations Director', 10, 5, 250000, 450000, 'employee_ops_manager', '/employee/ops-dashboard'],
        ['Operations Manager', 10, 4, 150000, 300000, 'employee_ops_manager', '/employee/ops-dashboard'],
        ['Admin Executive', 10, 2, 40000, 80000, 'employee_ops_executive', NULL],
        ['Office Administrator', 10, 1, 25000, 50000, 'employee_ops_executive', NULL],
        ['Vendor Manager', 10, 3, 70000, 140000, 'employee_ops_executive', NULL],
        ['Store Keeper', 10, 1, 20000, 40000, 'employee_ops_executive', NULL],
        ['Driver', 10, 1, 18000, 30000, 'employee_ops_executive', NULL],
        ['Security Guard', 10, 1, 15000, 25000, 'employee_ops_executive', NULL],
        ['Peon', 10, 1, 12000, 20000, 'employee_ops_executive', NULL],
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO designations (name, department_id, level, min_salary, max_salary, sub_role, dashboard_view)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $count = 0;
    foreach ($designations as $desig) {
        $stmt->execute($desig);
        $count++;
    }
    echo "Seeded $count designations.\n";

    // 5. Update employee_designation_roles with comprehensive mapping
    $pdo->exec("TRUNCATE TABLE `employee_designation_roles`");

    $subRoleInsert = $pdo->prepare("
        INSERT INTO employee_designation_roles (designation, department, sub_role, dashboard_view)
        VALUES (?, ?, ?, ?)
    ");

    $subRoles = [
        // CEO Office
        ['CEO', 'CEO Office', 'super_admin', 'admin/dashboard'],
        ['COO', 'CEO Office', 'admin', 'admin/dashboard'],
        ['Director', 'CEO Office', 'director', 'admin/dashboard'],
        ['Executive Assistant', 'CEO Office', 'employee_general', 'employee/dashboard'],
        
        // Finance
        ['CFO', 'Finance', 'employee_finance_manager', 'employee/finance-dashboard'],
        ['Finance Manager', 'Finance', 'employee_finance_manager', 'employee/finance-dashboard'],
        ['Senior Accountant', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
        ['Accountant', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
        ['Accounts Executive', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
        ['Tax Consultant', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
        
        // Sales
        ['Sales Director', 'Sales', 'employee_sales_manager', 'employee/sales-dashboard'],
        ['Sales Manager', 'Sales', 'employee_sales_manager', 'employee/sales-dashboard'],
        ['Senior Sales Executive', 'Sales', 'employee_sales_executive', 'employee/dashboard'],
        ['Sales Executive', 'Sales', 'employee_sales_executive', 'employee/dashboard'],
        ['Junior Sales Executive', 'Sales', 'employee_sales_executive', 'employee/dashboard'],
        ['Telecalling Lead', 'Sales', 'employee_telecalling_lead', 'employee/telecalling-dashboard'],
        ['Senior Telecaller', 'Sales', 'employee_telecaller', 'employee/telecalling-dashboard'],
        ['Telecaller', 'Sales', 'employee_telecaller', 'employee/telecalling-dashboard'],
        ['Marketing Manager', 'Sales', 'employee_marketing_manager', 'employee/dashboard'],
        ['Digital Marketer', 'Sales', 'employee_marketing_executive', 'employee/dashboard'],
        ['Content Writer', 'Sales', 'employee_marketing_executive', 'employee/dashboard'],
        ['SEO Specialist', 'Sales', 'employee_marketing_executive', 'employee/dashboard'],
        ['Social Media Executive', 'Sales', 'employee_marketing_executive', 'employee/dashboard'],
        
        // Land
        ['Land Director', 'Land', 'employee_land_manager', 'employee/land-dashboard'],
        ['Land Manager', 'Land', 'employee_land_manager', 'employee/land-dashboard'],
        ['Senior Surveyor', 'Land', 'employee_land_executive', 'employee/dashboard'],
        ['Surveyor', 'Land', 'employee_land_executive', 'employee/dashboard'],
        ['Liaison Officer', 'Land', 'employee_land_executive', 'employee/dashboard'],
        ['Land Executive', 'Land', 'employee_land_executive', 'employee/dashboard'],
        
        // Legal
        ['Legal Advisor', 'Legal', 'employee_legal_advisor', 'employee/legal-dashboard'],
        ['Senior Legal Executive', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        ['Legal Executive', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        ['Document Manager', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        ['Compliance Officer', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        
        // Construction
        ['Project Director', 'Construction', 'employee_project_manager', 'employee/construction-dashboard'],
        ['Project Manager', 'Construction', 'employee_project_manager', 'employee/construction-dashboard'],
        ['Senior Site Engineer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Site Engineer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Junior Site Engineer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Supervisor', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Quality Inspector', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Safety Officer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        
        // HR
        ['HR Director', 'HR', 'employee_hr_manager', 'employee/hr-dashboard'],
        ['HR Manager', 'HR', 'employee_hr_manager', 'employee/hr-dashboard'],
        ['Senior HR Executive', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['HR Executive', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['HR Assistant', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['Recruiter', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['Payroll Executive', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        
        // Customer Success
        ['Customer Success Head', 'Customer Success', 'employee_cs_manager', 'employee/dashboard'],
        ['Relationship Manager', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        ['Senior Support Executive', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        ['Support Executive', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        ['Customer Care Executive', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        
        // IT
        ['IT Director', 'IT', 'employee_it_manager', 'employee/it-dashboard'],
        ['IT Manager', 'IT', 'employee_it_manager', 'employee/it-dashboard'],
        ['Senior Developer', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['Developer', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['Junior Developer', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['System Admin', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['Data Analyst', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['UI/UX Designer', 'IT', 'employee_it_executive', 'employee/dashboard'],
        
        // Operations
        ['Operations Director', 'Operations', 'employee_ops_manager', 'employee/ops-dashboard'],
        ['Operations Manager', 'Operations', 'employee_ops_manager', 'employee/ops-dashboard'],
        ['Admin Executive', 'Operations', 'employee_ops_executive', 'employee/dashboard'],
        ['Office Administrator', 'Operations', 'employee_ops_executive', 'employee/dashboard'],
        ['Vendor Manager', 'Operations', 'employee_ops_executive', 'employee/dashboard'],
        ['Store Keeper', 'Operations', 'employee_ops_executive', 'employee/dashboard'],
        ['Driver', 'Operations', 'employee_ops_executive', 'employee/dashboard'],
        ['Security Guard', 'Operations', 'employee_ops_executive', 'employee/dashboard'],
        ['Peon', 'Operations', 'employee_ops_executive', 'employee/dashboard'],
    ];

    foreach ($subRoles as $sr) {
        $subRoleInsert->execute($sr);
    }
    echo "Seeded " . count($subRoles) . " designation-role mappings.\n";

    echo "\n✅ Migration complete!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
