<?php
// Budget Create View
$departments = $departments ?? [];
$fiscalYears = $fiscalYears ?? [['fiscal_year' => date('Y')]];
$currentYear = $currentYear ?? date('Y');
$categories = $categories ?? ['operating', 'capital', 'marketing', 'hr', 'it', 'maintenance', 'other'];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create Budget</h1>
        <a href="<?= BASE_URL ?>/admin/budgets" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/budgets/store" id="budgetForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Budget Name <span class="text-danger">*</span></label>
                        <input type="text" name="budget_name" class="form-control" required placeholder="e.g., FY 2025-26 Operating Budget">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fiscal Year <span class="text-danger">*</span></label>
                        <input type="text" name="fiscal_year" class="form-control" required value="<?= $currentYear ?>" placeholder="e.g., 2025-26" pattern="[0-9]{4}-[0-9]{2}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">None (Global)</option>
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c ?>"><?= ucwords(str_replace('_', ' ', $c)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period Start <span class="text-danger">*</span></label>
                        <input type="date" name="period_start" class="form-control" required value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Period End <span class="text-danger">*</span></label>
                        <input type="date" name="period_end" class="form-control" required value="<?= date('Y') === (int)$currentYear ? date('Y') . '-03-31' : date('Y', strtotime('+1 year')) . '-03-31' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Allocated Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="allocated_amount" class="form-control" step="0.01" min="0" required placeholder="0.00">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['draft', 'submitted', 'approved', 'rejected'] as $s): ?>
                            <option value="<?= $s ?>" <?= $s === 'draft' ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-2"></i>Create Budget</button>
                        <a href="<?= BASE_URL ?>/admin/budgets" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>