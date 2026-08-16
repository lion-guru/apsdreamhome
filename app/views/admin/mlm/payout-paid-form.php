<?php
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5 class="m-0"><i class="fas fa-check-circle me-2"></i>Mark Payout Paid</h5>
    </div>
    <div class="aps-cp-card-body">
        <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/<?= (int)($payout['id'] ?? 0) ?>/mark-paid">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="bank_transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                        <option value="upi">UPI</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                        <option value="wallet">Wallet Credit</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Account</label>
                    <input type="text" name="bank_account" class="form-control" placeholder="XXXX1234">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IFSC</label>
                    <input type="text" name="ifsc" class="form-control" placeholder="SBIN0001234">
                </div>
                <div class="col-md-4">
                    <label class="form-label">UPI ID</label>
                    <input type="text" name="upi_id" class="form-control" placeholder="name@bank">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cheque Number</label>
                    <input type="text" name="cheque_number" class="form-control" placeholder="000123">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transaction Reference</label>
                    <input type="text" name="transaction_ref" class="form-control" placeholder="UTR / ref no.">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Confirm Payment
                    </button>
                    <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
