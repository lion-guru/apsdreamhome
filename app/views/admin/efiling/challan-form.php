<?php
$page_title = $page_title ?? 'New TDS Challan (Form 281)';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-money-check me-2 text-warning"></i><?= htmlspecialchars($page_title) ?></h4>
    </div>
    <a href="/admin/efiling/tds/challans" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Challans</a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body aps-cp-card-body">
        <form method="POST" action="/admin/efiling/tds/challans/create">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Assessment Year <span class="text-danger">*</span></label>
                    <select name="assessment_year" class="form-select form-select-sm" required>
                        <option value="">Select</option>
                        <?php foreach ($ay_list as $ay): ?>
                            <option value="<?= $ay ?>" <?= $ay === $current_ay ? 'selected' : '' ?>><?= $ay ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">TDS Section <span class="text-danger">*</span></label>
                    <select name="section" class="form-select form-select-sm" required>
                        <option value="">Select</option>
                        <option value="194C">194C - Contractor</option>
                        <option value="194H">194H - Commission/Brokerage</option>
                        <option value="194IA">194IA - Property Transfer</option>
                        <option value="194IB">194IB - Rent</option>
                        <option value="194I">194I - Rent (Property)</option>
                        <option value="194J">194J - Professional/Technical Fees</option>
                        <option value="194M">194M - Contract (₹50L+)</option>
                        <option value="194N">194N - Cash Withdrawal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Deposit Date <span class="text-danger">*</span></label>
                    <input type="date" name="deposit_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">BSR Code (7 digits)</label>
                    <input type="text" name="bsr_code" class="form-control form-control-sm" maxlength="7" placeholder="e.g. 0320001">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Challan Serial Number</label>
                    <input type="text" name="challan_serial" class="form-control form-control-sm" maxlength="5" placeholder="e.g. 00001">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Deposited Via <span class="text-danger">*</span></label>
                    <select name="deposited_via" class="form-select form-select-sm" required>
                        <option value="bank">Bank Branch</option>
                        <option value="online">Online (e-Payment)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">TDS Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="tds_amount" class="form-control form-control-sm" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Interest (₹)</label>
                    <input type="number" name="interest_amount" class="form-control form-control-sm" step="0.01" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Penalty (₹)</label>
                    <input type="number" name="penalty_amount" class="form-control form-control-sm" step="0.01" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Late Fee (₹)</label>
                    <input type="number" name="late_fee" class="form-control form-control-sm" step="0.01" value="0">
                </div>
                <div class="col-12">
                    <label class="form-label small">Remarks</label>
                    <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Optional notes about this challan..."></textarea>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-save me-1"></i>Save Challan</button>
                <a href="/admin/efiling/tds/challans" class="btn btn-outline-secondary btn-sm ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
?>
