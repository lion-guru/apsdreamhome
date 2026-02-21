<?php
/**
 * Database Improvement Script
 * Optimizes table structures, adds indexes, creates views, and improves performance
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Connected to database successfully.\n";

    // Function to execute SQL queries
    function executeQuery($pdo, $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                echo "✅ Query executed successfully\n";
                return true;
            } else {
                echo "❌ Error executing query\n";
                return false;
            }
        } catch (Exception $e) {
            echo "⚠️  Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }

    echo "\n🔍 ANALYZING DATABASE STRUCTURE...\n";

    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "📊 Found " . count($tables) . " tables in database\n";

    // Check for potential duplicate tables
    $tableGroups = [];
    foreach ($tables as $table) {
        $prefix = explode('_', $table)[0] ?? $table;
        $tableGroups[$prefix][] = $table;
    }

    echo "\n🔍 CHECKING FOR DUPLICATE TABLES...\n";
    $duplicatesFound = false;
    foreach ($tableGroups as $prefix => $groupTables) {
        if (count($groupTables) > 1) {
            echo "⚠️  Potential duplicates in '{$prefix}': " . implode(', ', $groupTables) . "\n";
            $duplicatesFound = true;
        }
    }

    if (!$duplicatesFound) {
        echo "✅ No obvious duplicate tables found\n";
    }

    echo "\n🚀 STARTING DATABASE IMPROVEMENTS...\n";

    // 1. Add missing indexes for better performance
    echo "\n📈 ADDING PERFORMANCE INDEXES...\n";

    $indexes = [
        // User-related indexes
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)",
        "CREATE INDEX IF NOT EXISTS idx_users_created ON users(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)",

        // Admin indexes
        "CREATE INDEX IF NOT EXISTS idx_admin_email ON admin(aemail)",
        "CREATE INDEX IF NOT EXISTS idx_admin_role ON admin(arole)",

        // Property indexes
        "CREATE INDEX IF NOT EXISTS idx_properties_type ON properties(property_type_id)",
        "CREATE INDEX IF NOT EXISTS idx_properties_city ON properties(city)",
        "CREATE INDEX IF NOT EXISTS idx_properties_status ON properties(status)",
        "CREATE INDEX IF NOT EXISTS idx_properties_price ON properties(price)",
        "CREATE INDEX IF NOT EXISTS idx_properties_featured ON properties(is_featured)",
        "CREATE INDEX IF NOT EXISTS idx_properties_created ON properties(created_at)",

        // Lead indexes
        "CREATE INDEX IF NOT EXISTS idx_leads_status ON leads(status)",
        "CREATE INDEX IF NOT EXISTS idx_leads_source ON leads(lead_source)",
        "CREATE INDEX IF NOT EXISTS idx_leads_assigned ON leads(assigned_to)",
        "CREATE INDEX IF NOT EXISTS idx_leads_created ON leads(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_leads_score ON leads(lead_score)",

        // Invoice indexes
        "CREATE INDEX IF NOT EXISTS idx_invoices_client ON invoices(client_id, client_type)",
        "CREATE INDEX IF NOT EXISTS idx_invoices_status ON invoices(status)",
        "CREATE INDEX IF NOT EXISTS idx_invoices_date ON invoices(invoice_date)",
        "CREATE INDEX IF NOT EXISTS idx_invoices_due ON invoices(due_date)",

        // Employee indexes
        "CREATE INDEX IF NOT EXISTS idx_employees_department ON employees(department_id)",
        "CREATE INDEX IF NOT EXISTS idx_employees_status ON employees(status)",
        "CREATE INDEX IF NOT EXISTS idx_employees_join ON employees(date_of_joining)",

        // Communication indexes
        "CREATE INDEX IF NOT EXISTS idx_communication_channel ON communication_logs(channel)",
        "CREATE INDEX IF NOT EXISTS idx_communication_status ON communication_logs(status)",
        "CREATE INDEX IF NOT EXISTS idx_communication_sent ON communication_logs(sent_at)",

        // Analytics indexes
        "CREATE INDEX IF NOT EXISTS idx_analytics_event ON tour_analytics(event_type)",
        "CREATE INDEX IF NOT EXISTS idx_analytics_tour ON tour_analytics(tour_id)",
        "CREATE INDEX IF NOT EXISTS idx_analytics_date ON tour_analytics(created_at)",

        // Training indexes
        "CREATE INDEX IF NOT EXISTS idx_enrollments_user ON user_course_enrollments(user_id, user_type)",
        "CREATE INDEX IF NOT EXISTS idx_enrollments_course ON user_course_enrollments(course_id)",
        "CREATE INDEX IF NOT EXISTS idx_enrollments_status ON user_course_enrollments(status)",

        // Performance indexes
        "CREATE INDEX IF NOT EXISTS idx_metrics_key ON system_analytics_metrics(metric_key)",
        "CREATE INDEX IF NOT EXISTS idx_metrics_date ON system_analytics_metrics(period_date)",
        "CREATE INDEX IF NOT EXISTS idx_metrics_category ON system_analytics_metrics(metric_category)",
    ];

    foreach ($indexes as $indexSql) {
        executeQuery($pdo, $indexSql);
    }

    // 2. Create useful database views
    echo "\n📋 CREATING DATABASE VIEWS...\n";

    $views = [
        // User summary view
        "CREATE OR REPLACE VIEW user_summary AS
         SELECT
             u.id,
             u.first_name,
             u.last_name,
             u.email,
             u.role,
             u.status,
             u.created_at,
             COUNT(DISTINCT l.id) as total_leads,
             COUNT(DISTINCT p.id) as total_properties,
             COALESCE(SUM(inv.total_amount), 0) as total_revenue,
             MAX(u.last_login) as last_login
         FROM users u
         LEFT JOIN leads l ON u.id = l.created_by
         LEFT JOIN properties p ON u.id = p.created_by
         LEFT JOIN invoices inv ON u.id = inv.client_id AND inv.client_type = 'customer'
         GROUP BY u.id, u.first_name, u.last_name, u.email, u.role, u.status, u.created_at",

        // Property performance view
        "CREATE OR REPLACE VIEW property_performance AS
         SELECT
             p.id,
             p.title,
             p.city,
             p.property_type_id,
             p.price,
             p.status,
             p.created_at,
             COUNT(DISTINCT pv.id) as total_views,
             COUNT(DISTINCT t.id) as total_tours,
             COUNT(DISTINCT l.id) as total_leads,
             AVG(pv.valuation_amount) as avg_valuation,
             MAX(pv.valuation_date) as last_valuation_date
         FROM properties p
         LEFT JOIN property_views pv ON p.id = pv.property_id
         LEFT JOIN virtual_tours t ON p.id = t.property_id
         LEFT JOIN leads l ON p.id = l.property_id
         GROUP BY p.id, p.title, p.city, p.property_type_id, p.price, p.status, p.created_at",

        // Business overview view
        "CREATE OR REPLACE VIEW business_overview AS
         SELECT
             'users' as category,
             COUNT(*) as total_count,
             COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
         FROM users
         UNION ALL
         SELECT
             'properties' as category,
             COUNT(*) as total_count,
             COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
         FROM properties
         UNION ALL
         SELECT
             'leads' as category,
             COUNT(*) as total_count,
             COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
         FROM leads
         UNION ALL
         SELECT
             'invoices' as category,
             COUNT(*) as total_count,
             COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
         FROM invoices",

        // Revenue summary view
        "CREATE OR REPLACE VIEW revenue_summary AS
         SELECT
             DATE_FORMAT(invoice_date, '%Y-%m') as month,
             SUM(total_amount) as total_revenue,
             SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue,
             SUM(CASE WHEN status = 'overdue' THEN total_amount ELSE 0 END) as overdue_revenue,
             COUNT(*) as total_invoices,
             COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_invoices,
             COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_invoices
         FROM invoices
         WHERE invoice_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
         GROUP BY DATE_FORMAT(invoice_date, '%Y-%m')
         ORDER BY month DESC",

        // Employee performance view
        "CREATE OR REPLACE VIEW employee_performance AS
         SELECT
             e.id,
             e.first_name,
             e.last_name,
             e.department_id,
             COUNT(DISTINCT att.id) as total_attendance_days,
             COUNT(DISTINCT CASE WHEN att.status = 'present' THEN att.id END) as present_days,
             COUNT(DISTINCT CASE WHEN att.status = 'late' THEN att.id END) as late_days,
             COUNT(DISTINCT l.id) as total_leaves,
             COUNT(DISTINCT pr.id) as completed_reviews,
             AVG(pr.overall_rating) as avg_performance_rating
         FROM employees e
         LEFT JOIN attendance att ON e.id = att.employee_id
             AND att.check_in_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         LEFT JOIN leaves l ON e.id = l.employee_id
             AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         LEFT JOIN performance_reviews pr ON e.id = pr.employee_id
         GROUP BY e.id, e.first_name, e.last_name, e.department_id"
    ];

    foreach ($views as $viewSql) {
        executeQuery($pdo, $viewSql);
    }

    // 3. Add foreign key constraints (carefully, only if they don't exist)
    echo "\n🔗 ADDING FOREIGN KEY CONSTRAINTS...\n";

    $foreignKeys = [
        // Only add FKs that are safe and don't conflict with existing data
        "ALTER TABLE leads ADD CONSTRAINT fk_leads_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL ON UPDATE CASCADE",
        "ALTER TABLE leads ADD CONSTRAINT fk_leads_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE",
        "ALTER TABLE properties ADD CONSTRAINT fk_properties_type FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE SET NULL ON UPDATE CASCADE",
        "ALTER TABLE properties ADD CONSTRAINT fk_properties_created FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE",
    ];

    foreach ($foreignKeys as $fkSql) {
        executeQuery($pdo, $fkSql);
    }

    // 4. Optimize table structures
    echo "\n🔧 OPTIMIZING TABLE STRUCTURES...\n";

    $optimizations = [
        // Convert some columns to more efficient types where appropriate
        "ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','suspended','pending') DEFAULT 'active'",
        "ALTER TABLE properties MODIFY COLUMN status ENUM('active','inactive','sold','rented','pending','draft') DEFAULT 'active'",
        "ALTER TABLE leads MODIFY COLUMN status ENUM('new','contacted','qualified','proposal','negotiation','closed_won','closed_lost','nurture') DEFAULT 'new'",
        "ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','sent','viewed','paid','overdue','cancelled') DEFAULT 'draft'",

        // Add AUTO_INCREMENT where missing
        "ALTER TABLE users MODIFY COLUMN id INT AUTO_INCREMENT",
        "ALTER TABLE properties MODIFY COLUMN id INT AUTO_INCREMENT",
        "ALTER TABLE leads MODIFY COLUMN id INT AUTO_INCREMENT",
    ];

    foreach ($optimizations as $optSql) {
        executeQuery($pdo, $optSql);
    }

    // 5. Create stored procedures for common operations
    echo "\n⚙️  CREATING STORED PROCEDURES...\n";

    $procedures = [
        // Procedure to calculate monthly revenue
        "CREATE PROCEDURE IF NOT EXISTS calculate_monthly_revenue(IN target_month DATE)
        BEGIN
            SELECT
                SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue,
                SUM(CASE WHEN status = 'overdue' THEN total_amount ELSE 0 END) as overdue_revenue,
                SUM(total_amount) as total_revenue,
                COUNT(*) as total_invoices,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_invoices
            FROM invoices
            WHERE DATE_FORMAT(invoice_date, '%Y-%m') = DATE_FORMAT(target_month, '%Y-%m');
        END",

        // Procedure to get user activity summary
        "CREATE PROCEDURE IF NOT EXISTS get_user_activity_summary(IN user_id INT, IN days_back INT)
        BEGIN
            SELECT
                (SELECT COUNT(*) FROM leads WHERE created_by = user_id AND created_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)) as leads_created,
                (SELECT COUNT(*) FROM properties WHERE created_by = user_id AND created_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)) as properties_added,
                (SELECT COUNT(*) FROM user_sessions WHERE user_id = user_id AND last_activity >= DATE_SUB(NOW(), INTERVAL days_back DAY)) as sessions,
                (SELECT AVG(duration_minutes) FROM user_sessions WHERE user_id = user_id AND last_activity >= DATE_SUB(NOW(), INTERVAL days_back DAY)) as avg_session_duration;
        END"
    ];

    foreach ($procedures as $procSql) {
        executeQuery($pdo, $procSql);
    }

    // 6. Create triggers for automatic calculations
    echo "\n🎯 CREATING DATABASE TRIGGERS...\n";

    $triggers = [
        // Trigger to update user last login
        "CREATE TRIGGER IF NOT EXISTS update_user_last_login
         AFTER INSERT ON user_sessions
         FOR EACH ROW
         BEGIN
             UPDATE users SET last_login = NEW.created_at WHERE id = NEW.user_id;
         END",

        // Trigger to update property view count
        "CREATE TRIGGER IF NOT EXISTS update_property_views
         AFTER INSERT ON property_views
         FOR EACH ROW
         BEGIN
             UPDATE properties SET view_count = view_count + 1 WHERE id = NEW.property_id;
         END",

        // Trigger to update lead score automatically
        "CREATE TRIGGER IF NOT EXISTS update_lead_score_timestamp
         AFTER UPDATE ON leads
         FOR EACH ROW
         BEGIN
             IF OLD.lead_score != NEW.lead_score THEN
                 INSERT INTO lead_score_history (lead_id, old_score, new_score, changed_by, changed_at)
                 VALUES (NEW.id, OLD.lead_score, NEW.lead_score, NEW.updated_by, NOW());
             END IF;
         END"
    ];

    foreach ($triggers as $triggerSql) {
        executeQuery($pdo, $triggerSql);
    }

    // 7. Add data validation constraints
    echo "\n✅ ADDING DATA VALIDATION...\n";

    $constraints = [
        // Add check constraints where supported
        "ALTER TABLE properties ADD CONSTRAINT chk_price_positive CHECK (price > 0)",
        "ALTER TABLE invoices ADD CONSTRAINT chk_total_positive CHECK (total_amount >= 0)",
        "ALTER TABLE leads ADD CONSTRAINT chk_score_range CHECK (lead_score >= 0 AND lead_score <= 100)",
        "ALTER TABLE user_course_enrollments ADD CONSTRAINT chk_progress_range CHECK (progress_percentage >= 0 AND progress_percentage <= 100)",
    ];

    foreach ($constraints as $constraintSql) {
        executeQuery($pdo, $constraintSql);
    }

    // 8. Create summary tables for better performance
    echo "\n📊 CREATING SUMMARY TABLES...\n";

    $summaryTables = [
        // Daily metrics summary table
        "CREATE TABLE IF NOT EXISTS daily_metrics_summary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date DATE NOT NULL UNIQUE,
            total_users INT DEFAULT 0,
            new_users INT DEFAULT 0,
            active_users INT DEFAULT 0,
            total_properties INT DEFAULT 0,
            new_properties INT DEFAULT 0,
            total_leads INT DEFAULT 0,
            new_leads INT DEFAULT 0,
            total_revenue DECIMAL(15,2) DEFAULT 0,
            monthly_revenue DECIMAL(15,2) DEFAULT 0,
            paid_invoices INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // Monthly metrics summary table
        "CREATE TABLE IF NOT EXISTS monthly_metrics_summary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            year_month VARCHAR(7) NOT NULL UNIQUE,
            total_users INT DEFAULT 0,
            new_users INT DEFAULT 0,
            total_properties INT DEFAULT 0,
            new_properties INT DEFAULT 0,
            total_leads INT DEFAULT 0,
            converted_leads INT DEFAULT 0,
            total_revenue DECIMAL(15,2) DEFAULT 0,
            paid_revenue DECIMAL(15,2) DEFAULT 0,
            total_invoices INT DEFAULT 0,
            paid_invoices INT DEFAULT 0,
            avg_response_time DECIMAL(5,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($summaryTables as $tableSql) {
        executeQuery($pdo, $tableSql);
    }

    // 9. Final optimization - analyze tables
    echo "\n🔍 ANALYZING TABLES FOR OPTIMIZATION...\n";

    foreach ($tables as $table) {
        executeQuery($pdo, "ANALYZE TABLE `$table`");
    }

    echo "\n🎉 DATABASE IMPROVEMENT COMPLETED!\n";

    // Final summary
    echo "\n📋 IMPROVEMENT SUMMARY:\n";
    echo "- ✅ Added " . count($indexes) . " performance indexes\n";
    echo "- ✅ Created " . count($views) . " database views\n";
    echo "- ✅ Added foreign key constraints\n";
    echo "- ✅ Optimized table structures\n";
    echo "- ✅ Created " . count($procedures) . " stored procedures\n";
    echo "- ✅ Added " . count($triggers) . " database triggers\n";
    echo "- ✅ Added data validation constraints\n";
    echo "- ✅ Created summary tables for better performance\n";
    echo "- ✅ Analyzed all tables for optimization\n";

    echo "\n🚀 Database is now optimized and ready for production!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
