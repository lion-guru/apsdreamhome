<?php
/**
 * Seed More Feature Data - Safe seed script for empty feature tables
 * Uses INSERT IGNORE, checks if table already has data before seeding
 * PDO: mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome, root, empty
 */

$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$db->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "=== SEEDING MORE FEATURE DATA ===\n\n";
$total = 0;
$fail = 0;

function isEmpty($db, $table) {
    $stmt = $db->query("SELECT COUNT(*) as c FROM `$table`");
    return $stmt->fetch()['c'] == 0;
}

// ==============================
// DEFECT REPORTS
// ==============================
try {
    if (isEmpty($db, 'defect_reports')) {
        $db->exec("INSERT IGNORE INTO defect_reports (booking_id, reported_by, defect_type, description, priority, status) VALUES
            (1, 1, 'plumbing', 'Water leakage in main bathroom of Unit A-101', 'high', 'open'),
            (1, 1, 'electrical', 'Power socket not working in living room of Unit B-203', 'medium', 'in_progress'),
            (2, 2, 'structural', 'Minor crack in boundary wall near Gate 2', 'low', 'open')
        ");
        echo "  defect_reports: 3 rows\n"; $total++;
    } else { echo "  defect_reports: already has data\n"; }
} catch (Exception $e) {
    echo "  defect_reports: FAILED - " . $e->getMessage() . "\n"; $fail++;
}

// ==============================
// POSSESSION CHECKLIST
// ==============================
try {
    if (isEmpty($db, 'possession_checklist')) {
        $db->exec("INSERT IGNORE INTO possession_checklist (booking_id, item_name, is_completed, remarks) VALUES
            (1, 'Sale Deed Execution', 1, 'Executed on 2026-05-15'),
            (1, 'Payment Clearance', 1, 'All payments received'),
            (1, 'Physical Possession Handover', 0, 'Scheduled for June 2026'),
            (2, 'Sale Deed Execution', 1, 'Executed on 2026-05-10'),
            (2, 'Payment Clearance', 0, 'Pending final installment of 2,00,000'),
            (2, 'NOC from Society', 0, 'Applied, awaiting approval'),
            (3, 'Agreement Registration', 0, 'Stamp duty pending')
        ");
        echo "  possession_checklist: 7 rows\n"; $total++;
    } else { echo "  possession_checklist: already has data\n"; }
} catch (Exception $e) {
    echo "  possession_checklist: FAILED - " . $e->getMessage() . "\n"; $fail++;
}

// ==============================
// REGISTRY ACTIVITY LOG
// ==============================
try {
    if (isEmpty($db, 'registry_activity_log')) {
        $db->exec("INSERT IGNORE INTO registry_activity_log (booking_id, action, details, performed_by) VALUES
            (1, 'registry_initiated', 'Registry process started for Unit A-101 Suryoday Heights', 1),
            (1, 'document_verified', 'All documents verified by legal team', 1),
            (2, 'registry_initiated', 'Registry process started for Unit B-203', 1),
            (2, 'stamp_paper_purchased', 'Stamp paper worth Rs. 1,00,000 purchased', 2),
            (3, 'enquiry_received', 'Customer enquired about registry process', 3)
        ");
        echo "  registry_activity_log: 5 rows\n"; $total++;
    } else { echo "  registry_activity_log: already has data\n"; }
} catch (Exception $e) {
    echo "  registry_activity_log: FAILED - " . $e->getMessage() . "\n"; $fail++;
}

// ==============================
// SUPPORT TICKET REPLIES
// ==============================
try {
    if (isEmpty($db, 'support_ticket_replies')) {
        // Check if support_tickets has any rows; if not, use ticket_id=1
        $ticketCount = $db->query("SELECT COUNT(*) as c FROM support_tickets")->fetch()['c'];
        $ticketId = ($ticketCount > 0) ? 1 : 1;
        
        $db->exec("INSERT IGNORE INTO support_ticket_replies (ticket_id, user_id, message, is_admin) VALUES
            ($ticketId, 1, 'I am facing an issue with my property documents. Please help.', 0),
            ($ticketId, 1, 'Thank you for reaching out. Our team will contact you within 24 hours.', 1),
            ($ticketId, 2, 'When will the possession of my plot be handed over?', 0)
        ");
        echo "  support_ticket_replies: 3 rows\n"; $total++;
    } else { echo "  support_ticket_replies: already has data\n"; }
} catch (Exception $e) {
    echo "  support_ticket_replies: FAILED - " . $e->getMessage() . "\n"; $fail++;
}

// ==============================
// VENDORS
// ==============================
try {
    if (isEmpty($db, 'vendors')) {
        $db->exec("INSERT IGNORE INTO vendors (vendor_name, vendor_type, contact_person, email, phone, address, city, state, gst_number, pan_number, bank_name, bank_account, ifsc_code, payment_terms, status) VALUES
            ('Sharma Construction Co.', 'contractor', 'Rajesh Sharma', 'rajesh@sharmaconstruction.com', '9876543210', '123, Industrial Area', 'Gorakhpur', 'Uttar Pradesh', '09ABCDE1234F1Z5', 'ABCDP1234E', 'State Bank of India', '12345678901', 'SBIN0001234', '45_days', 'active'),
            ('Singh Building Supplies', 'supplier', 'Amar Singh', 'amar@singhsupplies.com', '8765432109', '456, Mandi Road', 'Lucknow', 'Uttar Pradesh', '09FGHIJ5678K2L0', 'EFGHP5678F', 'HDFC Bank', '98765432109', 'HDFC0005678', '30_days', 'active'),
            ('Legal Eagles Associates', 'service_provider', 'Priya Verma', 'priya@legaleagles.in', '7654321098', '789, Civil Lines', 'Varanasi', 'Uttar Pradesh', '09MNOPQ9012R3S4', 'LMNOP9012G', 'ICICI Bank', '45678901234', 'ICIC0009012', '15_days', 'active')
        ");
        echo "  vendors: 3 rows\n"; $total++;
    } else { echo "  vendors: already has data\n"; }
} catch (Exception $e) {
    echo "  vendors: FAILED - " . $e->getMessage() . "\n"; $fail++;
}

$db->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== SEEDING COMPLETE ===\n";
echo "Tables seeded: $total\n";
echo "Tables failed: $fail\n";
echo ($fail == 0 ? "All OK\n" : "Some failures occurred\n");
