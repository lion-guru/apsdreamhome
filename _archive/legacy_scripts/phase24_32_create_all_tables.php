<?php
/**
 * Phase 24-32: Create all missing feature tables
 * All 12 phases batch - then services & UI
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = [
    // PHASE 24 - USER FACING
    'incomplete_registrations' => "
        CREATE TABLE IF NOT EXISTS incomplete_registrations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL,
            email VARCHAR(150) NULL,
            phone VARCHAR(20) NULL,
            form_data JSON NULL,
            current_step VARCHAR(50) NULL,
            progress_percent INT UNSIGNED DEFAULT 0,
            last_activity_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            source VARCHAR(50) NULL,
            utm_data JSON NULL,
            recovered_at DATETIME NULL,
            recovered_user_id BIGINT UNSIGNED NULL,
            INDEX idx_session (session_id),
            INDEX idx_email (email),
            INDEX idx_phone (phone),
            INDEX idx_recovered (recovered_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'progressive_registrations' => "
        CREATE TABLE IF NOT EXISTS progressive_registrations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            current_step VARCHAR(50) NOT NULL,
            total_steps INT UNSIGNED DEFAULT 5,
            step_data JSON NULL,
            started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_step_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL,
            abandoned_at DATETIME NULL,
            INDEX idx_user (user_id),
            INDEX idx_step (current_step)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 25 - HRM
    'employee_advances' => "
        CREATE TABLE IF NOT EXISTS employee_advances (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            reason TEXT NULL,
            request_date DATE NOT NULL,
            approval_status ENUM('pending','approved','rejected','paid','recovered') DEFAULT 'pending',
            approved_by BIGINT UNSIGNED NULL,
            approval_date DATETIME NULL,
            repayment_months INT UNSIGNED DEFAULT 1,
            monthly_deduction DECIMAL(12,2) DEFAULT 0.00,
            recovered_amount DECIMAL(12,2) DEFAULT 0.00,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id),
            INDEX idx_status (approval_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'employee_bonuses' => "
        CREATE TABLE IF NOT EXISTS employee_bonuses (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            bonus_type ENUM('performance','festival','referral','milestone','diwali','new_year','project_completion') NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            reason TEXT NULL,
            bonus_date DATE NOT NULL,
            payout_month VARCHAR(7) NULL,
            status ENUM('pending','approved','paid','cancelled') DEFAULT 'pending',
            approved_by BIGINT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id),
            INDEX idx_type (bonus_type),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'payroll_entries' => "
        CREATE TABLE IF NOT EXISTS payroll_entries (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            payroll_run_id BIGINT UNSIGNED NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            basic_salary DECIMAL(12,2) DEFAULT 0.00,
            hra DECIMAL(12,2) DEFAULT 0.00,
            transport_allowance DECIMAL(12,2) DEFAULT 0.00,
            medical_allowance DECIMAL(12,2) DEFAULT 0.00,
            special_allowance DECIMAL(12,2) DEFAULT 0.00,
            bonus DECIMAL(12,2) DEFAULT 0.00,
            overtime DECIMAL(12,2) DEFAULT 0.00,
            gross_salary DECIMAL(12,2) DEFAULT 0.00,
            pf_deduction DECIMAL(12,2) DEFAULT 0.00,
            esi_deduction DECIMAL(12,2) DEFAULT 0.00,
            tds_deduction DECIMAL(12,2) DEFAULT 0.00,
            advance_deduction DECIMAL(12,2) DEFAULT 0.00,
            loan_deduction DECIMAL(12,2) DEFAULT 0.00,
            other_deductions DECIMAL(12,2) DEFAULT 0.00,
            total_deductions DECIMAL(12,2) DEFAULT 0.00,
            net_salary DECIMAL(12,2) DEFAULT 0.00,
            working_days INT UNSIGNED DEFAULT 0,
            present_days INT UNSIGNED DEFAULT 0,
            leave_days INT UNSIGNED DEFAULT 0,
            status ENUM('draft','approved','paid') DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_run (payroll_run_id),
            INDEX idx_employee (employee_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'salary_contracts' => "
        CREATE TABLE IF NOT EXISTS salary_contracts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            contract_number VARCHAR(50) NOT NULL UNIQUE,
            start_date DATE NOT NULL,
            end_date DATE NULL,
            ctc DECIMAL(12,2) NOT NULL,
            basic_salary DECIMAL(12,2) NOT NULL,
            terms TEXT NULL,
            status ENUM('draft','active','expired','terminated') DEFAULT 'draft',
            signed_date DATE NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'salary_history' => "
        CREATE TABLE IF NOT EXISTS salary_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            change_type ENUM('increment','promotion','role_change','revision','adjustment') NOT NULL,
            old_salary DECIMAL(12,2) NULL,
            new_salary DECIMAL(12,2) NOT NULL,
            effective_date DATE NOT NULL,
            reason TEXT NULL,
            approved_by BIGINT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id),
            INDEX idx_type (change_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'attendance_settings' => "
        CREATE TABLE IF NOT EXISTS attendance_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            description TEXT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'department_budgets' => "
        CREATE TABLE IF NOT EXISTS department_budgets (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            department_id BIGINT UNSIGNED NOT NULL,
            fiscal_year VARCHAR(10) NOT NULL,
            allocated_amount DECIMAL(15,2) NOT NULL,
            spent_amount DECIMAL(15,2) DEFAULT 0.00,
            remaining_amount DECIMAL(15,2) GENERATED ALWAYS AS (allocated_amount - spent_amount) STORED,
            category VARCHAR(50) NULL,
            approved_by BIGINT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_dept (department_id),
            INDEX idx_fiscal (fiscal_year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 26 - PROPERTY
    'property_valuations' => "
        CREATE TABLE IF NOT EXISTS property_valuations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            property_id BIGINT UNSIGNED NOT NULL,
            property_type VARCHAR(50) NOT NULL,
            valuation_date DATE NOT NULL,
            valuation_amount DECIMAL(15,2) NOT NULL,
            valued_by VARCHAR(100) NULL,
            methodology VARCHAR(50) NULL,
            notes TEXT NULL,
            source ENUM('manual','ai_prediction','market_rate','government') DEFAULT 'manual',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_property (property_id, property_type),
            INDEX idx_date (valuation_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'property_ai_tags' => "
        CREATE TABLE IF NOT EXISTS property_ai_tags (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            property_id BIGINT UNSIGNED NOT NULL,
            property_type VARCHAR(50) NOT NULL,
            tag VARCHAR(50) NOT NULL,
            confidence DECIMAL(5,2) DEFAULT 0.00,
            auto_generated TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_prop_tag (property_id, property_type, tag),
            INDEX idx_tag (tag)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'property_analytics' => "
        CREATE TABLE IF NOT EXISTS property_analytics (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            property_id BIGINT UNSIGNED NOT NULL,
            property_type VARCHAR(50) NOT NULL,
            metric_date DATE NOT NULL,
            view_count INT UNSIGNED DEFAULT 0,
            inquiry_count INT UNSIGNED DEFAULT 0,
            favorite_count INT UNSIGNED DEFAULT 0,
            share_count INT UNSIGNED DEFAULT 0,
            conversion_rate DECIMAL(5,2) DEFAULT 0.00,
            avg_view_duration_seconds INT UNSIGNED DEFAULT 0,
            UNIQUE KEY uniq_prop_date (property_id, property_type, metric_date),
            INDEX idx_date (metric_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'property_maintenance' => "
        CREATE TABLE IF NOT EXISTS property_maintenance (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            property_id BIGINT UNSIGNED NOT NULL,
            property_type VARCHAR(50) NOT NULL,
            maintenance_type ENUM('routine','repair','emergency','renovation','inspection') NOT NULL,
            scheduled_date DATE NOT NULL,
            completed_date DATE NULL,
            description TEXT NULL,
            cost DECIMAL(12,2) DEFAULT 0.00,
            vendor_id BIGINT UNSIGNED NULL,
            status ENUM('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
            INDEX idx_property (property_id, property_type),
            INDEX idx_date (scheduled_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'property_market_data' => "
        CREATE TABLE IF NOT EXISTS property_market_data (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            location_id BIGINT UNSIGNED NULL,
            district_id BIGINT UNSIGNED NULL,
            property_type VARCHAR(50) NOT NULL,
            data_date DATE NOT NULL,
            avg_price_per_sqft DECIMAL(10,2) NULL,
            median_price DECIMAL(15,2) NULL,
            total_listings INT UNSIGNED DEFAULT 0,
            avg_days_on_market INT UNSIGNED DEFAULT 0,
            price_trend_pct DECIMAL(5,2) DEFAULT 0.00,
            demand_index DECIMAL(5,2) DEFAULT 0.00,
            supply_index DECIMAL(5,2) DEFAULT 0.00,
            INDEX idx_location (location_id),
            INDEX idx_date (data_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'resell_properties' => "
        CREATE TABLE IF NOT EXISTS resell_properties (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            property_type VARCHAR(50) NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT NULL,
            location VARCHAR(200) NULL,
            district_id BIGINT UNSIGNED NULL,
            area_sqft INT UNSIGNED NULL,
            bedrooms INT UNSIGNED DEFAULT 0,
            bathrooms INT UNSIGNED DEFAULT 0,
            asking_price DECIMAL(15,2) NOT NULL,
            original_price DECIMAL(15,2) NULL,
            age_years INT UNSIGNED DEFAULT 0,
            amenities JSON NULL,
            status ENUM('pending','active','sold','inactive') DEFAULT 'pending',
            approved_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_type (property_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'resell_property_images' => "
        CREATE TABLE IF NOT EXISTS resell_property_images (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            resell_property_id BIGINT UNSIGNED NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            sort_order INT UNSIGNED DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_property (resell_property_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'resell_commission_structure' => "
        CREATE TABLE IF NOT EXISTS resell_commission_structure (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            property_type VARCHAR(50) NOT NULL,
            min_price DECIMAL(15,2) DEFAULT 0.00,
            max_price DECIMAL(15,2) DEFAULT NULL,
            commission_pct DECIMAL(5,2) NOT NULL,
            flat_amount DECIMAL(12,2) DEFAULT 0.00,
            effective_from DATE NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_type (property_type),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 27 - MLM
    'agent_commission_rates' => "
        CREATE TABLE IF NOT EXISTS agent_commission_rates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            agent_id BIGINT UNSIGNED NOT NULL,
            property_type VARCHAR(50) NOT NULL,
            base_rate_pct DECIMAL(5,2) NOT NULL,
            override_pct DECIMAL(5,2) DEFAULT 0.00,
            bonus_rate_pct DECIMAL(5,2) DEFAULT 0.00,
            effective_from DATE NOT NULL,
            effective_to DATE NULL,
            INDEX idx_agent (agent_id),
            INDEX idx_type (property_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'commission_calculation_rules' => "
        CREATE TABLE IF NOT EXISTS commission_calculation_rules (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rule_name VARCHAR(100) NOT NULL,
            rule_type ENUM('base','override','bonus','tier_based','performance') NOT NULL,
            conditions JSON NULL,
            formula VARCHAR(500) NULL,
            priority INT UNSIGNED DEFAULT 100,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_type (rule_type),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'hybrid_commission_records' => "
        CREATE TABLE IF NOT EXISTS hybrid_commission_records (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            plan_id BIGINT UNSIGNED NOT NULL,
            beneficiary_user_id BIGINT UNSIGNED NOT NULL,
            transaction_id BIGINT UNSIGNED NULL,
            base_amount DECIMAL(12,2) DEFAULT 0.00,
            override_amount DECIMAL(12,2) DEFAULT 0.00,
            tier_bonus DECIMAL(12,2) DEFAULT 0.00,
            performance_bonus DECIMAL(12,2) DEFAULT 0.00,
            total_amount DECIMAL(12,2) NOT NULL,
            level INT UNSIGNED DEFAULT 1,
            calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_beneficiary (beneficiary_user_id),
            INDEX idx_plan (plan_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'hybrid_commission_plans' => "
        CREATE TABLE IF NOT EXISTS hybrid_commission_plans (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            plan_name VARCHAR(100) NOT NULL,
            plan_type ENUM('mlm_focused','sales_focused','balanced','custom') NOT NULL,
            level_rates JSON NULL,
            override_levels INT UNSIGNED DEFAULT 3,
            performance_tiers JSON NULL,
            effective_from DATE NOT NULL,
            effective_to DATE NULL,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'farmer_commissions' => "
        CREATE TABLE IF NOT EXISTS farmer_commissions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            farmer_id BIGINT UNSIGNED NOT NULL,
            referrer_user_id BIGINT UNSIGNED NULL,
            transaction_id BIGINT UNSIGNED NULL,
            commission_type ENUM('referral','land_sale','crop_advisory','equipment') NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            rate_pct DECIMAL(5,2) NULL,
            status ENUM('pending','approved','paid','cancelled') DEFAULT 'pending',
            earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_farmer (farmer_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'farmer_commission_structures' => "
        CREATE TABLE IF NOT EXISTS farmer_commission_structures (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            structure_name VARCHAR(100) NOT NULL,
            commission_type ENUM('referral','land_sale','crop_advisory','equipment') NOT NULL,
            base_rate_pct DECIMAL(5,2) NOT NULL,
            tier_rules JSON NULL,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_type (commission_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'mlm_rank_rates' => "
        CREATE TABLE IF NOT EXISTS mlm_rank_rates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rank_name VARCHAR(50) NOT NULL,
            rank_level INT UNSIGNED NOT NULL,
            min_qualification_volume DECIMAL(15,2) DEFAULT 0.00,
            min_downline_count INT UNSIGNED DEFAULT 0,
            commission_multiplier DECIMAL(5,2) DEFAULT 1.00,
            bonus_amount DECIMAL(12,2) DEFAULT 0.00,
            UNIQUE KEY uniq_rank (rank_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 28 - NOTIFICATIONS
    'notification_templates' => "
        CREATE TABLE IF NOT EXISTS notification_templates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            template_code VARCHAR(100) NOT NULL UNIQUE,
            template_name VARCHAR(200) NOT NULL,
            channel ENUM('email','sms','whatsapp','push','in_app') NOT NULL,
            language VARCHAR(10) DEFAULT 'en',
            subject VARCHAR(200) NULL,
            body TEXT NOT NULL,
            variables JSON NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_code (template_code),
            INDEX idx_channel (channel),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'email_tracking' => "
        CREATE TABLE IF NOT EXISTS email_tracking (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email_id VARCHAR(100) NOT NULL,
            recipient VARCHAR(150) NULL,
            event_type ENUM('sent','delivered','opened','clicked','bounced','unsubscribed','spam') NOT NULL,
            link_url VARCHAR(500) NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            event_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email_id),
            INDEX idx_event (event_type),
            INDEX idx_recipient (recipient)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'push_notifications' => "
        CREATE TABLE IF NOT EXISTS push_notifications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            title VARCHAR(200) NOT NULL,
            body TEXT NULL,
            data JSON NULL,
            target_type VARCHAR(50) NULL,
            target_id BIGINT UNSIGNED NULL,
            status ENUM('queued','sent','delivered','failed','clicked') DEFAULT 'queued',
            sent_at DATETIME NULL,
            delivered_at DATETIME NULL,
            clicked_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'push_subscriptions' => "
        CREATE TABLE IF NOT EXISTS push_subscriptions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            device_type ENUM('web','android','ios') NOT NULL,
            device_token VARCHAR(500) NOT NULL,
            endpoint VARCHAR(500) NULL,
            p256dh_key VARCHAR(200) NULL,
            auth_key VARCHAR(100) NULL,
            is_active TINYINT(1) DEFAULT 1,
            last_used_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_token (device_token),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'whatsapp_lead_shares' => "
        CREATE TABLE IF NOT EXISTS whatsapp_lead_shares (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            lead_id BIGINT UNSIGNED NOT NULL,
            shared_by_user_id BIGINT UNSIGNED NOT NULL,
            shared_to_phone VARCHAR(20) NOT NULL,
            share_method ENUM('direct','group','broadcast') DEFAULT 'direct',
            message TEXT NULL,
            status ENUM('pending','sent','delivered','read','replied') DEFAULT 'pending',
            sent_at DATETIME NULL,
            INDEX idx_lead (lead_id),
            INDEX idx_shared_by (shared_by_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'realtime_notifications' => "
        CREATE TABLE IF NOT EXISTS realtime_notifications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            channel_name VARCHAR(100) NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            event_type VARCHAR(50) NOT NULL,
            payload JSON NULL,
            delivered_at DATETIME NULL,
            read_at DATETIME NULL,
            expires_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_channel (channel_name),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'notification_settings' => "
        CREATE TABLE IF NOT EXISTS notification_settings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            channel ENUM('email','sms','whatsapp','push','in_app') NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            is_enabled TINYINT(1) DEFAULT 1,
            UNIQUE KEY uniq_user_channel_event (user_id, channel, event_type),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 29 - DOCUMENT/WORKFLOW
    'document_classification' => "
        CREATE TABLE IF NOT EXISTS document_classification (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            document_id BIGINT UNSIGNED NOT NULL,
            classified_category VARCHAR(50) NULL,
            classified_type VARCHAR(50) NULL,
            confidence DECIMAL(5,2) DEFAULT 0.00,
            manual_override TINYINT(1) DEFAULT 0,
            classified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_doc (document_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'ocr_documents' => "
        CREATE TABLE IF NOT EXISTS ocr_documents (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            document_id BIGINT UNSIGNED NOT NULL,
            ocr_engine VARCHAR(50) NULL,
            status ENUM('queued','processing','completed','failed') DEFAULT 'queued',
            raw_text LONGTEXT NULL,
            confidence_avg DECIMAL(5,2) DEFAULT 0.00,
            error_message TEXT NULL,
            processed_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_doc (document_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'ocr_extracted_fields' => "
        CREATE TABLE IF NOT EXISTS ocr_extracted_fields (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ocr_document_id BIGINT UNSIGNED NOT NULL,
            field_name VARCHAR(100) NOT NULL,
            field_value TEXT NULL,
            confidence DECIMAL(5,2) DEFAULT 0.00,
            verified TINYINT(1) DEFAULT 0,
            INDEX idx_ocr (ocr_document_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'ocr_templates' => "
        CREATE TABLE IF NOT EXISTS ocr_templates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            template_name VARCHAR(100) NOT NULL,
            document_type VARCHAR(50) NOT NULL,
            field_definitions JSON NULL,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_type (document_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'report_executions' => "
        CREATE TABLE IF NOT EXISTS report_executions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            report_id BIGINT UNSIGNED NULL,
            report_name VARCHAR(200) NOT NULL,
            run_by_user_id BIGINT UNSIGNED NULL,
            parameters JSON NULL,
            format ENUM('pdf','excel','csv','html','json') DEFAULT 'html',
            status ENUM('queued','running','completed','failed') DEFAULT 'queued',
            started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL,
            row_count INT UNSIGNED DEFAULT 0,
            file_path VARCHAR(500) NULL,
            error_message TEXT NULL,
            INDEX idx_report (report_id),
            INDEX idx_user (run_by_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 30 - ANALYTICS
    'kpis' => "
        CREATE TABLE IF NOT EXISTS kpis (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kpi_name VARCHAR(100) NOT NULL,
            kpi_category VARCHAR(50) NULL,
            kpi_value DECIMAL(15,2) NOT NULL,
            target_value DECIMAL(15,2) NULL,
            unit VARCHAR(20) NULL,
            measurement_date DATE NOT NULL,
            department VARCHAR(50) NULL,
            INDEX idx_name (kpi_name),
            INDEX idx_date (measurement_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'employee_kpis' => "
        CREATE TABLE IF NOT EXISTS employee_kpis (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            kpi_name VARCHAR(100) NOT NULL,
            target_value DECIMAL(15,2) NOT NULL,
            achieved_value DECIMAL(15,2) DEFAULT 0.00,
            achievement_pct DECIMAL(5,2) DEFAULT 0.00,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            INDEX idx_employee (employee_id),
            INDEX idx_period (period_start, period_end)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'daily_metrics_summary' => "
        CREATE TABLE IF NOT EXISTS daily_metrics_summary (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            summary_date DATE NOT NULL,
            total_leads INT UNSIGNED DEFAULT 0,
            new_leads INT UNSIGNED DEFAULT 0,
            qualified_leads INT UNSIGNED DEFAULT 0,
            converted_leads INT UNSIGNED DEFAULT 0,
            total_inquiries INT UNSIGNED DEFAULT 0,
            total_visits INT UNSIGNED DEFAULT 0,
            total_bookings INT UNSIGNED DEFAULT 0,
            total_revenue DECIMAL(15,2) DEFAULT 0.00,
            avg_deal_size DECIMAL(15,2) DEFAULT 0.00,
            active_agents INT UNSIGNED DEFAULT 0,
            UNIQUE KEY uniq_date (summary_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'performance_benchmarks' => "
        CREATE TABLE IF NOT EXISTS performance_benchmarks (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            benchmark_name VARCHAR(100) NOT NULL,
            role VARCHAR(50) NULL,
            metric VARCHAR(50) NOT NULL,
            baseline_value DECIMAL(15,2) NOT NULL,
            target_value DECIMAL(15,2) NOT NULL,
            excellent_value DECIMAL(15,2) NULL,
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'forecast_results' => "
        CREATE TABLE IF NOT EXISTS forecast_results (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            forecast_type ENUM('sales','revenue','leads','inventory','demand') NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            predicted_value DECIMAL(15,2) NOT NULL,
            lower_bound DECIMAL(15,2) NULL,
            upper_bound DECIMAL(15,2) NULL,
            confidence DECIMAL(5,2) DEFAULT 0.00,
            model_used VARCHAR(50) NULL,
            input_data JSON NULL,
            generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_type (forecast_type),
            INDEX idx_period (period_start, period_end)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'market_analytics_summary' => "
        CREATE TABLE IF NOT EXISTS market_analytics_summary (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            district_id BIGINT UNSIGNED NULL,
            property_type VARCHAR(50) NOT NULL,
            summary_date DATE NOT NULL,
            avg_price DECIMAL(15,2) NULL,
            price_change_pct DECIMAL(5,2) DEFAULT 0.00,
            volume_change_pct DECIMAL(5,2) DEFAULT 0.00,
            demand_score DECIMAL(5,2) DEFAULT 0.00,
            top_localities JSON NULL,
            INDEX idx_district (district_id),
            INDEX idx_date (summary_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'analytics_dashboards' => "
        CREATE TABLE IF NOT EXISTS analytics_dashboards (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            dashboard_name VARCHAR(100) NOT NULL,
            owner_user_id BIGINT UNSIGNED NULL,
            layout JSON NULL,
            widgets JSON NULL,
            is_public TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_owner (owner_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 31 - AUTH/SECURITY
    'two_factor_tokens' => "
        CREATE TABLE IF NOT EXISTS two_factor_tokens (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            token VARCHAR(20) NOT NULL,
            method ENUM('totp','sms','email','backup_code') NOT NULL,
            used_at DATETIME NULL,
            expires_at DATETIME NOT NULL,
            ip_address VARCHAR(45) NULL,
            INDEX idx_user (user_id),
            INDEX idx_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'password_reset_tokens' => "
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            email VARCHAR(150) NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            ip_address VARCHAR(45) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_token (token_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'blocked_ips' => "
        CREATE TABLE IF NOT EXISTS blocked_ips (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL UNIQUE,
            reason TEXT NULL,
            blocked_until DATETIME NULL,
            blocked_by_user_id BIGINT UNSIGNED NULL,
            attempt_count INT UNSIGNED DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'failed_login_attempts' => "
        CREATE TABLE IF NOT EXISTS failed_login_attempts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(150) NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_ip (ip_address),
            INDEX idx_time (attempt_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 32 - MARKETING/FINANCE
    'campaign_deliveries' => "
        CREATE TABLE IF NOT EXISTS campaign_deliveries (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            campaign_id BIGINT UNSIGNED NOT NULL,
            channel ENUM('email','sms','whatsapp','push','in_app') NOT NULL,
            recipient VARCHAR(200) NOT NULL,
            status ENUM('queued','sent','delivered','opened','clicked','bounced','failed') DEFAULT 'queued',
            sent_at DATETIME NULL,
            delivered_at DATETIME NULL,
            opened_at DATETIME NULL,
            clicked_at DATETIME NULL,
            error_message TEXT NULL,
            INDEX idx_campaign (campaign_id),
            INDEX idx_channel (channel),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'budgets' => "
        CREATE TABLE IF NOT EXISTS budgets (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            budget_name VARCHAR(100) NOT NULL,
            department_id BIGINT UNSIGNED NULL,
            category VARCHAR(50) NULL,
            fiscal_year VARCHAR(10) NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            allocated_amount DECIMAL(15,2) NOT NULL,
            spent_amount DECIMAL(15,2) DEFAULT 0.00,
            committed_amount DECIMAL(15,2) DEFAULT 0.00,
            remaining_amount DECIMAL(15,2) GENERATED ALWAYS AS (allocated_amount - spent_amount - committed_amount) STORED,
            status ENUM('draft','approved','active','closed') DEFAULT 'draft',
            approved_by BIGINT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_fiscal (fiscal_year),
            INDEX idx_dept (department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'budget_planning' => "
        CREATE TABLE IF NOT EXISTS budget_planning (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fiscal_year VARCHAR(10) NOT NULL,
            department_id BIGINT UNSIGNED NULL,
            line_item VARCHAR(200) NOT NULL,
            category VARCHAR(50) NULL,
            q1_amount DECIMAL(15,2) DEFAULT 0.00,
            q2_amount DECIMAL(15,2) DEFAULT 0.00,
            q3_amount DECIMAL(15,2) DEFAULT 0.00,
            q4_amount DECIMAL(15,2) DEFAULT 0.00,
            total_amount DECIMAL(15,2) GENERATED ALWAYS AS (q1_amount + q2_amount + q3_amount + q4_amount) STORED,
            justification TEXT NULL,
            status ENUM('draft','submitted','approved','rejected') DEFAULT 'draft',
            INDEX idx_fiscal (fiscal_year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'cash_flow_projections' => "
        CREATE TABLE IF NOT EXISTS cash_flow_projections (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            projection_date DATE NOT NULL,
            scenario ENUM('optimistic','realistic','pessimistic') DEFAULT 'realistic',
            opening_balance DECIMAL(15,2) NOT NULL,
            projected_inflow DECIMAL(15,2) DEFAULT 0.00,
            projected_outflow DECIMAL(15,2) DEFAULT 0.00,
            net_flow DECIMAL(15,2) GENERATED ALWAYS AS (projected_inflow - projected_outflow) STORED,
            closing_balance DECIMAL(15,2) GENERATED ALWAYS AS (opening_balance + projected_inflow - projected_outflow) STORED,
            notes TEXT NULL,
            created_by BIGINT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_date (projection_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'gst_returns' => "
        CREATE TABLE IF NOT EXISTS gst_returns (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            return_period VARCHAR(10) NOT NULL,
            gstin VARCHAR(20) NULL,
            return_type ENUM('gstr1','gstr3b','gstr9','annual') NOT NULL,
            total_taxable_value DECIMAL(15,2) DEFAULT 0.00,
            total_tax_amount DECIMAL(15,2) DEFAULT 0.00,
            total_itc_claimed DECIMAL(15,2) DEFAULT 0.00,
            net_payable DECIMAL(15,2) DEFAULT 0.00,
            filing_status ENUM('draft','filed','late_filed','pending') DEFAULT 'draft',
            filed_at DATETIME NULL,
            acknowledgment_number VARCHAR(50) NULL,
            INDEX idx_period (return_period),
            INDEX idx_type (return_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'gst_settings' => "
        CREATE TABLE IF NOT EXISTS gst_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            description TEXT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'tax_slabs' => "
        CREATE TABLE IF NOT EXISTS tax_slabs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tax_type ENUM('income_tax','gst','tds','property_tax') NOT NULL,
            min_amount DECIMAL(15,2) NOT NULL,
            max_amount DECIMAL(15,2) NULL,
            rate_pct DECIMAL(5,2) NOT NULL,
            fiscal_year VARCHAR(10) NULL,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_type (tax_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'tax_types' => "
        CREATE TABLE IF NOT EXISTS tax_types (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type_code VARCHAR(20) NOT NULL UNIQUE,
            type_name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            default_rate DECIMAL(5,2) DEFAULT 0.00,
            is_active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'budget_expenses' => "
        CREATE TABLE IF NOT EXISTS budget_expenses (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            budget_id BIGINT UNSIGNED NOT NULL,
            expense_date DATE NOT NULL,
            vendor VARCHAR(200) NULL,
            description TEXT NULL,
            amount DECIMAL(15,2) NOT NULL,
            bill_number VARCHAR(50) NULL,
            bill_file VARCHAR(500) NULL,
            status ENUM('pending','approved','paid','rejected') DEFAULT 'pending',
            approved_by BIGINT UNSIGNED NULL,
            INDEX idx_budget (budget_id),
            INDEX idx_date (expense_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'sms_templates' => "
        CREATE TABLE IF NOT EXISTS sms_templates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            template_code VARCHAR(100) NOT NULL UNIQUE,
            template_name VARCHAR(200) NOT NULL,
            body TEXT NOT NULL,
            variables JSON NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // PHASE 33 - AGENT ORCHESTRATION
    'agent_tasks' => "
        CREATE TABLE IF NOT EXISTS agent_tasks (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            agent_name VARCHAR(100) NOT NULL,
            task_type VARCHAR(50) NOT NULL,
            task_payload JSON NULL,
            priority INT UNSIGNED DEFAULT 100,
            status ENUM('queued','running','completed','failed','cancelled') DEFAULT 'queued',
            assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            started_at DATETIME NULL,
            completed_at DATETIME NULL,
            result JSON NULL,
            error_message TEXT NULL,
            INDEX idx_agent (agent_name),
            INDEX idx_status (status),
            INDEX idx_priority (priority)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'agent_executions' => "
        CREATE TABLE IF NOT EXISTS agent_executions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            agent_name VARCHAR(100) NOT NULL,
            execution_start DATETIME DEFAULT CURRENT_TIMESTAMP,
            execution_end DATETIME NULL,
            tasks_total INT UNSIGNED DEFAULT 0,
            tasks_completed INT UNSIGNED DEFAULT 0,
            tasks_failed INT UNSIGNED DEFAULT 0,
            metrics JSON NULL,
            INDEX idx_agent (agent_name),
            INDEX idx_start (execution_start)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'agent_state' => "
        CREATE TABLE IF NOT EXISTS agent_state (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            agent_name VARCHAR(100) NOT NULL,
            state_key VARCHAR(200) NOT NULL,
            state_value JSON NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_agent_key (agent_name, state_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'workflow_automations' => "
        CREATE TABLE IF NOT EXISTS workflow_automations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            automation_name VARCHAR(100) NOT NULL,
            trigger_event VARCHAR(100) NOT NULL,
            conditions JSON NULL,
            actions JSON NULL,
            is_active TINYINT(1) DEFAULT 1,
            last_run_at DATETIME NULL,
            run_count INT UNSIGNED DEFAULT 0,
            INDEX idx_trigger (trigger_event),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
];

$created = 0;
$exists = 0;
$failed = 0;
foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        $created++;
        echo "  âœ“ $name\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            $exists++;
            echo "  - $name (exists)\n";
        } else {
            $failed++;
            echo "  âœ— $name: " . substr($e->getMessage(), 0, 80) . "\n";
        }
    }
}

echo PHP_EOL . "=== PHASE 24-33 TABLES COMPLETE ===" . PHP_EOL;
echo "Created: $created | Exists: $exists | Failed: $failed" . PHP_EOL;
echo "Total tables in script: " . count($tables) . PHP_EOL;?>