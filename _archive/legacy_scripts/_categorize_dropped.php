<?php
// Categorize dropped tables by feature and check if functionality exists
$root = dirname(__DIR__);
$csv = $root . '/_dropped_vs_survived.csv';
$rows = [];
$h = fopen($csv, 'r');
$header = fgetcsv($h);
while ($r = fgetcsv($h)) {
    $rows[$r[0]] = ['rows' => (int)$r[1], 'code' => (int)$r[2], 'status' => $r[3], 'now' => (int)$r[4], 'note' => $r[5]];
}
fclose($h);

$dropped = array_filter($rows, fn($r) => $r['status'] === 'DROPPED');

// Categorize by feature prefix
$cats = [
    'AI/Machine Learning' => ['ai_', 'chat_messages', 'chatbot_', 'predictive_', 'recommendation_'],
    'Voice/Calling' => ['voice_', 'call_logs', 'ai_call_', 'telecaller_'],
    'MLM/Network/Commission' => ['mlm_', 'commission_', 'payouts', 'wallet_', 'network_', 'referral_', 'associate_', 'sponsor_', 'salary_payouts', 'telecaller_commissions', 'farmer_commissions', 'resale_commissions', 'traditional_commissions', 'hybrid_commission_', 'salary_tracker'],
    'Property/Plot' => ['property_', 'plot_', 'resell_', 'sites', 'investment_', 'mortgage_', 'saved_properties', 'customer_favorites', 'favorites', 'virtual_tour', 'tour_templates', 'customer_wishlist', 'property_bookings', 'plot_bookings', 'plot_allocations', 'plot_emi_schedule'],
    'Lead/CRM' => ['lead_', 'crm_leads', 'customer_', 'inquiries', 'incomplete_registrations', 'follow_ups', 'customer_alerts', 'customer_inquiries', 'progressive_registrations', 'customer_summary', 'customer_behavior_analysis', 'customer_journeys', 'customers_ledger'],
    'Booking/Visit' => ['booking_', 'visit_', 'site_visit_', 'visitor_', 'lead_visits', 'visitor_leads', 'visitor_sessions', 'land_allocations'],
    'HRM/Payroll' => ['employee_', 'salary_', 'attendance_', 'payroll_', 'shift_', 'team_', 'leave_', 'department_', 'jobs', 'job_applications', 'team_members', 'company_employees'],
    'Document/File' => ['document_', 'file_', 'ocr_', 'generated_documents', 'files', 'legal_documents', 'business_documents', 'customer_documents', 'employee_documents', 'user_documents', 'message_attachments', 'file_uploads', 'file_shares', 'file_versions', 'file_tags', 'file_tag_relations'],
    'Notification/Communication' => ['notification_', 'email_', 'sms_', 'whatsapp_', 'push_', 'realtime_notifications', 'chat_messages', 'communications', 'messages', 'message_', 'conversation_', 'typing_indicators', 'message_reactions', 'communication_', 'communication_interactions', 'otp_verifications', 'email_verifications'],
    'Notification Templates' => ['email_templates', 'sms_templates', 'whatsapp_templates', 'document_templates', 'legal_document_templates', 'message_templates', 'ocr_templates', 'page_templates', 'layout_templates', 'tour_templates', 'invoice_templates'],
    'Analytics/Logs' => ['audit_', 'error_logs', 'activity_log', 'user_activity', 'event_log', 'analytics_', 'kpis', 'kpi', 'metric', 'log', 'api_logs', 'api_request_logs', 'security_logs', 'security_events', 'security_blacklist', 'security_sessions', 'security_rate_limits', 'system_health', 'system_metrics', 'system_status', 'system_logs', 'system_health_logs', 'system_activities', 'system_alerts', 'performance_logs', 'performance_metrics', 'performance_analytics', 'performance_benchmarks', 'performance_reports', 'performance_goals', 'admin_dashboard_stats', 'forecast_results', 'load_test_metrics', 'crash_reports', 'sync_log', 'sync_mappings', 'sync_queue_summary', 'data_change_log', 'data_stream_events', 'plan_status_history', 'inventory_log', 'quick_actions_log', 'upload_audit_log', 'user_behavior_tracking', 'user_browsing_history', 'user_search_history', 'mobile_app_analytics', 'daily_activity_tracker', 'file_access_logs', 'mcp_logs', 'mcp_config_history', 'registry_activity_log', 'financial_integration_log', 'foreclosure_logs', 'task_execution_logs', 'audit_schedules', 'audit_log_archive', 'event_log', 'event_log'],
    'Gamification' => ['gamification_', 'badges', 'achievements', 'user_achievements', 'user_badges', 'gamification_points', 'gamification_badges', 'gamification_challenges', 'challenge_participants'],
    'Training/Course' => ['training_', 'course_', 'learning_', 'personalized_learning_plans', 'module_progress', 'chatbot_training_data', 'quiz_questions', 'user_course_enrollments'],
    'Settings/Config' => ['settings', 'site_settings', 'app_config', 'company_settings', 'company_projects', 'system_settings', 'email_config', 'sms_config', 'api_configs', 'api_integrations', 'integrations', 'integration_', 'integration_settings', 'integration_logs', 'third_party_integrations', 'webhook_events', 'webhooks', 'gst_settings', 'layout_settings', 'mobile_app_settings', 'recommendation_settings', 'mcp_server_configs', 'mcp_servers', 'mcp_data', 'accounting_settings', 'user_dashboard_configs', 'dashboard_widgets_config', 'dashboard_widgets'],
    'Content/CMS' => ['pages', 'news', 'blogs', 'blog_posts', 'blog_categories', 'faqs', 'faq_categories', 'feedback', 'feedback_tickets', 'legal_pages', 'knowledge_base', 'content_', 'about', 'translations', 'languages', 'multi_language_translations', 'newsletter', 'site_content', 'todays_highlights', 'special_offers', 'seo_metadata', 'testimonials', 'agent_reviews', 'reviews', 'rating'],
    'Media' => ['media_', 'gallery', 'image_categories', 'document_gallery', 'project_gallery', 'social_media_links', 'media_library', 'images'],
    'Finance' => ['invoices', 'invoice_', 'expense', 'budget', 'cash_flow', 'payouts', 'tax_', 'gst_', 'revenue_', 'retained_earnings', 'journal_', 'budget_', 'funding_investors', 'investor_details', 'investment_plans', 'loan_', 'emi_', 'transactions', 'wallet_', 'farmer_'],
    'Marketing/Campaign' => ['campaign', 'popups', 'ad_slots', 'marketing_', 'popup_dismissals', 'event_log'],
    'Farmer/Agriculture' => ['farmer_', 'farmers', 'farmers_legacy', 'kisaan_', 'khatabook_', 'gata_master'],
    'Auth/Permission' => ['roles', 'permissions', 'user_roles', 'role_menus', 'user_permissions', 'user_role_permissions', 'role_permissions', 'admin_role_menu_permissions', 'admin_user_menu_permissions', 'user_sessions', 'remember_tokens', 'mobile_app_tokens', 'two_factor_tokens', 'password_reset_tokens', 'jwt_blacklist', 'blocked_ips', 'security_', 'admin'],
    'Workflow/Task' => ['workflow_', 'scheduled_tasks', 'task_', 'async_tasks', 'tasks', 'workflow_steps', 'compliance_tasks'],
    'Builder/Company' => ['builders', 'companies', 'builder_', 'company_'],
    'Address/Location' => ['addresses', 'user_addresses', 'countries', 'cities', 'districts', 'states', 'pincodes', 'real_estate_properties', 'sites'],
    'User/Customer' => ['users', 'agents', 'mobile_users', 'mobile_devices', 'user_', 'customer_'],
    'Sales/Deal/Pipeline' => ['sales', 'sales_', 'deals', 'deal_', 'pipeline_', 'opportunities', 'sales_funnel', 'todays_highlights', 'special_offers'],
    'Career/Job' => ['careers', 'career_applications', 'jobs', 'job_applications'],
    'Support/Ticket' => ['support_ticket', 'ticket_replies'],
    'Package/Subscription' => ['packages', 'saas_instances', 'marketplace_apps', 'tier_benefits', 'partner_certification', 'partner_rewards'],
    'Other' => ['cache', 'backup', 'replication', 'realtime', 'ocr_processing_queue', 'ocr_extracted_fields', 'iot_', 'mcp_data', 'app_store', 'components', 'interaction_routing', 'interaction_reminders', 'regional_performance', 'comparison_criteria', 'compar', 'smart_contracts', 'seasonality_patterns', 'portfolio_goals', 'user_similarity', 'virtual_tour_assets', 'websocket_channels', 'sales_data', 'pricing', 'import_jobs', 'data_stream_events', 'heatmap_data', 'market_trends', 'alert_triggers', 'automated_triggers', 'automation_triggers', 'communication_logs', 'leadership', 'scoring_thresholds', 'user_preferences', 'user_engagement', 'areas', 'amenities', 'saved_comparisons', 'about', 'dynamic_footers', 'dynamic_headers', 'images', 'files', 'media', 'social_media', 'cities', 'chatbot_', 'smart_', 'ai_', 'ml_'],
];

$out = [];
foreach ($dropped as $name => $info) {
    $matched = false;
    foreach ($cats as $cat => $patterns) {
        foreach ($patterns as $p) {
            if (stripos($name, $p) === 0 || strpos($name, $p) !== false) {
                $out[$cat][$name] = $info;
                $matched = true;
                break 2;
            }
        }
    }
    if (!$matched) {
        $out['Uncategorized'][$name] = $info;
    }
}

ksort($out);
$sum = 0;
foreach ($out as $cat => $items) {
    $sum += count($items);
    echo PHP_EOL . "=== $cat (" . count($items) . " tables) ===" . PHP_EOL;
    $i = 0;
    foreach ($items as $name => $info) {
        echo sprintf("  %-50s %5d rows  Code:%d\n", $name, $info['rows'], $info['code']);
        if (++$i >= 12) {
            if (count($items) > 12) echo "  ... +" . (count($items) - 12) . " more\n";
            break;
        }
    }
}
echo PHP_EOL . "=== TOTAL CATEGORIZED: $sum of " . count($dropped) . " dropped tables ===" . PHP_EOL;?>