<?php
/**
 * Migration: Create missing Employee Portal tables for work distribution, skills, performance
 * Session: 338
 * Idempotent: Safe to re-run
 */
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();

    // 1. Employee Skills Matrix
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            skill_name VARCHAR(100) NOT NULL,
            skill_category VARCHAR(50) DEFAULT 'general',
            proficiency_level ENUM('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
            years_experience DECIMAL(4,2) DEFAULT 0,
            certified BOOLEAN DEFAULT FALSE,
            certification_name VARCHAR(200),
            certification_expiry DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_emp_skill (tenant_id, employee_id, skill_name),
            KEY idx_emp_skill_emp (employee_id),
            KEY idx_emp_skill_cat (skill_category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_skills: CREATED/EXISTS\n";

    // 2. Employee Skill Matrix (normalized many-to-many for task matching)
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_skill_matrix (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            skill_id INT NOT NULL,
            proficiency DECIMAL(3,2) DEFAULT 1.00,
            last_used DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_skill_matrix_emp (employee_id),
            KEY idx_skill_matrix_skill (skill_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_skill_matrix: CREATED/EXISTS\n";

    // 3. Skills Reference Table
    $db->execute("
        CREATE TABLE IF NOT EXISTS skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(50) DEFAULT 'general',
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_skill_name (tenant_id, name),
            KEY idx_skill_cat (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "skills: CREATED/EXISTS\n";

    // 4. Employee Workload Configuration
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_workload (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            max_concurrent_tasks INT DEFAULT 10,
            max_workload_percentage DECIMAL(5,2) DEFAULT 80.00,
            preferred_departments JSON,
            auto_assign_enabled BOOLEAN DEFAULT TRUE,
            auto_assign_priority ENUM('low','medium','high','all') DEFAULT 'medium',
            work_hours_start TIME DEFAULT '09:00:00',
            work_hours_end TIME DEFAULT '18:00:00',
            timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_emp_workload (tenant_id, employee_id),
            KEY idx_emp_workload_emp (employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_workload: CREATED/EXISTS\n";

    // 5. Work Distribution Logs
    $db->execute("
        CREATE TABLE IF NOT EXISTS work_distribution_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            task_id INT NOT NULL,
            assigned_by BIGINT UNSIGNED NOT NULL,
            assigned_to BIGINT UNSIGNED NOT NULL,
            department VARCHAR(50) NOT NULL,
            assignment_type ENUM('auto','manual','rebalance','ai_suggested') DEFAULT 'manual',
            assignment_score DECIMAL(5,2),
            workload_before DECIMAL(5,2),
            workload_after DECIMAL(5,2),
            skill_match DECIMAL(5,2),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_wdl_task (task_id),
            KEY idx_wdl_assigned_by (assigned_by),
            KEY idx_wdl_assigned_to (assigned_to),
            KEY idx_wdl_dept (department),
            KEY idx_wdl_type (assignment_type),
            KEY idx_wdl_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "work_distribution_logs: CREATED/EXISTS\n";

    // 6. Task Rebalancing Logs
    $db->execute("
        CREATE TABLE IF NOT EXISTS task_rebalancing_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            task_id INT NOT NULL,
            from_employee BIGINT UNSIGNED NOT NULL,
            to_employee BIGINT UNSIGNED NOT NULL,
            reason ENUM('overload','skill_match','priority','leave','rebalance','manual') DEFAULT 'rebalance',
            initiated_by BIGINT UNSIGNED,
            workload_before_from DECIMAL(5,2),
            workload_after_from DECIMAL(5,2),
            workload_before_to DECIMAL(5,2),
            workload_after_to DECIMAL(5,2),
            approved_by BIGINT UNSIGNED,
            approved_at DATETIME,
            status ENUM('pending','approved','rejected','executed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            executed_at DATETIME,
            KEY idx_trl_task (task_id),
            KEY idx_trl_from (from_employee),
            KEY idx_trl_to (to_employee),
            KEY idx_trl_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "task_rebalancing_logs: CREATED/EXISTS\n";

    // 7. Work Distribution Config
    $db->execute("
        CREATE TABLE IF NOT EXISTS work_distribution_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            department VARCHAR(50) NOT NULL,
            auto_assign_enabled BOOLEAN DEFAULT TRUE,
            assignment_algorithm ENUM('round_robin','least_loaded','skill_based','performance_based','hybrid') DEFAULT 'hybrid',
            skill_weight DECIMAL(3,2) DEFAULT 0.40,
            workload_weight DECIMAL(3,2) DEFAULT 0.40,
            performance_weight DECIMAL(3,2) DEFAULT 0.20,
            max_tasks_per_employee INT DEFAULT 10,
            rebalance_threshold DECIMAL(5,2) DEFAULT 80.00,
            rebalance_interval_hours INT DEFAULT 4,
            notify_on_assignment BOOLEAN DEFAULT TRUE,
            notify_on_rebalance BOOLEAN DEFAULT TRUE,
            ai_assignment_enabled BOOLEAN DEFAULT FALSE,
            ai_model VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_config_dept (tenant_id, department)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "work_distribution_config: CREATED/EXISTS\n";

    // 8. Employee Performance
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_performance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            period_type ENUM('daily','weekly','monthly','quarterly','yearly') DEFAULT 'monthly',
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            tasks_assigned INT DEFAULT 0,
            tasks_completed INT DEFAULT 0,
            tasks_on_time INT DEFAULT 0,
            tasks_overdue INT DEFAULT 0,
            avg_completion_time_hours DECIMAL(8,2),
            quality_score DECIMAL(5,2),
            productivity_score DECIMAL(5,2),
            attendance_score DECIMAL(5,2),
            overall_score DECIMAL(5,2),
            reviewer_id BIGINT UNSIGNED,
            review_notes TEXT,
            reviewed_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_emp_perf_period (tenant_id, employee_id, period_type, period_start),
            KEY idx_emp_perf_emp (employee_id),
            KEY idx_emp_perf_period (period_type, period_start, period_end)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_performance: CREATED/EXISTS\n";

    // 8b. Employee Performance Reviews (detailed)
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_performance_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            reviewer_id BIGINT UNSIGNED NOT NULL,
            review_period_start DATE NOT NULL,
            review_period_end DATE NOT NULL,
            overall_rating DECIMAL(3,2),
            quality_rating DECIMAL(3,2),
            productivity_rating DECIMAL(3,2),
            communication_rating DECIMAL(3,2),
            teamwork_rating DECIMAL(3,2),
            initiative_rating DECIMAL(3,2),
            strengths TEXT,
            areas_for_improvement TEXT,
            goals TEXT,
            employee_comments TEXT,
            status ENUM('draft','submitted','acknowledged') DEFAULT 'draft',
            reviewed_at DATETIME,
            acknowledged_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_epr_emp (employee_id),
            KEY idx_epr_reviewer (reviewer_id),
            KEY idx_epr_period (review_period_start, review_period_end)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_performance_reviews: CREATED/EXISTS\n";

    // 9. AI Task Assignment Logs
    $db->execute("
        CREATE TABLE IF NOT EXISTS ai_task_assignment_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            task_id INT NOT NULL,
            ai_model VARCHAR(100),
            recommended_employee BIGINT UNSIGNED,
            recommendation_reason TEXT,
            confidence_score DECIMAL(5,2),
            factors_considered JSON,
            accepted BOOLEAN,
            accepted_by BIGINT UNSIGNED,
            accepted_at DATETIME,
            manual_override BOOLEAN DEFAULT FALSE,
            override_reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_atal_task (task_id),
            KEY idx_atal_emp (recommended_employee),
            KEY idx_atal_accepted (accepted)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "ai_task_assignment_logs: CREATED/EXISTS\n";

    // 10. Employee Notifications Preferences (extends notifications)
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_notification_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            task_assigned_email BOOLEAN DEFAULT TRUE,
            task_assigned_push BOOLEAN DEFAULT TRUE,
            task_reassigned_email BOOLEAN DEFAULT TRUE,
            task_reassigned_push BOOLEAN DEFAULT TRUE,
            task_overdue_email BOOLEAN DEFAULT TRUE,
            task_overdue_push BOOLEAN DEFAULT TRUE,
            task_completed_email BOOLEAN DEFAULT FALSE,
            task_completed_push BOOLEAN DEFAULT FALSE,
            leave_approved_email BOOLEAN DEFAULT TRUE,
            leave_approved_push BOOLEAN DEFAULT TRUE,
            performance_review_email BOOLEAN DEFAULT TRUE,
            performance_review_push BOOLEAN DEFAULT TRUE,
            daily_summary_email BOOLEAN DEFAULT FALSE,
            weekly_summary_email BOOLEAN DEFAULT TRUE,
            quiet_hours_start TIME DEFAULT '22:00:00',
            quiet_hours_end TIME DEFAULT '08:00:00',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_emp_notif_pref (tenant_id, employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_notification_preferences: CREATED/EXISTS\n";

    // 11. Task Dependencies (for complex workflows)
    $db->execute("
        CREATE TABLE IF NOT EXISTS task_dependencies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            task_id INT NOT NULL,
            depends_on_task_id INT NOT NULL,
            dependency_type ENUM('blocks','relates_to','duplicates','parent_of') DEFAULT 'blocks',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_task_dep (tenant_id, task_id, depends_on_task_id),
            KEY idx_td_task (task_id),
            KEY idx_td_dep (depends_on_task_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "task_dependencies: CREATED/EXISTS\n";

    // 12. Task Templates (for recurring/standardized work)
    $db->execute("
        CREATE TABLE IF NOT EXISTS task_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            department VARCHAR(50),
            default_priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
            estimated_hours DECIMAL(6,2),
            required_skills JSON,
            default_assignee_role VARCHAR(100),
            is_recurring BOOLEAN DEFAULT FALSE,
            recurrence_pattern ENUM('daily','weekly','monthly','quarterly','yearly'),
            recurrence_day INT,
            auto_assign BOOLEAN DEFAULT TRUE,
            created_by BIGINT UNSIGNED,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_tt_dept (department),
            KEY idx_tt_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "task_templates: CREATED/EXISTS\n";

    // 13. Employee Availability (for scheduling)
    $db->execute("
        CREATE TABLE IF NOT EXISTS employee_availability (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id BIGINT UNSIGNED NOT NULL,
            day_of_week TINYINT NOT NULL, -- 0=Sunday, 6=Saturday
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            is_available BOOLEAN DEFAULT TRUE,
            reason VARCHAR(200),
            valid_from DATE,
            valid_until DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_emp_avail (tenant_id, employee_id, day_of_week),
            KEY idx_ea_emp (employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "employee_availability: CREATED/EXISTS\n";

    // 14. Seed default skills
    $defaultSkills = [
        ['CRM & Lead Management', 'sales', 'Managing customer relationships and leads'],
        ['Property Sales', 'sales', 'Real estate property sales and closings'],
        ['Client Relationship', 'sales', 'Building and maintaining client relationships'],
        ['Negotiation', 'sales', 'Negotiation and deal closing skills'],
        ['Market Analysis', 'sales', 'Real estate market research and analysis'],
        ['Legal Documentation', 'legal', 'Property legal documents and compliance'],
        ['Contract Review', 'legal', 'Reviewing and drafting contracts'],
        ['RERA Compliance', 'legal', 'RERA registration and compliance'],
        ['Land Acquisition', 'land', 'Land acquisition and due diligence'],
        ['Property Valuation', 'land', 'Property appraisal and valuation'],
        ['Survey & Demarcation', 'land', 'Land survey and boundary demarcation'],
        ['Financial Analysis', 'finance', 'Financial modeling and analysis'],
        ['Tax Planning', 'finance', 'Tax optimization and compliance'],
        ['GST Compliance', 'finance', 'GST returns and compliance'],
        ['Payroll Processing', 'hr', 'Payroll management and processing'],
        ['Recruitment', 'hr', 'Talent acquisition and hiring'],
        ['Employee Relations', 'hr', 'Employee engagement and conflict resolution'],
        ['Training & Development', 'hr', 'Employee training and skill development'],
        ['Telecalling', 'telecalling', 'Outbound/inbound calling and lead qualification'],
        ['CRM Data Entry', 'telecalling', 'CRM data management and lead tracking'],
        ['Lead Qualification', 'telecalling', 'Qualifying and scoring leads'],
        ['Appointment Setting', 'telecalling', 'Scheduling client appointments'],
        ['Customer Support', 'support', 'Customer service and issue resolution'],
        ['Technical Support', 'it', 'IT systems and technical troubleshooting'],
        ['Software Development', 'it', 'Application development and maintenance'],
        ['Database Management', 'it', 'Database administration and optimization'],
        ['Network Administration', 'it', 'Network infrastructure management'],
        ['Digital Marketing', 'marketing', 'Online marketing and campaigns'],
        ['Content Creation', 'marketing', 'Content writing and creation'],
        ['Social Media Management', 'marketing', 'Social media strategy and execution'],
        ['SEO/SEM', 'marketing', 'Search engine optimization and marketing'],
    ];

    foreach ($defaultSkills as $skill) {
        $db->execute(
            "INSERT IGNORE INTO skills (tenant_id, name, category, description) VALUES (?, ?, ?, ?)",
            [1, $skill[0], $skill[1], $skill[2]]
        );
    }
    echo "skills: SEEDED with " . count($defaultSkills) . " default skills\n";

    // 15. Seed default work distribution config per department
    $departments = $db->fetchAll("SELECT code FROM departments WHERE status = 'active'");
    foreach ($departments as $dept) {
        $db->execute("
            INSERT IGNORE INTO work_distribution_config (tenant_id, department) VALUES (?, ?)
        ", [1, $dept['code']]);
    }
    echo "work_distribution_config: SEEDED for " . count($departments) . " departments\n";

    // 16. Initialize employee workload defaults
    $employees = $db->fetchAll("SELECT user_id as employee_id FROM employees WHERE status = 'active'");
    foreach ($employees as $emp) {
        $db->execute("
            INSERT IGNORE INTO employee_workload (tenant_id, employee_id) VALUES (?, ?)
        ", [1, $emp['employee_id']]);
    }
    echo "employee_workload: INITIALIZED for " . count($employees) . " employees\n";

    echo "\n=== ALL MIGRATIONS COMPLETED SUCCESSFULLY ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}