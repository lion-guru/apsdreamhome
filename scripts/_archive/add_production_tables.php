<?php
// Production ERP tables migration
// Run: C:\xampp\php\php.exe scripts\add_production_tables.php

$root = dirname(__DIR__);
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queries = [

// 1. user_login_history — Login audit trail
"CREATE TABLE IF NOT EXISTS user_login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    login_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    logout_time DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    device_type VARCHAR(50) NULL,
    location VARCHAR(255) NULL,
    status ENUM('success','failed','blocked') NOT NULL DEFAULT 'success',
    failure_reason VARCHAR(255) NULL,
    session_id VARCHAR(128) NULL,
    INDEX idx_ulh_user (user_id),
    INDEX idx_ulh_time (login_time),
    INDEX idx_ulh_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 2. customer_communication_log — Email/SMS/WhatsApp history per customer
"CREATE TABLE IF NOT EXISTS customer_communication_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    channel ENUM('email','sms','whatsapp','push','in_app') NOT NULL,
    direction ENUM('inbound','outbound') NOT NULL DEFAULT 'outbound',
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    status ENUM('sent','delivered','read','failed','pending') NOT NULL DEFAULT 'pending',
    template_id INT UNSIGNED NULL,
    related_entity_type VARCHAR(50) NULL,
    related_entity_id BIGINT UNSIGNED NULL,
    sent_at DATETIME NULL,
    delivered_at DATETIME NULL,
    read_at DATETIME NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ccl_user (user_id),
    INDEX idx_ccl_channel (channel),
    INDEX idx_ccl_status (status),
    INDEX idx_ccl_sent (sent_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 3. daily_cash_balance — End-of-day cash position per bank account
"CREATE TABLE IF NOT EXISTS daily_cash_balance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_account_id INT UNSIGNED NOT NULL,
    balance_date DATE NOT NULL,
    opening_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_receipts DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_payments DECIMAL(15,2) NOT NULL DEFAULT 0,
    closing_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    reconciled TINYINT(1) NOT NULL DEFAULT 0,
    reconciled_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_dcb_account_date (bank_account_id, balance_date),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 4. agreements — Signed agreement tracking
"CREATE TABLE IF NOT EXISTS agreements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agreement_number VARCHAR(50) NOT NULL UNIQUE,
    agreement_type ENUM('sale_deed','allotment','mortgage','lease','nda','joint_venture','other') NOT NULL,
    booking_id BIGINT UNSIGNED NULL,
    plot_id INT NULL,
    party_a_name VARCHAR(255) NOT NULL,
    party_a_id BIGINT UNSIGNED NULL,
    party_b_name VARCHAR(255) NOT NULL,
    party_b_id BIGINT UNSIGNED NULL,
    agreement_date DATE NOT NULL,
    registration_date DATE NULL,
    stamp_duty_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    registration_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    document_url VARCHAR(500) NULL,
    status ENUM('draft','pending_signature','signed','registered','cancelled','expired') NOT NULL DEFAULT 'draft',
    validity_date DATE NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_agreement_type (agreement_type),
    INDEX idx_agreement_status (status),
    INDEX idx_agreement_booking (booking_id),
    FOREIGN KEY (booking_id) REFERENCES plot_bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 5. court_cases — Legal dispute tracking
"CREATE TABLE IF NOT EXISTS court_cases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(100) NOT NULL,
    case_type ENUM('civil','criminal','consumer','tribunal','appellate','other') NOT NULL DEFAULT 'civil',
    court_name VARCHAR(255) NOT NULL,
    judge_name VARCHAR(255) NULL,
    plaintiff_name VARCHAR(255) NOT NULL,
    defendant_name VARCHAR(255) NOT NULL,
    property_id BIGINT UNSIGNED NULL,
    plot_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    filing_date DATE NOT NULL,
    next_hearing_date DATE NULL,
    expected_closure_date DATE NULL,
    amount_involved DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('filed','under_trial','hearing','verdict','appeal','settled','closed') NOT NULL DEFAULT 'filed',
    verdict TEXT NULL,
    lawyer_name VARCHAR(255) NULL,
    lawyer_contact VARCHAR(20) NULL,
    documents_url TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cc_status (status),
    INDEX idx_cc_next_hearing (next_hearing_date),
    INDEX idx_cc_property (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 6. material_inventory — Construction material stock
"CREATE TABLE IF NOT EXISTS material_inventory (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_name VARCHAR(255) NOT NULL,
    material_category ENUM('cement','steel','sand','bricks','tiles','paint','plumbing','electrical','wood','glass','other') NOT NULL,
    sku VARCHAR(100) NULL UNIQUE,
    unit VARCHAR(20) NOT NULL DEFAULT 'qty',
    current_stock DECIMAL(10,2) NOT NULL DEFAULT 0,
    minimum_stock DECIMAL(10,2) NOT NULL DEFAULT 0,
    maximum_stock DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_value DECIMAL(12,2) NOT NULL DEFAULT 0,
    location VARCHAR(255) NULL,
    supplier_name VARCHAR(255) NULL,
    supplier_contact VARCHAR(20) NULL,
    last_restocked_at DATETIME NULL,
    status ENUM('in_stock','low_stock','out_of_stock','discontinued') NOT NULL DEFAULT 'in_stock',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mi_category (material_category),
    INDEX idx_mi_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 7. vendor_materials — Material vendor directory
"CREATE TABLE IF NOT EXISTS vendor_materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    gst_number VARCHAR(20) NULL,
    pan_number VARCHAR(10) NULL,
    material_categories TEXT NULL,
    payment_terms VARCHAR(100) NULL,
    rating TINYINT(1) NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vm_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 8. construction_progress — Per-plot construction tracking
"CREATE TABLE IF NOT EXISTS construction_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plot_id INT NOT NULL,
    colony_id INT NOT NULL,
    stage ENUM('foundation','plinth','ground_floor','first_floor','second_floor','roofing','plastering','finishing','completed') NOT NULL DEFAULT 'foundation',
    progress_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    start_date DATE NULL,
    expected_completion DATE NULL,
    actual_completion DATE NULL,
    contractor_name VARCHAR(255) NULL,
    contractor_contact VARCHAR(20) NULL,
    estimated_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
    actual_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
    quality_score TINYINT(1) NULL,
    status ENUM('not_started','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'not_started',
    last_inspection_date DATE NULL,
    next_milestone VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cp_plot (plot_id),
    INDEX idx_cp_colony (colony_id),
    INDEX idx_cp_stage (stage),
    INDEX idx_cp_status (status),
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
    FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 9. site_visit_checklist — Pre/post visit checklist
"CREATE TABLE IF NOT EXISTS site_visit_checklist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visit_id BIGINT UNSIGNED NULL,
    checklist_type ENUM('pre_visit','post_visit') NOT NULL DEFAULT 'pre_visit',
    item_name VARCHAR(255) NOT NULL,
    is_completed TINYINT(1) NOT NULL DEFAULT 0,
    completed_at DATETIME NULL,
    completed_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_svc_visit (visit_id),
    INDEX idx_svc_type (checklist_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 10. recurring_transactions — Scheduled/recurring payment templates
"CREATE TABLE IF NOT EXISTS recurring_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_type ENUM('emi','rent','subscription','salary','vendor','utility','other') NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    vendor_id INT UNSIGNED NULL,
    bank_account_id INT UNSIGNED NULL,
    amount DECIMAL(12,2) NOT NULL,
    frequency ENUM('weekly','biweekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    next_execution_date DATE NOT NULL,
    last_execution_date DATE NULL,
    payment_method VARCHAR(50) NULL,
    reference_id VARCHAR(100) NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    execution_count INT NOT NULL DEFAULT 0,
    max_executions INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rt_next (next_execution_date),
    INDEX idx_rt_active (is_active),
    INDEX idx_rt_type (transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 11. employee_documents — Employee ID proof, offer letter, contracts
"CREATE TABLE IF NOT EXISTS employee_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM('offer_letter','appointment_letter','id_proof','address_proof','education_cert','experience_cert','salary_slip','increment_letter','relieving_letter','other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NULL,
    mime_type VARCHAR(100) NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ed_employee (employee_id),
    INDEX idx_ed_type (document_type),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 12. daily_sales_report — Auto-generated daily sales summary
"CREATE TABLE IF NOT EXISTS daily_sales_report (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_date DATE NOT NULL UNIQUE,
    total_new_leads INT NOT NULL DEFAULT 0,
    total_leads_contacted INT NOT NULL DEFAULT 0,
    total_leads_qualified INT NOT NULL DEFAULT 0,
    total_site_visits INT NOT NULL DEFAULT 0,
    total_new_bookings INT NOT NULL DEFAULT 0,
    total_booking_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_payments_received DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_cancellations INT NOT NULL DEFAULT 0,
    total_refunds DECIMAL(15,2) NOT NULL DEFAULT 0,
    conversion_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    avg_deal_size DECIMAL(15,2) NOT NULL DEFAULT 0,
    top_agent_id BIGINT UNSIGNED NULL,
    top_agent_name VARCHAR(255) NULL,
    notes TEXT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dsr_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 13. collection_report — Daily collection reconciliation
"CREATE TABLE IF NOT EXISTS collection_report (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_date DATE NOT NULL,
    bank_account_id INT UNSIGNED NOT NULL,
    opening_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    cash_collected DECIMAL(15,2) NOT NULL DEFAULT 0,
    cheque_received DECIMAL(15,2) NOT NULL DEFAULT 0,
    online_received DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_collection DECIMAL(15,2) NOT NULL DEFAULT 0,
    emi_collected DECIMAL(15,2) NOT NULL DEFAULT 0,
    down_payment_collected DECIMAL(15,2) NOT NULL DEFAULT 0,
    other_collected DECIMAL(15,2) NOT NULL DEFAULT 0,
    expenses_paid DECIMAL(15,2) NOT NULL DEFAULT 0,
    closing_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    is_reconciled TINYINT(1) NOT NULL DEFAULT 0,
    reconciled_at DATETIME NULL,
    reconciled_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cr_date_account (report_date, bank_account_id),
    INDEX idx_cr_date (report_date),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 14. user_sessions — Active session tracking
"CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_token VARCHAR(128) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    device_type VARCHAR(50) NULL,
    last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_us_user (user_id),
    INDEX idx_us_token (session_token),
    INDEX idx_us_active (is_active),
    INDEX idx_us_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 15. price_history — Track plot/property price changes
"CREATE TABLE IF NOT EXISTS price_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('plot','property','colony') NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    old_price DECIMAL(15,2) NULL,
    new_price DECIMAL(15,2) NOT NULL,
    change_reason VARCHAR(255) NULL,
    changed_by BIGINT UNSIGNED NULL,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ph_entity (entity_type, entity_id),
    INDEX idx_ph_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 16. project_documents — Master document repository
"CREATE TABLE IF NOT EXISTS project_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colony_id INT NULL,
    plot_id INT NULL,
    document_type ENUM('title_deed','sale_deed','allotment_letter','noc','rera_certificate','building_plan','layout_map','completion_certificate','occupation_certificate','other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_number VARCHAR(100) NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NULL,
    issued_date DATE NULL,
    expiry_date DATE NULL,
    issuing_authority VARCHAR(255) NULL,
    status ENUM('valid','expired','pending_renewal','revoked') NOT NULL DEFAULT 'valid',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pd_colony (colony_id),
    INDEX idx_pd_type (document_type),
    INDEX idx_pd_status (status),
    FOREIGN KEY (colony_id) REFERENCES colonies(id) ON DELETE SET NULL,
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"

];

$count = 0;
foreach ($queries as $q) {
    try {
        $pdo->exec($q);
        preg_match('/CREATE TABLE IF NOT EXISTS `(\w+)`/', $q, $m);
        echo "Created: {$m[1]}\n";
        $count++;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== $count tables created ===\n\n";

// === SEED DATA ===
echo "=== Seeding default data ===\n\n";

// 1. site_visit_checklist — 10 pre-visit items
$preVisitItems = [
    'Property photos captured and uploaded',
    'Layout map printed (2 copies)',
    'Price list ready with current rates',
    'Agreement draft prepared for review',
    'Customer ID proof verified (Aadhaar/PAN)',
    'Vehicle ready for site transport',
    'Site area cleaned and accessible',
    'Plot boundaries clearly marked',
    'Utilities (water/electricity) connected',
    'Access road clear and passable'
];

$ord = 1;
foreach ($preVisitItems as $item) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO site_visit_checklist (checklist_type, item_name, sort_order) VALUES ('pre_visit', ?, ?)");
    $stmt->execute([$item, $ord]);
    $ord++;
}
echo "Seeded: 10 pre-visit checklist items\n";

// 2. material_inventory — 10 common construction materials
$materials = [
    ['OPC 53 Grade Cement', 'cement', 'CEM-OPC53', 'bags', 500, 100, 1000, 380.00, 'Suryoday warehouse', 'UltraTech Cement', '9876543210'],
    ['TMT Steel Bars 12mm', 'steel', 'STL-TMT12', 'kg', 2000, 500, 5000, 62.00, 'Suryoday warehouse', 'Tata Tiscon', '9876543211'],
    ['River Sand (Fine)', 'sand', 'SND-RVR', 'cu.ft', 150, 50, 300, 45.00, 'Site storage', 'Local Supplier', '9876543212'],
    ['Red Clay Bricks', 'bricks', 'BRK-RED', 'pcs', 5000, 1000, 10000, 8.00, 'Site storage', 'Gorakhpur Bricks', '9876543213'],
    ['Ceramic Floor Tiles 2x2', 'tiles', 'TLS-CER22', 'sqft', 800, 200, 1500, 45.00, 'Suryoday warehouse', 'Kajaria Tiles', '9876543214'],
    ['Asian Paints Apex Ultima', 'paint', 'PNT-APEX', 'litres', 100, 20, 200, 520.00, 'Suryoday warehouse', 'Asian Paints', '9876543215'],
    ['PVC Pipes 4 inch', 'plumbing', 'PLB-PVC4', 'pcs', 200, 50, 400, 185.00, 'Site storage', 'Astral Pipes', '9876543216'],
    ['Electrical Wire 2.5 sqmm', 'electrical', 'ELC-W25', 'meter', 1000, 200, 2000, 28.00, 'Suryoday warehouse', 'Havells', '9876543217'],
    ['Tempered Glass 6mm', 'glass', 'GLS-TRM6', 'sqft', 150, 50, 300, 120.00, 'Site storage', 'Asahi India Glass', '9876543218'],
    ['Sagwan Wood Planks', 'wood', 'WD-SAG', 'cft', 80, 20, 150, 850.00, 'Suryoday warehouse', 'Local Timber', '9876543219']
];

foreach ($materials as $m) {
    $totalVal = $m[4] * $m[7]; // current_stock * unit_cost
    $status = $m[4] <= $m[5] ? 'low_stock' : 'in_stock';
    $stmt = $pdo->prepare("INSERT IGNORE INTO material_inventory 
        (material_name, material_category, sku, unit, current_stock, minimum_stock, maximum_stock, unit_cost, total_value, location, supplier_name, supplier_contact, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$m[0], $m[1], $m[2], $m[3], $m[4], $m[5], $m[6], $m[7], $totalVal, $m[8], $m[9], $m[10], $status]);
}
echo "Seeded: 10 material inventory items\n";

// 3. vendor_materials — 3 sample vendors
$vendors = [
    ['UltraTech Cement Depot', 'Rajesh Kumar', '9876543210', 'rajesh@ultratech.in', 'Station Road, Near Bus Stand', 'Gorakhpur', 'Uttar Pradesh', '273001', '09AABCU1234A1Z5', 'AABCU1234A', 'cement, plaster', 'Net 30 days', 4],
    ['Tata Tiscon Steel House', 'Amit Singh', '9876543211', 'amit@tatatiscon.com', 'Industrial Area, Phase 2', 'Gorakhpur', 'Uttar Pradesh', '273010', '09BBCTI5678B1Z5', 'BBCTI5678B', 'steel, tmt bars', 'Net 15 days', 5],
    ['Gorakhpur Hardware & Pipes', 'Suresh Verma', '9876543212', 'suresh@ghardware.in', 'Civil Lines, Main Market', 'Gorakhpur', 'Uttar Pradesh', '273001', '09CCCGH9012C1Z5', 'CCCGH9012C', 'plumbing, electrical, tiles', 'Cash on delivery', 3]
];

foreach ($vendors as $v) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO vendor_materials 
        (vendor_name, contact_person, phone, email, address, city, state, pincode, gst_number, pan_number, material_categories, payment_terms, rating)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute($v);
}
echo "Seeded: 3 vendor materials\n";

// 4. user_login_history — 20 recent login entries
$users = $pdo->query("SELECT id, name FROM users ORDER BY id LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$devices = ['Windows Desktop', 'Android Phone', 'iPhone', 'MacBook', 'Linux Desktop'];
$ips = ['192.168.1.10', '192.168.1.20', '10.0.0.5', '172.16.0.1', '127.0.0.1'];

$loginCount = 0;
foreach ($users as $u) {
    for ($i = 0; $i < 2; $i++) {
        $daysAgo = rand(0, 7);
        $hoursAgo = rand(0, 23);
        $loginTime = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days -{$hoursAgo} hours"));
        $device = $devices[array_rand($devices)];
        $ip = $ips[array_rand($ips)];
        $status = (rand(1, 10) > 1) ? 'success' : 'failed';
        $sessionId = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("INSERT IGNORE INTO user_login_history 
            (user_id, login_time, ip_address, user_agent, device_type, status, session_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$u['id'], $loginTime, $ip, "Mozilla/5.0 ($device)", $device, $status, $sessionId]);
        $loginCount++;
    }
}
echo "Seeded: $loginCount login history entries\n";

echo "\n=== Migration complete ===\n";
