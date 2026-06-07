<?php $page_title = $page_title ?? 'Issue Cheque'; $page_heading = $page_heading ?? 'Issue New Cheque / DD'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-money-check me-2 text-primary"></i>Issue New Cheque / DD</h2>
        <a href="<?= BASE_URL ?>/admin/finance/cheques" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/cheque-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="instrument_type" class="form-select">
                            <option value="cheque">Cheque</option>
                            <option value="dd">Demand Draft</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
                        <input type="date" name="cheque_date" required class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cheque / DD # <span class="text-danger">*</span></label>
                        <input type="text" name="cheque_number" required class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="1" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                        <select name="bank_account_id" required class="form-select">
                            <option value="">— Select Bank —</option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' — ' . $b['bank_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payee Name <span class="text-danger">*</span></label>
                        <input type="text" name="payee_name" required class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Purpose / Narration</label>
                        <textarea name="purpose" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference Type</label>
                        <select name="reference_type" class="form-select">
                            <option value="">—</option>
                            <option value="vendor">Vendor Payment</option>
                            <option value="expense">Expense</option>
                            <option value="refund">Refund</option>
                            <option value="loan">Loan Repayment</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference ID</label>
                        <input type="number" name="reference_id" class="form-control">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Issue</button>
                    <a href="<?= BASE_URL ?>/admin/finance/cheques" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
