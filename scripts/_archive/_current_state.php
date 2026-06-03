<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Group tables by "core" concept
echo "=== TABLES BY CATEGORY (post-cleanup) ===\n\n";

$groups = [
    'user_roles' => [],
    'user_profiles' => [],
    'addresses' => [],
    'bank_kyc' => [],
    'social_contact' => [],
    'property' => [],
    'project_colony' => [],
    'plots' => [],
    'bookings' => [],
    'visits' => [],
    'leads' => [],
    'deals_pipeline' => [],
    'commissions' => [],
    'payouts' => [],
    'payments' => [],
    'emi' => [],
    'invoices' => [],
    'expenses' => [],
    'refunds' => [],
    'wallet' => [],
    'rewards_loyalty' => [],
    'mlm' => [],
    'notifications' => [],
    'email_sms' => [],
    'whatsapp' => [],
    'ai_agents' => [],
    'voice_call' => [],
    'chat_messages' => [],
    'properties_listing' => [],
    'images_media' => [],
    'documents' => [],
    'support' => [],
    'careers' => [],
    'campaigns' => [],
    'social_features' => [],
    'favorites' => [],
    'reviews' => [],
    'testimonials' => [],
    'comments_feedback' => [],
    'content' => [],
    'faqs' => [],
    'pages' => [],
    'news_blog' => [],
    'newsletter' => [],
    'testimonials_etc' => [],
    'training' => [],
    'gamification' => [],
    'analytics' => [],
    'audit_log' => [],
    'permissions' => [],
    'auth' => [],
    'api' => [],
    'settings' => [],
    'menu' => [],
    'workflow' => [],
    'tasks' => [],
    'schedules' => [],
    'files_uploads' => [],
    'work_schedules' => [],
    'performance' => [],
    'kpi_metrics' => [],
    'reports' => [],
    'finance_extra' => [],
    'salary' => [],
    'attendance' => [],
    'leaves' => [],
    'shifts' => [],
    'payroll' => [],
    'employees' => [],
    'departments' => [],
    'designations' => [],
    'job_postings' => [],
    'job_applications' => [],
    'recruitment' => [],
    'interviews' => [],
    'training_modules' => [],
    'certificates' => [],
    'team' => [],
    'performance_metrics' => [],
    'sync' => [],
    'integrations' => [],
    'webhooks' => [],
    'company' => [],
    'builder' => [],
    'investor' => [],
    'sales' => [],
    'opportunities' => [],
    'forecasts' => [],
    'kpi' => [],
    'dashboard' => [],
    'widgets' => [],
    'mobile' => [],
    'pwa' => [],
    'system' => [],
    'cache' => [],
    'queue' => [],
    'cron' => [],
    'backup' => [],
    'system_settings' => [],
    'configs' => [],
    'localization' => [],
    'mcp' => [],
    'chatbot' => [],
    'virtual_tours' => [],
    'image_processing' => [],
    'maintenance' => [],
    'compliance' => [],
    'legal' => [],
    'rera' => [],
    'other' => [],
];

$uncategorized = 0;
foreach ($tables as $t) {
    $found = false;
    $lower = strtolower($t);
    foreach ($groups as $cat => $list) {
        $pattern = str_replace('_', '[_]?', $cat);
        if (preg_match("/$pattern/", $lower)) {
            $groups[$cat][] = $t;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $groups['other'][] = $t;
        $uncategorized++;
    }
}

$total = 0;
foreach ($groups as $cat => $list) {
    if (empty($list)) continue;
    echo "\n[$cat] " . count($list) . " tables\n";
    foreach ($list as $t) echo "  $t\n";
    $total += count($list);
}
echo "\n=== TOTAL: $total tables ===\n";
echo "Uncategorized: $uncategorized\n";
