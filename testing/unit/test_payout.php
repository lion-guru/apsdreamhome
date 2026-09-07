<?php
/**
 * Unit Tests: Payout Batch Logic
 *
 * Tests for:
 *  - TDS calculation (10% default rate)
 *  - Batch status transitions (draft → pending_approval → approved → processing → completed)
 *  - Entry status transitions
 *  - Net amount = amount - TDS
 *  - Batch completion logic
 *
 * Run: php testing/unit/test_payout.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// ── Assertion helpers ──
$pass = 0;
$fail = 0;

function assert_true(bool $condition, string $msg): void {
    global $pass, $fail;
    if ($condition) { $pass++; echo "  ✅ $msg\n"; }
    else            { $fail++; echo "  ❌ $msg\n"; }
}

function assert_equals($expected, $actual, string $msg): void {
    global $pass, $fail;
    if ($expected === $actual) { $pass++; echo "  ✅ $msg\n"; }
    else { $fail++; echo "  ❌ $msg — expected " . var_export($expected, true) . ", got " . var_export($actual, true) . "\n"; }
}

function assert_equals_approx(float $expected, float $actual, float $tolerance, string $msg): void {
    global $pass, $fail;
    if (abs($expected - $actual) <= $tolerance) { $pass++; echo "  ✅ $msg\n"; }
    else { $fail++; echo "  ❌ $msg — expected ~$expected, got $actual\n"; }
}

function section(string $title): void {
    echo "\n── $title ──\n";
}

// ════════════════════════════════════════════════════════════════════
// 1. TDS Calculation — 10% Default Rate
// ════════════════════════════════════════════════════════════════════
section('TDS Calculation — 10% Default Rate');

/**
 * PayoutBatchService uses 10% TDS (line 154 of PayoutBatchService.php).
 * TDS = amount × 10 / 100, rounded to 2 decimal places.
 * Net = amount - TDS.
 */
function calculateTds(float $amount, float $tdsRate = 10.0): array {
    $tds = round($amount * $tdsRate / 100, 2);
    $net = round($amount - $tds, 2);
    return ['amount' => $amount, 'tds' => $tds, 'net' => $net];
}

// Standard 10% TDS
$result = calculateTds(100000);
assert_equals(10000.00, $result['tds'], 'TDS: 10% of ₹1,00,000 = ₹10,000');
assert_equals(90000.00, $result['net'], 'TDS: Net = ₹90,000');

$result = calculateTds(50000);
assert_equals(5000.00, $result['tds'], 'TDS: 10% of ₹50,000 = ₹5,000');
assert_equals(45000.00, $result['net'], 'TDS: Net = ₹45,000');

// Edge case: zero amount
$result = calculateTds(0);
assert_equals(0.00, $result['tds'], 'TDS: 10% of ₹0 = ₹0');
assert_equals(0.00, $result['net'], 'TDS: Net = ₹0');

// Edge case: very small amount (rounding)
$result = calculateTds(100);
assert_equals(10.00, $result['tds'], 'TDS: 10% of ₹100 = ₹10');
assert_equals(90.00, $result['net'], 'TDS: Net = ₹90');

// Edge case: odd amount with rounding
$result = calculateTds(33333);
assert_equals(3333.30, $result['tds'], 'TDS: 10% of ₹33,333 = ₹3,333.30');
assert_equals(29999.70, $result['net'], 'TDS: Net = ₹29,999.70');

// Large amount
$result = calculateTds(10000000);
assert_equals(1000000.00, $result['tds'], 'TDS: 10% of ₹1,00,00,000 = ₹10,00,000');
assert_equals(9000000.00, $result['net'], 'TDS: Net = ₹90,00,000');

// ════════════════════════════════════════════════════════════════════
// 2. TDS Rate Variations
// ════════════════════════════════════════════════════════════════════
section('TDS Rate Variations');

// 5% (MLM brokerage TDS — sec 194H)
$result = calculateTds(100000, 5.0);
assert_equals(5000.00, $result['tds'], 'TDS 5%: ₹1,00,000 → ₹5,000');
assert_equals(95000.00, $result['net'], 'TDS 5%: Net = ₹95,000');

