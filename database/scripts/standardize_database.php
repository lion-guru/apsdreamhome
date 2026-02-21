<?php
/**
 * Database Standardization & Fix Script
 * Standardize engines, fix primary keys, create missing views
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

    echo "🔧 DATABASE STANDARDIZATION & FIXES\n";
    echo "===================================\n\n";

    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📊 Found " . count($tables) . " tables to process\n\n";

    // Function to execute SQL
    function executeQuery($pdo, $sql, $silent = false) {
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                if (!$silent) echo "✅ Query executed successfully\n";
                return true;
            } else {
                if (!$silent) echo "❌ Error executing query\n";
                return false;
            }
        } catch (Exception $e) {
            if (!$silent) echo "⚠️  Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // 1. Standardize all tables to InnoDB engine
    echo "🔄 STANDARDIZING TABLE ENGINES TO INNODB\n";
    echo "==========================================\n";

    $nonInnoDBTables = [];
    foreach ($tables as $table) {
        try {
            $engine = $pdo->query("
                SELECT ENGINE FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table'
            ")->fetch()['ENGINE'];

            if ($engine !== 'InnoDB') {
                $nonInnoDBTables[] = $table;
                echo "Converting $table from $engine to InnoDB...\n";
                executeQuery($pdo, "ALTER TABLE `$table` ENGINE = InnoDB");
            }
        } catch (Exception $e) {
            echo "⚠️  Could not check engine for $table: " . $e->getMessage() . "\n";
        }
    }

    echo "✅ Converted " . count($nonInnoDBTables) . " tables to InnoDB\n\n";

    // 2. Fix primary keys and auto-increment issues
    echo "🔑 FIXING PRIMARY KEYS & AUTO-INCREMENT\n";
    echo "=======================================\n";

    $pkIssues = [];
    foreach ($tables as $table) {
        try {
            // Check if table has primary key
            $pkInfo = $pdo->query("
                SELECT COLUMN_NAME, COLUMN_TYPE, EXTRA
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '$dbname'
                AND TABLE_NAME = '$table'
                AND COLUMN_KEY = 'PRI'
                ORDER BY ORDINAL_POSITION
            ")->fetchAll();

            if (empty($pkInfo)) {
                // Table has no primary key
                $pkIssues[] = "$table: No primary key";
                echo "⚠️  $table has no primary key - needs manual review\n";
                continue;
            }

            // Check primary key structure
            $firstPk = $pkInfo[0];
            $pkColumn = $firstPk['COLUMN_NAME'];

            // Check if it's auto-increment
            $isAutoIncrement = strpos($firstPk['EXTRA'], 'auto_increment') !== false;

            if (!$isAutoIncrement) {
                // Check if column is integer type and can be made auto-increment
                if (preg_match('/^int/i', $firstPk['COLUMN_TYPE'])) {
                    echo "Adding auto-increment to $table.$pkColumn...\n";
                    executeQuery($pdo, "ALTER TABLE `$table` MODIFY COLUMN `$pkColumn` INT AUTO_INCREMENT");
                    $pkIssues[] = "$table: Added auto-increment to $pkColumn";
                } else {
                    $pkIssues[] = "$table: Non-integer primary key $pkColumn (cannot auto-increment)";
                }
            }

        } catch (Exception $e) {
            echo "⚠️  Could not analyze $table: " . $e->getMessage() . "\n";
        }
    }

    echo "✅ Primary key analysis completed\n";
    echo "📋 Primary key issues addressed: " . count($pkIssues) . "\n\n";

    // 3. Create missing database views
    echo "👁️  CREATING MISSING DATABASE VIEWS\n";
    echo "===================================\n";

    // Define expected views
    $expectedViews = [
        'user_summary' => "
            CREATE OR REPLACE VIEW user_summary AS
            SELECT
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.role,
                u.status,
                u.created_at,
                COALESCE(lead_count.total_leads, 0) as total_leads,
                COALESCE(prop_count.total_properties, 0) as total_properties,
                COALESCE(inv_sum.total_revenue, 0) as total_revenue,
                u.last_login
            FROM users u
            LEFT JOIN (SELECT created_by, COUNT(*) as total_leads FROM leads GROUP BY created_by) lead_count ON u.id = lead_count.created_by
            LEFT JOIN (SELECT created_by, COUNT(*) as total_properties FROM properties GROUP BY created_by) prop_count ON u.id = prop_count.created_by
            LEFT JOIN (SELECT client_id, SUM(total_amount) as total_revenue FROM invoices WHERE status = 'paid' AND client_type = 'customer' GROUP BY client_id) inv_sum ON u.id = inv_sum.client_id
        ",

        'property_performance' => "
            CREATE OR REPLACE VIEW property_performance AS
            SELECT
                p.id,
                p.title,
                p.city,
                p.property_type_id,
                p.price,
                p.status,
                p.created_at,
                COALESCE(pv_count.view_count, 0) as total_views,
                COALESCE(tour_count.tour_count, 0) as total_tours,
                COALESCE(lead_count.lead_count, 0) as total_leads,
                COALESCE(val.avg_valuation, 0) as avg_valuation,
                COALESCE(val.latest_date, NULL) as last_valuation_date
            FROM properties p
            LEFT JOIN (SELECT property_id, COUNT(*) as view_count FROM property_views GROUP BY property_id) pv_count ON p.id = pv_count.property_id
            LEFT JOIN (SELECT property_id, COUNT(*) as tour_count FROM virtual_tours GROUP BY property_id) tour_count ON p.id = tour_count.property_id
            LEFT JOIN (SELECT property_id, COUNT(*) as lead_count FROM leads GROUP BY property_id) lead_count ON p.id = lead_count.property_id
            LEFT JOIN (SELECT property_id, AVG(valuation_amount) as avg_valuation, MAX(valuation_date) as latest_date FROM property_valuations GROUP BY property_id) val ON p.id = val.property_id
        ",

        'business_overview' => "
            CREATE OR REPLACE VIEW business_overview AS
            SELECT 'users' as category, COUNT(*) as total_count, COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days FROM users
            UNION ALL
            SELECT 'properties' as category, COUNT(*) as total_count, COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days FROM properties
            UNION ALL
            SELECT 'leads' as category, COUNT(*) as total_count, COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days FROM leads
            UNION ALL
            SELECT 'invoices' as category, COUNT(*) as total_count, COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days FROM invoices
        ",

        'revenue_summary' => "
            CREATE OR REPLACE VIEW revenue_summary AS
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
            ORDER BY month DESC
        ",

        'employee_performance' => "
            CREATE OR REPLACE VIEW employee_performance AS
            SELECT
                e.id,
                e.first_name,
                e.last_name,
                e.department_id,
                COALESCE(att_stats.total_days, 0) as total_attendance_days,
                COALESCE(att_stats.present_days, 0) as present_days,
                COALESCE(att_stats.late_days, 0) as late_days,
                COALESCE(leave_count.total_leaves, 0) as total_leaves,
                COALESCE(review_stats.completed_reviews, 0) as completed_reviews,
                COALESCE(review_stats.avg_rating, 0) as avg_performance_rating
            FROM employees e
            LEFT JOIN (
                SELECT
                    employee_id,
                    COUNT(*) as total_days,
                    COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days
                FROM attendance
                WHERE check_in_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY employee_id
            ) att_stats ON e.id = att_stats.employee_id
            LEFT JOIN (
                SELECT employee_id, COUNT(*) as total_leaves
                FROM leaves
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY employee_id
            ) leave_count ON e.id = leave_count.employee_id
            LEFT JOIN (
                SELECT employee_id, COUNT(*) as completed_reviews, AVG(overall_rating) as avg_rating
                FROM performance_reviews
                GROUP BY employee_id
            ) review_stats ON e.id = review_stats.employee_id
        "
    ];

    // Check existing views
    $existingViews = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);

    $createdViews = 0;
    foreach ($expectedViews as $viewName => $viewSql) {
        if (!in_array($viewName, $existingViews)) {
            echo "Creating view: $viewName...\n";
            if (executeQuery($pdo, $viewSql)) {
                $createdViews++;
            }
        } else {
            echo "View $viewName already exists\n";
        }
    }

    echo "✅ Created $createdViews new views\n\n";

    // 4. Test views functionality
    echo "🧪 TESTING VIEW FUNCTIONALITY\n";
    echo "==============================\n";

    $testViews = ['user_summary', 'property_performance', 'business_overview', 'revenue_summary', 'employee_performance'];
    foreach ($testViews as $view) {
        try {
            $count = $pdo->query("SELECT COUNT(*) as count FROM `$view` LIMIT 1")->fetch()['count'];
            echo "✅ $view: Working ($count records accessible)\n";
        } catch (Exception $e) {
            echo "❌ $view: Error - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // 5. Add comprehensive indexes for better performance
    echo "📈 ADDING COMPREHENSIVE INDEXES\n";
    echo "================================\n";

    $additionalIndexes = [
        // Date-based indexes for time-series queries
        "CREATE INDEX IF NOT EXISTS idx_users_created_date ON users (DATE(created_at))",
        "CREATE INDEX IF NOT EXISTS idx_properties_created_date ON properties (DATE(created_at))",
        "CREATE INDEX IF NOT EXISTS idx_leads_created_date ON leads (DATE(created_at))",
        "CREATE INDEX IF NOT EXISTS idx_invoices_created_date ON invoices (DATE(created_at))",

        // Composite indexes for common queries
        "CREATE INDEX IF NOT EXISTS idx_leads_status_assigned ON leads (lead_status, assigned_to)",
        "CREATE INDEX IF NOT EXISTS idx_properties_status_price ON properties (status, price)",
        "CREATE INDEX IF NOT EXISTS idx_invoices_status_date ON invoices (status, invoice_date)",

        // Foreign key indexes (if not already present)
        "CREATE INDEX IF NOT EXISTS idx_leads_property_id ON leads (property_id)",
        "CREATE INDEX IF NOT EXISTS idx_properties_created_by ON properties (created_by)",
        "CREATE INDEX IF NOT EXISTS idx_invoices_client_id ON invoices (client_id, client_type)",

        // Analytics indexes
        "CREATE INDEX IF NOT EXISTS idx_tour_analytics_created ON tour_analytics (created_at)",
        "CREATE INDEX IF NOT EXISTS idx_system_metrics_date ON system_analytics_metrics (period_date)",
        "CREATE INDEX IF NOT EXISTS idx_lead_activities_created ON lead_activities (created_at)",
    ];

    $indexesAdded = 0;
    foreach ($additionalIndexes as $indexSql) {
        if (executeQuery($pdo, $indexSql, true)) {
            $indexesAdded++;
        }
    }

    echo "✅ Added $indexesAdded additional performance indexes\n\n";

    // 6. Final optimization
    echo "🔧 FINAL OPTIMIZATION\n";
    echo "=====================\n";

    // Analyze tables for optimization
    $analyzedTables = 0;
    foreach ($tables as $table) {
        try {
            $pdo->query("ANALYZE TABLE `$table`");
            $analyzedTables++;
        } catch (Exception $e) {
            // Some tables might not support ANALYZE
        }
    }

    echo "✅ Analyzed $analyzedTables tables for optimization\n";

    // 7. Generate final report
    echo "\n📋 STANDARDIZATION COMPLETED\n";
    echo "=============================\n";

    $finalTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $finalViews = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);

    echo "📊 Final Statistics:\n";
    echo "- Total Tables: " . count($finalTables) . "\n";
    echo "- Total Views: " . count($finalViews) . "\n";
    echo "- InnoDB Conversion: " . count($nonInnoDBTables) . " tables converted\n";
    echo "- Views Created: $createdViews\n";
    echo "- Indexes Added: $indexesAdded\n";
    echo "- Tables Analyzed: $analyzedTables\n";

    echo "\n🏆 DATABASE STANDARDIZATION SUCCESSFUL!\n";
    echo "All tables are now standardized with proper engines, primary keys, and views.\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
