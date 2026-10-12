<?php
/** Token booking form for one available plot. */
/** @var array $plot */
$plot = $plot ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-handshake me-2"></i>Token Booking (Bayana)</h4>
        <p class="text-muted mb-0">Plot <?= $e($plot['plot_number'] ?? '') ?>, <?= $e($plot['colony_name'] ?? '') ?> — locks the plot for 7 days</p>
    </div>
    <a href="<?= $base ?>/admin/plots/map" class="btn btn-outline-secondary btn-sm">← Map</a>
</div>
<div class="aps-cp-card"><div class="aps-cp-card-body">
    <form method="POST" action="<?= $base ?>/admin/sales/token-booking/store">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="plot_id" value="<?= (int)($plot['id'] ?? 0) ?>">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label small">Buyer name *</label><input type="text" name="customer_name" class="form-control" required maxlength="150"></div>
            <div class="col-md-4"><label class="form-label small">Buyer phone *</label><input type="tel" name="customer_phone" class="form-control" required maxlength="15" placeholder="10-digit mobile"></div>
            <div class="col-md-4"><label class="form-label small">Buyer Aadhaar (optional)</label><input type="text" name="customer_aadhaar" class="form-control" maxlength="20"></div>
            <div class="col-md-3"><label class="form-label small">Token amount ₹ *</label><input type="number" step="1" min="1" name="token_amount" class="form-control" required value="11000"></div>
            <div class="col-md-3"><label class="form-label small">Payment mode</label>
                <select name="payment_mode" class="form-select"><option value="upi">UPI</option><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="cheque">Cheque</option></select>
            </div>
            <div class="col-md-3"><label class="form-label small">Payment ref (UTR/cheque no.)</label><input type="text" name="payment_ref" class="form-control" maxlength="100"></div>
            <div class="col-md-3"><label class="form-label small">Associate ID (optional)</label><input type="number" name="associate_id" class="form-control" min="0"></div>
            <div class="col-12"><label class="form-label small">Notes</label><textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea></div>
        </div>
        <button type="submit" class="btn btn-success mt-3"><i class="fas fa-lock me-1"></i>Collect Token &amp; Lock Plot 7 Days</button>
    </form>
</div></div>