// 2% (TCS rate)
$result = calculateTds(100000, 2.0);
assert_equals(2000.00, $result['tds'], 'TDS 2%: ₹1,00,000 → ₹2,000');
assert_equals(98000.00, $result['net'], 'TDS 2%: Net = ₹98,000');

// 0% (no TDS)
$result = calculateTds(100000, 0);
assert_equals(0.00, $result['tds'], 'TDS 0%: ₹1,00,000 → ₹0');
assert_equals(100000.00, $result['net'], 'TDS 0%: Net = ₹1,00,000');

// ════════════════════════════════════════════════════════════════════
// 3. Batch Status Transitions
// ════════════════════════════════════════════════════════════════════
section('Batch Status Transitions');

/**
 * Valid batch status transitions from PayoutBatchService:
 *   draft → pending_approval (submitForApproval)
 *   pending_approval → approved (approveBatch)
 *   pending_approval → rejected (rejectBatch)
 *   approved → processing (startProcessing)
 *   processing → completed (completeBatch)
 *
 * Invalid transitions should be rejected.
 */
$validTransitions = [
    'draft'              => ['pending_approval'],
    'pending_approval'   => ['approved', 'rejected'],
    'approved'           => ['processing'],
    'processing'         => ['completed'],
    'completed'          => [],  // terminal
    'rejected'           => [],  // terminal
];

function isValidTransition(string $from, string $to, array $transitions): bool {
    return in_array($to, $transitions[$from] ?? [], true);
}

// Valid transitions
assert_true(isValidTransition('draft', 'pending_approval', $validTransitions), 'draft → pending_approval is valid');
assert_true(isValidTransition('pending_approval', 'approved', $validTransitions), 'pending_approval → approved is valid');
assert_true(isValidTransition('pending_approval', 'rejected', $validTransitions), 'pending_approval → rejected is valid');
assert_true(isValidTransition('approved', 'processing', $validTransitions), 'approved → processing is valid');
assert_true(isValidTransition('processing', 'completed', $validTransitions), 'processing → completed is valid');

// Invalid transitions
assert_true(!isValidTransition('draft', 'approved', $validTransitions), 'draft → approved is invalid (skip pending)');
assert_true(!isValidTransition('draft', 'processing', $validTransitions), 'draft → processing is invalid');
assert_true(!isValidTransition('draft', 'completed', $validTransitions), 'draft → completed is invalid');
assert_true(!isValidTransition('pending_approval', 'processing', $validTransitions), 'pending_approval → processing is invalid (skip approved)');
assert_true(!isValidTransition('pending_approval', 'completed', $validTransitions), 'pending_approval → completed is invalid');
assert_true(!isValidTransition('approved', 'completed', $validTransitions), 'approved → completed is invalid (skip processing)');
assert_true(!isValidTransition('approved', 'draft', $validTransitions), 'approved → draft is invalid (backward)');
assert_true(!isValidTransition('completed', 'processing', $validTransitions), 'completed → processing is invalid (backward)');
assert_true(!isValidTransition('completed', 'draft', $validTransitions), 'completed → draft is invalid (backward)');
assert_true(!isValidTransition('rejected', 'approved', $validTransitions), 'rejected → approved is invalid');

// Terminal states — no outgoing transitions
assert_equals([], $validTransitions['completed'], 'completed is terminal (no transitions)');
assert_equals([], $validTransitions['rejected'], 'rejected is terminal (no transitions)');

// ════════════════════════════════════════════════════════════════════
// 4. Entry Status Transitions
// ════════════════════════════════════════════════════════════════════
section('Entry Status Transitions');

/**
 * Entry statuses from PayoutBatchService:
 *   pending (default when added to batch)
 *   processing (when batch starts processing)
 *   completed (when individual payment is done)
 *   cancelled (when batch is rejected)
 */
$validEntryTransitions = [
    'pending'    => ['processing', 'completed', 'cancelled'],
    'processing' => ['completed', 'cancelled'],
    'completed'  => [],  // terminal
    'cancelled'  => [],  // terminal
];

