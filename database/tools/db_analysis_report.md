# Database Analysis Report
Generated: 2025-11-01 19:39:56
Database: `apsdreamhome`
Dump File: c:\xampp\htdocs\apsdreamhome\database\apsdreamhome (5).sql

## Overview
- Tables: 227
- With Primary Key: 18
- Without Primary Key: 209
- Non-InnoDB Tables: 1
- Tables Without Any Index: 209
- Foreign Key Constraints: 6

## Tables Without Primary Key
- `about`
- `accounting_payments`
- `accounting_settings`
- `activities`
- `activity_log`
- `activity_logs`
- `admin`
- `admin_activity_log`
- `agents`
- `ai_chatbot_config`
- `ai_chatbot_interactions`
- `ai_config`
- `ai_lead_scores`
- `ai_logs`
- `api_developers`
- `api_integrations`
- `api_keys`
- `api_rate_limits`
- `api_request_logs`
- `api_sandbox`
- `api_usage`
- `app_store`
- `ar_vr_tours`
- `associates_backup`
- `associate_levels`
- `associate_mlm`
- `attendance`
- `audit_access_log`
- `audit_log`
- `audit_trail`
- `bank_accounts`
- `bank_reconciliation`
- `bank_transactions`
- `booking_payments`
- `booking_summary`
- `budget_planning`
- `builders`
- `builder_payments`
- `campaigns`
- `campaign_members`
- `career_applications`
- `cash_flow_projections`
- `chart_of_accounts`
- `chatbot_conversations`
- `chat_messages`
- `city`
- `colonies`
- `commission_payouts`
- `commission_transactions`
- `communications`
- `companies`
- `company_employees`
- `company_projects`
- `company_property_levels`
- `company_settings`
- `components`
- `construction_projects`
- `contact_backup`
- `content_backups`
- `crm_leads`
- `customers_ledger`
- `customer_documents`
- `customer_inquiries`
- `customer_journeys`
- `customer_summary`
- `data_stream_events`
- `documents`
- `emi`
- `emi_installments`
- `emi_plans`
- `emi_schedule`
- `employees`
- `expenses`
- `faqs`
- `farmers`
- `farmer_land_holdings`
- `farmer_profiles`
- `feedback`
- `feedback_tickets`
- `financial_reports`
- `financial_years`
- `foreclosure_logs`
- `gallery`
- `gata_master`
- `global_payments`
- `gst_records`
- `hybrid_commission_plans`
- `hybrid_commission_records`
- `images`
- `income_records`
- `inventory_log`
- `iot_devices`
- `iot_device_events`
- `job_applications`
- `journal_entries`
- `journal_entry_details`
- `jwt_blacklist`
- `kissan_master`
- `land_purchases`
- `layout_templates`
- `lead_files`
- `lead_notes`
- `leaves`
- `legal_documents`
- `legal_services`
- `loans`
- `loan_emi_schedule`
- `login_history`
- `marketing_campaigns`
- `marketing_strategies`
- `marketplace_apps`
- `media`
- `migrations`
- `migration_errors`
- `mlm_agents`
- `mlm_commissions`
- `mlm_commission_analytics`
- `mlm_commission_ledger`
- `mlm_commission_records`
- `mlm_commission_targets`
- `mlm_payouts`
- `mlm_tree`
- `mlm_withdrawal_requests`
- `mobile_devices`
- `news`
- `notification_logs`
- `notification_settings`
- `notification_templates`
- `opportunities`
- `pages`
- `partner_certification`
- `partner_rewards`
- `password_resets`
- `password_reset_temp`
- `payment_gateway_config`
- `payment_logs`
- `payment_orders`
- `payment_summary`
- `permissions`
- `plots`
- `plot_categories`
- `plot_development`
- `plot_master`
- `plot_rate_calculations`
- `project_amenities`
- `project_categories`
- `project_category_relations`
- `project_gallery`
- `project_progress`
- `property`
- `property_amenities`
- `property_bookings`
- `property_development_costs`
- `property_favorites`
- `property_feature_mappings`
- `property_type`
- `property_visits`
- `purchase_invoices`
- `purchase_invoice_items`
- `real_estate_properties`
- `recurring_transactions`
- `rental_properties`
- `rent_payments`
- `reports`
- `resale_commissions`
- `resale_properties`
- `resell_commission_structure`
- `resell_plots`
- `reward_history`
- `roles`
- `role_change_approvals`
- `role_permissions`
- `saas_instances`
- `salaries`
- `salary_plan`
- `sales_invoices`
- `sales_invoice_items`
- `saved_searches`
- `seo_metadata`
- `settings`
- `sites`
- `site_master`
- `site_settings`
- `smart_contracts`
- `social_media_links`
- `sponsor_running_no`
- `state`
- `suppliers`
- `support_tickets`
- `system_logs`
- `table_name`
- `team`
- `team_hierarchy`
- `team_members`
- `testimonials`
- `third_party_integrations`
- `transactions`
- `upload_audit_log`
- `user`
- `user_backup`
- `user_permissions`
- `user_preferences`
- `user_roles`
- `user_sessions`
- `user_social_accounts`
- `voice_assistant_config`
- `whatsapp_automation_config`
- `workflows`
- `workflow_automations`

## Non-InnoDB Tables
- `booking_summary`

## Tables Without Any Index
- `about`
- `accounting_payments`
- `accounting_settings`
- `activities`
- `activity_log`
- `activity_logs`
- `admin`
- `admin_activity_log`
- `agents`
- `ai_chatbot_config`
- `ai_chatbot_interactions`
- `ai_config`
- `ai_lead_scores`
- `ai_logs`
- `api_developers`
- `api_integrations`
- `api_keys`
- `api_rate_limits`
- `api_request_logs`
- `api_sandbox`
- `api_usage`
- `app_store`
- `ar_vr_tours`
- `associates_backup`
- `associate_levels`
- `associate_mlm`
- `attendance`
- `audit_access_log`
- `audit_log`
- `audit_trail`
- `bank_accounts`
- `bank_reconciliation`
- `bank_transactions`
- `booking_payments`
- `booking_summary`
- `budget_planning`
- `builders`
- `builder_payments`
- `campaigns`
- `campaign_members`
- `career_applications`
- `cash_flow_projections`
- `chart_of_accounts`
- `chatbot_conversations`
- `chat_messages`
- `city`
- `colonies`
- `commission_payouts`
- `commission_transactions`
- `communications`
- `companies`
- `company_employees`
- `company_projects`
- `company_property_levels`
- `company_settings`
- `components`
- `construction_projects`
- `contact_backup`
- `content_backups`
- `crm_leads`
- `customers_ledger`
- `customer_documents`
- `customer_inquiries`
- `customer_journeys`
- `customer_summary`
- `data_stream_events`
- `documents`
- `emi`
- `emi_installments`
- `emi_plans`
- `emi_schedule`
- `employees`
- `expenses`
- `faqs`
- `farmers`
- `farmer_land_holdings`
- `farmer_profiles`
- `feedback`
- `feedback_tickets`
- `financial_reports`
- `financial_years`
- `foreclosure_logs`
- `gallery`
- `gata_master`
- `global_payments`
- `gst_records`
- `hybrid_commission_plans`
- `hybrid_commission_records`
- `images`
- `income_records`
- `inventory_log`
- `iot_devices`
- `iot_device_events`
- `job_applications`
- `journal_entries`
- `journal_entry_details`
- `jwt_blacklist`
- `kissan_master`
- `land_purchases`
- `layout_templates`
- `lead_files`
- `lead_notes`
- `leaves`
- `legal_documents`
- `legal_services`
- `loans`
- `loan_emi_schedule`
- `login_history`
- `marketing_campaigns`
- `marketing_strategies`
- `marketplace_apps`
- `media`
- `migrations`
- `migration_errors`
- `mlm_agents`
- `mlm_commissions`
- `mlm_commission_analytics`
- `mlm_commission_ledger`
- `mlm_commission_records`
- `mlm_commission_targets`
- `mlm_payouts`
- `mlm_tree`
- `mlm_withdrawal_requests`
- `mobile_devices`
- `news`
- `notification_logs`
- `notification_settings`
- `notification_templates`
- `opportunities`
- `pages`
- `partner_certification`
- `partner_rewards`
- `password_resets`
- `password_reset_temp`
- `payment_gateway_config`
- `payment_logs`
- `payment_orders`
- `payment_summary`
- `permissions`
- `plots`
- `plot_categories`
- `plot_development`
- `plot_master`
- `plot_rate_calculations`
- `project_amenities`
- `project_categories`
- `project_category_relations`
- `project_gallery`
- `project_progress`
- `property`
- `property_amenities`
- `property_bookings`
- `property_development_costs`
- `property_favorites`
- `property_feature_mappings`
- `property_type`
- `property_visits`
- `purchase_invoices`
- `purchase_invoice_items`
- `real_estate_properties`
- `recurring_transactions`
- `rental_properties`
- `rent_payments`
- `reports`
- `resale_commissions`
- `resale_properties`
- `resell_commission_structure`
- `resell_plots`
- `reward_history`
- `roles`
- `role_change_approvals`
- `role_permissions`
- `saas_instances`
- `salaries`
- `salary_plan`
- `sales_invoices`
- `sales_invoice_items`
- `saved_searches`
- `seo_metadata`
- `settings`
- `sites`
- `site_master`
- `site_settings`
- `smart_contracts`
- `social_media_links`
- `sponsor_running_no`
- `state`
- `suppliers`
- `support_tickets`
- `system_logs`
- `table_name`
- `team`
- `team_hierarchy`
- `team_members`
- `testimonials`
- `third_party_integrations`
- `transactions`
- `upload_audit_log`
- `user`
- `user_backup`
- `user_permissions`
- `user_preferences`
- `user_roles`
- `user_sessions`
- `user_social_accounts`
- `voice_assistant_config`
- `whatsapp_automation_config`
- `workflows`
- `workflow_automations`

