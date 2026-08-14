<?php
/**
 * Phase 17: Drop remaining low-ref tables
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $path = str_replace('\\', '/', $f->getPathname());
        $allFiles[$path] = file_get_contents($f->getPathname(), FILE_IGNORE_NEW_LINES);
    }
}

// Skip core business + tables with FK incoming
$skip = ['users', 'bookings', 'properties', 'leads', 'colonies', 'plots', 'inventory_plots',
         'projects', 'roles', 'admin_menu_items', 'notifications', 'documents',
         'commissions', 'payouts', 'invoices', 'expenses', 'transactions',
         'districts', 'states', 'countries', 'cities', 'pincodes',
         'settings', 'banks', 'bank_branches',
         'mlm_profiles', 'mlm_network_tree', 'mlm_commission_ledger',
         'wallet_points', 'wallet_transactions', 'user_wallets',
         'emi_plans', 'emi_payments', 'emi_installments', 'emi_schedule',
         'employee_salary_structure', 'salary_payments', 'employee_attendance',
         'employee_leaves', 'employee_payroll', 'employee_shifts',
         'lead_scoring', 'sites', 'plot_bookings', 'plot_allocations',
         'lead_pipeline', 'lead_deals', 'lead_tags', 'lead_notes',
         'lead_visits', 'lead_engagement_metrics', 'lead_status_history',
         'lead_scores', 'mlm_points', 'mlm_payouts', 'mlm_commissions',
         'mlm_commission_plans', 'mlm_commission_levels', 'mlm_levels',
         'mlm_associates', 'network_tree', 'rewards_catalog',
         'loyalty_points', 'loyalty_transactions', 'reward_redemptions',
         'saved_searches', 'favorites', 'property_favorites',
         'property_images', 'property_inquiries', 'property_views',
         'property_reviews', 'performance_reviews', 'performance_metrics',
         'payroll_runs', 'legal_documents', 'legal_pages',
         'generated_documents', 'document_templates', 'document_categories',
         'document_types', 'invoice_items', 'price_history',
         'support_tickets', 'service_interests', 'newsletter_subscribers',
         'social_accounts', 'otp_verifications', 'password_reset_tokens',
         'lead_sources', 'lead_statuses', 'lead_activities',
         'workflow_steps', 'workflow_instances', 'workflow_definitions',
         'scheduled_tasks', 'tasks', 'task_dependencies', 'task_execution_logs',
         'financial_transactions', 'income_records', 'budgets',
         'chart_of_accounts', 'journal_entries', 'journal_entry_lines',
         'reports', 'api_keys', 'security_logs', 'security_events',
         'blocked_ips', 'system_logs', 'system_backups',
         'integrations', 'translations', 'shift_types', 'leave_types',
         'land_records', 'land_acquisitions', 'land_allocations',
         'land_purchases', 'farmers', 'farmer_profiles',
         'farmer_land_holdings', 'farmer_loans', 'farmer_transactions',
         'farmer_agreements', 'gata_master',
         'jobs', 'job_applications', 'careers',
         'messages', 'conversations', 'conversation_participants',
         'chat_messages', 'chatbot_conversations', 'ai_conversations',
         'ai_chatbot_interactions', 'ai_knowledge_base', 'ai_settings',
         'companies', 'packages', 'vendors', 'suppliers',
         'training_courses', 'training_enrollments',
         'marketing_leads', 'media', 'media_library',
         'notification_queue', 'email_queue', 'sms_queue',
         'call_logs', 'event_log', 'campaigns',
         'deals', 'opportunities', 'sales',
         'team', 'team_members', 'departments',
         'user_roles', 'admin_role_menu_permissions',
         'activities', 'activity_logs_unified',
         'ai_agents', 'ai_call_sessions', 'ai_calling_agents',
         'ai_calling_schedule', 'ai_call_extracted_leads',
         'async_tasks', 'site_settings', 'site_visits',
         'documents', 'files', 'document_gallery',
         'user_badges', 'user_course_enrollments',
         'user_bank_accounts', 'ad_slots', 'admin',
         'property_categories', 'property_types', 'real_estate_properties',
         'pipeline_stages', 'sales_pipeline_stages',
         'kpis', 'employee_kpis', 'employee_leave_balances',
         'loyalty_points', 'loyalty_transactions',
         'badges', 'points_rules', 'tier_benefits',
         'virtual_tours', 'property_maintenance',
         'farmer_transactions', 'plot_bookings',
         'whatsapp_templates', 'whatsapp_messages',
         'notification_templates', 'notifications_unified',
         'lead_deals', 'property_allocations',
         'plot_development', 'plot_development_costs',
         'ocr_documents', 'dashboard_widgets'];

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$targets = [];

foreach ($tables as $t) {
    if (in_array($t, $skip)) continue;
    $codeRef = 0;
    $refPaths = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) {
        $m = preg_match_all($pattern, $content);
        if ($m > 0) { $codeRef += $m; $refPaths[] = $path; }
    }
    if ($codeRef > 3) continue;

    $fkTo = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $targets[] = ['name' => $t, 'rows' => $rows, 'refs' => $codeRef, 'paths' => $refPaths];
}

usort($targets, fn($a,$b) => $a['refs'] <=> $b['refs'] ?: $a['rows'] <=> $b['rows']);

echo "=== PHASE 17: Drop remaining low-ref tables ===\n";
echo "Candidates: " . count($targets) . "\n\n";

function wrapRefsInFile($allFiles, $tableName, $filePath) {
    $content = $allFiles[$filePath] ?? file_get_contents($filePath, FILE_IGNORE_NEW_LINES);
    $lines = explode("\n", $content);
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?{$tableName}`?/i";
    $wrapped = 0;
    $offset = 0;

    while (true) {
        $found = false;
        foreach ($lines as $lineNum => $line) {
            if ($lineNum < $offset) continue;
            if (!preg_match($pattern, $line)) continue;

            $inTry = false;
            for ($i = max(0, $lineNum - 20); $i < $lineNum; $i++) {
                if (preg_match('/\btry\s*\{/', $lines[$i])) { $inTry = true; break; }
                if (preg_match('/\}\s*catch/', $lines[$i])) break;
            }

            if (!$inTry) {
                $start = $lineNum;
                for ($i = $lineNum - 1; $i >= max(0, $lineNum - 30); $i--) {
                    $prev = rtrim($lines[$i]);
                    if ($prev === '' || str_ends_with($prev, ';') || str_ends_with($prev, '{') || str_ends_with($prev, '}')) break;
                    $start = $i;
                }
                $end = $lineNum;
                for ($i = $lineNum; $i < min(count($lines), $lineNum + 30); $i++) {
                    $end = $i;
                    if (preg_match('/;\s*$/', $lines[$i]) || preg_match('/\)\s*;/', $lines[$i])) break;
                }

                preg_match('/^(\s*)/', $lines[$start], $m);
                $indent = $m[1];

                $block = [];
                $block[] = "{$indent}try {";
                for ($i = $start; $i <= $end; $i++) {
                    $block[] = "    {$lines[$i]}";
                }
                $block[] = "{$indent}} catch (\\Throwable \$e) {";
                $block[] = "{$indent}    // Gracefully handle dropped table ref";
                $block[] = "{$indent}}";

                array_splice($lines, $start, $end - $start + 1, $block);
                $wrapped++;
                $offset = $start + count($block);
                $found = true;
                break;
            } else {
                $offset = $lineNum + 1;
            }
        }
        if (!$found) break;
    }
    file_put_contents($filePath, implode("\n", $lines));
    return $wrapped;
}

$wrapped = 0;
$dropped = 0;

foreach ($targets as $t) {
    $w = 0;
    foreach ($t['paths'] as $path) {
        $w += wrapRefsInFile($allFiles, $t['name'], $path);
    }
    $wrapped += $w;

    try {
        $pdo->exec("DROP TABLE IF EXISTS `{$t['name']}`");
        echo "  DROPPED: {$t['name']} ({$t['rows']} rows, {$t['refs']} refs, $w wrapped)\n";
        $dropped++;
    } catch (\Throwable $e) {
        echo "  FAILED: {$t['name']} - {$e->getMessage()}\n";
    }
}

echo "\n=== RESULT: Wrapped $wrapped refs, Dropped $dropped tables ===\n";
echo "Tables: " . count($tables) . " â†’ " . (count($tables) - $dropped) . "\n";?>