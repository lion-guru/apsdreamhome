<?php
/** @var array $booking */
/** @var array $plots */
$booking = $booking ?? [];
$plots = $plots ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5 class="m-0 text-primary"><i class="fas fa-right-left me-2"></i>Swap Plot — <?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i>
            Same customer moves to a different plot. Paid money carries over on the same booking (receipts untouched).
            Old plot returns to Available; new plot becomes Booked. Regenerate the EMI schedule afterwards if prices differ.
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Current Plot</label>
                <input type="text" class="form-control" value="#<?= htmlspecialchars((string)($booking['plot_number'] ?? '')) ?> (<?= htmlspecialchars((string)($booking['colony_name'] ?? '')) ?>)" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Deal Value</label>
                <input type="text" class="form-control" value="Rs.<?= number_format((float)($booking['total_plot_value'] ?? 0)) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars((string)($booking['customer_name'] ?? '')) ?>" readonly>
            </div>
        </div>
        <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/swap">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">New Plot *</label>
                    <select name="new_plot_id" id="swapPlotSelect" class="form-select" required>
                        <option value="">Select new plot</option>
                        <?php foreach ($plots as $p): ?>
                            <option value="<?= (int)($p['id'] ?? 0) ?>" data-price="<?= (float)($p['total_price'] ?? 0) ?>">
                                #<?= htmlspecialchars((string)($p['plot_number'] ?? '')) ?> — <?= htmlspecialchars((string)($p['colony_name'] ?? '')) ?>
                                (Rs.<?= number_format((float)($p['total_price'] ?? 0)) ?><?= !empty($p['area_sqft']) ? ', ' . number_format((float)$p['area_sqft']) . ' sqft' : '' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Balance Difference</label>
                    <input type="text" class="form-control" id="swapDiffPreview" value="-" readonly>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Reason *</label>
                    <input type="text" name="reason" class="form-control" required placeholder="e.g. Customer wants corner plot">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Swap Charge (Rs.)</label>
                    <input type="number" step="0.01" min="0" name="swap_charge" value="0" class="form-control">
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit" data-aps-confirm="Swap plot? Paid money carries over to the new plot.">
                    <i class="fas fa-right-left me-1"></i>Confirm Swap
                </button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>">Back</a>
            </div>
        </form>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var oldTotal = <?= (float)($booking['total_plot_value'] ?? 0) ?>;
    var sel = document.getElementById('swapPlotSelect');
    var preview = document.getElementById('swapDiffPreview');
    if (!sel || !preview) return;
    function fmt(n) { return 'Rs.' + Number(n).toLocaleString('en-IN'); }
    sel.addEventListener('change', function() {
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) { preview.value = '-'; return; }
        var diff = parseFloat(opt.getAttribute('data-price') || 0) - oldTotal;
        preview.value = fmt(diff) + (diff > 0 ? ' (customer pays more)' : (diff < 0 ? ' (refundable excess)' : ' (equal value)'));
    });
})();
</script>
