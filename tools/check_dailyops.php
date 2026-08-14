<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$stmt = $pdo->query("SELECT COUNT(*) FROM daily_operations_log WHERE log_type = 'noc_generation'");
echo "noc_generation rows: " . $stmt->fetchColumn() . "\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM booking_documents WHERE document_type = 'noc'");
echo "booking_documents noc rows: " . $stmt->fetchColumn() . "\n";

echo "\n=== SUMMARY OF BUGS FOUND ===\n";

echo "\n=== BUG 1: NocRegistryService::checkNocEligibility() ===\n";
echo "  Line 61: SUM(total_amount - paid_amount)\n";
echo "  ACTUAL COLUMN: 'amount' (not 'total_amount')\n";
echo "  This will produce NULL instead of the actual balance.\n";

echo "\n=== BUG 2: NocRegistryService::checkNocEligibility() ===\n";
echo "  Lines 104-106: source_booking_id column\n";
echo "  ACTUAL COLUMNS: property_id, sale_amount (no source_booking_id)\n";
echo "  This will throw an SQL error (1054 Unknown column 'source_booking_id').\n";

echo "\n=== BUG 3: MoneyWorkflowService::generateNoc() ===\n";
echo "  Line 1572: operation_type column\n";
echo "  ACTUAL COLUMN: 'log_type' (not 'operation_type')\n";
echo "  This will throw an SQL error (1054 Unknown column 'operation_type').\n";

echo "\n=== BUG 4: MoneyWorkflowService::generateNoc() ===\n";
echo "  Line 1577: operation_date / operation_type columns in INSERT\n";
echo "  ACTUAL COLUMNS: 'log_date', 'log_type'\n";
echo "  This will also throw SQL errors.\n";

echo "\n=== GAP 1: Missing 'docs uploaded' check ===\n";
echo "  NocRegistryService does NOT check if all required documents exist\n";
echo "  in booking_documents before allowing NOC creation.\n";
echo "  MoneyWorkflowService only checks booking_documents in a different\n";
echo "  internal method (checkNocDocumentStatus), not in checkRegistryEligibility.\n";

echo "\n=== GAP 2: Duplicate NOC pipelines ===\n";
echo "  MoneyWorkflowService::generateNoc writes to daily_operations_log\n";
echo "  NocRegistryService::createNocRequest writes to noc_requests\n";
echo "  These are completely independent â€” no cross-referencing.\n";?>