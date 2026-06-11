<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== booking_payment_schedules amount columns ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM booking_payment_schedules WHERE Field IN ('amount','total_amount','paid_amount','accrued_penalty','status')");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']}\n";

echo "\n=== plot_bookings key columns ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM plot_bookings WHERE Field IN ('total_plot_value','total_amount','plot_value','agreement_value','customer_id','colony_id','status','booking_number')");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']}\n";

echo "\n=== mlm_commission_ledger ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']}\n";

echo "\n=== noc_requests ===\n";
$stmt = $pdo->query("DESCRIBE noc_requests");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']} Null={$row['Null']} Default={$row['Default']}\n";

echo "\n=== registries ===\n";
$stmt = $pdo->query("DESCRIBE registries");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']} Null={$row['Null']} Default={$row['Default']}\n";

echo "\n=== daily_operations_log ===\n";
$stmt = $pdo->query("DESCRIBE daily_operations_log");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']} Null={$row['Null']} Default={$row['Default']}\n";

echo "\n=== booking_documents ===\n";
$stmt = $pdo->query("DESCRIBE booking_documents");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']} Null={$row['Null']} Default={$row['Default']}\n";
