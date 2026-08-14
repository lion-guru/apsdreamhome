<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$results = [];

// 1. Notification templates (template_code, channel, subject, body, variables)
$templates = [
    ['welcome', 'email', 'Welcome to APS Dream Home!', '<h1>Welcome {{name}}!</h1><p>Thank you for joining APS Dream Home. Your account is now active.</p>', ['name']],
    ['booking_confirmed', 'email', 'Your Booking is Confirmed', '<h1>Booking Confirmed</h1><p>Dear {{customer}}, your booking #{{booking_id}} for {{property}} is confirmed. Total: â‚¹{{amount}}.</p>', ['customer', 'booking_id', 'property', 'amount']],
    ['payment_received', 'email', 'Payment Received', '<p>Payment of â‚¹{{amount}} received for booking {{booking_id}}. Receipt: {{receipt_no}}.</p>', ['amount', 'booking_id', 'receipt_no']],
    ['lead_assigned', 'email', 'New Lead Assigned', '<p>You have been assigned a new lead: {{lead_name}}. Contact: {{phone}}</p>', ['lead_name', 'phone']],
    ['password_reset', 'email', 'Reset Your Password', '<p>Click here to reset: {{link}} (expires in 1 hour)</p>', ['link']],
    ['otp_code', 'sms', 'Verification Code', 'Your verification code is {{code}}. Valid for 10 minutes.', ['code']],
    ['booking_otp', 'sms', 'Site Visit OTP', 'Your site visit OTP is {{otp}}. Show this at the site. Booking: {{booking_id}}', ['otp', 'booking_id']],
    ['welcome_sms', 'sms', 'Welcome', 'Welcome to APS Dream Home, {{name}}! Browse properties at apsdreamhome.com', ['name']],
    ['payment_due', 'sms', 'Payment Reminder', 'Dear {{name}}, your EMI of â‚¹{{amount}} is due on {{date}}. Pay now to avoid late fees.', ['name', 'amount', 'date']],
    ['booking_reminder', 'push', 'Booking Reminder', 'Your site visit is scheduled for {{date}} at {{time}}', ['date', 'time']],
    ['price_drop', 'push', 'Price Drop Alert', '{{property}} price reduced by {{percent}}%! New: â‚¹{{new_price}}', ['property', 'percent', 'new_price']],
    ['new_property', 'push', 'New Property Available', 'New {{type}} in {{location}}: â‚¹{{price}}', ['type', 'location', 'price']],
];

