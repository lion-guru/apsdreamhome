<?php
// Chart of Accounts Edit View
$account = $account ?? [];
$types = $types ?? ['asset', 'liability', 'equity', 'income', 'expense'];
$categories = $categories ?? [
    'current_asset' => 'Current Asset',
    'fixed_asset' => 'Fixed Asset',
    'current_liability' => 'Current Liability',
    'long_term_liability' => 'Long Term Liability',
    'owner_equity' => 'Owner Equity',
    'revenue' => 'Revenue',
    'operating_expense' => 'Operating Expense',
    'non_operating_expense' => 'Non-Operating Expense'
];
$accounts = $accounts ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Account: <?= htmlspecialchars($account['account_code'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/chart-of-accounts" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="coaForm" method="POST" action="<?= BASE_URL ?>/admin/chart-of-accounts/update/<?= $account['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Account Code <span class="text-danger">*</span></label>
                        <input type="text" name="account_code" class="form-control" required maxlength="20" value="<?= htmlspecialchars($account['account_code'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" required maxlength="255" value="<?= htmlspecialchars($account['account_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select name="account_type" class="form-select" required id="accountType">
                            <option value="">Select Type</option>
                            <?php foreach ($types as $t): ?>
                            <option value="<?= $t ?>" <?= ($account['account_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account Category <span class="text-danger">*</span></label>
                        <select name="account_category" class="form-select" required id="accountCategory">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $k => $v): ?>
                            <option value="<?= $k ?>" data-type="<?= match($k) { 'current_asset','fixed_asset' => 'asset', 'current_liability','long_term_liability' => 'liability', 'owner_equity' => 'equity', 'revenue' => 'income', 'operating_expense','non_operating_expense' => 'expense' } ?>" <?= ($account['account_category'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Parent Account</label>
                        <select name="parent_account_id" class="form-select" id="parentAccount">
                            <option value="">No Parent (Top Level)</option>
                            <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>" <?= ($account['parent_account_id'] ?? '') == $acc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" name="opening_balance" class="form-control" step="0.01" value="<?= htmlspecialchars($account['opening_balance'] ?? '0.00') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($account['description'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= !empty($account['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Account</button>
                        <a href="<?= BASE_URL ?>/admin/chart-of-accounts" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-filter categories based on selected type
document.getElementById('accountType').addEventListener('change', function() {
    const type = this.value;
    const catSelect = document.getElementById('accountCategory');
    const options = catSelect.querySelectorAll('option[data-type]');
    options.forEach(opt => {
        opt.style.display = (!type || opt.dataset.type === type) ? '' : 'none';
    });
    catSelect.value = '';
});

// Prevent self-referencing parent
const currentId = <?= (int)($account['id'] ?? 0) ?>;
document.getElementById('parentAccount').addEventListener('change', function() {
    if ((int)this.value === currentId) {
        alert('Account cannot be its own parent');
        this.value = '';
    }
});
</script>