## Text Columns (potentially heavy)
- `about`.`content` longtext
- `accounting_payments`.`description` text
- `accounting_settings`.`setting_value` text
- `accounting_settings`.`description` text
- `activities`.`description` text
- `activity_logs`.`description` text
- `activity_logs`.`user_agent` text
- `admin_activity_log`.`details` text
- `ai_chatbot_interactions`.`query` text
- `ai_chatbot_interactions`.`response` text
- `ai_config`.`config_json` text
- `ai_logs`.`input_text` text
- `ai_logs`.`ai_response` text
- `api_keys`.`permissions` text
- `api_request_logs`.`user_agent` text
- `api_sandbox`.`payload` text
- `associate_levels`.`reward_description` text
- `audit_access_log`.`details` text
- `audit_log`.`details` text
- `audit_trail`.`old_values` longtext
- `audit_trail`.`new_values` longtext
- `audit_trail`.`changed_fields` longtext
- `audit_trail`.`user_agent` text
- `bank_reconciliation`.`notes` text
- `bank_transactions`.`description` text
- `booking_payments`.`payment_notes` text
- `budget_planning`.`notes` text
- `builders`.`address` text
- `builder_payments`.`description` text
- `campaigns`.`description` text
- `career_applications`.`comments` text
- `cash_flow_projections`.`notes` text
- `chart_of_accounts`.`description` text
- `chat_messages`.`message` text
- `colonies`.`description` text
- `colonies`.`features` text
- `colonies`.`amenities` text
- `colonies`.`coordinates` text
- `commission_payouts`.`notes` text
- `communications`.`notes` text
- `company_projects`.`description` text
- `company_settings`.`address` text
- `company_settings`.`description` text
- `components`.`content` longtext
- `construction_projects`.`description` text
- `construction_projects`.`milestone_payments` longtext
- `contact_backup`.`message` text
- `content_backups`.`content` longtext
- `customers`.`address` text
- `customers_ledger`.`address` text
- `customer_inquiries`.`message` text
- `customer_inquiries`.`response` text
- `customer_journeys`.`journey` longtext
- `data_stream_events`.`payload` longtext
- `faqs`.`question` text
- `faqs`.`answer` text
- `farmer_land_holdings`.`remarks` text
- `farmer_profiles`.`address` text
- `farmer_profiles`.`crop_types` longtext
- `farmer_profiles`.`payment_history` longtext
- `feedback`.`message` text
- `feedback_tickets`.`message` text
- `financial_reports`.`report_data` longtext
- `foreclosure_logs`.`message` text
- `hybrid_commission_plans`.`description` text
- `hybrid_commission_records`.`commission_breakdown` longtext
- `images`.`content` longtext
- `income_records`.`description` text
- `job_applications`.`message` text
- `journal_entries`.`description` text
- `journal_entry_details`.`description` text
- `layout_templates`.`description` text
- `layout_templates`.`content` longtext
- `leads`.`notes` text
- `lead_notes`.`content` text
- `legal_documents`.`ai_summary` text
- `legal_documents`.`ai_flags` text
- `legal_services`.`description` text
- `legal_services`.`features` text
- `loans`.`purpose` text
- `loans`.`security_details` text
- `marketing_campaigns`.`message` text
- `marketing_strategies`.`description` text
- `migration_errors`.`error_message` text
- `mlm_agents`.`address` text
- `mlm_commission_ledger`.`details` text
- `mlm_commission_records`.`commission_details` longtext
- `mlm_rewards_recognition`.`description` text
- `mlm_rewards_recognition`.`qualification_criteria` longtext
- `mlm_special_bonuses`.`description` text
- `mlm_special_bonuses`.`qualification_criteria` longtext
- `mlm_withdrawal_requests`.`admin_notes` text
- `news`.`summary` text
- `news`.`content` text
- `notifications`.`message` text
- `notification_logs`.`error_message` text
- `notification_templates`.`title_template` text
- `notification_templates`.`message_template` text
- `pages`.`content` longtext
- `pages`.`meta_description` text
- `pages`.`meta_keywords` text
- `payments`.`notes` text
- `payment_orders`.`notes` text
- `plot_development`.`amenities` longtext
- `projects`.`description` text
- `projects`.`address` text
- `projects`.`amenities` text
- `projects`.`images` text
- `project_progress`.`work_description` text
- `project_progress`.`photos` longtext
- `properties`.`description` text
- `property`.`description` text
- `property`.`features` text
- `property`.`gallery_images` text
- `property_bookings`.`notes` text
- `property_type`.`description` text
- `property_types`.`description` text
- `property_visits`.`notes` text
- `property_visits`.`feedback_comments` text
- `purchase_invoices`.`notes` text
- `purchase_invoice_items`.`description` text
- `recurring_transactions`.`description` text
- `reports`.`content` text
- `resale_properties`.`details` text
- `resell_plots`.`content` text
- `resell_plots`.`full_address` text
- `sales_invoices`.`notes` text
- `sales_invoice_items`.`description` text
- `saved_searches`.`search_params` text
- `seo_metadata`.`meta_description` text
- `seo_metadata`.`meta_keywords` text
- `seo_metadata`.`og_description` text
- `settings`.`value` text
- `sites`.`location` text
- `sites`.`amenities` longtext
- `sites`.`description` text
- `site_settings`.`value` text
- `smart_contracts`.`terms` text
- `suppliers`.`address` text
- `support_tickets`.`message` text
- `system_logs`.`old_values` text
- `system_logs`.`new_values` text
- `system_logs`.`user_agent` text
- `tasks`.`description` text
- `tasks`.`notes` text
- `team`.`bio` text
- `team_members`.`bio` text
- `testimonials`.`testimonial` text
- `transactions`.`description` text
- `user_preferences`.`preference_value` text
- `user_social_accounts`.`token` text
- `user_social_accounts`.`refresh_token` text
- `workflows`.`definition` longtext

## VARCHAR >255 Columns
- `about`.`image` varchar(300)
- `jwt_blacklist`.`token` varchar(500)
- `lead_files`.`file_path` varchar(500)
- `notifications`.`action_url` varchar(500)
- `property_images`.`image_path` varchar(500)
- `site_master`.`gram` varchar(300)
- `user`.`uimage` varchar(300)

## Dump vs Live Differences
- Extra tables in live: `activities`, `activity_log`, `agents`, `associates_backup`, `booking_payments`, `budget_planning`, `campaigns`, `campaign_members`, `career_applications`, `city`, `components`, `contact_backup`, `content_backups`, `data_stream_events`, `gata_master`, `images`, `job_applications`, `kissan_master`, `layout_templates`, `login_history`, `media`, `migration_errors`, `mlm_levels`, `mlm_rank_advancements`, `mlm_rewards_recognition`, `mlm_special_bonuses`, `pages`, `password_reset_temp`, `permissions`, `plot_categories`, `plot_master`, `property_type`, `resell_plots`, `site_master`, `sponsor_running_no`, `state`, `user`, `user_backup`, `user_permissions`

### Table `about`
- Type mismatches:
  - `id` dump=int(10) not null live=int(10)
  - `title` dump=varchar(100) not null live=varchar(100)
  - `content` dump=longtext not null live=longtext
  - `image` dump=varchar(300) not null live=varchar(300)

### Table `accounting_payments`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `payment_number` dump=varchar(50) not null live=varchar(50)
  - `payment_date` dump=date not null live=date
  - `payment_type` dump=enum('received','paid') not null live=enum('received','paid')
  - `party_type` dump=enum('customer','supplier','employee','bank','other') not null live=enum('customer','supplier','employee','bank','other')
  - `party_id` dump=int(11) not null live=int(11)
  - `amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `payment_method` dump=enum('cash','bank_transfer','cheque','online','upi','card') not null live=enum('cash','bank_transfer','cheque','online','upi','card')
  - `bank_account_id` dump=int(11) default null live=int(11)
  - `reference_number` dump=varchar(100) default null live=varchar(100)
  - `transaction_id` dump=varchar(100) default null live=varchar(100)
  - `invoice_id` dump=int(11) default null live=int(11)
  - `description` dump=text default null live=text
  - `created_by` dump=int(11) not null live=int(11)
  - `status` dump=enum('pending','completed','failed','cancelled') default 'completed' live=enum('pending','completed','failed','cancelled')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `accounting_settings`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `setting_key` dump=varchar(100) not null live=varchar(100)
  - `setting_value` dump=text not null live=text
  - `setting_type` dump=enum('string','integer','decimal','boolean','json') default 'string' live=enum('string','integer','decimal','boolean','json')
  - `description` dump=text default null live=text
  - `is_system` dump=tinyint(1) default 0 live=tinyint(1)
  - `updated_by` dump=int(11) default null live=int(11)
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `activity_logs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `action` dump=varchar(100) not null live=varchar(100)
  - `description` dump=text default null live=text
  - `ip_address` dump=varchar(45) default null live=varchar(45)
  - `user_agent` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `admin`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `auser` dump=varchar(100) not null live=varchar(100)
  - `apass` dump=varchar(255) default null live=varchar(255)
  - `role` dump=varchar(50) not null live=varchar(50)
  - `status` dump=varchar(20) default 'active' live=varchar(20)
  - `email` dump=varchar(255) default null live=varchar(255)
  - `phone` dump=varchar(20) default null live=varchar(20)

### Table `admin_activity_log`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `admin_id` dump=int(11) default null live=int(11)
  - `username` dump=varchar(50) default null live=varchar(50)
  - `role` dump=varchar(20) default null live=varchar(20)
  - `action` dump=varchar(100) default null live=varchar(100)
  - `details` dump=text default null live=text
  - `ip_address` dump=varchar(45) default null live=varchar(45)
  - `user_agent` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `ai_chatbot_config`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `provider` dump=varchar(50) not null live=varchar(50)
  - `api_key` dump=varchar(255) default null live=varchar(255)
  - `webhook_url` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `ai_chatbot_interactions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `query` dump=text default null live=text
  - `response` dump=text default null live=text
  - `satisfaction_score` dump=decimal(2,1) default null live=decimal(2,1)
  - `response_time` dump=decimal(5,2) default null live=decimal(5,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `ai_config`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `feature` dump=varchar(100) default null live=varchar(100)
  - `enabled` dump=tinyint(1) default 1 live=tinyint(1)
  - `config_json` dump=text default null live=text
  - `updated_by` dump=int(11) default null live=int(11)
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `ai_lead_scores`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `lead_id` dump=int(11) not null live=int(11)
  - `score` dump=int(11) not null live=int(11)
  - `scored_at` dump=datetime default current_timestamp() live=datetime

### Table `ai_logs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `action` dump=varchar(100) default null live=varchar(100)
  - `input_text` dump=text default null live=text
  - `ai_response` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `api_developers`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `dev_name` dump=varchar(255) not null live=varchar(255)
  - `email` dump=varchar(255) not null live=varchar(255)
  - `api_key` dump=varchar(64) not null live=varchar(64)
  - `status` dump=varchar(50) default 'active' live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `api_integrations`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `service_name` dump=varchar(255) not null live=varchar(255)
  - `api_url` dump=varchar(255) not null live=varchar(255)
  - `api_key` dump=varchar(255) default null live=varchar(255)
  - `status` dump=varchar(50) default 'active' live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `api_keys`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `api_key` dump=varchar(64) not null live=varchar(64)
  - `name` dump=varchar(255) not null live=varchar(255)
  - `permissions` dump=text default null live=text
  - `rate_limit` dump=int(11) default 100 live=int(11)
  - `status` dump=enum('active','revoked') default 'active' live=enum('active','revoked')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp
  - `last_used_at` dump=timestamp null default null live=timestamp

### Table `api_rate_limits`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `api_key` dump=varchar(255) not null live=varchar(255)
  - `timestamp` dump=int(11) not null live=int(11)

### Table `api_request_logs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `api_key_id` dump=int(11) not null live=int(11)
  - `endpoint` dump=varchar(255) not null live=varchar(255)
  - `request_time` dump=timestamp not null default current_timestamp() live=timestamp
  - `ip_address` dump=varchar(45) default null live=varchar(45)
  - `user_agent` dump=text default null live=text

### Table `api_sandbox`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `dev_name` dump=varchar(255) default null live=varchar(255)
  - `endpoint` dump=varchar(255) default null live=varchar(255)
  - `payload` dump=text default null live=text
  - `status` dump=varchar(50) default 'pending' live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `api_usage`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `dev_name` dump=varchar(255) default null live=varchar(255)
  - `api_key` dump=varchar(64) default null live=varchar(64)
  - `endpoint` dump=varchar(255) default null live=varchar(255)
  - `usage_count` dump=int(11) default 1 live=int(11)
  - `timestamp` dump=datetime default current_timestamp() live=datetime

### Table `app_store`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `app_name` dump=varchar(255) not null live=varchar(255)
  - `provider` dump=varchar(255) default null live=varchar(255)
  - `app_url` dump=varchar(255) default null live=varchar(255)
  - `price` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `ar_vr_tours`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `asset_url` dump=varchar(255) not null live=varchar(255)
  - `asset_type` dump=varchar(50) default null live=varchar(50)
  - `uploaded_at` dump=datetime default current_timestamp() live=datetime

