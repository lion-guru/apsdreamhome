<?php
/**
 * User Bank Details Page
 * Add/Edit bank account details with IFSC lookup
 */

// Auth handled by UserController::bankDetails() (checks $_SESSION['user_id'])

$page_title = $page_title ?? __('bank_page_title', null, 'Bank Account Details');

$db = \App\Core\Database\Database::getInstance();
$userId = $_SESSION['user_id'];

// Get existing bank details
$bankAccount = $db->fetch(
    "SELECT * FROM user_bank_accounts WHERE user_id = ? AND is_primary = 1 LIMIT 1",
    [$userId]
);

// Get all bank accounts
$allAccounts = $db->fetchAll(
    "SELECT * FROM user_bank_accounts WHERE user_id = ? ORDER BY is_primary DESC, created_at DESC",
    [$userId]
);

// Get banks for dropdown
$banks = $db->fetchAll("SELECT id, name, short_name FROM banks WHERE is_active = 1 ORDER BY name LIMIT 30");

$success = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : null;
$error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-university me-2"></i><?= __('bank_page_title', null, 'Bank Account Details') ?></h2>
                <a href="/user/profile" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i><?= __('bank_back_to_profile', null, 'Back to Profile') ?>
                </a>
            </div>

            <!-- Alerts -->
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Bank Account Form -->
                <div class="col-lg-7">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-plus me-2"></i><?= $bankAccount ? __('bank_form_update_title', null, 'Update Bank Account') : __('bank_form_add_title', null, 'Add Bank Account') ?></h5>
                        </div>
                        <div class="card-body">
                            <form action="/user/bank-details/save" method="POST" id="bankForm">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <!-- Account Holder Name -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?= __('bank_label_account_holder', null, 'Account Holder Name') ?> *</label>
                                    <input type="text" name="account_holder" class="form-control"
                                           value="<?= htmlspecialchars($bankAccount['account_holder'] ?? $_SESSION['user_name'] ?? '') ?>"
                                           placeholder="<?= __('bank_ph_account_holder', null, 'As per bank records') ?>" required>
                                </div>

                                <!-- Account Number -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?= __('bank_label_account_number', null, 'Account Number') ?> *</label>
                                    <input type="text" name="account_number" id="account_number" class="form-control"
                                           value="<?= htmlspecialchars($bankAccount['account_number'] ?? '') ?>"
                                           placeholder="<?= __('bank_ph_account_number', null, 'Enter account number') ?>" required>
                                    <div class="form-text"><?= __('bank_help_account_number', null, 'We recommend adding account number only when receiving payments') ?></div>
                                </div>

                                <!-- IFSC Code with Auto-fill -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?= __('bank_label_ifsc', null, 'IFSC Code') ?> *</label>
                                    <div class="input-group">
                                        <input type="text" name="ifsc_code" id="ifsc_code" class="form-control"
                                               value="<?= htmlspecialchars($bankAccount['ifsc_code'] ?? '') ?>"
                                               placeholder="<?= __('bank_ph_ifsc', null, 'e.g., SBIN0001234') ?>"
                                               style="text-transform: uppercase" maxlength="11" required>
                                        <button type="button" class="btn btn-outline-secondary" id="lookupIfsc">
                                            <i class="fas fa-search"></i> <?= __('bank_btn_lookup', null, 'Lookup') ?>
                                        </button>
                                    </div>
                                    <div class="form-text" id="ifscStatus"></div>
                                </div>

                                <!-- Bank Name (Auto-filled) -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold"><?= __('bank_label_bank_name', null, 'Bank Name') ?></label>
                                        <input type="text" name="bank_name" id="bank_name" class="form-control"
                                               value="<?= htmlspecialchars($bankAccount['bank_name'] ?? '') ?>"
                                               placeholder="<?= __('bank_ph_bank_name_autofill', null, 'Auto-filled from IFSC') ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold"><?= __('bank_label_branch_name', null, 'Branch Name') ?></label>
                                        <input type="text" name="branch_name" id="branch_name" class="form-control"
                                               value="<?= htmlspecialchars($bankAccount['branch_name'] ?? '') ?>"
                                               placeholder="<?= __('bank_ph_branch_name_autofill', null, 'Auto-filled from IFSC') ?>" readonly>
                                    </div>
                                </div>

                                <!-- Account Type -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?= __('bank_label_account_type', null, 'Account Type') ?></label>
                                    <select name="account_type" class="form-select">
                                        <option value="savings" <?= ($bankAccount['account_type'] ?? '') === 'savings' ? 'selected' : '' ?>><?= __('bank_account_type_savings', null, 'Savings Account') ?></option>
                                        <option value="current" <?= ($bankAccount['account_type'] ?? '') === 'current' ? 'selected' : '' ?>><?= __('bank_account_type_current', null, 'Current Account') ?></option>
                                        <option value="od" <?= ($bankAccount['account_type'] ?? '') === 'od' ? 'selected' : '' ?>><?= __('bank_account_type_overdraft', null, 'Overdraft Account') ?></option>
                                    </select>
                                </div>

                                <!-- UPI ID -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?= __('bank_label_upi', null, 'UPI ID (Optional)') ?></label>
                                    <input type="text" name="upi_id" id="upi_id" class="form-control"
                                           value="<?= htmlspecialchars($bankAccount['upi_id'] ?? '') ?>"
                                           placeholder="<?= __('bank_ph_upi', null, 'e.g., yourname@okicici') ?>">
                                    <div class="form-text"><?= __('bank_help_upi', null, 'For instant payments via UPI') ?></div>
                                </div>

                                <!-- Submit -->
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i><?= $bankAccount ? __('bank_btn_update', null, 'Update Bank Details') : __('bank_btn_save', null, 'Save Bank Details') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Saved Accounts & Info -->
                <div class="col-lg-5">
                    <!-- Saved Accounts -->
                    <?php if (!empty($allAccounts)): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= __('bank_saved_accounts', null, 'Saved Accounts') ?></h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($allAccounts as $acc): ?>
                            <div class="border rounded p-3 mb-2 <?= $acc['is_primary'] ? 'border-primary bg-light' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= htmlspecialchars($acc['bank_name'] ?? 'Bank') ?></strong>
                                        <br>
                                        <small class="text-muted">A/C: <?= htmlspecialchars(substr($acc['account_number'], 0, 4)) ?>****<?= htmlspecialchars(substr($acc['account_number'], -4)) ?></small>
                                        <?php if (!empty($acc['upi_id'])): ?>
                                        <br><small class="text-success"><i class="fas fa-check me-1"></i>UPI: <?= htmlspecialchars($acc['upi_id']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($acc['is_primary']): ?>
                                    <span class="badge bg-primary"><?= __('bank_badge_primary', null, 'Primary') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Info Box -->
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="text-info"><i class="fas fa-info-circle me-2"></i><?= __('bank_why_title', null, 'Why Add Bank Details?') ?></h6>
                            <ul class="mb-0 small">
                                <li><?= __('bank_why_commission', null, 'Receive commission payments') ?></li>
                                <li><?= __('bank_why_refund', null, 'Get refunds for cancelled bookings') ?></li>
                                <li><?= __('bank_why_rental', null, 'Receive rental income') ?></li>
                                <li><?= __('bank_why_upi', null, 'Faster payments via UPI') ?></li>
                            </ul>
                            <hr>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-lock me-1"></i><?= __('bank_security_note', null, 'Your bank details are encrypted and stored securely.') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Smart Form JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/components/smart-form-autocomplete.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const smartForm = new SmartFormAutocomplete();
    
    // Initialize UPI validation
    smartForm.initUpiValidation('#upi_id', {
        onValid: function(data) {
            console.log('UPI Provider:', data.provider);
        }
    });
    
    // IFSC Lookup
    const ifscInput = document.getElementById('ifsc_code');
    const lookupBtn = document.getElementById('lookupIfsc');
    const ifscStatus = document.getElementById('ifscStatus');
    const bankName = document.getElementById('bank_name');
    const branchName = document.getElementById('branch_name');
    
    lookupBtn.addEventListener('click', lookupIfsc);
    ifscInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookupIfsc();
        }
    });
    
    async function lookupIfsc() {
        const ifsc = ifscInput.value.trim().toUpperCase();
        if (ifsc.length < 8) {
            ifscStatus.innerHTML = '<span class="text-danger">Please enter valid IFSC code</span>';
            return;
        }
        
        lookupBtn.disabled = true;
        lookupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            const response = await fetch('/api/banks/ifsc/' + ifsc);
            const data = await response.json();
            
            if (data.found) {
                bankName.value = data.bank_name;
                branchName.value = data.branch;
                ifscStatus.innerHTML = '<span class="text-success"><i class="fas fa-check me-1"></i>Bank found: ' + data.bank_name + '</span>';
                ifscInput.classList.add('is-valid');
                ifscInput.classList.remove('is-invalid');
            } else {
                ifscStatus.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation me-1"></i>IFSC not found. Please enter details manually.</span>';
                ifscInput.classList.remove('is-valid');
                ifscInput.classList.add('is-valid'); // Still valid for manual entry
            }
        } catch (error) {
            ifscStatus.innerHTML = '<span class="text-danger">Error looking up IFSC</span>';
            console.error(error);
        }
        
        lookupBtn.disabled = false;
        lookupBtn.innerHTML = '<i class="fas fa-search"></i> Lookup';
    }
    
    // Auto-uppercase IFSC
    ifscInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
