<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== mlm_commission_ledger ===\n";
$stmt = $pdo->query("DESCRIBE mlm_commission_ledger");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']}\n";

echo "\n=== booking_payment_schedules (all) ===\n";
$stmt = $pdo->query("DESCRIBE booking_payment_schedules");
foreach ($stmt as $row) echo "  {$row['Field']}: {$row['Type']}\n";

echo "\n=== noc_requests rows ===\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM noc_requests");
echo "  count: " . $stmt->fetchColumn() . "\n";

echo "\n=== registries rows ===\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM registries");
echo "  count: " . $stmt->fetchColumn() . "\n";

echo "\n=== daily_operations_log noc_generation rows ===\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM daily_operations_log WHERE operation_type = 'noc_generation'");
echo "  count: " . $stmt->fetchColumn() . "\n";

echo "\n=== booking_documents rows ===\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM booking_documents");
echo "  count: " . $stmt->fetchColumn() . "\n";

echo "\n=== MoneyWorkflowService checkRegistryEligibility uses correct column (amount not total_amount) ===\n";
echo "  MoneyWorkflowService uses 'amount - paid_amount' which is correct (amount column exists)" . "\n";

echo "\n=== NocRegistryService checkNocEligibility uses 'total_amount - paid_amount' ===\n";
echo "  'total_amount' column DOES NOT EXIST. Column is 'amount'." . "\n";
echo "  This will cause an SQL error or NULL result!" . "\n";

echo "\n=== NocRegistryService commission check uses 'source_booking_id' ===\n";
echo "  'source_booking_id' column DOES NOT EXIST in mlm_commission_ledger." . "\n";
echo "  Schema has: property_id, sale_amount — no booking_id column." . "\n";
echo "  This will cause an SQL error!" . "\n";