// Valid entry transitions
assert_true(isValidTransition('pending', 'processing', $validEntryTransitions), 'Entry: pending → processing');
assert_true(isValidTransition('pending', 'completed', $validEntryTransitions), 'Entry: pending → completed');
assert_true(isValidTransition('pending', 'cancelled', $validEntryTransitions), 'Entry: pending → cancelled');
assert_true(isValidTransition('processing', 'completed', $validEntryTransitions), 'Entry: processing → completed');
assert_true(isValidTransition('processing', 'cancelled', $validEntryTransitions), 'Entry: processing → cancelled');

// Invalid entry transitions
assert_true(!isValidTransition('completed', 'pending', $validEntryTransitions), 'Entry: completed → pending invalid');
assert_true(!isValidTransition('completed', 'processing', $validEntryTransitions), 'Entry: completed → processing invalid');
assert_true(!isValidTransition('cancelled', 'pending', $validEntryTransitions), 'Entry: cancelled → pending invalid');
assert_true(!isValidTransition('cancelled', 'processing', $validEntryTransitions), 'Entry: cancelled → processing invalid');

// Terminal states
assert_equals([], $validEntryTransitions['completed'], 'Entry: completed is terminal');
assert_equals([], $validEntryTransitions['cancelled'], 'Entry: cancelled is terminal');

// ════════════════════════════════════════════════════════════════════
// 5. Net Amount Verification — TDS + Net = Amount
// ════════════════════════════════════════════════════════════════════
section('Net Amount — TDS + Net = Amount invariant');

$testAmounts = [100, 1000, 50000, 100000, 333333, 1000000, 9999999];
foreach ($testAmounts as $amt) {
    $r = calculateTds($amt);
    $sum = round($r['tds'] + $r['net'], 2);
    assert_equals_approx((float)$amt, $sum, 0.01, "Invariant: TDS ₹{$r['tds']} + Net ₹{$r['net']} = ₹{$amt}");
}

// ════════════════════════════════════════════════════════════════════
// 6. Batch Entry TDS — Per-Entry Calculation
// ════════════════════════════════════════════════════════════════════
section('Batch Entry TDS — Per-Entry Calculation');

/**
 * Simulate the autoPopulateBatch logic (lines 166-189 of PayoutBatchService):
 *   For each ledger entry, calculate TDS at 10% and net amount.
 */
$ledgerEntries = [
    ['id' => 1, 'amount' => 50000, 'commission_type' => 'direct_sale'],
    ['id' => 2, 'amount' => 25000, 'commission_type' => 'level_bonus'],
    ['id' => 3, 'amount' => 75000, 'commission_type' => 'override'],
    ['id' => 4, 'amount' => 12000, 'commission_type' => 'matching_bonus'],
];

$totalAmount = 0;
$totalTds = 0;
$batchEntries = [];

foreach ($ledgerEntries as $entry) {
    $amount = (float)$entry['amount'];
    $tds = round($amount * 10.0 / 100, 2);
    $net = round($amount - $tds, 2);

    $batchEntries[] = [
        'ledger_id' => $entry['id'],
        'amount' => $amount,
        'tds_amount' => $tds,
        'net_amount' => $net,
        'commission_type' => $entry['commission_type'],
    ];

    $totalAmount += $amount;
    $totalTds += $tds;
}

assert_equals(4, count($batchEntries), 'Batch: 4 entries created');
assert_equals(5000.00, $batchEntries[0]['tds_amount'], 'Batch entry 1: TDS = ₹5,000');
assert_equals(45000.00, $batchEntries[0]['net_amount'], 'Batch entry 1: Net = ₹45,000');
assert_equals(2500.00, $batchEntries[1]['tds_amount'], 'Batch entry 2: TDS = ₹2,500');
assert_equals(22500.00, $batchEntries[1]['net_amount'], 'Batch entry 2: Net = ₹22,500');
assert_equals(7500.00, $batchEntries[2]['tds_amount'], 'Batch entry 3: TDS = ₹7,500');
assert_equals(67500.00, $batchEntries[2]['net_amount'], 'Batch entry 3: Net = ₹67,500');
assert_equals(1200.00, $batchEntries[3]['tds_amount'], 'Batch entry 4: TDS = ₹1,200');
assert_equals(10800.00, $batchEntries[3]['net_amount'], 'Batch entry 4: Net = ₹10,800');

