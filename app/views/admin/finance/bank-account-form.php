<?php $page_title = $page_title ?? 'Bank Account'; $page_heading = $page_heading ?? 'Bank Account'; $bank = $bank ?? null; $id = (int)($bank['id'] ?? 0); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-university me-2 text-primary"></i><?= $id > 0 ? 'Edit Bank Account' : 'New Bank Account' ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/finance/bank-account-store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" required class="form-control" value="<?= htmlspecialchars($bank['account_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" required class="form-control" value="<?= htmlspecialchars($bank['bank_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" required class="form-control" value="<?= htmlspecialchars($bank['account_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                        <input type="text" name="ifsc_code" required class="form-control text-uppercase" maxlength="11" value="<?= htmlspecialchars($bank['ifsc_code'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Branch</label>
                        <input type="text" name="branch" class="form-control" value="<?= htmlspecialchars($bank['branch'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account Type</label>
                        <select name="account_type" class="form-select">
                            <?php foreach (['current','savings','escrow','overdraft'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($bank['account_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="<?= htmlspecialchars($bank['opening_balance'] ?? '0.00') ?>" <?= $id > 0 ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Flags</label>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="is_escrow" value="1" id="escrow" <?= !empty($bank['is_escrow']) ? 'checked' : '' ?>><label class="form-check-label" for="escrow">RERA Escrow Account</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="gst_registered" value="1" id="gst" <?= !empty($bank['gst_registered']) ? 'checked' : '' ?>><label class="form-check-label" for="gst">GST Registered</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" id="active" <?= !empty($bank['active']) || $id === 0 ? 'checked' : '' ?>><label class="form-check-label" for="active">Active</label></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Signatory Name</label>
                        <input type="text" name="signatory_name" class="form-control" value="<?= htmlspecialchars($bank['signatory_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Signatory PAN</label>
                        <input type="text" name="signatory_pan" class="form-control text-uppercase" maxlength="10" value="<?= htmlspecialchars($bank['signatory_pan'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                    <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
