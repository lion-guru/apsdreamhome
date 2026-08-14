<?php
/**
 * Run department + designation migration (fixes partial state from 025)
 */
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected.\n";

    // 1. Drop old columns (head_id already dropped in previous ALTER)
    echo "Dropping old type column...\n";
    try {
        $pdo->exec("ALTER TABLE departments DROP COLUMN `type`");
        echo "Dropped type.\n";
    } catch (PDOException $e) {
        echo "type column may already be dropped: " . $e->getMessage() . "\n";
    }
    // Also drop old keys that referenced dropped columns
    try {
        $pdo->exec("ALTER TABLE departments DROP KEY `idx_departments_head_id`");
        echo "Dropped idx_departments_head_id.\n";
    } catch (PDOException $e) {
        echo "head_id key may already be dropped.\n";
    }
    try {
        $pdo->exec("ALTER TABLE departments DROP KEY `type`");
        echo "Dropped type key.\n";
    } catch (PDOException $e) {
        echo "type key may already be dropped.\n";
    }

    // 2. Update existing 8 departments with correct codes
    echo "Updating existing departments...\n";
    $updates = [
        ['SALES', 'Sales & Marketing â€” Lead generation, sales, customer acquisition', NULL, 1],
        ['FIN', 'Finance & Accounts â€” Financial management, accounting, TDS, GST, audits', NULL, 1],
        ['CS', 'Customer Success â€” Post-sale support, complaints, customer satisfaction', NULL, 1],
        ['OPS', 'Operations â€” Billing, invoicing, payment processing', NULL, 1],
        ['LEGAL', 'Legal & Compliance â€” Agreements, RERA, title verification, compliance', NULL, 1],
        ['HR', 'HR & Administration â€” Recruitment, attendance, payroll, employee welfare', NULL, 1],
        ['SALES', 'Marketing & Branding â€” Digital marketing, content, SEO, social media', NULL, 1],
        ['IT', 'IT & Digital â€” Software, infrastructure, data, digital marketing', NULL, 1],
    ];

    // Wait - the existing 8 rows have different names. Let me map them properly:
    // id=1: "Real Estate Sales" -> SALES
    // id=2: "Investment & Finance" -> FIN
    // id=3: "Customer Support" -> CS
    // id=4: "Billing & Accounts" -> OPS
    // id=5: "Legal & Documentation" -> LEGAL
    // id=6: "Human Resources" -> HR
    // id=7: "Marketing & Branding" -> MKTG (need a new code)
    // id=8: "IT & Systems" -> IT

    $pdo->exec("UPDATE departments SET code='SALES', description='Sales & Marketing â€” Lead generation, sales, customer acquisition', parent_dept_id=1 WHERE id=1");
    $pdo->exec("UPDATE departments SET code='FIN', description='Finance & Accounts â€” Financial management, accounting, TDS, GST, audits', parent_dept_id=1 WHERE id=2");
    $pdo->exec("UPDATE departments SET code='CS', description='Customer Success â€” Post-sale support, complaints, customer satisfaction', parent_dept_id=1 WHERE id=3");
    $pdo->exec("UPDATE departments SET code='OPS', description='Operations â€” Day-to-day operations, billing, vendor management', parent_dept_id=1 WHERE id=4");
    $pdo->exec("UPDATE departments SET code='LEGAL', description='Legal & Compliance â€” Agreements, RERA, title verification, compliance', parent_dept_id=1 WHERE id=5");
    $pdo->exec("UPDATE departments SET code='HR', description='HR & Administration â€” Recruitment, attendance, payroll, employee welfare', parent_dept_id=1 WHERE id=6");
    $pdo->exec("UPDATE departments SET code='MKTG', description='Marketing & Branding â€” Digital marketing, content, SEO, social media', parent_dept_id=1 WHERE id=7");
    $pdo->exec("UPDATE departments SET code='IT', description='IT & Digital â€” Software, infrastructure, data, digital marketing', parent_dept_id=1 WHERE id=8");
    echo "Updated 8 existing departments with codes.\n";

    // 3. Insert EXEC and CONST departments
    $pdo->exec("INSERT IGNORE INTO departments (code, name, description, parent_dept_id, dept_budget, status) VALUES ('EXEC', 'CEO Office', 'Executive leadership and strategy', NULL, 5000000, 'active')");
    $pdo->exec("INSERT IGNORE INTO departments (code, name, description, parent_dept_id, dept_budget, status) VALUES ('CONST', 'Construction & Projects', 'Construction, site management, quality control', 1, 5000000, 'active')");
    echo "Added EXEC and CONST departments.\n";

    // 4. Verify
    $rows = $pdo->query("SELECT id, code, name FROM departments ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nDepartments (" . count($rows) . " total):\n";
    foreach ($rows as $r) {
        echo "  {$r['id']}: {$r['code']} - {$r['name']}\n";
    }

    // 5. Seed designations
    echo "\nSeeding designations...\n";
    $stmt = $pdo->prepare("INSERT IGNORE INTO designations (name, department_id, level, min_salary, max_salary, sub_role, dashboard_view) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // We need department IDs. Let's get them by code.
    $deptMap = [];
    $deptRows = $pdo->query("SELECT id, code FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($deptRows as $dr) {
        $deptMap[$dr['code']] = $dr['id'];
    }
    echo "Department map: " . json_encode($deptMap) . "\n";

    $designations = [
        // EXEC (dept code EXEC -> id)
        ['CEO', $deptMap['EXEC'], 5, 500000, 1000000, 'super_admin', NULL],
        ['COO', $deptMap['EXEC'], 5, 400000, 800000, 'admin', NULL],
        ['Director', $deptMap['EXEC'], 4, 300000, 600000, 'director', NULL],
        ['Executive Assistant', $deptMap['EXEC'], 2, 80000, 150000, 'employee_general', NULL],

        // FIN
        ['CFO', $deptMap['FIN'], 5, 400000, 800000, 'employee_finance_manager', '/employee/finance-dashboard'],
        ['Finance Manager', $deptMap['FIN'], 4, 200000, 400000, 'employee_finance_manager', '/employee/finance-dashboard'],
        ['Senior Accountant', $deptMap['FIN'], 3, 120000, 250000, 'employee_finance_executive', NULL],
        ['Accountant', $deptMap['FIN'], 2, 60000, 120000, 'employee_finance_executive', NULL],
        ['Accounts Executive', $deptMap['FIN'], 1, 40000, 80000, 'employee_finance_executive', NULL],
        ['Tax Consultant', $deptMap['FIN'], 3, 100000, 200000, 'employee_finance_executive', NULL],

        // SALES
        ['Sales Director', $deptMap['SALES'], 5, 300000, 600000, 'employee_sales_manager', '/employee/sales-dashboard'],
        ['Sales Manager', $deptMap['SALES'], 4, 150000, 350000, 'employee_sales_manager', '/employee/sales-dashboard'],
        ['Senior Sales Executive', $deptMap['SALES'], 3, 80000, 180000, 'employee_sales_executive', NULL],
        ['Sales Executive', $deptMap['SALES'], 2, 50000, 100000, 'employee_sales_executive', NULL],
        ['Junior Sales Executive', $deptMap['SALES'], 1, 30000, 60000, 'employee_sales_executive', NULL],
        ['Telecalling Lead', $deptMap['SALES'], 3, 60000, 120000, 'employee_telecalling_lead', NULL],
        ['Senior Telecaller', $deptMap['SALES'], 2, 40000, 70000, 'employee_telecaller', NULL],
        ['Telecaller', $deptMap['SALES'], 1, 25000, 50000, 'employee_telecaller', NULL],
        ['Marketing Manager', $deptMap['SALES'], 4, 150000, 300000, 'employee_marketing_manager', NULL],
        ['Digital Marketer', $deptMap['SALES'], 2, 50000, 100000, 'employee_marketing_executive', NULL],
        ['Content Writer', $deptMap['SALES'], 1, 30000, 60000, 'employee_marketing_executive', NULL],
        ['SEO Specialist', $deptMap['SALES'], 2, 40000, 80000, 'employee_marketing_executive', NULL],
        ['Social Media Executive', $deptMap['SALES'], 1, 30000, 55000, 'employee_marketing_executive', NULL],

        // MKTG (Marketing & Branding)
        ['Marketing Director', $deptMap['MKTG'], 5, 250000, 500000, 'employee_marketing_manager', NULL],
        ['Brand Manager', $deptMap['MKTG'], 3, 80000, 150000, 'employee_marketing_executive', NULL],
        ['Performance Marketer', $deptMap['MKTG'], 2, 50000, 100000, 'employee_marketing_executive', NULL],
        ['Graphic Designer', $deptMap['MKTG'], 2, 40000, 80000, 'employee_marketing_executive', NULL],

        // LAND (Land & Acquisition â€” mapped to existing code LAND... wait, we don't have LAND)
        // Actually let me check. The 8 existing are: SALES, FIN, CS, OPS, LEGAL, HR, MKTG, IT
        // Plus EXEC, CONST = 10
        // We need LAND too. Let me add it as 11th dept.
    ];

    // Check if LAND exists
    $landCheck = $pdo->query("SELECT id FROM departments WHERE code='LAND'")->fetch();
    if (!$landCheck) {
        $pdo->exec("INSERT INTO departments (code, name, description, parent_dept_id, dept_budget, status) VALUES ('LAND', 'Land & Acquisition', 'Land identification, negotiation, purchase', 1, 2000000, 'active')");
        $landRow = $pdo->query("SELECT id FROM departments WHERE code='LAND'")->fetch();
        $deptMap['LAND'] = $landRow['id'];
        echo "Added LAND department.\n";
    }

    // Now add LAND designations
    $landDesignations = [
        ['Land Director', $deptMap['LAND'], 5, 300000, 500000, 'employee_land_manager', '/employee/land-dashboard'],
        ['Land Manager', $deptMap['LAND'], 4, 150000, 300000, 'employee_land_manager', '/employee/land-dashboard'],
        ['Senior Surveyor', $deptMap['LAND'], 3, 80000, 150000, 'employee_land_executive', NULL],
        ['Surveyor', $deptMap['LAND'], 2, 50000, 100000, 'employee_land_executive', NULL],
        ['Liaison Officer', $deptMap['LAND'], 2, 60000, 120000, 'employee_land_executive', NULL],
        ['Land Executive', $deptMap['LAND'], 1, 35000, 70000, 'employee_land_executive', NULL],
    ];

    // CONST designations
    $constDesignations = [
        ['Project Director', $deptMap['CONST'], 5, 350000, 600000, 'employee_project_manager', '/employee/construction-dashboard'],
        ['Project Manager', $deptMap['CONST'], 4, 180000, 350000, 'employee_project_manager', '/employee/construction-dashboard'],
        ['Senior Site Engineer', $deptMap['CONST'], 3, 100000, 200000, 'employee_site_engineer', NULL],
        ['Site Engineer', $deptMap['CONST'], 2, 60000, 120000, 'employee_site_engineer', NULL],
        ['Junior Site Engineer', $deptMap['CONST'], 1, 35000, 70000, 'employee_site_engineer', NULL],
        ['Supervisor', $deptMap['CONST'], 2, 45000, 90000, 'employee_site_engineer', NULL],
        ['Quality Inspector', $deptMap['CONST'], 2, 50000, 100000, 'employee_site_engineer', NULL],
        ['Safety Officer', $deptMap['CONST'], 2, 50000, 95000, 'employee_site_engineer', NULL],
    ];

    // LEGAL designations
    $legalDesignations = [
        ['Legal Advisor', $deptMap['LEGAL'], 4, 200000, 400000, 'employee_legal_advisor', '/employee/legal-dashboard'],
        ['Senior Legal Executive', $deptMap['LEGAL'], 3, 100000, 200000, 'employee_legal_executive', NULL],
        ['Legal Executive', $deptMap['LEGAL'], 2, 60000, 120000, 'employee_legal_executive', NULL],
        ['Document Manager', $deptMap['LEGAL'], 2, 50000, 90000, 'employee_legal_executive', NULL],
        ['Compliance Officer', $deptMap['LEGAL'], 3, 80000, 150000, 'employee_legal_executive', NULL],
    ];

    // HR designations
    $hrDesignations = [
        ['HR Director', $deptMap['HR'], 5, 250000, 450000, 'employee_hr_manager', '/employee/hr-dashboard'],
        ['HR Manager', $deptMap['HR'], 4, 120000, 250000, 'employee_hr_manager', '/employee/hr-dashboard'],
        ['Senior HR Executive', $deptMap['HR'], 3, 70000, 140000, 'employee_hr_executive', NULL],
        ['HR Executive', $deptMap['HR'], 2, 40000, 80000, 'employee_hr_executive', NULL],
        ['HR Assistant', $deptMap['HR'], 1, 25000, 50000, 'employee_hr_executive', NULL],
        ['Recruiter', $deptMap['HR'], 2, 40000, 75000, 'employee_hr_executive', NULL],
        ['Payroll Executive', $deptMap['HR'], 2, 45000, 85000, 'employee_hr_executive', NULL],
    ];

    // CS designations
    $csDesignations = [
        ['Customer Success Head', $deptMap['CS'], 4, 150000, 300000, 'employee_cs_manager', NULL],
        ['Relationship Manager', $deptMap['CS'], 3, 70000, 140000, 'employee_cs_executive', NULL],
        ['Senior Support Executive', $deptMap['CS'], 3, 55000, 100000, 'employee_cs_executive', NULL],
        ['Support Executive', $deptMap['CS'], 2, 35000, 70000, 'employee_cs_executive', NULL],
        ['Customer Care Executive', $deptMap['CS'], 1, 25000, 50000, 'employee_cs_executive', NULL],
    ];

    // IT designations
    $itDesignations = [
        ['IT Director', $deptMap['IT'], 5, 300000, 500000, 'employee_it_manager', '/employee/it-dashboard'],
        ['IT Manager', $deptMap['IT'], 4, 180000, 350000, 'employee_it_manager', '/employee/it-dashboard'],
        ['Senior Developer', $deptMap['IT'], 3, 120000, 250000, 'employee_it_executive', NULL],
        ['Developer', $deptMap['IT'], 2, 70000, 150000, 'employee_it_executive', NULL],
        ['Junior Developer', $deptMap['IT'], 1, 40000, 80000, 'employee_it_executive', NULL],
        ['System Admin', $deptMap['IT'], 3, 100000, 200000, 'employee_it_executive', NULL],
        ['Data Analyst', $deptMap['IT'], 2, 60000, 120000, 'employee_it_executive', NULL],
        ['UI/UX Designer', $deptMap['IT'], 2, 60000, 130000, 'employee_it_executive', NULL],
    ];

    // OPS designations
    $opsDesignations = [
        ['Operations Director', $deptMap['OPS'], 5, 250000, 450000, 'employee_ops_manager', '/employee/ops-dashboard'],
        ['Operations Manager', $deptMap['OPS'], 4, 150000, 300000, 'employee_ops_manager', '/employee/ops-dashboard'],
        ['Admin Executive', $deptMap['OPS'], 2, 40000, 80000, 'employee_ops_executive', NULL],
        ['Office Administrator', $deptMap['OPS'], 1, 25000, 50000, 'employee_ops_executive', NULL],
        ['Vendor Manager', $deptMap['OPS'], 3, 70000, 140000, 'employee_ops_executive', NULL],
        ['Store Keeper', $deptMap['OPS'], 1, 20000, 40000, 'employee_ops_executive', NULL],
        ['Driver', $deptMap['OPS'], 1, 18000, 30000, 'employee_ops_executive', NULL],
        ['Security Guard', $deptMap['OPS'], 1, 15000, 25000, 'employee_ops_executive', NULL],
        ['Peon', $deptMap['OPS'], 1, 12000, 20000, 'employee_ops_executive', NULL],
    ];

    $allDesignations = array_merge(
        $designations,
        $landDesignations,
        $constDesignations,
        $legalDesignations,
        $hrDesignations,
        $csDesignations,
        $itDesignations,
        $opsDesignations
    );

    $count = 0;
    foreach ($allDesignations as $desig) {
        $stmt->execute($desig);
        $count++;
    }
    echo "Seeded $count designations.\n";

    // 6. Rebuild employee_designation_roles
    echo "\nRebuilding employee_designation_roles...\n";
    $pdo->exec("TRUNCATE TABLE employee_designation_roles");

    $subRoleInsert = $pdo->prepare("INSERT INTO employee_designation_roles (designation, department, sub_role, dashboard_view) VALUES (?, ?, ?, ?)");

    $subRoles = [
        ['CEO', 'CEO Office', 'super_admin', 'admin/dashboard'],
        ['COO', 'CEO Office', 'admin', 'admin/dashboard'],
        ['Director', 'CEO Office', 'director', 'admin/dashboard'],
        ['Executive Assistant', 'CEO Office', 'employee_general', 'employee/dashboard'],
        ['CFO', 'Finance', 'employee_finance_manager', 'employee/finance-dashboard'],
        ['Finance Manager', 'Finance', 'employee_finance_manager', 'employee/finance-dashboard'],
        ['Senior Accountant', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
        ['Accountant', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
        ['Accounts Executive', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
        ['Tax Consultant', 'Finance', 'employee_finance_executive', 'employee/dashboard'],
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
        ['Marketing Director', 'Marketing', 'employee_marketing_manager', 'employee/dashboard'],
        ['Brand Manager', 'Marketing', 'employee_marketing_executive', 'employee/dashboard'],
        ['Performance Marketer', 'Marketing', 'employee_marketing_executive', 'employee/dashboard'],
        ['Graphic Designer', 'Marketing', 'employee_marketing_executive', 'employee/dashboard'],
        ['Land Director', 'Land', 'employee_land_manager', 'employee/land-dashboard'],
        ['Land Manager', 'Land', 'employee_land_manager', 'employee/land-dashboard'],
        ['Senior Surveyor', 'Land', 'employee_land_executive', 'employee/dashboard'],
        ['Surveyor', 'Land', 'employee_land_executive', 'employee/dashboard'],
        ['Liaison Officer', 'Land', 'employee_land_executive', 'employee/dashboard'],
        ['Land Executive', 'Land', 'employee_land_executive', 'employee/dashboard'],
        ['Legal Advisor', 'Legal', 'employee_legal_advisor', 'employee/legal-dashboard'],
        ['Senior Legal Executive', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        ['Legal Executive', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        ['Document Manager', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        ['Compliance Officer', 'Legal', 'employee_legal_executive', 'employee/dashboard'],
        ['Project Director', 'Construction', 'employee_project_manager', 'employee/construction-dashboard'],
        ['Project Manager', 'Construction', 'employee_project_manager', 'employee/construction-dashboard'],
        ['Senior Site Engineer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Site Engineer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Junior Site Engineer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Supervisor', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Quality Inspector', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['Safety Officer', 'Construction', 'employee_site_engineer', 'employee/dashboard'],
        ['HR Director', 'HR', 'employee_hr_manager', 'employee/hr-dashboard'],
        ['HR Manager', 'HR', 'employee_hr_manager', 'employee/hr-dashboard'],
        ['Senior HR Executive', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['HR Executive', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['HR Assistant', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['Recruiter', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['Payroll Executive', 'HR', 'employee_hr_executive', 'employee/dashboard'],
        ['Customer Success Head', 'Customer Success', 'employee_cs_manager', 'employee/dashboard'],
        ['Relationship Manager', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        ['Senior Support Executive', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        ['Support Executive', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        ['Customer Care Executive', 'Customer Success', 'employee_cs_executive', 'employee/dashboard'],
        ['IT Director', 'IT', 'employee_it_manager', 'employee/it-dashboard'],
        ['IT Manager', 'IT', 'employee_it_manager', 'employee/it-dashboard'],
        ['Senior Developer', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['Developer', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['Junior Developer', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['System Admin', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['Data Analyst', 'IT', 'employee_it_executive', 'employee/dashboard'],
        ['UI/UX Designer', 'IT', 'employee_it_executive', 'employee/dashboard'],
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

    // Final counts
    $deptCount = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    $desigCount = $pdo->query("SELECT COUNT(*) FROM designations")->fetchColumn();
    $roleCount = $pdo->query("SELECT COUNT(*) FROM employee_designation_roles")->fetchColumn();
    echo "\nFinal counts: $deptCount departments, $desigCount designations, $roleCount role mappings.\n";

    echo "\nâœ… Migration complete!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}?>