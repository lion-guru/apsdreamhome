<?php
/** @var array $installment */
/** @var array $booking */
$installment = $installment ?? [];
$booking = $booking ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5 class="m-0"><i class="fas fa-indian-rupee-sign me-2"></i><?= __('sale_record_payment') ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="text-muted small"><?= __('sale_booking_num') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small"><?= __('sale_installment_num') ?></div>
                <div class="fw-bold"><?= (int)($installment['installment_number'] ?? 0) ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small"><?= __('sale_due_date') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($installment['due_date'] ?? '')) ?></div>
            </div>
        </div>
        <form method="post" action="<?= htmlspecialchars($base) ?>/admin/sales/installments/<?= (int)($installment['id'] ?? 0) ?>/pay">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_amount_label') ?></label>
                    <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars((string)($installment['amount_due'] ?? 0)) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_payment_date') ?></label>
                    <input type="date" name="paid_date" value="<?= htmlspecialchars(date('Y-m-d')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_mode_label') ?></label>
                    <select name="payment_mode" class="form-select" id="payMode" required>
                        <?php foreach (['cash', 'cheque', 'dd', 'neft', 'rtgs', 'upi', 'card', 'bank_transfer'] as $m): ?>
                            <option value="<?= $m ?>"><?= strtoupper($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4" id="chequeFields" style="display:none">
                    <label class="form-label"><?= __('sale_cheque_dd_num') ?></label>
                    <input type="text" name="cheque_number" class="form-control">
                </div>
                <div class="col-md-4" id="chequeDateField" style="display:none">
                    <label class="form-label"><?= __('sale_cheque_date') ?></label>
                    <input type="date" name="cheque_date" class="form-control">
                </div>
                <div class="col-md-4" id="bankField" style="display:none">
                    <label class="form-label"><?= __('sale_bank_name') ?></label>
                    <input type="text" name="bank_name" class="form-control">
                </div>
                <div class="col-md-4" id="refField" style="display:none">
                    <label class="form-label"><?= __('sale_transaction_ref') ?></label>
                    <input type="text" name="transaction_ref" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_status_label') ?></label>
                    <select name="status" class="form-select" required>
                        <option value="cleared"><?= __('sale_cleared') ?></option>
                        <option value="pending"><?= __('sale_pending_clearing') ?></option>
                        <option value="bounced"><?= __('sale_bounced') ?></option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label"><?= __('sale_notes') ?></label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i><?= __('sale_save_receipt') ?></button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base) ?>/admin/sales/bookings/<?= (int)($installment['booking_id'] ?? 0) ?>/schedule"><?= __('sale_cancel') ?></a>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    var mode = document.getElementById('payMode');
    function refresh() {
        var v = (mode && mode.value) || 'cash';
        var isCheque = (v === 'cheque' || v === 'dd');
        var isBank   = (v === 'neft' || v === 'rtgs' || v === 'upi' || v === 'bank_transfer');
        document.getElementById('chequeFields').style.display    = isCheque ? '' : 'none';
        document.getElementById('chequeDateField').style.display = isCheque ? '' : 'none';
        document.getElementById('bankField').style.display       = isBank   ? '' : 'none';
        document.getElementById('refField').style.display        = isBank   ? '' : 'none';
    }
    if (mode) { mode.addEventListener('change', refresh); refresh(); }
})();
</script>
