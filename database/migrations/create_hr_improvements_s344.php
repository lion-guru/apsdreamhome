<?php
/**
 * Migration: HR Improvements - Salary Advance/Loan, Leave Encashment, Overtime, Asset View, Announcements, Shift Swaps, Helpdesk
 * Session: 344
 * Idempotent: Safe to re-run, checks before creating
 */
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();

    // 1. Salary Advances / Loans
    $db->execute("
        CREATE TABLE IF NOT EXISTS salary_advances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            type ENUM('advance','loan') NOT NULL DEFAULT 'advance',
            amount DECIMAL(12,2) NOT NULL,
            installments INT DEFAULT 1,
            emi_amount DECIMAL(10,2) DEFAULT 0,
            interest_rate DECIMAL(5,2) DEFAULT 0,
            status ENUM('pending','approved','rejected','disbursed','repaid','cancelled') DEFAULT 'pending',
            requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approved_at TIMESTAMP NULL,
            approved_by BIGINT UNSIGNED NULL,
            rejected_at TIMESTAMP NULL,
            rejected_by BIGINT UNSIGNED NULL,
            rejection_reason TEXT,
            disbursed_at TIMESTAMP NULL,
            disbursed_by BIGINT UNSIGNED NULL,
            disbursement_ref VARCHAR(100),
            total_repaid DECIMAL(12,2) DEFAULT 0,
            remaining_balance DECIMAL(12,2) DEFAULT 0,
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_sa_emp (employee_id),
            KEY idx_sa_status (status),
            KEY idx_sa_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "salary_advances: CREATED/EXISTS\n";

    // 2. Salary Advance Repayments
    $db->execute("
        CREATE TABLE IF NOT EXISTS salary_advance_repayments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            advance_id INT NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            installment_no INT NOT NULL,
            principal_amount DECIMAL(10,2) NOT NULL,
            interest_amount DECIMAL(10,2) DEFAULT 0,
            total_amount DECIMAL(10,2) NOT NULL,
            paid_amount DECIMAL(10,2) DEFAULT 0,
            payment_date DATE,
            salary_payment_id INT UNSIGNED,
            status ENUM('pending','paid','overdue','waived') DEFAULT 'pending',
            due_date DATE,
            paid_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_sar_advance (advance_id),
            KEY idx_sar_emp (employee_id),
            KEY idx_sar_status (status),
            KEY idx_sar_due (due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "salary_advance_repayments: CREATED/EXISTS\n";

    // 3. Leave Encashment Requests
    $db->execute("
        CREATE TABLE IF NOT EXISTS leave_encashment_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            leave_type_id INT NOT NULL,
            days_to_encash DECIMAL(5,2) NOT NULL,
            eligible_days DECIMAL(5,2) NOT NULL,
            amount_per_day DECIMAL(10,2) NOT NULL,
            total_amount DECIMAL(12,2) NOT NULL,
            tax_deduction DECIMAL(10,2) DEFAULT 0,
            net_amount DECIMAL(12,2) NOT NULL,
            status ENUM('pending','approved','rejected','paid','cancelled') DEFAULT 'pending',
            requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approved_at TIMESTAMP NULL,
            approved_by BIGINT UNSIGNED NULL,
            rejected_at TIMESTAMP NULL,
            rejected_by BIGINT UNSIGNED NULL,
            rejection_reason TEXT,
            paid_at TIMESTAMP NULL,
            paid_by BIGINT UNSIGNED NULL,
            payment_ref VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ler_emp (employee_id),
            KEY idx_ler_status (status),
            KEY idx_ler_type (leave_type_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "leave_encashment_requests: CREATED/EXISTS\n";

    // 4. Overtime Requests
    $db->execute("
        CREATE TABLE IF NOT EXISTS overtime_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            hours DECIMAL(4,2) NOT NULL,
            rate_multiplier DECIMAL(3,2) DEFAULT 1.50,
            hourly_rate DECIMAL(10,2) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            reason TEXT,
            status ENUM('pending','approved','rejected','paid','cancelled') DEFAULT 'pending',
            requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approved_at TIMESTAMP NULL,
            approved_by BIGINT UNSIGNED NULL,
            rejected_at TIMESTAMP NULL,
            rejected_by BIGINT UNSIGNED NULL,
            rejection_reason TEXT,
            paid_at TIMESTAMP NULL,
            paid_by BIGINT UNSIGNED NULL,
            payslip_id INT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ot_emp (employee_id),
            KEY idx_ot_status (status),
            KEY idx_ot_date (date),
            KEY idx_ot_payslip (payslip_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "overtime_requests: CREATED/EXISTS\n";

    // 5. Overtime Policies
    $db->execute("
        CREATE TABLE IF NOT EXISTS overtime_policies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            applicable_roles JSON,
            applicable_departments JSON,
            weekday_multiplier DECIMAL(3,2) DEFAULT 1.50,
            weekend_multiplier DECIMAL(3,2) DEFAULT 2.00,
            holiday_multiplier DECIMAL(3,2) DEFAULT 2.00,
            max_hours_per_day DECIMAL(4,2) DEFAULT 4.00,
            max_hours_per_month DECIMAL(5,2) DEFAULT 50.00,
            requires_approval BOOLEAN DEFAULT TRUE,
            auto_approve_threshold DECIMAL(4,2) DEFAULT 2.00,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_op_name (tenant_id, name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "overtime_policies: CREATED/EXISTS\n";

    // 6. Employee Asset Assignments (if not exists)
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_asset_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            asset_type ENUM('laptop','phone','vehicle','access_card','other') NOT NULL,
            asset_name VARCHAR(200) NOT NULL,
            asset_serial VARCHAR(100),
            asset_tag VARCHAR(50),
            assigned_date DATE NOT NULL,
            returned_date DATE NULL,
            condition_issued ENUM('new','good','fair','poor') DEFAULT 'good',
            condition_returned ENUM('new','good','fair','poor','damaged'),
            value DECIMAL(12,2) DEFAULT 0,
            condition_notes TEXT,
            issued_by BIGINT UNSIGNED,
            received_by BIGINT UNSIGNED,
            returned_to BIGINT UNSIGNED,
            status ENUM('active','returned','lost','damaged','transferred') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_eaa_emp (employee_id),
            KEY idx_eaa_status (status),
            KEY idx_eaa_type (asset_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_asset_assignments: CREATED/EXISTS\n";

    // 7. Company Announcements
    $db->execute("
        CREATE TABLE IF NOT EXISTS company_announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            type ENUM('general','hr','it','finance','operations','emergency') DEFAULT 'general',
            priority ENUM('low','normal','high','urgent') DEFAULT 'normal',
            target_audience ENUM('all','department','role','specific') DEFAULT 'all',
            target_departments JSON,
            target_roles JSON,
            target_users JSON,
            start_date DATE NOT NULL,
            end_date DATE,
            is_pinned BOOLEAN DEFAULT FALSE,
            attachments JSON,
            created_by BIGINT UNSIGNED,
            status ENUM('draft','published','archived') DEFAULT 'published',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ca_status (status),
            KEY idx_ca_dates (start_date, end_date),
            KEY idx_ca_audience (target_audience)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "company_announcements: CREATED/EXISTS\n";

    // 8. Announcement Read Receipts
    $db->execute("
        CREATE TABLE IF NOT EXISTS announcement_reads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            announcement_id INT NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_read (announcement_id, employee_id),
            KEY idx_ar_emp (employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "announcement_reads: CREATED/EXISTS\n";

    // 9. Shift Swap Requests
    $db->execute("
        CREATE TABLE IF NOT EXISTS shift_swap_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            requester_id BIGINT UNSIGNED NOT NULL,
            target_id BIGINT UNSIGNED NOT NULL,
            requester_shift_id INT UNSIGNED,
            target_shift_id INT UNSIGNED,
            swap_date DATE NOT NULL,
            requester_shift_start TIME,
            requester_shift_end TIME,
            target_shift_start TIME,
            target_shift_end TIME,
            reason TEXT,
            status ENUM('pending','approved','rejected','cancelled','completed') DEFAULT 'pending',
            requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            responded_at TIMESTAMP NULL,
            responded_by BIGINT UNSIGNED,
            approved_at TIMESTAMP NULL,
            approved_by BIGINT UNSIGNED,
            rejection_reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ssr_requester (requester_id),
            KEY idx_ssr_target (target_id),
            KEY idx_ssr_status (status),
            KEY idx_ssr_date (swap_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "shift_swap_requests: CREATED/EXISTS\n";

    // 10. Helpdesk Tickets
    $db->execute("
        CREATE TABLE IF NOT EXISTS helpdesk_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            ticket_number VARCHAR(20) NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            category ENUM('it','hr','finance','facilities','security','other') NOT NULL,
            sub_category VARCHAR(100),
            priority ENUM('low','normal','high','urgent','critical') DEFAULT 'normal',
            subject VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('open','in_progress','on_hold','resolved','closed','reopened') DEFAULT 'open',
            assigned_to BIGINT UNSIGNED,
            assigned_at TIMESTAMP NULL,
            resolved_at TIMESTAMP NULL,
            resolved_by BIGINT UNSIGNED,
            resolution TEXT,
            sla_hours INT DEFAULT 24,
            first_response_at TIMESTAMP NULL,
            sla_breached BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_ticket_number (tenant_id, ticket_number),
            KEY idx_ht_emp (employee_id),
            KEY idx_ht_status (status),
            KEY idx_ht_category (category),
            KEY idx_ht_priority (priority),
            KEY idx_ht_assigned (assigned_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "helpdesk_tickets: CREATED/EXISTS\n";

    // 11. Helpdesk Ticket Comments
    $db->execute("
        CREATE TABLE IF NOT EXISTS helpdesk_ticket_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            ticket_id INT NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            comment TEXT NOT NULL,
            is_internal BOOLEAN DEFAULT FALSE,
            attachments JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_htc_ticket (ticket_id),
            KEY idx_htc_emp (employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "helpdesk_ticket_comments: CREATED/EXISTS\n";

    // 12. Onboarding Checklists
    $db->execute("
        CREATE TABLE IF NOT EXISTS onboarding_checklists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            template_id INT UNSIGNED,
            item_name VARCHAR(255) NOT NULL,
            description TEXT,
            category ENUM('document','access','training','equipment','compliance','other') DEFAULT 'other',
            is_mandatory BOOLEAN DEFAULT TRUE,
            assigned_to BIGINT UNSIGNED,
            due_date DATE,
            status ENUM('pending','in_progress','completed','skipped','waived') DEFAULT 'pending',
            completed_at TIMESTAMP NULL,
            completed_by BIGINT UNSIGNED,
            notes TEXT,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at_ts TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_oc_emp (employee_id),
            KEY idx_oc_status (status),
            KEY idx_oc_assigned (assigned_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "onboarding_checklists: CREATED/EXISTS\n";

    // 13. Offboarding Checklists
    $db->execute("
        CREATE TABLE IF NOT EXISTS offboarding_checklists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            template_id INT UNSIGNED,
            item_name VARCHAR(255) NOT NULL,
            description TEXT,
            category ENUM('asset_return','access_revocation','knowledge_transfer','clearance','exit_interview','final_settlement','other') DEFAULT 'other',
            is_mandatory BOOLEAN DEFAULT TRUE,
            assigned_to BIGINT UNSIGNED,
            due_date DATE,
            status ENUM('pending','in_progress','completed','skipped','waived') DEFAULT 'pending',
            completed_at TIMESTAMP NULL,
            completed_by BIGINT UNSIGNED,
            notes TEXT,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_occ_emp (employee_id),
            KEY idx_occ_status (status),
            KEY idx_occ_assigned (assigned_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "offboarding_checklists: CREATED/EXISTS\n";

    // 14. Holiday Calendar
    $db->execute("
        CREATE TABLE IF NOT EXISTS holiday_calendar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            name VARCHAR(200) NOT NULL,
            date DATE NOT NULL,
            type ENUM('national','state','company','regional','religious','optional') DEFAULT 'company',
            region VARCHAR(100),
            description TEXT,
            is_half_day BOOLEAN DEFAULT FALSE,
            is_working_day BOOLEAN DEFAULT FALSE,
            is_recurring BOOLEAN DEFAULT FALSE,
            recurrence_rule VARCHAR(200),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_holiday (tenant_id, name, date),
            KEY idx_hc_date (date),
            KEY idx_hc_type (type),
            KEY idx_hc_region (region)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "holiday_calendar: CREATED/EXISTS\n";

    // 13. Grievance/Complaint System
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_grievances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            grievance_number VARCHAR(20) NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            category ENUM('harassment','discrimination','workplace_safety','payroll','leave','manager','colleague','policy','facilities','other') NOT NULL,
            sub_category VARCHAR(100),
            priority ENUM('low','normal','high','urgent','critical') DEFAULT 'normal',
            subject VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('open','under_review','investigation','mediation','resolved','closed','reopened') DEFAULT 'open',
            assigned_to BIGINT UNSIGNED,
            assigned_at TIMESTAMP NULL,
            resolved_at TIMESTAMP NULL,
            resolved_by BIGINT UNSIGNED,
            resolution TEXT,
            escalated BOOLEAN DEFAULT FALSE,
            escalated_at TIMESTAMP NULL,
            escalated_to BIGINT UNSIGNED,
            sla_hours INT DEFAULT 48,
            first_response_at TIMESTAMP NULL,
            sla_breached BOOLEAN DEFAULT FALSE,
            is_anonymous BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_grievance_number (tenant_id, grievance_number),
            KEY idx_eg_emp (employee_id),
            KEY idx_eg_status (status),
            KEY idx_eg_category (category),
            KEY idx_eg_priority (priority),
            KEY idx_eg_assigned (assigned_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_grievances: CREATED/EXISTS\n";

    // 14. Grievance Comments
    $db->execute("
        CREATE TABLE IF NOT EXISTS grievance_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            grievance_id INT NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            comment TEXT NOT NULL,
            is_internal BOOLEAN DEFAULT FALSE,
            attachments JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_gc_grievance (grievance_id),
            KEY idx_gc_emp (employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "grievance_comments: CREATED/EXISTS\n";

    // 14. Training Courses
    $db->execute("
        CREATE TABLE IF NOT EXISTS training_courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            code VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            category ENUM('technical','soft_skills','compliance','leadership','safety','onboarding','other') DEFAULT 'other',
            duration_hours DECIMAL(5,2) DEFAULT 0,
            format ENUM('online','offline','hybrid','self_paced') DEFAULT 'online',
            max_participants INT,
            prerequisites TEXT,
            instructor_name VARCHAR(200),
            instructor_email VARCHAR(200),
            material_url VARCHAR(500),
            assessment_required BOOLEAN DEFAULT FALSE,
            passing_score DECIMAL(5,2) DEFAULT 70,
            valid_for_months INT DEFAULT 12,
            certificate_template VARCHAR(500),
            is_active BOOLEAN DEFAULT TRUE,
            created_by BIGINT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_course_code (tenant_id, code),
            KEY idx_tc_category (category),
            KEY idx_tc_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "training_courses: CREATED/EXISTS\n";

    // 15. Course Assignments
    $db->execute("
        CREATE TABLE IF NOT EXISTS course_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            course_id INT NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            assigned_by BIGINT UNSIGNED,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            due_date DATE,
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            progress DECIMAL(5,2) DEFAULT 0,
            score DECIMAL(5,2),
            status ENUM('assigned','in_progress','completed','failed','overdue','cancelled') DEFAULT 'assigned',
            certificate_issued BOOLEAN DEFAULT FALSE,
            certificate_number VARCHAR(100),
            certificate_issued_at TIMESTAMP NULL,
            feedback TEXT,
            rating TINYINT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_course_emp (tenant_id, course_id, employee_id),
            KEY idx_ca_emp (employee_id),
            KEY idx_ca_course (course_id),
            KEY idx_ca_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "course_assignments: CREATED/EXISTS\n";

    // 15. Certifications
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_certifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            course_id INT,
            certification_name VARCHAR(255) NOT NULL,
            issuing_body VARCHAR(255),
            issue_date DATE NOT NULL,
            expiry_date DATE,
            credential_id VARCHAR(200),
            credential_url VARCHAR(500),
            status ENUM('active','expired','revoked','pending_verification') DEFAULT 'active',
            verified_at TIMESTAMP NULL,
            verified_by BIGINT UNSIGNED,
            attachment_path VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ec_emp (employee_id),
            KEY idx_ec_course (course_id),
            KEY idx_ec_status (status),
            KEY idx_ec_expiry (expiry_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_certifications: CREATED/EXISTS\n";

    // 16. Org Chart Nodes
    $db->execute("
        CREATE TABLE IF NOT EXISTS org_chart_nodes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            parent_id INT UNSIGNED,
            position_title VARCHAR(200) NOT NULL,
            department VARCHAR(100),
            level INT DEFAULT 0,
            sort_order INT DEFAULT 0,
            is_vacant BOOLEAN DEFAULT FALSE,
            vacant_since DATE,
            custom_data JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_org_emp (tenant_id, employee_id),
            KEY idx_oc_parent (parent_id),
            KEY idx_oc_dept (department),
            KEY idx_oc_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "org_chart_nodes: CREATED/EXISTS\n";

    // 17. Engagement Surveys
    $db->execute("
        CREATE TABLE IF NOT EXISTS engagement_surveys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            survey_type ENUM('pulse','annual','onboarding','exit','custom') DEFAULT 'pulse',
            status ENUM('draft','scheduled','active','closed','archived') DEFAULT 'draft',
            start_date DATE,
            end_date DATE,
            is_anonymous BOOLEAN DEFAULT TRUE,
            allow_multiple BOOLEAN DEFAULT FALSE,
            questions JSON NOT NULL,
            settings JSON,
            created_by BIGINT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_es_status (status),
            KEY idx_es_dates (start_date, end_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "engagement_surveys: CREATED/EXISTS\n";

    // 18. Survey Responses
    $db->execute("
        CREATE TABLE IF NOT EXISTS survey_responses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            survey_id INT NOT NULL,
            employee_id BIGINT UNSIGNED,
            answers JSON NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            completion_time_seconds INT,
            KEY idx_sr_survey (survey_id),
            KEY idx_sr_emp (employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "survey_responses: CREATED/EXISTS\n";

    // Seed default overtime policy
    $db->execute("
        INSERT IGNORE INTO overtime_policies (tenant_id, name, description, weekday_multiplier, weekend_multiplier, holiday_multiplier, max_hours_per_day, max_hours_per_month, requires_approval, auto_approve_threshold, is_active)
        VALUES (1, 'Standard OT Policy', 'Standard overtime policy for all departments', 1.50, 2.00, 2.00, 4.00, 50.00, 1, 2.00, 1)
    ");
    echo "overtime_policies: SEEDED\n";

    // Seed some default holidays
    $holidays = [
        ['New Year', '2026-01-01', 'national', 'IN', 'New Year Day', 0, 0],
        ['Republic Day', '2026-01-26', 'national', 'IN', 'Republic Day', 0, 0],
        ['Holi', '2026-03-14', 'national', 'IN', 'Festival of Colors', 0, 0],
        ['Good Friday', '2026-04-03', 'national', 'IN', 'Good Friday', 0, 0],
        ['Eid al-Fitr', '2026-03-21', 'religious', 'IN', 'Eid al-Fitr', 0, 0],
        ['Independence Day', '2026-08-15', 'national', 'IN', 'Independence Day', 0, 0],
        ['Gandhi Jayanti', '2026-10-02', 'national', 'IN', 'Gandhi Jayanti', 0, 0],
        ['Diwali', '2026-10-20', 'national', 'IN', 'Festival of Lights', 0, 0],
        ['Christmas', '2026-12-25', 'national', 'IN', 'Christmas Day', 0, 0],
    ];
    
    foreach ($holidays as $h) {
        $db->execute("
            INSERT IGNORE INTO holiday_calendar (tenant_id, name, date, type, region, description, is_half_day, is_working_day)
            VALUES (1, ?, ?, ?, ?, ?, ?, ?)
        ", $h);
    }
    echo "holiday_calendar: SEEDED with " . count($holidays) . " holidays\n";

    // Seed default announcement
    $db->execute("
        INSERT IGNORE INTO company_announcements (tenant_id, title, content, type, priority, target_audience, start_date, end_date, is_pinned, created_by, status)
        VALUES (1, 'Welcome to APS Dream Home HR Portal', 'Welcome to the new HR Self-Service Portal. Here you can manage your leaves, payslips, reimbursements, and more.', 'general', 'normal', 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1, 1, 'published')
    ");
    echo "company_announcements: SEEDED\n";

    echo "\n=== ALL HR IMPROVEMENT MIGRATIONS COMPLETED SUCCESSFULLY ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}