### Table `associates`
- Missing columns (from dump): `parent_id`, `name`, `email`, `phone`, `mobile`, `address`, `city`, `state`, `pincode`, `aadhar_number`, `pan_number`, `bank_account`, `ifsc_code`, `sponsor_id`, `level_id`, `total_business`, `total_earnings`, `join_date`, `status`, `kyc_status`
- Extra columns (only live): `user_id`, `company_name`, `registration_number`
- Type mismatches:
  - `id` dump=int(11) not null live=bigint(20) unsigned
  - `commission_rate` dump=decimal(5,2) default 5.00 live=decimal(5,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp

### Table `associate_levels`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(50) not null live=varchar(50)
  - `commission_percent` dump=decimal(5,2) not null live=decimal(5,2)
  - `direct_referral_bonus` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `level_bonus` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `reward_description` dump=text default null live=text
  - `min_team_size` dump=int(11) default 0 live=int(11)
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp
  - `min_business` dump=decimal(15,2) not null default 0.00 live=decimal(15,2)
  - `max_business` dump=decimal(15,2) not null default 99999999.99 live=decimal(15,2)

### Table `associate_mlm`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `attendance`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `employee_id` dump=int(11) default null live=int(11)
  - `date` dump=date default null live=date
  - `in_time` dump=time default null live=time
  - `out_time` dump=time default null live=time
  - `status` dump=enum('present','absent','leave') default 'present' live=enum('present','absent','leave')

### Table `audit_access_log`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `accessed_at` dump=datetime default current_timestamp() live=datetime
  - `action` dump=varchar(50) default null live=varchar(50)
  - `user_id` dump=int(11) default null live=int(11)
  - `details` dump=text default null live=text

### Table `audit_log`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `action` dump=varchar(100) default null live=varchar(100)
  - `details` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `audit_trail`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `table_name` dump=varchar(100) not null live=varchar(100)
  - `record_id` dump=int(11) not null live=int(11)
  - `action` dump=enum('create','update','delete') not null live=enum('create','update','delete')
  - `old_values` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`old_values`)) live=longtext
  - `new_values` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`new_values`)) live=longtext
  - `changed_fields` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`changed_fields`)) live=longtext
  - `user_id` dump=int(11) not null live=int(11)
  - `ip_address` dump=varchar(45) default null live=varchar(45)
  - `user_agent` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `bank_accounts`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `account_name` dump=varchar(255) not null live=varchar(255)
  - `bank_name` dump=varchar(255) not null live=varchar(255)
  - `account_number` dump=varchar(50) not null live=varchar(50)
  - `ifsc_code` dump=varchar(15) not null live=varchar(15)
  - `branch_name` dump=varchar(255) default null live=varchar(255)
  - `account_type` dump=enum('savings','current','business','fd','loan') default 'current' live=enum('savings','current','business','fd','loan')
  - `opening_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `current_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `minimum_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `overdraft_limit` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `interest_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `is_primary` dump=tinyint(1) default 0 live=tinyint(1)
  - `status` dump=enum('active','inactive','closed') default 'active' live=enum('active','inactive','closed')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `bank_reconciliation`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `bank_account_id` dump=int(11) not null live=int(11)
  - `reconciliation_date` dump=date not null live=date
  - `book_balance` dump=decimal(15,2) not null live=decimal(15,2)
  - `bank_balance` dump=decimal(15,2) not null live=decimal(15,2)
  - `difference_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `unreconciled_transactions` dump=int(11) default 0 live=int(11)
  - `notes` dump=text default null live=text
  - `reconciled_by` dump=int(11) not null live=int(11)
  - `status` dump=enum('in_progress','completed','reviewed') default 'in_progress' live=enum('in_progress','completed','reviewed')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `bank_transactions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `bank_account_id` dump=int(11) not null live=int(11)
  - `transaction_date` dump=date not null live=date
  - `transaction_type` dump=enum('debit','credit') not null live=enum('debit','credit')
  - `amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `description` dump=text not null live=text
  - `reference_number` dump=varchar(100) default null live=varchar(100)
  - `cheque_number` dump=varchar(50) default null live=varchar(50)
  - `party_name` dump=varchar(255) default null live=varchar(255)
  - `balance_after` dump=decimal(15,2) not null live=decimal(15,2)
  - `payment_id` dump=int(11) default null live=int(11)
  - `is_reconciled` dump=tinyint(1) default 0 live=tinyint(1)
  - `reconciled_date` dump=date default null live=date
  - `bank_statement_ref` dump=varchar(100) default null live=varchar(100)
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `bookings`
- Missing columns (from dump): `booking_number`, `plot_id`, `associate_id`, `source`, `remarks`, `documents`, `created_by`, `total_amount`, `payment_plan`
- Type mismatches:
  - `id` dump=int(11) not null live=bigint(20) unsigned
  - `property_id` dump=int(11) default null live=bigint(20) unsigned
  - `customer_id` dump=int(11) not null live=bigint(20) unsigned
  - `booking_date` dump=date default null live=date
  - `status` dump=enum('booked','cancelled','completed') default 'booked' live=enum('pending','confirmed','cancelled')
  - `amount` dump=decimal(15,2) default null live=decimal(10,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `booking_summary`
- Missing columns (from dump): `id`, `budget_name`, `budget_year`, `budget_type`, `account_id`, `budgeted_amount`, `actual_amount`, `variance_amount`, `variance_percentage`, `period_start`, `period_end`, `notes`, `status`, `created_by`, `created_at`, `updated_at`
- Extra columns (only live): `booking_date`, `booking_status`, `amount`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `property_id`, `property_title`, `property_location`, `property_price`, `associate_id`, `associate_name`, `company_name`
- Type mismatches:
  - `booking_id` dump=int(11) live=bigint(20) unsigned

### Table `builders`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(255) not null live=varchar(255)
  - `email` dump=varchar(255) not null live=varchar(255)
  - `mobile` dump=varchar(15) not null live=varchar(15)
  - `address` dump=text not null live=text
  - `license_number` dump=varchar(100) default null live=varchar(100)
  - `experience_years` dump=int(11) default 0 live=int(11)
  - `specialization` dump=enum('residential','commercial','industrial','infrastructure') default 'residential' live=enum('residential','commercial','industrial','infrastructure')
  - `rating` dump=decimal(2,1) default 5.0 live=decimal(2,1)
  - `total_projects` dump=int(11) default 0 live=int(11)
  - `completed_projects` dump=int(11) default 0 live=int(11)
  - `ongoing_projects` dump=int(11) default 0 live=int(11)
  - `status` dump=enum('active','inactive','blacklisted') default 'active' live=enum('active','inactive','blacklisted')
  - `bank_account` dump=varchar(50) default null live=varchar(50)
  - `ifsc_code` dump=varchar(15) default null live=varchar(15)
  - `pan_number` dump=varchar(20) default null live=varchar(20)
  - `gst_number` dump=varchar(30) default null live=varchar(30)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `builder_payments`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `project_id` dump=int(11) not null live=int(11)
  - `builder_id` dump=int(11) not null live=int(11)
  - `payment_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `payment_type` dump=enum('advance','milestone','final','penalty','bonus') default 'milestone' live=enum('advance','milestone','final','penalty','bonus')
  - `payment_date` dump=date not null live=date
  - `payment_method` dump=enum('cash','bank_transfer','cheque','online') default 'bank_transfer' live=enum('cash','bank_transfer','cheque','online')
  - `transaction_id` dump=varchar(100) default null live=varchar(100)
  - `description` dump=text default null live=text
  - `milestone_reference` dump=varchar(255) default null live=varchar(255)
  - `invoice_number` dump=varchar(100) default null live=varchar(100)
  - `tax_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `net_amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `paid_by` dump=int(11) default null live=int(11)
  - `approved_by` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `cash_flow_projections`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `projection_date` dump=date not null live=date
  - `projected_inflow` dump=decimal(15,2) not null live=decimal(15,2)
  - `projected_outflow` dump=decimal(15,2) not null live=decimal(15,2)
  - `net_cash_flow` dump=decimal(15,2) not null live=decimal(15,2)
  - `cumulative_balance` dump=decimal(15,2) not null live=decimal(15,2)
  - `actual_inflow` dump=decimal(15,2) default null live=decimal(15,2)
  - `actual_outflow` dump=decimal(15,2) default null live=decimal(15,2)
  - `actual_net_flow` dump=decimal(15,2) default null live=decimal(15,2)
  - `variance_inflow` dump=decimal(15,2) default null live=decimal(15,2)
  - `variance_outflow` dump=decimal(15,2) default null live=decimal(15,2)
  - `notes` dump=text default null live=text
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `chart_of_accounts`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `account_code` dump=varchar(20) not null live=varchar(20)
  - `account_name` dump=varchar(255) not null live=varchar(255)
  - `account_type` dump=enum('asset','liability','equity','income','expense') not null live=enum('asset','liability','equity','income','expense')
  - `account_category` dump=enum('current_asset','fixed_asset','current_liability','long_term_liability','owner_equity','revenue','operating_expense','non_operating_expense') not null live=enum('current_asset','fixed_asset','current_liability','long_term_liability','owner_equity','revenue','operating_expense','non_operating_expense')
  - `parent_account_id` dump=int(11) default null live=int(11)
  - `is_active` dump=tinyint(1) default 1 live=tinyint(1)
  - `opening_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `current_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `description` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `chatbot_conversations`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `chat_messages`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `sender_email` dump=varchar(255) default null live=varchar(255)
  - `message` dump=text default null live=text
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `colonies`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(255) not null live=varchar(255)
  - `location` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text default null live=text
  - `total_area` dump=varchar(50) default null live=varchar(50)
  - `developed_area` dump=varchar(50) default null live=varchar(50)
  - `total_plots` dump=int(11) default 0 live=int(11)
  - `available_plots` dump=int(11) default 0 live=int(11)
  - `completion_status` dump=enum('planning','under development','completed') default 'planning' live=enum('planning','under development','completed')
  - `status` dump=enum('available','sold_out','coming_soon') default 'available' live=enum('available','sold_out','coming_soon')
  - `starting_price` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `current_price` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `features` dump=text default null live=text
  - `amenities` dump=text default null live=text
  - `coordinates` dump=text default null live=text
  - `developer` dump=varchar(255) default 'aps dream homes private limited' live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `commission_payouts`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `amount` dump=decimal(10,2) not null live=decimal(10,2)
  - `payout_date` dump=date not null live=date
  - `status` dump=enum('pending','paid','cancelled') default 'pending' live=enum('pending','paid','cancelled')
  - `transaction_id` dump=varchar(100) default null live=varchar(100)
  - `notes` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp

### Table `commission_transactions`
- Type mismatches:
  - `transaction_id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `booking_id` dump=int(11) not null live=int(11)
  - `business_amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `commission_amount` dump=decimal(10,2) not null live=decimal(10,2)
  - `commission_percentage` dump=decimal(4,2) not null live=decimal(4,2)
  - `level_difference_amount` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `upline_id` dump=int(11) default null live=int(11)
  - `transaction_date` dump=timestamp not null default current_timestamp() live=timestamp
  - `status` dump=enum('pending','paid','cancelled') default 'pending' live=enum('pending','paid','cancelled')

### Table `communications`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `lead_id` dump=int(11) default null live=int(11)
  - `type` dump=enum('call','email','meeting','whatsapp','sms') default null live=enum('call','email','meeting','whatsapp','sms')
  - `subject` dump=varchar(100) default null live=varchar(100)
  - `notes` dump=text default null live=text
  - `communication_date` dump=datetime default null live=datetime
  - `user_id` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `companies`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(100) not null live=varchar(100)
  - `address` dump=varchar(255) default null live=varchar(255)
  - `gstin` dump=varchar(20) default null live=varchar(20)
  - `pan` dump=varchar(20) default null live=varchar(20)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `company_employees`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `company_id` dump=int(11) default null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `position` dump=varchar(100) default null live=varchar(100)
  - `salary` dump=decimal(12,2) default null live=decimal(12,2)
  - `join_date` dump=date default null live=date
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')