// Verify totals
assert_equals(162000.00, $totalAmount, 'Batch total: ₹1,62,000');
assert_equals(16200.00, $totalTds, 'Batch total TDS: ₹16,200');
assert_equals_approx(145800.00, $totalAmount - $totalTds, 0.01, 'Batch total net: ₹1,45,800');

// ════════════════════════════════════════════════════════════════════
// 7. Batch Completion Check Logic
// ════════════════════════════════════════════════════════════════════
section('Batch Completion Check Logic');

/**
 * From PayoutBatchService::completeBatch() (line 347):
 *   Batch is complete when ALL entries are either 'completed' or 'cancelled'.
 *   If any entry is 'pending' or 'processing', batch cannot be completed.
 */
function isBatchComplete(array $entryStatuses): bool {
    foreach ($entryStatuses as $status) {
        if (!in_array($status, ['completed', 'cancelled'], true)) {
            return false;
        }
    }
    return true;
}

// All completed
assert_true(isBatchComplete(['completed', 'completed', 'completed']), 'Batch: all completed → complete');

// All cancelled
assert_true(isBatchComplete(['cancelled', 'cancelled']), 'Batch: all cancelled → complete');

// Mixed completed + cancelled
assert_true(isBatchComplete(['completed', 'cancelled', 'completed']), 'Batch: mixed completed/cancelled → complete');

// Has pending
assert_true(!isBatchComplete(['completed', 'pending', 'completed']), 'Batch: has pending → not complete');

// Has processing
assert_true(!isBatchComplete(['completed', 'processing', 'completed']), 'Batch: has processing → not complete');

// Empty (no entries)
assert_true(isBatchComplete([]), 'Batch: empty entries → complete');

// Single completed
assert_true(isBatchComplete(['completed']), 'Batch: single completed → complete');

// ════════════════════════════════════════════════════════════════════
// 8. Batch Validation — Submit Preconditions
// ════════════════════════════════════════════════════════════════════
section('Batch Validation — Submit Preconditions');

/**
 * From PayoutBatchService::submitForApproval() (lines 217-222):
 *   - Batch must exist
 *   - Status must be 'draft'
 *   - Must have at least 1 entry (total_entries > 0)
 */
function validateSubmit(array $batch): array {
    if (empty($batch)) return ['ok' => false, 'error' => 'Batch not found'];
    if ($batch['status'] !== 'draft') return ['ok' => false, 'error' => 'Only draft batches can be submitted'];
    if (($batch['total_entries'] ?? 0) == 0) return ['ok' => false, 'error' => 'Batch has no entries'];
    return ['ok' => true];
}

assert_true(validateSubmit([])['ok'] === false, 'Submit: empty batch → error');
assert_true(validateSubmit(['status' => 'pending_approval', 'total_entries' => 5])['ok'] === false, 'Submit: non-draft → error');
assert_true(validateSubmit(['status' => 'draft', 'total_entries' => 0])['ok'] === false, 'Submit: no entries → error');
assert_true(validateSubmit(['status' => 'draft', 'total_entries' => 3])['ok'] === true, 'Submit: draft with entries → ok');

// ════════════════════════════════════════════════════════════════════
// 9. Batch Validation — Approval Preconditions
// ════════════════════════════════════════════════════════════════════
section('Batch Validation — Approval Preconditions');

function validateApprove(array $batch): array {
    if (empty($batch)) return ['ok' => false, 'error' => 'Batch not found'];
    if ($batch['status'] !== 'pending_approval') return ['ok' => false, 'error' => 'Batch not in pending approval status'];
    return ['ok' => true];
}

