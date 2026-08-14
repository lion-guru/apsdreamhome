<?php
/**
 * Seed Feature Tables - Part 2: Communication, CRM, Finance, HR, Content
 * Seeds remaining empty feature tables with realistic data
 * Schemas extracted via SHOW CREATE TABLE to ensure column accuracy
 */

$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$db->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "=== SEEDING FEATURE TABLES - PART 2 ===\n\n";
$total = 0;

function isEmpty($db, $table) {
    $stmt = $db->query("SELECT COUNT(*) as c FROM `$table`");
    return $stmt->fetch()['c'] == 0;
}

// ==============================
// COMPANIES
// ==============================
if (isEmpty($db, 'companies')) {
    $db->exec("INSERT INTO companies (name, address, gstin, pan) VALUES
        ('APS Dream Home', 'Gorakhpur, Uttar Pradesh', '09ABCDE1234F1Z5', 'ABCDP1234E')
    ");
    echo "  companies: 1 row\n"; $total++;
}

// ==============================
// BUILDERS
// ==============================
if (isEmpty($db, 'builders')) {
    $db->exec("INSERT INTO builders (name, email, mobile, address, license_number, experience_years, specialization, rating, total_projects, completed_projects, ongoing_projects, status, bank_account, ifsc_code, pan_number, gst_number) VALUES
        ('APS Dream Home', 'info@apsdreamhome.com', '9876543210', 'Gorakhpur, UP', 'RERA-UP-BLD-001', 15, 'residential', 4.5, 5, 3, 2, 'active', '12345678901', 'SBIN0001234', 'ABCDP1234E', '09ABCDE1234F1Z5'),
        ('Suryoday Builders', 'info@suryoday.com', '8765432109', 'Lucknow, UP', 'RERA-UP-BLD-002', 10, 'residential', 4.2, 3, 2, 1, 'active', '9876543210', 'HDFC0005678', 'EFGHP5678F', '09FGHIJ5678K2L0')
    ");
    echo "  builders: 2 rows\n"; $total++;
}

// ==============================
// BUILDER DETAILS (FK: user_id)
// ==============================
if (isEmpty($db, 'builder_details')) {
    $stmt = $db->query("SELECT id FROM users WHERE role='admin' LIMIT 1");
    $uid = $stmt->fetch()['id'] ?? 1;
    $db->exec("INSERT INTO builder_details (user_id, company_name, rera_registration, office_address) VALUES
        ($uid, 'APS Dream Home Developers Pvt Ltd', 'RERA-UP-2020-00123', 'Gorakhpur, Uttar Pradesh')
    ");
    echo "  builder_details: 1 row\n"; $total++;
}

// ==============================
// INVESTOR DETAILS (FK: user_id)
// ==============================
if (isEmpty($db, 'investor_details')) {
    $db->exec("INSERT INTO investor_details (user_id, investment_range, investment_type, preferred_locations) VALUES
        (5, '5-10 Lakhs', 'plot_investment', 'Gorakhpur, Lucknow'),
        (8, '2-5 Lakhs', 'fixed_deposit', 'Gorakhpur'),
        (77, '10-25 Lakhs', 'project_investment', 'Gorakhpur, Varanasi')
    ");
    echo "  investor_details: 3 rows\n"; $total++;
}

// ==============================
// SOCIAL ACCOUNTS (FK: user_id)
// ==============================
if (isEmpty($db, 'social_accounts')) {
    $db->exec("INSERT INTO social_accounts (user_id, provider, provider_id, provider_name, provider_avatar, access_token, refresh_token, expires_at, created_at) VALUES
        (5, 'google', 'google_uid_001', 'Rahul Kumar', NULL, 'mock_at_1', NULL, DATE_ADD(NOW(), INTERVAL 360 DAY), NOW() - INTERVAL 30 DAY),
        (8, 'facebook', 'fb_uid_001', 'Priya Singh', NULL, 'mock_at_2', NULL, DATE_ADD(NOW(), INTERVAL 360 DAY), NOW() - INTERVAL 15 DAY),
        (12, 'google', 'google_uid_002', 'Amit Verma', NULL, 'mock_at_3', NULL, DATE_ADD(NOW(), INTERVAL 360 DAY), NOW() - INTERVAL 7 DAY)
    ");
    echo "  social_accounts: 3 rows\n"; $total++;
}

// ==============================
// TEAM
// ==============================
if (isEmpty($db, 'team')) {
    $db->exec("INSERT INTO team (name, designation, bio, status) VALUES
        ('Sales Team - Gorakhpur', 'Sales Team', 'Handles property sales in Gorakhpur region', 'active'),
        ('Telecalling Team', 'Telecalling Team', 'Handles inbound/outbound calls for lead qualification', 'active'),
        ('Operations Team', 'Operations', 'Site management and customer support', 'active')
    ");
    echo "  team: 3 rows\n"; $total++;
}

// ==============================
// WORK SCHEDULES (FK: employee_id)
// ==============================
if (isEmpty($db, 'work_schedules')) {
    $emps = $db->query("SELECT id FROM employees LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    if (count($emps) >= 2) {
        $db->exec("INSERT INTO work_schedules (employee_id, shift_start, shift_end, work_days, is_active) VALUES
            ({$emps[0]}, '09:00:00', '18:00:00', 'Mon,Tue,Wed,Thu,Fri', 1),
            ({$emps[0]}, '10:00:00', '19:00:00', 'Mon,Tue,Wed,Thu,Fri', 0),
            ({$emps[1]}, '14:00:00', '23:00:00', 'Mon,Tue,Wed,Thu,Fri', 1)
        ");
        echo "  work_schedules: 3 rows\n"; $total++;
    }
}

// ==============================
// SALARIES (FK: employee_id)
// ==============================
if (isEmpty($db, 'salaries')) {
    $emps = $db->query("SELECT id FROM employees LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);
    if (count($emps) >= 2) {
        $vals = [];
        $rates = [65000, 47500, 85000, 28000];
        foreach ($emps as $i => $eid) {
            $amt = $rates[$i] ?? 50000;
            $vals[] = "($eid, 5, 2026, $amt, 'paid', '2026-05-01')";
        }
        $db->exec("INSERT INTO salaries (employee_id, month, year, amount, status, paid_on) VALUES " . implode(',', $vals));
        echo "  salaries: " . count($vals) . " rows\n"; $total++;
    }
}

// ==============================
// SALARY RECORDS (FK: employee_id)
// ==============================
if (isEmpty($db, 'salary_records')) {
    $emps = $db->query("SELECT id FROM employees LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);
    if (count($emps) >= 2) {
        $vals = [];
        $bases = [35000, 25000, 45000, 15000];
        foreach ($emps as $i => $eid) {
            $base = $bases[$i] ?? 25000;
            $allow = round($base * 0.5);
            $deduc = round($base * 0.12);
            $net = $base + $allow - $deduc;
            $vals[] = "($eid, $base, $allow, $deduc, $net, '2026-05-01', 'paid')";
        }
        $db->exec("INSERT INTO salary_records (employee_id, base_salary, allowances, deductions, net_salary, payment_date, status) VALUES " . implode(',', $vals));
        echo "  salary_records: " . count($vals) . " rows\n"; $total++;
    }
}

// ==============================
// SALARY TRACKER (FK: user_id)
// ==============================
if (isEmpty($db, 'salary_tracker')) {
    $db->exec("INSERT INTO salary_tracker (user_id, target_volume, achieved_in_days, achieved_date, monthly_payout, duration_months, start_date, end_date, status) VALUES
        (1, 5000000, 25, NOW() - INTERVAL 5 DAY, 65000, 12, '2026-04-01', '2027-03-31', 'active'),
        (2, 3000000, 20, NOW() - INTERVAL 10 DAY, 47500, 12, '2026-04-01', '2027-03-31', 'active')
    ");
    echo "  salary_tracker: 2 rows\n"; $total++;
}

// ==============================
// CALLING SCRIPTS
// ==============================
if (isEmpty($db, 'calling_scripts')) {
    $db->exec("INSERT INTO calling_scripts (title, content, category, is_active) VALUES
        ('Lead Follow-up Script', 'Hello [NAME], this is [CALLER] from APS Dream Home. You inquired about [PROPERTY]. Would you like to know more?', 'lead_followup', 1),
        ('Site Visit Confirmation', 'Hello [NAME], this is [CALLER] from APS Dream Home. Your site visit for [PROPERTY] on [DATE] at [TIME] is confirmed.', 'site_visit', 1),
        ('Payment Reminder', 'Hello [NAME], this is [CALLER] from APS Dream Home Accounts. EMI of [AMOUNT] is due on [DATE].', 'payment', 1),
        ('Festival Greeting', 'Hello [NAME], Happy [FESTIVAL] from APS Dream Home! Wishing you prosperity in your new home.', 'greeting', 1)
    ");
    echo "  calling_scripts: 4 rows\n"; $total++;
}

// ==============================
// TELE-CALLER DAILY TASKS (FK: user_id)
// ==============================
if (isEmpty($db, 'telecaller_daily_tasks')) {
    $db->exec("INSERT INTO telecaller_daily_tasks (user_id, task_date, total_leads_assigned, calls_made, calls_connected, leads_converted, leads_callback, leads_not_interested, pending_calls, target_calls, notes) VALUES
        (2, CURDATE() - INTERVAL 1 DAY, 15, 45, 38, 5, 3, 2, 7, 50, 'Good day. 5 leads converted, 3 callbacks scheduled.'),
        (2, CURDATE() - INTERVAL 2 DAY, 12, 52, 42, 3, 5, 4, 10, 50, 'Average. Follow up on 5 callback leads.'),
        (4, CURDATE() - INTERVAL 1 DAY, 8, 30, 25, 2, 2, 1, 5, 35, 'Part-time shift. Good conversion rate.')
    ");
    echo "  telecaller_daily_tasks: 3 rows\n"; $total++;
}

// ==============================
// TELE-CALLER PERFORMANCE
// ==============================
if (isEmpty($db, 'telecaller_performance')) {
    $db->exec("INSERT INTO telecaller_performance (telecaller_id, period_start, period_end, total_calls, connected_calls, leads_converted, total_commission, target_achieved, rating) VALUES
        (2, '2026-04-01', '2026-04-30', 450, 380, 45, 22500.00, 90, 'excellent'),
        (4, '2026-04-01', '2026-04-30', 280, 220, 18, 9000.00, 65, 'good'),
        (2, '2026-05-01', '2026-05-28', 520, 440, 52, 26000.00, 95, 'excellent')
    ");
    echo "  telecaller_performance: 3 rows\n"; $total++;
}

// ==============================
// EMAIL QUEUE
// ==============================
if (isEmpty($db, 'email_queue')) {
    $db->exec("INSERT INTO email_queue (queue_id, to_email, to_name, from_email, from_name, subject, body_html, body_text, priority, status, attempts, max_attempts, scheduled_at, sent_at, created_at) VALUES
        (UUID(), 'rahul.kumar@gmail.com', 'Rahul Kumar', 'noreply@apsdreamhome.com', 'APS Dream Home', 'Property Registration Complete', '<h1>Congratulations!</h1><p>Registration for Plot B-7 is complete.</p>', 'Registration for Plot B-7 is complete.', 'high', 'sent', 1, 3, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 3 DAY),
        (UUID(), 'sunita.devi@gmail.com', 'Sunita Devi', 'noreply@apsdreamhome.com', 'APS Dream Home', 'Site Visit Confirmed', '<h1>Confirmed</h1><p>Site visit for Suryoday Heights on 15 May at 10 AM.</p>', 'Site visit confirmed.', 'normal', 'sent', 1, 3, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 2 DAY),
        (UUID(), 'amit.verma@gmail.com', 'Amit Verma', 'accounts@apsdreamhome.com', 'APS Dream Home Accounts', 'Payment Receipt - EMI #4', '<h1>Payment Received</h1><p>EMI #4 of Rs.45,000 received.</p>', 'EMI #4 payment received.', 'high', 'pending', 0, 3, NOW(), NULL, NOW())
    ");
    echo "  email_queue: 3 rows\n"; $total++;
}

// ==============================
// SMS QUEUE
// ==============================
if (isEmpty($db, 'sms_queue')) {
    $db->exec("INSERT INTO sms_queue (recipient, message, status, provider, attempts, scheduled_at, sent_at, created_at) VALUES
        ('+919988776655', 'Property registration for Plot B-7 is complete. - APS Dream Home', 'sent', 'twilio', 1, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),
        ('+918877665544', 'Reminder: Site visit tomorrow at 10 AM. Contact 9277121112. - APS Dream Home', 'sent', 'twilio', 1, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY),
        ('+917766554433', 'EMI #4 payment of Rs.45,000 received. - APS Dream Home', 'pending', 'twilio', 0, NOW(), NULL, NOW())
    ");
    echo "  sms_queue: 3 rows\n"; $total++;
}

// ==============================
// WHATSAPP MESSAGES
// ==============================
if (isEmpty($db, 'whatsapp_messages')) {
    $db->exec("INSERT INTO whatsapp_messages (phone_number, message, direction, message_type, status, created_at) VALUES
        ('919988776655', 'Your property documents are ready for collection.', 'outbound', 'text', 'read', NOW() - INTERVAL 2 DAY),
        ('919277121112', 'Thank you! When can I collect?', 'inbound', 'text', 'read', NOW() - INTERVAL 2 DAY),
        ('918877665544', 'Check out Suryoday Heights! Starting from Rs.15 Lakh.', 'outbound', 'text', 'delivered', NOW() - INTERVAL 1 DAY)
    ");
    echo "  whatsapp_messages: 3 rows\n"; $total++;
}

// ==============================
// WHATSAPP CAMPAIGNS
// ==============================
if (isEmpty($db, 'whatsapp_campaigns')) {
    $db->exec("INSERT INTO whatsapp_campaigns (template_id, campaign_name, recipients, sent_count, delivered_count, read_count, status, scheduled_at, created_by, created_at) VALUES
        (1, 'Diwali Greetings 2026', '{\"user_types\":[\"customer\",\"associate\"]}', 0, 0, 0, 'draft', '2026-10-31 09:00:00', 1, NOW()),
        (2, 'Suryoday Phase 2 Launch', '{\"user_types\":[\"customer\",\"lead\"],\"cities\":[\"Gorakhpur\"]}', 0, 0, 0, 'scheduled', '2026-06-15 10:00:00', 1, NOW())
    ");
    echo "  whatsapp_campaigns: 2 rows\n"; $total++;
}

// ==============================
// WHATSAPP AUTOMATION CONFIG
// ==============================
if (isEmpty($db, 'whatsapp_automation_config')) {
    $db->exec("INSERT INTO whatsapp_automation_config (provider, api_key, sender_number, created_at) VALUES
        ('twilio', 'mock_key_twilio', '+919277121112', NOW())
    ");
    echo "  whatsapp_automation_config: 1 row\n"; $total++;
}

// ==============================
// NOTIFICATION QUEUE
// ==============================
if (isEmpty($db, 'notification_queue')) {
    $db->exec("INSERT INTO notification_queue (notification_id, user_id, user_type, channel, template_key, title, message, priority, status, scheduled_at, sent_at, retry_count, max_retries, created_at) VALUES
        (UUID(), 5, 'customer', 'email', 'booking_confirmed', 'Booking Confirmed', 'Site visit confirmed for 15 May at 10 AM.', 'high', 'sent', NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, 0, 3, NOW() - INTERVAL 3 DAY),
        (UUID(), 5, 'customer', 'sms', 'payment_reminder', 'Payment Reminder', 'EMI #5 of Rs.45,000 due on 1 June.', 'normal', 'pending', NOW() + INTERVAL 3 DAY, NULL, 0, 3, NOW() - INTERVAL 1 DAY),
        (UUID(), 8, 'customer', 'push', 'property_approved', 'Property Approved', 'Your listing has been approved.', 'high', 'sent', NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY, 0, 3, NOW() - INTERVAL 5 DAY),
        (UUID(), 1, 'admin', 'in_app', 'new_lead', 'New Lead Captured', 'New lead from website chatbot.', 'normal', 'sent', NOW() - INTERVAL 6 HOUR, NOW() - INTERVAL 6 HOUR, 0, 3, NOW() - INTERVAL 6 HOUR)
    ");
    echo "  notification_queue: 4 rows\n"; $total++;
}

// ==============================
// NOTIFICATION FEED
// ==============================
if (isEmpty($db, 'notification_feed')) {
    $db->exec("INSERT INTO notification_feed (notification_id, user_id, type, title, message, is_read, action_url, created_at) VALUES
        (UUID(), 5, 'welcome', 'Welcome to APS Dream Home!', 'Start exploring our properties.', 1, '/properties', NOW() - INTERVAL 60 DAY),
        (UUID(), 5, 'booking', 'Site Visit Confirmed', 'Suryoday Heights on 15 May at 10 AM.', 1, '/user/bookings', NOW() - INTERVAL 2 DAY),
        (UUID(), 5, 'reminder', 'EMI Due Reminder', 'EMI #5 of Rs.45,000 due on 1 June.', 0, '/user/bookings', NOW() - INTERVAL 1 DAY),
        (UUID(), 8, 'success', 'Property Approved', 'Your listing has been approved!', 1, '/user/properties', NOW() - INTERVAL 5 DAY),
        (UUID(), 1, 'lead', 'New Lead from Chatbot', 'Rajesh interested in 3BHK.', 0, '/admin/leads', NOW() - INTERVAL 6 HOUR),
        (UUID(), 77, 'success', 'Referral Bonus Received', 'You earned Rs.2,500!', 1, '/associate/dashboard', NOW() - INTERVAL 3 DAY)
    ");
    echo "  notification_feed: 6 rows\n"; $total++;
}

// ==============================
// NOTIFICATION CAMPAIGNS
// ==============================
if (isEmpty($db, 'notification_campaigns')) {
    $db->exec("INSERT INTO notification_campaigns (campaign_name, campaign_type, target_audience, template_key, channels, status, total_recipients, sent_count, delivered_count, opened_count, created_by, created_at) VALUES
        ('Summer Sale 2026', 'email', '{\"user_types\":[\"customer\",\"lead\"]}', 'summer_sale', '[\"email\"]', 'completed', 150, 150, 145, 45, 1, NOW() - INTERVAL 60 DAY),
        ('Suryoday Phase 2 Launch', 'push', '{\"cities\":[\"Gorakhpur\"]}', 'new_launch', '[\"push\",\"email\"]', 'scheduled', 0, 0, 0, 0, 1, NOW()),
        ('Diwali Greetings', 'sms', '{\"all_users\":true}', 'festival_greeting', '[\"sms\"]', 'draft', 0, 0, 0, 0, 1, NOW())
    ");
    echo "  notification_campaigns: 3 rows\n"; $total++;
}

// ==============================
// PIPELINE ACTIVITIES
// ==============================
if (isEmpty($db, 'pipeline_activities')) {
    $db->exec("INSERT INTO pipeline_activities (lead_id, activity_type, activity_title, activity_description, activity_date, duration_minutes, outcome, performed_by, assigned_to, is_completed, completed_at, created_at) VALUES
        (1, 'call', 'Initial Contact', 'Discussed property requirements', '2026-04-20', 15, 'positive', 1, 1, 1, '2026-04-20', NOW() - INTERVAL 40 DAY),
        (1, 'meeting', 'Site Visit', 'Showed 3 properties at Suryoday Heights', '2026-04-25', 60, 'positive', 1, 1, 1, '2026-04-25', NOW() - INTERVAL 35 DAY),
        (1, 'email', 'Proposal Sent', 'Sent detailed proposal with payment plan', '2026-05-01', 10, 'neutral', 1, 1, 1, '2026-05-01', NOW() - INTERVAL 30 DAY),
        (2, 'meeting', 'Consultation Meeting', 'Client visited office for consultation', '2026-05-10', 45, 'positive', 1, 1, 1, '2026-05-10', NOW() - INTERVAL 20 DAY),
        (2, 'call', 'Follow-up', 'Called with commercial property options', '2026-05-15', 10, 'neutral', 1, 1, 1, '2026-05-15', NOW() - INTERVAL 15 DAY)
    ");
    echo "  pipeline_activities: 5 rows\n"; $total++;
}

// ==============================
// PIPELINE FILTERS
// ==============================
if (isEmpty($db, 'pipeline_filters')) {
    $db->exec("INSERT INTO pipeline_filters (user_id, filter_name, filter_criteria, is_default, is_shared) VALUES
        (1, 'Hot Leads', '{\"probability_min\":70,\"stages\":[\"negotiation\",\"proposal\"]}', 1, 0),
        (1, 'New This Week', '{\"created_within_days\":7}', 0, 0),
        (2, 'My Active Deals', '{\"assigned_to_me\":true,\"stages\":[\"new\",\"contacted\",\"qualified\"]}', 1, 0)
    ");
    echo "  pipeline_filters: 3 rows\n"; $total++;
}

// ==============================
// FORECAST RESULTS
// ==============================
if (isEmpty($db, 'forecast_results')) {
    $db->exec("INSERT INTO forecast_results (forecast_date, forecast_period, forecast_value, confidence_interval_lower, confidence_interval_upper, actual_value, forecast_type, created_at) VALUES
        ('2026-01-01', '{\"start\":\"2026-04-01\",\"end\":\"2026-06-30\"}', 2500000.00, 2100000.00, 2900000.00, 0.00, 'revenue', NOW() - INTERVAL 150 DAY),
        ('2026-01-01', '{\"start\":\"2026-04-01\",\"end\":\"2026-06-30\"}', 15, 12, 18, 0, 'sales_volume', NOW() - INTERVAL 150 DAY),
        ('2026-04-01', '{\"start\":\"2026-07-01\",\"end\":\"2026-09-30\"}', 3200000.00, 2800000.00, 3600000.00, 0.00, 'revenue', NOW() - INTERVAL 60 DAY)
    ");
    echo "  forecast_results: 3 rows\n"; $total++;
}

// ==============================
// LEGAL PAGES
// ==============================
if (isEmpty($db, 'legal_pages')) {
    $db->exec("INSERT INTO legal_pages (page_type, title, content, updated_by) VALUES
        ('privacy', 'Privacy Policy', 'This Privacy Policy describes how APS Dream Home collects, uses, and protects your personal information when you use our website and services.', 1),
        ('terms', 'Terms of Service', 'By accessing and using the APS Dream Home website and services, you agree to these terms. All property listings are for informational purposes.', 1)
    ");
    echo "  legal_pages: 2 rows\n"; $total++;
}

// ==============================
// PROPERTY FEATURE MAP
// ==============================
if (isEmpty($db, 'property_feature_map')) {
    $db->exec("INSERT INTO property_feature_map (property_id, feature_id) VALUES
        (1, 1), (1, 2), (1, 3),
        (2, 1), (2, 4), (2, 5),
        (3, 1), (3, 3), (3, 6)
    ");
    echo "  property_feature_map: 9 rows\n"; $total++;
}

// ==============================
// PURCHASE INVOICE ITEMS
// ==============================
if (isEmpty($db, 'purchase_invoice_items')) {
    $inv = $db->query("SELECT id FROM purchase_invoices LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    if (count($inv) >= 3) {
        $db->exec("INSERT INTO purchase_invoice_items (invoice_id, item_name, description, quantity, unit_price, total_price, tax_rate, tax_amount) VALUES
            ({$inv[0]}, 'Steel TMT Bars 12mm', 'Construction grade steel', 50, 6500.00, 325000.00, 18.00, 58500.00),
            ({$inv[0]}, 'Cement OPC 53 Grade', 'UltraTech cement', 200, 380.00, 76000.00, 18.00, 13680.00),
            ({$inv[1]}, 'PVC Electrical Wires', 'Fire retardant wiring 1.5sqmm', 100, 850.00, 85000.00, 12.00, 10200.00),
            ({$inv[1]}, 'MCB Distribution Box', '16-way distribution box', 10, 3500.00, 35000.00, 12.00, 4200.00),
            ({$inv[2]}, 'PVC Pipes 4 inch', 'Drainage pipes', 80, 450.00, 36000.00, 12.00, 4320.00)
        ");
        echo "  purchase_invoice_items: 5 rows\n"; $total++;
    }
}

// ==============================
// SALES INVOICE ITEMS
// ==============================
if (isEmpty($db, 'sales_invoice_items')) {
    $inv = $db->query("SELECT id FROM invoices LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    if (count($inv) >= 2) {
        $db->exec("INSERT INTO sales_invoice_items (invoice_id, item_name, description, quantity, unit_price, total_price, tax_rate, tax_amount) VALUES
            ({$inv[0]}, 'Plot A-12 Sale', 'Residential plot Suryoday Heights', 1, 1650000.00, 1650000.00, 18.00, 297000.00),
            ({$inv[0]}, 'Registration Fee', 'Property registration service', 1, 25000.00, 25000.00, 18.00, 4500.00),
            ({$inv[1]}, '3BHK Flat - Tower C', 'Premium apartment Suryoday Heights', 1, 4500000.00, 4500000.00, 18.00, 810000.00)
        ");
        echo "  sales_invoice_items: 3 rows\n"; $total++;
    }
}

// ==============================
// TAX REMINDERS
// ==============================
if (isEmpty($db, 'tax_reminders')) {
    $db->exec("INSERT INTO tax_reminders (tax_type_id, title, description, due_date, status) VALUES
        (1, 'GST Return Filing', 'File GSTR-9 for FY 2025-26', '2026-06-20', 'pending'),
        (2, 'TDS Return Filing', 'Quarterly TDS return for Q1', '2026-07-07', 'pending'),
        (3, 'Income Tax Filing', 'File ITR for FY 2025-26', '2026-07-31', 'pending')
    ");
    echo "  tax_reminders: 3 rows\n"; $total++;
}

// ==============================
// CUSTOMER BEHAVIOR ANALYSIS
// ==============================
if (isEmpty($db, 'customer_behavior_analysis')) {
    $db->exec("INSERT INTO customer_behavior_analysis (customer_id, analysis_date, behavioral_data, patterns, segmentation, predictions, insights, recommendations) VALUES
        (5, NOW() - INTERVAL 1 DAY, '{\"page_views\":45,\"avg_session\":320,\"property_views\":12,\"inquiries\":3,\"site_visits\":2}', '{\"active_days\":[\"Mon\",\"Sat\"],\"peak_hours\":[10,20]}', '\"high_value\"', '{\"purchase_probability\":0.85,\"expected_value\":1650000}', '\"Interested in plots under 20L. Prefers Gorakhpur.\"', '\"Show Suryoday Heights plots. Offer site visit.\"'),
        (8, NOW() - INTERVAL 3 DAY, '{\"page_views\":28,\"avg_session\":180,\"property_views\":8,\"inquiries\":1,\"site_visits\":1}', '{\"active_days\":[\"Wed\"],\"peak_hours\":[15]}', '\"medium_value\"', '{\"purchase_probability\":0.45,\"expected_value\":500000}', '\"Looking for flats under 50L.\"', '\"Show flat options in Braj Radha Enclave.\"')
    ");
    echo "  customer_behavior_analysis: 2 rows\n"; $total++;
}

// ==============================
// CUSTOMER JOURNEYS
// ==============================
if (isEmpty($db, 'customer_journeys')) {
    $db->exec("INSERT INTO customer_journeys (customer_id, journey, started_at, last_touch_at) VALUES
        (5, '{\"stages\":[{\"stage\":\"awareness\",\"source\":\"google\"},{\"stage\":\"consideration\"},{\"stage\":\"purchase\"}]}', '2026-03-01 10:00:00', NOW() - INTERVAL 2 DAY),
        (8, '{\"stages\":[{\"stage\":\"awareness\",\"source\":\"facebook\"},{\"stage\":\"consideration\"}]}', '2026-04-15 14:00:00', NOW() - INTERVAL 5 DAY)
    ");
    echo "  customer_journeys: 2 rows\n"; $total++;
}

// ==============================
// FILE TAGS
// ==============================
if (isEmpty($db, 'file_tags')) {
    $db->exec("INSERT INTO file_tags (name, color) VALUES
        ('Property Document', '#007bff'),
        ('Legal', '#dc3545'),
        ('Financial', '#28a745'),
        ('Customer', '#ffc107'),
        ('Internal', '#6c757d')
    ");
    echo "  file_tags: 5 rows\n"; $total++;
}

// ==============================
// FILE UPLOADS
// ==============================
if (isEmpty($db, 'file_uploads')) {
    $db->exec("INSERT INTO file_uploads (original_name, file_name, file_path, file_size, mime_type, uploaded_by, upload_category) VALUES
        ('Sale_Deed_Plot_B7.pdf', 'sale_deed_001.pdf', '/uploads/documents/', 524288, 'application/pdf', 1, 'document'),
        ('Property_Photos.zip', 'photos_suryoday.zip', '/uploads/documents/', 15728640, 'application/zip', 1, 'image'),
        ('Agreement_Rahul.pdf', 'agreement_rahul.pdf', '/uploads/documents/', 262144, 'application/pdf', 1, 'document'),
        ('Tax_Returns_2025.pdf', 'tax_returns_2025.pdf', '/uploads/documents/', 1048576, 'application/pdf', 1, 'document')
    ");
    echo "  file_uploads: 4 rows\n"; $total++;
}

// ==============================
// FILE TAG RELATIONS
// ==============================
if (isEmpty($db, 'file_tag_relations')) {
    $db->exec("INSERT INTO file_tag_relations (file_id, tag_id) VALUES
        (1, 1), (1, 2),
        (3, 1), (3, 4),
        (4, 3)
    ");
    echo "  file_tag_relations: 5 rows\n"; $total++;
}

// ==============================
// FILE VERSIONS
// ==============================
if (isEmpty($db, 'file_versions')) {
    $db->exec("INSERT INTO file_versions (file_id, version_number, file_name, file_path, size_bytes, created_by, change_notes, created_at) VALUES
        (1, 1, 'sale_deed_001_v1.pdf', '/uploads/documents/', 524288, 1, 'Original version', NOW() - INTERVAL 30 DAY),
        (1, 2, 'sale_deed_001_v2.pdf', '/uploads/documents/', 548864, 1, 'Updated with corrections', NOW() - INTERVAL 28 DAY)
    ");
    echo "  file_versions: 2 rows\n"; $total++;
}

// ==============================
// FILE SHARES
// ==============================
if (isEmpty($db, 'file_shares')) {
    $db->exec("INSERT INTO file_shares (file_id, shared_by, shared_with_email, shared_with_user_id, share_token, permissions, access_count, is_active, created_at) VALUES
        (1, 1, 'rahul.kumar@gmail.com', 5, 'tok_001', 'view', 3, 1, NOW() - INTERVAL 20 DAY),
        (3, 1, 'rahul.kumar@gmail.com', 5, 'tok_002', 'download', 1, 1, NOW() - INTERVAL 18 DAY)
    ");
    echo "  file_shares: 2 rows\n"; $total++;
}

// ==============================
// ASSOCIATE ACHIEVEMENTS
// ==============================
if (isEmpty($db, 'associate_achievements')) {
    $db->exec("INSERT INTO associate_achievements (associate_id, achievement_id, achieved_date, achievement_data) VALUES
        (77, 1, '2026-02-15 10:00:00', '{\"type\":\"first_sale\",\"amount\":1650000,\"property\":\"Plot B-7\"}'),
        (77, 2, '2026-03-20 14:30:00', '{\"type\":\"team_building\",\"team_size\":5}'),
        (78, 1, '2026-04-10 11:00:00', '{\"type\":\"first_sale\",\"amount\":950000,\"property\":\"Plot A-12\"}'),
        (79, 3, '2026-05-01 16:00:00', '{\"type\":\"milestone\",\"total_commission\":1050000}')
    ");
    echo "  associate_achievements: 4 rows\n"; $total++;
}

// ==============================
// LOYALTY TRANSACTIONS
// ==============================
if (isEmpty($db, 'loyalty_transactions')) {
    $db->exec("INSERT INTO loyalty_transactions (user_id, user_type, points, transaction_type, reference_type, reference_id, description, created_at) VALUES
        (5, 'customer', 500, 'credit', 'registration', 5, 'Welcome bonus', NOW() - INTERVAL 60 DAY),
        (5, 'customer', 200, 'credit', 'purchase', 1, 'Purchase points', NOW() - INTERVAL 30 DAY),
        (5, 'customer', 100, 'credit', 'referral', 1, 'Referral bonus', NOW() - INTERVAL 15 DAY),
        (5, 'customer', 50, 'debit', 'reward', 1, 'Voucher redemption', NOW() - INTERVAL 10 DAY),
        (8, 'customer', 500, 'credit', 'registration', 8, 'Welcome bonus', NOW() - INTERVAL 45 DAY),
        (77, 'associate', 1000, 'credit', 'commission', 1, 'Commission points', NOW() - INTERVAL 30 DAY)
    ");
    echo "  loyalty_transactions: 6 rows\n"; $total++;
}

// ==============================
// POINTS TRANSACTIONS
// ==============================
if (isEmpty($db, 'points_transactions')) {
    $db->exec("INSERT INTO points_transactions (user_id, user_type, transaction_type, points_amount, reference_type, reference_id, description, created_by, created_at) VALUES
        (5, 'customer', 'credit', 500, 'registration', 5, 'Welcome bonus', 1, NOW() - INTERVAL 60 DAY),
        (5, 'customer', 'credit', 200, 'purchase', 1, 'Purchase points', 1, NOW() - INTERVAL 30 DAY),
        (5, 'customer', 'debit', 50, 'reward', 1, 'Redemption', 1, NOW() - INTERVAL 10 DAY),
        (77, 'associate', 'credit', 1000, 'commission', 1, 'Commission points', 1, NOW() - INTERVAL 30 DAY)
    ");
    echo "  points_transactions: 4 rows\n"; $total++;
}

// ==============================
// REWARD REDEMPTIONS
// ==============================
if (isEmpty($db, 'reward_redemptions')) {
    $db->exec("INSERT INTO reward_redemptions (user_id, user_type, reward_id, points_spent, redemption_date, status, notes) VALUES
        (5, 'customer', 1, 500, NOW() - INTERVAL 10 DAY, 'completed', 'Amazon Gift Voucher Rs.500'),
        (5, 'customer', 2, 200, NOW() - INTERVAL 2 DAY, 'pending', 'Document discount'),
        (77, 'associate', 3, 500, NOW() - INTERVAL 20 DAY, 'completed', 'Cash withdrawal')
    ");
    echo "  reward_redemptions: 3 rows\n"; $total++;
}

// ==============================
// WALLET EMI TRANSFERS
// ==============================
if (isEmpty($db, 'wallet_emi_transfers')) {
    $db->exec("INSERT INTO wallet_emi_transfers (user_id, emi_id, emi_amount, wallet_amount_used, transaction_id, transfer_status, transferred_at, created_at) VALUES
        (5, 1, 45000.00, 25000.00, 'TXN-WET-001', 'completed', '2026-04-01 10:00:00', NOW() - INTERVAL 60 DAY),
        (5, 2, 45000.00, 25000.00, 'TXN-WET-002', 'completed', '2026-05-01 10:00:00', NOW() - INTERVAL 30 DAY)
    ");
    echo "  wallet_emi_transfers: 2 rows\n"; $total++;
}

// ==============================
// WORKFLOW ACTIONS
// ==============================
if (isEmpty($db, 'workflow_actions')) {
    $db->exec("INSERT INTO workflow_actions (instance_id, step_id, action_type, action_by, action_by_type, comments, created_at) VALUES
        (1, 1, 'send_email', 1, 'system', 'Sent welcome email', NOW() - INTERVAL 7 DAY),
        (1, 2, 'assign_lead', 1, 'system', 'Assigned to sales team', NOW() - INTERVAL 7 DAY),
        (3, 1, 'send_sms', 1, 'system', 'Sent visit confirmation SMS', NOW() - INTERVAL 2 DAY)
    ");
    echo "  workflow_actions: 3 rows\n"; $total++;
}

// ==============================
// WORKFLOW INSTANCES
// ==============================
if (isEmpty($db, 'workflow_instances')) {
    $db->exec("INSERT INTO workflow_instances (workflow_id, entity_type, entity_id, current_step, status, requested_by, requested_at, completed_at, created_at) VALUES
        (1, 'lead', 15, 2, 'completed', 1, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 8 DAY),
        (1, 'lead', 16, 1, 'in_progress', 1, NOW() - INTERVAL 2 DAY, NULL, NOW() - INTERVAL 3 DAY),
        (2, 'booking', 1, 1, 'completed', 1, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 3 DAY)
    ");
    echo "  workflow_instances: 3 rows\n"; $total++;
}

// ==============================
// TASK DEPENDENCIES
// ==============================
if (isEmpty($db, 'task_dependencies')) {
    $db->exec("INSERT INTO task_dependencies (task_id, depends_on_task_id, created_at) VALUES
        (2, 1, NOW() - INTERVAL 30 DAY),
        (3, 2, NOW() - INTERVAL 25 DAY)
    ");
    echo "  task_dependencies: 2 rows\n"; $total++;
}

// ==============================
// TASK QUEUE
// ==============================
if (isEmpty($db, 'task_queue')) {
    $db->exec("INSERT INTO task_queue (task_id, queue_name, created_at) VALUES
        (1, 'default', NOW() - INTERVAL 7 DAY),
        (2, 'email', NOW() - INTERVAL 7 DAY),
        (3, 'default', NOW() - INTERVAL 2 DAY),
        (4, 'reports', NOW())
    ");
    echo "  task_queue: 4 rows\n"; $total++;
}

// ==============================
// MODULE PROGRESS
// ==============================
if (isEmpty($db, 'module_progress')) {
    $db->exec("INSERT INTO module_progress (enrollment_id, module_id, started_at, completed_at, time_spent_minutes, progress_percentage, score_percentage, attempts_count, last_attempt_at, status) VALUES
        (1, 1, NOW() - INTERVAL 90 DAY, NOW() - INTERVAL 60 DAY, 480, 100, 92, 1, NOW() - INTERVAL 60 DAY, 'completed'),
        (1, 2, NOW() - INTERVAL 60 DAY, NULL, 240, 80, NULL, 0, NOW() - INTERVAL 10 DAY, 'in_progress'),
        (1, 3, NOW() - INTERVAL 45 DAY, NULL, 180, 60, NULL, 0, NOW() - INTERVAL 5 DAY, 'in_progress'),
        (2, 1, NOW() - INTERVAL 60 DAY, NOW() - INTERVAL 30 DAY, 360, 100, 88, 2, NOW() - INTERVAL 30 DAY, 'completed')
    ");
    echo "  module_progress: 4 rows\n"; $total++;
}

// ==============================
// PERFORMANCE METRICS
// ==============================
if (isEmpty($db, 'performance_metrics')) {
    $db->exec("INSERT INTO performance_metrics (metric_name, metric_value, metric_unit, threshold, status, timestamp) VALUES
        ('page_load_time', 1.2, 'seconds', 2.0, 'healthy', NOW() - INTERVAL 1 HOUR),
        ('api_response_time', 0.45, 'seconds', 1.0, 'healthy', NOW() - INTERVAL 1 HOUR),
        ('db_query_time', 0.08, 'seconds', 0.2, 'healthy', NOW() - INTERVAL 1 HOUR),
        ('memory_usage', 64, 'MB', 256, 'healthy', NOW() - INTERVAL 1 HOUR),
        ('cpu_usage', 35, 'percent', 80, 'healthy', NOW() - INTERVAL 1 HOUR),
        ('active_users', 12, 'count', 100, 'healthy', NOW() - INTERVAL 1 HOUR)
    ");
    echo "  performance_metrics: 6 rows\n"; $total++;
}

// ==============================
// PERFORMANCE ANALYTICS
// ==============================
if (isEmpty($db, 'performance_analytics')) {
    $db->exec("INSERT INTO performance_analytics (metric_name, metric_value, metric_type, timestamp) VALUES
        ('homepage_load', 1.1, 'page_load', NOW() - INTERVAL 1 HOUR),
        ('properties_load', 1.5, 'page_load', NOW() - INTERVAL 1 HOUR),
        ('admin_dashboard_load', 2.3, 'page_load', NOW() - INTERVAL 1 HOUR),
        ('lead_capture_api', 0.35, 'api', NOW() - INTERVAL 1 HOUR),
        ('property_search_api', 0.52, 'api', NOW() - INTERVAL 1 HOUR)
    ");
    echo "  performance_analytics: 5 rows\n"; $total++;
}

// ==============================
// PERFORMANCE BENCHMARKS
// ==============================
if (isEmpty($db, 'performance_benchmarks')) {
    $db->exec("INSERT INTO performance_benchmarks (endpoint, method, benchmark_response_time, benchmark_rps, acceptable_response_time, acceptable_rps, last_updated) VALUES
        ('/api/properties', 'GET', 0.5, 100, 1.0, 200, NOW()),
        ('/api/leads', 'POST', 0.3, 50, 0.8, 100, NOW()),
        ('/api/bookings', 'GET', 0.4, 80, 0.9, 150, NOW())
    ");
    echo "  performance_benchmarks: 3 rows\n"; $total++;
}

// ==============================
// DAILY METRICS SUMMARY
// ==============================
if (isEmpty($db, 'daily_metrics_summary')) {
    $db->exec("INSERT INTO daily_metrics_summary (date, total_users, new_users, active_users, total_properties, new_properties, total_leads, new_leads, total_revenue, monthly_revenue, paid_invoices, created_at) VALUES
        (CURDATE() - INTERVAL 6 DAY, 145, 3, 98, 45, 2, 12, 8, 500000.00, 2500000.00, 3, NOW() - INTERVAL 6 DAY),
        (CURDATE() - INTERVAL 5 DAY, 132, 2, 85, 44, 1, 11, 6, 350000.00, 2350000.00, 2, NOW() - INTERVAL 5 DAY),
        (CURDATE() - INTERVAL 4 DAY, 158, 4, 105, 47, 3, 14, 10, 750000.00, 2700000.00, 4, NOW() - INTERVAL 4 DAY),
        (CURDATE() - INTERVAL 3 DAY, 120, 1, 78, 48, 1, 10, 5, 250000.00, 2450000.00, 1, NOW() - INTERVAL 3 DAY),
        (CURDATE() - INTERVAL 2 DAY, 165, 5, 110, 49, 2, 16, 12, 850000.00, 2850000.00, 5, NOW() - INTERVAL 2 DAY),
        (CURDATE() - INTERVAL 1 DAY, 148, 3, 92, 50, 0, 13, 7, 400000.00, 2600000.00, 2, NOW() - INTERVAL 1 DAY)
    ");
    echo "  daily_metrics_summary: 6 rows\n"; $total++;
}

// ==============================
// NETWORK ANALYTICS
// ==============================
if (isEmpty($db, 'network_analytics')) {
    $db->exec("INSERT INTO network_analytics (user_id, user_type, network_level, direct_recruits, total_network_size, active_members, inactive_members, network_growth_rate, average_commission_per_member, total_network_commission, analytics_date, calculated_at) VALUES
        (77, 'associate', 3, 5, 12, 10, 2, 15.5, 25000.00, 300000.00, CURDATE(), NOW()),
        (78, 'associate', 2, 2, 5, 4, 1, 8.2, 18000.00, 90000.00, CURDATE(), NOW()),
        (79, 'associate', 4, 8, 18, 15, 3, 22.0, 35000.00, 525000.00, CURDATE(), NOW())
    ");
    echo "  network_analytics: 3 rows\n"; $total++;
}

// ==============================
// CAMPAIGN MEMBERS
// ==============================
if (isEmpty($db, 'campaign_members')) {
    $db->exec("INSERT INTO campaign_members (member_id, campaign_id, lead_id, status, created_at) VALUES
        (1, 1, 1, 'sent', NOW() - INTERVAL 60 DAY),
        (2, 1, 2, 'opened', NOW() - INTERVAL 60 DAY),
        (3, 2, 3, 'sent', NOW() - INTERVAL 30 DAY)
    ");
    echo "  campaign_members: 3 rows\n"; $total++;
}

// ==============================
// ROLE CHANGE APPROVALS
// ==============================
if (isEmpty($db, 'role_change_approvals')) {
    $db->exec("INSERT INTO role_change_approvals (user_id, role_id, action, requested_by, status, requested_at, decided_by, decided_at) VALUES
        (64, 2, 'promotion', 1, 'approved', NOW() - INTERVAL 35 DAY, 1, NOW() - INTERVAL 30 DAY),
        (54, 3, 'upgrade', 1, 'pending', NOW() - INTERVAL 5 DAY, NULL, NULL)
    ");
    echo "  role_change_approvals: 2 rows\n"; $total++;
}

// ==============================
// CONVERSATION PARTICIPANTS
// ==============================
if (isEmpty($db, 'conversation_participants')) {
    $db->exec("INSERT INTO conversation_participants (conversation_id, user_id, user_type, role, is_active, joined_at, last_seen_at, unread_count, is_muted) VALUES
        (1, 5, 'customer', 'member', 1, NOW() - INTERVAL 30 DAY, NOW() - INTERVAL 1 DAY, 2, 0),
        (1, 1, 'admin', 'agent', 1, NOW() - INTERVAL 30 DAY, NOW() - INTERVAL 1 DAY, 0, 0),
        (2, 8, 'customer', 'member', 1, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 3 DAY, 1, 0),
        (2, 1, 'admin', 'agent', 1, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 3 DAY, 0, 0),
        (3, 77, 'associate', 'member', 1, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 5 DAY, 0, 0),
        (3, 1, 'admin', 'agent', 1, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 5 DAY, 0, 0)
    ");
    echo "  conversation_participants: 6 rows\n"; $total++;
}

// ==============================
// CUSTOMERS LEDGER
// ==============================
if (isEmpty($db, 'customers_ledger')) {
    $db->exec("INSERT INTO customers_ledger (customer_id, customer_name, mobile, email, opening_balance, current_balance, total_sales, total_payments, status, created_at) VALUES
        (5, 'Rajesh Kumar', '9988776655', 'rajesh@email.com', 0.00, -5000.00, 450000.00, 445000.00, 'active', NOW() - INTERVAL 60 DAY),
        (8, 'Sunita Devi', '8877665544', 'sunita@email.com', 0.00, 0.00, 350000.00, 350000.00, 'active', NOW() - INTERVAL 45 DAY)
    ");
    echo "  customers_ledger: 2 rows\n"; $total++;
}

// ==============================
// SUMMARY
// ==============================
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== SEEDING COMPLETE ===\n";
echo "Total tables seeded: {$total}\n";?>