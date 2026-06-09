<?php $page_title = $page_title ?? __('finance_issue_cheque'); $page_heading = $page_heading ?? __('finance_issue_new_cheque_dd'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-money-check me-2 text-primary"></i><?php echo __('finance_issue_new_cheque_dd'); ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/cheques" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?php echo __('finance_back'); ?></a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/cheque-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"><?php echo __('finance_type'); ?></label>
                        <select name="instrument_type" class="form-select">
                            <option value="cheque"><?php echo __('finance_cheque'); ?></option>
                            <option value="dd"><?php echo __('finance_demand_draft'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?php echo __('finance_cheque_date'); ?> <span class="text-danger">*</span></label>
                        <input type="date" name="cheque_date" required class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?php echo __('finance_cheque_hash'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="cheque_number" required class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?php echo __('finance_amount'); ?> (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="1" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_bank_account'); ?> <span class="text-danger">*</span></label>
                        <select name="bank_account_id" required class="form-select">
                            <option value="">— <?php echo __('finance_select_bank'); ?> —</option>
                            <?php foreach (($banks ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' — ' . $b['bank_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_payee'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="payee_name" required class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?php echo __('finance_purpose_narration'); ?></label>
                        <textarea name="purpose" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_reference_type'); ?></label>
                        <select name="reference_type" class="form-select">
                            <option value="">—</option>
                            <option value="vendor"><?php echo __('finance_vendor_payment'); ?></option>
                            <option value="expense"><?php echo __('finance_expense'); ?></option>
                            <option value="refund"><?php echo __('finance_refund'); ?></option>
                            <option value="loan"><?php echo __('finance_loan_repayment'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_reference_id'); ?></label>
                        <input type="number" name="reference_id" class="form-control">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?php echo __('finance_issue'); ?></button>
                    <a href="<?= BASE_URL ?>/admin/finance/cheques" class="btn btn-outline-secondary"><?php echo __('finance_cancel'); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
