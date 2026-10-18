<?php
// Budget Edit View
$budget = $budget ?? [];
$departments = $departments ?? [];
$fiscalYears = $fiscalYears ?? [['fiscal_year' => date('Y')]];
$categories = $categories ?? ['operating', 'capital', 'marketing', 'hr', 'it', 'maintenance', 'other'];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Budget: <?= htmlspecialchars($budget['budget_name'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/budgets" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/budgets/update/<?= $budget['id'] ?? '' ?>" id="budgetForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Budget Name <span class="text-danger">*</span></label>
                        <input type="text" name="budget_name" class="form-control" required value="<?= htmlspecialchars($budget['budget_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fiscal Year <span class="text-danger">*</span></label>
                        <select name="fiscal_year" class="form-select" required>
                            <?php foreach ($fiscalYears as $fy): ?>
                            <option value="<?= htmlspecialchars($fy['fiscal_year'] ?? '') ?>" <?= ($budget['fiscal_year'] ?? '') === ($fy['fiscal_year'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($fy['fiscal_year'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">None (Global)</option>
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($budget['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c ?>" <?= ($budget['category'] ?? '') === $c ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $c)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period Start <span class="text-danger">*</span></label>
                        <input type="date" name="period_start" class="form-control" required value="<?= $budget['period_start'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period End <span class="text-danger">*</span></label>
                        <input type="date" name="period_end" class="form-control" required value="<?= $budget['period_end'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Allocated Amount (₹)</label>
                        <input type="number" name="allocated_amount" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($budget['allocated_amount'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= ($budget['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="alert alert-info mb-0">Allocated: ₹<?= number_format((float)($budget['allocated_amount'] ?? 0), 2) ?></div></div>
                    <div class="col-md-3"><div class="alert alert-warning mb-0">Spent: ₹<?= number_format((float)($budget['spent_amount'] ?? 0), 2) ?></div></div>
                    <div class="col-md-3"><div class="alert alert-secondary mb-0">Committed: ₹<?= number_format((float)($budget['committed_amount'] ?? 0), 2) ?></div></div>
                    <div class="col-md-3"><div class="alert alert-<?= ((float)($budget['remaining_amount'] ?? 0) >= 0) ? 'success' : 'danger' ?> mb-0">Remaining: ₹<?= number_format((float)($budget['remaining_amount'] ?? 0), 2) ?></div></div>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/admin/budgets" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>