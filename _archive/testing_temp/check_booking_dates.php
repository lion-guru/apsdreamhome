<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check booking dates for installments with 0 penalty
$installmentIds = [20, 4, 7, 15, 26];
$placeholders = implode(',', array_fill(0, count($installmentIds), '?'));
$sql = "
    SELECT bps.id, bps.booking_id, bps.due_date, bps.installment_no, pb.booking_date,
           DATEDIFF(CURDATE(), bps.due_date) AS days_overdue
    FROM booking_payment_schedules bps
    LEFT JOIN plot_bookings pb ON pb.id = bps.booking_id
    WHERE bps.id IN ($placeholders)
";
$stmt = $pdo->prepare($sql);
$stmt->execute($installmentIds);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Installment details:\n";
foreach ($rows as $r) {
    $bookingDate = $r['booking_date'] ?? 'N/A';
    $dueDate = $r['due_date'];
    $daysOverdue = $r['days_overdue'];
    
    if ($bookingDate !== 'N/A') {
        $bDate = new DateTime($bookingDate);
        $dDate = new DateTime($dueDate);
        $threeYearsLimit = (clone $bDate)->modify('+3 years');
        $isInterestFree = $dDate <= $threeYearsLimit;
        echo sprintf("  ID %d: booking=%s, due=%s, overdue=%dd, interest_free=%s (3y limit=%s)\n",
            $r['id'], $bookingDate, $dueDate, $daysOverdue, $isInterestFree ? 'YES' : 'NO', $threeYearsLimit->format('Y-m-d'));
    } else {
        echo sprintf("  ID %d: NO BOOKING DATE, due=%s, overdue=%dd\n", $r['id'], $dueDate, $daysOverdue);
    }
}

// Also check advance payment status for these bookings
echo "\nAdvance payment check:\n";
$bookingIds = array_unique(array_column($rows, 'booking_id'));
foreach ($bookingIds as $bid) {
    $totalPaid = (float)$pdo->query("SELECT COALESCE(SUM(paid_amount), 0) FROM booking_payment_schedules WHERE booking_id = $bid")->fetchColumn();
    $totalScheduled = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM booking_payment_schedules WHERE booking_id = $bid AND due_date <= CURDATE()")->fetchColumn();
    echo "  Booking $bid: paid=$totalPaid, scheduled=$totalScheduled, is_advance=" . ($totalPaid >= $totalScheduled ? 'YES' : 'NO') . "\n";
}

// Check 3 consecutive overdue for these bookings
echo "\n3+ consecutive overdue check:\n";
foreach ($bookingIds as $bid) {
    $consecutive = $pdo->query("
        SELECT COUNT(*) as cnt FROM booking_payment_schedules
        WHERE booking_id = $bid AND status = 'overdue'
        ORDER BY installment_no DESC LIMIT 3
    ")->fetchColumn();
    echo "  Booking $bid: consecutive overdue = $consecutive\n";
}?>