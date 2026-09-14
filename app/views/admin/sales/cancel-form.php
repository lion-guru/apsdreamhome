<?php
/** @var array $booking */
$booking = $booking ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

// ═══ LEGAL COMPLIANCE: Compute refund deduction timeline from booking date ═══
// Per Tripartite Master Legal Deed V8:
// - Token/Booking Amount: 100% NON-REFUNDABLE (always forfeited)
// - Mandatory Base Admin Charge: 5% of total plot consideration
// - Within 1 year: 25% admin deduction on total consideration
// - 1-3 years: 10% admin deduction on total consideration
// - 3+ years: 5% admin deduction on total consideration
// - 180-working-day staggered payout, zero cash (NEFT/RTGS only)
$bookingDate = $booking['booking_date'] ?? null;
$bookingAmount = (float)($booking['booking_amount'] ?? 0);
$plotPrice = (float)($booking['plot_total_price'] ?? $booking['total_plot_value'] ?? 0);
$plotArea = (float)($booking['area_sqft'] ?? 0);

$bookingTimestamp = $bookingDate ? strtotime($bookingDate) : 0;
$now = time();
$daysSinceBooking = $bookingTimestamp ? (int)floor(($now - $bookingTimestamp) / 86400) : 0;
$monthsSinceBooking = $bookingTimestamp ? (int)floor($daysSinceBooking / 30) : 0;
$yearsSinceBooking = $bookingTimestamp ? (int)floor($daysSinceBooking / 365) : 0;

// Per Master Deed V8: Token is ALWAYS forfeited, admin slabs apply on total consideration
$mandatoryBaseAdmin = round($plotPrice * 0.05, 2); // 5% mandatory base admin
if ($monthsSinceBooking <= 12) {
    $adminDeductionPct = 25; // 25% within 1 year
    $tierLabel = 'Within 1 Year — 25% Admin Deduction';
    $tierColor = '#dc3545';
    $tierIcon = 'fa-ban';
} elseif ($yearsSinceBooking <= 3) {
    $adminDeductionPct = 10; // 10% for 1-3 years
    $tierLabel = '1–3 Years — 10% Admin Deduction';
    $tierColor = '#ffc107';
    $tierIcon = 'fa-exclamation-triangle';
} else {
    $adminDeductionPct = 5; // 5% for 3+ years
    $tierLabel = '3+ Years — 5% Admin Deduction';
    $tierColor = '#198754';
    $tierIcon = 'fa-check-circle';
}

