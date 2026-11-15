<?php
/**
 * Widen mlm_commission_ledger.status to include 'processing'.
 *
 * PayoutBatchService marks ledger rows `status = 'processing'` while a batch
 * settles (lines 308/317) and flips them to 'paid' afterwards. The live enum
 * never contained 'processing', so under STRICT_TRANS_TABLES every attempt to
 * move into the processing state raised SQLSTATE 1265 and rolled back the
 * transaction - a batch could never complete settlement.
 *
 * Idempotent: run 1 = ALTER, run 2 = SKIP. Appends at END of enum so existing
 * member indices are preserved. No data is rewritten.
 */
$mysqli = new mysqli('localhost', 'root', '', 'apsdreamhome');
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connect failed: " . $mysqli->connect_error . "\n");
    exit(1);
}

$row = $mysqli->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'status'")->fetch_assoc();
if (!$row) {
    fwrite(STDERR, "FATAL: mlm_commission_ledger.status column not found\n");
    exit(1);
}
$current = (string)$row['Type'];
echo "before: status {$current}\n";

$members = preg_match('/enum\(([^)]*)\)/i', $current, $m) ? $m[1] : '';
if (stripos(',' . $members . ',', "'processing'") !== false) {
    echo "SKIP: 'processing' already in enum\n";
    exit(0);
}

$result = $mysqli->query("ALTER TABLE mlm_commission_ledger MODIFY COLUMN status ENUM('pending','approved','paid','cancelled','clawed_back','missed','processing') DEFAULT 'pending'");
if (!$result) {
    fwrite(STDERR, "ALTER failed: " . $mysqli->error . "\n");
    exit(1);
}
$after = $mysqli->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'status'")->fetch_assoc();
echo "after:  status {$after['Type']}\n";
echo "rows:   " . $mysqli->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetch_row()[0] . " (unchanged)\n";
?>
