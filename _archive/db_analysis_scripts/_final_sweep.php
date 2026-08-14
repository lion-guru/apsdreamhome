<?php
/**
 * Final sweep: find all remaining tables with <=3 code refs
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Core tables to NEVER touch
$core = ['users', 'bookings', 'properties', 'user_properties', 'leads', 'colonies',
         'admin_menu_items', 'districts', 'states', 'countries', 'cities', 'pincodes',
         'settings', 'banks', 'bank_branches', 'projects', 'roles', 'invoices',
         'expenses', 'transactions', 'commissions', 'payouts', 'documents', 'notifications',
         'notification_queue', 'email_queue', 'sms_queue', 'campaigns', 'visits',
         'site_visits', 'plots', 'inventory_plots', 'plot_master', 'roles',
         'permissions', 'role_permissions', 'admin_role_menu_permissions',
         'mlm_profiles', 'mlm_network_tree', 'mlm_commission_ledger',
         'wallet_points', 'wallet_transactions', 'user_wallets',
         'emi_plans', 'emi_payments', 'emi_installments', 'emi_schedule',
         'employee_salary_structure', 'salary_payments', 'employee_attendance',
         'employee_leaves', 'employee_payroll', 'employee_shifts',
         'lead_scoring', 'lead_scoring_history', 'lead_scoring_rules',
         'notification_templates', 'notification_settings',
         'points_rules', 'tier_benefits', 'wallet_configuration',
         'comparison_criteria', 'error_logs', 'audit_log',
         'ai_call_sessions', 'ai_calling_agents', 'ai_calling_schedule',
         'ai_call_extracted_leads', 'ai_settings', 'ai_agents',
         'sales_pipeline_stages', 'pipeline_stages',
         'property_categories', 'property_types',
         'real_estate_properties', 'deals', 'opportunities',
         'team', 'team_members', 'departments',
         'support_tickets', 'service_interests',
         'faqs', 'gallery', 'blog_posts', 'news', 'testimonials',
         'newsletter_subscribers', 'social_accounts',
         'otp_verifications', 'password_reset_tokens',
         'lead_sources', 'lead_statuses', 'lead_activities',
         'performance_reviews', 'performance_metrics',
         'payroll_runs', 'salary_tracker',
         'legal_documents', 'legal_pages', 'generated_documents',
         'document_templates', 'document_categories', 'document_types',
         'invoice_items', 'price_history',
         'rewards_catalog', 'loyalty_points', 'loyalty_transactions',
         'reward_redemptions', 'saved_searches',
         'sites', 'plot_bookings', 'plot_allocations',
         'plot_development', 'plot_development_costs',
         'lead_pipeline', 'lead_deals', 'lead_tags', 'lead_notes',
         'lead_visits', 'lead_engagement_metrics', 'lead_status_history',
         'lead_scores', 'mlm_points', 'mlm_payouts', 'mlm_commissions',
         'mlm_commission_plans', 'mlm_commission_levels', 'mlm_levels',
         'mlm_associates', 'network_tree',
         'feedback', 'favorites', 'property_favorites',
         'property_images', 'property_inquiries', 'property_views',
         'property_visits', 'property_reviews', 'property_performance',
         'property_summary', 'property_maintenance',
         'workflow_steps', 'workflow_instances', 'workflow_executions',
         'workflow_actions', 'workflow_definitions', 'workflows',
         'scheduled_tasks', 'tasks', 'task_dependencies', 'task_execution_logs',
         'financial_transactions', 'income_records', 'budgets',
         'chart_of_accounts', 'journal_entries', 'journal_entry_lines',
         'revenue_summary', 'reports', 'saved_reports',
         'api_keys', 'security_logs', 'security_events',
         'blocked_ips', 'system_logs', 'system_backups',
         'integrations', 'translations', 'shift_types', 'leave_types',
         'land_records', 'land_acquisitions', 'land_allocations',
         'land_purchases', 'farmers', 'farmer_profiles',
         'farmer_land_holdings', 'farmer_loans', 'farmer_transactions',
         'farmer_agreements', 'gata_master',
         'jobs', 'job_applications', 'careers',
         'messages', 'conversations', 'conversation_participants',
         'chat_messages', 'chatbot_conversations', 'ai_conversations',
         'ai_chatbot_interactions', 'ai_knowledge_base',
         'companies', 'packages', 'vendors', 'suppliers',
         'training_courses', 'training_enrollments',
         'marketing_leads', 'opportunities', 'media', 'media_library',
         'notification_settings', 'call_logs', 'events',
         'companies', 'ad_slots', 'admin',
         'data_change_log', 'import_jobs', 'event_log'];

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $path = str_replace('\\', '/', $f->getPathname());
        $allFiles[$path] = file_get_contents($f->getPathname(), FILE_IGNORE_NEW_LINES);
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$candidates = [];

foreach ($tables as $t) {
    if (in_array($t, $core)) continue;
    $codeRef = 0;
    $refFiles = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) {
        $m = preg_match_all($pattern, $content);
        if ($m > 0) { $codeRef += $m; $refFiles[] = basename($path); }
    }

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    $candidates[] = ['name' => $t, 'rows' => $rows, 'refs' => $codeRef, 'fk' => $fkTo, 'files' => $refFiles];
}

usort($candidates, fn($a,$b) => $a['refs'] <=> $b['refs'] ?: $b['rows'] <=> $a['rows']);

echo "REMAINING NON-CORE TABLES:\n";
echo sprintf("%-40s %5s %5s %4s  %s\n", "TABLE", "ROWS", "REFS", "FK", "FILES");
echo str_repeat("-", 110) . "\n";
foreach ($candidates as $t) {
    echo sprintf("%-40s %5d %5d %4d  %s\n", $t['name'], $t['rows'], $t['refs'], $t['fk'], implode(', ', array_slice($t['files'], 0, 3)));
}
echo "\nTotal: " . count($candidates) . "\n";
$dropable = count(array_filter($candidates, fn($t) => $t['fk'] == 0 && $t['refs'] <= 5));
echo "Dropable (0 FK, <=5 refs): $dropable\n";?>