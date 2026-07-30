<?php
$files = glob("app/Services/**/*.php");
$results = [];

foreach ($files as $f) {
    $content = file_get_contents($f);

    // Skip files that already have ServiceTenantTrait or TenantContext
    if (strpos($content, 'ServiceTenantTrait') !== false) continue;
    if (strpos($content, 'TenantContext::') !== false) continue;

    // Check for SQL writes on tenant-scoped tables
    $tenantTables = [
        'leads', 'bookings', 'payments', 'plot_bookings', 'plots', 'properties',
        'user_properties', 'inquiries', 'site_visits', 'agreements', 'invoices',
        'expenses', 'commissions', 'mlm_commission_ledger', 'mlm_network_tree',
        'network_tree', 'referrals', 'wallet_points', 'notifications', 'support_tickets',
        'support_ticket_replies', 'crm_leads', 'crm_interactions', 'crm_tasks',
        'lead_activities', 'lead_deals', 'email_queue', 'sms_queue', 'push_notifications',
        'notification_logs', 'fcm_tokens', 'marketing_leads', 'saved_searches',
        'resell_properties', 'resell_property_images', 'property_reviews',
        'review_helpful_votes', 'review_reports', 'testimonials', 'team_members',
        'career_applications', 'nps_submissions', 'lead_files', 'lead_custom_values',
        'company_loans', 'loan_installments', 'loan_offers', 'loan_documents',
        'loan_guarantors', 'loan_early_incentives', 'loan_activity_log',
        'booking_emis', 'payment_orders', 'gateway_logs', 'payout_batches',
        'payout_entries', 'commission_recalculations', 'plot_transfers',
        'interior_inquiries', 'lead_scoring', 'lead_tags', 'lead_notes',
        'employees', 'attendance', 'payslips', 'leaves', 'tasks', 'projects',
        'holidays', 'documents', 'social_media_posts', 'traffic_stats',
        'newsletter_subscribers', 'agent_reviews', 'property_alerts',
        'crm_segments', 'crm_lead_forms', 'email_templates', 'sms_templates',
        'campaigns', 'crm_custom_fields', 'crm_sla_rules', 'crm_sla_logs',
        'crm_meetings', 'drip_enrollments', 'drip_email_log', 'sessions',
        'user_activity_logs_unified', 'ai_chatbot_settings', 'ad_placements',
        'directory_listings', 'directory_reviews', 'directory_jobs',
        'plot_categories', 'plot_costs', 'ai_api_logs', 'agent_task_logs',
    ];

    $hasWrite = preg_match('/\b(INSERT\s+INTO|UPDATE\s+\w+|DELETE\s+FROM)\b/i', $content);
    if (!$hasWrite) continue;

    $foundTables = [];
    $contentLower = strtolower($content);
    foreach ($tenantTables as $t) {
        if (strpos($contentLower, $t) !== false) {
            $foundTables[] = $t;
        }
    }
    if (empty($foundTables)) continue;

    $results[] = $f;
}

echo "Truly unscoped files with SQL writes on tenant tables: " . count($results) . "\n";
foreach ($results as $f) echo "  $f\n";