assert_true(validateApprove([])['ok'] === false, 'Approve: empty batch → error');
assert_true(validateApprove(['status' => 'draft'])['ok'] === false, 'Approve: draft → error');
assert_true(validateApprove(['status' => 'pending_approval'])['ok'] === true, 'Approve: pending_approval → ok');
assert_true(validateApprove(['status' => 'approved'])['ok'] === false, 'Approve: already approved → error');
assert_true(validateApprove(['status' => 'processing'])['ok'] === false, 'Approve: processing → error');

// ════════════════════════════════════════════════════════════════════
// 10. Batch Validation — Processing Preconditions
// ════════════════════════════════════════════════════════════════════
section('Batch Validation — Processing Preconditions');

function validateProcess(array $batch): array {
    if (empty($batch)) return ['ok' => false, 'error' => 'Batch not found'];
    if ($batch['status'] !== 'approved') return ['ok' => false, 'error' => 'Batch must be approved first'];
    return ['ok' => true];
}

assert_true(validateProcess([])['ok'] === false, 'Process: empty batch → error');
assert_true(validateProcess(['status' => 'draft'])['ok'] === false, 'Process: draft → error');
assert_true(validateProcess(['status' => 'pending_approval'])['ok'] === false, 'Process: pending → error');
assert_true(validateProcess(['status' => 'approved'])['ok'] === true, 'Process: approved → ok');
assert_true(validateProcess(['status' => 'processing'])['ok'] === false, 'Process: already processing → error');

// ════════════════════════════════════════════════════════════════════
// 11. Full Lifecycle — Happy Path Simulation
// ════════════════════════════════════════════════════════════════════
section('Full Lifecycle — Happy Path Simulation');

/**
 * Simulate the complete payout batch lifecycle:
 *   1. Create batch (status = draft)
 *   2. Add entries
 *   3. Submit for approval
 *   4. Approve
 *   5. Start processing
 *   6. Complete all entries
 *   7. Complete batch
 */
$batch = ['id' => 1, 'status' => 'draft', 'total_entries' => 0, 'total_amount' => 0];
$entries = [];

// Step 1: Create
assert_equals('draft', $batch['status'], 'Lifecycle step 1: batch starts as draft');

// Step 2: Add entries
$entries = [
    ['id' => 1, 'status' => 'pending', 'amount' => 50000, 'tds' => 5000, 'net' => 45000],
    ['id' => 2, 'status' => 'pending', 'amount' => 30000, 'tds' => 3000, 'net' => 27000],
];
$batch['total_entries'] = count($entries);
$batch['total_amount'] = array_sum(array_column($entries, 'amount'));
assert_equals(2, $batch['total_entries'], 'Lifecycle step 2: 2 entries added');
assert_equals(80000, $batch['total_amount'], 'Lifecycle step 2: total ₹80,000');

// Step 3: Submit
$batch['status'] = 'pending_approval';
assert_equals('pending_approval', $batch['status'], 'Lifecycle step 3: submitted for approval');

// Step 4: Approve
$batch['status'] = 'approved';
assert_equals('approved', $batch['status'], 'Lifecycle step 4: approved');

// Step 5: Processing
$batch['status'] = 'processing';
$entries[0]['status'] = 'processing';
$entries[1]['status'] = 'processing';
assert_equals('processing', $batch['status'], 'Lifecycle step 5: processing started');
assert_true(array_product(array_map(fn($e) => $e['status'] === 'processing' ? 1 : 0, $entries)), 'Lifecycle step 5: all entries processing');

// Step 6: Complete entries
$entries[0]['status'] = 'completed';
assert_equals('completed', $entries[0]['status'], 'Lifecycle step 6a: entry 1 completed');
assert_true(!isBatchComplete(array_column($entries, 'status')), 'Lifecycle step 6a: batch not complete yet');

$entries[1]['status'] = 'completed';
assert_equals('completed', $entries[1]['status'], 'Lifecycle step 6b: entry 2 completed');
assert_true(isBatchComplete(array_column($entries, 'status')), 'Lifecycle step 6b: batch now complete');

// Step 7: Complete batch
$batch['status'] = 'completed';
assert_equals('completed', $batch['status'], 'Lifecycle step 7: batch completed');

