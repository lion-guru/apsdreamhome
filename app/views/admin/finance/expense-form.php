<?php $page_title = $page_title ?? __('finance_submit_expense'); $page_heading = $page_heading ?? __('finance_submit_new_expense'); $expense = $expense ?? null; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i><?php echo __('finance_submit_expense'); ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/expenses" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?php echo __('finance_back'); ?></a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/expense-store" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label"><?php echo __('finance_date'); ?> <span class="text-danger">*</span></label><input type="date" name="expense_date" required class="form-control" value="<?= htmlspecialchars($expense['expense_date'] ?? date('Y-m-d')) ?>"></div>
                    <div class="col-md-3"><label class="form-label"><?php echo __('finance_category'); ?> <span class="text-danger">*</span></label>
                        <select name="category" required class="form-select">
                            <?php foreach (['office','travel','marketing','utilities','salary','maintenance','professional_fee','rent','misc'] as $c): ?>
                                <option value="<?= $c ?>" <?= ($expense['category'] ?? '') === $c ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$c)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label"><?php echo __('finance_amount'); ?> (₹) <span class="text-danger">*</span></label><input type="number" name="amount" step="0.01" min="1" required class="form-control" value="<?= htmlspecialchars($expense['amount'] ?? '') ?>"></div>
                    <div class="col-md-3"><label class="form-label"><?php echo __('finance_payment_mode'); ?></label>
                        <select name="payment_mode" class="form-select">
                            <option value="cash"><?php echo __('finance_cash'); ?></option>
                            <option value="bank"><?php echo __('finance_bank_transfer'); ?></option>
                            <option value="cheque"><?php echo __('finance_cheque'); ?></option>
                            <option value="upi"><?php echo __('finance_upi'); ?></option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label"><?php echo __('finance_description'); ?> <span class="text-danger">*</span></label><textarea name="description" required class="form-control" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label"><?php echo __('finance_bank_account'); ?></label>
                        <select name="bank_account_id" class="form-select">
                            <option value=""><?php echo __('finance_cash_other'); ?></option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label"><?php echo __('finance_vendor_id_optional'); ?></label><input type="number" name="vendor_id" class="form-control"></div>
                    <div class="col-12"><label class="form-label"><?php echo __('finance_supporting_document'); ?></label><input type="file" name="supporting_doc" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i><?php echo __('finance_submit_for_approval'); ?></button>
                    <a href="<?= BASE_URL ?>/admin/finance/expenses" class="btn btn-outline-secondary"><?php echo __('finance_cancel'); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
