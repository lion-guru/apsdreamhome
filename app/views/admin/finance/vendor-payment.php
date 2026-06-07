<?php $page_title = $page_title ?? 'New Vendor Payment'; $page_heading = $page_heading ?? 'Record Vendor Payment'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i>Record Vendor Payment</h2>
        <a href="<?= BASE_URL ?>/admin/finance/vendors" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/vendor-payment-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Payment Date <span class="text-danger">*</span></label><input type="date" name="payment_date" required class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Vendor Type <span class="text-danger">*</span></label>
                        <select name="vendor_type" required class="form-select">
                            <option value="contractor">Contractor</option>
                            <option value="broker">Broker</option>
                            <option value="consultant">Consultant</option>
                            <option value="supplier">Supplier</option>
                            <option value="employee">Employee</option>
                            <option value="land_owner">Land Owner</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Vendor ID</label><input type="number" name="vendor_id" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Vendor Name <span class="text-danger">*</span></label><input type="text" name="vendor_name" required class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Vendor PAN</label><input type="text" name="vendor_pan" class="form-control text-uppercase" maxlength="10"></div>
                    <div class="col-md-3"><label class="form-label">Bill / Invoice #</label><input type="text" name="bill_number" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Amount (₹) <span class="text-danger">*</span></label><input type="number" name="amount" step="0.01" min="1" required class="form-control" id="vAmt" oninput="vCalc()"></div>
                    <div class="col-md-3"><label class="form-label">TDS Deducted (₹)</label><input type="number" name="tds_deducted" step="0.01" class="form-control" id="vTds" oninput="vCalc()"></div>
                    <div class="col-md-3"><label class="form-label">GST Amount (₹)</label><input type="number" name="gst_amount" step="0.01" class="form-control" id="vGst" oninput="vCalc()"></div>
                    <div class="col-md-3"><label class="form-label">Net Payable (₹)</label><input type="number" step="0.01" class="form-control" id="vNet" readonly></div>
                    <div class="col-md-3"><label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">— Select —</option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Narration</label><textarea name="narration" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Record Payment</button>
                    <a href="<?= BASE_URL ?>/admin/finance/vendors" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function vCalc(){
    const a = parseFloat(document.getElementById('vAmt').value)||0;
    const t = parseFloat(document.getElementById('vTds').value)||0;
    const g = parseFloat(document.getElementById('vGst').value)||0;
    document.getElementById('vNet').value = (a - t + (g > 0 ? 0 : 0)).toFixed(2);
}
</script>
