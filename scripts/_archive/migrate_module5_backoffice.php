<?php
/**
 * Module 5 Migration: Backoffice + Daily Operations
 * Creates 8 tables + seeds 8 report definitions
 * Run: php scripts/migrate_module5_backoffice.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "Connected to database.\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 1. employee_attendance
    $pdo->exec("DROP TABLE IF EXISTS `employee_attendance`");
    $pdo->exec("CREATE TABLE `employee_attendance` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id` INT(11) NOT NULL,
        `attendance_date` DATE NOT NULL,
        `status` ENUM('present','absent','half_day','on_leave','work_from_home') NOT NULL DEFAULT 'present',
        `check_in_time` DATETIME DEFAULT NULL,
        `check_out_time` DATETIME DEFAULT NULL,
        `hours_worked` DECIMAL(4,2) DEFAULT 0.00,
        `overtime_hours` DECIMAL(4,2) DEFAULT 0.00,
        `late_minutes` INT(11) DEFAULT 0,
        `remarks` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_emp_date` (`employee_id`, `attendance_date`),
        KEY `idx_att_date_status` (`attendance_date`, `status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] employee_attendance\n";

    // 2. employee_leave_requests
    $pdo->exec("DROP TABLE IF EXISTS `employee_leave_requests`");
    $pdo->exec("CREATE TABLE `employee_leave_requests` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id` INT(11) NOT NULL,
        `leave_type` ENUM('casual','sick','earned','maternity','paternity','unpaid','compensatory') NOT NULL DEFAULT 'casual',
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `total_days` DECIMAL(4,1) NOT NULL,
        `reason` TEXT DEFAULT NULL,
        `approved_by` INT(11) DEFAULT NULL,
        `approval_date` DATE DEFAULT NULL,
        `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
        `remarks` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_leave_emp_status` (`employee_id`, `status`),
        KEY `idx_leave_dates` (`start_date`, `end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] employee_leave_requests\n";

    // 3. employee_payslips
    $pdo->exec("DROP TABLE IF EXISTS `employee_payslips`");
    $pdo->exec("CREATE TABLE `employee_payslips` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id` INT(11) NOT NULL,
        `period_month` INT(2) NOT NULL,
        `period_year` INT(4) NOT NULL,
        `basic_salary` DECIMAL(12,2) DEFAULT 0.00,
        `hra` DECIMAL(12,2) DEFAULT 0.00,
        `allowances` DECIMAL(12,2) DEFAULT 0.00,
        `deductions` DECIMAL(12,2) DEFAULT 0.00,
        `tds` DECIMAL(12,2) DEFAULT 0.00,
        `pf` DECIMAL(12,2) DEFAULT 0.00,
        `esi` DECIMAL(12,2) DEFAULT 0.00,
        `professional_tax` DECIMAL(12,2) DEFAULT 0.00,
        `net_salary` DECIMAL(12,2) DEFAULT 0.00,
        `days_present` INT(2) DEFAULT 0,
        `lop_days` INT(2) DEFAULT 0,
        `status` ENUM('draft','approved','paid') NOT NULL DEFAULT 'draft',
        `paid_date` DATE DEFAULT NULL,
        `payment_mode` ENUM('bank_transfer','cheque','cash') DEFAULT NULL,
        `transaction_ref` VARCHAR(100) DEFAULT NULL,
        `generated_by` INT(11) DEFAULT NULL,
        `paid_by` INT(11) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_emp_period` (`employee_id`, `period_month`, `period_year`),
        KEY `idx_payslip_period` (`period_year`, `period_month`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] employee_payslips\n";

    // 4. lead_pipeline
    $pdo->exec("DROP TABLE IF EXISTS `lead_pipeline`");
    $pdo->exec("CREATE TABLE `lead_pipeline` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `lead_number` VARCHAR(30) NOT NULL,
        `lead_name` VARCHAR(200) NOT NULL,
        `lead_source` ENUM('walk_in','referral','website','phone','social_media','agent','advertisement','portal','cold_call','other') DEFAULT 'other',
        `lead_type` ENUM('buyer','seller','tenant','landlord','investor') DEFAULT 'buyer',
        `contact_name` VARCHAR(200) DEFAULT NULL,
        `contact_phone` VARCHAR(20) DEFAULT NULL,
        `contact_email` VARCHAR(200) DEFAULT NULL,
        `property_type` VARCHAR(50) DEFAULT NULL,
        `budget_min` DECIMAL(15,2) DEFAULT NULL,
        `budget_max` DECIMAL(15,2) DEFAULT NULL,
        `preferred_location` TEXT DEFAULT NULL,
        `requirement_details` TEXT DEFAULT NULL,
        `assigned_to` INT(11) DEFAULT NULL,
        `follow_up_date` DATE DEFAULT NULL,
        `follow_up_count` INT(11) DEFAULT 0,
        `priority` ENUM('hot','warm','cold','dead') DEFAULT 'warm',
        `score` INT(11) DEFAULT 50,
        `status` ENUM('new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold') DEFAULT 'new',
        `created_by` INT(11) DEFAULT NULL,
        `closed_date` DATE DEFAULT NULL,
        `closure_notes` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_lead_number` (`lead_number`),
        KEY `idx_lead_type_status` (`lead_type`, `status`),
        KEY `idx_lead_assigned_followup` (`assigned_to`, `follow_up_date`),
        KEY `idx_lead_score` (`score` DESC),
        KEY `idx_lead_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] lead_pipeline\n";

    // 5. lead_pipeline_activities
    $pdo->exec("DROP TABLE IF EXISTS `lead_pipeline_activities`");
    $pdo->exec("CREATE TABLE `lead_pipeline_activities` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `lead_id` INT(11) NOT NULL,
        `activity_type` ENUM('call','sms','whatsapp','email','visit','meeting','note','status_change') NOT NULL DEFAULT 'note',
        `subject` VARCHAR(200) DEFAULT NULL,
        `description` TEXT DEFAULT NULL,
        `activity_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `next_follow_up` DATE DEFAULT NULL,
        `outcome` VARCHAR(200) DEFAULT NULL,
        `created_by` INT(11) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_activity_lead_date` (`lead_id`, `activity_date` DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] lead_pipeline_activities\n";

    // 6. daily_operations_log
    $pdo->exec("DROP TABLE IF EXISTS `daily_operations_log`");
    $pdo->exec("CREATE TABLE `daily_operations_log` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `log_date` DATE NOT NULL,
        `log_type` ENUM('site_visit','client_meeting','collection','payment_received','cheque_collected','document_submission','registry','mutation','legal_update','construction_update','other') NOT NULL DEFAULT 'other',
        `colony_id` INT(11) DEFAULT NULL,
        `plot_id` INT(11) DEFAULT NULL,
        `description` TEXT DEFAULT NULL,
        `amount` DECIMAL(15,2) DEFAULT NULL,
        `party_name` VARCHAR(200) DEFAULT NULL,
        `party_type` ENUM('customer','vendor','land_owner','employee','government','other') DEFAULT 'other',
        `status` ENUM('completed','in_progress','pending','cancelled') DEFAULT 'pending',
        `priority` ENUM('high','medium','low') DEFAULT 'medium',
        `assigned_to` INT(11) DEFAULT NULL,
        `completed_at` DATETIME DEFAULT NULL,
        `notes` TEXT DEFAULT NULL,
        `created_by` INT(11) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_ops_date_type` (`log_date`, `log_type`),
        KEY `idx_ops_colony_date` (`colony_id`, `log_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] daily_operations_log\n";

    // 7. report_definitions
    $pdo->exec("DROP TABLE IF EXISTS `report_definitions`");
    $pdo->exec("CREATE TABLE `report_definitions` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `report_name` VARCHAR(200) NOT NULL,
        `report_type` ENUM('sales','collection','tds','gst','commission','booking','payment','lead','employee','operations','custom') NOT NULL,
        `sql_template` TEXT NOT NULL,
        `parameters` JSON DEFAULT NULL,
        `schedule_cron` VARCHAR(100) DEFAULT NULL,
        `last_run_at` DATETIME DEFAULT NULL,
        `format` ENUM('html','pdf','csv','excel') DEFAULT 'html',
        `access_roles` JSON DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] report_definitions\n";

    // 8. report_executions
    $pdo->exec("DROP TABLE IF EXISTS `report_executions`");
    $pdo->exec("CREATE TABLE `report_executions` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `report_id` INT(11) NOT NULL,
        `executed_by` INT(11) DEFAULT NULL,
        `start_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `end_time` DATETIME DEFAULT NULL,
        `parameters_used` JSON DEFAULT NULL,
        `row_count` INT(11) DEFAULT 0,
        `file_path` VARCHAR(500) DEFAULT NULL,
        `status` ENUM('running','completed','failed','cancelled') DEFAULT 'running',
        `error_message` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_report_exec_id` (`report_id`, `created_at` DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [OK] report_executions\n";

    // Seed 8 report definitions
    $reports = [
        [
            'Daily Collection Report', 'collection',
            "SELECT pt.id, pt.amount, pt.payment_date, pt.payment_method, pt.reference_number,
                    u.name AS customer_name, pt.description
             FROM payment_transactions pt
             LEFT JOIN users u ON pt.user_id = u.id
             WHERE DATE(pt.payment_date) = CURDATE()
             ORDER BY pt.payment_date DESC",
            json_encode(['date' => 'Date (YYYY-MM-DD)']),
            null, 'csv', json_encode(['admin', 'manager'])
        ],
        [
            'Monthly Sales Report', 'sales',
            "SELECT pb.id, pb.booking_number, pb.total_amount, pb.status,
                    pb.booking_date, u.name AS customer_name, ip.plot_no,
                    c.name AS colony_name
             FROM plot_bookings pb
             LEFT JOIN users u ON pb.user_id = u.id
             LEFT JOIN inventory_plots ip ON pb.plot_id = ip.id
             LEFT JOIN colonies c ON ip.colony_id = c.id
             WHERE MONTH(pb.booking_date) = :month AND YEAR(pb.booking_date) = :year
             ORDER BY pb.booking_date DESC",
            json_encode(['month' => 'Month (1-12)', 'year' => 'Year']),
            null, 'csv', json_encode(['admin', 'manager', 'sales'])
        ],
        [
            'TDS TCS Summary', 'tds',
            "SELECT tds_section, COUNT(*) AS count, SUM(taxable_amount) AS total_taxable,
                    SUM(tds_amount) AS total_tds,
                    financial_year, quarter
             FROM tds_register
             WHERE financial_year = :fy AND quarter = :quarter
             GROUP BY tds_section, financial_year, quarter
             ORDER BY total_tds DESC",
            json_encode(['fy' => 'Financial Year (e.g. 2026-27)', 'quarter' => 'Quarter (1-4)']),
            null, 'html', json_encode(['admin', 'finance'])
        ],
        [
            'GST Summary', 'gst',
            "SELECT supply_type, COUNT(*) AS count,
                    SUM taxable_amount, SUM cgst + sgst AS total_cgst_sgst,
                    SUM igst AS total_igst
             FROM gst_transactions
             WHERE MONTH(transaction_date) = :month AND YEAR(transaction_date) = :year
             GROUP BY supply_type
             ORDER BY supply_type",
            json_encode(['month' => 'Month (1-12)', 'year' => 'Year']),
            null, 'html', json_encode(['admin', 'finance'])
        ],
        [
            'Commission Summary', 'commission',
            "SELECT beneficiary_user_id, commission_type, status,
                    COUNT(*) AS count, SUM(amount) AS total_amount,
                    DATE_FORMAT(created_at, '%Y-%m') AS month
             FROM mlm_commission_ledger
             WHERE DATE_FORMAT(created_at, '%Y-%m') = :ym
             GROUP BY beneficiary_user_id, commission_type, status
             ORDER BY total_amount DESC",
            json_encode(['ym' => 'Year-Month (YYYY-MM)']),
            null, 'csv', json_encode(['admin', 'manager'])
        ],
        [
            'Lead Funnel', 'lead',
            "SELECT status, COUNT(*) AS count,
                    AVG(score) AS avg_score,
                    MIN(created_at) AS oldest,
                    MAX(created_at) AS newest
             FROM lead_pipeline
             WHERE created_at >= :from_date
             GROUP BY status
             ORDER BY FIELD(status, 'new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold')",
            json_encode(['from_date' => 'From Date (YYYY-MM-DD)']),
            null, 'html', json_encode(['admin', 'manager', 'sales'])
        ],
        [
            'Employee Attendance', 'employee',
            "SELECT ea.employee_id, u.name AS employee_name, ea.attendance_date, ea.status,
                    ea.check_in_time, ea.check_out_time, ea.hours_worked,
                    ea.late_minutes, ea.overtime_hours
             FROM employee_attendance ea
             LEFT JOIN users u ON ea.employee_id = u.id
             WHERE MONTH(ea.attendance_date) = :month AND YEAR(ea.attendance_date) = :year
             ORDER BY ea.attendance_date, u.name",
            json_encode(['month' => 'Month (1-12)', 'year' => 'Year']),
            null, 'csv', json_encode(['admin', 'hr'])
        ],
        [
            'Cash Position', 'operations',
            "SELECT log_date, log_type, description, amount, party_name, status
             FROM daily_operations_log
             WHERE log_type IN ('collection','payment_received','cheque_collected')
               AND log_date = :log_date
             ORDER BY log_date DESC",
            json_encode(['log_date' => 'Date (YYYY-MM-DD)']),
            null, 'html', json_encode(['admin', 'finance'])
        ],
    ];

    $insertStmt = $pdo->prepare("INSERT INTO `report_definitions`
        (report_name, report_type, sql_template, parameters, schedule_cron, format, access_roles)
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($reports as $r) {
        $insertStmt->execute($r);
    }
    echo "  [OK] 8 report definitions seeded\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n=== Module 5 Migration Complete ===\n";
    echo "8 tables created + 8 report definitions seeded.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