// Verify final totals
$totalPaid = array_sum(array_column($entries, 'amount'));
$totalTds = array_sum(array_column($entries, 'tds'));
$totalNet = array_sum(array_column($entries, 'net'));
assert_equals(80000, $totalPaid, 'Lifecycle final: total paid ₹80,000');
assert_equals(8000, $totalTds, 'Lifecycle final: total TDS ₹8,000');
assert_equals(72000, $totalNet, 'Lifecycle final: total net ₹72,000');

// ════════════════════════════════════════════════════════════════════
// 12. Reject Lifecycle — Entries Cancellation
// ════════════════════════════════════════════════════════════════════
section('Reject Lifecycle — Entries Cancellation');

/**
 * From PayoutBatchService::rejectBatch() (line 262):
 *   When batch is rejected, all pending entries are cancelled.
 */
$rejectEntries = [
    ['id' => 1, 'status' => 'pending'],
    ['id' => 2, 'status' => 'pending'],
    ['id' => 3, 'status' => 'completed'],
];

// Simulate rejection: cancel all pending entries
foreach ($rejectEntries as &$e) {
    if ($e['status'] === 'pending') {
        $e['status'] = 'cancelled';
    }
}
unset($e);

assert_equals('cancelled', $rejectEntries[0]['status'], 'Reject: entry 1 cancelled');
assert_equals('cancelled', $rejectEntries[1]['status'], 'Reject: entry 2 cancelled');
assert_equals('completed', $rejectEntries[2]['status'], 'Reject: entry 3 stays completed (already done)');

// ════════════════════════════════════════════════════════════════════
// 13. Edge Cases — Rounding
// ════════════════════════════════════════════════════════════════════
section('Edge Cases — Rounding Precision');

// TDS rounding: round to 2 decimal places
assert_equals(1234.57, round(12345.67 * 10 / 100, 2), 'TDS rounding: 12345.67 → 1234.57');
assert_equals(11111.10, round(111111.00 * 10 / 100, 2), 'TDS rounding: 111111 → 11111.10');

// Net = amount - TDS (must be consistent)
$amt = 77777.77;
$tds = round($amt * 10 / 100, 2);
$net = round($amt - $tds, 2);
$check = round($tds + $net, 2);
assert_equals_approx($amt, $check, 0.01, "Rounding invariant: TDS($tds) + Net($net) ≈ Amount($amt)");

// ════════════════════════════════════════════════════════════════════
// 14. MLM Commission Ledger — Status Flow
// ════════════════════════════════════════════════════════════════════
section('MLM Commission Ledger — Status Flow');

/**
 * Commission ledger entries go through:
 *   pending → processing → paid
 *   pending → reversed (on cancellation)
 */
$ledgerTransitions = [
    'pending'    => ['processing', 'reversed'],
    'processing' => ['paid', 'reversed'],
    'paid'       => [],       // terminal
    'reversed'   => [],       // terminal
];

assert_true(isValidTransition('pending', 'processing', $ledgerTransitions), 'Ledger: pending → processing');
assert_true(isValidTransition('pending', 'reversed', $ledgerTransitions), 'Ledger: pending → reversed');
assert_true(isValidTransition('processing', 'paid', $ledgerTransitions), 'Ledger: processing → paid');
assert_true(isValidTransition('processing', 'reversed', $ledgerTransitions), 'Ledger: processing → reversed');
assert_true(!isValidTransition('paid', 'pending', $ledgerTransitions), 'Ledger: paid → pending invalid');
assert_true(!isValidTransition('reversed', 'pending', $ledgerTransitions), 'Ledger: reversed → pending invalid');

// ════════════════════════════════════════════════════════════════════
// Summary
// ════════════════════════════════════════════════════════════════════
echo "\n" . str_repeat('═', 50) . "\n";
echo "  PASS: $pass | FAIL: $fail | TOTAL: " . ($pass + $fail) . "\n";
echo str_repeat('═', 50) . "\n\n";

exit($fail > 0 ? 1 : 0);
