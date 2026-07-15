<?php
/**
 * Seed 26 empty finance tables with realistic Indian real estate data.
 * Tables seeded: 26 (booking_payment_schedules already has 6 rows, skipped)
 * Run: php scripts/seed_finance_tables.php
 */
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"');

$seeded = 0;
$tableCounts = [];

try {
    // ─── 1. bank_reconciliation ───
    $pdo->exec("INSERT IGNORE INTO bank_reconciliation (bank_account_id, statement_date, statement_balance, book_balance, reconciled_by, reconciled_at, status, notes, reconciliation_date, opening_balance, closing_balance) VALUES
        (1, '2026-05-31', 2450000.00, 2445000.00, 2, '2026-06-01 10:30:00', 'completed', 'May 2026 month-end reconciliation for HDFC main account', '2026-05-31', 2200000.00, 2450000.00),
        (2, '2026-05-31', 890000.00, 888500.00, 2, '2026-06-01 11:00:00', 'completed', 'May 2026 axis bank current account reconciliation', '2026-05-31', 750000.00, 890000.00),
        (3, '2026-06-10', 1250000.00, 1250000.00, NULL, NULL, 'in_progress', 'June mid-month SBI reconciliation in progress', '2026-06-10', 1100000.00, 1250000.00),
        (1, '2026-06-30', 2680000.00, 2675000.00, NULL, NULL, 'draft', 'June 2026 month-end pending', '2026-06-30', 2450000.00, 2680000.00)
    ");
    $tableCounts['bank_reconciliation'] = 4;

    // ─── 2. bank_reconciliation_items ───
    $pdo->exec("INSERT IGNORE INTO bank_reconciliation_items (reconciliation_id, transaction_type, transaction_date, amount, description, status) VALUES
        (1, 'cheque_issued_not_cleared', '2026-05-28', 75000.00, 'Cheque #4521 issued to Raj Constructions - not yet cleared', 'pending'),
        (1, 'bank_charges', '2026-05-31', 1250.00, 'HDFC monthly account maintenance charges', 'cleared'),
        (1, 'direct_credit', '2026-05-30', 350000.00, 'NEFT from Customer - Plot A-001 installment', 'cleared'),
        (2, 'interest_credited', '2026-05-31', 4500.00, 'Q4 interest on fixed deposit linked to current account', 'cleared'),
        (2, 'direct_debit', '2026-05-29', 28000.00, 'Auto-debit for electricity bill - Braj Radha Nagri colony', 'pending'),
        (3, 'cheque_deposited_not_cleared', '2026-06-08', 150000.00, 'Customer cheque deposited at SBI branch - awaiting clearance', 'pending')
    ");
    $tableCounts['bank_reconciliation_items'] = 6;

    // ─── 3. bank_statement_imports ───
    $pdo->exec("INSERT IGNORE INTO bank_statement_imports (bank_account_id, filename, original_filename, import_date, total_rows, matched_rows, unmatched_rows, status, imported_by) VALUES
        (1, 'hdfc_may2026_1717200000.csv', 'HDFC_Statement_May2026.csv', '2026-06-01', 145, 138, 7, 'completed', 2),
        (2, 'axis_may2026_1717286400.csv', 'Axis_Current_May2026.csv', '2026-06-01', 89, 85, 4, 'completed', 2),
        (1, 'hdfc_june2026_1718150400.csv', 'HDFC_Statement_June2026.csv', '2026-06-10', 67, 60, 7, 'completed', 5),
        (3, 'sbi_june2026_1718409600.csv', 'SBI_June2026_Statement.csv', '2026-06-12', 42, 38, 4, 'processing', 2)
    ");
    $tableCounts['bank_statement_imports'] = 4;

    // ─── 4. bank_transactions ───
    $pdo->exec("INSERT IGNORE INTO bank_transactions (import_id, bank_account_id, transaction_date, value_date, description, debit, credit, balance, cheque_number, reference_number, matched) VALUES
        (1, 1, '2026-05-02', '2026-05-02', 'NEFT INR - Rahul Sharma - Plot A-001 Token', 0.00, 250000.00, 1950000.00, NULL, 'NEFT20260502001', 1),
        (1, 1, '2026-05-05', '2026-05-05', 'UPI INR - Amit Patel - Plot B-003 Booking Advance', 0.00, 100000.00, 2050000.00, NULL, 'UPI20260505001', 1),
        (1, 1, '2026-05-10', '2026-05-10', 'Cheque Clearing - Raj Constructions Pvt Ltd', 350000.00, 0.00, 1700000.00, '4518', 'CHQ4518', 1),
        (1, 1, '2026-05-15', '2026-05-15', 'NEFT INR - Vikram Singh - Plot C-007 EMI May', 0.00, 85000.00, 1785000.00, NULL, 'NEFT20260515001', 1),
        (1, 1, '2026-05-20', '2026-05-20', 'ATM WDL - Office petty cash', 25000.00, 0.00, 1760000.00, NULL, 'ATM20260520001', 1),
        (2, 2, '2026-05-03', '2026-05-03', 'NEFT INR - Priya Verma - Plot A-002 installment', 0.00, 150000.00, 700000.00, NULL, 'NEFT20260503002', 1),
        (2, 2, '2026-05-18', '2026-05-18', 'Cheque - Mahaveer Builders - Material supply', 120000.00, 0.00, 580000.00, '7823', 'CHQ7823', 1),
        (3, 1, '2026-06-02', '2026-06-02', 'NEFT INR - Suresh Kumar - Plot D-012 booking', 0.00, 500000.00, 2950000.00, NULL, 'NEFT20260602001', 1),
        (3, 1, '2026-06-05', '2026-06-05', 'RTGS - Land acquisition payment - Gram Panchayat Gorakhpur', 1200000.00, 0.00, 1750000.00, NULL, 'RTGS20260605001', 1)
    ");
    $tableCounts['bank_transactions'] = 9;

    // ─── 5. booking_commissions ───
    $pdo->exec("INSERT IGNORE INTO booking_commissions (booking_id, beneficiary_user_id, source_user_id, commission_type, amount, percent, level, status, approved_by, paid_at, notes) VALUES
        (9001, 8, 3, 'direct_sale', 75000.00, 2.00, 1, 'paid', 2, '2026-05-15 14:00:00', 'Direct sale commission for Booking #9001 - Plot A-001 (Suryoday Heights)'),
        (9001, 9, 8, 'mlm_level_1', 112500.00, 3.00, 1, 'paid', 2, '2026-05-15 14:00:00', 'L1 upline commission - Associate referral'),
        (9001, 10, 8, 'mlm_level_2', 56250.00, 1.50, 2, 'approved', 2, NULL, 'L2 upline commission - Level 2 override'),
        (9001, 11, 8, 'mlm_level_3', 37500.00, 1.00, 3, 'pending', NULL, NULL, 'L3 upline commission - Level 3 override'),
        (9002, 3, 5, 'direct_sale', 112500.00, 2.00, 1, 'paid', 2, '2026-05-20 16:30:00', 'Direct sale commission for Booking #9002 - Plot A-002'),
        (9002, 8, 3, 'mlm_level_1', 168750.00, 3.00, 1, 'approved', 2, NULL, 'L1 upline for Booking #9002'),
        (9002, 9, 8, 'mlm_level_2', 84375.00, 1.50, 2, 'pending', NULL, NULL, 'L2 upline for Booking #9002')
    ");
    $tableCounts['booking_commissions'] = 7;

    // ─── 6. booking_demand_letters ───
    $pdo->exec("INSERT IGNORE INTO booking_demand_letters (booking_id, installment_id, letter_number, generated_date, due_date, amount, status, sent_via, sent_to_email, sent_at, pdf_path) VALUES
        (9001, 2, 'DL-2026-0001', '2026-04-25', '2026-05-05', 83333.00, 'overdue', 'email', 'rahul.sharma@gmail.com', '2026-04-25 09:00:00', 'uploads/demand-letters/DL-2026-0001.pdf'),
        (9001, 3, 'DL-2026-0002', '2026-05-25', '2026-06-05', 83333.00, 'sent', 'email', 'rahul.sharma@gmail.com', '2026-05-25 09:00:00', 'uploads/demand-letters/DL-2026-0002.pdf'),
        (9002, 5, 'DL-2026-0003', '2026-05-25', '2026-06-05', 125000.00, 'sent', 'whatsapp', 'priya.verma@gmail.com', '2026-05-25 10:30:00', 'uploads/demand-letters/DL-2026-0003.pdf'),
        (9002, 6, 'DL-2026-0004', '2026-06-25', '2026-07-05', 125000.00, 'drafted', NULL, NULL, NULL, NULL)
    ");
    $tableCounts['booking_demand_letters'] = 4;

    // ─── 7. booking_documents ───
    $pdo->exec("INSERT IGNORE INTO booking_documents (booking_id, document_type, document_name, file_path, file_size, mime_type, uploaded_by, verified_by, verified_at, status, notes) VALUES
        (9001, 'sale_agreement', 'Sale Agreement - Rahul Sharma - Plot A-001', 'uploads/bookings/9001/sale_agreement.pdf', 245000, 'application/pdf', 3, 2, '2026-05-10 11:00:00', 'verified', 'Original signed copy received'),
        (9001, 'token_receipt', 'Token Receipt - Booking #9001', 'uploads/bookings/9001/token_receipt.pdf', 45000, 'application/pdf', 3, 2, '2026-05-02 09:30:00', 'verified', 'Token amount Rs 2,50,000'),
        (9001, 'application_form', 'Application Form - Rahul Sharma', 'uploads/bookings/9001/application.pdf', 120000, 'application/pdf', 3, NULL, NULL, 'verified', 'Completed application with KYC'),
        (9002, 'sale_agreement', 'Sale Agreement - Priya Verma - Plot A-002', 'uploads/bookings/9002/sale_agreement.pdf', 250000, 'application/pdf', 5, 2, '2026-05-18 14:00:00', 'verified', 'Signed on stamp paper'),
        (9002, 'allotment_letter', 'Allotment Letter - Plot A-002', 'uploads/bookings/9002/allotment_letter.pdf', 78000, 'application/pdf', 2, NULL, NULL, 'verified', 'Allotment for Braj Radha Nagri Phase 1'),
        (9002, 'receipt', 'Payment Receipt - Rs 1,50,000 Advance', 'uploads/bookings/9002/advance_receipt.pdf', 35000, 'application/pdf', 3, 2, '2026-05-03 16:00:00', 'verified', NULL)
    ");
    $tableCounts['booking_documents'] = 6;

    // ─── 8. booking_payment_receipts ───
    $pdo->exec("INSERT IGNORE INTO booking_payment_receipts (booking_id, installment_id, receipt_number, receipt_date, amount, payment_mode, cheque_number, cheque_date, bank_name, transaction_ref, collected_by, status, notes) VALUES
        (9001, 2, 'APS-RCP-20260501-0001', '2026-05-01', 250000.00, 'neft', NULL, NULL, 'HDFC Bank', 'NEFT20260501001', 3, 'cleared', 'Token payment for Plot A-001'),
        (9001, 3, 'APS-RCP-20260515-0002', '2026-05-15', 83333.00, 'upi', NULL, NULL, NULL, 'UPI20260515002', 3, 'cleared', '1st EMI installment'),
        (9002, 5, 'APS-RCP-20260503-0003', '2026-05-03', 150000.00, 'neft', NULL, NULL, 'Axis Bank', 'NEFT20260503002', 5, 'cleared', 'Booking advance for Plot A-002'),
        (9002, 6, 'APS-RCP-20260601-0004', '2026-06-01', 125000.00, 'cheque', '7824', '2026-05-28', 'ICICI Bank', NULL, 5, 'cleared', '2nd installment via cheque'),
        (9001, 4, 'APS-RCP-20260605-0005', '2026-06-05', 83333.00, 'rtgs', NULL, NULL, 'SBI', 'RTGS20260605005', 2, 'pending', '2nd EMI - awaiting bank confirmation')
    ");
    $tableCounts['booking_payment_receipts'] = 5;

    // ─── 9. booking_refunds ───
    $pdo->exec("INSERT IGNORE INTO booking_refunds (booking_id, refund_amount, cancellation_charge, deduction_reason, refund_mode, refund_date, bank_account, transaction_ref, status, approved_by, processed_by) VALUES
        (9001, 166667.00, 83333.00, '10% cancellation charge on token amount as per agreement clause 7.2', 'neft', NULL, NULL, NULL, 'pending', NULL, NULL),
        (9002, 25000.00, 25000.00, 'Administrative processing charge for partial refund request', 'bank_transfer', '2026-05-20', 'HDFC-50100012345678', 'NEFT20260520R01', 'processed', 2, 2)
    ");
    $tableCounts['booking_refunds'] = 2;

    // ─── 10. booking_status_history ───
    $pdo->exec("INSERT IGNORE INTO booking_status_history (booking_id, from_status, to_status, changed_by, reason, ip_address, user_agent) VALUES
        (9001, NULL, 'token_paid', 3, 'Initial token payment received for Plot A-001', '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0'),
        (9001, 'token_paid', 'agreement_signed', 2, 'Sale agreement signed at Suryoday Heights office', '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0'),
        (9001, 'agreement_signed', 'emi_active', 2, 'EMI schedule generated - 12 months starting May 2026', '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0'),
        (9002, NULL, 'token_paid', 5, 'Token payment for Plot A-002 at Braj Radha Nagri', '192.168.1.105', 'Mozilla/5.0 Windows NT 10.0'),
        (9002, 'token_paid', 'agreement_signed', 2, 'Agreement executed on stamp paper Rs 100', '192.168.1.105', 'Mozilla/5.0 Windows NT 10.0'),
        (9002, 'agreement_signed', 'emi_active', 2, 'EMI plan activated - 12 months quarterly', '192.168.1.105', 'Mozilla/5.0 Windows NT 10.0')
    ");
    $tableCounts['booking_status_history'] = 6;

    // ─── 11. booking_transfers ───
    $pdo->exec("INSERT IGNORE INTO booking_transfers (original_booking_id, new_customer_id, transfer_reason, transfer_date, transfer_charge, legal_charges, status, approved_by) VALUES
        (9001, 6, 'Customer relocating to Delhi - requests ownership transfer to brother Vikram Singh', '2026-06-15', 25000.00, 5000.00, 'initiated', NULL),
        (9002, 22, 'Inheritance transfer - original buyer Priya Verma transferring to son Arjun Verma', '2026-06-20', 15000.00, 8000.00, 'docs_verified', 2)
    ");
    $tableCounts['booking_transfers'] = 2;

    // ─── 12. budgets ───
    $pdo->exec("INSERT IGNORE INTO budgets (budget_name, department_id, category, fiscal_year, period_start, period_end, allocated_amount, spent_amount, committed_amount, status, approved_by) VALUES
        ('Marketing Budget FY2026-27', 3, 'marketing', '2026-27', '2026-04-01', '2027-03-31', 5000000.00, 1200000.00, 350000.00, 'active', 2),
        ('Construction - Suryoday Phase 2', 1, 'construction', '2026-27', '2026-04-01', '2027-03-31', 25000000.00, 8500000.00, 3000000.00, 'active', 2),
        ('HR & Admin FY2026-27', 4, 'operations', '2026-27', '2026-04-01', '2027-03-31', 3500000.00, 900000.00, 200000.00, 'active', 2),
        ('Legal & Compliance FY2026-27', 5, 'legal', '2026-27', '2026-04-01', '2027-03-31', 1200000.00, 300000.00, 150000.00, 'active', 2),
        ('IT Infrastructure FY2026-27', 2, 'technology', '2026-27', '2026-04-01', '2027-03-31', 800000.00, 200000.00, 100000.00, 'draft', NULL)
    ");
    $tableCounts['budgets'] = 5;

    // ─── 13. budget_expenses ───
    $pdo->exec("INSERT IGNORE INTO budget_expenses (budget_id, expense_date, vendor, description, amount, bill_number, status, approved_by) VALUES
        (1, '2026-04-10', 'Google Ads India', 'Google Ads campaign for Suryoday Heights listings', 150000.00, 'GADS-2026-0410', 'paid', 2),
        (1, '2026-04-20', 'Hindustan Times Media', 'Full-page ad in HT Gorakhpur edition', 200000.00, 'HT-2026-0420', 'paid', 2),
        (1, '2026-05-05', 'Zomato Swiggy Branding', 'Local event sponsorship - Property Expo Gorakhpur', 85000.00, 'EXP-2026-0505', 'approved', 2),
        (2, '2026-04-15', 'UltraTech Cement', 'Cement supply for Phase 2 foundation work', 1200000.00, 'UTC-2026-0415', 'paid', 2),
        (2, '2026-05-01', 'Tata Steel', 'TMT bar supply - 50 tonnes for Phase 2 construction', 2500000.00, 'TATA-2026-0501', 'paid', 2),
        (3, '2026-04-01', 'Salary Disbursement', 'April 2026 employee salaries - 18 staff', 540000.00, 'SAL-2026-04', 'paid', 2),
        (3, '2026-05-01', 'Salary Disbursement', 'May 2026 employee salaries - 18 staff', 540000.00, 'SAL-2026-05', 'paid', 2)
    ");
    $tableCounts['budget_expenses'] = 7;

    // ─── 14. budget_planning ───
    $pdo->exec("INSERT IGNORE INTO budget_planning (fiscal_year, department_id, line_item, category, q1_amount, q2_amount, q3_amount, q4_amount, justification, status) VALUES
        ('2027-28', 3, 'Digital Marketing Campaigns', 'marketing', 400000.00, 350000.00, 500000.00, 450000.00, 'Increased digital presence for new project launches', 'submitted'),
        ('2027-28', 1, 'Suryoday Phase 3 Construction', 'construction', 5000000.00, 7000000.00, 6000000.00, 4000000.00, 'Phase 3 construction covering 80 plots', 'submitted'),
        ('2027-28', 4, 'Employee Welfare & Training', 'operations', 200000.00, 150000.00, 200000.00, 250000.00, 'Annual training program + team outings', 'draft'),
        ('2027-28', 2, 'ERP System Upgrade', 'technology', 300000.00, 100000.00, 50000.00, 50000.00, 'Upgrade to latest PHP version + new modules', 'draft'),
        ('2027-28', 5, 'RERA Compliance & Legal Retainer', 'legal', 150000.00, 100000.00, 100000.00, 150000.00, 'Quarterly compliance filings + legal counsel', 'approved')
    ");
    $tableCounts['budget_planning'] = 5;

    // ─── 15. cash_flow_forecast ───
    $pdo->exec("INSERT IGNORE INTO cash_flow_forecast (projection_date, type, category, expected_amount, probability_pct, expected_date, notes, created_by, forecast_date, opening_balance, expected_receipts, expected_payments, closing_balance) VALUES
        ('2026-06-15', 'inflow', 'customer_payment', 3500000.00, 85.00, '2026-06-15', 'Expected customer installments from Suryoday and Braj Radha', 2, '2026-06-01', 5200000.00, 3500000.00, 2100000.00, 6600000.00),
        ('2026-06-15', 'outflow', 'land_acquisition', 1500000.00, 70.00, '2026-06-15', 'Land payment to Gram Panchayat - Kushinagar parcel', 2, '2026-06-01', 5200000.00, 3500000.00, 2100000.00, 6600000.00),
        ('2026-06-15', 'outflow', 'salary', 600000.00, 100.00, '2026-06-01', 'June 2026 salary disbursement - 18 employees', 2, '2026-06-01', 5200000.00, 3500000.00, 2100000.00, 6600000.00),
        ('2026-07-01', 'inflow', 'customer_payment', 4200000.00, 75.00, '2026-07-01', 'Projected July installment collections', 2, '2026-06-01', 6600000.00, 4200000.00, 2800000.00, 8000000.00),
        ('2026-07-01', 'outflow', 'development', 2000000.00, 90.00, '2026-07-01', 'Phase 2 road and drainage construction', 2, '2026-06-01', 6600000.00, 4200000.00, 2800000.00, 8000000.00),
        ('2026-08-01', 'inflow', 'loan', 5000000.00, 60.00, '2026-08-01', 'Project loan disbursement from HDFC Corp Bank', 2, '2026-06-01', 8000000.00, 5000000.00, 3200000.00, 9800000.00)
    ");
    $tableCounts['cash_flow_forecast'] = 6;

    // ─── 16. cash_flow_projections ───
    $pdo->exec("INSERT IGNORE INTO cash_flow_projections (projection_date, scenario, opening_balance, projected_inflow, projected_outflow, notes, created_by) VALUES
        ('2026-06-30', 'optimistic', 5200000.00, 8000000.00, 4500000.00, 'Best case: all 12 expected payments received + loan disbursement', 2),
        ('2026-06-30', 'realistic', 5200000.00, 6200000.00, 5100000.00, 'Base case: 80% collection rate + regular expenses', 2),
        ('2026-06-30', 'pessimistic', 5200000.00, 4000000.00, 5500000.00, 'Worst case: low collections + delayed loan + higher construction cost', 2),
        ('2026-07-31', 'optimistic', 5800000.00, 9500000.00, 5000000.00, 'Phase 2 bookings accelerate + second loan tranche', 2),
        ('2026-07-31', 'realistic', 5300000.00, 7000000.00, 5500000.00, 'Steady state operations', 2),
        ('2026-07-31', 'pessimistic', 3700000.00, 4500000.00, 6000000.00, 'Market slowdown impact on collections', 2),
        ('2026-08-31', 'realistic', 6800000.00, 7500000.00, 4800000.00, 'Aug projections with festival season boost', 2)
    ");
    $tableCounts['cash_flow_projections'] = 7;

    // ─── 17. cheque_bounce_log ───
    $pdo->exec("INSERT IGNORE INTO cheque_bounce_log (cheque_id, bounce_date, bank_name, bounce_reason, charges, recovery_status, legal_action) VALUES
        (1, '2026-05-25', 'HDFC Bank', 'Insufficient funds in drawer account - customer cheque returned', 500.00, 'recovered', NULL),
        (1, '2026-06-05', 'SBI', 'Cheque presented beyond validity period (3 months old)', 350.00, 'pending', 'Legal notice sent to customer on 2026-06-08 under Section 138 NI Act');
    ");
    $tableCounts['cheque_bounce_log'] = 2;

    // ─── 18. expense_approvals ───
    $pdo->exec("INSERT IGNORE INTO expense_approvals (expense_id, expense_table, requested_by, current_approver, approver_role, approval_level, status, approved_at, remarks, next_approver) VALUES
        (1, 'expenses', 3, 2, 'admin', 1, 'approved', '2026-04-12 10:00:00', 'Marketing expense approved - Google Ads is within budget', NULL),
        (2, 'expenses', 5, 2, 'admin', 1, 'approved', '2026-04-22 11:30:00', 'Print ad approved for Gorakhpur edition', NULL),
        (3, 'expenses', 3, 2, 'admin', 1, 'pending', NULL, NULL, NULL)
    ");
    $tableCounts['expense_approvals'] = 3;

    // ─── 19. payment_plans ───
    $pdo->exec("INSERT IGNORE INTO payment_plans (property_id, plan_name, plan_type, total_amount, down_payment_percent, number_of_installments, installment_frequency, interest_applicable, interest_rate, description, is_active) VALUES
        (1, 'Suryoday Heights - Standard 12 Month EMI', 'construction', 2500000.00, 10.00, 12, 'monthly', 0, 0.00, 'Standard construction-linked payment plan for Plot A-001 at Suryoday Heights', 1),
        (2, 'Braj Radha - Quarterly Construction Plan', 'construction', 3750000.00, 15.00, 8, 'quarterly', 1, 8.50, 'Construction-linked plan for Plot A-002 at Braj Radha Nagri with 8.5% interest', 1),
        (3, 'Raghunath City - Possession-Linked Plan', 'possession', 4200000.00, 20.00, 6, 'milestone', 0, 0.00, 'Possession-linked payment: 20% down + 4 installments at milestones + 10% on possession', 1),
        (4, 'Budh Bihar - Custom Farmhouse Plan', 'custom', 5500000.00, 25.00, 4, 'milestone', 1, 9.00, 'Custom plan for farmhouse plot - 25% down + 3 milestones + possession', 1)
    ");
    $tableCounts['payment_plans'] = 4;

    // ─── 20. payment_plan_milestones ───
    $pdo->exec("INSERT IGNORE INTO payment_plan_milestones (plan_id, milestone_order, milestone_name, percentage, amount, due_date, description) VALUES
        (1, 1, 'Booking Token', 10.00, 250000.00, '2026-05-01', 'Initial token to block the plot'),
        (1, 2, 'Agreement Signing', 15.00, 375000.00, '2026-05-15', 'On execution of sale agreement'),
        (1, 3, 'Foundation Complete', 25.00, 625000.00, '2026-07-31', 'When foundation work is completed'),
        (1, 4, 'Plinth Level', 20.00, 500000.00, '2026-09-30', 'Plinth level construction completed'),
        (1, 5, 'Superstructure', 20.00, 500000.00, '2026-12-31', 'Roof/superstructure completed'),
        (1, 6, 'Possession', 10.00, 250000.00, '2027-03-31', 'Final payment on possession handover'),
        (2, 1, 'Booking Advance', 15.00, 562500.00, '2026-05-03', 'Initial advance at booking'),
        (2, 2, 'Foundation', 20.00, 750000.00, '2026-07-31', 'Foundation work completion'),
        (2, 3, 'Brickwork', 20.00, 750000.00, '2026-10-31', 'Brickwork and plastering'),
        (2, 4, 'Finishing', 25.00, 937500.00, '2027-01-31', 'Internal finishing and fixtures'),
        (2, 5, 'Possession', 20.00, 750000.00, '2027-04-30', 'Final possession with completion certificate')
    ");
    $tableCounts['payment_plan_milestones'] = 11;

    // ─── 21. payment_schedules ───
    $pdo->exec("INSERT IGNORE INTO payment_schedules (user_id, entity_type, entity_id, total_amount, paid_amount, next_due_date, next_due_amount, installment_count, installments_paid, late_fee_amount, status, auto_debit) VALUES
        (3, 'booking', 9001, 2500000.00, 333333.00, '2026-07-05', 83333.00, 12, 2, 0.00, 'active', 1),
        (5, 'booking', 9002, 3750000.00, 275000.00, '2026-07-05', 125000.00, 8, 1, 0.00, 'active', 0),
        (6, 'plot', 1, 1800000.00, 450000.00, '2026-06-30', 150000.00, 6, 1, 0.00, 'active', 0),
        (22, 'booking', 9002, 3750000.00, 0.00, '2026-08-01', 468750.00, 8, 0, 0.00, 'active', 0),
        (8, 'emi', 1, 500000.00, 100000.00, '2026-06-15', 50000.00, 10, 1, 250.00, 'overdue', 0)
    ");
    $tableCounts['payment_schedules'] = 5;

    // ─── 22. payment_webhook_logs ───
    $pdo->exec("INSERT IGNORE INTO payment_webhook_logs (gateway, event_type, event_id, payload, signature, signature_verified, processed, processing_error, ip_address) VALUES
        ('razorpay', 'payment.captured', 'evt_20260515001', '{\"event\":\"payment.captured\",\"payload\":{\"payment\":{\"entity\":{\"id\":\"pay_20260515001\",\"amount\":25000000,\"currency\":\"INR\",\"status\":\"captured\",\"method\":\"upi\",\"order_id\":\"order_20260515001\"}}}}', 'hmac_sha256_sig_abc123', 1, 1, NULL, '54.239.28.85'),
        ('razorpay', 'payment.captured', 'evt_20260503001', '{\"event\":\"payment.captured\",\"payload\":{\"payment\":{\"entity\":{\"id\":\"pay_20260503001\",\"amount\":15000000,\"currency\":\"INR\",\"status\":\"captured\",\"method\":\"netbanking\",\"order_id\":\"order_20260503001\"}}}}', 'hmac_sha256_sig_def456', 1, 1, NULL, '54.239.28.85'),
        ('razorpay', 'payment.failed', 'evt_20260601001', '{\"event\":\"payment.failed\",\"payload\":{\"payment\":{\"entity\":{\"id\":\"pay_20260601001\",\"amount\":8333300,\"currency\":\"INR\",\"status\":\"failed\",\"error_code\":\"payment_failed\",\"error_description\":\"Payment failed due to insufficient funds\"}}}}', 'hmac_sha256_sig_ghi789', 1, 1, NULL, '54.239.28.85'),
        ('razorpay', 'payment.authorized', 'evt_20260605001', '{\"event\":\"payment.authorized\",\"payload\":{\"payment\":{\"entity\":{\"id\":\"pay_20260605001\",\"amount\":12500000,\"currency\":\"INR\",\"status\":\"authorized\",\"method\":\"card\",\"order_id\":\"order_20260605001\"}}}}', 'hmac_sha256_sig_jkl012', 1, 0, NULL, '54.239.28.85')
    ");
    $tableCounts['payment_webhook_logs'] = 4;

    // ─── 23. payroll_entries ───
    $pdo->exec("INSERT IGNORE INTO payroll_entries (payroll_run_id, employee_id, basic_salary, hra, transport_allowance, medical_allowance, special_allowance, bonus, overtime, gross_salary, pf_deduction, esi_deduction, tds_deduction, advance_deduction, loan_deduction, other_deductions, total_deductions, net_salary, working_days, present_days, leave_days, status) VALUES
        (1, 1, 35000.00, 14000.00, 3000.00, 2000.00, 5000.00, 0.00, 0.00, 59000.00, 4200.00, 0.00, 2100.00, 0.00, 0.00, 0.00, 6300.00, 52700.00, 31, 28, 3, 'paid'),
        (1, 2, 50000.00, 20000.00, 5000.00, 3000.00, 8000.00, 0.00, 0.00, 86000.00, 6000.00, 0.00, 6500.00, 0.00, 0.00, 0.00, 12500.00, 73500.00, 31, 30, 1, 'paid'),
        (1, 3, 25000.00, 10000.00, 2500.00, 1500.00, 3500.00, 0.00, 500.00, 43000.00, 3000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 3000.00, 40000.00, 31, 29, 2, 'paid'),
        (2, 1, 35000.00, 14000.00, 3000.00, 2000.00, 5000.00, 0.00, 1000.00, 60000.00, 4200.00, 0.00, 2200.00, 0.00, 0.00, 0.00, 6400.00, 53600.00, 31, 30, 1, 'paid'),
        (2, 2, 50000.00, 20000.00, 5000.00, 3000.00, 8000.00, 5000.00, 0.00, 91000.00, 6000.00, 0.00, 7000.00, 0.00, 0.00, 0.00, 13000.00, 78000.00, 31, 31, 0, 'paid'),
        (2, 3, 25000.00, 10000.00, 2500.00, 1500.00, 3500.00, 0.00, 0.00, 42500.00, 3000.00, 0.00, 0.00, 500.00, 0.00, 0.00, 3500.00, 39000.00, 31, 27, 4, 'approved'),
        (3, 1, 35000.00, 14000.00, 3000.00, 2000.00, 5000.00, 0.00, 0.00, 59000.00, 4200.00, 0.00, 2100.00, 0.00, 0.00, 0.00, 6300.00, 52700.00, 30, 28, 2, 'draft')
    ");
    $tableCounts['payroll_entries'] = 7;

    // ─── 24. salary_contracts ───
    $pdo->exec("INSERT IGNORE INTO salary_contracts (employee_id, contract_number, start_date, end_date, ctc, basic_salary, terms, status, signed_date) VALUES
        (1, 'APS-SC-2026-001', '2026-04-01', '2027-03-31', 708000.00, 420000.00, 'Annual CTC Rs 7,08,000 including HRA 40%, PF employer contribution, medical insurance. 18 working days leave per year. 3 month notice period.', 'active', '2026-03-28'),
        (2, 'APS-SC-2026-002', '2026-04-01', '2027-03-31', 1032000.00, 600000.00, 'Annual CTC Rs 10,32,000 including HRA 40%, PF, ESIC, performance bonus 10%. 24 days leave. 3 month notice period.', 'active', '2026-03-25'),
        (3, 'APS-SC-2026-003', '2026-04-01', '2027-03-31', 516000.00, 300000.00, 'Annual CTC Rs 5,16,000 including HRA 40%, PF employer contribution. 18 days annual leave. 1 month notice period.', 'active', '2026-03-30'),
        (4, 'APS-SC-2026-004', '2026-04-01', '2027-03-31', 480000.00, 288000.00, 'Annual CTC Rs 4,80,000. Basic 60% of CTC. HRA as per company policy. Standard benefits apply.', 'active', '2026-04-01'),
        (5, 'APS-SC-2026-005', '2026-06-01', '2027-05-31', 600000.00, 360000.00, 'Annual CTC Rs 6,00,000 for Sales Executive role. 5% incentive on closed deals above Rs 25L.', 'active', '2026-05-28')
    ");
    $tableCounts['salary_contracts'] = 5;

    // ─── 25. salary_history ───
    $pdo->exec("INSERT IGNORE INTO salary_history (employee_id, change_type, old_salary, new_salary, effective_date, reason, approved_by) VALUES
        (1, 'increment', 30000.00, 35000.00, '2026-04-01', 'Annual increment - 16.7% hike based on performance rating Exceeds Expectations', 2),
        (2, 'promotion', 40000.00, 50000.00, '2026-04-01', 'Promoted from Senior Executive to Manager - 25% hike on promotion', 2),
        (2, 'increment', 35000.00, 40000.00, '2025-04-01', 'Annual increment FY2025-26', 2),
        (3, 'increment', 22000.00, 25000.00, '2026-04-01', 'Annual increment - 13.6% based on satisfactory performance', 2),
        (5, 'role_change', 20000.00, 25000.00, '2026-06-01', 'Transferred from Junior to Sales Executive role with salary revision', 2),
        (4, 'increment', 22000.00, 24000.00, '2026-04-01', 'Annual increment - 9% hike', 2)
    ");
    $tableCounts['salary_history'] = 6;

    // ─── 26. tds_challans ───
    $pdo->exec("INSERT IGNORE INTO tds_challans (challan_number, bsr_code, tan, assessment_year, financial_year, quarter, deposit_date, tds_section, total_amount, interest_amount, penalty_amount, surcharge_amount, cess_amount, total_with_charges, challan_status, govt_challan_id, receipt_number, deposited_via, bank_name, remarks) VALUES
        ('CHL-2026-Q4-001', '7501234', 'APSD00001A', '2026-27', '2025-26', 'Q4', '2026-04-15', '194IA', 150000.00, 0.00, 0.00, 0.00, 0.00, 150000.00, 'deposited', 'GOVT-2026-0415001', 'REC-2026-0415001', 'net_banking', 'HDFC Bank', 'TDS on immovable property - Plot registry Q4 FY2025-26'),
        ('CHL-2026-Q4-002', '7501234', 'APSD00001A', '2026-27', '2025-26', 'Q4', '2026-04-15', '194C', 45000.00, 0.00, 0.00, 0.00, 0.00, 45000.00, 'deposited', 'GOVT-2026-0415002', 'REC-2026-0415002', 'net_banking', 'HDFC Bank', 'TDS on contractor payments - Q4 FY2025-26'),
        ('CHL-2026-Q1-001', '7501234', 'APSD00001A', '2026-27', '2026-27', 'Q1', '2026-06-15', '194C', 52000.00, 200.00, 0.00, 0.00, 0.00, 52200.00, 'deposited', 'GOVT-2026-0615001', 'REC-2026-0615001', 'net_banking', 'SBI', 'Q1 FY2026-27 contractor TDS + late deposit interest'),
        ('CHL-2026-Q1-002', '7501234', 'APSD00001A', '2026-27', '2026-27', 'Q1', '2026-06-15', '194H', 28000.00, 0.00, 0.00, 0.00, 0.00, 28000.00, 'prepared', NULL, NULL, 'net_banking', NULL, 'Commission TDS - pending deposit via net banking'),
        ('CHL-2026-Q1-003', '7501234', 'APSD00001A', '2026-27', '2026-27', 'Q1', '2026-06-30', '194IA', 180000.00, 0.00, 0.00, 0.00, 0.00, 180000.00, 'prepared', NULL, NULL, 'offline', NULL, 'Property registration TDS - to be deposited at bank counter')
    ");
    $tableCounts['tds_challans'] = 5;

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo "=== SEED COMPLETE ===\n\n";
$total = 0;
foreach ($tableCounts as $table => $count) {
    echo str_pad($table, 35) . ": $count rows\n";
    $total += $count;
}
echo str_pad('TOTAL', 35) . ": $total rows\n";

echo "\n=== VERIFICATION ===\n";
$verifyTables = array_keys($tableCounts);
foreach ($verifyTables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $expected = $tableCounts[$t];
    $status = ($count >= $expected) ? 'OK' : 'MISMATCH (expected ' . $expected . ', got ' . $count . ')';
    echo str_pad($t, 35) . ": $count rows - $status\n";
}
