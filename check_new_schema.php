<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome", "root", "");

echo "=== TOTAL TABLES ===\n";
$total = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='apsdreamhome'")->fetchColumn();
echo "Total: $total\n\n";

echo "=== NEW TABLES (added after Sep 5 backup) ===\n";
$newTables = [
    'booking_emis', 'payout_batches', 'payout_entries', 'service_configs',
    'whatsapp_click_log', 'visitor_page_views', 'visitor_sessions',
    'ai_calling_campaigns', 'ai_calling_schedule',
    'document_esign', 'agent_agreements',
    'listing_packages', 'property_boost_orders', 'property_agents', 'property_messages',
    'company_loans', 'loan_installments', 'loan_offers', 'loan_documents', 'loan_guarantors', 'loan_early_incentives', 'loan_activity_log',
    'crm_custom_fields', 'crm_lead_custom_values', 'crm_sla_rules', 'crm_sla_logs', 'crm_meetings',
    'agentic_task_logs',
    'departments', 'designations', 'employee_designation_roles', 'admin_user_menu_permissions',
    'login_attempts', 'notification_logs',
    'team_members', 'team_groups',
    'social_media_posts', 'ai_chatbot_settings', 'company_settings',
    'plot_categories', 'plot_costs', 'plot_transfers',
    'legal_document_categories', 'legal_document_templates', 'legal_template_versions', 'legal_clause_library', 'legal_documents', 'legal_document_uploads', 'legal_ai_prompts',
    'mlm_salary_grants', 'nach_mandates', 'reconciliation_collections',
    'campaign_deliveries', 'campaign_delivery_schedule',
    'interior_inquiries', 'ai_chatbot_training',
];

foreach ($newTables as $t) {
    try {
        $pdo->query("SELECT COUNT(*) FROM $t");
        echo "  [OK] $t\n";
    } catch(Exception $e) {
        echo "  [MISSING] $t\n";
    }
}

echo "\n=== KEY NEW COLUMNS ===\n";
$checks = [
    ['legal_documents', 'template_id'], ['legal_documents', 'effective_date'], ['legal_documents', 'entity_type'],
    ['legal_documents', 'content'], ['legal_documents', 'kyc_verified'],
    ['site_visits', 'cab_details'], ['site_visits', 'outcome'], ['site_visits', 'outcome_notes'],
    ['payout_entries', 'utr_number'], ['payout_entries', 'paid_at'],
    ['mlm_commission_ledger', 'plan_id'], ['mlm_commission_ledger', 'plan_version'],
    ['mlm_commission_ledger', 'plan_snapshot'], ['mlm_commission_ledger', 'calculation_engine'],
    ['mlm_commission_ledger', 'booking_id'], ['mlm_commission_ledger', 'receipt_id'],
    ['ai_api_logs', 'engine_used'], ['ai_api_logs', 'confidence'],
    ['ai_knowledge_base', 'is_active'], ['ai_knowledge_base', 'confidence'],
    ['ai_conversations', 'platform'],
    ['colonies', 'layout_image'], ['colonies', 'virtual_tour_url'], ['colonies', 'latitude'], ['colonies', 'longitude'],
    ['departments', 'id'], ['designations', 'id'], ['employee_designation_roles', 'id'],
];

foreach ($checks as $c) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM {$c[0]}")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array($c[1], $cols)) {
            echo "  [OK] {$c[0]}.{$c[1]}\n";
        } else {
            echo "  [MISSING] {$c[0]}.{$c[1]}\n";
        }
    } catch(Exception $e) {
        echo "  [ERROR] {$c[0]}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE ===\n";
