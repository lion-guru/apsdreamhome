<?php
/**
 * Seed More Tables - APS Dream Home
 * Seeds empty tables with sample data dynamically.
 */

$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function tableExists(PDO $pdo, string $name): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$name]);
    return (bool) $stmt->fetch();
}

function tableRowCount(PDO $pdo, string $name): int {
    return (int) $pdo->query("SELECT COUNT(*) AS cnt FROM `$name`")->fetch()['cnt'];
}

function getColumns(PDO $pdo, string $name): array {
    $cols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `$name`");
    while ($row = $stmt->fetch()) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

function hasColumns(PDO $pdo, string $table, array $required): bool {
    $existing = getColumns($pdo, $table);
    return empty(array_diff($required, $existing));
}

function insertDynamic(PDO $pdo, string $table, array $rows): int {
    if (empty($rows)) return 0;
    $cols = getColumns($pdo, $table);
    $inserted = 0;
    foreach ($rows as $row) {
        // Only keep columns that exist in the table
        $filtered = array_intersect_key($row, array_flip($cols));
        if (empty($filtered)) continue;
        $names = array_keys($filtered);
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $sql = "INSERT IGNORE INTO `$table` (`" . implode('`,`', $names) . "`) VALUES ($placeholders)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($filtered));
            if ($stmt->rowCount() > 0) $inserted++;
        } catch (Exception $e) {
            echo "  SKIP $table: {$e->getMessage()}\n";
        }
    }
    return $inserted;
}

function seedOrSkip(PDO $pdo, string $label, string $table, array $rows): void {
    echo "$label ($table): ";
    if (!tableExists($pdo, $table)) {
        echo "TABLE NOT FOUND\n";
        return;
    }
    $count = tableRowCount($pdo, $table);
    if ($count > 0) {
        echo "already has $count rows — SKIPPED\n";
        return;
    }
    $inserted = insertDynamic($pdo, $table, $rows);
    echo "seeded $inserted row(s)\n";
}

echo "=== APS Dream Home - Seed More Tables ===\n\n";

// ── Voice AI System ──────────────────────────────────────────────