### Table `company_projects`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `project_name` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text default null live=text
  - `location` dump=varchar(255) default null live=varchar(255)
  - `project_type` dump=enum('residential','commercial','mixed') default 'residential' live=enum('residential','commercial','mixed')
  - `status` dump=enum('planning','ongoing','completed','cancelled') default 'planning' live=enum('planning','ongoing','completed','cancelled')
  - `start_date` dump=date default null live=date
  - `end_date` dump=date default null live=date
  - `budget` dump=decimal(15,2) default null live=decimal(15,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `company_property_levels`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `plan_id` dump=int(11) not null live=int(11)
  - `level_name` dump=varchar(100) not null live=varchar(100)
  - `level_order` dump=int(11) not null live=int(11)
  - `direct_commission_percentage` dump=decimal(5,2) not null live=decimal(5,2)
  - `team_commission_percentage` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `level_bonus_percentage` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `matching_bonus_percentage` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `leadership_bonus_percentage` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `monthly_target` dump=decimal(15,2) not null live=decimal(15,2)
  - `min_plot_value` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `max_plot_value` dump=decimal(15,2) default 999999999.00 live=decimal(15,2)

### Table `company_settings`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `company_name` dump=varchar(255) not null live=varchar(255)
  - `phone` dump=varchar(20) not null live=varchar(20)
  - `email` dump=varchar(255) not null live=varchar(255)
  - `address` dump=text default null live=text
  - `description` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `construction_projects`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `project_name` dump=varchar(255) not null live=varchar(255)
  - `builder_id` dump=int(11) not null live=int(11)
  - `site_id` dump=int(11) default null live=int(11)
  - `project_type` dump=enum('residential','commercial','infrastructure','mixed_use') default 'residential' live=enum('residential','commercial','infrastructure','mixed_use')
  - `start_date` dump=date default null live=date
  - `estimated_completion` dump=date default null live=date
  - `actual_completion` dump=date default null live=date
  - `budget_allocated` dump=decimal(15,2) not null live=decimal(15,2)
  - `amount_spent` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `progress_percentage` dump=int(11) default 0 live=int(11)
  - `status` dump=enum('planning','in_progress','on_hold','completed','cancelled') default 'planning' live=enum('planning','in_progress','on_hold','completed','cancelled')
  - `description` dump=text default null live=text
  - `contract_amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `advance_paid` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `milestone_payments` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`milestone_payments`)) live=longtext
  - `quality_rating` dump=decimal(2,1) default null live=decimal(2,1)
  - `completion_certificate` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp
  - `last_updated` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `crm_leads`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `customers`
- Missing columns (from dump): `customer_type`, `kyc_status`, `name`, `email`, `mobile`, `alternate_phone`, `date_of_birth`, `gender`, `aadhar_number`, `pan_number`, `occupation`, `company_name`, `monthly_income`, `referred_by`, `referrer_code`, `status`, `notes`
- Type mismatches:
  - `id` dump=int(11) not null live=bigint(20) unsigned
  - `user_id` dump=int(11) default null live=bigint(20) unsigned
  - `address` dump=text default null live=text
  - `city` dump=varchar(100) default null live=varchar(100)
  - `state` dump=varchar(100) default null live=varchar(100)
  - `pincode` dump=varchar(10) default null live=varchar(20)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `customers_ledger`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `customer_name` dump=varchar(255) not null live=varchar(255)
  - `mobile` dump=varchar(15) not null live=varchar(15)
  - `email` dump=varchar(255) default null live=varchar(255)
  - `address` dump=text default null live=text
  - `gst_number` dump=varchar(20) default null live=varchar(20)
  - `pan_number` dump=varchar(20) default null live=varchar(20)
  - `credit_limit` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `credit_days` dump=int(11) default 0 live=int(11)
  - `opening_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `current_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_sales` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_payments` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `last_payment_date` dump=date default null live=date
  - `status` dump=enum('active','inactive','blocked') default 'active' live=enum('active','inactive','blocked')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `customer_documents`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `doc_name` dump=varchar(255) default null live=varchar(255)
  - `status` dump=varchar(50) default 'uploaded' live=varchar(50)
  - `uploaded_at` dump=datetime default current_timestamp() live=datetime
  - `blockchain_hash` dump=varchar(255) default null live=varchar(255)

### Table `customer_inquiries`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `subject` dump=varchar(255) not null live=varchar(255)
  - `message` dump=text not null live=text
  - `inquiry_type` dump=enum('general','payment','booking','technical','complaint') default 'general' live=enum('general','payment','booking','technical','complaint')
  - `status` dump=enum('open','in_progress','resolved','closed') default 'open' live=enum('open','in_progress','resolved','closed')
  - `priority` dump=enum('low','medium','high','urgent') default 'medium' live=enum('low','medium','high','urgent')
  - `assigned_to` dump=int(11) default null live=int(11)
  - `response` dump=text default null live=text
  - `response_date` dump=timestamp null default null live=timestamp
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `customer_journeys`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `journey` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`journey`)) live=longtext
  - `started_at` dump=datetime default current_timestamp() live=datetime
  - `last_touch_at` dump=datetime default current_timestamp() live=datetime

### Table `customer_summary`
- Missing columns (from dump): `id`, `event_type`, `payload`, `streamed_at`
- Extra columns (only live): `customer_name`, `email`, `mobile`, `customer_type`, `kyc_status`, `total_bookings`, `total_investment`, `last_booking_date`, `days_since_last_booking`, `customer_since`

### Table `documents`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `property_id` dump=int(11) default null live=int(11)
  - `type` dump=varchar(50) default null live=varchar(50)
  - `url` dump=varchar(255) default null live=varchar(255)
  - `uploaded_on` dump=timestamp not null default current_timestamp() live=timestamp
  - `drive_file_id` dump=varchar(128) default null live=varchar(128)

### Table `emi`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `property_id` dump=int(11) default null live=int(11)
  - `amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `due_date` dump=date default null live=date
  - `paid_date` dump=date default null live=date
  - `status` dump=enum('pending','paid','overdue') default 'pending' live=enum('pending','paid','overdue')

### Table `emi_installments`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `emi_plan_id` dump=int(11) not null live=int(11)
  - `installment_number` dump=int(11) not null live=int(11)
  - `amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `principal_amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `interest_amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `due_date` dump=date not null live=date
  - `payment_date` dump=date default null live=date
  - `payment_status` dump=enum('pending','paid','overdue') not null default 'pending' live=enum('pending','paid','overdue')
  - `payment_id` dump=int(11) default null live=int(11)
  - `reminder_sent` dump=tinyint(1) default 0 live=tinyint(1)
  - `last_reminder_date` dump=datetime default null live=datetime
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp

### Table `emi_plans`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `total_amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `interest_rate` dump=decimal(5,2) not null live=decimal(5,2)
  - `tenure_months` dump=int(11) not null live=int(11)
  - `emi_amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `down_payment` dump=decimal(12,2) not null live=decimal(12,2)
  - `start_date` dump=date not null live=date
  - `end_date` dump=date not null live=date
  - `status` dump=enum('active','completed','defaulted','cancelled') not null default 'active' live=enum('active','completed','defaulted','cancelled')
  - `foreclosure_date` dump=date default null live=date
  - `foreclosure_amount` dump=decimal(12,2) default null live=decimal(12,2)
  - `foreclosure_payment_id` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp
  - `created_by` dump=int(11) not null live=int(11)

### Table `emi_schedule`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `booking_id` dump=int(11) default null live=int(11)
  - `emi_number` dump=int(11) not null live=int(11)
  - `amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `due_date` dump=date not null live=date
  - `status` dump=enum('pending','paid','overdue','waived') default 'pending' live=enum('pending','paid','overdue','waived')
  - `paid_date` dump=date default null live=date
  - `paid_amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `late_fee` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `payment_id` dump=int(11) default null live=int(11)
  - `reminder_sent` dump=int(11) default 0 live=int(11)
  - `last_reminder` dump=date default null live=date
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `employees`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(100) default null live=varchar(100)
  - `email` dump=varchar(100) default null live=varchar(100)
  - `phone` dump=varchar(20) default null live=varchar(20)
  - `role` dump=varchar(50) default null live=varchar(50)
  - `salary` dump=decimal(12,2) default null live=decimal(12,2)
  - `join_date` dump=date default null live=date
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')
  - `password` dump=varchar(255) not null live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `expenses`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `amount` dump=decimal(12,2) default null live=decimal(12,2)
  - `source` dump=varchar(100) default null live=varchar(100)
  - `expense_date` dump=date default null live=date
  - `description` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `faqs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `question` dump=text not null live=text
  - `answer` dump=text not null live=text
  - `category` dump=varchar(100) default 'general' live=varchar(100)
  - `display_order` dump=int(11) default 0 live=int(11)
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `farmers`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `land_area` dump=decimal(10,2) default null live=decimal(10,2)
  - `location` dump=varchar(255) default null live=varchar(255)
  - `kyc_doc` dump=varchar(255) default null live=varchar(255)

### Table `farmer_land_holdings`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `farmer_id` dump=int(11) not null live=int(11)
  - `khasra_number` dump=varchar(50) default null live=varchar(50)
  - `land_area` dump=decimal(10,2) not null live=decimal(10,2)
  - `land_area_unit` dump=varchar(20) default 'sqft' live=varchar(20)
  - `land_type` dump=enum('agricultural','residential','commercial','mixed') default 'agricultural' live=enum('agricultural','residential','commercial','mixed')
  - `soil_type` dump=varchar(100) default null live=varchar(100)
  - `irrigation_source` dump=varchar(100) default null live=varchar(100)
  - `water_source` dump=varchar(100) default null live=varchar(100)
  - `electricity_available` dump=tinyint(1) default 0 live=tinyint(1)
  - `road_access` dump=tinyint(1) default 0 live=tinyint(1)
  - `location` dump=varchar(255) default null live=varchar(255)
  - `village` dump=varchar(100) default null live=varchar(100)
  - `tehsil` dump=varchar(100) default null live=varchar(100)
  - `district` dump=varchar(100) default null live=varchar(100)
  - `state` dump=varchar(100) default null live=varchar(100)
  - `land_value` dump=decimal(15,2) default null live=decimal(15,2)
  - `current_status` dump=enum('cultivated','fallow','sold','under_acquisition','disputed') default 'cultivated' live=enum('cultivated','fallow','sold','under_acquisition','disputed')
  - `ownership_document` dump=varchar(255) default null live=varchar(255)
  - `mutation_document` dump=varchar(255) default null live=varchar(255)
  - `acquisition_status` dump=enum('not_acquired','under_negotiation','acquired','rejected') default 'not_acquired' live=enum('not_acquired','under_negotiation','acquired','rejected')
  - `acquisition_date` dump=date default null live=date
  - `acquisition_amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `payment_status` dump=enum('pending','partial','completed') default 'pending' live=enum('pending','partial','completed')
  - `payment_received` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `remarks` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `farmer_profiles`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `farmer_number` dump=varchar(50) not null live=varchar(50)
  - `full_name` dump=varchar(100) not null live=varchar(100)
  - `father_name` dump=varchar(100) default null live=varchar(100)
  - `spouse_name` dump=varchar(100) default null live=varchar(100)
  - `date_of_birth` dump=date default null live=date
  - `gender` dump=enum('male','female','other') default 'male' live=enum('male','female','other')
  - `phone` dump=varchar(15) not null live=varchar(15)
  - `alternate_phone` dump=varchar(15) default null live=varchar(15)
  - `email` dump=varchar(100) default null live=varchar(100)
  - `address` dump=text default null live=text
  - `village` dump=varchar(100) default null live=varchar(100)
  - `post_office` dump=varchar(100) default null live=varchar(100)
  - `tehsil` dump=varchar(100) default null live=varchar(100)
  - `district` dump=varchar(100) default null live=varchar(100)
  - `state` dump=varchar(100) default null live=varchar(100)
  - `pincode` dump=varchar(10) default null live=varchar(10)
  - `aadhar_number` dump=varchar(20) default null live=varchar(20)
  - `pan_number` dump=varchar(20) default null live=varchar(20)
  - `voter_id` dump=varchar(20) default null live=varchar(20)
  - `bank_account_number` dump=varchar(30) default null live=varchar(30)
  - `bank_name` dump=varchar(100) default null live=varchar(100)
  - `ifsc_code` dump=varchar(20) default null live=varchar(20)
  - `account_holder_name` dump=varchar(100) default null live=varchar(100)
  - `total_land_holding` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `cultivated_area` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `irrigated_area` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `non_irrigated_area` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `crop_types` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`crop_types`)) live=longtext
  - `farming_experience` dump=int(11) default 0 live=int(11)
  - `education_level` dump=varchar(50) default null live=varchar(50)
  - `family_members` dump=int(11) default 0 live=int(11)
  - `family_income` dump=decimal(15,2) default null live=decimal(15,2)
  - `credit_score` dump=enum('excellent','good','fair','poor') default 'fair' live=enum('excellent','good','fair','poor')
  - `credit_limit` dump=decimal(15,2) default 50000.00 live=decimal(15,2)
  - `outstanding_loans` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `payment_history` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`payment_history`)) live=longtext
  - `status` dump=enum('active','inactive','blacklisted','under_review') default 'active' live=enum('active','inactive','blacklisted','under_review')
  - `associate_id` dump=int(11) default null live=int(11)
  - `created_by` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `feedback`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `message` dump=text default null live=text
  - `status` dump=varchar(20) default 'new' live=varchar(20)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `feedback_tickets`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `message` dump=text not null live=text
  - `status` dump=enum('open','closed') default 'open' live=enum('open','closed')
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `financial_reports`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `report_type` dump=enum('profit_loss','balance_sheet','cash_flow','trial_balance','ledger','aging','gst_summary') not null live=enum('profit_loss','balance_sheet','cash_flow','trial_balance','ledger','aging','gst_summary')
  - `report_period` dump=varchar(50) not null live=varchar(50)
  - `from_date` dump=date not null live=date
  - `to_date` dump=date not null live=date
  - `report_data` dump=longtext not null live=longtext
  - `generated_by` dump=int(11) not null live=int(11)
  - `generated_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `is_cached` dump=tinyint(1) default 1 live=tinyint(1)
  - `cache_expires_at` dump=datetime default null live=datetime

### Table `financial_years`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `year_name` dump=varchar(20) not null live=varchar(20)
  - `start_date` dump=date not null live=date
  - `end_date` dump=date not null live=date
  - `is_current` dump=tinyint(1) default 0 live=tinyint(1)
  - `is_closed` dump=tinyint(1) default 0 live=tinyint(1)
  - `closing_date` dump=date default null live=date
  - `opening_balances_set` dump=tinyint(1) default 0 live=tinyint(1)
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `foreclosure_logs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `emi_plan_id` dump=int(11) not null live=int(11)
  - `status` dump=enum('success','failed') not null live=enum('success','failed')
  - `message` dump=text default null live=text
  - `attempted_by` dump=int(11) not null live=int(11)
  - `attempted_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `gallery`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `image_path` dump=varchar(255) not null live=varchar(255)
  - `caption` dump=varchar(255) default null live=varchar(255)
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `global_payments`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `client` dump=varchar(255) default null live=varchar(255)
  - `amount` dump=decimal(12,2) default 0.00 live=decimal(12,2)
  - `currency` dump=varchar(10) default 'inr' live=varchar(10)
  - `purpose` dump=varchar(255) default null live=varchar(255)
  - `status` dump=varchar(50) default 'pending' live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `gst_records`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `transaction_type` dump=enum('sale','purchase','expense','income') not null live=enum('sale','purchase','expense','income')
  - `transaction_id` dump=int(11) not null live=int(11)
  - `invoice_number` dump=varchar(100) not null live=varchar(100)
  - `transaction_date` dump=date not null live=date
  - `party_name` dump=varchar(255) not null live=varchar(255)
  - `party_gstin` dump=varchar(20) default null live=varchar(20)
  - `hsn_code` dump=varchar(20) default null live=varchar(20)
  - `taxable_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `cgst_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `cgst_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `sgst_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `sgst_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `igst_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `igst_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `cess_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `cess_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_tax` dump=decimal(15,2) not null live=decimal(15,2)
  - `total_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `place_of_supply` dump=varchar(100) default null live=varchar(100)
  - `reverse_charge` dump=tinyint(1) default 0 live=tinyint(1)
  - `gst_return_period` dump=varchar(10) default null live=varchar(10)
  - `filed_in_gstr` dump=tinyint(1) default 0 live=tinyint(1)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `hybrid_commission_plans`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `plan_name` dump=varchar(255) not null live=varchar(255)
  - `plan_code` dump=varchar(50) not null live=varchar(50)
  - `plan_type` dump=enum('company_mlm','resell_fixed','hybrid') not null live=enum('company_mlm','resell_fixed','hybrid')
  - `description` dump=text default null live=text
  - `total_commission_percentage` dump=decimal(5,2) not null live=decimal(5,2)
  - `company_commission_percentage` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `resell_commission_percentage` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `development_cost_included` dump=tinyint(1) default 1 live=tinyint(1)
  - `status` dump=enum('active','inactive','draft') default 'draft' live=enum('active','inactive','draft')
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `hybrid_commission_records`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) default null live=int(11)
  - `sale_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `commission_amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `commission_type` dump=enum('company_mlm','resell_fixed','direct') not null live=enum('company_mlm','resell_fixed','direct')
  - `commission_breakdown` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`commission_breakdown`)) live=longtext
  - `level_achieved` dump=varchar(100) default null live=varchar(100)
  - `payout_status` dump=enum('pending','approved','paid','cancelled') default 'pending' live=enum('pending','approved','paid','cancelled')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `paid_at` dump=timestamp null default null live=timestamp

### Table `income_records`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `income_number` dump=varchar(50) not null live=varchar(50)
  - `income_date` dump=date not null live=date
  - `income_category` dump=varchar(255) not null live=varchar(255)
  - `income_subcategory` dump=varchar(255) default null live=varchar(255)
  - `amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `description` dump=text not null live=text
  - `customer_name` dump=varchar(255) default null live=varchar(255)
  - `invoice_number` dump=varchar(100) default null live=varchar(100)
  - `payment_method` dump=enum('cash','bank_transfer','cheque','online','upi','card') not null live=enum('cash','bank_transfer','cheque','online','upi','card')
  - `bank_account_id` dump=int(11) default null live=int(11)
  - `customer_id` dump=int(11) default null live=int(11)
  - `project_id` dump=int(11) default null live=int(11)
  - `tax_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `tax_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `is_recurring` dump=tinyint(1) default 0 live=tinyint(1)
  - `recurring_frequency` dump=enum('monthly','quarterly','yearly') default null live=enum('monthly','quarterly','yearly')
  - `status` dump=enum('pending','received','cancelled') default 'received' live=enum('pending','received','cancelled')
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `inventory_log`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `plot_id` dump=int(11) default null live=int(11)
  - `action` dump=enum('created','booked','sold','transferred','released') default null live=enum('created','booked','sold','transferred','released')
  - `user_id` dump=int(11) default null live=int(11)
  - `note` dump=varchar(255) default null live=varchar(255)
  - `action_date` dump=datetime default null live=datetime
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `iot_devices`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `device_name` dump=varchar(255) default null live=varchar(255)
  - `device_type` dump=varchar(100) default null live=varchar(100)
  - `status` dump=varchar(50) default 'active' live=varchar(50)
  - `last_seen` dump=datetime default current_timestamp() live=datetime
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `iot_device_events`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `device_id` dump=int(11) not null live=int(11)
  - `event_type` dump=varchar(100) default null live=varchar(100)
  - `event_value` dump=varchar(255) default null live=varchar(255)
  - `event_time` dump=datetime default current_timestamp() live=datetime

### Table `journal_entries`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `journal_number` dump=varchar(50) not null live=varchar(50)
  - `entry_date` dump=date not null live=date
  - `reference_number` dump=varchar(100) default null live=varchar(100)
  - `description` dump=text not null live=text
  - `total_debit` dump=decimal(15,2) not null live=decimal(15,2)
  - `total_credit` dump=decimal(15,2) not null live=decimal(15,2)
  - `entry_type` dump=enum('manual','system','adjustment','closing') default 'manual' live=enum('manual','system','adjustment','closing')
  - `source_document` dump=enum('invoice','payment','expense','transfer','adjustment','opening') default null live=enum('invoice','payment','expense','transfer','adjustment','opening')
  - `source_id` dump=int(11) default null live=int(11)
  - `created_by` dump=int(11) not null live=int(11)
  - `approved_by` dump=int(11) default null live=int(11)
  - `approval_date` dump=datetime default null live=datetime
  - `status` dump=enum('draft','approved','rejected') default 'draft' live=enum('draft','approved','rejected')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `journal_entry_details`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `journal_entry_id` dump=int(11) not null live=int(11)
  - `account_id` dump=int(11) not null live=int(11)
  - `description` dump=text default null live=text
  - `debit_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `credit_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `reference_type` dump=enum('customer','supplier','bank','employee','other') default null live=enum('customer','supplier','bank','employee','other')
  - `reference_id` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `jwt_blacklist`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `token` dump=varchar(500) not null live=varchar(500)
  - `expires_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `land_purchases`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `farmer_id` dump=int(11) default null live=int(11)
  - `property_id` dump=int(11) default null live=int(11)
  - `purchase_date` dump=date default null live=date
  - `amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `registry_no` dump=varchar(100) default null live=varchar(100)
  - `agreement_doc` dump=varchar(255) default null live=varchar(255)

### Table `leads`
- Missing columns (from dump): `contact`, `converted_amount`
- Extra columns (only live): `email`, `phone`, `property_interest`, `budget_range`, `location_preference`, `updated_at`, `is_converted`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(100) default null live=varchar(100)
  - `source` dump=varchar(100) default null live=varchar(50)
  - `assigned_to` dump=int(11) default null live=int(11)
  - `status` dump=varchar(50) default null live=varchar(50)
  - `notes` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `converted_at` dump=datetime default null live=timestamp

### Table `lead_files`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `lead_id` dump=int(11) default null live=int(11)
  - `original_name` dump=varchar(255) default null live=varchar(255)
  - `file_path` dump=varchar(500) default null live=varchar(500)
  - `file_type` dump=varchar(100) default null live=varchar(100)
  - `file_size` dump=int(11) default null live=int(11)
  - `uploaded_by` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `lead_notes`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `lead_id` dump=int(11) default null live=int(11)
  - `content` dump=text default null live=text
  - `is_private` dump=tinyint(1) default 0 live=tinyint(1)
  - `created_by` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `leaves`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `employee_id` dump=int(11) default null live=int(11)
  - `leave_type` dump=varchar(50) default null live=varchar(50)
  - `from_date` dump=date default null live=date
  - `to_date` dump=date default null live=date
  - `status` dump=enum('pending','approved','rejected') default 'pending' live=enum('pending','approved','rejected')
  - `remarks` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `legal_documents`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `file_name` dump=varchar(255) not null live=varchar(255)
  - `file_url` dump=varchar(255) not null live=varchar(255)
  - `review_status` dump=varchar(50) default 'pending' live=varchar(50)
  - `ai_summary` dump=text default null live=text
  - `ai_flags` dump=text default null live=text
  - `uploaded_at` dump=datetime default current_timestamp() live=datetime

### Table `legal_services`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `title` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text not null live=text
  - `icon` dump=varchar(100) default null live=varchar(100)
  - `price_range` dump=varchar(100) default null live=varchar(100)
  - `duration` dump=varchar(50) default null live=varchar(50)
  - `features` dump=text default null live=text
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')
  - `display_order` dump=int(11) default 0 live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `loans`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `loan_number` dump=varchar(50) not null live=varchar(50)
  - `loan_type` dump=enum('business','personal','property','vehicle','equipment') not null live=enum('business','personal','property','vehicle','equipment')
  - `lender_name` dump=varchar(255) not null live=varchar(255)
  - `loan_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `interest_rate` dump=decimal(5,2) not null live=decimal(5,2)
  - `tenure_months` dump=int(11) not null live=int(11)
  - `emi_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `start_date` dump=date not null live=date
  - `end_date` dump=date not null live=date
  - `disbursement_date` dump=date default null live=date
  - `outstanding_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `paid_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `bank_account_id` dump=int(11) default null live=int(11)
  - `purpose` dump=text default null live=text
  - `security_details` dump=text default null live=text
  - `status` dump=enum('applied','approved','disbursed','active','closed','defaulted') default 'applied' live=enum('applied','approved','disbursed','active','closed','defaulted')
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `loan_emi_schedule`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `loan_id` dump=int(11) not null live=int(11)
  - `emi_number` dump=int(11) not null live=int(11)
  - `due_date` dump=date not null live=date
  - `emi_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `principal_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `interest_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `outstanding_balance` dump=decimal(15,2) not null live=decimal(15,2)
  - `paid_date` dump=date default null live=date
  - `paid_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `late_fee` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `status` dump=enum('pending','paid','overdue','partial') default 'pending' live=enum('pending','paid','overdue','partial')
  - `payment_id` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `marketing_campaigns`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(255) not null live=varchar(255)
  - `type` dump=enum('email','sms') not null live=enum('email','sms')
  - `message` dump=text not null live=text
  - `scheduled_at` dump=datetime default null live=datetime
  - `status` dump=varchar(50) default 'scheduled' live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `marketing_strategies`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `title` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text not null live=text
  - `image_url` dump=varchar(255) default null live=varchar(255)
  - `active` dump=tinyint(1) default 1 live=tinyint(1)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `marketplace_apps`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `app_name` dump=varchar(255) not null live=varchar(255)
  - `provider` dump=varchar(255) default null live=varchar(255)
  - `app_url` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `migrations`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `version` dump=varchar(20) not null live=varchar(20)
  - `migration_name` dump=varchar(255) not null live=varchar(255)
  - `applied_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `mlm_agents`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `full_name` dump=varchar(255) not null live=varchar(255)
  - `mobile` dump=varchar(20) not null live=varchar(20)
  - `email` dump=varchar(100) not null live=varchar(100)
  - `aadhar_number` dump=varchar(20) default null live=varchar(20)
  - `pan_number` dump=varchar(20) default null live=varchar(20)
  - `address` dump=text default null live=text
  - `state` dump=varchar(100) default null live=varchar(100)
  - `district` dump=varchar(100) default null live=varchar(100)
  - `pin_code` dump=varchar(10) default null live=varchar(10)
  - `bank_account` dump=varchar(50) default null live=varchar(50)
  - `ifsc_code` dump=varchar(20) default null live=varchar(20)
  - `referral_code` dump=varchar(20) not null live=varchar(20)
  - `sponsor_id` dump=int(11) default null live=int(11)
  - `password` dump=varchar(255) not null live=varchar(255)
  - `current_level` dump=varchar(50) default 'associate' live=varchar(50)
  - `total_business` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_team_size` dump=int(11) default 0 live=int(11)
  - `direct_referrals` dump=int(11) default 0 live=int(11)
  - `status` dump=enum('active','inactive','pending') default 'pending' live=enum('active','inactive','pending')
  - `registration_date` dump=datetime default null live=datetime
  - `last_login` dump=datetime default null live=datetime

### Table `mlm_commissions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `level` dump=int(11) not null live=int(11)
  - `commission_amount` dump=decimal(10,2) not null live=decimal(10,2)
  - `payout_id` dump=int(11) default null live=int(11)
  - `status` dump=enum('pending','paid','cancelled') default 'pending' live=enum('pending','paid','cancelled')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp

### Table `mlm_commission_analytics`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `period_date` dump=date not null live=date
  - `total_earned` dump=decimal(12,2) not null live=decimal(12,2)
  - `total_paid` dump=decimal(12,2) not null live=decimal(12,2)
  - `pending_amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `direct_commissions` dump=decimal(10,2) default null live=decimal(10,2)
  - `team_commissions` dump=decimal(10,2) default null live=decimal(10,2)
  - `bonus_commissions` dump=decimal(10,2) default null live=decimal(10,2)
  - `rank_advances` dump=decimal(10,2) default null live=decimal(10,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `mlm_commission_ledger`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `commission_id` dump=int(11) not null live=int(11)
  - `action` dump=enum('created','updated','paid','cancelled') not null live=enum('created','updated','paid','cancelled')
  - `details` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `mlm_commission_records`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `booking_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `commission_details` dump=longtext character set utf8mb4 collate utf8mb4_bin not null check (json_valid(`commission_details`)) live=longtext
  - `total_commission` dump=decimal(12,2) not null live=decimal(12,2)
  - `status` dump=enum('calculated','approved','paid','cancelled') default 'calculated' live=enum('calculated','approved','paid','cancelled')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `mlm_commission_targets`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `target_period` dump=enum('monthly','quarterly','yearly') not null live=enum('monthly','quarterly','yearly')
  - `target_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `achieved_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `target_type` dump=enum('personal_sales','team_sales','recruitment') not null live=enum('personal_sales','team_sales','recruitment')
  - `start_date` dump=date not null live=date
  - `end_date` dump=date not null live=date
  - `reward_amount` dump=decimal(10,2) default null live=decimal(10,2)
  - `status` dump=enum('active','achieved','expired') default 'active' live=enum('active','achieved','expired')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `mlm_payouts`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `payout_month` dump=varchar(7) not null comment 'yyyy-mm format' live=varchar(7)
  - `total_commission` dump=decimal(15,2) not null live=decimal(15,2)
  - `tds_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `admin_charges` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `net_payout` dump=decimal(15,2) not null live=decimal(15,2)
  - `status` dump=enum('calculated','processed','paid','failed') default 'calculated' live=enum('calculated','processed','paid','failed')
  - `payment_method` dump=enum('bank_transfer','upi','cheque','cash') default 'bank_transfer' live=enum('bank_transfer','upi','cheque','cash')
  - `payment_reference` dump=varchar(100) default null live=varchar(100)
  - `processed_by` dump=int(11) default null live=int(11)
  - `processed_at` dump=timestamp null default null live=timestamp
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `mlm_performance`
- Missing columns (from dump): `previous_level`, `new_level`, `bonus_amount`, `payout_status`, `advancement_date`
- Extra columns (only live): `associate_name`, `commission_rate`, `status`, `total_referrals`, `total_sales`, `total_sales_amount`, `estimated_commission`, `updated_at`
- Type mismatches:
  - `associate_id` dump=int(11) not null live=int(11)
  - `id` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=datetime

### Table `mlm_tree`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `parent_id` dump=int(11) default null live=int(11)
  - `level` dump=int(11) default null live=int(11)
  - `join_date` dump=date default null live=date

### Table `mlm_withdrawal_requests`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `amount` dump=decimal(12,2) not null live=decimal(12,2)
  - `available_balance` dump=decimal(12,2) not null live=decimal(12,2)
  - `status` dump=enum('pending','approved','rejected','processed') default 'pending' live=enum('pending','approved','rejected','processed')
  - `request_date` dump=date not null live=date
  - `processed_date` dump=date default null live=date
  - `admin_notes` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `mobile_devices`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `device_user` dump=varchar(255) not null live=varchar(255)
  - `push_token` dump=varchar(255) default null live=varchar(255)
  - `platform` dump=varchar(20) default null live=varchar(20)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `news`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `title` dump=varchar(255) not null live=varchar(255)
  - `date` dump=date not null live=date
  - `summary` dump=text default null live=text
  - `image` dump=varchar(255) default null live=varchar(255)
  - `content` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `notifications`
- Extra columns (only live): `title`, `is_read`, `priority`, `related_id`, `related_type`, `action_url`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `message` dump=text default null live=text
  - `type` dump=varchar(50) default null live=varchar(50)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `read_at` dump=timestamp null default null live=timestamp

### Table `notification_logs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `notification_id` dump=int(11) not null live=int(11)
  - `status` dump=varchar(50) not null live=varchar(50)
  - `error_message` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `notification_settings`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `type` dump=varchar(50) not null live=varchar(50)
  - `email_enabled` dump=tinyint(1) default 1 live=tinyint(1)
  - `push_enabled` dump=tinyint(1) default 1 live=tinyint(1)
  - `sms_enabled` dump=tinyint(1) default 0 live=tinyint(1)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp

### Table `notification_templates`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `type` dump=varchar(50) not null live=varchar(50)
  - `title_template` dump=text not null live=text
  - `message_template` dump=text not null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp

### Table `opportunities`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `lead_id` dump=int(11) default null live=int(11)
  - `stage` dump=varchar(50) default null live=varchar(50)
  - `value` dump=decimal(12,2) default null live=decimal(12,2)
  - `expected_close` dump=date default null live=date
  - `status` dump=enum('open','won','lost') default 'open' live=enum('open','won','lost')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `partner_certification`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `partner_name` dump=varchar(255) default null live=varchar(255)
  - `app_name` dump=varchar(255) default null live=varchar(255)
  - `cert_status` dump=varchar(50) default 'pending' live=varchar(50)
  - `revenue_share` dump=int(11) default 0 live=int(11)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `partner_rewards`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `partner_email` dump=varchar(255) default null live=varchar(255)
  - `points` dump=int(11) default 0 live=int(11)
  - `description` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `password_resets`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `email` dump=varchar(255) not null live=varchar(255)
  - `token` dump=varchar(255) not null live=varchar(255)
  - `created_at` dump=datetime not null live=datetime
  - `expires_at` dump=datetime not null live=datetime
  - `used` dump=tinyint(1) not null default 0 live=tinyint(1)

### Table `payments`
- Missing columns (from dump): `method`
- Extra columns (only live): `customer_id`, `payment_type`, `payment_method`, `transaction_id`, `notes`, `created_by`, `updated_at`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `booking_id` dump=int(11) default null live=int(11)
  - `amount` dump=decimal(12,2) default null live=decimal(15,2)
  - `payment_date` dump=date default null live=date
  - `status` dump=enum('pending','completed','failed') default 'pending' live=varchar(50)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `payment_gateway_config`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `provider` dump=varchar(50) not null live=varchar(50)
  - `api_key` dump=varchar(255) default null live=varchar(255)
  - `api_secret` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `payment_logs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `payment_method` dump=varchar(50) default null live=varchar(50)
  - `amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `status` dump=varchar(50) default null live=varchar(50)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `payment_orders`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `razorpay_order_id` dump=varchar(255) not null live=varchar(255)
  - `amount` dump=decimal(10,2) not null live=decimal(10,2)
  - `currency` dump=varchar(3) default 'inr' live=varchar(3)
  - `receipt` dump=varchar(255) default null live=varchar(255)
  - `status` dump=enum('created','paid','failed','cancelled') default 'created' live=enum('created','paid','failed','cancelled')
  - `razorpay_payment_id` dump=varchar(255) default null live=varchar(255)
  - `payment_method` dump=varchar(50) default null live=varchar(50)
  - `payment_status` dump=varchar(50) default null live=varchar(50)
  - `refund_id` dump=varchar(255) default null live=varchar(255)
  - `refund_amount` dump=decimal(10,2) default null live=decimal(10,2)
  - `refund_status` dump=varchar(50) default null live=varchar(50)
  - `notes` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp
  - `paid_at` dump=timestamp null default null live=timestamp
  - `refunded_at` dump=timestamp null default null live=timestamp

### Table `payment_summary`
- Missing columns (from dump): `id`, `action`, `description`
- Extra columns (only live): `booking_id`, `booking_number`, `customer_id`, `customer_name`, `payment_amount`, `payment_date`, `payment_method`, `payment_status`, `booking_amount`, `total_paid_amount`, `pending_amount`

### Table `plots`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `colonies_id` dump=int(11) not null live=int(11)
  - `plot_number` dump=varchar(50) not null live=varchar(50)
  - `size` dump=decimal(10,2) not null comment 'in square feet' live=decimal(10,2)
  - `price` dump=decimal(15,2) not null live=decimal(15,2)
  - `status` dump=enum('available','booked','sold','blocked') default 'available' live=enum('available','booked','sold','blocked')
  - `facing` dump=varchar(50) default null live=varchar(50)
  - `corner_plot` dump=tinyint(1) default 0 live=tinyint(1)
  - `booking_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `plot_development`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `land_purchase_id` dump=int(11) not null live=int(11)
  - `plot_number` dump=varchar(50) not null live=varchar(50)
  - `plot_size` dump=decimal(10,2) not null comment 'in sqft' live=decimal(10,2)
  - `plot_type` dump=enum('residential','commercial','agricultural') default 'residential' live=enum('residential','commercial','agricultural')
  - `development_cost` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `selling_price` dump=decimal(15,2) default null live=decimal(15,2)
  - `status` dump=enum('planned','under_development','ready_to_sell','sold','booked') default 'planned' live=enum('planned','under_development','ready_to_sell','sold','booked')
  - `customer_id` dump=int(11) default null live=int(11)
  - `sold_date` dump=date default null live=date
  - `sold_price` dump=decimal(15,2) default null live=decimal(15,2)
  - `profit_loss` dump=decimal(15,2) default null live=decimal(15,2)
  - `amenities` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`amenities`)) live=longtext
  - `plot_facing` dump=enum('north','south','east','west','northeast','northwest','southeast','southwest') default null live=enum('north','south','east','west','northeast','northwest','southeast','southwest')
  - `road_width` dump=decimal(5,2) default null live=decimal(5,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `plot_rate_calculations`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `land_cost` dump=decimal(15,2) not null live=decimal(15,2)
  - `development_cost` dump=decimal(15,2) not null live=decimal(15,2)
  - `total_commission` dump=decimal(15,2) not null live=decimal(15,2)
  - `profit_margin` dump=decimal(5,2) not null live=decimal(5,2)
  - `final_rate_per_sqft` dump=decimal(10,2) not null live=decimal(10,2)
  - `calculated_by` dump=int(11) not null live=int(11)
  - `calculation_date` dump=timestamp not null default current_timestamp() live=timestamp

### Table `projects`
- Missing columns (from dump): `type`, `tagline`, `meta_description`, `builder_id`, `project_name`, `start_date`, `end_date`, `budget`, `brochure_path`, `youtube_url`, `brochure_drive_id`
- Extra columns (only live): `city`, `state`, `project_type`, `total_units`, `available_units`, `starting_price`, `completion_date`, `launch_date`, `developer_name`, `contact_person`, `contact_phone`, `contact_email`, `address`, `amenities`, `images`, `created_by`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(100) default null live=varchar(200)
  - `location` dump=varchar(255) default null live=varchar(200)
  - `description` dump=text default null live=text
  - `status` dump=enum('active','inactive') default 'active' live=varchar(50)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `project_amenities`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `project_id` dump=int(11) not null live=int(11)
  - `icon_path` dump=varchar(255) not null live=varchar(255)
  - `label` dump=varchar(100) not null live=varchar(100)

### Table `project_categories`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(100) not null live=varchar(100)
  - `slug` dump=varchar(100) not null live=varchar(100)
  - `created_by` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `project_category_relations`
- Type mismatches:
  - `project_id` dump=int(11) not null live=int(11)
  - `category_id` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `project_gallery`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `project_id` dump=int(11) not null live=int(11)
  - `image_path` dump=varchar(255) not null live=varchar(255)
  - `caption` dump=varchar(255) default null live=varchar(255)
  - `drive_file_id` dump=varchar(128) default null live=varchar(128)

### Table `project_progress`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `project_id` dump=int(11) not null live=int(11)
  - `progress_percentage` dump=int(11) not null live=int(11)
  - `milestone_achieved` dump=varchar(255) not null live=varchar(255)
  - `work_description` dump=text not null live=text
  - `amount_spent` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `next_milestone` dump=varchar(255) default null live=varchar(255)
  - `photos` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`photos`)) live=longtext
  - `updated_by` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `properties`
- Missing columns (from dump): `slug`, `property_type_id`, `area_sqft`, `bedrooms`, `bathrooms`, `address`, `city`, `state`, `country`, `postal_code`, `latitude`, `longitude`, `featured`, `hot_offer`
- Extra columns (only live): `location`, `type`
- Type mismatches:
  - `id` dump=int(11) not null live=bigint(20) unsigned
  - `title` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text default null live=text
  - `price` dump=decimal(15,2) not null live=decimal(10,2)
  - `status` dump=enum('available','sold','reserved','under_construction') default 'available' live=enum('available','sold','booked')
  - `created_by` dump=int(11) default null live=bigint(20) unsigned
  - `updated_by` dump=int(11) default null live=bigint(20) unsigned
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `property`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `title` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text default null live=text
  - `price` dump=decimal(15,2) default null live=decimal(15,2)
  - `location` dump=varchar(255) default null live=varchar(255)
  - `city` dump=varchar(100) default null live=varchar(100)
  - `state` dump=varchar(100) default null live=varchar(100)
  - `status` dump=enum('available','sold','pending') default 'available' live=enum('available','sold','pending')
  - `type` dump=enum('residential','commercial','land') default 'residential' live=enum('residential','commercial','land')
  - `bedrooms` dump=int(11) default null live=int(11)
  - `bathrooms` dump=int(11) default null live=int(11)
  - `area` dump=decimal(10,2) default null live=decimal(10,2)
  - `features` dump=text default null live=text
  - `main_image` dump=varchar(255) default null live=varchar(255)
  - `gallery_images` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp
  - `created_by` dump=int(11) default null live=int(11)
  - `is_featured` dump=tinyint(1) default 0 live=tinyint(1)

### Table `property_amenities`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `amenity_name` dump=varchar(100) not null live=varchar(100)
  - `amenity_type` dump=varchar(50) default 'basic' live=varchar(50)
  - `amenity_icon` dump=varchar(50) default null live=varchar(50)
  - `is_available` dump=tinyint(1) default 1 live=tinyint(1)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `property_bookings`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `amount` dump=decimal(10,2) not null live=decimal(10,2)
  - `payment_order_id` dump=int(11) default null live=int(11)
  - `status` dump=enum('pending','confirmed','cancelled','refunded') default 'pending' live=enum('pending','confirmed','cancelled','refunded')
  - `booking_date` dump=timestamp not null default current_timestamp() live=timestamp
  - `confirmation_date` dump=timestamp null default null live=timestamp
  - `notes` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `property_development_costs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `cost_type` dump=enum('land_cost','construction','infrastructure','legal','marketing','commission','other') not null live=enum('land_cost','construction','infrastructure','legal','marketing','commission','other')
  - `description` dump=varchar(255) default null live=varchar(255)
  - `amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `percentage_of_total` dump=decimal(5,2) default null live=decimal(5,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `property_favorites`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `property_features`
- Missing columns (from dump): `name`, `icon`, `status`
- Extra columns (only live): `property_id`, `feature_name`, `feature_value`, `feature_type`, `is_active`, `updated_at`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `property_feature_mappings`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `feature_id` dump=int(11) not null live=int(11)
  - `value` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `property_images`
- Extra columns (only live): `image_type`, `caption`, `alt_text`, `is_active`, `updated_at`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `image_path` dump=varchar(255) not null live=varchar(500)
  - `is_primary` dump=tinyint(1) default 0 live=tinyint(1)
  - `sort_order` dump=int(11) default 0 live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `property_types`
- Missing columns (from dump): `name`, `icon`
- Extra columns (only live): `type`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `description` dump=text default null live=text
  - `status` dump=enum('active','inactive') default 'active' live=varchar(50)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `property_visits`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `customer_id` dump=int(11) not null live=int(11)
  - `property_id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) default null live=int(11)
  - `visit_date` dump=datetime not null live=datetime
  - `visit_type` dump=enum('site_visit','virtual_tour','office_meeting','follow_up') default 'site_visit' live=enum('site_visit','virtual_tour','office_meeting','follow_up')
  - `status` dump=enum('scheduled','confirmed','completed','cancelled','rescheduled','no_show') default 'scheduled' live=enum('scheduled','confirmed','completed','cancelled','rescheduled','no_show')
  - `notes` dump=text default null live=text
  - `feedback_rating` dump=int(1) default null live=int(1)
  - `feedback_comments` dump=text default null live=text
  - `interest_level` dump=enum('low','medium','high','very_high') default 'medium' live=enum('low','medium','high','very_high')
  - `follow_up_required` dump=tinyint(1) default 0 live=tinyint(1)
  - `follow_up_date` dump=datetime default null live=datetime
  - `created_by` dump=int(11) default null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `purchase_invoices`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `invoice_number` dump=varchar(50) not null live=varchar(50)
  - `supplier_invoice_number` dump=varchar(50) default null live=varchar(50)
  - `supplier_id` dump=int(11) not null live=int(11)
  - `invoice_date` dump=date not null live=date
  - `due_date` dump=date default null live=date
  - `subtotal` dump=decimal(15,2) not null live=decimal(15,2)
  - `tax_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `discount_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `paid_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `balance_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `notes` dump=text default null live=text
  - `status` dump=enum('draft','received','paid','partial','overdue') default 'draft' live=enum('draft','received','paid','partial','overdue')
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `purchase_invoice_items`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `invoice_id` dump=int(11) not null live=int(11)
  - `item_name` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text default null live=text
  - `quantity` dump=decimal(10,2) not null live=decimal(10,2)
  - `unit_price` dump=decimal(15,2) not null live=decimal(15,2)
  - `total_price` dump=decimal(15,2) not null live=decimal(15,2)
  - `tax_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `tax_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `real_estate_properties`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `property_code` dump=varchar(50) not null live=varchar(50)
  - `property_name` dump=varchar(255) not null live=varchar(255)
  - `property_type` dump=enum('company','resell') not null live=enum('company','resell')
  - `property_category` dump=enum('plot','flat','house','commercial','land') not null live=enum('plot','flat','house','commercial','land')
  - `location` dump=varchar(255) not null live=varchar(255)
  - `area_sqft` dump=decimal(10,2) not null live=decimal(10,2)
  - `rate_per_sqft` dump=decimal(10,2) not null live=decimal(10,2)
  - `total_value` dump=decimal(15,2) not null live=decimal(15,2)
  - `development_cost` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `commission_percentage` dump=decimal(5,2) not null live=decimal(5,2)
  - `status` dump=enum('available','booked','sold','cancelled') default 'available' live=enum('available','booked','sold','cancelled')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `recurring_transactions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `transaction_name` dump=varchar(255) not null live=varchar(255)
  - `transaction_type` dump=enum('income','expense','transfer') not null live=enum('income','expense','transfer')
  - `amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `frequency` dump=enum('daily','weekly','monthly','quarterly','yearly') not null live=enum('daily','weekly','monthly','quarterly','yearly')
  - `start_date` dump=date not null live=date
  - `end_date` dump=date default null live=date
  - `next_due_date` dump=date not null live=date
  - `account_id` dump=int(11) not null live=int(11)
  - `category` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text default null live=text
  - `auto_create` dump=tinyint(1) default 0 live=tinyint(1)
  - `status` dump=enum('active','paused','completed','cancelled') default 'active' live=enum('active','paused','completed','cancelled')
  - `created_transactions` dump=int(11) default 0 live=int(11)
  - `last_created_date` dump=date default null live=date
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `rental_properties`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `owner_id` dump=int(11) default null live=int(11)
  - `address` dump=varchar(255) default null live=varchar(255)
  - `rent_amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `status` dump=enum('available','rented','inactive') default 'available' live=enum('available','rented','inactive')

### Table `rent_payments`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `rental_property_id` dump=int(11) default null live=int(11)
  - `tenant_id` dump=int(11) default null live=int(11)
  - `amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `due_date` dump=date default null live=date
  - `paid_date` dump=date default null live=date
  - `status` dump=enum('pending','paid','overdue') default 'pending' live=enum('pending','paid','overdue')

