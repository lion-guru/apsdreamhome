<?php $page_title = $page_title ?? 'Submit Expense'; $page_heading = $page_heading ?? 'Submit New Expense'; $expense = $expense ?? null; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i>Submit Expense</h2>
        <a href="<?= BASE_URL ?>/admin/finance/expenses" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/expense-store" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Date <span class="text-danger">*</span></label><input type="date" name="expense_date" required class="form-control" value="<?= htmlspecialchars($expense['expense_date'] ?? date('Y-m-d')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" required class="form-select">
                            <?php foreach (['office','travel','marketing','utilities','salary','maintenance','professional_fee','rent','misc'] as $c): ?>
                                <option value="<?= $c ?>" <?= ($expense['category'] ?? '') === $c ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$c)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Amount (₹) <span class="text-danger">*</span></label><input type="number" name="amount" step="0.01" min="1" required class="form-control" value="<?= htmlspecialchars($expense['amount'] ?? '') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Description <span class="text-danger">*</span></label><textarea name="description" required class="form-control" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">— Cash / Other —</option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Vendor ID (optional)</label><input type="number" name="vendor_id" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Supporting Document</label><input type="file" name="supporting_doc" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Submit for Approval</button>
                    <a href="<?= BASE_URL ?>/admin/finance/expenses" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
