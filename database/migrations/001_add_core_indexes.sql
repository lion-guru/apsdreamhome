-- Auto-generated core index migration
-- This file was generated based on current live schema.
-- It adds indexes for common *_id and frequently queried fields, skipping existing ones.


ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_payment_date` (`payment_date`);
ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_party_id` (`party_id`);
ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_bank_account_id` (`bank_account_id`);
ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_transaction_id` (`transaction_id`);
ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_invoice_id` (`invoice_id`);
ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_status` (`status`);
ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_created_at` (`created_at`);
ALTER TABLE `accounting_payments` ADD INDEX `ix_accounting_payments_updated_at` (`updated_at`);

ALTER TABLE `accounting_settings` ADD INDEX `ix_accounting_settings_updated_at` (`updated_at`);

ALTER TABLE `activities` ADD INDEX `ix_activities_activity_id` (`activity_id`);
ALTER TABLE `activities` ADD INDEX `ix_activities_lead_id` (`lead_id`);
ALTER TABLE `activities` ADD INDEX `ix_activities_opportunity_id` (`opportunity_id`);
ALTER TABLE `activities` ADD INDEX `ix_activities_due_date` (`due_date`);
ALTER TABLE `activities` ADD INDEX `ix_activities_completed_date` (`completed_date`);
ALTER TABLE `activities` ADD INDEX `ix_activities_created_at` (`created_at`);
ALTER TABLE `activities` ADD INDEX `ix_activities_updated_at` (`updated_at`);

ALTER TABLE `activity_log` ADD INDEX `ix_activity_log_created_at` (`created_at`);

ALTER TABLE `activity_logs` ADD INDEX `ix_activity_logs_user_id` (`user_id`);
ALTER TABLE `activity_logs` ADD INDEX `ix_activity_logs_created_at` (`created_at`);

-- SKIP: `addresses`.`user_id` already indexed
-- SKIP: `addresses`.`property_id` already indexed
ALTER TABLE `addresses` ADD INDEX `ix_addresses_created_at` (`created_at`);
ALTER TABLE `addresses` ADD INDEX `ix_addresses_updated_at` (`updated_at`);

ALTER TABLE `admin` ADD INDEX `ix_admin_status` (`status`);
ALTER TABLE `admin` ADD INDEX `ix_admin_email` (`email`);
ALTER TABLE `admin` ADD INDEX `ix_admin_phone` (`phone`);

ALTER TABLE `admin_activity_log` ADD INDEX `ix_admin_activity_log_admin_id` (`admin_id`);
ALTER TABLE `admin_activity_log` ADD INDEX `ix_admin_activity_log_created_at` (`created_at`);


ALTER TABLE `ai_chatbot_config` ADD INDEX `ix_ai_chatbot_config_created_at` (`created_at`);

ALTER TABLE `ai_chatbot_interactions` ADD INDEX `ix_ai_chatbot_interactions_user_id` (`user_id`);
ALTER TABLE `ai_chatbot_interactions` ADD INDEX `ix_ai_chatbot_interactions_created_at` (`created_at`);

ALTER TABLE `ai_config` ADD INDEX `ix_ai_config_updated_at` (`updated_at`);

ALTER TABLE `ai_lead_scores` ADD INDEX `ix_ai_lead_scores_lead_id` (`lead_id`);
ALTER TABLE `ai_lead_scores` ADD INDEX `ix_ai_lead_scores_scored_at` (`scored_at`);

ALTER TABLE `ai_logs` ADD INDEX `ix_ai_logs_user_id` (`user_id`);
ALTER TABLE `ai_logs` ADD INDEX `ix_ai_logs_created_at` (`created_at`);

ALTER TABLE `api_developers` ADD INDEX `ix_api_developers_email` (`email`);
ALTER TABLE `api_developers` ADD INDEX `ix_api_developers_status` (`status`);
ALTER TABLE `api_developers` ADD INDEX `ix_api_developers_created_at` (`created_at`);

ALTER TABLE `api_integrations` ADD INDEX `ix_api_integrations_status` (`status`);
ALTER TABLE `api_integrations` ADD INDEX `ix_api_integrations_created_at` (`created_at`);

ALTER TABLE `api_keys` ADD INDEX `ix_api_keys_user_id` (`user_id`);
ALTER TABLE `api_keys` ADD INDEX `ix_api_keys_status` (`status`);
ALTER TABLE `api_keys` ADD INDEX `ix_api_keys_created_at` (`created_at`);
ALTER TABLE `api_keys` ADD INDEX `ix_api_keys_updated_at` (`updated_at`);
ALTER TABLE `api_keys` ADD INDEX `ix_api_keys_last_used_at` (`last_used_at`);


ALTER TABLE `api_request_logs` ADD INDEX `ix_api_request_logs_api_key_id` (`api_key_id`);

ALTER TABLE `api_sandbox` ADD INDEX `ix_api_sandbox_status` (`status`);
ALTER TABLE `api_sandbox` ADD INDEX `ix_api_sandbox_created_at` (`created_at`);


ALTER TABLE `app_store` ADD INDEX `ix_app_store_created_at` (`created_at`);

ALTER TABLE `ar_vr_tours` ADD INDEX `ix_ar_vr_tours_property_id` (`property_id`);
ALTER TABLE `ar_vr_tours` ADD INDEX `ix_ar_vr_tours_uploaded_at` (`uploaded_at`);

ALTER TABLE `associate_levels` ADD INDEX `ix_associate_levels_status` (`status`);
ALTER TABLE `associate_levels` ADD INDEX `ix_associate_levels_created_at` (`created_at`);
ALTER TABLE `associate_levels` ADD INDEX `ix_associate_levels_updated_at` (`updated_at`);

ALTER TABLE `associate_mlm` ADD INDEX `ix_associate_mlm_created_at` (`created_at`);
ALTER TABLE `associate_mlm` ADD INDEX `ix_associate_mlm_updated_at` (`updated_at`);

-- SKIP: `associates`.`user_id` already indexed
ALTER TABLE `associates` ADD INDEX `ix_associates_created_at` (`created_at`);
ALTER TABLE `associates` ADD INDEX `ix_associates_updated_at` (`updated_at`);

ALTER TABLE `associates_backup` ADD INDEX `ix_associates_backup_associate_id` (`associate_id`);
ALTER TABLE `associates_backup` ADD INDEX `ix_associates_backup_user_id` (`user_id`);
ALTER TABLE `associates_backup` ADD INDEX `ix_associates_backup_sponsor_id` (`sponsor_id`);
ALTER TABLE `associates_backup` ADD INDEX `ix_associates_backup_created_at` (`created_at`);

ALTER TABLE `attendance` ADD INDEX `ix_attendance_employee_id` (`employee_id`);
ALTER TABLE `attendance` ADD INDEX `ix_attendance_status` (`status`);

ALTER TABLE `audit_access_log` ADD INDEX `ix_audit_access_log_accessed_at` (`accessed_at`);
ALTER TABLE `audit_access_log` ADD INDEX `ix_audit_access_log_user_id` (`user_id`);

ALTER TABLE `audit_log` ADD INDEX `ix_audit_log_user_id` (`user_id`);
ALTER TABLE `audit_log` ADD INDEX `ix_audit_log_created_at` (`created_at`);

ALTER TABLE `audit_trail` ADD INDEX `ix_audit_trail_record_id` (`record_id`);
ALTER TABLE `audit_trail` ADD INDEX `ix_audit_trail_user_id` (`user_id`);
ALTER TABLE `audit_trail` ADD INDEX `ix_audit_trail_created_at` (`created_at`);

ALTER TABLE `bank_accounts` ADD INDEX `ix_bank_accounts_status` (`status`);
ALTER TABLE `bank_accounts` ADD INDEX `ix_bank_accounts_created_at` (`created_at`);
ALTER TABLE `bank_accounts` ADD INDEX `ix_bank_accounts_updated_at` (`updated_at`);

ALTER TABLE `bank_reconciliation` ADD INDEX `ix_bank_reconciliation_bank_account_id` (`bank_account_id`);
ALTER TABLE `bank_reconciliation` ADD INDEX `ix_bank_reconciliation_reconciliation_date` (`reconciliation_date`);
ALTER TABLE `bank_reconciliation` ADD INDEX `ix_bank_reconciliation_status` (`status`);
ALTER TABLE `bank_reconciliation` ADD INDEX `ix_bank_reconciliation_created_at` (`created_at`);
ALTER TABLE `bank_reconciliation` ADD INDEX `ix_bank_reconciliation_updated_at` (`updated_at`);

ALTER TABLE `bank_transactions` ADD INDEX `ix_bank_transactions_bank_account_id` (`bank_account_id`);
ALTER TABLE `bank_transactions` ADD INDEX `ix_bank_transactions_transaction_date` (`transaction_date`);
ALTER TABLE `bank_transactions` ADD INDEX `ix_bank_transactions_payment_id` (`payment_id`);
ALTER TABLE `bank_transactions` ADD INDEX `ix_bank_transactions_reconciled_date` (`reconciled_date`);
ALTER TABLE `bank_transactions` ADD INDEX `ix_bank_transactions_created_at` (`created_at`);

ALTER TABLE `booking_payments` ADD INDEX `ix_booking_payments_payment_id` (`payment_id`);
ALTER TABLE `booking_payments` ADD INDEX `ix_booking_payments_booking_id` (`booking_id`);
ALTER TABLE `booking_payments` ADD INDEX `ix_booking_payments_payment_date` (`payment_date`);
ALTER TABLE `booking_payments` ADD INDEX `ix_booking_payments_transaction_id` (`transaction_id`);

ALTER TABLE `booking_summary` ADD INDEX `ix_booking_summary_booking_id` (`booking_id`);
ALTER TABLE `booking_summary` ADD INDEX `ix_booking_summary_booking_date` (`booking_date`);
ALTER TABLE `booking_summary` ADD INDEX `ix_booking_summary_customer_id` (`customer_id`);
ALTER TABLE `booking_summary` ADD INDEX `ix_booking_summary_property_id` (`property_id`);
ALTER TABLE `booking_summary` ADD INDEX `ix_booking_summary_associate_id` (`associate_id`);

-- SKIP: `bookings`.`property_id` already indexed
-- SKIP: `bookings`.`customer_id` already indexed
ALTER TABLE `bookings` ADD INDEX `ix_bookings_booking_date` (`booking_date`);
-- SKIP: `bookings`.`status` already indexed
-- SKIP: `bookings`.`created_at` already indexed
ALTER TABLE `bookings` ADD INDEX `ix_bookings_updated_at` (`updated_at`);

ALTER TABLE `budget_planning` ADD INDEX `ix_budget_planning_account_id` (`account_id`);
ALTER TABLE `budget_planning` ADD INDEX `ix_budget_planning_status` (`status`);
ALTER TABLE `budget_planning` ADD INDEX `ix_budget_planning_created_at` (`created_at`);
ALTER TABLE `budget_planning` ADD INDEX `ix_budget_planning_updated_at` (`updated_at`);

ALTER TABLE `builder_payments` ADD INDEX `ix_builder_payments_project_id` (`project_id`);
ALTER TABLE `builder_payments` ADD INDEX `ix_builder_payments_builder_id` (`builder_id`);
ALTER TABLE `builder_payments` ADD INDEX `ix_builder_payments_payment_date` (`payment_date`);
ALTER TABLE `builder_payments` ADD INDEX `ix_builder_payments_transaction_id` (`transaction_id`);
ALTER TABLE `builder_payments` ADD INDEX `ix_builder_payments_created_at` (`created_at`);

ALTER TABLE `builders` ADD INDEX `ix_builders_email` (`email`);
ALTER TABLE `builders` ADD INDEX `ix_builders_mobile` (`mobile`);
ALTER TABLE `builders` ADD INDEX `ix_builders_status` (`status`);
ALTER TABLE `builders` ADD INDEX `ix_builders_created_at` (`created_at`);
ALTER TABLE `builders` ADD INDEX `ix_builders_updated_at` (`updated_at`);

ALTER TABLE `campaign_members` ADD INDEX `ix_campaign_members_member_id` (`member_id`);
ALTER TABLE `campaign_members` ADD INDEX `ix_campaign_members_campaign_id` (`campaign_id`);
ALTER TABLE `campaign_members` ADD INDEX `ix_campaign_members_lead_id` (`lead_id`);
ALTER TABLE `campaign_members` ADD INDEX `ix_campaign_members_status` (`status`);
ALTER TABLE `campaign_members` ADD INDEX `ix_campaign_members_created_at` (`created_at`);
ALTER TABLE `campaign_members` ADD INDEX `ix_campaign_members_updated_at` (`updated_at`);

ALTER TABLE `campaigns` ADD INDEX `ix_campaigns_campaign_id` (`campaign_id`);
ALTER TABLE `campaigns` ADD INDEX `ix_campaigns_status` (`status`);
ALTER TABLE `campaigns` ADD INDEX `ix_campaigns_start_date` (`start_date`);
ALTER TABLE `campaigns` ADD INDEX `ix_campaigns_end_date` (`end_date`);
ALTER TABLE `campaigns` ADD INDEX `ix_campaigns_created_at` (`created_at`);
ALTER TABLE `campaigns` ADD INDEX `ix_campaigns_updated_at` (`updated_at`);

ALTER TABLE `career_applications` ADD INDEX `ix_career_applications_phone` (`phone`);
ALTER TABLE `career_applications` ADD INDEX `ix_career_applications_email` (`email`);
ALTER TABLE `career_applications` ADD INDEX `ix_career_applications_created_at` (`created_at`);

ALTER TABLE `cash_flow_projections` ADD INDEX `ix_cash_flow_projections_projection_date` (`projection_date`);
ALTER TABLE `cash_flow_projections` ADD INDEX `ix_cash_flow_projections_created_at` (`created_at`);
ALTER TABLE `cash_flow_projections` ADD INDEX `ix_cash_flow_projections_updated_at` (`updated_at`);

ALTER TABLE `chart_of_accounts` ADD INDEX `ix_chart_of_accounts_parent_account_id` (`parent_account_id`);
ALTER TABLE `chart_of_accounts` ADD INDEX `ix_chart_of_accounts_created_at` (`created_at`);
ALTER TABLE `chart_of_accounts` ADD INDEX `ix_chart_of_accounts_updated_at` (`updated_at`);

ALTER TABLE `chat_messages` ADD INDEX `ix_chat_messages_created_at` (`created_at`);

ALTER TABLE `chatbot_conversations` ADD INDEX `ix_chatbot_conversations_created_at` (`created_at`);
ALTER TABLE `chatbot_conversations` ADD INDEX `ix_chatbot_conversations_updated_at` (`updated_at`);


ALTER TABLE `colonies` ADD INDEX `ix_colonies_status` (`status`);
ALTER TABLE `colonies` ADD INDEX `ix_colonies_created_at` (`created_at`);
ALTER TABLE `colonies` ADD INDEX `ix_colonies_updated_at` (`updated_at`);

ALTER TABLE `commission_payouts` ADD INDEX `ix_commission_payouts_associate_id` (`associate_id`);
ALTER TABLE `commission_payouts` ADD INDEX `ix_commission_payouts_payout_date` (`payout_date`);
ALTER TABLE `commission_payouts` ADD INDEX `ix_commission_payouts_status` (`status`);
ALTER TABLE `commission_payouts` ADD INDEX `ix_commission_payouts_transaction_id` (`transaction_id`);
ALTER TABLE `commission_payouts` ADD INDEX `ix_commission_payouts_created_at` (`created_at`);
ALTER TABLE `commission_payouts` ADD INDEX `ix_commission_payouts_updated_at` (`updated_at`);

ALTER TABLE `commission_transactions` ADD INDEX `ix_commission_transactions_transaction_id` (`transaction_id`);
ALTER TABLE `commission_transactions` ADD INDEX `ix_commission_transactions_associate_id` (`associate_id`);
ALTER TABLE `commission_transactions` ADD INDEX `ix_commission_transactions_booking_id` (`booking_id`);
ALTER TABLE `commission_transactions` ADD INDEX `ix_commission_transactions_upline_id` (`upline_id`);
ALTER TABLE `commission_transactions` ADD INDEX `ix_commission_transactions_transaction_date` (`transaction_date`);
ALTER TABLE `commission_transactions` ADD INDEX `ix_commission_transactions_status` (`status`);

ALTER TABLE `communications` ADD INDEX `ix_communications_lead_id` (`lead_id`);
ALTER TABLE `communications` ADD INDEX `ix_communications_communication_date` (`communication_date`);
ALTER TABLE `communications` ADD INDEX `ix_communications_user_id` (`user_id`);
ALTER TABLE `communications` ADD INDEX `ix_communications_created_at` (`created_at`);

ALTER TABLE `companies` ADD INDEX `ix_companies_created_at` (`created_at`);

ALTER TABLE `company_employees` ADD INDEX `ix_company_employees_company_id` (`company_id`);
ALTER TABLE `company_employees` ADD INDEX `ix_company_employees_user_id` (`user_id`);
ALTER TABLE `company_employees` ADD INDEX `ix_company_employees_join_date` (`join_date`);
ALTER TABLE `company_employees` ADD INDEX `ix_company_employees_status` (`status`);

ALTER TABLE `company_projects` ADD INDEX `ix_company_projects_status` (`status`);
ALTER TABLE `company_projects` ADD INDEX `ix_company_projects_start_date` (`start_date`);
ALTER TABLE `company_projects` ADD INDEX `ix_company_projects_end_date` (`end_date`);
ALTER TABLE `company_projects` ADD INDEX `ix_company_projects_created_at` (`created_at`);
ALTER TABLE `company_projects` ADD INDEX `ix_company_projects_updated_at` (`updated_at`);

ALTER TABLE `company_property_levels` ADD INDEX `ix_company_property_levels_plan_id` (`plan_id`);

ALTER TABLE `company_settings` ADD INDEX `ix_company_settings_phone` (`phone`);
ALTER TABLE `company_settings` ADD INDEX `ix_company_settings_email` (`email`);
ALTER TABLE `company_settings` ADD INDEX `ix_company_settings_created_at` (`created_at`);
ALTER TABLE `company_settings` ADD INDEX `ix_company_settings_updated_at` (`updated_at`);

ALTER TABLE `components` ADD INDEX `ix_components_created_at` (`created_at`);
ALTER TABLE `components` ADD INDEX `ix_components_updated_at` (`updated_at`);

ALTER TABLE `construction_projects` ADD INDEX `ix_construction_projects_builder_id` (`builder_id`);
ALTER TABLE `construction_projects` ADD INDEX `ix_construction_projects_site_id` (`site_id`);
ALTER TABLE `construction_projects` ADD INDEX `ix_construction_projects_start_date` (`start_date`);
ALTER TABLE `construction_projects` ADD INDEX `ix_construction_projects_status` (`status`);
ALTER TABLE `construction_projects` ADD INDEX `ix_construction_projects_created_at` (`created_at`);
ALTER TABLE `construction_projects` ADD INDEX `ix_construction_projects_updated_at` (`updated_at`);

ALTER TABLE `contact_backup` ADD INDEX `ix_contact_backup_email` (`email`);
ALTER TABLE `contact_backup` ADD INDEX `ix_contact_backup_phone` (`phone`);
ALTER TABLE `contact_backup` ADD INDEX `ix_contact_backup_status` (`status`);
ALTER TABLE `contact_backup` ADD INDEX `ix_contact_backup_created_at` (`created_at`);

ALTER TABLE `content_backups` ADD INDEX `ix_content_backups_page_id` (`page_id`);
ALTER TABLE `content_backups` ADD INDEX `ix_content_backups_created_at` (`created_at`);

ALTER TABLE `crm_leads` ADD INDEX `ix_crm_leads_created_at` (`created_at`);
ALTER TABLE `crm_leads` ADD INDEX `ix_crm_leads_updated_at` (`updated_at`);

ALTER TABLE `customer_documents` ADD INDEX `ix_customer_documents_customer_id` (`customer_id`);
ALTER TABLE `customer_documents` ADD INDEX `ix_customer_documents_status` (`status`);
ALTER TABLE `customer_documents` ADD INDEX `ix_customer_documents_uploaded_at` (`uploaded_at`);

ALTER TABLE `customer_inquiries` ADD INDEX `ix_customer_inquiries_customer_id` (`customer_id`);
ALTER TABLE `customer_inquiries` ADD INDEX `ix_customer_inquiries_status` (`status`);
ALTER TABLE `customer_inquiries` ADD INDEX `ix_customer_inquiries_response_date` (`response_date`);
ALTER TABLE `customer_inquiries` ADD INDEX `ix_customer_inquiries_created_at` (`created_at`);
ALTER TABLE `customer_inquiries` ADD INDEX `ix_customer_inquiries_updated_at` (`updated_at`);

ALTER TABLE `customer_journeys` ADD INDEX `ix_customer_journeys_customer_id` (`customer_id`);
ALTER TABLE `customer_journeys` ADD INDEX `ix_customer_journeys_started_at` (`started_at`);
ALTER TABLE `customer_journeys` ADD INDEX `ix_customer_journeys_last_touch_at` (`last_touch_at`);

ALTER TABLE `customer_summary` ADD INDEX `ix_customer_summary_customer_id` (`customer_id`);
ALTER TABLE `customer_summary` ADD INDEX `ix_customer_summary_email` (`email`);
ALTER TABLE `customer_summary` ADD INDEX `ix_customer_summary_mobile` (`mobile`);
ALTER TABLE `customer_summary` ADD INDEX `ix_customer_summary_last_booking_date` (`last_booking_date`);

-- SKIP: `customers`.`user_id` already indexed
ALTER TABLE `customers` ADD INDEX `ix_customers_created_at` (`created_at`);
ALTER TABLE `customers` ADD INDEX `ix_customers_updated_at` (`updated_at`);

ALTER TABLE `customers_ledger` ADD INDEX `ix_customers_ledger_customer_id` (`customer_id`);
ALTER TABLE `customers_ledger` ADD INDEX `ix_customers_ledger_mobile` (`mobile`);
ALTER TABLE `customers_ledger` ADD INDEX `ix_customers_ledger_email` (`email`);
ALTER TABLE `customers_ledger` ADD INDEX `ix_customers_ledger_last_payment_date` (`last_payment_date`);
ALTER TABLE `customers_ledger` ADD INDEX `ix_customers_ledger_status` (`status`);
ALTER TABLE `customers_ledger` ADD INDEX `ix_customers_ledger_created_at` (`created_at`);
ALTER TABLE `customers_ledger` ADD INDEX `ix_customers_ledger_updated_at` (`updated_at`);

ALTER TABLE `data_stream_events` ADD INDEX `ix_data_stream_events_streamed_at` (`streamed_at`);

ALTER TABLE `documents` ADD INDEX `ix_documents_user_id` (`user_id`);
-- SKIP: `documents`.`property_id` already indexed
ALTER TABLE `documents` ADD INDEX `ix_documents_drive_file_id` (`drive_file_id`);

ALTER TABLE `emi` ADD INDEX `ix_emi_user_id` (`user_id`);
ALTER TABLE `emi` ADD INDEX `ix_emi_property_id` (`property_id`);
ALTER TABLE `emi` ADD INDEX `ix_emi_due_date` (`due_date`);
ALTER TABLE `emi` ADD INDEX `ix_emi_paid_date` (`paid_date`);
ALTER TABLE `emi` ADD INDEX `ix_emi_status` (`status`);

ALTER TABLE `emi_installments` ADD INDEX `ix_emi_installments_emi_plan_id` (`emi_plan_id`);
ALTER TABLE `emi_installments` ADD INDEX `ix_emi_installments_due_date` (`due_date`);
ALTER TABLE `emi_installments` ADD INDEX `ix_emi_installments_payment_date` (`payment_date`);
ALTER TABLE `emi_installments` ADD INDEX `ix_emi_installments_payment_id` (`payment_id`);
ALTER TABLE `emi_installments` ADD INDEX `ix_emi_installments_last_reminder_date` (`last_reminder_date`);
ALTER TABLE `emi_installments` ADD INDEX `ix_emi_installments_created_at` (`created_at`);
ALTER TABLE `emi_installments` ADD INDEX `ix_emi_installments_updated_at` (`updated_at`);

ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_property_id` (`property_id`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_customer_id` (`customer_id`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_start_date` (`start_date`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_end_date` (`end_date`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_status` (`status`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_foreclosure_date` (`foreclosure_date`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_foreclosure_payment_id` (`foreclosure_payment_id`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_created_at` (`created_at`);
ALTER TABLE `emi_plans` ADD INDEX `ix_emi_plans_updated_at` (`updated_at`);

ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_customer_id` (`customer_id`);
ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_booking_id` (`booking_id`);
ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_due_date` (`due_date`);
ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_status` (`status`);
ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_paid_date` (`paid_date`);
ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_payment_id` (`payment_id`);
ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_created_at` (`created_at`);
ALTER TABLE `emi_schedule` ADD INDEX `ix_emi_schedule_updated_at` (`updated_at`);

ALTER TABLE `employees` ADD INDEX `ix_employees_email` (`email`);
ALTER TABLE `employees` ADD INDEX `ix_employees_phone` (`phone`);
ALTER TABLE `employees` ADD INDEX `ix_employees_join_date` (`join_date`);
ALTER TABLE `employees` ADD INDEX `ix_employees_status` (`status`);
ALTER TABLE `employees` ADD INDEX `ix_employees_created_at` (`created_at`);

ALTER TABLE `expenses` ADD INDEX `ix_expenses_user_id` (`user_id`);
ALTER TABLE `expenses` ADD INDEX `ix_expenses_expense_date` (`expense_date`);
ALTER TABLE `expenses` ADD INDEX `ix_expenses_created_at` (`created_at`);

ALTER TABLE `faqs` ADD INDEX `ix_faqs_status` (`status`);
ALTER TABLE `faqs` ADD INDEX `ix_faqs_created_at` (`created_at`);
ALTER TABLE `faqs` ADD INDEX `ix_faqs_updated_at` (`updated_at`);

ALTER TABLE `farmer_land_holdings` ADD INDEX `ix_farmer_land_holdings_farmer_id` (`farmer_id`);
ALTER TABLE `farmer_land_holdings` ADD INDEX `ix_farmer_land_holdings_acquisition_date` (`acquisition_date`);
ALTER TABLE `farmer_land_holdings` ADD INDEX `ix_farmer_land_holdings_created_at` (`created_at`);
ALTER TABLE `farmer_land_holdings` ADD INDEX `ix_farmer_land_holdings_updated_at` (`updated_at`);

ALTER TABLE `farmer_profiles` ADD INDEX `ix_farmer_profiles_phone` (`phone`);
ALTER TABLE `farmer_profiles` ADD INDEX `ix_farmer_profiles_email` (`email`);
ALTER TABLE `farmer_profiles` ADD INDEX `ix_farmer_profiles_voter_id` (`voter_id`);
ALTER TABLE `farmer_profiles` ADD INDEX `ix_farmer_profiles_status` (`status`);
ALTER TABLE `farmer_profiles` ADD INDEX `ix_farmer_profiles_associate_id` (`associate_id`);
ALTER TABLE `farmer_profiles` ADD INDEX `ix_farmer_profiles_created_at` (`created_at`);
ALTER TABLE `farmer_profiles` ADD INDEX `ix_farmer_profiles_updated_at` (`updated_at`);

ALTER TABLE `farmers` ADD INDEX `ix_farmers_user_id` (`user_id`);

-- SKIP: `favorites`.`user_id` already indexed
-- SKIP: `favorites`.`property_id` already indexed
ALTER TABLE `favorites` ADD INDEX `ix_favorites_created_at` (`created_at`);

ALTER TABLE `feedback` ADD INDEX `ix_feedback_user_id` (`user_id`);
ALTER TABLE `feedback` ADD INDEX `ix_feedback_status` (`status`);
ALTER TABLE `feedback` ADD INDEX `ix_feedback_created_at` (`created_at`);

ALTER TABLE `feedback_tickets` ADD INDEX `ix_feedback_tickets_user_id` (`user_id`);
ALTER TABLE `feedback_tickets` ADD INDEX `ix_feedback_tickets_status` (`status`);
ALTER TABLE `feedback_tickets` ADD INDEX `ix_feedback_tickets_created_at` (`created_at`);

ALTER TABLE `financial_reports` ADD INDEX `ix_financial_reports_from_date` (`from_date`);
ALTER TABLE `financial_reports` ADD INDEX `ix_financial_reports_to_date` (`to_date`);
ALTER TABLE `financial_reports` ADD INDEX `ix_financial_reports_generated_at` (`generated_at`);
ALTER TABLE `financial_reports` ADD INDEX `ix_financial_reports_cache_expires_at` (`cache_expires_at`);

ALTER TABLE `financial_years` ADD INDEX `ix_financial_years_start_date` (`start_date`);
ALTER TABLE `financial_years` ADD INDEX `ix_financial_years_end_date` (`end_date`);
ALTER TABLE `financial_years` ADD INDEX `ix_financial_years_closing_date` (`closing_date`);
ALTER TABLE `financial_years` ADD INDEX `ix_financial_years_created_at` (`created_at`);
ALTER TABLE `financial_years` ADD INDEX `ix_financial_years_updated_at` (`updated_at`);

ALTER TABLE `foreclosure_logs` ADD INDEX `ix_foreclosure_logs_emi_plan_id` (`emi_plan_id`);
ALTER TABLE `foreclosure_logs` ADD INDEX `ix_foreclosure_logs_status` (`status`);
ALTER TABLE `foreclosure_logs` ADD INDEX `ix_foreclosure_logs_attempted_at` (`attempted_at`);

ALTER TABLE `gallery` ADD INDEX `ix_gallery_status` (`status`);
ALTER TABLE `gallery` ADD INDEX `ix_gallery_created_at` (`created_at`);

ALTER TABLE `gata_master` ADD INDEX `ix_gata_master_gata_id` (`gata_id`);
ALTER TABLE `gata_master` ADD INDEX `ix_gata_master_site_id` (`site_id`);

ALTER TABLE `global_payments` ADD INDEX `ix_global_payments_status` (`status`);
ALTER TABLE `global_payments` ADD INDEX `ix_global_payments_created_at` (`created_at`);

ALTER TABLE `gst_records` ADD INDEX `ix_gst_records_transaction_id` (`transaction_id`);
ALTER TABLE `gst_records` ADD INDEX `ix_gst_records_transaction_date` (`transaction_date`);
ALTER TABLE `gst_records` ADD INDEX `ix_gst_records_created_at` (`created_at`);

ALTER TABLE `hybrid_commission_plans` ADD INDEX `ix_hybrid_commission_plans_status` (`status`);
ALTER TABLE `hybrid_commission_plans` ADD INDEX `ix_hybrid_commission_plans_created_at` (`created_at`);
ALTER TABLE `hybrid_commission_plans` ADD INDEX `ix_hybrid_commission_plans_updated_at` (`updated_at`);

ALTER TABLE `hybrid_commission_records` ADD INDEX `ix_hybrid_commission_records_associate_id` (`associate_id`);
ALTER TABLE `hybrid_commission_records` ADD INDEX `ix_hybrid_commission_records_property_id` (`property_id`);
ALTER TABLE `hybrid_commission_records` ADD INDEX `ix_hybrid_commission_records_customer_id` (`customer_id`);
ALTER TABLE `hybrid_commission_records` ADD INDEX `ix_hybrid_commission_records_created_at` (`created_at`);
ALTER TABLE `hybrid_commission_records` ADD INDEX `ix_hybrid_commission_records_paid_at` (`paid_at`);


ALTER TABLE `income_records` ADD INDEX `ix_income_records_income_date` (`income_date`);
ALTER TABLE `income_records` ADD INDEX `ix_income_records_bank_account_id` (`bank_account_id`);
ALTER TABLE `income_records` ADD INDEX `ix_income_records_customer_id` (`customer_id`);
ALTER TABLE `income_records` ADD INDEX `ix_income_records_project_id` (`project_id`);
ALTER TABLE `income_records` ADD INDEX `ix_income_records_status` (`status`);
ALTER TABLE `income_records` ADD INDEX `ix_income_records_created_at` (`created_at`);
ALTER TABLE `income_records` ADD INDEX `ix_income_records_updated_at` (`updated_at`);

ALTER TABLE `inventory_log` ADD INDEX `ix_inventory_log_plot_id` (`plot_id`);
ALTER TABLE `inventory_log` ADD INDEX `ix_inventory_log_user_id` (`user_id`);
ALTER TABLE `inventory_log` ADD INDEX `ix_inventory_log_action_date` (`action_date`);
ALTER TABLE `inventory_log` ADD INDEX `ix_inventory_log_created_at` (`created_at`);

ALTER TABLE `iot_device_events` ADD INDEX `ix_iot_device_events_device_id` (`device_id`);

ALTER TABLE `iot_devices` ADD INDEX `ix_iot_devices_property_id` (`property_id`);
ALTER TABLE `iot_devices` ADD INDEX `ix_iot_devices_status` (`status`);
ALTER TABLE `iot_devices` ADD INDEX `ix_iot_devices_created_at` (`created_at`);

ALTER TABLE `job_applications` ADD INDEX `ix_job_applications_phone` (`phone`);
ALTER TABLE `job_applications` ADD INDEX `ix_job_applications_email` (`email`);

ALTER TABLE `journal_entries` ADD INDEX `ix_journal_entries_entry_date` (`entry_date`);
ALTER TABLE `journal_entries` ADD INDEX `ix_journal_entries_source_id` (`source_id`);
ALTER TABLE `journal_entries` ADD INDEX `ix_journal_entries_approval_date` (`approval_date`);
ALTER TABLE `journal_entries` ADD INDEX `ix_journal_entries_status` (`status`);
ALTER TABLE `journal_entries` ADD INDEX `ix_journal_entries_created_at` (`created_at`);
ALTER TABLE `journal_entries` ADD INDEX `ix_journal_entries_updated_at` (`updated_at`);

ALTER TABLE `journal_entry_details` ADD INDEX `ix_journal_entry_details_journal_entry_id` (`journal_entry_id`);
ALTER TABLE `journal_entry_details` ADD INDEX `ix_journal_entry_details_account_id` (`account_id`);
ALTER TABLE `journal_entry_details` ADD INDEX `ix_journal_entry_details_reference_id` (`reference_id`);
ALTER TABLE `journal_entry_details` ADD INDEX `ix_journal_entry_details_created_at` (`created_at`);

ALTER TABLE `jwt_blacklist` ADD INDEX `ix_jwt_blacklist_expires_at` (`expires_at`);
ALTER TABLE `jwt_blacklist` ADD INDEX `ix_jwt_blacklist_created_at` (`created_at`);

ALTER TABLE `kissan_master` ADD INDEX `ix_kissan_master_kissan_id` (`kissan_id`);
ALTER TABLE `kissan_master` ADD INDEX `ix_kissan_master_site_id` (`site_id`);

ALTER TABLE `land_purchases` ADD INDEX `ix_land_purchases_farmer_id` (`farmer_id`);
ALTER TABLE `land_purchases` ADD INDEX `ix_land_purchases_property_id` (`property_id`);
ALTER TABLE `land_purchases` ADD INDEX `ix_land_purchases_purchase_date` (`purchase_date`);

ALTER TABLE `layout_templates` ADD INDEX `ix_layout_templates_created_at` (`created_at`);
ALTER TABLE `layout_templates` ADD INDEX `ix_layout_templates_updated_at` (`updated_at`);

ALTER TABLE `lead_files` ADD INDEX `ix_lead_files_lead_id` (`lead_id`);
ALTER TABLE `lead_files` ADD INDEX `ix_lead_files_created_at` (`created_at`);

ALTER TABLE `lead_notes` ADD INDEX `ix_lead_notes_lead_id` (`lead_id`);
ALTER TABLE `lead_notes` ADD INDEX `ix_lead_notes_created_at` (`created_at`);

ALTER TABLE `leads` ADD INDEX `ix_leads_email` (`email`);
ALTER TABLE `leads` ADD INDEX `ix_leads_phone` (`phone`);
-- SKIP: `leads`.`status` already indexed
-- SKIP: `leads`.`created_at` already indexed
ALTER TABLE `leads` ADD INDEX `ix_leads_updated_at` (`updated_at`);
ALTER TABLE `leads` ADD INDEX `ix_leads_converted_at` (`converted_at`);

ALTER TABLE `leaves` ADD INDEX `ix_leaves_employee_id` (`employee_id`);
ALTER TABLE `leaves` ADD INDEX `ix_leaves_from_date` (`from_date`);
ALTER TABLE `leaves` ADD INDEX `ix_leaves_to_date` (`to_date`);
ALTER TABLE `leaves` ADD INDEX `ix_leaves_status` (`status`);
ALTER TABLE `leaves` ADD INDEX `ix_leaves_created_at` (`created_at`);

ALTER TABLE `legal_documents` ADD INDEX `ix_legal_documents_uploaded_at` (`uploaded_at`);

ALTER TABLE `legal_services` ADD INDEX `ix_legal_services_status` (`status`);
ALTER TABLE `legal_services` ADD INDEX `ix_legal_services_created_at` (`created_at`);
ALTER TABLE `legal_services` ADD INDEX `ix_legal_services_updated_at` (`updated_at`);

ALTER TABLE `loan_emi_schedule` ADD INDEX `ix_loan_emi_schedule_loan_id` (`loan_id`);
ALTER TABLE `loan_emi_schedule` ADD INDEX `ix_loan_emi_schedule_due_date` (`due_date`);
ALTER TABLE `loan_emi_schedule` ADD INDEX `ix_loan_emi_schedule_paid_date` (`paid_date`);
ALTER TABLE `loan_emi_schedule` ADD INDEX `ix_loan_emi_schedule_status` (`status`);
ALTER TABLE `loan_emi_schedule` ADD INDEX `ix_loan_emi_schedule_payment_id` (`payment_id`);
ALTER TABLE `loan_emi_schedule` ADD INDEX `ix_loan_emi_schedule_created_at` (`created_at`);

ALTER TABLE `loans` ADD INDEX `ix_loans_start_date` (`start_date`);
ALTER TABLE `loans` ADD INDEX `ix_loans_end_date` (`end_date`);
ALTER TABLE `loans` ADD INDEX `ix_loans_disbursement_date` (`disbursement_date`);
ALTER TABLE `loans` ADD INDEX `ix_loans_bank_account_id` (`bank_account_id`);
ALTER TABLE `loans` ADD INDEX `ix_loans_status` (`status`);
ALTER TABLE `loans` ADD INDEX `ix_loans_created_at` (`created_at`);
ALTER TABLE `loans` ADD INDEX `ix_loans_updated_at` (`updated_at`);

ALTER TABLE `login_history` ADD INDEX `ix_login_history_user_id` (`user_id`);

ALTER TABLE `marketing_campaigns` ADD INDEX `ix_marketing_campaigns_scheduled_at` (`scheduled_at`);
ALTER TABLE `marketing_campaigns` ADD INDEX `ix_marketing_campaigns_status` (`status`);
ALTER TABLE `marketing_campaigns` ADD INDEX `ix_marketing_campaigns_created_at` (`created_at`);

ALTER TABLE `marketing_strategies` ADD INDEX `ix_marketing_strategies_created_at` (`created_at`);
ALTER TABLE `marketing_strategies` ADD INDEX `ix_marketing_strategies_updated_at` (`updated_at`);

ALTER TABLE `marketplace_apps` ADD INDEX `ix_marketplace_apps_created_at` (`created_at`);

ALTER TABLE `media` ADD INDEX `ix_media_uploaded_at` (`uploaded_at`);


ALTER TABLE `migrations` ADD INDEX `ix_migrations_applied_at` (`applied_at`);

ALTER TABLE `mlm_agents` ADD INDEX `ix_mlm_agents_mobile` (`mobile`);
ALTER TABLE `mlm_agents` ADD INDEX `ix_mlm_agents_email` (`email`);
ALTER TABLE `mlm_agents` ADD INDEX `ix_mlm_agents_sponsor_id` (`sponsor_id`);
ALTER TABLE `mlm_agents` ADD INDEX `ix_mlm_agents_status` (`status`);
ALTER TABLE `mlm_agents` ADD INDEX `ix_mlm_agents_registration_date` (`registration_date`);

ALTER TABLE `mlm_commission_analytics` ADD INDEX `ix_mlm_commission_analytics_associate_id` (`associate_id`);
ALTER TABLE `mlm_commission_analytics` ADD INDEX `ix_mlm_commission_analytics_period_date` (`period_date`);
ALTER TABLE `mlm_commission_analytics` ADD INDEX `ix_mlm_commission_analytics_created_at` (`created_at`);

ALTER TABLE `mlm_commission_ledger` ADD INDEX `ix_mlm_commission_ledger_commission_id` (`commission_id`);
ALTER TABLE `mlm_commission_ledger` ADD INDEX `ix_mlm_commission_ledger_created_at` (`created_at`);

ALTER TABLE `mlm_commission_records` ADD INDEX `ix_mlm_commission_records_associate_id` (`associate_id`);
ALTER TABLE `mlm_commission_records` ADD INDEX `ix_mlm_commission_records_customer_id` (`customer_id`);
ALTER TABLE `mlm_commission_records` ADD INDEX `ix_mlm_commission_records_status` (`status`);
ALTER TABLE `mlm_commission_records` ADD INDEX `ix_mlm_commission_records_created_at` (`created_at`);
ALTER TABLE `mlm_commission_records` ADD INDEX `ix_mlm_commission_records_updated_at` (`updated_at`);

ALTER TABLE `mlm_commission_targets` ADD INDEX `ix_mlm_commission_targets_associate_id` (`associate_id`);
ALTER TABLE `mlm_commission_targets` ADD INDEX `ix_mlm_commission_targets_start_date` (`start_date`);
ALTER TABLE `mlm_commission_targets` ADD INDEX `ix_mlm_commission_targets_end_date` (`end_date`);
ALTER TABLE `mlm_commission_targets` ADD INDEX `ix_mlm_commission_targets_status` (`status`);
ALTER TABLE `mlm_commission_targets` ADD INDEX `ix_mlm_commission_targets_created_at` (`created_at`);

-- SKIP: `mlm_commissions`.`associate_id` already indexed
ALTER TABLE `mlm_commissions` ADD INDEX `ix_mlm_commissions_payout_id` (`payout_id`);
ALTER TABLE `mlm_commissions` ADD INDEX `ix_mlm_commissions_status` (`status`);
ALTER TABLE `mlm_commissions` ADD INDEX `ix_mlm_commissions_created_at` (`created_at`);
ALTER TABLE `mlm_commissions` ADD INDEX `ix_mlm_commissions_updated_at` (`updated_at`);

ALTER TABLE `mlm_levels` ADD INDEX `ix_mlm_levels_created_at` (`created_at`);
ALTER TABLE `mlm_levels` ADD INDEX `ix_mlm_levels_updated_at` (`updated_at`);

ALTER TABLE `mlm_payouts` ADD INDEX `ix_mlm_payouts_associate_id` (`associate_id`);
ALTER TABLE `mlm_payouts` ADD INDEX `ix_mlm_payouts_status` (`status`);
ALTER TABLE `mlm_payouts` ADD INDEX `ix_mlm_payouts_processed_at` (`processed_at`);
ALTER TABLE `mlm_payouts` ADD INDEX `ix_mlm_payouts_created_at` (`created_at`);

ALTER TABLE `mlm_performance` ADD INDEX `ix_mlm_performance_associate_id` (`associate_id`);
ALTER TABLE `mlm_performance` ADD INDEX `ix_mlm_performance_status` (`status`);
ALTER TABLE `mlm_performance` ADD INDEX `ix_mlm_performance_created_at` (`created_at`);
ALTER TABLE `mlm_performance` ADD INDEX `ix_mlm_performance_updated_at` (`updated_at`);

ALTER TABLE `mlm_rank_advancements` ADD INDEX `ix_mlm_rank_advancements_associate_id` (`associate_id`);
ALTER TABLE `mlm_rank_advancements` ADD INDEX `ix_mlm_rank_advancements_advancement_date` (`advancement_date`);
ALTER TABLE `mlm_rank_advancements` ADD INDEX `ix_mlm_rank_advancements_created_at` (`created_at`);
ALTER TABLE `mlm_rank_advancements` ADD INDEX `ix_mlm_rank_advancements_updated_at` (`updated_at`);

ALTER TABLE `mlm_rewards_recognition` ADD INDEX `ix_mlm_rewards_recognition_created_at` (`created_at`);
ALTER TABLE `mlm_rewards_recognition` ADD INDEX `ix_mlm_rewards_recognition_updated_at` (`updated_at`);

ALTER TABLE `mlm_special_bonuses` ADD INDEX `ix_mlm_special_bonuses_created_at` (`created_at`);
ALTER TABLE `mlm_special_bonuses` ADD INDEX `ix_mlm_special_bonuses_updated_at` (`updated_at`);

ALTER TABLE `mlm_tree` ADD INDEX `ix_mlm_tree_user_id` (`user_id`);
ALTER TABLE `mlm_tree` ADD INDEX `ix_mlm_tree_parent_id` (`parent_id`);
ALTER TABLE `mlm_tree` ADD INDEX `ix_mlm_tree_join_date` (`join_date`);

ALTER TABLE `mlm_withdrawal_requests` ADD INDEX `ix_mlm_withdrawal_requests_associate_id` (`associate_id`);
ALTER TABLE `mlm_withdrawal_requests` ADD INDEX `ix_mlm_withdrawal_requests_status` (`status`);
ALTER TABLE `mlm_withdrawal_requests` ADD INDEX `ix_mlm_withdrawal_requests_request_date` (`request_date`);
ALTER TABLE `mlm_withdrawal_requests` ADD INDEX `ix_mlm_withdrawal_requests_processed_date` (`processed_date`);
ALTER TABLE `mlm_withdrawal_requests` ADD INDEX `ix_mlm_withdrawal_requests_created_at` (`created_at`);

ALTER TABLE `mobile_devices` ADD INDEX `ix_mobile_devices_created_at` (`created_at`);

ALTER TABLE `news` ADD INDEX `ix_news_created_at` (`created_at`);

ALTER TABLE `notification_logs` ADD INDEX `ix_notification_logs_notification_id` (`notification_id`);
ALTER TABLE `notification_logs` ADD INDEX `ix_notification_logs_status` (`status`);
ALTER TABLE `notification_logs` ADD INDEX `ix_notification_logs_created_at` (`created_at`);

ALTER TABLE `notification_settings` ADD INDEX `ix_notification_settings_user_id` (`user_id`);
ALTER TABLE `notification_settings` ADD INDEX `ix_notification_settings_created_at` (`created_at`);
ALTER TABLE `notification_settings` ADD INDEX `ix_notification_settings_updated_at` (`updated_at`);

ALTER TABLE `notification_templates` ADD INDEX `ix_notification_templates_created_at` (`created_at`);
ALTER TABLE `notification_templates` ADD INDEX `ix_notification_templates_updated_at` (`updated_at`);

-- SKIP: `notifications`.`user_id` already indexed
ALTER TABLE `notifications` ADD INDEX `ix_notifications_related_id` (`related_id`);
-- SKIP: `notifications`.`created_at` already indexed
ALTER TABLE `notifications` ADD INDEX `ix_notifications_read_at` (`read_at`);

ALTER TABLE `opportunities` ADD INDEX `ix_opportunities_lead_id` (`lead_id`);
ALTER TABLE `opportunities` ADD INDEX `ix_opportunities_status` (`status`);
ALTER TABLE `opportunities` ADD INDEX `ix_opportunities_created_at` (`created_at`);

ALTER TABLE `pages` ADD INDEX `ix_pages_status` (`status`);
ALTER TABLE `pages` ADD INDEX `ix_pages_created_at` (`created_at`);
ALTER TABLE `pages` ADD INDEX `ix_pages_updated_at` (`updated_at`);

ALTER TABLE `partner_certification` ADD INDEX `ix_partner_certification_created_at` (`created_at`);

ALTER TABLE `partner_rewards` ADD INDEX `ix_partner_rewards_created_at` (`created_at`);

ALTER TABLE `password_reset_temp` ADD INDEX `ix_password_reset_temp_email` (`email`);

ALTER TABLE `password_resets` ADD INDEX `ix_password_resets_email` (`email`);
ALTER TABLE `password_resets` ADD INDEX `ix_password_resets_created_at` (`created_at`);
ALTER TABLE `password_resets` ADD INDEX `ix_password_resets_expires_at` (`expires_at`);

ALTER TABLE `payment_gateway_config` ADD INDEX `ix_payment_gateway_config_created_at` (`created_at`);

ALTER TABLE `payment_logs` ADD INDEX `ix_payment_logs_user_id` (`user_id`);
ALTER TABLE `payment_logs` ADD INDEX `ix_payment_logs_status` (`status`);
ALTER TABLE `payment_logs` ADD INDEX `ix_payment_logs_created_at` (`created_at`);

ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_razorpay_order_id` (`razorpay_order_id`);
ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_status` (`status`);
ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_razorpay_payment_id` (`razorpay_payment_id`);
ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_refund_id` (`refund_id`);
ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_created_at` (`created_at`);
ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_updated_at` (`updated_at`);
ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_paid_at` (`paid_at`);
ALTER TABLE `payment_orders` ADD INDEX `ix_payment_orders_refunded_at` (`refunded_at`);

ALTER TABLE `payment_summary` ADD INDEX `ix_payment_summary_payment_id` (`payment_id`);
ALTER TABLE `payment_summary` ADD INDEX `ix_payment_summary_booking_id` (`booking_id`);
ALTER TABLE `payment_summary` ADD INDEX `ix_payment_summary_customer_id` (`customer_id`);
ALTER TABLE `payment_summary` ADD INDEX `ix_payment_summary_payment_date` (`payment_date`);

-- SKIP: `payments`.`booking_id` already indexed
-- SKIP: `payments`.`customer_id` already indexed
-- SKIP: `payments`.`payment_date` already indexed
ALTER TABLE `payments` ADD INDEX `ix_payments_transaction_id` (`transaction_id`);
-- SKIP: `payments`.`status` already indexed
ALTER TABLE `payments` ADD INDEX `ix_payments_created_at` (`created_at`);
ALTER TABLE `payments` ADD INDEX `ix_payments_updated_at` (`updated_at`);



ALTER TABLE `plot_development` ADD INDEX `ix_plot_development_land_purchase_id` (`land_purchase_id`);
ALTER TABLE `plot_development` ADD INDEX `ix_plot_development_status` (`status`);
ALTER TABLE `plot_development` ADD INDEX `ix_plot_development_customer_id` (`customer_id`);
ALTER TABLE `plot_development` ADD INDEX `ix_plot_development_sold_date` (`sold_date`);
ALTER TABLE `plot_development` ADD INDEX `ix_plot_development_created_at` (`created_at`);
ALTER TABLE `plot_development` ADD INDEX `ix_plot_development_updated_at` (`updated_at`);

ALTER TABLE `plot_master` ADD INDEX `ix_plot_master_plot_id` (`plot_id`);
ALTER TABLE `plot_master` ADD INDEX `ix_plot_master_site_id` (`site_id`);

ALTER TABLE `plot_rate_calculations` ADD INDEX `ix_plot_rate_calculations_property_id` (`property_id`);
ALTER TABLE `plot_rate_calculations` ADD INDEX `ix_plot_rate_calculations_calculation_date` (`calculation_date`);

ALTER TABLE `plots` ADD INDEX `ix_plots_colonies_id` (`colonies_id`);
ALTER TABLE `plots` ADD INDEX `ix_plots_status` (`status`);
ALTER TABLE `plots` ADD INDEX `ix_plots_created_at` (`created_at`);
ALTER TABLE `plots` ADD INDEX `ix_plots_updated_at` (`updated_at`);
-- SKIP: `plots`.`project_id` already indexed
-- SKIP: `plots`.`customer_id` already indexed
-- SKIP: `plots`.`associate_id` already indexed

ALTER TABLE `project_amenities` ADD INDEX `ix_project_amenities_project_id` (`project_id`);

ALTER TABLE `project_categories` ADD INDEX `ix_project_categories_created_at` (`created_at`);

ALTER TABLE `project_category_relations` ADD INDEX `ix_project_category_relations_project_id` (`project_id`);
ALTER TABLE `project_category_relations` ADD INDEX `ix_project_category_relations_category_id` (`category_id`);
ALTER TABLE `project_category_relations` ADD INDEX `ix_project_category_relations_created_at` (`created_at`);

ALTER TABLE `project_gallery` ADD INDEX `ix_project_gallery_project_id` (`project_id`);
ALTER TABLE `project_gallery` ADD INDEX `ix_project_gallery_drive_file_id` (`drive_file_id`);

ALTER TABLE `project_progress` ADD INDEX `ix_project_progress_project_id` (`project_id`);
ALTER TABLE `project_progress` ADD INDEX `ix_project_progress_created_at` (`created_at`);

-- SKIP: `projects`.`status` already indexed
ALTER TABLE `projects` ADD INDEX `ix_projects_completion_date` (`completion_date`);
ALTER TABLE `projects` ADD INDEX `ix_projects_launch_date` (`launch_date`);
-- SKIP: `projects`.`created_at` already indexed
ALTER TABLE `projects` ADD INDEX `ix_projects_updated_at` (`updated_at`);

-- SKIP: `properties`.`status` already indexed
ALTER TABLE `properties` ADD INDEX `ix_properties_created_at` (`created_at`);
ALTER TABLE `properties` ADD INDEX `ix_properties_updated_at` (`updated_at`);

ALTER TABLE `property` ADD INDEX `ix_property_status` (`status`);
ALTER TABLE `property` ADD INDEX `ix_property_created_at` (`created_at`);
ALTER TABLE `property` ADD INDEX `ix_property_updated_at` (`updated_at`);

ALTER TABLE `property_amenities` ADD INDEX `ix_property_amenities_property_id` (`property_id`);
ALTER TABLE `property_amenities` ADD INDEX `ix_property_amenities_created_at` (`created_at`);

ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_user_id` (`user_id`);
ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_property_id` (`property_id`);
ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_payment_order_id` (`payment_order_id`);
ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_status` (`status`);
ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_booking_date` (`booking_date`);
ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_confirmation_date` (`confirmation_date`);
ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_created_at` (`created_at`);
ALTER TABLE `property_bookings` ADD INDEX `ix_property_bookings_updated_at` (`updated_at`);

ALTER TABLE `property_development_costs` ADD INDEX `ix_property_development_costs_property_id` (`property_id`);
ALTER TABLE `property_development_costs` ADD INDEX `ix_property_development_costs_created_at` (`created_at`);

ALTER TABLE `property_favorites` ADD INDEX `ix_property_favorites_created_at` (`created_at`);
ALTER TABLE `property_favorites` ADD INDEX `ix_property_favorites_updated_at` (`updated_at`);

ALTER TABLE `property_feature_map` ADD INDEX `ix_property_feature_map_created_at` (`created_at`);

ALTER TABLE `property_feature_mappings` ADD INDEX `ix_property_feature_mappings_property_id` (`property_id`);
ALTER TABLE `property_feature_mappings` ADD INDEX `ix_property_feature_mappings_feature_id` (`feature_id`);
ALTER TABLE `property_feature_mappings` ADD INDEX `ix_property_feature_mappings_created_at` (`created_at`);

-- SKIP: `property_features`.`property_id` already indexed
ALTER TABLE `property_features` ADD INDEX `ix_property_features_created_at` (`created_at`);
ALTER TABLE `property_features` ADD INDEX `ix_property_features_updated_at` (`updated_at`);

-- SKIP: `property_images`.`property_id` already indexed
ALTER TABLE `property_images` ADD INDEX `ix_property_images_created_at` (`created_at`);
ALTER TABLE `property_images` ADD INDEX `ix_property_images_updated_at` (`updated_at`);

ALTER TABLE `property_type` ADD INDEX `ix_property_type_status` (`status`);

-- SKIP: `property_types`.`status` already indexed
ALTER TABLE `property_types` ADD INDEX `ix_property_types_created_at` (`created_at`);
ALTER TABLE `property_types` ADD INDEX `ix_property_types_updated_at` (`updated_at`);

-- SKIP: `property_visits`.`customer_id` already indexed
-- SKIP: `property_visits`.`property_id` already indexed
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_associate_id` (`associate_id`);
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_visit_date` (`visit_date`);
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_status` (`status`);
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_follow_up_date` (`follow_up_date`);
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_created_at` (`created_at`);
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_updated_at` (`updated_at`);

ALTER TABLE `purchase_invoice_items` ADD INDEX `ix_purchase_invoice_items_invoice_id` (`invoice_id`);
ALTER TABLE `purchase_invoice_items` ADD INDEX `ix_purchase_invoice_items_created_at` (`created_at`);

ALTER TABLE `purchase_invoices` ADD INDEX `ix_purchase_invoices_supplier_id` (`supplier_id`);
ALTER TABLE `purchase_invoices` ADD INDEX `ix_purchase_invoices_invoice_date` (`invoice_date`);
ALTER TABLE `purchase_invoices` ADD INDEX `ix_purchase_invoices_due_date` (`due_date`);
ALTER TABLE `purchase_invoices` ADD INDEX `ix_purchase_invoices_status` (`status`);
ALTER TABLE `purchase_invoices` ADD INDEX `ix_purchase_invoices_created_at` (`created_at`);
ALTER TABLE `purchase_invoices` ADD INDEX `ix_purchase_invoices_updated_at` (`updated_at`);

ALTER TABLE `real_estate_properties` ADD INDEX `ix_real_estate_properties_status` (`status`);
ALTER TABLE `real_estate_properties` ADD INDEX `ix_real_estate_properties_created_at` (`created_at`);
ALTER TABLE `real_estate_properties` ADD INDEX `ix_real_estate_properties_updated_at` (`updated_at`);

ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_start_date` (`start_date`);
ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_end_date` (`end_date`);
ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_next_due_date` (`next_due_date`);
ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_account_id` (`account_id`);
ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_status` (`status`);
ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_last_created_date` (`last_created_date`);
ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_created_at` (`created_at`);
ALTER TABLE `recurring_transactions` ADD INDEX `ix_recurring_transactions_updated_at` (`updated_at`);

ALTER TABLE `rent_payments` ADD INDEX `ix_rent_payments_rental_property_id` (`rental_property_id`);
ALTER TABLE `rent_payments` ADD INDEX `ix_rent_payments_tenant_id` (`tenant_id`);
ALTER TABLE `rent_payments` ADD INDEX `ix_rent_payments_due_date` (`due_date`);
ALTER TABLE `rent_payments` ADD INDEX `ix_rent_payments_paid_date` (`paid_date`);
ALTER TABLE `rent_payments` ADD INDEX `ix_rent_payments_status` (`status`);

ALTER TABLE `rental_properties` ADD INDEX `ix_rental_properties_owner_id` (`owner_id`);
ALTER TABLE `rental_properties` ADD INDEX `ix_rental_properties_status` (`status`);

ALTER TABLE `reports` ADD INDEX `ix_reports_created_at` (`created_at`);
ALTER TABLE `reports` ADD INDEX `ix_reports_updated_at` (`updated_at`);

ALTER TABLE `resale_commissions` ADD INDEX `ix_resale_commissions_associate_id` (`associate_id`);
ALTER TABLE `resale_commissions` ADD INDEX `ix_resale_commissions_resale_property_id` (`resale_property_id`);

ALTER TABLE `resale_properties` ADD INDEX `ix_resale_properties_owner_id` (`owner_id`);
ALTER TABLE `resale_properties` ADD INDEX `ix_resale_properties_status` (`status`);

ALTER TABLE `resell_commission_structure` ADD INDEX `ix_resell_commission_structure_plan_id` (`plan_id`);

ALTER TABLE `resell_plots` ADD INDEX `ix_resell_plots_created_at` (`created_at`);

ALTER TABLE `reward_history` ADD INDEX `ix_reward_history_associate_id` (`associate_id`);
ALTER TABLE `reward_history` ADD INDEX `ix_reward_history_reward_date` (`reward_date`);
ALTER TABLE `reward_history` ADD INDEX `ix_reward_history_created_at` (`created_at`);

ALTER TABLE `role_change_approvals` ADD INDEX `ix_role_change_approvals_user_id` (`user_id`);
ALTER TABLE `role_change_approvals` ADD INDEX `ix_role_change_approvals_role_id` (`role_id`);
ALTER TABLE `role_change_approvals` ADD INDEX `ix_role_change_approvals_status` (`status`);
ALTER TABLE `role_change_approvals` ADD INDEX `ix_role_change_approvals_requested_at` (`requested_at`);
ALTER TABLE `role_change_approvals` ADD INDEX `ix_role_change_approvals_decided_at` (`decided_at`);

ALTER TABLE `role_permissions` ADD INDEX `ix_role_permissions_role_id` (`role_id`);
ALTER TABLE `role_permissions` ADD INDEX `ix_role_permissions_permission_id` (`permission_id`);


ALTER TABLE `saas_instances` ADD INDEX `ix_saas_instances_status` (`status`);
ALTER TABLE `saas_instances` ADD INDEX `ix_saas_instances_created_at` (`created_at`);

ALTER TABLE `salaries` ADD INDEX `ix_salaries_employee_id` (`employee_id`);
ALTER TABLE `salaries` ADD INDEX `ix_salaries_status` (`status`);

ALTER TABLE `salary_plan` ADD INDEX `ix_salary_plan_associate_id` (`associate_id`);
ALTER TABLE `salary_plan` ADD INDEX `ix_salary_plan_payout_date` (`payout_date`);
ALTER TABLE `salary_plan` ADD INDEX `ix_salary_plan_status` (`status`);
ALTER TABLE `salary_plan` ADD INDEX `ix_salary_plan_created_at` (`created_at`);

ALTER TABLE `sales_invoice_items` ADD INDEX `ix_sales_invoice_items_invoice_id` (`invoice_id`);
ALTER TABLE `sales_invoice_items` ADD INDEX `ix_sales_invoice_items_created_at` (`created_at`);

ALTER TABLE `sales_invoices` ADD INDEX `ix_sales_invoices_customer_id` (`customer_id`);
ALTER TABLE `sales_invoices` ADD INDEX `ix_sales_invoices_invoice_date` (`invoice_date`);
ALTER TABLE `sales_invoices` ADD INDEX `ix_sales_invoices_due_date` (`due_date`);
ALTER TABLE `sales_invoices` ADD INDEX `ix_sales_invoices_status` (`status`);
ALTER TABLE `sales_invoices` ADD INDEX `ix_sales_invoices_created_at` (`created_at`);
ALTER TABLE `sales_invoices` ADD INDEX `ix_sales_invoices_updated_at` (`updated_at`);

-- SKIP: `saved_searches`.`user_id` already indexed
ALTER TABLE `saved_searches` ADD INDEX `ix_saved_searches_created_at` (`created_at`);
ALTER TABLE `saved_searches` ADD INDEX `ix_saved_searches_updated_at` (`updated_at`);

ALTER TABLE `schema_migrations` ADD INDEX `ix_schema_migrations_run_at` (`run_at`);

ALTER TABLE `seo_metadata` ADD INDEX `ix_seo_metadata_created_at` (`created_at`);
ALTER TABLE `seo_metadata` ADD INDEX `ix_seo_metadata_updated_at` (`updated_at`);

-- SKIP: `sessions`.`user_id` already indexed
ALTER TABLE `sessions` ADD INDEX `ix_sessions_created_at` (`created_at`);
ALTER TABLE `sessions` ADD INDEX `ix_sessions_last_seen_at` (`last_seen_at`);
ALTER TABLE `sessions` ADD INDEX `ix_sessions_expires_at` (`expires_at`);


ALTER TABLE `site_master` ADD INDEX `ix_site_master_site_id` (`site_id`);

ALTER TABLE `site_settings` ADD INDEX `ix_site_settings_created_at` (`created_at`);
ALTER TABLE `site_settings` ADD INDEX `ix_site_settings_updated_at` (`updated_at`);

ALTER TABLE `sites` ADD INDEX `ix_sites_status` (`status`);
ALTER TABLE `sites` ADD INDEX `ix_sites_manager_id` (`manager_id`);
ALTER TABLE `sites` ADD INDEX `ix_sites_created_at` (`created_at`);
ALTER TABLE `sites` ADD INDEX `ix_sites_updated_at` (`updated_at`);

ALTER TABLE `smart_contracts` ADD INDEX `ix_smart_contracts_status` (`status`);
ALTER TABLE `smart_contracts` ADD INDEX `ix_smart_contracts_created_at` (`created_at`);

ALTER TABLE `social_media_links` ADD INDEX `ix_social_media_links_created_at` (`created_at`);



ALTER TABLE `suppliers` ADD INDEX `ix_suppliers_mobile` (`mobile`);
ALTER TABLE `suppliers` ADD INDEX `ix_suppliers_email` (`email`);
ALTER TABLE `suppliers` ADD INDEX `ix_suppliers_last_payment_date` (`last_payment_date`);
ALTER TABLE `suppliers` ADD INDEX `ix_suppliers_status` (`status`);
ALTER TABLE `suppliers` ADD INDEX `ix_suppliers_created_at` (`created_at`);
ALTER TABLE `suppliers` ADD INDEX `ix_suppliers_updated_at` (`updated_at`);

ALTER TABLE `support_tickets` ADD INDEX `ix_support_tickets_user_id` (`user_id`);
ALTER TABLE `support_tickets` ADD INDEX `ix_support_tickets_status` (`status`);
ALTER TABLE `support_tickets` ADD INDEX `ix_support_tickets_created_at` (`created_at`);

ALTER TABLE `system_logs` ADD INDEX `ix_system_logs_user_id` (`user_id`);
ALTER TABLE `system_logs` ADD INDEX `ix_system_logs_record_id` (`record_id`);
ALTER TABLE `system_logs` ADD INDEX `ix_system_logs_created_at` (`created_at`);


-- SKIP: `tasks`.`status` already indexed
-- SKIP: `tasks`.`due_date` already indexed
ALTER TABLE `tasks` ADD INDEX `ix_tasks_completed_at` (`completed_at`);
ALTER TABLE `tasks` ADD INDEX `ix_tasks_related_id` (`related_id`);
ALTER TABLE `tasks` ADD INDEX `ix_tasks_created_at` (`created_at`);
ALTER TABLE `tasks` ADD INDEX `ix_tasks_updated_at` (`updated_at`);

ALTER TABLE `team` ADD INDEX `ix_team_status` (`status`);
ALTER TABLE `team` ADD INDEX `ix_team_created_at` (`created_at`);

ALTER TABLE `team_hierarchy` ADD INDEX `ix_team_hierarchy_associate_id` (`associate_id`);
ALTER TABLE `team_hierarchy` ADD INDEX `ix_team_hierarchy_upline_id` (`upline_id`);
ALTER TABLE `team_hierarchy` ADD INDEX `ix_team_hierarchy_created_at` (`created_at`);

ALTER TABLE `team_members` ADD INDEX `ix_team_members_email` (`email`);
ALTER TABLE `team_members` ADD INDEX `ix_team_members_phone` (`phone`);
ALTER TABLE `team_members` ADD INDEX `ix_team_members_status` (`status`);
ALTER TABLE `team_members` ADD INDEX `ix_team_members_created_at` (`created_at`);
ALTER TABLE `team_members` ADD INDEX `ix_team_members_updated_at` (`updated_at`);

ALTER TABLE `testimonials` ADD INDEX `ix_testimonials_email` (`email`);
ALTER TABLE `testimonials` ADD INDEX `ix_testimonials_status` (`status`);
ALTER TABLE `testimonials` ADD INDEX `ix_testimonials_created_at` (`created_at`);
ALTER TABLE `testimonials` ADD INDEX `ix_testimonials_updated_at` (`updated_at`);

ALTER TABLE `third_party_integrations` ADD INDEX `ix_third_party_integrations_created_at` (`created_at`);

ALTER TABLE `transactions` ADD INDEX `ix_transactions_user_id` (`user_id`);
ALTER TABLE `transactions` ADD INDEX `ix_transactions_ref_id` (`ref_id`);
ALTER TABLE `transactions` ADD INDEX `ix_transactions_created_at` (`created_at`);
ALTER TABLE `transactions` ADD INDEX `ix_transactions_updated_at` (`updated_at`);
-- SKIP: `transactions`.`customer_id` already indexed
-- SKIP: `transactions`.`property_id` already indexed

ALTER TABLE `upload_audit_log` ADD INDEX `ix_upload_audit_log_entity_id` (`entity_id`);
ALTER TABLE `upload_audit_log` ADD INDEX `ix_upload_audit_log_drive_file_id` (`drive_file_id`);
ALTER TABLE `upload_audit_log` ADD INDEX `ix_upload_audit_log_created_at` (`created_at`);

ALTER TABLE `user` ADD INDEX `ix_user_sponsor_id` (`sponsor_id`);
ALTER TABLE `user` ADD INDEX `ix_user_join_date` (`join_date`);

ALTER TABLE `user_backup` ADD INDEX `ix_user_backup_email` (`email`);
ALTER TABLE `user_backup` ADD INDEX `ix_user_backup_phone` (`phone`);
ALTER TABLE `user_backup` ADD INDEX `ix_user_backup_created_at` (`created_at`);
ALTER TABLE `user_backup` ADD INDEX `ix_user_backup_updated_at` (`updated_at`);
ALTER TABLE `user_backup` ADD INDEX `ix_user_backup_status` (`status`);

ALTER TABLE `user_permissions` ADD INDEX `ix_user_permissions_user_id` (`user_id`);
ALTER TABLE `user_permissions` ADD INDEX `ix_user_permissions_created_at` (`created_at`);
ALTER TABLE `user_permissions` ADD INDEX `ix_user_permissions_updated_at` (`updated_at`);

ALTER TABLE `user_preferences` ADD INDEX `ix_user_preferences_user_id` (`user_id`);
ALTER TABLE `user_preferences` ADD INDEX `ix_user_preferences_created_at` (`created_at`);
ALTER TABLE `user_preferences` ADD INDEX `ix_user_preferences_updated_at` (`updated_at`);

ALTER TABLE `user_roles` ADD INDEX `ix_user_roles_user_id` (`user_id`);
ALTER TABLE `user_roles` ADD INDEX `ix_user_roles_role_id` (`role_id`);

ALTER TABLE `user_sessions` ADD INDEX `ix_user_sessions_user_id` (`user_id`);
ALTER TABLE `user_sessions` ADD INDEX `ix_user_sessions_status` (`status`);

ALTER TABLE `user_social_accounts` ADD INDEX `ix_user_social_accounts_user_id` (`user_id`);
ALTER TABLE `user_social_accounts` ADD INDEX `ix_user_social_accounts_provider_id` (`provider_id`);
ALTER TABLE `user_social_accounts` ADD INDEX `ix_user_social_accounts_expires_at` (`expires_at`);
ALTER TABLE `user_social_accounts` ADD INDEX `ix_user_social_accounts_created_at` (`created_at`);
ALTER TABLE `user_social_accounts` ADD INDEX `ix_user_social_accounts_updated_at` (`updated_at`);

-- SKIP: `users`.`email` already indexed
ALTER TABLE `users` ADD INDEX `ix_users_phone` (`phone`);
-- SKIP: `users`.`status` already indexed
ALTER TABLE `users` ADD INDEX `ix_users_created_at` (`created_at`);
ALTER TABLE `users` ADD INDEX `ix_users_updated_at` (`updated_at`);

ALTER TABLE `voice_assistant_config` ADD INDEX `ix_voice_assistant_config_created_at` (`created_at`);

ALTER TABLE `whatsapp_automation_config` ADD INDEX `ix_whatsapp_automation_config_created_at` (`created_at`);

ALTER TABLE `workflow_automations` ADD INDEX `ix_workflow_automations_status` (`status`);
ALTER TABLE `workflow_automations` ADD INDEX `ix_workflow_automations_created_at` (`created_at`);

ALTER TABLE `workflows` ADD INDEX `ix_workflows_created_at` (`created_at`);

