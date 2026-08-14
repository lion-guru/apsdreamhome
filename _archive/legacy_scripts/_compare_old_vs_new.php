<?php
// Compare old backup (809 tables) vs current DB (213 tables)
$old = json_decode(file_get_contents('_old_db_schema.json'), true);

// Get current DB tables
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$current = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Get row count + column info for each current table
$now = [];
foreach ($current as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
    $now[$t] = ['rows' => $rows, 'cols' => $cols, 'col_count' => count($cols)];
}

$oldNames = array_keys($old);
$dropped = array_diff($oldNames, $current);
$survived = array_intersect($oldNames, $current);
$added = array_diff($current, $oldNames);

echo "=== OLD (May 27 backup) vs CURRENT (Jun 3) ===\n";
echo "Old tables: " . count($old) . "\n";
echo "Current tables: " . count($current) . "\n";
echo "DROPPED: " . count($dropped) . "\n";
echo "SURVIVED: " . count($survived) . "\n";
echo "ADDED since backup: " . count($added) . "\n\n";

echo "=== ADDED tables (since May 27) ===\n";
foreach ($added as $a) echo "  + $a (" . $now[$a]['col_count'] . " cols, " . $now[$a]['rows'] . " rows)\n";

// Categorize dropped tables by PURPOSE (column analysis)
echo "\n=== DROPPED tables categorized by PURPOSE (inferred from columns) ===\n";

$purposeMap = [
    'ai|machine learning|recommendation|chatbot|context|memory|learning|prediction|ai_' => 'AI/ML',
    'voice|call|ai_call|ai_calling|telecall|telephony' => 'Voice AI/Calling',
    'mlm|commission|payout|wallet|network|referral|sponsor|associate|tier|rank|level' => 'MLM/Network/Commission',
    'property|plot|resell|site|virtual_tour|tour_' => 'Property/Plot',
    'lead|crm|customer_|inquiry|incomplete_registr|progressive_registr|follow_up|customer_alert' => 'Lead/CRM',
    'booking|visit|site_visit|visitor_|lead_visit' => 'Booking/Visit',
    'employee|salary|attendance|payroll|shift|team|leave|department|job|HR' => 'HRM/Payroll',
    'document|file|ocr|generated_doc|attachment|legal_doc|business_doc|employee_doc|user_doc|customer_doc' => 'Document/File',
    'notification|email|sms|whatsapp|push|realtime|chat_messages|comm|message|conversation|typing|reaction|otp|email_verif' => 'Notification/Communication',
    'audit|log|activity|event|metric|kpi|analytics|stat|track|monitor|history|sync|api_log|security_|system_health|system_metric|system_status|system_log|crash|load_test|foreclosure|quick_action|upload_audit|behavior|file_access|mcp_log|registry_act|integration_log|task_exec|audit_sched|audit_log_arc|data_change|data_stream|plan_status|inventory_log|sync_log|sync_mapping|plan_status|integration_act|performance_log|performance_met|performance_anal|performance_bench|performance_rep|performance_goal|admin_dashboard_stat|forecast_res|error_log|security_black|security_rate|security_session|security_event' => 'Logs/Audit/Analytics',
    'gamification|badge|achievement|challenge' => 'Gamification',
    'training|course|learning|module|lesson|quiz|chatbot_train' => 'Training/Course',
    'setting|config|api_key|api_token|api_developer|api_integration|integration|webhook|gst_setting|layout_setting|mobile_app_setting|recommendation_setting|mcp_server|mcp_config|sms_config|email_config|dashboard_widget' => 'Settings/Config/Integration',
    'page|news|blog|faq|feedback|knowledge_base|legal_page|content|about|translation|language|newsletter|site_content|todays|seo_meta|special_offer|testimonial|review|rating' => 'Content/CMS',
    'media|gallery|image|project_gallery|social_media' => 'Media',
    'invoice|expense|budget|cash_flow|tax_|gst_|revenue|retained|journal|funding|investor|investment|loan|emi|transaction' => 'Finance/Accounting',
    'campaign|popup|ad_slot|marketing_|popup_dismiss' => 'Marketing/Campaign',
    'farmer|agriculture|kisaan|khatabook|gata_master' => 'Farmer/Agriculture',
    'role|permission|user_role|admin_role|admin_user_menu|user_perm|role_perm|user_session|remember_token|mobile_app_token|two_factor|password_reset|jwt_black|blocked_ip' => 'Auth/Permission',
    'workflow|scheduled_task|task_|async_task|compliance' => 'Workflow/Task',
    'builder|company|builder_' => 'Builder/Company',
    'address|country|city|district|state|pincode|real_estate' => 'Address/Location',
    'user_|customer_|mobile_user' => 'User/Customer',
    'sales|deal|pipeline|opportunity|funnel|compar|saved_property|saved_search' => 'Sales/Deal',
    'career|job_' => 'Career/Job',
    'support_ticket|ticket_repl' => 'Support/Ticket',
    'package|saas|marketplace|tier_benefit|partner|subscription' => 'Package/Subscription',
];

$cats = [];
$uncategorized = [];
foreach ($dropped as $name) {
    $matched = false;
    foreach ($purposeMap as $patterns => $cat) {
        $parts = explode('|', $patterns);
        foreach ($parts as $p) {
            if (stripos($name, $p) !== false) {
                $cats[$cat][$name] = $old[$name];
                $matched = true;
                break 2;
            }
        }
    }
    if (!$matched) $uncategorized[$name] = $old[$name];
}

ksort($cats);
$totalCategorized = 0;
foreach ($cats as $cat => $tables) {
    $totalCategorized += count($tables);
}
echo "Categorized: $totalCategorized of " . count($dropped) . "\n";
echo "Uncategorized: " . count($uncategorized) . "\n\n";

foreach ($cats as $cat => $tables) {
    echo "--- $cat (" . count($tables) . " tables) ---\n";
    foreach ($tables as $name => $info) {
        echo sprintf("  %-50s %3d cols  %4d rows\n", $name, $info['col_count'], $info['rows']);
    }
    echo "\n";
}

if (count($uncategorized) > 0) {
    echo "--- UNCATEGORIZED (" . count($uncategorized) . " tables) ---\n";
    foreach ($uncategorized as $name => $info) {
        echo sprintf("  %-50s %3d cols  %4d rows\n", $name, $info['col_count'], $info['rows']);
    }
}?>