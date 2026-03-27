<?php

/**
 * APS Dream Home - Enterprise RBAC Database Migration
 * Creates roles, permissions, and role_permissions tables
 */

require_once __DIR__ . '/../config/database.php';

class RBACMigration
{
    private $pdo;

    public function __construct()
    {
        $dbConfig = require __DIR__ . '/../config/database.php';
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                $dbConfig['username'],
                $dbConfig['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function up()
    {
        echo "Running RBAC Migration...\n\n";

        $this->createRolesTable();
        $this->createPermissionsTable();
        $this->createRolePermissionsTable();
        $this->createUserRolesTable();
        $this->createAuditLogTable();
        $this->insertDefaultRoles();
        $this->insertDefaultPermissions();
        $this->assignPermissionsToRoles();

        echo "\n✅ RBAC Migration completed successfully!\n";
    }

    private function createRolesTable()
    {
        echo "Creating roles table...\n";

        $sql = "CREATE TABLE IF NOT EXISTS `roles` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `display_name` VARCHAR(150) NOT NULL,
            `description` TEXT NULL,
            `level` INT NOT NULL DEFAULT 10,
            `category` VARCHAR(50) NOT NULL DEFAULT 'General',
            `dashboard_type` VARCHAR(50) NOT NULL DEFAULT 'default',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `is_system` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_slug` (`slug`),
            KEY `idx_level` (`level`),
            KEY `idx_category` (`category`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
        echo "  ✅ roles table created\n";
    }

    private function createPermissionsTable()
    {
        echo "Creating permissions table...\n";

        $sql = "CREATE TABLE IF NOT EXISTS `permissions` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(150) NOT NULL UNIQUE,
            `slug` VARCHAR(150) NOT NULL UNIQUE,
            `display_name` VARCHAR(200) NOT NULL,
            `description` TEXT NULL,
            `category` VARCHAR(50) NOT NULL DEFAULT 'General',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_name` (`name`),
            UNIQUE KEY `idx_slug` (`slug`),
            KEY `idx_category` (`category`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
        echo "  ✅ permissions table created\n";
    }

    private function createRolePermissionsTable()
    {
        echo "Creating role_permissions table...\n";

        $sql = "CREATE TABLE IF NOT EXISTS `role_permissions` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `role_id` INT UNSIGNED NOT NULL,
            `permission_id` INT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_role_permission` (`role_id`, `permission_id`),
            KEY `idx_role` (`role_id`),
            KEY `idx_permission` (`permission_id`),
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
        echo "  ✅ role_permissions table created\n";
    }

    private function createUserRolesTable()
    {
        echo "Creating user_roles table...\n";

        $sql = "CREATE TABLE IF NOT EXISTS `user_roles` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `role_id` INT UNSIGNED NOT NULL,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            `assigned_by` INT UNSIGNED NULL,
            `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `expires_at` DATETIME NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_user` (`user_id`),
            KEY `idx_role` (`role_id`),
            KEY `idx_primary` (`is_primary`),
            KEY `idx_active` (`is_active`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
        echo "  ✅ user_roles table created\n";
    }

    private function createAuditLogTable()
    {
        echo "Creating permission_audit_log table...\n";

        $sql = "CREATE TABLE IF NOT EXISTS `permission_audit_log` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NULL,
            `action` VARCHAR(50) NOT NULL,
            `entity_type` VARCHAR(50) NOT NULL,
            `entity_id` INT UNSIGNED NULL,
            `old_values` JSON NULL,
            `new_values` JSON NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user` (`user_id`),
            KEY `idx_action` (`action`),
            KEY `idx_entity` (`entity_type`, `entity_id`),
            KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
        echo "  ✅ permission_audit_log table created\n";
    }

    private function insertDefaultRoles()
    {
        echo "Inserting default roles...\n";

        $roles = [
            // Executive Level (C-Suite)
            ['super_admin', 'Super Admin', 'Full system access', 1, 'Executive', 'superadmin', true, true],
            ['ceo', 'CEO', 'Chief Executive Officer - Full business overview', 2, 'Executive', 'executive', true, false],
            ['cfo', 'CFO', 'Chief Financial Officer - Financial control', 3, 'Executive', 'executive', true, false],
            ['coo', 'COO', 'Chief Operations Officer - Operations control', 3, 'Executive', 'executive', true, false],
            ['cto', 'CTO', 'Chief Technology Officer - Technical control', 3, 'Executive', 'executive', true, false],
            ['cmo', 'CMO', 'Chief Marketing Officer - Marketing control', 3, 'Executive', 'executive', true, false],
            ['chro', 'CHRO', 'Chief HR Officer - HR control', 3, 'Executive', 'executive', true, false],

            // Management Level
            ['director', 'Director', 'Department director', 4, 'Management', 'manager', true, false],
            ['sales_director', 'Sales Director', 'Sales department head', 4, 'Management', 'manager', true, false],
            ['marketing_director', 'Marketing Director', 'Marketing department head', 4, 'Management', 'manager', true, false],
            ['construction_director', 'Construction Director', 'Construction department head', 4, 'Management', 'manager', true, false],

            // Departmental Level
            ['department_manager', 'Department Manager', 'Generic department manager', 5, 'Departmental', 'manager', true, false],
            ['project_manager', 'Project Manager', 'Project management', 5, 'Departmental', 'manager', true, false],
            ['sales_manager', 'Sales Manager', 'Sales team manager', 5, 'Departmental', 'manager', true, false],
            ['hr_manager', 'HR Manager', 'HR department manager', 5, 'Departmental', 'manager', true, false],
            ['marketing_manager', 'Marketing Manager', 'Marketing team manager', 5, 'Departmental', 'manager', true, false],
            ['finance_manager', 'Finance Manager', 'Finance team manager', 5, 'Departmental', 'manager', true, false],
            ['property_manager', 'Property Manager', 'Property management', 5, 'Departmental', 'manager', true, false],
            ['it_manager', 'IT Manager', 'IT department manager', 5, 'Departmental', 'manager', true, false],
            ['operations_manager', 'Operations Manager', 'Operations management', 5, 'Departmental', 'manager', true, false],

            // Team Lead Level
            ['team_lead', 'Team Lead', 'Generic team lead', 6, 'Team Lead', 'team_lead', true, false],
            ['telecalling_lead', 'Telecalling Lead', 'Telecalling team lead', 6, 'Team Lead', 'team_lead', true, false],
            ['sales_team_lead', 'Sales Team Lead', 'Sales team lead', 6, 'Team Lead', 'team_lead', true, false],
            ['support_lead', 'Support Lead', 'Support team lead', 6, 'Team Lead', 'team_lead', true, false],

            // Senior Staff Level
            ['senior_accountant', 'Senior Accountant', 'Senior accounting staff', 7, 'Senior Staff', 'employee', true, false],
            ['senior_developer', 'Senior Developer', 'Senior developer', 7, 'Senior Staff', 'employee', true, false],
            ['legal_advisor', 'Legal Advisor', 'Legal consultant', 7, 'Senior Staff', 'employee', true, false],
            ['chartered_accountant', 'Chartered Accountant', 'CA for compliance', 7, 'Senior Staff', 'employee', true, false],

            // Staff Level
            ['accountant', 'Accountant', 'Accounting staff', 8, 'Staff', 'employee', true, false],
            ['developer', 'Developer', 'Software developer', 8, 'Staff', 'employee', true, false],
            ['content_writer', 'Content Writer', 'Content creation', 8, 'Staff', 'employee', true, false],
            ['graphic_designer', 'Graphic Designer', 'Design work', 8, 'Staff', 'employee', true, false],
            ['data_entry_operator', 'Data Entry Operator', 'Data entry', 8, 'Staff', 'employee', true, false],
            ['backoffice_staff', 'Back Office Staff', 'Back office work', 8, 'Staff', 'employee', true, false],

            // Telecalling Level
            ['telecaller', 'Telecaller', 'Telecalling staff', 9, 'Telecalling', 'employee', true, false],
            ['telecalling_executive', 'Telecalling Executive', 'Telecalling executive', 9, 'Telecalling', 'employee', true, false],
            ['support_executive', 'Support Executive', 'Customer support', 9, 'Support', 'employee', true, false],

            // MLM/Associate Level
            ['senior_associate', 'Senior Associate', 'Senior MLM associate', 10, 'MLM', 'associate', true, false],
            ['associate_team_lead', 'Associate Team Lead', 'MLM team lead', 10, 'MLM', 'associate', true, false],
            ['associate', 'Associate', 'MLM associate/distributor', 10, 'MLM', 'associate', true, false],
            ['senior_agent', 'Senior Agent', 'Senior sales agent', 10, 'Agent', 'associate', true, false],
            ['agent', 'Agent', 'Sales agent', 10, 'Agent', 'associate', true, false],

            // Franchise
            ['franchise_owner', 'Franchise Owner', 'Franchise business owner', 9, 'Franchise', 'franchise', true, false],

            // Customer Level
            ['premium_customer', 'Premium Customer', 'Premium paying customer', 11, 'Customer', 'customer', true, false],
            ['verified_customer', 'Verified Customer', 'Verified customer account', 11, 'Customer', 'customer', true, false],
            ['guest_customer', 'Guest Customer', 'Basic customer', 11, 'Customer', 'customer', true, false],

            // Lead Level
            ['hot_lead', 'Hot Lead', 'High priority lead', 12, 'Lead', 'lead', true, false],
            ['warm_lead', 'Warm Lead', 'Medium priority lead', 12, 'Lead', 'lead', true, false],
            ['cold_lead', 'Cold Lead', 'Low priority lead', 12, 'Lead', 'lead', true, false],

            // Guest
            ['guest', 'Guest', 'Unregistered user', 13, 'Guest', 'guest', true, false],

            // Legacy Aliases
            ['admin', 'Admin', 'Administrator (legacy)', 2, 'Legacy', 'admin', true, false],
            ['manager', 'Manager', 'Manager (legacy)', 5, 'Legacy', 'manager', true, false],
            ['user', 'User', 'Regular user (legacy)', 11, 'Legacy', 'user', true, false],
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO roles (slug, name, description, level, category, dashboard_type, is_active, is_system) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($roles as $role) {
            $stmt->execute($role);
        }

        echo "  ✅ " . count($roles) . " roles inserted\n";
    }

    private function insertDefaultPermissions()
    {
        echo "Inserting default permissions...\n";

        $permissions = [
            // Dashboard
            ['dashboard.view', 'dashboard.view', 'View Dashboard', 'Dashboard', 'Can view dashboard'],
            ['dashboard.customize', 'dashboard.customize', 'Customize Dashboard', 'Dashboard', 'Can customize dashboard'],
            ['dashboard.widgets', 'dashboard.widgets', 'Manage Dashboard Widgets', 'Dashboard', 'Can manage widgets'],

            // Users
            ['users.view.all', 'users.view.all', 'View All Users', 'Users', 'Can view all users'],
            ['users.view.team', 'users.view.team', 'View Team Users', 'Users', 'Can view team users'],
            ['users.create', 'users.create', 'Create Users', 'Users', 'Can create users'],
            ['users.edit', 'users.edit', 'Edit Users', 'Users', 'Can edit users'],
            ['users.delete', 'users.delete', 'Delete Users', 'Users', 'Can delete users'],
            ['users.roles', 'users.roles', 'Manage User Roles', 'Users', 'Can manage roles'],

            // Properties
            ['property.view', 'property.view', 'View Properties', 'Properties', 'Can view properties'],
            ['property.view.all', 'property.view.all', 'View All Properties', 'Properties', 'Can view all properties'],
            ['property.create', 'property.create', 'Create Properties', 'Properties', 'Can create properties'],
            ['property.edit', 'property.edit', 'Edit Properties', 'Properties', 'Can edit properties'],
            ['property.delete', 'property.delete', 'Delete Properties', 'Properties', 'Can delete properties'],
            ['property.pricing', 'property.pricing', 'Manage Property Pricing', 'Properties', 'Can manage pricing'],

            // Leads
            ['leads.view.all', 'leads.view.all', 'View All Leads', 'Leads', 'Can view all leads'],
            ['leads.view.team', 'leads.view.team', 'View Team Leads', 'Leads', 'Can view team leads'],
            ['leads.view.own', 'leads.view.own', 'View Own Leads', 'Leads', 'Can view own leads'],
            ['leads.create', 'leads.create', 'Create Leads', 'Leads', 'Can create leads'],
            ['leads.edit', 'leads.edit', 'Edit Leads', 'Leads', 'Can edit leads'],
            ['leads.delete', 'leads.delete', 'Delete Leads', 'Leads', 'Can delete leads'],
            ['leads.assign', 'leads.assign', 'Assign Leads', 'Leads', 'Can assign leads'],

            // Sales
            ['sales.view', 'sales.view', 'View Sales', 'Sales', 'Can view sales'],
            ['sales.view.all', 'sales.view.all', 'View All Sales', 'Sales', 'Can view all sales'],
            ['sales.view.team', 'sales.view.team', 'View Team Sales', 'Sales', 'Can view team sales'],
            ['sales.create', 'sales.create', 'Create Sales', 'Sales', 'Can create sales'],
            ['commission.view', 'commission.view', 'View Commissions', 'Sales', 'Can view commissions'],
            ['commission.manage', 'commission.manage', 'Manage Commissions', 'Sales', 'Can manage commissions'],
            ['mlm.tree.view', 'mlm.tree.view', 'View MLM Tree', 'MLM', 'Can view MLM network tree'],
            ['mlm.downline.view', 'mlm.downline.view', 'View Downline', 'MLM', 'Can view downline members'],

            // Financial
            ['financial.view', 'financial.view', 'View Financials', 'Financial', 'Can view financial data'],
            ['financial.view.all', 'financial.view.all', 'View All Financials', 'Financial', 'Can view all financials'],
            ['invoice.create', 'invoice.create', 'Create Invoices', 'Financial', 'Can create invoices'],
            ['invoice.view', 'invoice.view', 'View Invoices', 'Financial', 'Can view invoices'],
            ['payment.process', 'payment.process', 'Process Payments', 'Financial', 'Can process payments'],
            ['expense.create', 'expense.create', 'Create Expenses', 'Financial', 'Can create expenses'],
            ['expense.view', 'expense.view', 'View Expenses', 'Financial', 'Can view expenses'],
            ['emi.manage', 'emi.manage', 'Manage EMI', 'Financial', 'Can manage EMI'],
            ['payroll.view', 'payroll.view', 'View Payroll', 'Financial', 'Can view payroll'],
            ['payroll.manage', 'payroll.manage', 'Manage Payroll', 'Financial', 'Can manage payroll'],
            ['tax.manage', 'tax.manage', 'Manage Tax', 'Financial', 'Can manage tax'],

            // HR
            ['employee.view.all', 'employee.view.all', 'View All Employees', 'HR', 'Can view all employees'],
            ['employee.view.team', 'employee.view.team', 'View Team Employees', 'HR', 'Can view team employees'],
            ['employee.add', 'employee.add', 'Add Employees', 'HR', 'Can add employees'],
            ['employee.edit', 'employee.edit', 'Edit Employees', 'HR', 'Can edit employees'],
            ['attendance.view', 'attendance.view', 'View Attendance', 'HR', 'Can view attendance'],
            ['attendance.manage', 'attendance.manage', 'Manage Attendance', 'HR', 'Can manage attendance'],
            ['leave.view', 'leave.view', 'View Leave', 'HR', 'Can view leave'],
            ['leave.approve', 'leave.approve', 'Approve Leave', 'HR', 'Can approve leave'],
            ['leave.manage', 'leave.manage', 'Manage Leave', 'HR', 'Can manage leave'],
            ['performance.view', 'performance.view', 'View Performance', 'HR', 'Can view performance'],
            ['performance.manage', 'performance.manage', 'Manage Performance', 'HR', 'Can manage performance'],
            ['training.manage', 'training.manage', 'Manage Training', 'HR', 'Can manage training'],

            // Marketing
            ['marketing.view', 'marketing.view', 'View Marketing', 'Marketing', 'Can view marketing'],
            ['marketing.view.all', 'marketing.view.all', 'View All Marketing', 'Marketing', 'Can view all marketing'],
            ['campaign.view', 'campaign.view', 'View Campaigns', 'Marketing', 'Can view campaigns'],
            ['campaign.create', 'campaign.create', 'Create Campaigns', 'Marketing', 'Can create campaigns'],
            ['campaign.execute', 'campaign.execute', 'Execute Campaigns', 'Marketing', 'Can execute campaigns'],
            ['email.marketing', 'email.marketing', 'Email Marketing', 'Marketing', 'Can send marketing emails'],
            ['sms.marketing', 'sms.marketing', 'SMS Marketing', 'Marketing', 'Can send SMS'],
            ['social.media', 'social.media', 'Social Media', 'Marketing', 'Can manage social media'],
            ['analytics.view', 'analytics.view', 'View Analytics', 'Marketing', 'Can view analytics'],

            // Content
            ['content.view', 'content.view', 'View Content', 'Content', 'Can view content'],
            ['pages.manage', 'pages.manage', 'Manage Pages', 'Content', 'Can manage pages'],
            ['media.manage', 'media.manage', 'Manage Media', 'Content', 'Can manage media'],
            ['blog.manage', 'blog.manage', 'Manage Blog', 'Content', 'Can manage blog'],
            ['faq.manage', 'faq.manage', 'Manage FAQ', 'Content', 'Can manage FAQ'],

            // AI
            ['ai.dashboard', 'ai.dashboard', 'AI Dashboard', 'AI', 'Can access AI dashboard'],
            ['ai.settings', 'ai.settings', 'AI Settings', 'AI', 'Can manage AI settings'],
            ['ai.property.valuation', 'ai.property.valuation', 'AI Property Valuation', 'AI', 'Can use AI valuation'],
            ['ai.lead.scoring', 'ai.lead.scoring', 'AI Lead Scoring', 'AI', 'Can use AI lead scoring'],
            ['chatbot.settings', 'chatbot.settings', 'Chatbot Settings', 'AI', 'Can manage chatbot'],

            // Reports
            ['reports.view', 'reports.view', 'View Reports', 'Reports', 'Can view reports'],
            ['reports.generate', 'reports.generate', 'Generate Reports', 'Reports', 'Can generate reports'],
            ['reports.export', 'reports.export', 'Export Reports', 'Reports', 'Can export reports'],
            ['reports.schedule', 'reports.schedule', 'Schedule Reports', 'Reports', 'Can schedule reports'],

            // System
            ['system.settings', 'system.settings', 'System Settings', 'System', 'Can access system settings'],
            ['backup.manage', 'backup.manage', 'Manage Backup', 'System', 'Can manage backup'],
            ['security.settings', 'security.settings', 'Security Settings', 'System', 'Can manage security'],
            ['api.manage', 'api.manage', 'Manage API', 'System', 'Can manage API'],
            ['log.viewer', 'log.viewer', 'View Logs', 'System', 'Can view logs'],
            ['cache.manage', 'cache.manage', 'Manage Cache', 'System', 'Can manage cache'],

            // Communication
            ['notifications.send', 'notifications.send', 'Send Notifications', 'Communication', 'Can send notifications'],
            ['email.templates', 'email.templates', 'Email Templates', 'Communication', 'Can manage email templates'],
            ['sms.templates', 'sms.templates', 'SMS Templates', 'Communication', 'Can manage SMS templates'],
            ['whatsapp.templates', 'whatsapp.templates', 'WhatsApp Templates', 'Communication', 'Can manage WhatsApp templates'],

            // Support
            ['support.view', 'support.view', 'View Support', 'Support', 'Can view support tickets'],
            ['support.reply', 'support.reply', 'Reply to Tickets', 'Support', 'Can reply to tickets'],
            ['support.escalate', 'support.escalate', 'Escalate Tickets', 'Support', 'Can escalate tickets'],

            // Tasks
            ['tasks.view', 'tasks.view', 'View Tasks', 'Tasks', 'Can view tasks'],
            ['tasks.create', 'tasks.create', 'Create Tasks', 'Tasks', 'Can create tasks'],
            ['tasks.assign', 'tasks.assign', 'Assign Tasks', 'Tasks', 'Can assign tasks'],
            ['tasks.complete', 'tasks.complete', 'Complete Tasks', 'Tasks', 'Can complete tasks'],
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO permissions (name, slug, display_name, category, description) VALUES (?, ?, ?, ?, ?)");

        foreach ($permissions as $perm) {
            $stmt->execute($perm);
        }

        echo "  ✅ " . count($permissions) . " permissions inserted\n";
    }

    private function assignPermissionsToRoles()
    {
        echo "Assigning permissions to roles...\n";

        // Get all permissions
        $permStmt = $this->pdo->query("SELECT id, slug FROM permissions");
        $permissions = [];
        while ($row = $permStmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[$row['slug']] = $row['id'];
        }

        // Get all roles
        $roleStmt = $this->pdo->query("SELECT id, slug FROM roles");
        $roles = [];
        while ($row = $roleStmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[$row['slug']] = $row['id'];
        }

        // Load RBAC Manager
        require_once __DIR__ . '/../../app/Http/Middleware/RBACManager.php';

        // Assign permissions to roles
        $assignments = 0;
        $insertStmt = $this->pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");

        foreach ($roles as $roleSlug => $roleId) {
            $rolePermissions = RBACManager::getRolePermissions($roleSlug);

            // Super admin gets all permissions
            if ($roleSlug === 'super_admin') {
                foreach ($permissions as $permSlug => $permId) {
                    $insertStmt->execute([$roleId, $permId]);
                    $assignments++;
                }
            } else {
                foreach ($rolePermissions as $permSlug) {
                    if (isset($permissions[$permSlug])) {
                        $insertStmt->execute([$roleId, $permissions[$permSlug]]);
                        $assignments++;
                    }
                }
            }
        }

        echo "  ✅ $assignments role-permission assignments created\n";
    }

    public function down()
    {
        echo "Rolling back RBAC tables...\n";

        $this->pdo->exec("DROP TABLE IF EXISTS `permission_audit_log`");
        echo "  ✅ permission_audit_log dropped\n";

        $this->pdo->exec("DROP TABLE IF EXISTS `user_roles`");
        echo "  ✅ user_roles dropped\n";

        $this->pdo->exec("DROP TABLE IF EXISTS `role_permissions`");
        echo "  ✅ role_permissions dropped\n";

        $this->pdo->exec("DROP TABLE IF EXISTS `permissions`");
        echo "  ✅ permissions dropped\n";

        $this->pdo->exec("DROP TABLE IF EXISTS `roles`");
        echo "  ✅ roles dropped\n";

        echo "\n✅ RBAC tables dropped successfully!\n";
    }
}

// Run migration
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $migration = new RBACMigration();
    
    if ($argv[1] === 'up') {
        $migration->up();
    } elseif ($argv[1] === 'down') {
        $migration->down();
    } else {
        echo "Usage: php rbac_migration.php up|down\n";
    }
}
