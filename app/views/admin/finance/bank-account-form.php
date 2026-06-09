<?php $page_title = $page_title ?? __('finance_bank_account'); $page_heading = $page_heading ?? __('finance_bank_account'); $bank = $bank ?? null; $id = (int)($bank['id'] ?? 0); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-university me-2 text-primary"></i><?= $id > 0 ? __('finance_edit_bank_account') : __('finance_add_bank_account') ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?php echo __('finance_back'); ?></a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/bank-account-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_account_name'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" required class="form-control" value="<?= htmlspecialchars($bank['account_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_bank_name'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" required class="form-control" value="<?= htmlspecialchars($bank['bank_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_account_number'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" required class="form-control" value="<?= htmlspecialchars($bank['account_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?php echo __('finance_ifsc'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="ifsc_code" required class="form-control text-uppercase" maxlength="11" value="<?= htmlspecialchars($bank['ifsc_code'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?php echo __('finance_branch'); ?></label>
                        <input type="text" name="branch" class="form-control" value="<?= htmlspecialchars($bank['branch'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo __('finance_account_type'); ?></label>
                        <select name="account_type" class="form-select">
                            <option value="current" <?= ($bank['account_type'] ?? '') === 'current' ? 'selected' : '' ?>><?php echo __('finance_current'); ?></option>
                            <option value="savings" <?= ($bank['account_type'] ?? '') === 'savings' ? 'selected' : '' ?>><?php echo __('finance_savings'); ?></option>
                            <option value="escrow" <?= ($bank['account_type'] ?? '') === 'escrow' ? 'selected' : '' ?>><?php echo __('finance_escrow'); ?></option>
                            <option value="overdraft" <?= ($bank['account_type'] ?? '') === 'overdraft' ? 'selected' : '' ?>><?php echo __('finance_overdraft'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo __('finance_opening_balance'); ?></label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="<?= htmlspecialchars($bank['opening_balance'] ?? '0.00') ?>" <?= $id > 0 ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo __('finance_flags'); ?></label>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="is_escrow" value="1" id="escrow" <?= !empty($bank['is_escrow']) ? 'checked' : '' ?>><label class="form-check-label" for="escrow"><?php echo __('finance_rera_escrow_account'); ?></label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="gst_registered" value="1" id="gst" <?= !empty($bank['gst_registered']) ? 'checked' : '' ?>><label class="form-check-label" for="gst"><?php echo __('finance_gst_registered'); ?></label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" id="active" <?= !empty($bank['active']) || $id === 0 ? 'checked' : '' ?>><label class="form-check-label" for="active"><?php echo __('finance_active'); ?></label></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_signatory_name'); ?></label>
                        <input type="text" name="signatory_name" class="form-control" value="<?= htmlspecialchars($bank['signatory_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo __('finance_signatory_pan'); ?></label>
                        <input type="text" name="signatory_pan" class="form-control text-uppercase" maxlength="10" value="<?= htmlspecialchars($bank['signatory_pan'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?php echo __('finance_save'); ?></button>
                    <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-outline-secondary"><?php echo __('finance_cancel'); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