### Table `reports`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `title` dump=varchar(255) not null live=varchar(255)
  - `type` dump=varchar(50) not null live=varchar(50)
  - `content` dump=text not null live=text
  - `file_path` dump=varchar(255) default null live=varchar(255)
  - `generated_for_month` dump=int(11) not null live=int(11)
  - `generated_for_year` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp null default null on update current_timestamp() live=timestamp

### Table `resale_commissions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) default null live=int(11)
  - `resale_property_id` dump=int(11) default null live=int(11)
  - `amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `paid_on` dump=date default null live=date

### Table `resale_properties`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `owner_id` dump=int(11) default null live=int(11)
  - `details` dump=text default null live=text
  - `price` dump=decimal(15,2) default null live=decimal(15,2)
  - `status` dump=enum('available','sold','inactive') default 'available' live=enum('available','sold','inactive')

### Table `resell_commission_structure`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `plan_id` dump=int(11) not null live=int(11)
  - `property_category` dump=enum('plot','flat','house','commercial','land') not null live=enum('plot','flat','house','commercial','land')
  - `min_value` dump=decimal(15,2) not null live=decimal(15,2)
  - `max_value` dump=decimal(15,2) not null live=decimal(15,2)
  - `commission_percentage` dump=decimal(5,2) not null live=decimal(5,2)
  - `fixed_commission` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `commission_type` dump=enum('percentage','fixed','both') default 'percentage' live=enum('percentage','fixed','both')

### Table `reward_history`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) default null live=int(11)
  - `reward_type` dump=varchar(50) default null live=varchar(50)
  - `reward_value` dump=decimal(12,2) default null live=decimal(12,2)
  - `reward_date` dump=date default null live=date
  - `description` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `roles`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(50) not null live=varchar(50)

### Table `role_change_approvals`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `role_id` dump=int(11) not null live=int(11)
  - `action` dump=enum('assign','remove') not null live=enum('assign','remove')
  - `requested_by` dump=int(11) not null live=int(11)
  - `status` dump=enum('pending','approved','rejected') default 'pending' live=enum('pending','approved','rejected')
  - `requested_at` dump=datetime default current_timestamp() live=datetime
  - `decided_by` dump=int(11) default null live=int(11)
  - `decided_at` dump=datetime default null live=datetime

### Table `role_permissions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `role_id` dump=int(11) default null live=int(11)
  - `permission_id` dump=int(11) default null live=int(11)

