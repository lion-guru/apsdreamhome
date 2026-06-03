<?php
/**
 * Smart Database Consolidation & Analysis
 * Phase 1: Empty Tables Analysis & Purpose Documentation
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== SMART DATABASE ANALYSIS ===\n\n";

// Get all tables
$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Categorize all tables
$categories = [
    'USER_MGMT' => [],        // User management
    'CRM_LEADS' => [],        // Lead management
    'PROPERTY' => [],         // Property listings
    'PLOT_LAND' => [],        // Colony/Land development
    'PAYMENT' => [],          // Payments & Finance
    'MLM_NETWORK' => [],      // MLM & Referrals
    'AI_ML' => [],            // AI features
    'ERP' => [],              // ERP modules
    'MARKETING' => [],        // Marketing
    'COMMUNICATION' => [],    // Email, SMS, WhatsApp
    'SETTINGS' => [],         // Config & Settings
    'LOGS_AUDIT' => [],       // Logs & Audit
    'CACHE_TEMP' => [],       // Cache & Temp
    'ANALYTICS' => [],        // Analytics
    'TESTING' => [],          // Testing
    'UNKNOWN' => []           // Uncategorized
];

// Pattern-based categorization
$patterns = [
    'USER_MGMT' => ['user', 'customer', 'admin', 'agent', 'associate', 'employee', 'member', 'kyc', 'permission', 'role'],
    'CRM_LEADS' => ['lead', 'inquiry', 'contact', 'opportunity', 'pipeline', 'activity', 'note', 'deal', 'visit', 'score', 'tag', 'source', 'status'],
    'PROPERTY' => ['property', 'listing', 'gallery', 'image', 'feature', 'amenity', 'category', 'type', 'valuation', 'rating', 'review', 'favorite', 'saved', 'comparison', 'view', 'booking'],
    'PLOT_LAND' => ['plot', 'colony', 'site', 'land', 'gata', 'sector', 'block', 'possession', 'booking'],
    'PAYMENT' => ['payment', 'transaction', 'invoice', 'receipt', 'refund', 'commission', 'payout', 'wallet', 'bank', 'loan', 'emi', 'salary'],
    'MLM_NETWORK' => ['mlm', 'tree', 'network', 'referral', 'level', 'rank', 'sponsor', 'binary', 'matrix', 'downline', 'upline'],
    'AI_ML' => ['ai_', 'chatbot', 'bot_', 'conversation', 'knowledge', 'workflow', 'agent', 'suggestion', 'recommendation', 'learning', 'context', 'memory'],
    'ERP' => ['project', 'task', 'attendance', 'leave', 'department', 'designation', 'shift', 'schedule', 'document', 'inventory', 'purchase', 'expense', 'budget'],
    'MARKETING' => ['campaign', 'campaign_', 'newsletter', 'subscriber', 'popup', 'notification', 'notification_', 'email', 'sms', 'whatsapp', 'advertisement'],
    'COMMUNICATION' => ['email', 'sms', 'whatsapp', 'notification', 'message', 'chat', 'conversation', 'template'],
    'SETTINGS' => ['setting', 'config', 'configuration', 'preference'],
    'LOGS_AUDIT' => ['log', 'audit', 'history', 'trail'],
    'CACHE_TEMP' => ['cache', 'temp', 'tmp', 'session'],
    'ANALYTICS' => ['analytics', 'metric', 'report', 'dashboard', 'conversion', 'event', 'page_view'],
    'TESTING' => ['test', 'visual_', 'test_']
];

foreach ($allTables as $table) {
    $categorized = false;
    foreach ($patterns as $category => $pats) {
        foreach ($pats as $pat) {
            if (stripos($table, $pat) !== false) {
                $categories[$category][] = $table;
                $categorized = true;
                break 2;
            }
        }
    }
    if (!$categorized) {
        $categories['UNKNOWN'][] = $table;
    }
}

// Get row counts for all tables
function getCounts($pdo, $tables) {
    $counts = [];
    foreach ($tables as $t) {
        try {
            $counts[$t] = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        } catch (Exception $e) {
            $counts[$t] = -1; // Broken
        }
    }
    return $counts;
}

echo "=== CATEGORY BREAKDOWN ===\n\n";

$totalTables = 0;
$totalRows = 0;
$categoryReport = [];

foreach ($categories as $cat => $tables) {
    if (empty($tables)) continue;
    
    $counts = getCounts($pdo, $tables);
    $active = array_filter($counts, fn($c) => $c > 0);
    $empty = array_filter($counts, fn($c) => $c == 0);
    $broken = array_filter($counts, fn($c) => $c < 0);
    $totalRowsInCat = array_sum($counts);
    
    $totalTables += count($tables);
    $totalRows += $totalRowsInCat;
    
    $categoryReport[$cat] = [
        'total' => count($tables),
        'active' => count($active),
        'empty' => count($empty),
        'broken' => count($broken),
        'rows' => $totalRowsInCat,
        'tables' => $tables,
        'counts' => $counts
    ];
}

// Sort by importance
$priority = ['USER_MGMT', 'CRM_LEADS', 'PROPERTY', 'PLOT_LAND', 'PAYMENT', 'MLM_NETWORK', 'AI_ML', 'ERP', 'MARKETING', 'COMMUNICATION', 'SETTINGS', 'LOGS_AUDIT', 'ANALYTICS', 'CACHE_TEMP', 'TESTING', 'UNKNOWN'];

echo str_repeat("=", 70) . "\n";
printf("%-20s | %-8s | %-8s | %-8s | %-10s\n", "CATEGORY", "TOTAL", "ACTIVE", "EMPTY", "ROWS");
echo str_repeat("=", 70) . "\n";

foreach ($priority as $cat) {
    if (!isset($categoryReport[$cat]) || $categoryReport[$cat]['total'] == 0) continue;
    
    $r = $categoryReport[$cat];
    $status = $r['empty'] == $r['total'] ? "🗑️" : ($r['active'] > 0 ? "✅" : "❓");
    printf("%s %-17s | %-8d | %-8d | %-8d | %-10d\n", 
        $status, $cat, $r['total'], $r['active'], $r['empty'], $r['rows']);
}

echo str_repeat("=", 70) . "\n";
printf("%-20s | %-8d | %-8d | %-8d | %-10d\n", "TOTAL", $totalTables, $totalTables - count(array_filter($categoryReport, fn($r) => $r['empty'] == $r['total'] && $r['broken'] == 0)), 0, $totalRows);

echo "\n\n=== DETAILED EMPTY TABLES BY CATEGORY ===\n\n";

foreach ($priority as $cat) {
    if (!isset($categoryReport[$cat])) continue;
    $r = $categoryReport[$cat];
    $emptyTables = array_keys(array_filter($r['counts'], fn($c) => $c == 0));
    
    if (!empty($emptyTables)) {
        echo "🔹 $cat (" . count($emptyTables) . " empty tables)\n";
        foreach ($emptyTables as $t) {
            echo "   - $t\n";
        }
        echo "\n";
    }
}

// =====================================================
// CONSOLIDATION RECOMMENDATIONS
// =====================================================

echo "\n=== CONSOLIDATION RECOMMENDATIONS ===\n\n";

echo "1. MERGE CANDIDATES:\n";
echo "   ─────────────────────────────────────────────────────\n";
echo "   • users + customers + admin_users → users (with role column)\n";
echo "   • leads + lead_* tables → Keep separate (different purposes)\n";
echo "   • properties + plots + plot_master → Keep separate (different purposes)\n";
echo "   • ai_* tables → Keep (AI feature ecosystem)\n";
echo "   • mlm_* tables → Keep (MLM feature ecosystem)\n\n";

echo "2. DELETE EMPTY TABLES (Safe to remove):\n";
echo "   ─────────────────────────────────────────────────────\n";

$safeDelete = [];
foreach ($categoryReport as $cat => $r) {
    if (in_array($cat, ['CACHE_TEMP', 'TESTING'])) {
        foreach (array_keys(array_filter($r['counts'], fn($c) => $c == 0)) as $t) {
            $safeDelete[] = $t;
        }
    }
}

foreach (array_slice($safeDelete, 0, 20) as $t) {
    echo "   🗑️ $t\n";
}
if (count($safeDelete) > 20) echo "   ... and " . (count($safeDelete) - 20) . " more\n\n";

echo "3. KEEP AS-IS (Features need these):\n";
echo "   ─────────────────────────────────────────────────────\n";
echo "   • USER_MGMT: Role-based access, KYC management\n";
echo "   • CRM_LEADS: Lead scoring, pipeline, activities\n";
echo "   • PLOT_LAND: Colony development, land records (Gata)\n";
echo "   • PAYMENT: EMI, salary, commissions\n";
echo "   • MLM_NETWORK: Binary tree, referrals, ranks\n";
echo "   • AI_ML: Smart automation, chatbot, suggestions\n\n";

echo "4. UNUSED BUT POTENTIALLY NEEDED:\n";
echo "   ─────────────────────────────────────────────────────\n";
echo "   • performance_* (8 tables) - Performance reviews\n";
echo "   • security_* (5 tables) - Security audit\n";
echo "   • analytics_* (10 tables) - Analytics tracking\n";
echo "   • notification_* (7 tables) - Push notifications\n\n";

echo "5. DATABASE SCHEMA RECOMMENDATIONS:\n";
echo "   ─────────────────────────────────────────────────────\n";
echo "   • Add 'deleted_at' soft delete to all main tables\n";
echo "   • Add 'tenant_id' for multi-tenancy support\n";
echo "   • Add 'metadata' JSON column for flexibility\n";
echo "   • Standardize timestamps: created_at, updated_at, deleted_at\n";
echo "   • Use UUIDs for external-facing IDs\n";
echo "   • Add indexes on foreign keys and commonly queried columns\n\n";

// Generate SQL for consolidation
echo "\n=== SUGGESTED SCHEMA CHANGES ===\n\n";
?>