seedOrSkip($pdo, 'AI Calling Agents', 'ai_calling_agents', [
    ['name' => 'Riya', 'language' => 'hi,en', 'voice' => 'female', 'personality' => 'friendly and patient', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
    ['name' => 'Alex', 'language' => 'en', 'voice' => 'male', 'personality' => 'professional and concise', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
    ['name' => 'Priya', 'language' => 'hi', 'voice' => 'female', 'personality' => 'warm and persuasive', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'AI Call Scripts', 'ai_call_scripts', [
    ['name' => 'Property Inquiry Script', 'language' => 'en', 'content' => 'Hello! I am calling from APS Dream Home regarding your interest in properties. Would you like to know about our latest projects?', 'category' => 'inquiry', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
    ['name' => 'Site Visit Booking Script', 'language' => 'hi,en', 'content' => 'Namaste! APS Dream Home se bol rahe hain. Aapne property inquiry ki thi, kya aap site visit ke liye interested hain?', 'category' => 'booking', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
    ['name' => 'Follow-up Script', 'language' => 'en', 'content' => 'Hi, this is a follow-up from APS Dream Home. We wanted to check if you have any questions about our properties.', 'category' => 'followup', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'AI Call Sessions', 'ai_call_sessions', [
    ['agent_name' => 'Riya', 'caller_phone' => '9876543210', 'caller_name' => 'Amit Sharma', 'status' => 'completed', 'duration_seconds' => 145, 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['agent_name' => 'Alex', 'caller_phone' => '9876543211', 'caller_name' => 'Priya Patel', 'status' => 'in_progress', 'duration_seconds' => 0, 'created_at' => date('Y-m-d H:i:s')],
    ['agent_name' => 'Priya', 'caller_phone' => '9876543212', 'caller_name' => 'Rahul Verma', 'status' => 'scheduled', 'duration_seconds' => 0, 'created_at' => date('Y-m-d H:i:s', strtotime('+1 day'))],
]);

seedOrSkip($pdo, 'AI Call Logs', 'ai_call_logs', [
    ['session_id' => 1, 'message' => 'Customer inquired about 2BHK flats in Gorakhpur', 'role' => 'assistant', 'sentiment' => 'positive', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['session_id' => 1, 'message' => 'Interested in visiting Suryoday Heights this weekend', 'role' => 'user', 'sentiment' => 'positive', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['session_id' => 2, 'message' => 'Asked about payment plans and EMI options', 'role' => 'user', 'sentiment' => 'neutral', 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'AI Calling Schedule', 'ai_calling_schedule', [
    ['lead_id' => 1, 'phone' => '9876543213', 'scheduled_date' => date('Y-m-d', strtotime('+1 day')), 'scheduled_time' => '10:00:00', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
    ['lead_id' => 2, 'phone' => '9876543214', 'scheduled_date' => date('Y-m-d', strtotime('+1 day')), 'scheduled_time' => '11:30:00', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
    ['lead_id' => 3, 'phone' => '9876543215', 'scheduled_date' => date('Y-m-d', strtotime('+1 day')), 'scheduled_time' => '14:00:00', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'AI Call Extracted Leads', 'ai_call_extracted_leads', [
    ['session_id' => 1, 'name' => 'Amit Sharma', 'phone' => '9876543210', 'email' => 'amit.sharma@email.com', 'property_interest' => '2BHK Flat', 'budget_range' => '35-45 Lakhs', 'location' => 'Gorakhpur', 'status' => 'qualified', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['session_id' => 2, 'name' => 'Priya Patel', 'phone' => '9876543211', 'email' => 'priya.patel@email.com', 'property_interest' => '3BHK Flat', 'budget_range' => '50-65 Lakhs', 'location' => 'Lucknow', 'status' => 'new', 'created_at' => date('Y-m-d H:i:s')],
    ['session_id' => 3, 'name' => 'Rahul Verma', 'phone' => '9876543212', 'email' => 'rahul.verma@email.com', 'property_interest' => 'Plot 1500 sqft', 'budget_range' => '20-30 Lakhs', 'location' => 'Kushinagar', 'status' => 'contacted', 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'AI Lead Scores', 'ai_lead_scores', [
    ['lead_id' => 1, 'score' => 85, 'engagement_level' => 'high', 'sentiment_score' => 0.8, 'property_match_score' => 0.9, 'last_updated' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['lead_id' => 2, 'score' => 72, 'engagement_level' => 'medium', 'sentiment_score' => 0.6, 'property_match_score' => 0.75, 'last_updated' => date('Y-m-d H:i:s')],
    ['lead_id' => 3, 'score' => 91, 'engagement_level' => 'high', 'sentiment_score' => 0.85, 'property_match_score' => 0.95, 'last_updated' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'Voice Assistant Config', 'voice_assistant_config', [
    ['language' => 'hi,en', 'voice' => 'female', 'tts_speed' => 1.0, 'welcome_message' => 'Namaste! APS Dream Home virtual assistant mein aapka swagat hai.', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
]);

// ── Documents ───────────────────────────────────────────────────

seedOrSkip($pdo, 'Business Documents', 'business_documents', [
    ['lead_id' => 1, 'doc_type' => 'agreement', 'file_path' => '/uploads/documents/agreement_1.pdf', 'generated_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
    ['lead_id' => 2, 'doc_type' => 'investment_plan', 'file_path' => '/uploads/documents/investment_2.pdf', 'generated_at' => date('Y-m-d H:i:s', strtotime('-3 days'))],
    ['lead_id' => 3, 'doc_type' => 'report', 'file_path' => '/uploads/documents/report_3.pdf', 'generated_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
]);

seedOrSkip($pdo, 'Customer Documents', 'customer_documents', [
    ['customer_name' => 'Amit Sharma', 'document_type' => 'KYC', 'status' => 'verified', 'created_at' => date('Y-m-d H:i:s')],
    ['customer_name' => 'Priya Patel', 'document_type' => 'Income Proof', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
    ['customer_name' => 'Rahul Verma', 'document_type' => 'Identity Proof', 'status' => 'verified', 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'Document Templates', 'document_templates', [
    ['template_code' => 'SALE_AGREEMENT', 'template_name' => 'Property Sale Agreement', 'category' => 'agreement', 'content_html' => '<h1>Sale Agreement</h1><p>This agreement is made on [date] between [buyer] and [seller].</p>', 'content_text' => 'Sale Agreement - Date: [date] Between: [buyer] and [seller]', 'variables' => '{"date":"","buyer":"","seller":"","property":"","amount":""}', 'description' => 'Standard property sale agreement template', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
    ['template_code' => 'NOC_FORMAT', 'template_name' => 'No Objection Certificate', 'category' => 'certificate', 'content_html' => '<h1>No Objection Certificate</h1><p>This is to certify that [authority] has no objection to [activity].</p>', 'content_text' => 'No Objection Certificate - [authority] has no objection to [activity]', 'variables' => '{"authority":"","applicant":"","property":"","date":""}', 'description' => 'NOC format for development authorities', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
    ['template_code' => 'DEED_FORMAT', 'template_name' => 'Title Deed Format', 'category' => 'legal', 'content_html' => '<h1>Title Deed</h1><p>This deed of title is executed on [date] by [party].</p>', 'content_text' => 'Title Deed - Date: [date] Party: [party]', 'variables' => '{"date":"","party":"","property":"","consideration":""}', 'description' => 'Title deed format for land registration', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'Document Classification', 'document_classification', [
    ['document_content_hash' => 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1', 'predicted_type' => 'aadhar', 'confidence_score' => 0.9500, 'created_at' => date('Y-m-d H:i:s')],
    ['document_content_hash' => 'b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2', 'predicted_type' => 'pan', 'confidence_score' => 0.8800, 'created_at' => date('Y-m-d H:i:s')],
    ['document_content_hash' => 'c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3', 'predicted_type' => 'bank_statement', 'confidence_score' => 0.9200, 'created_at' => date('Y-m-d H:i:s')],
]);

seedOrSkip($pdo, 'Document Reviews', 'document_reviews', [
    ['document_id' => 1, 'reviewer_name' => 'Admin', 'status' => 'approved', 'comments' => 'All documents verified and authentic', 'reviewed_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
    ['document_id' => 2, 'reviewer_name' => 'Admin', 'status' => 'pending', 'comments' => 'Awaiting additional supporting documents', 'reviewed_at' => null, 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['document_id' => 3, 'reviewer_name' => 'Admin', 'status' => 'rejected', 'comments' => 'Document expired, please submit updated version', 'reviewed_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))],
]);

// ── HR/Payroll ──────────────────────────────────────────────────

seedOrSkip($pdo, 'Employee Leaves', 'employee_leaves', [
    ['employee_id' => 1, 'leave_type' => 'sick', 'start_date' => date('Y-m-d', strtotime('-5 days')), 'end_date' => date('Y-m-d', strtotime('-3 days')), 'status' => 'approved', 'reason' => 'Medical appointment', 'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))],
    ['employee_id' => 2, 'leave_type' => 'casual', 'start_date' => date('Y-m-d', strtotime('+10 days')), 'end_date' => date('Y-m-d', strtotime('+10 days')), 'status' => 'pending', 'reason' => 'Personal work', 'created_at' => date('Y-m-d H:i:s')],
    ['employee_id' => 1, 'leave_type' => 'annual', 'start_date' => date('Y-m-d', strtotime('+20 days')), 'end_date' => date('Y-m-d', strtotime('+24 days')), 'status' => 'approved', 'reason' => 'Family vacation', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
]);

// Payroll — try both employee_payroll and employee_payrolls
foreach (['employee_payroll', 'employee_payrolls'] as $ptable) {
    seedOrSkip($pdo, "Employee Payroll ($ptable)", $ptable, [
        ['employee_id' => 1, 'basic_salary' => 35000, 'allowances' => 8000, 'deductions' => 5000, 'net_salary' => 38000, 'pay_period' => date('Y-m'), 'status' => 'paid', 'created_at' => date('Y-m-d H:i:s')],
        ['employee_id' => 2, 'basic_salary' => 45000, 'allowances' => 10000, 'deductions' => 6000, 'net_salary' => 49000, 'pay_period' => date('Y-m'), 'status' => 'processing', 'created_at' => date('Y-m-d H:i:s')],
        ['employee_id' => 3, 'basic_salary' => 55000, 'allowances' => 12000, 'deductions' => 7500, 'net_salary' => 59500, 'pay_period' => date('Y-m'), 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
    ]);
}

// ── Commission ──────────────────────────────────────────────────

seedOrSkip($pdo, 'Commission Calculations', 'commission_calculations', [
    ['agent_name' => 'Vikram Singh', 'sale_amount' => 4500000, 'commission_rate' => 5.00, 'commission_amount' => 225000, 'type' => 'direct_sale', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
    ['agent_name' => 'Sneha Gupta', 'sale_amount' => 2800000, 'commission_rate' => 3.50, 'commission_amount' => 98000, 'type' => 'team_sale', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
    ['agent_name' => 'Anita Desai', 'sale_amount' => 6200000, 'commission_rate' => 4.00, 'commission_amount' => 248000, 'type' => 'referral', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))],
]);

seedOrSkip($pdo, 'Commission Payouts', 'commission_payouts', [
    ['agent_name' => 'Vikram Singh', 'amount' => 225000, 'status' => 'paid', 'payment_method' => 'bank_transfer', 'paid_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
    ['agent_name' => 'Sneha Gupta', 'amount' => 98000, 'status' => 'pending', 'payment_method' => 'bank_transfer', 'paid_at' => null, 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
    ['agent_name' => 'Anita Desai', 'amount' => 248000, 'status' => 'processing', 'payment_method' => 'cheque', 'paid_at' => null, 'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))],
]);

seedOrSkip($pdo, 'Commission Transactions', 'commission_transactions', [
    ['commission_id' => 1, 'amount' => 225000, 'type' => 'credit', 'description' => 'Commission for direct sale - Suryoday Heights', 'status' => 'completed', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))],
    ['commission_id' => 2, 'amount' => 98000, 'type' => 'credit', 'description' => 'Commission for team sale - Braj Radha Enclave', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['commission_id' => 3, 'amount' => 248000, 'type' => 'credit', 'description' => 'Referral commission - Raghunath City Center', 'status' => 'completed', 'created_at' => date('Y-m-d H:i:s', strtotime('-8 days'))],
]);

// ── Marketing ───────────────────────────────────────────────────

seedOrSkip($pdo, 'Campaign Members', 'campaign_members', [
    ['member_id' => 1, 'campaign_id' => 1, 'lead_id' => 1, 'status' => 'sent', 'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))],
    ['member_id' => 2, 'campaign_id' => 1, 'lead_id' => 2, 'status' => 'opened', 'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))],
    ['member_id' => 3, 'campaign_id' => 1, 'lead_id' => 3, 'status' => 'responded', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
]);

echo "\n=== Seeding complete ===\n";
