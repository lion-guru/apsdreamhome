<?php
require "config/bootstrap.php";
$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = [
    "user_activity_log", "ad_placements", "user_addresses", "user_properties", "search_history",
    "customer_assignments", "agent_tasks", "agent_executions", "agent_state", "workflow_automations",
    "auctions", "auction_bids", "auction_deposits", "auction_watchers",
    "audit_log", "audit_log_archive", "company_credentials", "dashboard_widgets", "drip_campaigns",
    "drip_emails", "drip_email_log", "drip_enrollments", "booking_payment_schedules", "plot_bookings",
    "dunning_log", "email_queue", "email_templates", "mlm_goals", "mlm_goal_events", "mlm_goal_progress",
    "mlm_associate_metrics", "notifications", "notification_templates", "ai_api_logs",
    "mlm_leaderboard_snapshots", "mlm_profiles", "cash_collections", "budgets", "budget_expenses",
    "nps_surveys", "nps_responses", "portal_menu_items", "property_tax_rates", "property_tax_assessments",
    "system_logs", "comprehensive_audit_log", "totp_secrets", "two_factor_backup_codes_log",
    "used_codes", "webhook_endpoints", "webhook_deliveries", "workflow_definitions", "workflow_instances",
    "workflow_steps", "workflow_actions", "digilocker_sessions", "esign_transactions", "esign_documents",
    "rera_verifications", "loan_documents", "emi_calculations", "property_coordinates", "property_images",
    "property_image_tags", "ai_recommendations", "ai_chat_conversations", "ai_learning_data",
    "analytics_events", "analytics_funnels", "land_brokers", "land_acquisitions", "plots",
    "colonies", "colony_layouts", "users", "leads", "deals", "tasks", "campaigns", "loyalty_points",
    "loyalty_rewards", "saved_searches", "events", "meetings", "documents", "gallery", "team_members",
    "career_applications", "testimonials", "reviews", "agent_reviews"
];
foreach ($tables as $t) {
    try {
        $cols = $db->query("SHOW COLUMNS FROM `$t`")->fetchAll(\PDO::FETCH_COLUMN, 0);
        $hasTenant = in_array("tenant_id", $cols);
        echo ($hasTenant ? "HAS_TID" : "NO_TID    ") . " | $t\n";
    } catch (\Throwable $e) {
        echo "ERROR    | $t (" . $e->getMessage() . ")\n";
    }
}
