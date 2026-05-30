<?php
/**
 * APS Dream Home - Master Seeding Script
 * Seeds 68 empty feature tables with realistic sample data.
 * Usage: php tools/seed_all_feature_tables.php
 */

$host = '127.0.0.1';
$port = '3307';
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo "FATAL: Cannot connect - " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== APS Dream Home - Master Feature Table Seeder ===\n\n";
echo "Connected to MySQL $host:$port / $dbname\n\n";

function tableExists(PDO $pdo, string $table): bool {
    return (bool)$pdo->query("SHOW TABLES LIKE " . $pdo->quote($table))->fetch();
}

function isTableEmpty(PDO $pdo, string $table): bool {
    $r = $pdo->query("SELECT COUNT(*) as c FROM `$table`")->fetch();
    return ((int)$r['c']) === 0;
}

function getColumns(PDO $pdo, string $table): array {
    return $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
}

function seedTable(PDO $pdo, string $table, array $rows, array $requiredColumns = []): void {
    if (!tableExists($pdo, $table)) {
        echo "  SKIP  | $table | Table does not exist\n";
        return;
    }
    if (!isTableEmpty($pdo, $table)) {
        $cnt = $pdo->query("SELECT COUNT(*) as c FROM `$table`")->fetch()['c'];
        echo "  SKIP  | $table | Already has $cnt row(s)\n";
        return;
    }
    $actualCols = getColumns($pdo, $table);
    foreach ($requiredColumns as $rc) {
        if (!in_array($rc, $actualCols)) {
            echo "  SKIP  | $table | Missing required column '$rc'\n";
            return;
        }
    }
    $inserted = 0; $errors = 0;
    foreach ($rows as $i => $row) {
        $filtered = [];
        foreach ($row as $col => $val) {
            if (in_array($col, $actualCols)) { $filtered[$col] = $val; }
        }
        if (empty($filtered)) { $errors++; continue; }
        $cols = array_keys($filtered);
        $vals = array_values($filtered);
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        $colList = '`' . implode('`, `', $cols) . '`';
        try {
            $stmt = $pdo->prepare("INSERT INTO `$table` ($colList) VALUES ($placeholders)");
            $stmt->execute($vals);
            $inserted++;
        } catch (PDOException $e) {
            echo "  ERROR | $table | Row " . ($i + 1) . ": " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    $status = $errors === 0 ? 'OK' : 'PARTIAL';
    echo "  $status | $table | Inserted $inserted row(s)" . ($errors ? ", $errors error(s)" : "") . "\n";
}

$NOW = date('Y-m-d H:i:s');

echo "============================================================\n";
echo "  BOOKING TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'plot_bookings', [
    ['plot_id' => 11, 'customer_id' => 3, 'booking_date' => '2026-05-01', 'booking_amount' => 50000, 'payment_status' => 'paid', 'agreement_signed' => 1, 'emi_enabled' => 1, 'emi_tenure_months' => 12, 'emi_amount' => 20833, 'interest_rate' => 8.5, 'advance_paid' => 50000, 'balance_amount' => 250000, 'payment_plan' => 'emi', 'status' => 'active', 'notes' => 'Plot A-001 Suryoday Colony', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['plot_id' => 12, 'customer_id' => 6, 'booking_date' => '2026-05-10', 'booking_amount' => 30000, 'payment_status' => 'partial', 'agreement_signed' => 0, 'emi_enabled' => 0, 'emi_tenure_months' => 0, 'emi_amount' => 0, 'interest_rate' => 0, 'advance_paid' => 30000, 'balance_amount' => 2970000, 'payment_plan' => 'down_payment', 'status' => 'active', 'notes' => 'Plot A-002 partial payment', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['plot_id' => 13, 'customer_id' => 10, 'booking_date' => '2026-05-15', 'booking_amount' => 100000, 'payment_status' => 'paid', 'agreement_signed' => 1, 'emi_enabled' => 1, 'emi_tenure_months' => 24, 'emi_amount' => 12500, 'interest_rate' => 9.0, 'advance_paid' => 100000, 'balance_amount' => 2900000, 'payment_plan' => 'emi', 'status' => 'active', 'notes' => 'Plot A-003 Suryoday Colony', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'booking_emis', [
    ['booking_id' => 1, 'installment_no' => 1, 'due_date' => '2026-06-01', 'amount' => 20833, 'paid_amount' => 20833, 'status' => 'paid', 'paid_at' => '2026-06-01 10:00:00', 'created_at' => $NOW],
    ['booking_id' => 1, 'installment_no' => 2, 'due_date' => '2026-07-01', 'amount' => 20833, 'paid_amount' => 0, 'status' => 'pending', 'paid_at' => null, 'created_at' => $NOW],
    ['booking_id' => 3, 'installment_no' => 1, 'due_date' => '2026-06-15', 'amount' => 12500, 'paid_amount' => 12500, 'status' => 'paid', 'paid_at' => '2026-06-15 09:30:00', 'created_at' => $NOW],
]);
seedTable($pdo, 'booking_logs', [
    ['booking_id' => 1, 'action' => 'created', 'user_id' => 68, 'changes' => '{"status":"active","amount":300000}', 'created_at' => $NOW],
    ['booking_id' => 1, 'action' => 'payment_received', 'user_id' => 68, 'changes' => '{"payment_status":"paid","amount":50000}', 'created_at' => $NOW],
    ['booking_id' => 2, 'action' => 'created', 'user_id' => 68, 'changes' => '{"status":"active","amount":3000000}', 'created_at' => $NOW],
]);
seedTable($pdo, 'booking_payments', [
    ['payment_id' => 1, 'booking_id' => 1, 'payment_amount' => 50000, 'payment_date' => '2026-05-01', 'payment_method' => 'bank_transfer', 'transaction_id' => 'TXN-BOK-001', 'payment_notes' => 'Booking advance for A-001'],
    ['payment_id' => 2, 'booking_id' => 1, 'payment_amount' => 20833, 'payment_date' => '2026-06-01', 'payment_method' => 'online', 'transaction_id' => 'TXN-BOK-002', 'payment_notes' => 'EMI 1 for A-001'],
    ['payment_id' => 3, 'booking_id' => 3, 'payment_amount' => 100000, 'payment_date' => '2026-05-15', 'payment_method' => 'cheque', 'transaction_id' => 'CHQ-0042', 'payment_notes' => 'Booking advance for A-003'],
]);
echo "\n============================================================\n";
echo "  COMMISSION TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'hybrid_commission_plans', [
    ['plan_name' => 'Standard Hybrid Plan', 'plan_code' => 'HYB-001', 'plan_type' => 'hybrid', 'description' => 'Standard hybrid commission plan with tiered payouts', 'total_commission_percentage' => 5.00, 'company_commission_percentage' => 2.00, 'resell_commission_percentage' => 1.00, 'development_cost_included' => 0, 'status' => 'active', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'hybrid_commission_records', [
    ['associate_id' => 2, 'property_id' => 1, 'customer_id' => 3, 'sale_amount' => 2500000, 'commission_amount' => 125000, 'commission_type' => 'hybrid', 'commission_breakdown' => '{"direct":5,"team":2}', 'level_achieved' => 'silver', 'payout_status' => 'pending', 'created_at' => $NOW, 'paid_at' => null],
    ['associate_id' => 53, 'property_id' => 3, 'customer_id' => 6, 'sale_amount' => 10400000, 'commission_amount' => 520000, 'commission_type' => 'hybrid', 'commission_breakdown' => '{"direct":5,"team":0}', 'level_achieved' => 'gold', 'payout_status' => 'approved', 'created_at' => $NOW, 'paid_at' => null],
    ['associate_id' => 54, 'property_id' => 4, 'customer_id' => 10, 'sale_amount' => 5000000, 'commission_amount' => 250000, 'commission_type' => 'direct', 'commission_breakdown' => '{"direct":5}', 'level_achieved' => 'bronze', 'payout_status' => 'paid', 'created_at' => $NOW, 'paid_at' => $NOW],
]);
seedTable($pdo, 'mlm_commission_agreements', [
    ['user_id' => 2, 'property_id' => 1, 'commission_rate' => 5.00, 'flat_amount' => null, 'valid_from' => '2026-01-01', 'valid_to' => '2026-12-31', 'notes' => 'Annual commission agreement', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 53, 'property_id' => 3, 'commission_rate' => 7.50, 'flat_amount' => null, 'valid_from' => '2026-03-01', 'valid_to' => '2027-02-28', 'notes' => 'Premium rate for senior associate', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 54, 'property_id' => 4, 'commission_rate' => null, 'flat_amount' => 100000, 'valid_from' => '2026-06-01', 'valid_to' => '2027-05-31', 'notes' => 'Flat fee agreement', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'mlm_commission_targets', [
    ['associate_id' => 2, 'target_period' => '2026-Q1', 'target_amount' => 5000000, 'achieved_amount' => 3200000, 'target_type' => 'quarterly', 'start_date' => '2026-01-01', 'end_date' => '2026-03-31', 'reward_amount' => 50000, 'status' => 'completed', 'created_at' => $NOW],
    ['associate_id' => 53, 'target_period' => '2026-Q2', 'target_amount' => 8000000, 'achieved_amount' => 0, 'target_type' => 'quarterly', 'start_date' => '2026-04-01', 'end_date' => '2026-06-30', 'reward_amount' => 75000, 'status' => 'in_progress', 'created_at' => $NOW],
    ['associate_id' => 54, 'target_period' => '2026-annual', 'target_amount' => 25000000, 'achieved_amount' => 5000000, 'target_type' => 'annual', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'reward_amount' => 200000, 'status' => 'in_progress', 'created_at' => $NOW],
]);
seedTable($pdo, 'resale_commissions', [
    ['associate_id' => 2, 'resale_property_id' => 1, 'amount' => 35000, 'paid_on' => '2026-04-15'],
    ['associate_id' => 53, 'resale_property_id' => 2, 'amount' => 50000, 'paid_on' => '2026-05-10'],
    ['associate_id' => 54, 'resale_property_id' => 3, 'amount' => 22000, 'paid_on' => '2026-05-20'],
]);
seedTable($pdo, 'resell_commission_structure', [
    ['plan_id' => 1, 'property_category' => 'plot', 'min_value' => 0, 'max_value' => 5000000, 'commission_percentage' => 2.00, 'fixed_commission' => null, 'commission_type' => 'percentage'],
]);
seedTable($pdo, 'traditional_commissions', [
    ['agent_id' => 2, 'property_id' => 1, 'commission_amount' => 140000, 'commission_rate' => 2.00, 'region' => 'Gorakhpur', 'sale_date' => '2026-04-01', 'status' => 'paid', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['agent_id' => 54, 'property_id' => 3, 'commission_amount' => 208000, 'commission_rate' => 2.00, 'region' => 'Lucknow', 'sale_date' => '2026-04-15', 'status' => 'approved', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['agent_id' => 81, 'property_id' => 4, 'commission_amount' => 100000, 'commission_rate' => 2.00, 'region' => 'Allahabad', 'sale_date' => '2026-05-01', 'status' => 'pending', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  COMMUNICATION TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'communications', [
    ['lead_id' => 1, 'type' => 'email', 'subject' => 'Property Visit Confirmation', 'notes' => 'Scheduled site visit for Villa project', 'communication_date' => $NOW, 'user_id' => 68, 'created_at' => $NOW],
    ['lead_id' => 2, 'type' => 'phone', 'subject' => 'Follow-up Call', 'notes' => 'Discussed plot options in Suryoday Colony', 'communication_date' => $NOW, 'user_id' => 63, 'created_at' => $NOW],
    ['lead_id' => 3, 'type' => 'sms', 'subject' => 'Festival Greeting', 'notes' => 'Sent Diwali wishes with special offer', 'communication_date' => $NOW, 'user_id' => 68, 'created_at' => $NOW],
]);
seedTable($pdo, 'communication_interactions', [
    ['lead_id' => 1, 'channel' => 'email', 'interaction_type' => 'outbound', 'direction' => 'sent', 'content' => 'Site visit confirmed for Saturday at 11 AM.', 'tag' => 'visit', 'status' => 'completed', 'created_at' => $NOW],
    ['lead_id' => 2, 'channel' => 'phone', 'interaction_type' => 'inbound', 'direction' => 'received', 'content' => 'Customer inquired about EMI options for plot.', 'tag' => 'inquiry', 'status' => 'completed', 'created_at' => $NOW],
    ['lead_id' => 3, 'channel' => 'whatsapp', 'interaction_type' => 'outbound', 'direction' => 'sent', 'content' => 'Happy Diwali! Check our festive offers on plots.', 'tag' => 'marketing', 'status' => 'sent', 'created_at' => $NOW],
]);
seedTable($pdo, 'communication_logs', [
    ['recipient_id' => 3, 'recipient_type' => 'customer', 'channel' => 'email', 'message_type' => 'booking_confirmation', 'subject' => 'Booking Confirmed - Plot A-001', 'message' => 'Your booking for Plot A-001 is confirmed.', 'status' => 'delivered', 'sent_at' => $NOW, 'delivered_at' => $NOW, 'read_at' => null, 'created_by' => 68, 'created_at' => $NOW],
    ['recipient_id' => 6, 'recipient_type' => 'customer', 'channel' => 'sms', 'message_type' => 'payment_reminder', 'subject' => 'Payment Due Reminder', 'message' => 'Your EMI of Rs.20,833 is due on 1st July.', 'status' => 'sent', 'sent_at' => $NOW, 'delivered_at' => null, 'read_at' => null, 'created_by' => 68, 'created_at' => $NOW],
    ['recipient_id' => 10, 'recipient_type' => 'customer', 'channel' => 'email', 'message_type' => 'welcome', 'subject' => 'Welcome to APS Dream Home', 'message' => 'Thank you for choosing us for your property investment.', 'status' => 'delivered', 'sent_at' => $NOW, 'delivered_at' => $NOW, 'read_at' => $NOW, 'created_by' => 68, 'created_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  DOCUMENT TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'farmer_documents', [
    ['farmer_id' => 1, 'document_type' => 'aadhar', 'document_number' => '1234-5678-9012', 'file_path' => '/uploads/farmers/aadhar/1.pdf', 'file_name' => 'aadhar_card.pdf', 'file_size' => 245760, 'mime_type' => 'application/pdf', 'uploaded_by' => 68, 'is_verified' => 1, 'verified_by' => 1, 'verified_at' => $NOW, 'remarks' => 'Original verified', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['farmer_id' => 2, 'document_type' => 'land_record', 'document_number' => 'KHATA-042/2026', 'file_path' => '/uploads/farmers/land/2.pdf', 'file_name' => 'khata_extract.pdf', 'file_size' => 512000, 'mime_type' => 'application/pdf', 'uploaded_by' => 68, 'is_verified' => 0, 'remarks' => 'Pending verification', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['farmer_id' => 3, 'document_type' => 'pan', 'document_number' => 'ABCDE1234F', 'file_path' => '/uploads/farmers/pan/3.pdf', 'file_name' => 'pan_card.pdf', 'file_size' => 102400, 'mime_type' => 'application/pdf', 'uploaded_by' => 63, 'is_verified' => 1, 'verified_by' => 1, 'verified_at' => $NOW, 'remarks' => 'PAN verified online', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'generated_documents', [
    ['template_id' => 1, 'document_code' => 'DOC-2026-0001', 'document_type' => 'agreement', 'entity_type' => 'booking', 'entity_id' => 1, 'title' => 'Plot Sale Agreement - A-001', 'variables_data' => '{"buyer":"Customer One","plot":"A-001","amount":3000000}', 'file_path' => '/uploads/documents/agreement_001.pdf', 'file_size' => 1024000, 'pages' => 12, 'digital_signature' => 'sig_abc123', 'signatures_required' => 2, 'signatures_collected' => 1, 'status' => 'generated', 'generated_by' => 68, 'generated_at' => $NOW, 'download_count' => 0, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['template_id' => 1, 'document_code' => 'DOC-2026-0002', 'document_type' => 'agreement', 'entity_type' => 'booking', 'entity_id' => 2, 'title' => 'Plot Sale Agreement - A-002', 'variables_data' => '{"buyer":"Customer Two","plot":"A-002","amount":3000000}', 'file_path' => '/uploads/documents/agreement_002.pdf', 'file_size' => 980000, 'pages' => 10, 'digital_signature' => 'sig_def456', 'signatures_required' => 2, 'signatures_collected' => 2, 'status' => 'sent', 'generated_by' => 68, 'generated_at' => $NOW, 'sent_at' => $NOW, 'download_count' => 2, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['template_id' => 2, 'document_code' => 'DOC-2026-0003', 'document_type' => 'offer_letter', 'entity_type' => 'lead', 'entity_id' => 1, 'title' => 'Special Offer Letter - Rahul Sharma', 'variables_data' => '{"customer":"Rahul Sharma","discount":"5%","valid_until":"2026-07-31"}', 'file_path' => '/uploads/documents/offer_001.pdf', 'file_size' => 450000, 'pages' => 3, 'status' => 'generated', 'generated_by' => 63, 'generated_at' => $NOW, 'download_count' => 0, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'legal_document_templates', [
    ['title' => 'Standard Plot Sale Agreement', 'content' => 'This agreement is made on [date] between [seller] and [buyer] for the sale of plot [plot_no]...', 'category' => 'sale', 'is_active' => 1, 'created_at' => $NOW],
    ['title' => 'Non-Disclosure Agreement', 'content' => 'This NDA is entered into by [party_a] and [party_b] for...', 'category' => 'nda', 'is_active' => 1, 'created_at' => $NOW],
    ['title' => 'Property Lease Contract', 'content' => 'This lease agreement is made on [date] between [lessor] and [lessee]...', 'category' => 'lease', 'is_active' => 1, 'created_at' => $NOW],
]);
seedTable($pdo, 'ocr_documents', [
    ['original_document_id' => 1, 'file_path' => '/uploads/ocr/aadhar_1.pdf', 'file_name' => 'aadhar_ocr.pdf', 'file_size' => 300000, 'mime_type' => 'application/pdf', 'document_type' => 'aadhar', 'ocr_status' => 'completed', 'processing_time' => 12.5, 'confidence_score' => 95.5, 'extracted_text' => 'Name: Rahul Sharma\nAadhar: 1234-5678-9012\nDOB: 15-08-1990', 'structured_data' => '{"name":"Rahul Sharma","aadhar":"1234-5678-9012","dob":"1990-08-15"}', 'validation_status' => 'valid', 'processed_by' => 68, 'processed_at' => $NOW, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['original_document_id' => 2, 'file_path' => '/uploads/ocr/pan_1.pdf', 'file_name' => 'pan_ocr.pdf', 'file_size' => 200000, 'mime_type' => 'application/pdf', 'document_type' => 'pan', 'ocr_status' => 'completed', 'processing_time' => 8.2, 'confidence_score' => 98.0, 'extracted_text' => 'PAN: ABCDE1234F\nName: Priya Singh\nDOB: 22-03-1985', 'structured_data' => '{"pan":"ABCDE1234F","name":"Priya Singh","dob":"1985-03-22"}', 'validation_status' => 'valid', 'processed_by' => 68, 'processed_at' => $NOW, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['original_document_id' => 3, 'file_path' => '/uploads/ocr/land_1.pdf', 'file_name' => 'land_ocr.pdf', 'file_size' => 1500000, 'mime_type' => 'application/pdf', 'document_type' => 'other', 'ocr_status' => 'failed', 'processing_time' => 45.0, 'confidence_score' => 0, 'extracted_text' => '', 'structured_data' => null, 'validation_status' => 'invalid', 'validation_errors' => '["Poor image quality, unable to extract text"]', 'processed_by' => 68, 'processed_at' => $NOW, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'property_documents', [
    ['property_id' => 1, 'plot_id' => 11, 'document_type' => 'title_deed', 'document_name' => 'Title Deed - A-001', 'file_path' => '/uploads/properties/title_deed_001.pdf', 'uploaded_at' => $NOW],
    ['property_id' => 3, 'document_type' => 'tax_receipt', 'document_name' => 'Property Tax Receipt 2025-26', 'file_path' => '/uploads/properties/tax_003.pdf', 'uploaded_at' => $NOW],
    ['property_id' => 4, 'document_type' => 'encumbrance', 'document_name' => 'Encumbrance Certificate', 'file_path' => '/uploads/properties/encumbrance_004.pdf', 'uploaded_at' => $NOW],
]);
seedTable($pdo, 'user_documents', [
    ['user_id' => 3, 'document_type' => 'aadhar', 'document_number' => '1111-2222-3333', 'file_path' => '/uploads/users/aadhar/3.pdf', 'file_type' => 'pdf', 'file_size' => 250000, 'verification_status' => 'verified', 'verified_by' => 1, 'verified_at' => $NOW, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 6, 'document_type' => 'pan', 'document_number' => 'XYZPQ5678R', 'file_path' => '/uploads/users/pan/6.pdf', 'file_type' => 'pdf', 'file_size' => 180000, 'verification_status' => 'pending', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 10, 'document_type' => 'bank_statement', 'document_number' => 'ACC-12345678', 'file_path' => '/uploads/users/bank/10.pdf', 'file_type' => 'pdf', 'file_size' => 520000, 'verification_status' => 'verified', 'verified_by' => 1, 'verified_at' => $NOW, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  EMPLOYEE TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'company_employees', [
    ['company_id' => 1, 'user_id' => 7, 'position' => 'Sales Manager', 'salary' => 60000, 'join_date' => '2025-06-01', 'status' => 'active'],
    ['company_id' => 1, 'user_id' => 64, 'position' => 'Land Acquisition Manager', 'salary' => 75000, 'join_date' => '2025-08-15', 'status' => 'active'],
    ['company_id' => 1, 'user_id' => 89, 'position' => 'Accountant', 'salary' => 45000, 'join_date' => '2026-01-10', 'status' => 'active'],
]);
seedTable($pdo, 'employee_activities', [
    ['employee_id' => 1, 'activity' => 'Logged into system', 'activity_type' => 'login', 'created_at' => $NOW, 'ip_address' => '192.168.1.10', 'user_agent' => 'Mozilla/5.0'],
    ['employee_id' => 1, 'activity' => 'Created new lead Rahul Sharma', 'activity_type' => 'lead', 'created_at' => $NOW, 'ip_address' => '192.168.1.10', 'user_agent' => 'Mozilla/5.0'],
    ['employee_id' => 1, 'activity' => 'Updated plot A-002 status to sold', 'activity_type' => 'update', 'created_at' => $NOW, 'ip_address' => '192.168.1.10', 'user_agent' => 'Mozilla/5.0'],
    ['employee_id' => 2, 'activity' => 'Processed land document for farmer 1', 'activity_type' => 'document', 'created_at' => $NOW, 'ip_address' => '192.168.1.20', 'user_agent' => 'Mozilla/5.0'],
    ['employee_id' => 3, 'activity' => 'Generated monthly financial report', 'activity_type' => 'report', 'created_at' => $NOW, 'ip_address' => '192.168.1.30', 'user_agent' => 'Mozilla/5.0'],
]);
seedTable($pdo, 'employee_advances', [
    ['employee_id' => 1, 'advance_number' => 'ADV-2026-001', 'advance_amount' => 25000, 'advance_date' => '2026-04-01', 'reason' => 'Medical emergency', 'repayment_method' => 'salary_deduction', 'installment_amount' => 5000, 'total_installments' => 5, 'paid_installments' => 2, 'outstanding_amount' => 15000, 'status' => 'approved', 'approved_by' => 68, 'approved_at' => $NOW, 'disbursement_date' => '2026-04-05', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 2, 'advance_number' => 'ADV-2026-002', 'advance_amount' => 50000, 'advance_date' => '2026-05-01', 'reason' => 'Home renovation', 'repayment_method' => 'salary_deduction', 'installment_amount' => 10000, 'total_installments' => 5, 'paid_installments' => 1, 'outstanding_amount' => 40000, 'status' => 'approved', 'approved_by' => 68, 'approved_at' => $NOW, 'disbursement_date' => '2026-05-05', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 3, 'advance_number' => 'ADV-2026-003', 'advance_amount' => 15000, 'advance_date' => '2026-05-15', 'reason' => 'Travel advance for fieldwork', 'repayment_method' => 'salary_deduction', 'installment_amount' => 3000, 'total_installments' => 5, 'paid_installments' => 0, 'outstanding_amount' => 15000, 'status' => 'pending', 'approved_by' => null, 'approved_at' => null, 'disbursement_date' => null, 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'employee_bonuses', [
    ['employee_id' => 1, 'bonus_number' => 'BON-2026-001', 'bonus_type' => 'performance', 'bonus_amount' => 20000, 'bonus_month' => 3, 'bonus_year' => 2026, 'reason' => 'Q1 2026 performance exceeded targets', 'payment_status' => 'paid', 'payment_date' => '2026-04-15', 'transaction_id' => 'TXN-BON-001', 'approved_by' => 68, 'approved_at' => $NOW, 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 2, 'bonus_number' => 'BON-2026-002', 'bonus_type' => 'diwali', 'bonus_amount' => 15000, 'bonus_month' => 10, 'bonus_year' => 2026, 'reason' => 'Festival bonus', 'payment_status' => 'approved', 'payment_date' => null, 'transaction_id' => null, 'approved_by' => 68, 'approved_at' => $NOW, 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 3, 'bonus_number' => 'BON-2026-003', 'bonus_type' => 'spot', 'bonus_amount' => 5000, 'bonus_month' => 5, 'bonus_year' => 2026, 'reason' => 'Extra effort on month-end closing', 'payment_status' => 'paid', 'payment_date' => '2026-05-31', 'transaction_id' => 'TXN-BON-002', 'approved_by' => 68, 'approved_at' => $NOW, 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'employee_kpis', [
    ['employee_id' => 1, 'kpi_id' => 1, 'period_start' => '2026-01-01', 'period_end' => '2026-03-31', 'target_value' => 5000000, 'actual_value' => 6200000, 'achievement_percentage' => 124.00, 'score' => 9.5, 'status' => 'achieved', 'comments' => 'Exceeded sales target by 24%', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 2, 'kpi_id' => 2, 'period_start' => '2026-01-01', 'period_end' => '2026-03-31', 'target_value' => 10, 'actual_value' => 8, 'achievement_percentage' => 80.00, 'score' => 7.0, 'status' => 'partial', 'comments' => 'Acquired 8 out of 10 target land parcels', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 3, 'kpi_id' => 1, 'period_start' => '2026-04-01', 'period_end' => '2026-06-30', 'target_value' => 100, 'actual_value' => 45, 'achievement_percentage' => 45.00, 'score' => 4.0, 'status' => 'below_target', 'comments' => 'In progress - mid quarter review', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'employee_salary_structure', [
    ['employee_id' => 1, 'basic_salary' => 25000, 'hra' => 12500, 'da' => 5000, 'ta' => 2000, 'medical_allowance' => 1500, 'special_allowance' => 4000, 'other_allowance' => 3000, 'pf_deduction' => 3000, 'esi_deduction' => 500, 'professional_tax' => 200, 'tds_deduction' => 2000, 'other_deduction' => 500, 'gross_salary' => 53000, 'net_salary' => 46800, 'effective_from' => '2026-04-01', 'effective_to' => null, 'is_active' => 1, 'approved_by' => 68, 'approved_at' => $NOW, 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 2, 'basic_salary' => 35000, 'hra' => 17500, 'da' => 7000, 'ta' => 3000, 'medical_allowance' => 1500, 'special_allowance' => 6000, 'other_allowance' => 4500, 'pf_deduction' => 4200, 'esi_deduction' => 700, 'professional_tax' => 200, 'tds_deduction' => 3500, 'other_deduction' => 600, 'gross_salary' => 74500, 'net_salary' => 65300, 'effective_from' => '2026-04-01', 'effective_to' => null, 'is_active' => 1, 'approved_by' => 68, 'approved_at' => $NOW, 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 3, 'basic_salary' => 18000, 'hra' => 9000, 'da' => 3600, 'ta' => 1500, 'medical_allowance' => 1500, 'special_allowance' => 3000, 'other_allowance' => 2000, 'pf_deduction' => 2160, 'esi_deduction' => 360, 'professional_tax' => 200, 'tds_deduction' => 1000, 'other_deduction' => 300, 'gross_salary' => 38600, 'net_salary' => 34580, 'effective_from' => '2026-01-01', 'effective_to' => null, 'is_active' => 1, 'approved_by' => 68, 'approved_at' => $NOW, 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'employee_shifts', [
    ['employee_id' => 1, 'shift_type_id' => 1, 'shift_date' => '2026-05-01', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'actual_start_time' => '09:05:00', 'actual_end_time' => '18:10:00', 'duration_hours' => 9.0, 'status' => 'completed', 'notes' => 'On time', 'assigned_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 2, 'shift_type_id' => 2, 'shift_date' => '2026-05-01', 'start_time' => '14:00:00', 'end_time' => '23:00:00', 'actual_start_time' => '14:00:00', 'actual_end_time' => '23:15:00', 'duration_hours' => 9.0, 'status' => 'completed', 'notes' => 'Field visit extended', 'assigned_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 3, 'shift_type_id' => 3, 'shift_date' => '2026-05-01', 'start_time' => '23:00:00', 'end_time' => '08:00:00', 'actual_start_time' => null, 'actual_end_time' => null, 'duration_hours' => 9.0, 'status' => 'absent', 'notes' => 'Employee called in sick', 'assigned_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'employee_tasks', [
    ['employee_id' => 1, 'title' => 'Prepare Q2 sales report', 'description' => 'Compile all sales data for Q2 2026 and create presentation', 'priority' => 'high', 'status' => 'in_progress', 'due_date' => '2026-06-30', 'created_at' => $NOW, 'updated_at' => $NOW, 'created_by' => 68],
    ['employee_id' => 1, 'title' => 'Client meeting - Sharma family', 'description' => 'Meet with Sharma family for plot A-015 site visit', 'priority' => 'high', 'status' => 'pending', 'due_date' => '2026-06-05', 'created_at' => $NOW, 'updated_at' => $NOW, 'created_by' => 68],
    ['employee_id' => 2, 'title' => 'Land valuation report', 'description' => 'Complete valuation of proposed land parcel in Deoria', 'priority' => 'medium', 'status' => 'in_progress', 'due_date' => '2026-06-15', 'created_at' => $NOW, 'updated_at' => $NOW, 'created_by' => 68],
    ['employee_id' => 3, 'title' => 'Tax filing - Q1', 'description' => 'File GST returns for Q1 2026', 'priority' => 'high', 'status' => 'completed', 'due_date' => '2026-05-20', 'created_at' => $NOW, 'updated_at' => $NOW, 'created_by' => 68, 'completed_at' => '2026-05-18 16:30:00', 'completed_by' => 3],
    ['employee_id' => 3, 'title' => 'Monthly expense reconciliation', 'description' => 'Reconcile all May 2026 expenses', 'priority' => 'low', 'status' => 'pending', 'due_date' => '2026-06-10', 'created_at' => $NOW, 'updated_at' => $NOW, 'created_by' => 68],
]);
echo "\n============================================================\n";
echo "  HRM & TRAINING TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'payroll_runs', [
    ['run_name' => 'May 2026 Salary Run', 'pay_period_start' => '2026-05-01', 'pay_period_end' => '2026-05-31', 'pay_date' => '2026-06-01', 'status' => 'pending', 'total_employees' => 3, 'total_gross' => 166100, 'total_net' => 146680, 'total_deductions' => 19420, 'processed_by' => 68, 'notes' => 'Regular monthly payroll', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['run_name' => 'April 2026 Salary Run', 'pay_period_start' => '2026-04-01', 'pay_period_end' => '2026-04-30', 'pay_date' => '2026-05-01', 'status' => 'completed', 'total_employees' => 3, 'total_gross' => 166100, 'total_net' => 146680, 'total_deductions' => 19420, 'processed_by' => 68, 'approved_by' => 1, 'approved_at' => '2026-04-30 14:00:00', 'notes' => 'Processed successfully', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['run_name' => 'March 2026 Salary Run', 'pay_period_start' => '2026-03-01', 'pay_period_end' => '2026-03-31', 'pay_date' => '2026-04-01', 'status' => 'completed', 'total_employees' => 2, 'total_gross' => 127500, 'total_net' => 112100, 'total_deductions' => 15400, 'processed_by' => 68, 'approved_by' => 1, 'approved_at' => '2026-03-31 15:00:00', 'notes' => 'Two employees - one joined late', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'payroll_entries', [
    ['payroll_run_id' => 1, 'employee_id' => 1, 'basic_salary' => 25000, 'house_rent_allowance' => 12500, 'conveyance_allowance' => 2000, 'medical_allowance' => 1500, 'special_allowance' => 4000, 'other_allowances' => 3000, 'overtime_hours' => 0, 'overtime_rate' => 0, 'overtime_amount' => 0, 'provident_fund' => 3000, 'professional_tax' => 200, 'income_tax' => 2000, 'other_deductions' => 500, 'gross_earnings' => 53000, 'total_deductions' => 5700, 'net_salary' => 47300, 'payment_status' => 'pending', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['payroll_run_id' => 1, 'employee_id' => 2, 'basic_salary' => 35000, 'house_rent_allowance' => 17500, 'conveyance_allowance' => 3000, 'medical_allowance' => 1500, 'special_allowance' => 6000, 'other_allowances' => 4500, 'overtime_hours' => 8, 'overtime_rate' => 250, 'overtime_amount' => 2000, 'provident_fund' => 4200, 'professional_tax' => 200, 'income_tax' => 3500, 'other_deductions' => 600, 'gross_earnings' => 76500, 'total_deductions' => 8500, 'net_salary' => 68000, 'payment_status' => 'pending', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['payroll_run_id' => 2, 'employee_id' => 3, 'basic_salary' => 18000, 'house_rent_allowance' => 9000, 'conveyance_allowance' => 1500, 'medical_allowance' => 1500, 'special_allowance' => 3000, 'other_allowances' => 2000, 'overtime_hours' => 0, 'overtime_rate' => 0, 'overtime_amount' => 0, 'provident_fund' => 2160, 'professional_tax' => 200, 'income_tax' => 1000, 'other_deductions' => 300, 'gross_earnings' => 38600, 'total_deductions' => 3660, 'net_salary' => 34940, 'payment_status' => 'paid', 'payment_date' => '2026-05-01', 'payment_method' => 'bank_transfer', 'bank_reference' => 'SAL-MAR-003', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'salary_payments', [
    ['employee_id' => 1, 'salary_structure_id' => 1, 'payment_month' => 5, 'payment_year' => 2026, 'payment_date' => $NOW, 'basic_amount' => 25000, 'allowance_amount' => 28000, 'gross_amount' => 53000, 'deduction_amount' => 5700, 'net_amount' => 47300, 'payment_method' => 'bank_transfer', 'transaction_id' => 'TXN-SAL-001', 'payment_status' => 'pending', 'payment_processed_by' => 68, 'remarks' => 'May 2026 salary', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 2, 'salary_structure_id' => 2, 'payment_month' => 5, 'payment_year' => 2026, 'payment_date' => $NOW, 'basic_amount' => 35000, 'allowance_amount' => 41500, 'gross_amount' => 76500, 'deduction_amount' => 8500, 'net_amount' => 68000, 'payment_method' => 'bank_transfer', 'transaction_id' => 'TXN-SAL-002', 'payment_status' => 'pending', 'payment_processed_by' => 68, 'remarks' => 'May 2026 salary with overtime', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['employee_id' => 3, 'salary_structure_id' => 3, 'payment_month' => 4, 'payment_year' => 2026, 'payment_date' => '2026-05-01', 'basic_amount' => 18000, 'allowance_amount' => 20600, 'gross_amount' => 38600, 'deduction_amount' => 3660, 'net_amount' => 34940, 'payment_method' => 'bank_transfer', 'transaction_id' => 'TXN-SAL-003', 'bank_reference' => 'SAL-APR-003', 'payment_status' => 'paid', 'payment_processed_by' => 68, 'payment_processed_at' => '2026-05-01 10:00:00', 'remarks' => 'April 2026 salary', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'training_enrollments', [
    ['user_id' => 53, 'course_id' => 1, 'enrolled_at' => '2026-04-01', 'completed_at' => null, 'progress_percentage' => 65, 'status' => 'in_progress', 'current_lesson_id' => 4, 'last_accessed_at' => $NOW, 'certificate_issued' => 0, 'final_score' => null, 'attempts_count' => 2, 'notes' => 'Progressing well', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 54, 'course_id' => 1, 'enrolled_at' => '2026-03-15', 'completed_at' => '2026-04-20', 'progress_percentage' => 100, 'status' => 'completed', 'current_lesson_id' => 8, 'last_accessed_at' => '2026-04-20 14:00:00', 'certificate_issued' => 1, 'certificate_url' => '/uploads/certificates/cert_001.pdf', 'final_score' => 92, 'attempts_count' => 1, 'notes' => 'Completed with distinction', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 2, 'course_id' => 2, 'enrolled_at' => '2026-05-01', 'completed_at' => null, 'progress_percentage' => 25, 'status' => 'in_progress', 'current_lesson_id' => 2, 'last_accessed_at' => $NOW, 'certificate_issued' => 0, 'final_score' => null, 'attempts_count' => 1, 'deadline_at' => '2026-08-01', 'notes' => 'New enrollment', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'training_certificates', [
    ['associate_id' => 54, 'certificate_type' => 'sales_training', 'issued_date' => '2026-04-20', 'certificate_url' => '/uploads/certificates/sales_001.pdf'],
    ['associate_id' => 53, 'certificate_type' => 'product_knowledge', 'issued_date' => '2026-03-15', 'certificate_url' => '/uploads/certificates/product_001.pdf'],
    ['associate_id' => 2, 'certificate_type' => 'compliance', 'issued_date' => '2026-01-10', 'certificate_url' => '/uploads/certificates/compliance_001.pdf'],
]);
echo "\n============================================================\n";
echo "  LEGAL & KYC & GST TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'legal_disputes', [
    ['title' => 'Boundary dispute - Plot B-045', 'description' => 'Neighboring landowner claims 2 feet encroachment on plot boundary', 'party_a' => 'APS Dream Homes', 'party_b' => 'Mr. Suresh Verma', 'dispute_type' => 'boundary', 'status' => 'in_mediation', 'assigned_to' => 89, 'filed_date' => '2026-04-10', 'notes' => 'Survey ordered to verify boundaries', 'created_at' => $NOW],
    ['title' => 'Title clearance - Land Parcel Deoria', 'description' => 'Title deed verification for proposed land acquisition in Deoria district', 'party_a' => 'APS Dream Homes', 'party_b' => 'Gram Panchayat Deoria', 'dispute_type' => 'title', 'status' => 'open', 'assigned_to' => 89, 'filed_date' => '2026-05-05', 'notes' => 'Awaiting revenue records from Tehsildar', 'created_at' => $NOW],
    ['title' => 'Property tax assessment dispute', 'description' => 'Municipal corporation reassessed property tax at higher rate than applicable', 'party_a' => 'APS Dream Homes', 'party_b' => 'Gorakhpur Municipal Corporation', 'dispute_type' => 'tax', 'status' => 'resolved', 'assigned_to' => 89, 'filed_date' => '2026-02-01', 'resolved_date' => '2026-03-15', 'notes' => 'Resolved in favor of APS Dream Homes', 'created_at' => $NOW],
]);
seedTable($pdo, 'legal_deadlines', [
    ['title' => 'GST Return Filing - Q1', 'description' => 'File quarterly GST returns before deadline', 'legal_type' => 'compliance', 'deadline_date' => '2026-07-20', 'assigned_to' => 89, 'status' => 'upcoming', 'created_at' => $NOW],
    ['title' => 'RERA Annual Compliance', 'description' => 'Submit annual RERA compliance report for all projects', 'legal_type' => 'regulatory', 'deadline_date' => '2026-12-31', 'assigned_to' => 89, 'status' => 'upcoming', 'created_at' => $NOW],
    ['title' => 'Contract renewal - Security Services', 'description' => 'Renew annual contract with security service provider', 'legal_type' => 'contract', 'deadline_date' => '2026-08-15', 'assigned_to' => 89, 'status' => 'upcoming', 'created_at' => $NOW],
]);
seedTable($pdo, 'legal_activities', [
    ['legal_case_id' => 1, 'activity_type' => 'hearing', 'description' => 'Preliminary hearing completed - next hearing scheduled', 'user_id' => 89, 'created_at' => $NOW],
    ['legal_case_id' => 1, 'activity_type' => 'document_filing', 'description' => 'Filed boundary survey report with court', 'user_id' => 89, 'created_at' => $NOW],
    ['legal_case_id' => 2, 'activity_type' => 'meeting', 'description' => 'Meeting with Gram Panchayat secretary', 'user_id' => 89, 'created_at' => $NOW],
]);
seedTable($pdo, 'kyc_verification', [
    ['associate_id' => 53, 'aadhar_doc' => '/uploads/kyc/aadhar_53.pdf', 'pan_doc' => '/uploads/kyc/pan_53.pdf', 'address_doc' => '/uploads/kyc/address_53.pdf', 'status' => 'verified', 'submitted_at' => '2026-04-01', 'verified_by' => 1, 'verified_at' => '2026-04-05', 'verification_notes' => 'All documents verified successfully'],
    ['associate_id' => 54, 'aadhar_doc' => '/uploads/kyc/aadhar_54.pdf', 'pan_doc' => '/uploads/kyc/pan_54.pdf', 'address_doc' => '/uploads/kyc/address_54.pdf', 'status' => 'pending', 'submitted_at' => '2026-05-10', 'verified_by' => null, 'verified_at' => null, 'verification_notes' => null],
    ['associate_id' => 2, 'aadhar_doc' => '/uploads/kyc/aadhar_2.pdf', 'pan_doc' => '/uploads/kyc/pan_2.pdf', 'address_doc' => null, 'status' => 'submitted', 'submitted_at' => '2026-05-20', 'verified_by' => null, 'verified_at' => null, 'verification_notes' => 'Address proof pending submission'],
]);
seedTable($pdo, 'gst_returns', [
    ['return_type' => 'GSTR-1', 'period_from' => '2026-01-01', 'period_to' => '2026-03-31', 'filing_date' => '2026-04-10', 'due_date' => '2026-04-20', 'status' => 'filed', 'arn_no' => 'ARN-2026-Q1-001', 'remarks' => 'Filed on time', 'filed_by' => 89, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['return_type' => 'GSTR-3B', 'period_from' => '2026-04-01', 'period_to' => '2026-04-30', 'filing_date' => null, 'due_date' => '2026-05-20', 'status' => 'pending', 'arn_no' => null, 'remarks' => 'Awaiting data from accounts', 'filed_by' => 89, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['return_type' => 'GSTR-9', 'period_from' => '2025-04-01', 'period_to' => '2026-03-31', 'filing_date' => '2026-05-15', 'due_date' => '2026-06-30', 'status' => 'filed', 'arn_no' => 'ARN-2025-ANNUAL-001', 'remarks' => 'Annual return filed early', 'filed_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  LEAD & LOCATION TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'lead_scoring', [
    ['lead_id' => 1, 'score' => 85, 'criteria' => 'budget_interest_engagement', 'created_at' => $NOW, 'updated_at' => $NOW, 'breakdown_json' => '{"budget":30,"interest":25,"engagement":20,"profile":10}'],
    ['lead_id' => 2, 'score' => 60, 'criteria' => 'budget_interest', 'created_at' => $NOW, 'updated_at' => $NOW, 'breakdown_json' => '{"budget":20,"interest":25,"engagement":5,"profile":10}'],
    ['lead_id' => 3, 'score' => 45, 'criteria' => 'interest_only', 'created_at' => $NOW, 'updated_at' => $NOW, 'breakdown_json' => '{"budget":10,"interest":20,"engagement":5,"profile":10}'],
]);
seedTable($pdo, 'lead_status_history', [
    ['lead_id' => 1, 'old_status' => 'new', 'new_status' => 'contacted', 'changed_by' => 63, 'notes' => 'Initial call made', 'changed_at' => $NOW],
    ['lead_id' => 1, 'old_status' => 'contacted', 'new_status' => 'qualified', 'changed_by' => 63, 'notes' => 'Lead is interested and has budget', 'changed_at' => $NOW],
    ['lead_id' => 2, 'old_status' => 'new', 'new_status' => 'contacted', 'changed_by' => 63, 'notes' => 'Sent introductory email', 'changed_at' => $NOW],
    ['lead_id' => 3, 'old_status' => 'new', 'new_status' => 'contacted', 'changed_by' => 63, 'notes' => 'Called and left voicemail', 'changed_at' => $NOW],
    ['lead_id' => 3, 'old_status' => 'contacted', 'new_status' => 'qualified', 'changed_by' => 63, 'notes' => 'Lead interested in villa project', 'changed_at' => $NOW],
]);
seedTable($pdo, 'lead_events', [
    ['lead_id' => 1, 'event_type' => 'page_view', 'event_data' => '{"page":"/properties","duration":120}', 'source_page' => '/properties', 'ip_address' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0', 'created_at' => $NOW],
    ['lead_id' => 1, 'event_type' => 'form_submit', 'event_data' => '{"form":"inquiry","property_id":1}', 'source_page' => '/properties/1', 'ip_address' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0', 'created_at' => $NOW],
    ['lead_id' => 2, 'event_type' => 'search', 'event_data' => '{"query":"plot in Gorakhpur","budget":"30-50 lakhs"}', 'source_page' => '/properties', 'ip_address' => '192.168.1.101', 'user_agent' => 'Mozilla/5.0', 'created_at' => $NOW],
    ['lead_id' => 3, 'event_type' => 'page_view', 'event_data' => '{"page":"/projects/2","duration":300}', 'source_page' => '/projects/2', 'ip_address' => '192.168.1.102', 'user_agent' => 'Mozilla/5.0', 'created_at' => $NOW],
    ['lead_id' => 3, 'event_type' => 'phone_call', 'event_data' => '{"duration_sec":480,"outcome":"interested"}', 'source_page' => null, 'ip_address' => '192.168.1.102', 'user_agent' => null, 'created_at' => $NOW],
]);
seedTable($pdo, 'marketing_leads', [
    ['first_name' => 'Vikram', 'last_name' => 'Singh', 'email' => 'vikram@example.com', 'phone' => '9876543301', 'source' => 'facebook', 'status' => 'new', 'score' => 35, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['first_name' => 'Anita', 'last_name' => 'Sharma', 'email' => 'anita@example.com', 'phone' => '9876543302', 'source' => 'google', 'status' => 'contacted', 'score' => 50, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['first_name' => 'Ravi', 'last_name' => 'Kumar', 'email' => 'ravi@example.com', 'phone' => '9876543303', 'source' => 'referral', 'company' => 'Infosys', 'position' => 'Manager', 'status' => 'qualified', 'score' => 70, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'land_allocations', [
    ['farmer_id' => 1, 'customer_id' => 3, 'plot_id' => 11, 'allocation_date' => '2026-04-01', 'allocation_type' => 'sale', 'total_area' => 1000, 'area_unit' => 'sqft', 'unit_price' => 2500, 'total_amount' => 2500000, 'payment_terms' => '50% upfront, 50% in 6 months', 'status' => 'active', 'approved_by' => 68, 'approved_at' => $NOW, 'allocation_document' => '/uploads/allocations/alloc_001.pdf', 'remarks' => 'Standard sale agreement', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['farmer_id' => 2, 'customer_id' => 6, 'plot_id' => 12, 'allocation_date' => '2026-04-15', 'allocation_type' => 'lease', 'total_area' => 1000, 'area_unit' => 'sqft', 'unit_price' => 500, 'total_amount' => 500000, 'payment_terms' => 'Annual lease payment', 'status' => 'active', 'approved_by' => 68, 'approved_at' => $NOW, 'remarks' => '5-year lease agreement', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['farmer_id' => 3, 'customer_id' => 10, 'plot_id' => 13, 'allocation_date' => '2026-05-01', 'allocation_type' => 'sale', 'total_area' => 1200, 'area_unit' => 'sqft', 'unit_price' => 2500, 'total_amount' => 3000000, 'payment_terms' => 'Full payment within 3 months', 'status' => 'pending', 'approved_by' => null, 'approved_at' => null, 'remarks' => 'Awaiting approval', 'created_by' => 68, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'plot_allocations', [
    ['plot_id' => 11, 'customer_id' => 3, 'allocation_date' => '2026-05-01', 'agreement_number' => 'AGR-2026-001', 'agreement_date' => '2026-05-01', 'registry_number' => 'REG-2026-001', 'registry_date' => '2026-05-15', 'registry_amount' => 250000, 'plot_rate' => 2500, 'total_plot_value' => 2500000, 'development_charges' => 500000, 'total_amount' => 3000000, 'amount_paid' => 500000, 'amount_pending' => 2500000, 'possession_date' => null, 'possession_status' => 'pending', 'status' => 'active', 'notes' => 'EMI payment plan active', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['plot_id' => 12, 'customer_id' => 6, 'allocation_date' => '2026-05-10', 'agreement_number' => 'AGR-2026-002', 'agreement_date' => '2026-05-10', 'registry_number' => null, 'registry_date' => null, 'registry_amount' => 0, 'plot_rate' => 2500, 'total_plot_value' => 2500000, 'development_charges' => 500000, 'total_amount' => 3000000, 'amount_paid' => 30000, 'amount_pending' => 2970000, 'possession_date' => null, 'possession_status' => 'pending', 'status' => 'active', 'notes' => 'Down payment plan', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['plot_id' => 13, 'customer_id' => 10, 'allocation_date' => '2026-05-15', 'agreement_number' => 'AGR-2026-003', 'agreement_date' => '2026-05-15', 'registry_number' => null, 'registry_date' => null, 'registry_amount' => 0, 'plot_rate' => 2500, 'total_plot_value' => 3600000, 'development_charges' => 600000, 'total_amount' => 4200000, 'amount_paid' => 100000, 'amount_pending' => 4100000, 'possession_date' => null, 'possession_status' => 'pending', 'status' => 'active', 'notes' => '24-month EMI plan', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  MLM TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'mlm_plan_levels', [
    ['plan_id' => 6, 'level_name' => 'Bronze', 'level_order' => 1, 'direct_commission' => 5.00, 'team_commission' => 2.00, 'level_bonus' => 0, 'matching_bonus' => 0, 'leadership_bonus' => 0, 'performance_bonus' => 0, 'monthly_target' => 100000, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['plan_id' => 6, 'level_name' => 'Silver', 'level_order' => 2, 'direct_commission' => 7.00, 'team_commission' => 3.00, 'level_bonus' => 5000, 'matching_bonus' => 1.00, 'leadership_bonus' => 0, 'performance_bonus' => 0, 'monthly_target' => 250000, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['plan_id' => 6, 'level_name' => 'Gold', 'level_order' => 3, 'direct_commission' => 10.00, 'team_commission' => 4.00, 'level_bonus' => 15000, 'matching_bonus' => 2.00, 'leadership_bonus' => 1.00, 'performance_bonus' => 5000, 'monthly_target' => 500000, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['plan_id' => 6, 'level_name' => 'Platinum', 'level_order' => 4, 'direct_commission' => 12.00, 'team_commission' => 5.00, 'level_bonus' => 30000, 'matching_bonus' => 3.00, 'leadership_bonus' => 2.00, 'performance_bonus' => 10000, 'monthly_target' => 1000000, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'mlm_referrals', [
    ['referrer_user_id' => 53, 'associate_id' => 54, 'referred_user_id' => 55, 'customer_id' => 55, 'referral_type' => 'associate', 'channel' => 'direct', 'commission_amount' => 5000, 'status' => 'completed', 'notes' => 'Direct referral from senior associate', 'referral_code' => 'REF-53-001', 'created_at' => $NOW, 'converted_at' => $NOW],
    ['referrer_user_id' => 53, 'associate_id' => 54, 'referred_user_id' => 56, 'customer_id' => 56, 'referral_type' => 'customer', 'channel' => 'social', 'commission_amount' => 2500, 'status' => 'completed', 'notes' => 'Facebook campaign referral', 'referral_code' => 'REF-53-002', 'created_at' => $NOW, 'converted_at' => $NOW],
    ['referrer_user_id' => 2, 'referred_user_id' => 58, 'customer_id' => 58, 'referral_type' => 'customer', 'channel' => 'direct', 'commission_amount' => 0, 'status' => 'pending', 'notes' => 'New referral - not yet converted', 'referral_code' => 'REF-02-001', 'created_at' => $NOW, 'converted_at' => null],
]);
seedTable($pdo, 'mlm_rank_criteria', [
    ['rank' => 'Bronze', 'min_monthly_sales' => 100000, 'min_team_size' => 3, 'min_active_downlines' => 2, 'min_monthly_commission' => 5000, 'created_at' => $NOW],
    ['rank' => 'Silver', 'min_monthly_sales' => 500000, 'min_team_size' => 10, 'min_active_downlines' => 5, 'min_monthly_commission' => 25000, 'created_at' => $NOW],
    ['rank' => 'Gold', 'min_monthly_sales' => 2000000, 'min_team_size' => 25, 'min_active_downlines' => 15, 'min_monthly_commission' => 100000, 'created_at' => $NOW],
]);
seedTable($pdo, 'mlm_withdrawal_requests', [
    ['associate_id' => 53, 'amount' => 25000, 'payment_method' => 'bank_transfer', 'available_balance' => 75000, 'status' => 'approved', 'request_date' => '2026-05-01', 'processed_date' => '2026-05-03', 'admin_notes' => 'Processed via NEFT', 'created_at' => $NOW],
    ['associate_id' => 54, 'amount' => 50000, 'payment_method' => 'bank_transfer', 'available_balance' => 120000, 'status' => 'pending', 'request_date' => '2026-05-15', 'processed_date' => null, 'admin_notes' => null, 'created_at' => $NOW],
    ['associate_id' => 2, 'amount' => 10000, 'payment_method' => 'upi', 'available_balance' => 15000, 'status' => 'paid', 'request_date' => '2026-04-20', 'processed_date' => '2026-04-22', 'admin_notes' => 'UPI transfer completed', 'created_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  PAYMENT TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'payment_gateway_config', [
    ['provider' => 'razorpay', 'api_key' => 'rzp_live_xxxxxxxxxxxx', 'api_secret' => 'rzp_secret_xxxxxxxxxxxx', 'created_at' => $NOW],
]);
seedTable($pdo, 'payment_receipts', [
    ['payment_id' => 1, 'receipt_number' => 'RCP-2026-001', 'receipt_url' => '/uploads/receipts/rcp_001.pdf', 'generated_at' => $NOW],
    ['payment_id' => 2, 'receipt_number' => 'RCP-2026-002', 'receipt_url' => '/uploads/receipts/rcp_002.pdf', 'generated_at' => $NOW],
    ['payment_id' => 1, 'receipt_number' => 'RCP-2026-003', 'receipt_url' => '/uploads/receipts/rcp_003.pdf', 'generated_at' => $NOW],
]);
seedTable($pdo, 'payment_orders', [
    ['razorpay_order_id' => 'order_ABC123', 'amount' => 50000, 'currency' => 'INR', 'receipt' => 'RCP-001', 'status' => 'paid', 'razorpay_payment_id' => 'pay_ABC123', 'payment_method' => 'upi', 'payment_status' => 'captured', 'created_at' => $NOW, 'updated_at' => $NOW, 'paid_at' => $NOW],
    ['razorpay_order_id' => 'order_DEF456', 'amount' => 100000, 'currency' => 'INR', 'receipt' => 'RCP-002', 'status' => 'paid', 'razorpay_payment_id' => 'pay_DEF456', 'payment_method' => 'card', 'payment_status' => 'captured', 'created_at' => $NOW, 'updated_at' => $NOW, 'paid_at' => $NOW],
    ['razorpay_order_id' => 'order_GHI789', 'amount' => 25000, 'currency' => 'INR', 'receipt' => 'RCP-003', 'status' => 'created', 'razorpay_payment_id' => null, 'payment_method' => null, 'payment_status' => 'pending', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  PROPERTY TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'property_favorites', [
    ['user_id' => 3, 'property_id' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 3, 'property_id' => 3, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 6, 'property_id' => 4, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 10, 'property_id' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 15, 'property_id' => 3, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'property_inquiries', [
    ['property_id' => 1, 'name' => 'Rahul Sharma', 'email' => 'rahul@example.com', 'phone' => '9876543210', 'message' => 'Is this property still available? I am interested in site visit.', 'status' => 'read', 'created_at' => $NOW],
    ['property_id' => 3, 'name' => 'Priya Singh', 'email' => 'priya@example.com', 'phone' => '9876543211', 'message' => 'Can you share more details about the payment plan?', 'status' => 'new', 'created_at' => $NOW],
    ['property_id' => 4, 'name' => 'Amit Kumar', 'email' => 'amit@example.com', 'phone' => '9876543212', 'message' => 'What are the possession timelines for this property?', 'status' => 'new', 'created_at' => $NOW],
]);
seedTable($pdo, 'property_maintenance', [
    ['property_id' => 1, 'plot_id' => 11, 'issue_type' => 'plumbing', 'description' => 'Water pipe leakage in main bathroom', 'priority' => 'high', 'status' => 'in_progress', 'assigned_to' => 1, 'created_at' => $NOW, 'completed_at' => null],
    ['property_id' => 3, 'issue_type' => 'electrical', 'description' => 'Main switchboard needs replacement', 'priority' => 'medium', 'status' => 'pending', 'assigned_to' => 1, 'created_at' => $NOW, 'completed_at' => null],
    ['property_id' => 4, 'issue_type' => 'cleaning', 'description' => 'Deep cleaning required before handover', 'priority' => 'low', 'status' => 'completed', 'assigned_to' => 1, 'created_at' => $NOW, 'completed_at' => $NOW],
]);
seedTable($pdo, 'property_ratings', [
    ['user_id' => 3, 'property_id' => 1, 'rating' => 4.5, 'review_text' => 'Excellent property with great location', 'rating_criteria' => '{"location":5,"price":4,"amenities":4,"construction":5}', 'is_verified_viewing' => 1, 'helpful_votes' => 3, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 6, 'property_id' => 3, 'rating' => 3.5, 'review_text' => 'Good value for money, but location could be better', 'rating_criteria' => '{"location":3,"price":4,"amenities":3,"construction":4}', 'is_verified_viewing' => 0, 'helpful_votes' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 10, 'property_id' => 4, 'rating' => 5.0, 'review_text' => 'Absolutely love this property! Highly recommended.', 'rating_criteria' => '{"location":5,"price":5,"amenities":5,"construction":5}', 'is_verified_viewing' => 1, 'helpful_votes' => 7, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'property_reviews', [
    ['customer_id' => 3, 'property_id' => 1, 'rating' => 4, 'review_text' => 'Great investment opportunity in a growing area.', 'anonymous' => 0, 'status' => 'approved', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['customer_id' => 6, 'property_id' => 3, 'rating' => 3, 'review_text' => 'Decent property but needs some improvements.', 'anonymous' => 1, 'status' => 'approved', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['customer_id' => 10, 'property_id' => 4, 'rating' => 5, 'review_text' => 'Best investment I have made! Excellent customer service.', 'anonymous' => 0, 'status' => 'pending', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'property_views', [
    ['customer_id' => 3, 'property_id' => 1, 'session_id' => 'sess_abc001', 'view_duration' => 120, 'viewed_at' => $NOW],
    ['customer_id' => 6, 'property_id' => 1, 'session_id' => 'sess_abc002', 'view_duration' => 45, 'viewed_at' => $NOW],
    ['customer_id' => 10, 'property_id' => 3, 'session_id' => 'sess_abc003', 'view_duration' => 200, 'viewed_at' => $NOW],
    ['customer_id' => 15, 'property_id' => 4, 'session_id' => 'sess_abc004', 'view_duration' => 90, 'viewed_at' => $NOW],
    ['customer_id' => 22, 'property_id' => 4, 'session_id' => 'sess_abc005', 'view_duration' => 150, 'viewed_at' => $NOW],
]);
seedTable($pdo, 'property_sales', [
    ['property_id' => 1, 'buyer_id' => 3, 'agent_id' => 2, 'sale_amount' => 7000000, 'commission_amount' => 140000, 'commission_distributed' => 1, 'sale_date' => '2026-04-15', 'created_at' => $NOW],
    ['property_id' => 3, 'buyer_id' => 6, 'agent_id' => 54, 'sale_amount' => 10400000, 'commission_amount' => 208000, 'commission_distributed' => 0, 'sale_date' => '2026-04-20', 'created_at' => $NOW],
    ['property_id' => 4, 'buyer_id' => 10, 'agent_id' => 81, 'sale_amount' => 5000000, 'commission_amount' => 100000, 'commission_distributed' => 0, 'sale_date' => '2026-05-01', 'created_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  REPORT TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'saved_reports', [
    ['report_name' => 'Q1 2026 Sales Report', 'report_type' => 'sales', 'description' => 'Quarterly sales performance across all colonies', 'data_source' => 'plots,sales', 'filters' => '{"period":"Q1-2026"}', 'columns' => '["colony","plots_sold","revenue","commission"]', 'chart_type' => 'bar', 'created_by' => 68, 'is_public' => 0, 'is_active' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['report_name' => 'Monthly Lead Conversion', 'report_type' => 'leads', 'description' => 'Lead conversion metrics for current month', 'data_source' => 'leads,communications', 'filters' => '{"month":"May-2026"}', 'columns' => '["total_leads","contacted","qualified","converted","rate"]', 'chart_type' => 'funnel', 'created_by' => 63, 'is_public' => 1, 'is_active' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['report_name' => 'Employee Performance Summary', 'report_type' => 'hrm', 'description' => 'Monthly KPI achievement across departments', 'data_source' => 'employees,kpi', 'filters' => '{"year":2026,"month":5}', 'columns' => '["employee","department","kpi","target","achieved","score"]', 'chart_type' => 'table', 'schedule_frequency' => 'monthly', 'schedule_day' => 1, 'schedule_time' => '09:00:00', 'created_by' => 68, 'is_public' => 0, 'is_active' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'analytics_reports', [
    ['report_name' => 'Website Traffic Analysis - May 2026', 'report_type' => 'traffic', 'parameters' => '{"period":"2026-05","metrics":["visitors","pageviews","bounce_rate"]}', 'report_data' => '{"visitors":12500,"pageviews":45000,"bounce_rate":"35%"}', 'generated_at' => $NOW, 'expires_at' => '2026-06-30 23:59:59'],
    ['report_name' => 'Property Search Trends', 'report_type' => 'search', 'parameters' => '{"period":"Q1-2026","top":10}', 'report_data' => '{"top_searches":["plot","villa","apartment"],"locations":["Gorakhpur","Lucknow","Deoria"]}', 'generated_at' => $NOW, 'expires_at' => '2026-07-31 23:59:59'],
    ['report_name' => 'Lead Source Attribution', 'report_type' => 'marketing', 'parameters' => '{"period":"2026-01-01 to 2026-05-31"}', 'report_data' => '{"facebook":"35%","google":"25%","referral":"20%","direct":"15%","other":"5%"}', 'generated_at' => $NOW, 'expires_at' => '2026-06-30 23:59:59'],
]);
echo "\n============================================================\n";
echo "  SECURITY TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'security_audit_log', [
    ['event_type' => 'login', 'user_id' => 68, 'ip_address' => '192.168.1.50', 'user_agent' => 'Mozilla/5.0', 'event_data' => '{"status":"success","method":"password"}', 'risk_level' => 'low', 'created_at' => $NOW],
    ['event_type' => 'permission_change', 'user_id' => 1, 'ip_address' => '192.168.1.1', 'user_agent' => 'Mozilla/5.0', 'event_data' => '{"target_user":63,"role":"manager","changed_by":1}', 'risk_level' => 'medium', 'created_at' => $NOW],
    ['event_type' => 'failed_login', 'user_id' => null, 'ip_address' => '203.0.113.45', 'user_agent' => 'curl/7.68', 'event_data' => '{"attempts":5,"username":"admin"}', 'risk_level' => 'high', 'created_at' => $NOW],
]);
seedTable($pdo, 'security_blacklist', [
    ['ip_address' => '203.0.113.45', 'reason' => 'Multiple failed login attempts', 'blacklisted_at' => $NOW, 'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')), 'is_active' => 1],
    ['ip_address' => '198.51.100.23', 'reason' => 'Brute force detected', 'blacklisted_at' => $NOW, 'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')), 'is_active' => 1],
    ['ip_address' => '192.0.2.100', 'reason' => 'Suspicious API scanning', 'blacklisted_at' => $NOW, 'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), 'is_active' => 1],
]);
seedTable($pdo, 'security_events', [
    ['event_type' => 'sql_injection_attempt', 'severity' => 'critical', 'source' => 'web', 'description' => 'SQL injection pattern detected in login form', 'ip_address' => '203.0.113.45', 'user_id' => null, 'metadata' => '{"uri":"/login","payload":"1=1--"}', 'created_at' => $NOW],
    ['event_type' => 'unauthorized_access', 'severity' => 'high', 'source' => 'web', 'description' => 'Attempt to access admin panel without auth', 'ip_address' => '198.51.100.23', 'user_id' => null, 'metadata' => '{"uri":"/admin/dashboard","method":"GET"}', 'created_at' => $NOW],
    ['event_type' => 'password_reset', 'severity' => 'info', 'source' => 'web', 'description' => 'Password reset requested for user ID 3', 'ip_address' => '192.168.1.100', 'user_id' => 3, 'metadata' => '{"email":"customer1@apsdreamhome.com"}', 'created_at' => $NOW],
]);
seedTable($pdo, 'security_rate_limits', [
    ['ip_address' => '203.0.113.45', 'action_type' => 'login', 'request_count' => 150, 'window_start' => date('Y-m-d H:i:s', strtotime('-15 minutes')), 'window_duration' => 900, 'is_blocked' => 1],
    ['ip_address' => '192.168.1.100', 'action_type' => 'api', 'request_count' => 85, 'window_start' => date('Y-m-d H:i:s', strtotime('-5 minutes')), 'window_duration' => 300, 'is_blocked' => 0],
    ['ip_address' => '10.0.0.55', 'action_type' => 'login', 'request_count' => 3, 'window_start' => date('Y-m-d H:i:s', strtotime('-2 minutes')), 'window_duration' => 300, 'is_blocked' => 0],
]);
echo "\n============================================================\n";
echo "  SUPPORT TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'support_ticket_responses', [
    ['ticket_id' => 1, 'user_id' => 68, 'response' => 'We are looking into your payment issue. Our accounts team will contact you within 24 hours.', 'created_at' => $NOW],
    ['ticket_id' => 1, 'user_id' => 3, 'response' => 'Thank you for the quick response. I will wait for their call.', 'created_at' => $NOW],
    ['ticket_id' => 2, 'user_id' => 68, 'response' => 'The water connection team has been notified. We will send an engineer tomorrow.', 'created_at' => $NOW],
]);
echo "\n============================================================\n";
echo "  USER TABLES\n";
echo "============================================================\n";
seedTable($pdo, 'user_bank_accounts', [
    ['user_id' => 3, 'account_type' => 'savings', 'account_holder_name' => 'Customer One', 'account_number' => '12345678901', 'bank_name' => 'State Bank of India', 'branch_name' => 'Gorakhpur Main', 'ifsc_code' => 'SBIN0001234', 'micr_code' => '123456789', 'is_primary' => 1, 'is_verified' => 1, 'verified_at' => $NOW, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 6, 'account_type' => 'savings', 'account_holder_name' => 'Customer Two', 'account_number' => '98765432101', 'bank_name' => 'HDFC Bank', 'branch_name' => 'Lucknow Hazratganj', 'ifsc_code' => 'HDFC0005678', 'micr_code' => '987654321', 'is_primary' => 1, 'is_verified' => 0, 'verified_at' => null, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 53, 'account_type' => 'current', 'account_holder_name' => 'Associate One', 'account_number' => '45678901234', 'bank_name' => 'ICICI Bank', 'branch_name' => 'Deoria', 'ifsc_code' => 'ICIC0009012', 'micr_code' => '456789012', 'is_primary' => 1, 'is_verified' => 1, 'verified_at' => $NOW, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'user_wallets', [
    ['user_id' => 3, 'user_type' => 'customer', 'balance' => 50000, 'hold_balance' => 0, 'total_credited' => 150000, 'total_debited' => 100000, 'is_active' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 53, 'user_type' => 'associate', 'balance' => 125000, 'hold_balance' => 25000, 'total_credited' => 500000, 'total_debited' => 350000, 'is_active' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 2, 'user_type' => 'agent', 'balance' => 75000, 'hold_balance' => 10000, 'total_credited' => 300000, 'total_debited' => 215000, 'is_active' => 1, 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'user_notification_preferences', [
    ['user_id' => 3, 'user_type' => 'customer', 'notification_type' => 'booking_update', 'email_enabled' => 1, 'push_enabled' => 1, 'sms_enabled' => 0, 'whatsapp_enabled' => 0, 'frequency' => 'immediate', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 53, 'user_type' => 'associate', 'notification_type' => 'commission', 'email_enabled' => 1, 'push_enabled' => 1, 'sms_enabled' => 1, 'whatsapp_enabled' => 1, 'frequency' => 'daily', 'quiet_hours_start' => '22:00:00', 'quiet_hours_end' => '08:00:00', 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 2, 'user_type' => 'agent', 'notification_type' => 'lead_assignment', 'email_enabled' => 1, 'push_enabled' => 1, 'sms_enabled' => 1, 'whatsapp_enabled' => 0, 'frequency' => 'immediate', 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'user_social_accounts', [
    ['user_id' => 3, 'provider' => 'google', 'provider_id' => 'google_uid_abc123', 'token' => 'ya29.a0A...', 'refresh_token' => '1//0g...', 'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 6, 'provider' => 'facebook', 'provider_id' => 'fb_uid_def456', 'token' => 'EAA...', 'refresh_token' => null, 'expires_at' => date('Y-m-d H:i:s', strtotime('+60 days')), 'created_at' => $NOW, 'updated_at' => $NOW],
    ['user_id' => 10, 'provider' => 'google', 'provider_id' => 'google_uid_ghi789', 'token' => 'ya29.a0B...', 'refresh_token' => '1//0h...', 'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), 'created_at' => $NOW, 'updated_at' => $NOW],
]);
seedTable($pdo, 'user_activity_logs', [
    ['user_id' => 3, 'action' => 'logged_in', 'ip_address' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0', 'context' => '{"browser":"Chrome","platform":"Windows"}', 'timestamp' => $NOW, 'created_at' => $NOW],
    ['user_id' => 3, 'action' => 'viewed_properties', 'ip_address' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0', 'context' => '{"count":5,"filters":{"type":"plot"}}', 'timestamp' => $NOW, 'created_at' => $NOW],
    ['user_id' => 53, 'action' => 'submitted_property', 'ip_address' => '192.168.1.101', 'user_agent' => 'Mozilla/5.0', 'context' => '{"property_id":2,"type":"plot"}', 'timestamp' => $NOW, 'created_at' => $NOW],
    ['user_id' => 68, 'action' => 'approved_property', 'ip_address' => '192.168.1.50', 'user_agent' => 'Mozilla/5.0', 'context' => '{"property_id":2,"admin_id":68}', 'timestamp' => $NOW, 'created_at' => $NOW],
    ['user_id' => 10, 'action' => 'submitted_inquiry', 'ip_address' => '192.168.1.102', 'user_agent' => 'Mozilla/5.0', 'context' => '{"property_id":4,"type":"site_visit"}', 'timestamp' => $NOW, 'created_at' => $NOW],
]);

echo "\n============================================================\n";
echo "  SEEDING COMPLETE\n";
echo "============================================================\n";