### Table `saas_instances`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `client_name` dump=varchar(255) not null live=varchar(255)
  - `domain` dump=varchar(255) not null live=varchar(255)
  - `status` dump=varchar(50) default 'active' live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `salaries`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `employee_id` dump=int(11) default null live=int(11)
  - `month` dump=int(11) default null live=int(11)
  - `year` dump=int(11) default null live=int(11)
  - `amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `status` dump=enum('pending','paid','failed') default 'pending' live=enum('pending','paid','failed')
  - `paid_on` dump=date default null live=date

### Table `salary_plan`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) default null live=int(11)
  - `level` dump=int(11) default null live=int(11)
  - `salary_amount` dump=decimal(12,2) default null live=decimal(12,2)
  - `payout_date` dump=date default null live=date
  - `status` dump=enum('pending','paid') default 'pending' live=enum('pending','paid')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `sales_invoices`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `invoice_number` dump=varchar(50) not null live=varchar(50)
  - `customer_id` dump=int(11) not null live=int(11)
  - `invoice_date` dump=date not null live=date
  - `due_date` dump=date default null live=date
  - `subtotal` dump=decimal(15,2) not null live=decimal(15,2)
  - `tax_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `discount_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `paid_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `balance_amount` dump=decimal(15,2) not null live=decimal(15,2)
  - `payment_terms` dump=varchar(255) default null live=varchar(255)
  - `notes` dump=text default null live=text
  - `status` dump=enum('draft','sent','paid','partial','overdue','cancelled') default 'draft' live=enum('draft','sent','paid','partial','overdue','cancelled')
  - `created_by` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `sales_invoice_items`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `invoice_id` dump=int(11) not null live=int(11)
  - `item_name` dump=varchar(255) not null live=varchar(255)
  - `description` dump=text default null live=text
  - `quantity` dump=decimal(10,2) not null live=decimal(10,2)
  - `unit_price` dump=decimal(15,2) not null live=decimal(15,2)
  - `total_price` dump=decimal(15,2) not null live=decimal(15,2)
  - `tax_rate` dump=decimal(5,2) default 0.00 live=decimal(5,2)
  - `tax_amount` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `saved_searches`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(100) not null live=varchar(100)
  - `search_params` dump=text not null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `seo_metadata`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `page_name` dump=varchar(100) not null live=varchar(100)
  - `meta_title` dump=varchar(255) default null live=varchar(255)
  - `meta_description` dump=text default null live=text
  - `meta_keywords` dump=text default null live=text
  - `og_title` dump=varchar(255) default null live=varchar(255)
  - `og_description` dump=text default null live=text
  - `og_image` dump=varchar(255) default null live=varchar(255)
  - `canonical_url` dump=varchar(255) default null live=varchar(255)
  - `robots` dump=varchar(50) default 'index, follow' live=varchar(50)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `settings`