// Token forfeited (100%), plus admin slab on total consideration
$tokenForfeited = $bookingAmount; // 100% non-refundable
$adminDeduction = round($plotPrice * $adminDeductionPct / 100, 2);
$totalDeduction = $tokenForfeited + $mandatoryBaseAdmin + $adminDeduction;
$refundAmount = max(0, round($plotPrice - $totalDeduction, 2));
$refundPct = $plotPrice > 0 ? round($refundAmount / $plotPrice * 100, 1) : 0;
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5 class="m-0 text-danger"><i class="fas fa-ban me-2"></i><?= __('sale_cancel_booking') ?> — <?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></h5>
    </div>
    <div class="aps-cp-card-body">

        <!-- ═══ LEGAL COMPLIANCE: Dynamic Deduction Warning ═══ -->
        <div class="border rounded-3 p-4 mb-4" style="background: #f8f9fa; border-color: <?= $tierColor ?> !important; border-width: 2px !important;">
            <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: <?= $tierColor ?>; color: #fff;">
                    <i class="fas <?= $tierIcon ?> fa-lg"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0" style="color: <?= $tierColor ?>;"><?= $tierLabel ?></h6>
                    <small class="text-muted">Booking date: <?= $bookingDate ? date('d M Y', $bookingTimestamp) : 'N/A' ?> — <?= $daysSinceBooking ?> days elapsed</small>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="text-center p-2 rounded" style="background: #f8d7da;">
                        <div class="small text-muted mb-1">Token Forfeited (100%)</div>
                        <div class="fw-bold fs-5 text-danger">−₹<?= number_format($tokenForfeited, 2) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-2 rounded" style="background: #fff3cd;">
                        <div class="small text-muted mb-1">Base Admin (5%) + <?= $adminDeductionPct ?>% Slab</div>
                        <div class="fw-bold fs-5 text-danger">−₹<?= number_format($mandatoryBaseAdmin + $adminDeduction, 2) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-2 rounded" style="background: #d1e7dd;">
                        <div class="small text-muted mb-1">Net Refund (180-day NEFT/RTGS)</div>
                        <div class="fw-bold fs-5 text-success">₹<?= number_format($refundAmount, 2) ?></div>
                    </div>
                </div>
            </div>

            <div class="small" style="color: #495057; line-height: 1.6;">
                <strong>Legal basis:</strong> Per Tripartite Master Legal Deed V8 — Token 100% non-refundable, mandatory 5% base admin charge, <?= $adminDeductionPct ?>% admin deduction on total consideration (<?= $daysSinceBooking ?> days elapsed). Net refund disbursed over 180 working days via NEFT/RTGS to primary allottee only — zero cash.
                (<a href="<?= $base ?>/legal/refund-policy" target="_blank" class="fw-bold text-decoration-underline">view policy</a>).
            </div>
        </div>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= __('sale_cancel_warning') ?>
        </div>

        <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/cancel" id="cancelBookingForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <input type="hidden" name="token_forfeited" value="<?= (int)$tokenForfeited ?>">
            <input type="hidden" name="mandatory_base_admin" value="<?= (int)$mandatoryBaseAdmin ?>">
            <input type="hidden" name="admin_deduction_pct" value="<?= (int)$adminDeductionPct ?>">
            <input type="hidden" name="total_deduction" value="<?= (int)$totalDeduction ?>">
            <input type="hidden" name="refund_amount" value="<?= (int)$refundAmount ?>">
            <input type="hidden" name="booking_days" value="<?= (int)$daysSinceBooking ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_reason') ?> *</label>
                    <select name="reason" class="form-select" required id="cancelReason">
                        <option value="">— Select reason —</option>
                        <?php foreach (['Customer request', 'EMI default', 'Title issue', 'Payment failure', 'Force majeure', 'Other'] as $r): ?>
                            <option value="<?= htmlspecialchars($r ?? '') ?>"><?= htmlspecialchars($r ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_cancellation_charge') ?></label>
                    <input type="number" step="0.01" name="cancellation_charge" value="<?= $deductionAmount ?>" class="form-control" id="cancelCharge">
                    <small class="text-muted">Auto-calculated: <?= $deductionPct ?>% of ₹<?= number_format($bookingAmount, 2) ?></small>
                </div>
                <div class="col-12">
                    <label class="form-label"><?= __('sale_notes') ?></label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional context for the audit log..."></textarea>
                </div>
            </div>

            <!-- ═══ LEGAL COMPLIANCE: Admin Confirmation Checkbox ═══ -->
            <div class="border border-danger rounded-3 p-3 mt-4" style="background: #fff5f5;">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="adminConfirmDeduction"
                           style="border-color: #dc3545; width: 1.3em; height: 1.3em; margin-top: 0.15em;">
                    <label class="form-check-label fw-semibold" for="adminConfirmDeduction" style="font-size: 0.92rem; line-height: 1.5; color: #1a1a2e;">
                        <i class="fas fa-calculator text-danger me-1"></i>
                        I confirm the refund deduction of <strong>₹<?= number_format($deductionAmount, 2) ?></strong> (<?= $deductionPct ?>%) is correct per the applicable refund policy.
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="adminConfirmPlotRelease"
                           style="border-color: #dc3545; width: 1.3em; height: 1.3em; margin-top: 0.15em;">
                    <label class="form-check-label fw-semibold" for="adminConfirmPlotRelease" style="font-size: 0.92rem; line-height: 1.5; color: #1a1a2e;">
                        <i class="fas fa-home text-danger me-1"></i>
                        I confirm the plot will be released and made available for re-sale upon cancellation.
                    </label>
                </div>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="adminConfirmLegal"
                           style="border-color: #dc3545; width: 1.3em; height: 1.3em; margin-top: 0.15em;">
                    <label class="form-check-label fw-semibold" for="adminConfirmLegal" style="font-size: 0.92rem; line-height: 1.5; color: #1a1a2e;">
                        <i class="fas fa-gavel text-danger me-1"></i>
                        I understand this cancellation is governed by the Tripartite Master Deed and applicable law.
                    </label>
                </div>
                <div id="adminConfirmError" class="text-danger small mt-2 d-none">
                    <i class="fas fa-exclamation-circle me-1"></i>All three confirmations are required before cancellation can proceed.
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-danger fw-bold" type="submit" id="cancelBtn" disabled>
                    <i class="fas fa-ban me-1"></i><?= __('sale_cancel_booking') ?>
                </button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>"><?= __('sale_back') ?></a>
            </div>
        </form>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var checkboxes = ['adminConfirmDeduction', 'adminConfirmPlotRelease', 'adminConfirmLegal'];
    var submitBtn = document.getElementById('cancelBtn');
    var errorEl = document.getElementById('adminConfirmError');

    function checkAllConfirmed() {
        var allChecked = checkboxes.every(function(id) {
            return document.getElementById(id).checked;
        });
        submitBtn.disabled = !allChecked;
        if (allChecked) {
            errorEl.classList.add('d-none');
        }
    }

    checkboxes.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', checkAllConfirmed);
        }
    });

    // Prevent form submit if not all confirmed
    document.getElementById('cancelBookingForm').addEventListener('submit', function(e) {
        var allChecked = checkboxes.every(function(id) {
            return document.getElementById(id).checked;
        });
        if (!allChecked) {
            e.preventDefault();
            errorEl.classList.remove('d-none');
            return false;
        }

        if (!confirm('CONFIRM CANCELLATION\n\nBooking: <?= $booking["booking_number"] ?? "" ?>\nRefund: ₹<?= number_format($refundAmount, 2) ?>\nDeduction: ₹<?= number_format($deductionAmount, 2) ?>\n\nThis action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
})();
</script>
