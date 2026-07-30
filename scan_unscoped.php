<?php
$files = glob("app/Services/**/*.php");
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

$tenantTablesLower = array_map('strtolower', $tenantTables);

function hasTenantId($content) {
    return strpos($content, 'tenant_id') !== false || strpos($content, 'tenantId') !== false || strpos($content, 'ServiceTenantTrait') !== false || strpos($content, 'TenantContext') !== false;
}

function hasSqlWrite($content) {
    $patterns = [
        '/INSERT\s+INTO/i',
        '/UPDATE\s+\w+/i',
        '/DELETE\s+FROM/i',
        '/\->prepare\s*\(/i',
        '/\->query\s*\(/i',
        '/\->execute\s*\(/i',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $content)) return true;
    }
    return false;
}

function referencesTenantTable($content, $tenantTablesLower) {
    foreach ($tenantTablesLower as $t) {
        if (stripos($content, $t) !== false) return true;
    }
    return false;
}

$results = [];
foreach ($files as $f) {
    $content = file_get_contents($f);
    $basename = basename($f);

    // Skip already scoped
    if (hasTenantId($content)) continue;

    // Skip if no SQL writes
    if (!hasSqlWrite($content)) continue;

    // Check if references tenant tables
    if (!referencesTenantTable($content, $tenantTablesLower)) continue;

    // Find which tenant tables referenced
    $foundTables = [];
    foreach ($tenantTablesLower as $t) {
        if (stripos($content, $t) !== false) {
            $foundTables[] = $t;
        }
    }

    // Determine risk level
    $risk = 'MEDIUM';
    // HIGH if has INSERT/UPDATE/DELETE
    if (preg_match('/INSERT\s+INTO/i', $content) || preg_match('/UPDATE\s+\w+/i', $content) || preg_match('/DELETE\s+FROM/i', $content)) {
        $risk = 'HIGH';
    }

    $results[] = [
        'file' => $f,
        'risk' => $risk,
        'tables' => implode(', ', $foundTables),
        'hasInsert' => (bool)preg_match('/INSERT\s+INTO/i', $content),
        'hasUpdate' => (bool)preg_match('/UPDATE\s+\w+/i', $content),
        'hasDelete' => (bool)preg_match('/DELETE\s+FROM/i', $content),
        'hasPrepare' => (bool)preg_match('/\->prepare\s*\(/i', $content),
    ];
}

usort($results, function($a, $b) {
    if ($a['risk'] === 'HIGH' && $b['risk'] !== 'HIGH') return -1;
    if ($a['risk'] !== 'HIGH' && $b['risk'] === 'HIGH') return 1;
    return strcmp($a['file'], $b['file']);
});

echo "HIGH RISK:\n";
foreach ($results as $r) {
    if ($r['risk'] === 'HIGH') {
        echo "  HIGH: {$r['file']}\n    Tables: {$r['tables']}\n";
    }
}
echo "\nMEDIUM RISK (no direct INSERT/UPDATE/DELETE in same pattern):\n";
foreach ($results as $r) {
    if ($r['risk'] === 'MEDIUM') {
        echo "  MEDIUM: {$r['file']}\n    Tables: {$r['tables']}\n";
    }
}
echo "\nTotal files needing scoping: " . count($results) . "\n";