- Type mismatches:
  - `key` dump=varchar(100) not null live=varchar(100)
  - `value` dump=text default null live=text

### Table `sites`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `site_name` dump=varchar(255) not null live=varchar(255)
  - `location` dump=text not null live=text
  - `city` dump=varchar(100) default null live=varchar(100)
  - `state` dump=varchar(100) default null live=varchar(100)
  - `district` dump=varchar(100) default null live=varchar(100)
  - `pincode` dump=varchar(10) default null live=varchar(10)
  - `total_area` dump=decimal(10,2) not null comment 'in acres' live=decimal(10,2)
  - `developed_area` dump=decimal(10,2) default 0.00 live=decimal(10,2)
  - `site_type` dump=enum('residential','commercial','mixed','industrial') default 'residential' live=enum('residential','commercial','mixed','industrial')
  - `status` dump=enum('planning','under_development','active','completed','inactive') default 'planning' live=enum('planning','under_development','active','completed','inactive')
  - `manager_id` dump=int(11) default null live=int(11)
  - `latitude` dump=decimal(10,8) default null live=decimal(10,8)
  - `longitude` dump=decimal(11,8) default null live=decimal(11,8)
  - `amenities` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`amenities`)) live=longtext
  - `description` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `site_settings`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `setting_name` dump=varchar(100) not null live=varchar(100)
  - `value` dump=text not null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `smart_contracts`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `agreement_name` dump=varchar(255) not null live=varchar(255)
  - `parties` dump=varchar(255) default null live=varchar(255)
  - `terms` dump=text default null live=text
  - `status` dump=varchar(50) default 'pending' live=varchar(50)
  - `blockchain_txn` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `social_media_links`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `platform_name` dump=varchar(50) not null live=varchar(50)
  - `platform_url` dump=varchar(255) not null live=varchar(255)
  - `is_active` dump=tinyint(1) default 1 live=tinyint(1)
  - `display_order` dump=int(11) default 0 live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `suppliers`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `supplier_name` dump=varchar(255) not null live=varchar(255)
  - `contact_person` dump=varchar(255) default null live=varchar(255)
  - `mobile` dump=varchar(15) not null live=varchar(15)
  - `email` dump=varchar(255) default null live=varchar(255)
  - `address` dump=text default null live=text
  - `gst_number` dump=varchar(20) default null live=varchar(20)
  - `pan_number` dump=varchar(20) default null live=varchar(20)
  - `bank_account` dump=varchar(50) default null live=varchar(50)
  - `ifsc_code` dump=varchar(15) default null live=varchar(15)
  - `credit_limit` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `credit_days` dump=int(11) default 0 live=int(11)
  - `opening_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `current_balance` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_purchases` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `total_payments` dump=decimal(15,2) default 0.00 live=decimal(15,2)
  - `last_payment_date` dump=date default null live=date
  - `supplier_type` dump=enum('material','service','both') default 'material' live=enum('material','service','both')
  - `status` dump=enum('active','inactive','blocked') default 'active' live=enum('active','inactive','blocked')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `support_tickets`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `subject` dump=varchar(255) default null live=varchar(255)
  - `message` dump=text default null live=text
  - `status` dump=varchar(20) default 'open' live=varchar(20)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `system_logs`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `action` dump=varchar(100) not null live=varchar(100)
  - `table_name` dump=varchar(100) default null live=varchar(100)
  - `record_id` dump=int(11) default null live=int(11)
  - `old_values` dump=text default null live=text
  - `new_values` dump=text default null live=text
  - `ip_address` dump=varchar(45) default null live=varchar(45)
  - `user_agent` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `table_name`
