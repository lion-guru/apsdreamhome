<?php
/**
 * User Bank Details Page - Modern UI
 * Add/Edit bank account details with live IFSC lookup
 */

$page_title = $page_title ?? __('bank_page_title', null, 'Bank Account Details');

$db = \App\Core\Database\Database::getInstance();
$userId = $_SESSION['user_id'] ?? 0;

$bankAccount = $db->fetch(
    "SELECT * FROM user_bank_accounts WHERE user_id = ? AND is_primary = 1 LIMIT 1",
    [$userId]
);

$allAccounts = $db->fetchAll(
    "SELECT * FROM user_bank_accounts WHERE user_id = ? ORDER BY is_primary DESC, created_at DESC",
    [$userId]
);

$banks = $db->fetchAll("SELECT id, name, short_name FROM banks WHERE is_active = 1 ORDER BY name LIMIT 30");

$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">

            <div class="aps-cp-hero">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-university me-2"></i><?= __('bank_page_title', null, 'Bank Account Details') ?></h2>
                        <p><?= __('bank_hero_subtitle', null, 'Add your bank account to receive commission payments, refunds and rental income securely.') ?></p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="<?= BASE_URL ?>/user/profile" class="btn btn-light">
                            <i class="fas fa-user me-2"></i><?= __('bank_back_to_profile', null, 'Back to Profile') ?>
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($success): ?>
            <div class="aps-cp-card mb-3" class="style-1416">
                <div class="aps-cp-card-body py-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success fa-lg me-3"></i>
                        <div class="flex-grow-1"><?= htmlspecialchars($success) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="aps-cp-card mb-3" class="style-53011">
                <div class="aps-cp-card-body py-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle text-danger fa-lg me-3"></i>
                        <div class="flex-grow-1"><?= htmlspecialchars($error) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="aps-cp-card">
                        <div class="aps-cp-card-header" class="style-75848">
                            <h4 class="style-56551">
                                <i class="fas <?= $bankAccount ? 'fa-edit' : 'fa-plus-circle' ?>" class="style-56551"></i>
                                <?= $bankAccount ? __('bank_form_update_title', null, 'Update Bank Account') : __('bank_form_add_title', null, 'Add Bank Account') ?>
                            </h4>
                        </div>
                        <div class="aps-cp-card-body">
                            <form action="<?= BASE_URL ?>/user/bank-details/save" method="POST" data-aps-ajax id="bankForm" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                <div class="aps-cp-form-section">
                                    <h6><i class="fas fa-user-circle"></i> <?= __('bank_section_holder', null, 'Account Holder') ?></h6>
                                    <div class="aps-form-field">
                                        <label class="aps-cp-label" for="account_holder">
                                            <?= __('bank_label_account_holder', null, 'Account Holder Name') ?>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" name="account_holder" id="account_holder" class="form-control" required
                                               value="<?= htmlspecialchars($bankAccount['account_holder'] ?? $_SESSION['user_name'] ?? '') ?>"
                                               placeholder="<?= __('bank_ph_account_holder', null, 'As per bank records') ?>"
                                               autocomplete="name" maxlength="100">
                                    </div>
                                </div>

                                <div class="aps-cp-form-section">
                                    <h6><i class="fas fa-hashtag"></i> <?= __('bank_section_account', null, 'Account & IFSC') ?></h6>

                                    <div class="aps-form-field">
                                        <label class="aps-cp-label" for="account_number">
                                            <?= __('bank_label_account_number', null, 'Account Number') ?>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" name="account_number" id="account_number" class="form-control" required
                                               value="<?= htmlspecialchars($bankAccount['account_number'] ?? '') ?>"
                                               placeholder="<?= __('bank_ph_account_number', null, 'Enter account number') ?>"
                                               autocomplete="off" inputmode="numeric" pattern="[0-9]{9,18}">
                                        <div class="form-text">
                                            <i class="fas fa-shield-alt"></i>
                                            <?= __('bank_help_account_number', null, 'Encrypted and stored securely. Only shown masked to you.') ?>
                                        </div>
                                    </div>

                                    <div class="aps-form-field">
                                        <label class="aps-cp-label" for="ifsc_code">
                                            <?= __('bank_label_ifsc', null, 'IFSC Code') ?>
                                            <span class="required">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" name="ifsc_code" id="ifsc_code" class="form-control"
                                                   value="<?= htmlspecialchars($bankAccount['ifsc_code'] ?? '') ?>"
                                                   placeholder="<?= __('bank_ph_ifsc', null, 'e.g., SBIN0001234') ?>"
                                                   class="style-32119" maxlength="11" required
                                                   data-aps-ifsc
                                                   data-aps-ifsc-status="#ifscStatus"
                                                   data-aps-ifsc-bank="#bank_name"
                                                   data-aps-ifsc-branch="#branch_name"
                                                   data-aps-ifsc-trigger="#lookupIfsc">
                                            <button type="button" class="btn" id="lookupIfsc" aria-label="Lookup IFSC">
                                                <i class="fas fa-search"></i>
                                                <span class="d-none d-sm-inline ms-1"><?= __('bank_btn_lookup', null, 'Lookup') ?></span>
                                            </button>
                                        </div>
                                        <div id="ifscStatus" class="aps-cp-ifsc-status"></div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6 aps-form-field">
                                            <label class="aps-cp-label" for="bank_name">
                                                <i class="fas fa-university text-muted"></i>
                                                <?= __('bank_label_bank_name', null, 'Bank Name') ?>
                                            </label>
                                            <input type="text" name="bank_name" id="bank_name" class="form-control"
                                                   value="<?= htmlspecialchars($bankAccount['bank_name'] ?? '') ?>"
                                                   placeholder="<?= __('bank_ph_bank_name_autofill', null, 'Auto-filled from IFSC') ?>" readonly>
                                        </div>
                                        <div class="col-md-6 aps-form-field">
                                            <label class="aps-cp-label" for="branch_name">
                                                <i class="fas fa-map-marker-alt text-muted"></i>
                                                <?= __('bank_label_branch_name', null, 'Branch Name') ?>
                                            </label>
                                            <input type="text" name="branch_name" id="branch_name" class="form-control"
                                                   value="<?= htmlspecialchars($bankAccount['branch_name'] ?? '') ?>"
                                                   placeholder="<?= __('bank_ph_branch_name_autofill', null, 'Auto-filled from IFSC') ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="aps-cp-form-section">
                                    <h6><i class="fas fa-cog"></i> <?= __('bank_section_options', null, 'Options') ?></h6>

                                    <div class="aps-form-field">
                                        <label class="aps-cp-label" for="account_type">
                                            <?= __('bank_label_account_type', null, 'Account Type') ?>
                                        </label>
                                        <select name="account_type" id="account_type" class="form-select">
                                            <option value="savings" <?= ($bankAccount['account_type'] ?? 'savings') === 'savings' ? 'selected' : '' ?>><?= __('bank_account_type_savings', null, 'Savings Account') ?></option>
                                            <option value="current" <?= ($bankAccount['account_type'] ?? '') === 'current' ? 'selected' : '' ?>><?= __('bank_account_type_current', null, 'Current Account') ?></option>
                                            <option value="od" <?= ($bankAccount['account_type'] ?? '') === 'od' ? 'selected' : '' ?>><?= __('bank_account_type_overdraft', null, 'Overdraft Account') ?></option>
                                        </select>
                                    </div>

                                    <div class="aps-form-field">
                                        <label class="aps-cp-label" for="upi_id">
                                            <i class="fas fa-mobile-alt text-muted"></i>
                                            <?= __('bank_label_upi', null, 'UPI ID (Optional)') ?>
                                        </label>
                                        <input type="text" name="upi_id" id="upi_id" class="form-control"
                                               value="<?= htmlspecialchars($bankAccount['upi_id'] ?? '') ?>"
                                               placeholder="<?= __('bank_ph_upi', null, 'e.g., yourname@okicici') ?>"
                                               pattern="[a-zA-Z0-9._-]+@[a-zA-Z]{2,}" maxlength="100">
                                        <div class="form-text">
                                            <i class="fas fa-bolt text-warning"></i>
                                            <?= __('bank_help_upi', null, 'For instant payments via UPI') ?>
                                        </div>
                                    </div>

                                    <?php if (!$bankAccount): ?>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="set_primary" id="set_primary" value="1" checked>
                                        <label class="form-check-label" for="set_primary">
                                            <?= __('bank_set_primary', null, 'Set as primary account for transactions') ?>
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="aps-cp-save-btn flex-grow-1">
                                        <i class="fas <?= $bankAccount ? 'fa-save' : 'fa-plus' ?>"></i>
                                        <?= $bankAccount ? __('bank_btn_update', null, 'Update Bank Details') : __('bank_btn_save', null, 'Save Bank Details') ?>
                                    </button>
                                    <?php if ($bankAccount): ?>
                                    <a href="<?= BASE_URL ?>/user/profile" class="btn btn-outline-secondary">
                                        <?= __('cancel', null, 'Cancel') ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <?php if (!empty($allAccounts)): ?>
                    <div class="aps-cp-card mb-4">
                        <div class="aps-cp-card-header">
                            <h5><i class="fas fa-credit-card"></i> <?= __('bank_saved_accounts', null, 'Saved Accounts') ?>
                                <span class="badge bg-primary ms-2"><?= count($allAccounts) ?></span>
                            </h5>
                        </div>
                        <div class="aps-cp-card-body">
                            <?php foreach ($allAccounts as $acc): ?>
                            <div class="aps-cp-account <?= !empty($acc['is_primary']) ? 'aps-cp-account--primary' : '' ?>">
                                <div class="aps-cp-account-info">
                                    <div class="aps-cp-account-bank">
                                        <i class="fas fa-university text-primary"></i>
                                        <?= htmlspecialchars($acc['bank_name'] ?? 'Bank') ?>
                                    </div>
                                    <div class="aps-cp-account-number">
                                        <i class="fas fa-credit-card me-1"></i>
                                        A/C: <?= htmlspecialchars(substr($acc['account_number'] ?? '****', 0, 4)) ?>****<?= htmlspecialchars(substr($acc['account_number'] ?? '****', -4)) ?>
                                    </div>
                                    <?php if (!empty($acc['upi_id'])): ?>
                                    <div class="aps-cp-account-upi">
                                        <i class="fas fa-check-circle"></i>
                                        UPI: <?= htmlspecialchars($acc['upi_id']) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="text-muted mt-1" class="style-11723">
                                        <?= ucfirst($acc['account_type'] ?? 'savings') ?> &middot;
                                        Added <?= date('M d, Y', strtotime($acc['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                                <div class="aps-cp-account-actions">
                                    <?php if (!empty($acc['is_primary'])): ?>
                                    <span class="aps-cp-badge-primary"><?= __('bank_badge_primary', null, 'Primary') ?></span>
                                    <?php else: ?>
                                    <form method="POST" action="<?= BASE_URL ?>/user/bank-details/save" class="d-inline" data-aps-ajax>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="set_primary">
                                        <input type="hidden" name="account_id" value="<?= (int)$acc['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="<?= __('bank_set_primary_title', null, 'Set as primary') ?>">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="aps-cp-info-card">
                        <h6><i class="fas fa-info-circle"></i> <?= __('bank_why_title', null, 'Why Add Bank Details?') ?></h6>
                        <ul>
                            <li><?= __('bank_why_commission', null, 'Receive commission payments') ?></li>
                            <li><?= __('bank_why_refund', null, 'Get refunds for cancelled bookings') ?></li>
                            <li><?= __('bank_why_rental', null, 'Receive rental income') ?></li>
                            <li><?= __('bank_why_upi', null, 'Faster payments via UPI') ?></li>
                        </ul>
                        <hr>
                        <p class="small mb-0">
                            <i class="fas fa-lock"></i> <?= __('bank_security_note', null, 'Your bank details are encrypted and stored securely. We never share your information with third parties.') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/components/smart-form-autocomplete.js"></script>
<script>
(function() {
    if (typeof CustomerPages === 'undefined') return;

    if (typeof SmartFormAutocomplete !== 'undefined') {
        var smartForm = new SmartFormAutocomplete();
        smartForm.initUpiValidation('#upi_id', {
            onValid: function(data) { /* UPI validated */ }
        });
    }

    var form = document.getElementById('bankForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = form.querySelector('[type="submit"]');
            if (typeof APS !== 'undefined' && !APS.validateForm(form)) {
                APS.toast('Please fix the errors before saving', 'error');
                return;
            }
            if (submitBtn) APS.showButtonLoader(submitBtn);

            var formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                redirect: 'follow',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) {
                if (submitBtn) APS.hideButtonLoader(submitBtn);
                if (r.redirected || r.ok) {
                    APS.toast('Bank details saved successfully', 'success');
                    setTimeout(function() { window.location.reload(); }, 600);
                    return;
                }
                throw new Error('Save failed (HTTP ' + r.status + ')');
            })
            .catch(function(err) {
                if (submitBtn) APS.hideButtonLoader(submitBtn);
                APS.toast(err.message || 'Could not save. Please try again.', 'error');
            });
        });
    }

    var accInput = document.getElementById('account_number');
    if (accInput) {
        accInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
})();
</script>