foreach ($templates as $t) {
    $st = $pdo->prepare("INSERT INTO notification_templates (template_code, template_name, channel, subject, body, variables, is_active, created_at)
                          VALUES (:c, :tn, :ch, :s, :b, :v, 1, NOW())
                          ON DUPLICATE KEY UPDATE subject = VALUES(subject), body = VALUES(body), variables = VALUES(variables), is_active = 1");
    $st->execute([':c' => $t[0], ':tn' => ucwords(str_replace('_', ' ', $t[0])), ':ch' => $t[1], ':s' => $t[2], ':b' => $t[3], ':v' => json_encode($t[4], JSON_UNESCAPED_UNICODE)]);
}
$results['notification_templates'] = count($templates);

// 2. SMS templates
$smsTemplates = [
    ['welcome_user', 'Welcome User', 'Welcome to APS Dream Home, {{name}}!'],
    ['otp_login', 'Login OTP', 'Your login OTP is {{otp}}. Do not share.'],
    ['otp_txn', 'Transaction OTP', 'Transaction OTP: {{otp}} for {{txn}}'],
    ['emi_remind', 'EMI Reminder', 'Dear {{name}}, EMI of Rs {{amount}} due {{date}}. Pay to avoid penalty.'],
    ['booking_site_visit', 'Site Visit', 'Site visit confirmed: {{date}} {{time}} for {{property}}.'],
    ['payment_thanks', 'Payment Thanks', 'Payment of Rs {{amount}} received. Receipt: {{receipt}}.'],
    ['lead_status', 'Lead Status', 'Your inquiry {{id}} status: {{status}}.'],
    ['wishes', 'Festival Wishes', 'Happy {{festival}} from APS Dream Home! Special offers inside.'],
];

foreach ($smsTemplates as $t) {
    $st = $pdo->prepare("INSERT INTO sms_templates (template_code, template_name, body, variables, is_active, created_at) VALUES (:c, :n, :b, :v, 1, NOW())
                          ON DUPLICATE KEY UPDATE body = VALUES(body), is_active = 1");
    $st->execute([':c' => $t[0], ':n' => $t[1], ':b' => $t[2], ':v' => '{}']);
}
$results['sms_templates'] = count($smsTemplates);

// 3. Tax types (type_code, type_name, description, default_rate)
$taxTypes = [
    ['GST', 'Goods and Services Tax', 'Central + State GST (CGST/SGST) or Integrated GST (IGST)', 18.0],
    ['CGST', 'Central GST', 'Central portion of GST (intra-state)', 9.0],
    ['SGST', 'State GST', 'State portion of GST (intra-state)', 9.0],
    ['IGST', 'Integrated GST', 'Inter-state GST', 18.0],
    ['TDS', 'Tax Deducted at Source', 'Tax on property transactions over 50L', 1.0],
    ['STAMP_DUTY', 'Stamp Duty', 'State stamp duty on property registration', 5.0],
    ['REGISTRATION', 'Registration Fee', 'Property registration fee', 1.0],
    ['INCOME_TAX', 'Income Tax', 'Capital gains tax on property', 20.0],
];

foreach ($taxTypes as $t) {
    $st = $pdo->prepare("INSERT INTO tax_types (type_code, type_name, description, default_rate, is_active) VALUES (:c, :n, :d, :r, 1)
                          ON DUPLICATE KEY UPDATE type_name = VALUES(type_name), description = VALUES(description), default_rate = VALUES(default_rate), is_active = 1");
    $st->execute([':c' => $t[0], ':n' => $t[1], ':d' => $t[2], ':r' => $t[3]]);
}
$results['tax_types'] = count($taxTypes);

// GST settings (key-value)
$gstSettings = [
    ['UP_CGST', '9.00', 'Uttar Pradesh CGST rate'],
    ['UP_SGST', '9.00', 'Uttar Pradesh SGST rate'],
    ['UP_IGST', '18.00', 'Uttar Pradesh IGST rate (interstate)'],
    ['MH_CGST', '9.00', 'Maharashtra CGST rate'],
    ['MH_SGST', '9.00', 'Maharashtra SGST rate'],
    ['MH_IGST', '18.00', 'Maharashtra IGST rate'],
    ['DL_CGST', '9.00', 'Delhi CGST rate'],
    ['DL_SGST', '9.00', 'Delhi SGST rate'],
    ['DL_IGST', '18.00', 'Delhi IGST rate'],
    ['DEFAULT_CGST', '9.00', 'Default CGST if state not specified'],
    ['DEFAULT_SGST', '9.00', 'Default SGST if state not specified'],
    ['DEFAULT_IGST', '18.00', 'Default IGST if state not specified'],
    ['STAMP_DUTY_UP', '5.00', 'UP stamp duty percentage'],
    ['STAMP_DUTY_MH', '5.00', 'MH stamp duty percentage'],
    ['STAMP_DUTY_DL', '6.00', 'Delhi stamp duty percentage (men)'],
    ['STAMP_DUTY_DL_W', '4.00', 'Delhi stamp duty percentage (women)'],
    ['REGISTRATION_UP', '1.00', 'UP registration fee percentage'],
    ['TDS_THRESHOLD', '5000000', 'TDS threshold for property (INR)'],
    ['TDS_RATE', '1.00', 'TDS rate for property transactions'],
    ['CAPITAL_GAINS_SHORT', '15.00', 'Short-term capital gains (under 2 years)'],
    ['CAPITAL_GAINS_LONG', '20.00', 'Long-term capital gains (over 2 years, with indexation)'],
];

foreach ($gstSettings as $g) {
    $st = $pdo->prepare("INSERT INTO gst_settings (setting_key, setting_value, description, updated_at) VALUES (:k, :v, :d, NOW())
                          ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description), updated_at = NOW()");
    $st->execute([':k' => $g[0], ':v' => $g[1], ':d' => $g[2]]);
}
$results['gst_settings'] = count($gstSettings);

// 4. Tax slabs (tax_type ENUM, min, max, rate_pct, fiscal_year)
$slabs = [
    ['income_tax', 0, 250000, 0, '2025-26', 1],
    ['income_tax', 250000, 500000, 5, '2025-26', 1],
    ['income_tax', 500000, 750000, 10, '2025-26', 1],
    ['income_tax', 750000, 1000000, 15, '2025-26', 1],
    ['income_tax', 1000000, 1500000, 20, '2025-26', 1],
    ['income_tax', 1500000, null, 30, '2025-26', 1],
    ['gst', 0, null, 18, '2025-26', 1],
    ['tds', 5000000, null, 1, '2025-26', 1],
    ['property_tax', 0, null, 5, '2025-26', 1],
];

foreach ($slabs as $s) {
    $st = $pdo->prepare("INSERT INTO tax_slabs (tax_type, min_amount, max_amount, rate_pct, fiscal_year, is_active) VALUES (:t, :mi, :ma, :r, :fy, :a)
                          ON DUPLICATE KEY UPDATE rate_pct = VALUES(rate_pct), is_active = VALUES(is_active)");
    $st->execute([':t' => $s[0], ':mi' => $s[1], ':ma' => $s[2], ':r' => $s[3], ':fy' => $s[4], ':a' => $s[5]]);
}
$results['tax_slabs'] = count($slabs);

// 5. MLM ranks (rank_name, rank_level, min_qualification_volume, min_downline_count, commission_multiplier, bonus_amount)
$ranks = [
    ['Associate', 1, 0, 0, 1.0, 0],
    ['Senior Associate', 2, 50000, 5, 1.4, 5000],
    ['Team Leader', 3, 250000, 15, 1.8, 15000],
    ['Branch Manager', 4, 1000000, 50, 2.2, 50000],
    ['Regional Manager', 5, 5000000, 150, 2.7, 200000],
    ['Director', 6, 15000000, 500, 3.2, 1000000],
    ['National Director', 7, 50000000, 1000, 3.7, 5000000],
    ['Ambassador', 8, 100000000, 2500, 4.5, 10000000],
];

foreach ($ranks as $r) {
    $st = $pdo->prepare("INSERT INTO mlm_rank_rates (rank_name, rank_level, min_qualification_volume, min_downline_count, commission_multiplier, bonus_amount)
                          VALUES (:n, :l, :v, :d, :m, :b)
                          ON DUPLICATE KEY UPDATE min_qualification_volume = VALUES(min_qualification_volume), min_downline_count = VALUES(min_downline_count), commission_multiplier = VALUES(commission_multiplier), bonus_amount = VALUES(bonus_amount)");
    $st->execute([':n' => $r[0], ':l' => $r[1], ':v' => $r[2], ':d' => $r[3], ':m' => $r[4], ':b' => $r[5]]);
}
$results['mlm_ranks'] = count($ranks);

// 6. KPIs (name, description, category ENUM, unit, default_target, target_type, weightage)
$kpis = [
    ['Sales Target', 'Monthly sales target in INR', 'sales', 'INR', 5000000, 'fixed', 20.0],
    ['New Leads', 'Number of new leads per month', 'productivity', 'count', 100, 'fixed', 15.0],
    ['Conversion Rate', 'Lead to customer conversion percentage', 'sales', '%', 25, 'percentage', 25.0],
    ['Customer Satisfaction', 'Average customer rating', 'customer_satisfaction', 'score', 4.5, 'range', 15.0],
    ['Response Time', 'Average response time in hours', 'operational', 'hours', 4, 'fixed', 10.0],
    ['Site Visits', 'Site visits per week', 'productivity', 'count', 20, 'fixed', 10.0],
    ['EMI Collection', 'EMI collection rate', 'financial', '%', 95, 'percentage', 20.0],
    ['Agent Performance', 'Average agent sales', 'sales', 'INR', 500000, 'fixed', 15.0],
    ['Property View', 'Property page views', 'productivity', 'count', 5000, 'fixed', 5.0],
    ['Support Resolution', 'Support ticket resolution time', 'operational', 'hours', 24, 'fixed', 10.0],
    ['Quality Score', 'Work quality score', 'quality', 'score', 85, 'range', 15.0],
    ['NPS Score', 'Net Promoter Score', 'customer_satisfaction', 'score', 50, 'range', 10.0],
];

foreach ($kpis as $k) {
    $st = $pdo->prepare("INSERT INTO kpis (name, description, category, unit, default_target, target_type, weightage, is_active, created_at) VALUES (:n, :d, :c, :u, :t, :tt, :w, 1, NOW())
                          ON DUPLICATE KEY UPDATE description = VALUES(description), default_target = VALUES(default_target), target_type = VALUES(target_type), weightage = VALUES(weightage), is_active = 1");
    $st->execute([':n' => $k[0], ':d' => $k[1], ':c' => $k[2], ':u' => $k[3], ':t' => $k[4], ':tt' => $k[5], ':w' => $k[6]]);
}
$results['kpis'] = count($kpis);

// 7. Performance benchmarks (benchmark_name, role, metric, baseline, target, excellent)
$benchmarks = [
    ['Top Agent Sales', 'agent', 'sales_inr', 500000, 1500000, 3000000],
    ['Top Associate Commission', 'associate', 'commission_inr', 200000, 800000, 2000000],
    ['Response Time (min)', 'support', 'minutes', 240, 60, 5],
    ['Resolution Rate', 'support', '%', 80, 95, 100],
    ['Network Growth', 'mlm', '%', 5, 15, 50],
    ['Active Associate Ratio', 'mlm', '%', 50, 80, 100],
    ['Collection Rate', 'finance', '%', 85, 95, 100],
    ['Outstanding Days', 'finance', 'days', 60, 30, 15],
    ['Lead Quality Score', 'marketing', 'score', 40, 70, 90],
    ['Cost per Lead', 'marketing', 'INR', 500, 200, 50],
    ['Site Visit Show Rate', 'sales', '%', 50, 80, 95],
    ['Document Completion', 'operations', '%', 80, 95, 100],
];

foreach ($benchmarks as $b) {
    $st = $pdo->prepare("INSERT INTO performance_benchmarks (benchmark_name, role, metric, baseline_value, target_value, excellent_value) VALUES (:n, :r, :m, :b, :t, :e)
                          ON DUPLICATE KEY UPDATE baseline_value = VALUES(baseline_value), target_value = VALUES(target_value), excellent_value = VALUES(excellent_value)");
    $st->execute([':n' => $b[0], ':r' => $b[1], ':m' => $b[2], ':b' => $b[3], ':t' => $b[4], ':e' => $b[5]]);
}
$results['benchmarks'] = count($benchmarks);

// 8. Agent commission rates (agent_id, property_type, base_rate, override, bonus, effective_from)
$defaultRates = [
    [0, 'plot', 2.0, 0, 0.5, '2025-01-01'],
    [0, 'house', 1.5, 0, 0.5, '2025-01-01'],
    [0, 'flat', 1.5, 0, 0.5, '2025-01-01'],
    [0, 'shop', 2.0, 0, 0.5, '2025-01-01'],
    [0, 'farmhouse', 2.5, 0, 1.0, '2025-01-01'],
    [0, 'commercial', 2.0, 0, 0.5, '2025-01-01'],
    [0, 'land', 2.0, 0, 0.5, '2025-01-01'],
];

foreach ($defaultRates as $r) {
    $st = $pdo->prepare("INSERT INTO agent_commission_rates (agent_id, property_type, base_rate_pct, override_pct, bonus_rate_pct, effective_from) VALUES (:a, :pt, :b, :o, :bo, :e)
                          ON DUPLICATE KEY UPDATE base_rate_pct = VALUES(base_rate_pct), override_pct = VALUES(override_pct), bonus_rate_pct = VALUES(bonus_rate_pct)");
    $st->execute([':a' => $r[0], ':pt' => $r[1], ':b' => $r[2], ':o' => $r[3], ':bo' => $r[4], ':e' => $r[5]]);
}
$results['commission_rates'] = count($defaultRates);

// 9. Resell commission structure (property_type, min, max, commission_pct, flat, effective_from, is_active)
$resellComm = [
    ['plot', 0, 2500000, 1.5, 0, '2025-01-01'],
    ['plot', 2500000, 10000000, 2.0, 0, '2025-01-01'],
    ['plot', 10000000, 50000000, 2.5, 0, '2025-01-01'],
    ['plot', 50000000, null, 3.0, 0, '2025-01-01'],
    ['house', 0, 5000000, 1.5, 0, '2025-01-01'],
    ['house', 5000000, 20000000, 2.0, 0, '2025-01-01'],
    ['house', 20000000, null, 2.5, 0, '2025-01-01'],
    ['flat', 0, 5000000, 1.5, 0, '2025-01-01'],
    ['flat', 5000000, 20000000, 2.0, 0, '2025-01-01'],
    ['flat', 20000000, null, 2.5, 0, '2025-01-01'],
    ['commercial', 0, null, 3.0, 0, '2025-01-01'],
    ['farmhouse', 0, null, 3.5, 0, '2025-01-01'],
];

foreach ($resellComm as $r) {
    $st = $pdo->prepare("INSERT INTO resell_commission_structure (property_type, min_price, max_price, commission_pct, flat_amount, effective_from, is_active) VALUES (:pt, :mi, :ma, :r, :f, :e, 1)
                          ON DUPLICATE KEY UPDATE commission_pct = VALUES(commission_pct), flat_amount = VALUES(flat_amount), is_active = 1");
    $st->execute([':pt' => $r[0], ':mi' => $r[1], ':ma' => $r[2], ':r' => $r[3], ':f' => $r[4], ':e' => $r[5]]);
}
$results['resell_commission'] = count($resellComm);

// 10. Workflow automations (automation_name, trigger_event, conditions, actions)
$workflows = [
    ['New Lead Auto-Assign', 'lead.created', '{"score_threshold": 0}', '[{"type": "score_lead"}, {"type": "send_whatsapp", "message": "Welcome to APS Dream Home!"}]'],
    ['Booking Confirmation', 'booking.confirmed', '{}', '[{"type": "send_email", "template": "booking_confirmed"}, {"type": "send_sms", "template": "welcome_user"}]'],
    ['Payment Received', 'payment.received', '{}', '[{"type": "send_email", "template": "payment_received"}]'],
    ['High-Value Lead Alert', 'lead.high_value', '{"min_budget": 5000000}', '[{"type": "send_email", "subject": "High-Value Lead", "message": "New high-value lead needs attention"}]'],
    ['Site Visit Reminder', 'visit.scheduled', '{"hours_before": 24}', '[{"type": "send_sms", "template": "booking_site_visit"}, {"type": "send_push", "template": "booking_reminder"}]'],
    ['EMI Due Reminder', 'emi.due_soon', '{"days_before": 3}', '[{"type": "send_sms", "template": "emi_remind"}]'],
    ['Welcome Series', 'user.registered', '{}', '[{"type": "send_email", "template": "welcome"}, {"type": "send_sms", "template": "welcome_sms"}]'],
    ['Document Expiry Alert', 'document.expiring', '{"days_before": 30}', '[{"type": "send_email", "subject": "Document Expiring"}]'],
    ['Missed Call Follow-up', 'lead.missed_call', '{"hours_after": 1}', '[{"type": "send_sms", "message": "Sorry we missed your call. How can we help?"}]'],
    ['Birthday Wishes', 'customer.birthday', '{}', '[{"type": "send_sms", "message": "Happy Birthday! Special offer inside."}]'],
];

foreach ($workflows as $w) {
    $st = $pdo->prepare("INSERT INTO workflow_automations (automation_name, trigger_event, conditions, actions, is_active, last_run_at, run_count) VALUES (:n, :t, :c, :a, 1, NULL, 0)
                          ON DUPLICATE KEY UPDATE conditions = VALUES(conditions), actions = VALUES(actions), is_active = 1");
    $st->execute([':n' => $w[0], ':t' => $w[1], ':c' => $w[2], ':a' => $w[3]]);
}
$results['workflows'] = count($workflows);

// 11. Admin menu items (name, icon, url, section, order_index, is_active)
$menus = [
    ['Progressive Registrations', 'fas fa-user-clock', '/admin/features/registrations', 'users', 50, 1],
    ['Payroll', 'fas fa-money-check-alt', '/admin/features/payroll', 'hr', 25, 1],
    ['Resell Properties', 'fas fa-home', '/admin/features/resell', 'properties', 30, 1],
    ['Commission Engine', 'fas fa-percentage', '/admin/features/commissions', 'finance', 35, 1],
    ['Notification Center', 'fas fa-bell', '/admin/features/notifications', 'operations', 40, 1],
    ['Security Center', 'fas fa-shield-alt', '/admin/features/security', 'system', 50, 1],
    ['Finance Management', 'fas fa-rupee-sign', '/admin/features/finance', 'finance', 40, 1],
    ['Analytics & KPIs', 'fas fa-chart-line', '/admin/features/analytics', 'reports', 20, 1],
    ['Agent Tasks & Workflows', 'fas fa-robot', '/admin/features/agent-tasks', 'operations', 45, 1],
    ['OCR & Documents', 'fas fa-file-alt', '/admin/features/ocr', 'operations', 55, 1],
    ['Property Maintenance', 'fas fa-tools', '/admin/features/maintenance', 'properties', 60, 1],
];

foreach ($menus as $m) {
    $st = $pdo->prepare("INSERT INTO admin_menu_items (name, icon, url, section, order_index, is_active, created_at, updated_at) VALUES (:n, :i, :u, :s, :o, 1, NOW(), NOW())
                          ON DUPLICATE KEY UPDATE name = VALUES(name), url = VALUES(url), icon = VALUES(icon), section = VALUES(section), order_index = VALUES(order_index), is_active = 1");
    $st->execute([':n' => $m[0], ':i' => $m[1], ':u' => $m[2], ':s' => $m[3], ':o' => $m[4]]);
}
$results['menu_items'] = count($menus);

// 12. Farmer commission structures
$farmerStructures = [
    ['Basic Farmer', 'referral', 1.0, '{"tiers": [{"min": 0, "rate": 1.0}, {"min": 5, "rate": 1.5}, {"min": 10, "rate": 2.0}]}'],
    ['Active Farmer', 'referral', 1.5, '{"tiers": [{"min": 0, "rate": 1.5}, {"min": 5, "rate": 2.0}, {"min": 10, "rate": 2.5}]}'],
    ['Lead Farmer', 'land_sale', 2.0, '{"tiers": [{"min": 0, "rate": 2.0}, {"min": 3, "rate": 2.5}, {"min": 5, "rate": 3.0}]}'],
    ['Equipment Partner', 'equipment', 3.0, '{"tiers": [{"min": 0, "rate": 3.0}]}'],
    ['Crop Advisor', 'crop_advisory', 1.5, '{"tiers": [{"min": 0, "rate": 1.5}]}'],
];

foreach ($farmerStructures as $f) {
    $st = $pdo->prepare("INSERT INTO farmer_commission_structures (structure_name, commission_type, base_rate_pct, tier_rules, is_active) VALUES (:n, :t, :r, :tr, 1)
                          ON DUPLICATE KEY UPDATE base_rate_pct = VALUES(base_rate_pct), tier_rules = VALUES(tier_rules), is_active = 1");
    $st->execute([':n' => $f[0], ':t' => $f[1], ':r' => $f[2], ':tr' => $f[3]]);
}
$results['farmer_structures'] = count($farmerStructures);

// 13. OCR templates
$ocrTemplates = [
    ['Aadhaar Card', 'identity', '{"fields": [{"name": "name", "regex": "Name[:\\s]+(.+)"}, {"name": "dob", "regex": "DOB[:\\s]+(.+)"}, {"name": "uid", "regex": "\\d{4}\\s\\d{4}\\s\\d{4}"}, {"name": "address", "regex": "Address[:\\s]+(.+)"}]}'],
    ['PAN Card', 'identity', '{"fields": [{"name": "pan", "regex": "[A-Z]{5}\\d{4}[A-Z]"}, {"name": "name", "regex": "Name[:\\s]+(.+)"}]}'],
    ['Passport', 'identity', '{"fields": [{"name": "passport_no", "regex": "[A-Z]\\d{7}"}, {"name": "name", "regex": "Name[:\\s]+(.+)"}, {"name": "expiry", "regex": "\\d{2}/\\d{2}/\\d{4}"}]}'],
    ['Bank Statement', 'financial', '{"fields": [{"name": "account_no", "regex": "Account[:\\s]+(\\d+)"}, {"name": "ifsc", "regex": "[A-Z]{4}0[A-Z0-9]{6}"}, {"name": "balance", "regex": "Balance[:\\s]+(.+)"}]}'],
    ['Property Deed', 'property', '{"fields": [{"name": "khasra", "regex": "Khasra[:\\s]+(.+)"}, {"name": "khewat", "regex": "Khewat[:\\s]+(.+)"}, {"name": "owner", "regex": "Owner[:\\s]+(.+)"}]}'],
    ['RERA Certificate', 'property', '{"fields": [{"name": "rera_no", "regex": "RERA[:\\s]+(.+)"}, {"name": "project", "regex": "Project[:\\s]+(.+)"}, {"name": "validity", "regex": "Valid[:\\s]+(.+)"}]}'],
    ['GST Certificate', 'tax', '{"fields": [{"name": "gstin", "regex": "\\d{2}[A-Z]{5}\\d{4}[A-Z]\\d[A-Z]\\d"}, {"name": "legal_name", "regex": "Name[:\\s]+(.+)"}, {"name": "trade_name", "regex": "Trade Name[:\\s]+(.+)"}]}'],
    ['Salary Slip', 'financial', '{"fields": [{"name": "employee", "regex": "Employee[:\\s]+(.+)"}, {"name": "gross", "regex": "Gross[:\\s]+(.+)"}, {"name": "net", "regex": "Net[:\\s]+(.+)"}]}'],
    ['Invoice', 'financial', '{"fields": [{"name": "invoice_no", "regex": "Invoice[:\\s]+(.+)"}, {"name": "date", "regex": "Date[:\\s]+(.+)"}, {"name": "amount", "regex": "Amount[:\\s]+(.+)"}]}'],
    ['Agreement', 'legal', '{"fields": [{"name": "parties", "regex": "Between[:\\s]+(.+)"}, {"name": "date", "regex": "Date[:\\s]+(.+)"}, {"name": "value", "regex": "Value[:\\s]+(.+)"}]}'],
];

foreach ($ocrTemplates as $t) {
    $st = $pdo->prepare("INSERT INTO ocr_templates (template_name, document_type, field_definitions, is_active) VALUES (:n, :t, :f, 1)
                          ON DUPLICATE KEY UPDATE field_definitions = VALUES(field_definitions), is_active = 1");
    $st->execute([':n' => $t[0], ':t' => $t[1], ':f' => $t[2]]);
}
$results['ocr_templates'] = count($ocrTemplates);

echo "=== SEED COMPLETE ===\n";
foreach ($results as $k => $v) echo "  $k: $v\n";
echo "  TOTAL: " . array_sum($results) . " records\n";?>