- Type mismatches:
  - `id` dump=int(11) not null comment 'primary key' live=int(11)
  - `create_time` dump=datetime default null comment 'create time' live=datetime
  - `name` dump=varchar(255) default null live=varchar(255)

### Table `tasks`
- Missing columns (from dump): `assigned_by`
- Extra columns (only live): `created_by`, `priority`, `completed_at`, `notes`, `related_type`, `related_id`, `updated_at`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `title` dump=varchar(100) default null live=varchar(200)
  - `description` dump=text default null live=text
  - `assigned_to` dump=int(11) default null live=int(11)
  - `due_date` dump=date default null live=date
  - `status` dump=enum('pending','in_progress','completed','cancelled') default 'pending' live=varchar(50)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `team`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(100) not null live=varchar(100)
  - `designation` dump=varchar(100) default null live=varchar(100)
  - `bio` dump=text default null live=text
  - `photo` dump=varchar(255) default null live=varchar(255)
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `team_hierarchy`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `associate_id` dump=int(11) not null live=int(11)
  - `upline_id` dump=int(11) not null live=int(11)
  - `level` dump=int(11) not null live=int(11)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `team_members`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(255) not null live=varchar(255)
  - `position` dump=varchar(255) not null live=varchar(255)
  - `bio` dump=text default null live=text
  - `photo` dump=varchar(255) default null live=varchar(255)
  - `email` dump=varchar(255) default null live=varchar(255)
  - `phone` dump=varchar(50) default null live=varchar(50)
  - `linkedin` dump=varchar(255) default null live=varchar(255)
  - `expertise` dump=varchar(255) default null live=varchar(255)
  - `experience` dump=varchar(100) default null live=varchar(100)
  - `display_order` dump=int(11) default 0 live=int(11)
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `testimonials`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `client_name` dump=varchar(100) not null live=varchar(100)
  - `email` dump=varchar(100) default null live=varchar(100)
  - `rating` dump=tinyint(1) default 5 live=tinyint(1)
  - `testimonial` dump=text not null live=text
  - `client_photo` dump=varchar(255) default null live=varchar(255)
  - `status` dump=enum('pending','approved','rejected','active','inactive') not null default 'pending' live=enum('pending','approved','rejected','active','inactive')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `third_party_integrations`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `type` dump=varchar(50) not null live=varchar(50)
  - `api_token` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `transactions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `type` dump=varchar(50) default null live=varchar(50)
  - `amount` dump=decimal(15,2) default null live=decimal(15,2)
  - `date` dump=date default null live=date
  - `description` dump=text default null live=text
  - `ref_id` dump=varchar(100) default null live=varchar(100)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `upload_audit_log`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `event_type` dump=varchar(64) not null live=varchar(64)
  - `entity_id` dump=int(11) not null live=int(11)
  - `entity_table` dump=varchar(64) not null live=varchar(64)
  - `file_name` dump=varchar(255) not null live=varchar(255)
  - `drive_file_id` dump=varchar(128) default null live=varchar(128)
  - `uploader` dump=varchar(128) not null live=varchar(128)
  - `slack_status` dump=varchar(32) default null live=varchar(32)
  - `telegram_status` dump=varchar(32) default null live=varchar(32)
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp

### Table `users`
- Type mismatches:
  - `id` dump=int(11) not null live=bigint(20) unsigned
  - `name` dump=varchar(100) not null live=varchar(255)
  - `email` dump=varchar(100) default null live=varchar(255)
  - `profile_picture` dump=varchar(255) default null live=varchar(255)
  - `phone` dump=varchar(20) default null live=varchar(20)
  - `type` dump=varchar(50) default null live=enum('admin','agent','customer','employee')
  - `password` dump=varchar(255) not null live=varchar(255)
  - `status` dump=enum('active','inactive') default 'active' live=enum('active','inactive','pending')
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp
  - `api_access` dump=tinyint(1) default 0 live=tinyint(1)
  - `api_rate_limit` dump=int(11) default 1000 live=int(11)

### Table `user_preferences`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `preference_key` dump=varchar(100) not null live=varchar(100)
  - `preference_value` dump=text default null live=text
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `user_roles`
- Type mismatches:
  - `user_id` dump=int(11) not null live=int(11)
  - `role_id` dump=int(11) not null live=int(11)

### Table `user_sessions`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) default null live=int(11)
  - `login_time` dump=datetime default null live=datetime
  - `logout_time` dump=datetime default null live=datetime
  - `ip_address` dump=varchar(45) default null live=varchar(45)
  - `status` dump=enum('active','ended') default 'active' live=enum('active','ended')

### Table `user_social_accounts`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `user_id` dump=int(11) not null live=int(11)
  - `provider` dump=varchar(50) not null live=varchar(50)
  - `provider_id` dump=varchar(255) not null live=varchar(255)
  - `token` dump=text default null live=text
  - `refresh_token` dump=text default null live=text
  - `expires_at` dump=timestamp null default null live=timestamp
  - `created_at` dump=timestamp not null default current_timestamp() live=timestamp
  - `updated_at` dump=timestamp not null default current_timestamp() on update current_timestamp() live=timestamp

### Table `voice_assistant_config`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `provider` dump=varchar(50) not null live=varchar(50)
  - `api_key` dump=varchar(255) default null live=varchar(255)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `whatsapp_automation_config`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `provider` dump=varchar(50) not null live=varchar(50)
  - `api_key` dump=varchar(255) default null live=varchar(255)
  - `sender_number` dump=varchar(50) default null live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `workflows`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(255) not null live=varchar(255)
  - `definition` dump=longtext character set utf8mb4 collate utf8mb4_bin default null check (json_valid(`definition`)) live=longtext
  - `created_by` dump=int(11) default null live=int(11)
  - `created_at` dump=datetime default current_timestamp() live=datetime

### Table `workflow_automations`
- Type mismatches:
  - `id` dump=int(11) not null live=int(11)
  - `name` dump=varchar(255) not null live=varchar(255)
  - `provider` dump=varchar(50) default null live=varchar(50)
  - `webhook_url` dump=varchar(255) default null live=varchar(255)
  - `status` dump=varchar(50) default 'active' live=varchar(50)
  - `created_at` dump=datetime default current_timestamp() live=datetime
