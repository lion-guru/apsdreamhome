<?php
// Seed sample data into empty feature tables
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
try {
  $db = new PDO($dsn, $user, $pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $seeded = 0; $skipped = 0;

  // -- CAMPAIGNS --
  if ($db->query("SHOW TABLES LIKE 'campaigns'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();
    if ($cnt == 0) {
      $db->exec("INSERT INTO campaigns (campaign_id, name, description, type, status, start_date, end_date, budget, created_at, updated_at) VALUES
        (1, 'Summer Property Drive', 'Discount offers on premium plots this summer', 'email', 'active', '2026-06-01', '2026-08-31', 50000.00, NOW(), NOW()),
        (2, 'Holiday Referral Bonus', 'Refer a friend and get 5% commission', 'referral', 'planned', '2026-12-01', '2026-12-31', 25000.00, NOW(), NOW()),
        (3, 'Q1 Brand Awareness', 'Newspaper and radio ads for colony launch', 'print', 'completed', '2026-01-15', '2026-03-15', 100000.00, NOW(), NOW())");
      $seeded += 3; echo "Seeded 3 campaigns\n";
    } else { echo "campaigns: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- COMMISSIONS --
  if ($db->query("SHOW TABLES LIKE 'commissions'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM commissions")->fetchColumn();
    if ($cnt == 0) {
      $assocId = $db->query("SELECT id FROM associates WHERE status='active' LIMIT 1")->fetchColumn();
      if ($assocId) {
        $db->prepare("INSERT INTO commissions (associate_id, amount, percentage, commission_type, status, description, created_at) VALUES
          (?, 15000.00, 5.00, 'direct', 'paid', 'Commission on plot sale at Suryoday Heights', NOW()),
          (?, 7500.00, 2.50, 'team', 'pending', 'Team override on referral sale', NOW()),
          (?, 5000.00, 5.00, 'referral', 'paid', 'Referral bonus for customer A Sharma', NOW())")
          ->execute([$assocId, $assocId, $assocId]);
        $seeded += 3; echo "Seeded 3 commissions\n";
      } else { echo "commissions: skipped (no active associate)\n"; }
    } else { echo "commissions: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- PAYOUTS --
  if ($db->query("SHOW TABLES LIKE 'payouts'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM payouts")->fetchColumn();
    if ($cnt == 0) {
      $assocId = $db->query("SELECT id FROM associates WHERE status='active' LIMIT 1")->fetchColumn();
      if ($assocId) {
        $db->prepare("INSERT INTO payouts (associate_id, amount, payment_method, status, reference_number, paid_at, notes, created_at) VALUES
          (?, 25000.00, 'bank_transfer', 'completed', 'PAY-001-2026', NOW(), 'Monthly commission payout May 2026', NOW()),
          (?, 15000.00, 'upi', 'pending', NULL, NULL, 'Pending payout for plot commissions', NOW())")
          ->execute([$assocId, $assocId]);
        $seeded += 2; echo "Seeded 2 payouts\n";
      } else { echo "payouts: skipped (no active associate)\n"; }
    } else { echo "payouts: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- INVOICES --
  if ($db->query("SHOW TABLES LIKE 'invoices'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
    if ($cnt == 0) {
      $db->exec("INSERT INTO invoices (invoice_number, invoice_date, due_date, client_name, client_email, client_phone, subtotal, tax_amount, total_amount, status, created_at) VALUES
        ('INV-2026-001', '2026-05-01', '2026-05-15', 'Rajesh Kumar', 'rajesh.kumar@example.com', '9876543210', 500000.00, 90000.00, 590000.00, 'paid', NOW()),
        ('INV-2026-002', '2026-05-10', '2026-05-25', 'Associate Sharma', 'associate.sharma@apsdreamhome.com', '9876543212', 250000.00, 45000.00, 295000.00, 'sent', NOW()),
        ('INV-2026-003', '2026-05-20', '2026-06-05', 'Verma Properties', 'verma@property.com', '9876543213', 1000000.00, 180000.00, 1180000.00, 'draft', NOW())");
      $seeded += 3; echo "Seeded 3 invoices\n";
    } else { echo "invoices: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- EXPENSES --
  if ($db->query("SHOW TABLES LIKE 'expenses'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM expenses")->fetchColumn();
    if ($cnt == 0) {
      $assocId = $db->query("SELECT id FROM associates WHERE status='active' LIMIT 1")->fetchColumn();
      if ($assocId) {
        $db->prepare("INSERT INTO expenses (associate_id, category, amount, description, expense_date, status, created_at) VALUES
          (?, 'Travel', 2500.00, 'Site visit to Gorakhpur colony', '2026-05-15', 'approved', NOW()),
          (?, 'Marketing', 15000.00, 'Brochure printing for summer drive', '2026-05-10', 'approved', NOW()),
          (?, 'Office Supplies', 1200.00, 'Stationery and printer ink', '2026-05-18', 'pending', NOW())")
          ->execute([$assocId, $assocId, $assocId]);
        $seeded += 3; echo "Seeded 3 expenses\n";
      } else { echo "expenses: skipped (no active associate)\n"; }
    } else { echo "expenses: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- TRANSACTIONS --
  if ($db->query("SHOW TABLES LIKE 'transactions'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    if ($cnt == 0) {
      $userId = $db->query("SELECT id FROM users WHERE user_type='customer' LIMIT 1")->fetchColumn();
      if ($userId) {
        $db->prepare("INSERT INTO transactions (user_id, type, amount, date, description, ref_id, created_at) VALUES
          (?, 'payment', 500000.00, '2026-05-01', 'Plot booking payment - Suryoday Heights', 'TXN-001', NOW()),
          (?, 'refund', 25000.00, '2026-05-12', 'Excess payment refund', 'TXN-002', NOW()),
          (?, 'commission', 15000.00, '2026-05-20', 'Referral commission payout', 'TXN-003', NOW())")
          ->execute([$userId, $userId, $userId]);
        $seeded += 3; echo "Seeded 3 transactions\n";
      } else { echo "transactions: skipped (no customer user)\n"; }
    } else { echo "transactions: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- SUPPORT TICKETS --
  if ($db->query("SHOW TABLES LIKE 'support_tickets'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn();
    if ($cnt == 0) {
      $userId = $db->query("SELECT id FROM users WHERE user_type='customer' LIMIT 1")->fetchColumn();
      $empId = $db->query("SELECT user_id FROM employees WHERE status='active' LIMIT 1")->fetchColumn();
      if ($userId) {
        $db->prepare("INSERT INTO support_tickets (user_id, assigned_to, subject, category, message, status, priority, created_at) VALUES
          (?, ?, 'Question about plot registration', 'registration', 'How do I register my plot at Suryoday Heights?', 'open', 'medium', NOW()),
          (?, ?, 'Payment receipt not received', 'payment', 'I paid my booking amount but havent received the receipt.', 'open', 'high', NOW()),
          (?, ?, 'Site visit request for weekend', 'site-visit', 'I want to visit the Gorakhpur colony this Saturday.', 'open', 'low', NOW())")
          ->execute([$userId, $userId, $userId, $empId, $empId, $empId]);
        $seeded += 3; echo "Seeded 3 support tickets\n";
      } else { echo "support_tickets: skipped (no customer user)\n"; }
    } else { echo "support_tickets: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- VISITS --
  if ($db->query("SHOW TABLES LIKE 'visits'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM visits")->fetchColumn();
    if ($cnt == 0) {
      $userId = $db->query("SELECT id FROM users WHERE user_type='customer' LIMIT 1")->fetchColumn();
      $assocId = $db->query("SELECT id FROM associates WHERE status='active' LIMIT 1")->fetchColumn();
      if ($userId && $assocId) {
        $db->prepare("INSERT INTO visits (associate_id, customer_id, visit_date, visit_time, location_address, notes, status, created_at) VALUES
          (?, ?, '2026-05-10', '10:30:00', 'Suryoday Heights, Gorakhpur', 'Customer interested in 150sq yd plot', 'completed', NOW()),
          (?, ?, '2026-05-20', '15:00:00', 'Raghunath City Center', 'Follow-up visit, customer wants corner plot', 'scheduled', NOW())")
          ->execute([$assocId, $userId, $assocId, $userId]);
        $seeded += 2; echo "Seeded 2 visits\n";
      } else { echo "visits: skipped (no customer/associate)\n"; }
    } else { echo "visits: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- LEAVES --
  if ($db->query("SHOW TABLES LIKE 'leaves'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM leaves")->fetchColumn();
    if ($cnt == 0) {
      $empId = $db->query("SELECT user_id FROM employees WHERE status='active' LIMIT 1")->fetchColumn();
      if ($empId) {
        $db->prepare("INSERT INTO leaves (employee_id, leave_type, from_date, to_date, status, remarks, created_at) VALUES
          (?, 'sick', '2026-05-15', '2026-05-16', 'approved', 'Medical leave', NOW()),
          (?, 'casual', '2026-05-25', '2026-05-25', 'pending', 'Personal work', NOW()),
          (?, 'annual', '2026-06-10', '2026-06-14', 'pending', 'Family vacation', NOW())")
          ->execute([$empId, $empId, $empId]);
        $seeded += 3; echo "Seeded 3 leaves\n";
      } else { echo "leaves: skipped (no active employee)\n"; }
    } else { echo "leaves: skipped ($cnt rows)\n"; $skipped++; }
  }

  // -- DOCUMENTS --
  if ($db->query("SHOW TABLES LIKE 'documents'")->rowCount() > 0) {
    $cnt = $db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
    if ($cnt == 0) {
      $userId = $db->query("SELECT id FROM users WHERE user_type='customer' LIMIT 1")->fetchColumn();
      if ($userId) {
        $db->prepare("INSERT INTO documents (user_id, type, url, uploaded_on) VALUES
          (?, 'aadhar', '/uploads/documents/aadhar_sample.pdf', NOW()),
          (?, 'pan', '/uploads/documents/pan_sample.pdf', NOW()),
          (?, 'agreement', '/uploads/documents/agreement_sample.pdf', NOW())")
          ->execute([$userId, $userId, $userId]);
        $seeded += 3; echo "Seeded 3 documents\n";
      } else { echo "documents: skipped (no customer user)\n"; }
    } else { echo "documents: skipped ($cnt rows)\n"; $skipped++; }
  }

  echo "\n--- Done: $seeded records seeded, $skipped tables skipped (already have data) ---\n";
} catch (Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  exit(1);
}
