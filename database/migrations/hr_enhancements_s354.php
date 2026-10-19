<?php
/**
 * Migration: HR System Enhancements - Reimbursement, Performance, Payroll, Recruitment, Documents
 * Session: 354
 * Idempotent: Safe to re-run, checks before creating
 */
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();

    // ============================================================
    // 1. EXPENSE REIMBURSEMENT SYSTEM
    // ============================================================

    // 1.1 Expense Categories
    $db->execute("
        CREATE TABLE IF NOT EXISTS expense_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(20) NOT NULL,
            description TEXT,
            max_amount_per_claim DECIMAL(12,2) DEFAULT 0,
            requires_receipt BOOLEAN DEFAULT TRUE,
            gst_applicable BOOLEAN DEFAULT FALSE,
            gst_rate DECIMAL(5,2) DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_ec_code (tenant_id, code),
            KEY idx_ec_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "expense_categories: CREATED/EXISTS\n";

    // 1.2 Expense Claims
    $db->execute("
        CREATE TABLE IF NOT EXISTS expense_claims (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            claim_number VARCHAR(30) NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            category_id INT NOT NULL,
            total_amount DECIMAL(12,2) NOT NULL,
            gst_amount DECIMAL(10,2) DEFAULT 0,
            currency VARCHAR(3) DEFAULT 'INR',
            exchange_rate DECIMAL(10,6) DEFAULT 1,
            claim_date DATE NOT NULL,
            description TEXT,
            status ENUM('draft','submitted','under_review','approved','rejected','paid','cancelled') DEFAULT 'draft',
            submitted_at TIMESTAMP NULL,
            reviewed_at TIMESTAMP NULL,
            reviewed_by BIGINT UNSIGNED NULL,
            rejection_reason TEXT,
            approved_at TIMESTAMP NULL,
            approved_by BIGINT UNSIGNED NULL,
            paid_at TIMESTAMP NULL,
            paid_by BIGINT UNSIGNED NULL,
            payment_ref VARCHAR(100),
            payment_mode ENUM('bank_transfer','cash','upi','wallet','other') DEFAULT 'bank_transfer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_claim_number (tenant_id, claim_number),
            KEY idx_ec_emp (employee_id),
            KEY idx_ec_status (status),
            KEY idx_ec_date (claim_date),
            KEY idx_ec_category (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "expense_claims: CREATED/EXISTS\n";

    // 1.3 Expense Claim Items
    $db->execute("
        CREATE TABLE IF NOT EXISTS expense_claim_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            claim_id INT NOT NULL,
            expense_date DATE NOT NULL,
            description VARCHAR(255) NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            gst_amount DECIMAL(10,2) DEFAULT 0,
            gst_rate DECIMAL(5,2) DEFAULT 0,
            hsn_sac_code VARCHAR(20),
            receipt_path VARCHAR(500),
            is_billable BOOLEAN DEFAULT FALSE,
            project_code VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_eci_claim (claim_id),
            KEY idx_eci_date (expense_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "expense_claim_items: CREATED/EXISTS\n";

    // 1.4 Expense Approval Workflow
    $db->execute("
        CREATE TABLE IF NOT EXISTS expense_approval_workflow (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            claim_id INT NOT NULL,
            level INT NOT NULL DEFAULT 1,
            approver_id BIGINT UNSIGNED NOT NULL,
            approver_role VARCHAR(50),
            status ENUM('pending','approved','rejected','delegated') DEFAULT 'pending',
            comments TEXT,
            action_at TIMESTAMP NULL,
            delegated_to BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_eaw_claim (claim_id),
            KEY idx_eaw_approver (approver_id),
            KEY idx_eaw_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "expense_approval_workflow: CREATED/EXISTS\n";

    // ============================================================
    // 2. PERFORMANCE & GOALS SYSTEM
    // ============================================================

    // 2.1 Goals/OKRs
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_goals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            goal_type ENUM('okr','kpi','personal','team','organizational') DEFAULT 'okr',
            title VARCHAR(255) NOT NULL,
            description TEXT,
            target_value DECIMAL(15,4) NOT NULL,
            target_unit VARCHAR(50),
            current_value DECIMAL(15,4) DEFAULT 0,
            weightage DECIMAL(5,2) DEFAULT 100,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('draft','active','on_track','at_risk','off_track','completed','cancelled') DEFAULT 'draft',
            progress_percentage DECIMAL(5,2) DEFAULT 0,
            parent_goal_id INT NULL,
            assigned_by BIGINT UNSIGNED,
            reviewed_by BIGINT UNSIGNED,
            review_frequency ENUM('weekly','biweekly','monthly','quarterly') DEFAULT 'monthly',
            last_reviewed_at TIMESTAMP NULL,
            next_review_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_eg_emp (employee_id),
            KEY idx_eg_status (status),
            KEY idx_eg_dates (start_date, end_date),
            KEY idx_eg_parent (parent_goal_id),
            KEY idx_eg_type (goal_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_goals: CREATED/EXISTS\n";

    // 2.2 Goal Progress Checkpoints
    $db->execute("
        CREATE TABLE IF NOT EXISTS goal_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            goal_id INT NOT NULL,
            checkpoint_date DATE NOT NULL,
            actual_value DECIMAL(15,4) NOT NULL,
            percentage_complete DECIMAL(5,2) DEFAULT 0,
            status ENUM('on_track','at_risk','off_track','achieved') DEFAULT 'on_track',
            notes TEXT,
            reviewer_id BIGINT UNSIGNED,
            reviewed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_gp_goal_date (goal_id, checkpoint_date),
            KEY idx_gp_goal (goal_id),
            KEY idx_gp_date (checkpoint_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "goal_progress: CREATED/EXISTS\n";

    // 2.3 Performance Reviews
    $db->execute("
        CREATE TABLE IF NOT EXISTS performance_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            reviewer_id BIGINT UNSIGNED NOT NULL,
            review_period_start DATE NOT NULL,
            review_period_end DATE NOT NULL,
            review_type ENUM('probation','quarterly','half_yearly','annual','project','360') DEFAULT 'annual',
            status ENUM('draft','in_progress','submitted','acknowledged','completed','disputed') DEFAULT 'draft',
            overall_rating DECIMAL(3,2),
            strengths TEXT,
            areas_of_improvement TEXT,
            goals_achievement TEXT,
            reviewer_comments TEXT,
            employee_comments TEXT,
            development_plan TEXT,
            next_review_date DATE,
            submitted_at TIMESTAMP NULL,
            acknowledged_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_pr_emp (employee_id),
            KEY idx_pr_reviewer (reviewer_id),
            KEY idx_pr_status (status),
            KEY idx_pr_period (review_period_start, review_period_end),
            KEY idx_pr_type (review_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "performance_reviews: CREATED/EXISTS\n";

    // 2.4 Performance Review Criteria/Ratings
    $db->execute("
        CREATE TABLE IF NOT EXISTS performance_review_ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            review_id INT NOT NULL,
            criterion_name VARCHAR(200) NOT NULL,
            criterion_description TEXT,
            weightage DECIMAL(5,2) DEFAULT 1,
            self_rating DECIMAL(3,2),
            reviewer_rating DECIMAL(3,2),
            final_rating DECIMAL(3,2),
            comments TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_prr_review (review_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "performance_review_ratings: CREATED/EXISTS\n";

    // ============================================================
    // 3. PAYROLL PROCESSING SYSTEM
    // ============================================================

    // 3.1 Payroll Runs
    $db->execute("
        CREATE TABLE IF NOT EXISTS payroll_runs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            run_number VARCHAR(30) NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            pay_date DATE NOT NULL,
            status ENUM('draft','calculating','review','approved','processing','completed','cancelled') DEFAULT 'draft',
            total_employees INT DEFAULT 0,
            total_gross DECIMAL(15,2) DEFAULT 0,
            total_deductions DECIMAL(15,2) DEFAULT 0,
            total_net DECIMAL(15,2) DEFAULT 0,
            processed_by BIGINT UNSIGNED,
            processed_at TIMESTAMP NULL,
            approved_by BIGINT UNSIGNED,
            approved_at TIMESTAMP NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_pr_run (tenant_id, run_number),
            KEY idx_pr_status (status),
            KEY idx_pr_period (period_start, period_end)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "payroll_runs: CREATED/EXISTS\n";

    // 3.2 Payroll Items (per employee per run)
    $db->execute("
        CREATE TABLE IF NOT EXISTS payroll_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            run_id INT NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            salary_structure_id INT,
            -- Earnings
            basic_salary DECIMAL(12,2) DEFAULT 0,
            hra DECIMAL(12,2) DEFAULT 0,
            da DECIMAL(12,2) DEFAULT 0,
            conveyance DECIMAL(12,2) DEFAULT 0,
            medical_allowance DECIMAL(12,2) DEFAULT 0,
            special_allowance DECIMAL(12,2) DEFAULT 0,
            overtime_pay DECIMAL(12,2) DEFAULT 0,
            bonus DECIMAL(12,2) DEFAULT 0,
            incentives DECIMAL(12,2) DEFAULT 0,
            arrears DECIMAL(12,2) DEFAULT 0,
            other_earnings DECIMAL(12,2) DEFAULT 0,
            gross_earnings DECIMAL(15,2) DEFAULT 0,
            -- Deductions
            pf_employee DECIMAL(12,2) DEFAULT 0,
            pf_employer DECIMAL(12,2) DEFAULT 0,
            esi_employee DECIMAL(12,2) DEFAULT 0,
            esi_employer DECIMAL(12,2) DEFAULT 0,
            professional_tax DECIMAL(12,2) DEFAULT 0,
            tds DECIMAL(12,2) DEFAULT 0,
            advance_recovery DECIMAL(12,2) DEFAULT 0,
            loan_recovery DECIMAL(12,2) DEFAULT 0,
            other_deductions DECIMAL(12,2) DEFAULT 0,
            total_deductions DECIMAL(15,2) DEFAULT 0,
            -- Net
            net_pay DECIMAL(15,2) DEFAULT 0,
            -- Employer Contributions
            employer_pf DECIMAL(12,2) DEFAULT 0,
            employer_esi DECIMAL(12,2) DEFAULT 0,
            gratuity_provision DECIMAL(12,2) DEFAULT 0,
            leave_encashment_provision DECIMAL(12,2) DEFAULT 0,
            total_employer_cost DECIMAL(15,2) DEFAULT 0,
            -- Status
            status ENUM('draft','calculated','verified','approved','paid') DEFAULT 'draft',
            payment_ref VARCHAR(100),
            payment_mode VARCHAR(50),
            paid_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_pi_run_emp (run_id, employee_id),
            KEY idx_pi_emp (employee_id),
            KEY idx_pi_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "payroll_items: CREATED/EXISTS\n";

    // 3.3 Statutory Returns
    $db->execute("
        CREATE TABLE IF NOT EXISTS statutory_returns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            return_type ENUM('pf','esi','pt','tds','labour_welfare','gratuity','bonus') NOT NULL,
            period_month INT NOT NULL,
            period_year INT NOT NULL,
            due_date DATE NOT NULL,
            filing_date DATE NULL,
            status ENUM('pending','filed','paid','overdue','revised') DEFAULT 'pending',
            amount_due DECIMAL(15,2) DEFAULT 0,
            amount_paid DECIMAL(15,2) DEFAULT 0,
            challan_number VARCHAR(100),
            challan_date DATE NULL,
            filed_by BIGINT UNSIGNED,
            filed_at TIMESTAMP NULL,
            acknowledgement_number VARCHAR(100),
            acknowledgement_date DATE NULL,
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_sr_type_period (tenant_id, return_type, period_month, period_year),
            KEY idx_sr_status (status),
            KEY idx_sr_due (due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "statutory_returns: CREATED/EXISTS\n";

    // ============================================================
    // 4. RECRUITMENT / ATS SYSTEM
    // ============================================================

    // 4.1 Job Postings
    $db->execute("
        CREATE TABLE IF NOT EXISTS job_postings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            job_code VARCHAR(30) NOT NULL,
            title VARCHAR(200) NOT NULL,
            department VARCHAR(100),
            designation VARCHAR(100),
            employment_type ENUM('full_time','part_time','contract','intern','freelance') DEFAULT 'full_time',
            experience_min INT DEFAULT 0,
            experience_max INT DEFAULT 0,
            salary_min DECIMAL(12,2),
            salary_max DECIMAL(12,2),
            salary_currency VARCHAR(3) DEFAULT 'INR',
            location VARCHAR(200),
            is_remote BOOLEAN DEFAULT FALSE,
            description TEXT NOT NULL,
            requirements TEXT,
            responsibilities TEXT,
            benefits TEXT,
            skills_required JSON,
            education_requirement VARCHAR(200),
            openings INT DEFAULT 1,
            hired_count INT DEFAULT 0,
            status ENUM('draft','published','on_hold','closed','cancelled') DEFAULT 'draft',
            published_at TIMESTAMP NULL,
            closes_at TIMESTAMP NULL,
            hiring_manager_id BIGINT UNSIGNED,
            recruiter_id BIGINT UNSIGNED,
            published_on JSON, -- ['careers_page', 'linkedin', 'naukri', 'indeed', 'referral']
            created_by BIGINT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_jp_code (tenant_id, job_code),
            KEY idx_jp_status (status),
            KEY idx_jp_dept (department),
            KEY idx_jp_dates (published_at, closes_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "job_postings: CREATED/EXISTS\n";

    // 4.2 Job Applications
    $db->execute("
        CREATE TABLE IF NOT EXISTS job_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            application_number VARCHAR(30) NOT NULL,
            job_id INT NOT NULL,
            candidate_name VARCHAR(200) NOT NULL,
            email VARCHAR(200) NOT NULL,
            phone VARCHAR(20),
            alternate_phone VARCHAR(20),
            current_location VARCHAR(200),
            preferred_location VARCHAR(200),
            current_ctc DECIMAL(12,2),
            expected_ctc DECIMAL(12,2),
            notice_period_days INT DEFAULT 0,
            resume_path VARCHAR(500),
            cover_letter TEXT,
            linkedin_url VARCHAR(500),
            portfolio_url VARCHAR(500),
            current_employer VARCHAR(200),
            current_designation VARCHAR(100),
            total_experience_years DECIMAL(4,1) DEFAULT 0,
            relevant_experience_years DECIMAL(4,1) DEFAULT 0,
            highest_qualification VARCHAR(200),
            skills JSON,
            certifications JSON,
            source ENUM('careers_page','linkedin','naukri','indeed','referral','agency','walkin','other') DEFAULT 'careers_page',
            referral_employee_id BIGINT UNSIGNED,
            status ENUM('applied','screening','shortlisted','interview_scheduled','interviewed','offer_extended','offer_accepted','offer_declined','hired','rejected','withdrawn') DEFAULT 'applied',
            current_stage VARCHAR(100),
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_stage_change_at TIMESTAMP NULL,
            screened_by BIGINT UNSIGNED,
            screened_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_ja_number (tenant_id, application_number),
            KEY idx_ja_job (job_id),
            KEY idx_ja_status (status),
            KEY idx_ja_email (email),
            KEY idx_ja_stage (current_stage)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "job_applications: CREATED/EXISTS\n";

    // 4.3 Interview Schedules
    $db->execute("
        CREATE TABLE IF NOT EXISTS interview_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            application_id INT NOT NULL,
            round_number INT DEFAULT 1,
            round_type ENUM('screening','technical','hr','managerial','final','cultural') DEFAULT 'technical',
            interviewer_id BIGINT UNSIGNED NOT NULL,
            interview_type ENUM('phone','video','in_person','assignment','assessment') DEFAULT 'video',
            scheduled_at DATETIME NOT NULL,
            duration_minutes INT DEFAULT 60,
            meeting_link VARCHAR(500),
            meeting_id VARCHAR(100),
            meeting_password VARCHAR(50),
            location VARCHAR(200),
            status ENUM('scheduled','confirmed','rescheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
            feedback TEXT,
            rating DECIMAL(3,2),
            recommendation ENUM('strong_hire','hire','no_hire','strong_no_hire') NULL,
            feedback_submitted_at TIMESTAMP NULL,
            reminder_sent BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_is_app (application_id),
            KEY idx_is_interviewer (interviewer_id),
            KEY idx_is_scheduled (scheduled_at),
            KEY idx_is_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "interview_schedules: CREATED/EXISTS\n";

    // 4.4 Offer Letters
    $db->execute("
        CREATE TABLE IF NOT EXISTS offer_letters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            offer_number VARCHAR(30) NOT NULL,
            application_id INT NOT NULL,
            candidate_name VARCHAR(200) NOT NULL,
            email VARCHAR(200) NOT NULL,
            job_title VARCHAR(200) NOT NULL,
            department VARCHAR(100),
            reporting_manager_id BIGINT UNSIGNED,
            joining_date DATE,
            employment_type ENUM('full_time','part_time','contract','intern') DEFAULT 'full_time',
            probation_period_months INT DEFAULT 3,
            ctc_annual DECIMAL(15,2) NOT NULL,
            ctc_monthly DECIMAL(12,2) NOT NULL,
            basic_salary DECIMAL(12,2),
            hra DECIMAL(12,2),
            allowances DECIMAL(12,2),
            bonus_variable DECIMAL(12,2),
            pf_employer DECIMAL(12,2),
            esi_employer DECIMAL(12,2),
            gratuity DECIMAL(12,2),
            mediclaim DECIMAL(12,2),
            other_benefits TEXT,
            offer_validity_days INT DEFAULT 7,
            status ENUM('draft','sent','viewed','accepted','declined','expired','withdrawn') DEFAULT 'draft',
            sent_at TIMESTAMP NULL,
            viewed_at TIMESTAMP NULL,
            responded_at TIMESTAMP NULL,
            response_reason TEXT,
            document_path VARCHAR(500),
            signed_document_path VARCHAR(500),
            created_by BIGINT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_ol_number (tenant_id, offer_number),
            KEY idx_ol_app (application_id),
            KEY idx_ol_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "offer_letters: CREATED/EXISTS\n";

    // ============================================================
    // 5. EMPLOYEE DOCUMENT VAULT
    // ============================================================

    // 5.1 Document Categories
    $db->execute("
        CREATE TABLE IF NOT EXISTS document_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(30) NOT NULL,
            description TEXT,
            is_mandatory BOOLEAN DEFAULT FALSE,
            retention_years INT DEFAULT 7,
            allowed_types JSON, -- ['pdf','jpg','png','doc','docx']
            max_size_mb INT DEFAULT 10,
            requires_expiry BOOLEAN DEFAULT FALSE,
            expiry_alert_days INT DEFAULT 30,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_dc_code (tenant_id, code),
            KEY idx_dc_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "document_categories: CREATED/EXISTS\n";

    // 5.2 Employee Documents
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            document_number VARCHAR(30) NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            category_id INT NOT NULL,
            document_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size INT DEFAULT 0,
            mime_type VARCHAR(100),
            file_hash VARCHAR(64), -- SHA256 for integrity
            issue_date DATE,
            expiry_date DATE,
            issuing_authority VARCHAR(200),
            document_number_ref VARCHAR(100), -- PAN, Aadhaar, Passport, DL number
            verification_status ENUM('not_verified','pending','verified','rejected','expired') DEFAULT 'not_verified',
            verified_by BIGINT UNSIGNED,
            verified_at TIMESTAMP NULL,
            rejection_reason TEXT,
            is_confidential BOOLEAN DEFAULT FALSE,
            access_level ENUM('self','hr','management','all') DEFAULT 'self',
            uploaded_by BIGINT UNSIGNED,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_ed_number (tenant_id, document_number),
            KEY idx_ed_emp (employee_id),
            KEY idx_ed_category (category_id),
            KEY idx_ed_status (verification_status),
            KEY idx_ed_expiry (expiry_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_documents: CREATED/EXISTS\n";

    // 5.3 Document Requests (HR requests docs from employee)
    $db->execute("
        CREATE TABLE IF NOT EXISTS document_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            request_number VARCHAR(30) NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            category_id INT NOT NULL,
            requested_by BIGINT UNSIGNED NOT NULL,
            due_date DATE NOT NULL,
            priority ENUM('low','normal','high','urgent') DEFAULT 'normal',
            message TEXT,
            status ENUM('pending','submitted','verified','rejected','overdue','cancelled') DEFAULT 'pending',
            submitted_at TIMESTAMP NULL,
            verified_by BIGINT UNSIGNED,
            verified_at TIMESTAMP NULL,
            rejection_reason TEXT,
            reminder_sent BOOLEAN DEFAULT FALSE,
            reminder_count INT DEFAULT 0,
            last_reminder_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_dr_number (tenant_id, request_number),
            KEY idx_dr_emp (employee_id),
            KEY idx_dr_status (status),
            KEY idx_dr_due (due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "document_requests: CREATED/EXISTS\n";

    // ============================================================
    // 6. SEED DEFAULT DATA
    // ============================================================

    // Seed Expense Categories
    $expenseCategories = [
        ['Travel - Local', 'TRAVEL_LOCAL', 'Local travel (auto, metro, bus)', 5000, 1, 1, 0],
        ['Travel - Outstation', 'TRAVEL_OUT', 'Outstation travel (train, flight, hotel)', 50000, 1, 1, 18],
        ['Food & Meals', 'FOOD', 'Business meals and entertainment', 3000, 1, 1, 5],
        ['Office Supplies', 'OFFICE_SUP', 'Stationery, printer ink, etc.', 5000, 1, 0, 18],
        ['Communication', 'COMM', 'Mobile, internet, phone bills', 3000, 1, 1, 18],
        ['Training & Development', 'TRAINING', 'Courses, certifications, workshops', 20000, 1, 1, 18],
        ['Medical', 'MEDICAL', 'Medical expenses, pharmacy', 10000, 1, 1, 0],
        ['Client Entertainment', 'CLIENT_ENT', 'Client meetings, gifts', 10000, 1, 1, 18],
        ['Vehicle/Fuel', 'VEHICLE', 'Fuel, maintenance, parking', 15000, 1, 1, 28],
        ['Miscellaneous', 'MISC', 'Other business expenses', 5000, 1, 0, 18],
    ];

    foreach ($expenseCategories as $i => $cat) {
        $db->execute("
            INSERT IGNORE INTO expense_categories (tenant_id, name, code, description, max_amount_per_claim, requires_receipt, gst_applicable, gst_rate, is_active, sort_order)
            VALUES (1, ?, ?, ?, ?, ?, ?, ?, 1, ?)
        ", [$cat[0], $cat[1], $cat[2], $cat[3], $cat[4], $cat[5], $cat[6], $i]);
    }
    echo "expense_categories: SEEDED\n";

    // Seed Document Categories (existing table has different schema - use name, description, parent_id, is_active, sort_order)
    $docCategories = [
        ['Identity Proof', 'Government issued ID (Aadhaar, PAN, Passport, Voter ID)', 0, 1, 1],
        ['Address Proof', 'Utility bill, rental agreement, bank statement', 0, 1, 2],
        ['Education Certificates', 'Degree, diploma, mark sheets, transcripts', 0, 1, 3],
        ['Experience Letters', 'Previous employment relieving/experience letters', 0, 1, 4],
        ['Offer & Appointment Letters', 'Current and previous offer letters', 0, 1, 5],
        ['Bank Details', 'Cancelled cheque, bank passbook first page', 0, 1, 6],
        ['Tax Documents', 'Form 16, 26AS, investment proofs', 0, 1, 7],
        ['Insurance & Nomination', 'Mediclaim, life insurance, nomination forms', 0, 1, 8],
        ['Statutory Registrations', 'PF, ESI, PT, Labour Welfare registration', 0, 1, 9],
        ['Agreements & Contracts', 'Employment contract, NDA, non-compete', 0, 1, 10],
    ];

    foreach ($docCategories as $i => $cat) {
        $db->execute("
            INSERT IGNORE INTO document_categories (name, description, parent_id, is_active, sort_order)
            VALUES (?, ?, 0, 1, ?)
        ", [$cat[0], $cat[1], $i + 1]);
    }
    echo "document_categories: SEEDED\n";

    // Seed Statutory Returns (monthly/quarterly schedule)
    $statutoryReturns = [
        ['pf', 'Provident Fund', 15, 'monthly'],
        ['esi', 'ESI', 15, 'monthly'],
        ['pt', 'Professional Tax', 20, 'monthly'],
        ['tds', 'TDS', 7, 'monthly'],
        ['labour_welfare', 'Labour Welfare Fund', 15, 'half_yearly'],
        ['gratuity', 'Gratuity Act Compliance', 30, 'annual'],
        ['bonus', 'Bonus Act Compliance', 30, 'annual'],
    ];

    foreach ($statutoryReturns as $sr) {
        $db->execute("
            INSERT IGNORE INTO statutory_returns (tenant_id, return_type, period_month, period_year, due_date, status)
            VALUES (1, ?, MONTH(CURDATE()), YEAR(CURDATE()), DATE_ADD(LAST_DAY(CURDATE()), INTERVAL ? DAY), 'pending')
        ", [$sr[0], $sr[3] === 'monthly' ? 15 : ($sr[3] === 'half_yearly' ? 45 : 60)]);
    }
    echo "statutory_returns: SEEDED\n";

    echo "\n=== ALL HR ENHANCEMENT MIGRATIONS COMPLETED SUCCESSFULLY ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}