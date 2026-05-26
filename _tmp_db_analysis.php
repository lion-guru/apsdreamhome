<?php
/**
 * Database Schema Analysis Script
 * Research only - does NOT modify anything
 */
$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = '';
$db   = 'apsdreamhome';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$total = count($tables);
echo "=== APS DREAM HOME DATABASE ANALYSIS ===\n";
echo "Database: $db\n";
echo "Total Tables: $total\n\n";

// For each table, get CREATE TABLE and COUNT
$results = [];

echo "TABLE_SCHEMAS_START\n";
foreach ($tables as $table) {
    // Create table
    $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
    $row = $stmt->fetch();
    $createSQL = $row['Create Table'] ?? 'N/A';
    
    // Row count
    $cnt = $pdo->query("SELECT COUNT(*) AS cnt FROM `$table`")->fetch();
    $count = $cnt['cnt'];
    
    // Get column count
    $cols = $pdo->query("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table'")->fetch();
    $colCount = $cols['cnt'];
    
    // Get index info
    $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll();
    $indexCount = count($indexes);
    $hasPK = false;
    $hasFK = false;
    foreach ($indexes as $idx) {
        if ($idx['Key_name'] === 'PRIMARY') $hasPK = true;
    }
    
    // Check for FK constraints
    $fkStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '$db' AND TABLE_NAME = '$table' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
    $fkCount = $fkStmt->fetch()['cnt'];
    if ($fkCount > 0) $hasFK = true;
    
    // Check if empty
    $isEmpty = ($count == 0);
    
    $results[$table] = [
        'createSQL' => $createSQL,
        'count' => $count,
        'colCount' => $colCount,
        'indexCount' => $indexCount,
        'hasPK' => $hasPK,
        'hasFK' => $hasFK,
        'isEmpty' => $isEmpty,
    ];
    
    echo "=== $table ===\n";
    echo "Rows: $count | Columns: $colCount | Indexes: $indexCount | PK: " . ($hasPK ? 'Y' : 'N') . " | FK: " . ($hasFK ? 'Y' : 'N') . " | Empty: " . ($isEmpty ? 'Y' : 'N') . "\n";
    echo $createSQL . "\n\n";
}
echo "TABLE_SCHEMAS_END\n";

// === ANALYSIS ===
echo "\n\n========================================\n";
echo "          ANALYSIS & FINDINGS           \n";
echo "========================================\n\n";

// 1. Tables with <= 3 columns
echo "--- TABLES WITH <= 3 COLUMNS (potential merge candidates) ---\n";
$smallTables = [];
foreach ($results as $table => $info) {
    if ($info['colCount'] <= 3) {
        $smallTables[] = $table;
        echo "  $table ({$info['colCount']} cols, {$info['count']} rows)\n";
    }
}
echo "Total: " . count($smallTables) . "\n\n";

// 2. Empty tables (0 rows)
echo "--- EMPTY TABLES (0 rows) ---\n";
$emptyTables = [];
foreach ($results as $table => $info) {
    if ($info['isEmpty']) {
        $emptyTables[] = $table;
    }
}
echo "Total: " . count($emptyTables) . "\n";
// Show them in groups of 20
$chunks = array_chunk($emptyTables, 20);
foreach ($chunks as $chunk) {
    echo "  " . implode(', ', $chunk) . "\n";
}
echo "\n";

// 3. Tables without PRIMARY KEY
echo "--- TABLES WITHOUT PRIMARY KEY ---\n";
$noPK = [];
foreach ($results as $table => $info) {
    if (!$info['hasPK']) {
        $noPK[] = $table;
        echo "  $table ({$info['colCount']} cols, {$info['count']} rows)\n";
    }
}
echo "Total: " . count($noPK) . "\n\n";

// 4. Tables without FK relationships (potential orphans)
echo "--- TABLES WITHOUT FK CONSTRAINTS (potentially orphaned) ---\n";
$noFK = [];
foreach ($results as $table => $info) {
    if (!$info['hasFK']) {
        $noFK[] = $table;
    }
}
echo "Total: " . count($noFK) . " (out of $total)\n\n";

// 5. Similar name grouping
echo "--- SIMILAR NAME GROUPS (potential duplicates/redundancy) ---\n";
$groups = [];
foreach ($tables as $table) {
    // Extract base name by stripping common prefixes/suffixes
    $base = preg_replace('/^(ai_|mlm_|user_|admin_|property_|plot_|lead_|commission_|payment_|salary_|employee_|training_|api_|notification_|system_|analytics_|performance_|bank_|farmer_|legal_|document_|email_|sms_|chatbot_|gst_|whatsapp_|mcp_|visitor_)/i', '', $table);
    $base = preg_replace('/_(logs|history|settings|config|analytics|records|data|backup|legacy|temp|archive|v2)$/i', '', $base);
    $groups[$base][] = $table;
}

// Show groups with 3+ members
foreach ($groups as $base => $members) {
    if (count($members) >= 3) {
        echo "  Base '$base': " . implode(', ', $members) . "\n";
    }
}
echo "\n";

