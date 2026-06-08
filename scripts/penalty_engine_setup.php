<?php
/**
 * EMI Penalty Engine — DB Setup + Seed
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected OK" . PHP_EOL;

    // 1. Add accrued_penalty column if missing
    $cols = [];
    $r = $pdo->query('SHOW COLUMNS FROM booking_payment_schedules');
    while ($c = $r->fetch(PDO::FETCH_ASSOC)) { $cols[] = $c['Field']; }
    if (!in_array('accrued_penalty', $cols)) {
        $pdo->exec("ALTER TABLE booking_payment_schedules ADD COLUMN accrued_penalty DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER late_fee");
        echo "Added accrued_penalty column" . PHP_EOL;
    } else {
        echo "accrued_penalty column already exists" . PHP_EOL;
    }

    // 2. Create penalty_audit table
    $pdo->exec("CREATE TABLE IF NOT EXISTS penalty_audit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        installment_id BIGINT(20) UNSIGNED NOT NULL,
        booking_id BIGINT(20) UNSIGNED NOT NULL,
        days_overdue INT NOT NULL,
        penalty_amount DECIMAL(10,2) NOT NULL,
        total_accrued DECIMAL(10,2) NOT NULL,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_penalty_audit_booking (booking_id),
        KEY idx_penalty_audit_date (applied_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "penalty_audit table created/verified" . PHP_EOL;

    // 3. Seed overdue installments if table is empty
    $count = (int)$pdo->query('SELECT COUNT(*) FROM booking_payment_schedules')->fetchColumn();
    if ($count === 0) {
        echo "Seeding test data..." . PHP_EOL;

        // Insert plot_bookings (FK target for booking_payment_schedules.booking_id)
        $pdo->exec("INSERT IGNORE INTO plot_bookings 
            (id, plot_id, customer_id, booking_number, booking_date, total_plot_value, booking_amount, agreement_value, status, channel, created_at)
            VALUES
            (9001, 11, 3, 'APS-BK-PEN-001', '2026-01-15', 2500000, 250000, 2500000, 'emi_active', 'direct', NOW()),
            (9002, 12, 3, 'APS-BK-PEN-002', '2026-02-01', 3750000, 375000, 3750000, 'emi_active', 'associate', NOW())");
        echo "Inserted 2 test plot_bookings" . PHP_EOL;

        // Insert overdue installments
        $overdue1 = date('Y-m-d', strtotime('-40 days'));
        $overdue2 = date('Y-m-d', strtotime('-15 days'));
        $pending  = date('Y-m-d', strtotime('+5 days'));

        $stmt = $pdo->prepare("INSERT INTO booking_payment_schedules 
            (booking_id, installment_no, due_date, amount, principal, interest, opening_balance, closing_balance, status, paid_amount, paid_date, accrued_penalty, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");

        // Booking 9001 (plot 11, total 2500000): 3 installments
        $stmt->execute([9001, 1, date('Y-m-d', strtotime('-45 days')), 250000, 200000, 50000, 2500000, 2250000, 'paid',    250000, date('Y-m-d', strtotime('-43 days'))]);
        $stmt->execute([9001, 2, $overdue1,                            250000, 200000, 50000, 2250000, 2000000, 'overdue', 0,     null]);
        $stmt->execute([9001, 3, $pending,                             250000, 200000, 50000, 2000000, 1750000, 'pending', 0,     null]);

        // Booking 9002 (plot 12, total 3750000): 3 installments
        $stmt->execute([9002, 1, date('Y-m-d', strtotime('-50 days')), 375000, 300000, 75000, 3750000, 3375000, 'paid',    375000, date('Y-m-d', strtotime('-48 days'))]);
        $stmt->execute([9002, 2, $overdue2,                            375000, 300000, 75000, 3375000, 3000000, 'overdue', 0,     null]);
        $stmt->execute([9002, 3, $pending,                             375000, 300000, 75000, 3000000, 2625000, 'pending', 0,     null]);

        echo "Seeded 6 installments (2 paid, 2 overdue, 2 pending-future)" . PHP_EOL;
    } else {
        echo "booking_payment_schedules already has $count rows — skipping seed" . PHP_EOL;
    }

    // Verify
    $r = $pdo->query("SELECT COUNT(*) FROM booking_payment_schedules WHERE status IN ('pending','overdue') AND due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY)");
    echo "Overdue installments past grace: " . $r->fetchColumn() . PHP_EOL;

    echo PHP_EOL . "DONE" . PHP_EOL;

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