// 6. Module grouping
echo "--- MODULE GROUPING ---\n";
$modules = [
    'Core/System' => ['settings', 'config', 'system_', 'app_config', 'cache_', 'sessions', 'migration', 'schema_', 'audit_', 'log$', '_logs', 'activity_log', 'error_logs', 'event_log', 'security_', 'backup_', 'crash_', 'sync_', 'webhook', 'async_', 'jobs', 'tasks', 'file_', 'files', 'media', 'uploads', 'downloads', 'cron_', 'scheduled_'],
    'User/Auth' => ['users', 'customers', 'associates', 'agents', 'admins', 'employees', 'roles', 'permissions', 'login_', 'password_', 'auth_', 'remember_', 'jwt_', 'otp_', 'two_factor', 'social_accounts', 'user_', 'customer_', 'team', 'member'],
    'Property/RealEstate' => ['properties', 'property_', 'real_estate_', 'rental_', 'resale_', 'resell_', 'saved_propert', 'favorites', 'customer_favorites', 'customer_wishlist', 'comparison', 'price_history', 'mortgage_', 'home_loan'],
    'Colony/Plot/Site' => ['colonies', 'plots', 'plot_', 'site_', 'sites', 'gata_', 'kissan_', 'kisaan_', 'colony', 'plotting', 'project_', 'construction_', 'land_', 'colony'],
    'MLM/Network' => ['mlm_', 'commission_', 'payout', 'network_tree', 'referral', 'sponsor_', 'rank_', 'level', 'bonus', 'hybrid_commission', 'traditional_commission'],
    'Finance/Payment' => ['payment', 'invoice', 'transaction', 'accounting_', 'bank_', 'wallet_', 'emi', 'loan', 'tax_', 'gst_', 'budget', 'expense', 'income_', 'financial_', 'journal_', 'chart_of_', 'cash_flow', 'revenue', 'booking_payment'],
    'CRM/Lead' => ['leads', 'lead_', 'crm_', 'inquiries', 'enquiries', 'contact_', 'follow_up', 'interaction', 'communication', 'conversation', 'chat_message', 'ticket', 'feedback_', 'support_', 'visitor_'],
    'Marketing' => ['campaign', 'marketing_', 'newsletter', 'email_', 'sms_', 'whatsapp_', 'push_', 'notification_', 'ad_', 'popup', 'seo_', 'social_media'],
    'HR/Employee' => ['employee_', 'attendance', 'leave_', 'payroll_', 'salary_', 'hrm_', 'hr_', 'kpi', 'performance_', 'shift_', 'training_', 'onboarding'],
    'Content/Blog' => ['blog', 'news', 'pages', 'page_', 'gallery', 'image', 'media_library', 'faqs', 'testimonials', 'content_', 'translation', 'language', 'dynamic_', 'footer', 'header'],
    'AI/Automation' => ['ai_', 'chatbot_', 'smart_', 'automation', 'workflow_', 'predictive', 'recommendation', 'bot_', 'voice_', 'ocr_', 'virtual_tour', 'ar_vr'],
    'Reports/Analytics' => ['analytics_', 'report_', 'dashboard_', 'metrics', 'statistics', 'kpi', 'performance_', 'traffic_', 'heatmap', 'summary'],
    'Settings/Config' => ['config', 'settings', 'preferences', 'options', 'integration', 'api_', 'mcp_', 'third_party'],
    'Other/Unknown' => ['_'],
];

$moduleMap = [];
foreach ($tables as $table) {
    $assigned = false;
    foreach ($modules as $module => $patterns) {
        if ($module === 'Other/Unknown') continue;
        foreach ($patterns as $pattern) {
            // Exact match or prefix match
            if ($pattern === $table || fnmatch($pattern, $table) || strpos($table, rtrim($pattern, '_')) === 0) {
                // Check if it's already assigned
                if (!isset($moduleMap[$module])) $moduleMap[$module] = [];
                $moduleMap[$module][] = $table;
                $assigned = true;
                break 2;
            }
        }
    }
    if (!$assigned) {
        $moduleMap['Other/Unknown'][] = $table;
    }
}

foreach ($moduleMap as $module => $tbls) {
    echo "  $module: " . count($tbls) . " tables\n";
    foreach (array_chunk($tbls, 15) as $chunk) {
        echo "    " . implode(', ', $chunk) . "\n";
    }
}
echo "\n";

// 7. Category/Type tables that could be ENUMs
echo "--- TABLES THAT COULD BE ENUMS (type/status/category) ---\n";
$enumCandidates = [];
foreach ($tables as $table) {
    $lower = strtolower($table);
    if (preg_match('/_(types?|status(es)?|categories?|sources?|stages?|levels?|plans?|reasons?|channels?|methods?|templates?|preferences?|config|settings)$/', $lower)) {
        $info = $results[$table];
        echo "  $table ({$info['colCount']} cols, {$info['count']} rows) - " . ($info['colCount'] <= 4 ? "Small (candidate)" : "Larger") . "\n";
        $enumCandidates[] = $table;
    }
}
echo "Total candidate: " . count($enumCandidates) . "\n\n";

// Summary statistics
echo "========================================\n";
echo "          SUMMARY STATISTICS           \n";
echo "========================================\n";
echo "Total Tables: $total\n";
echo "Empty Tables (0 rows): " . count($emptyTables) . " (" . round(count($emptyTables)/$total*100, 1) . "%)\n";
echo "Tables <= 3 columns: " . count($smallTables) . "\n";
echo "Tables without PK: " . count($noPK) . "\n";
echo "Tables without FK: " . count($noFK) . " (" . round(count($noFK)/$total*100, 1) . "%)\n";

$totalRows = 0;
foreach ($results as $info) {
    $totalRows += $info['count'];
}
echo "Total Rows (all tables): $totalRows\n";

// Module count summary
echo "\nModule Distribution:\n";
foreach ($moduleMap as $module => $tbls) {
    echo "  $module: " . count($tbls) . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
