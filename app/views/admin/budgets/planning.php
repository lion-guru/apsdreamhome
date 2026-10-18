<?php
// Budget Planning View
$planning = $planning ?? [];
$departments = $departments ?? [];
$fiscalYears = $fiscalYears ?? [];
$filters = $filters ?? ['fiscal_year' => '', 'department_id' => null];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Budget Planning</h1>
        <a href="<?= BASE_URL ?>/admin/budgets" class="btn btn-secondary">Back to Budgets</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fiscal Year</label>
                    <select name="fiscal_year" class="form-select">
                        <option value="">All Years</option>
                        <?php foreach ($fiscalYears as $fy): ?>
                        <option value="<?= htmlspecialchars($fy['fiscal_year'] ?? '') ?>" <?= $filters['fiscal_year'] === ($fy['fiscal_year'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($fy['fiscal_year'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $filters['department_id'] == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/budgets/planning" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Planning Items</h5>
            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('planningForm').scrollIntoView()"><i class="fas fa-plus me-1"></i>Add Planning Item</button>
        </div>
        <div class="card-body p-0">
            <?php if (empty($planning)): ?>
            <div class="p-4 text-center text-muted">No planning items found</div>
            <?php else: ?>
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Line Item</th><th>Department</th><th>Fiscal Year</th><th>Category</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($planning as $p): ?>
                    <?php $total = (float)($p['q1_amount'] ?? 0) + (float)($p['q2_amount'] ?? 0) + (float)($p['q3_amount'] ?? 0) + (float)($p['q4_amount'] ?? 0); ?>
                    <tr>
                        <td><?= htmlspecialchars($p['line_item']) ?></td>
                        <td><?= htmlspecialchars($p['department_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['fiscal_year']) ?></td>
                        <td><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                        <td>₹<?= number_format((float)($p['q1_amount'] ?? 0), 0) ?></td>
                        <td>₹<?= number_format((float)($p['q2_amount'] ?? 0), 0) ?></td>
                        <td>₹<?= number_format((float)($p['q3_amount'] ?? 0), 0) ?></td>
                        <td>₹<?= number_format((float)($p['q4_amount'] ?? 0), 0) ?></td>
                        <td class="fw-bold">₹<?= number_format($total, 0) ?></td>
                        <td><span class="badge bg-<?= match($p['status']) { 'approved'=>'success', 'rejected'=>'danger', 'submitted'=>'info', default=>'warning' } ?>"><?= ucfirst($p['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" id="planningForm">
        <div class="card-header"><strong>Add Planning Item</strong></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/budgets/save-planning" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="col-md-4"><label class="form-label">Fiscal Year</label><input type="text" name="fiscal_year" class="form-control" value="<?= $filters['fiscal_year'] ?: date('Y') ?>" required></div>
                <div class="col-md-4"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">None</option><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Line Item</label><input type="text" name="line_item" class="form-control" placeholder="e.g., Road Construction Phase 1" required></div>
                <div class="col-md-3"><label class="form-label">Category</label><select name="category" class="form-select"><option value="">Select</option><?php foreach ($categories as $c): ?><option value="<?= $c ?>"><?= ucwords(str_replace('_', ' ', $c)) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Q1 Amount</label><input type="number" name="q1_amount" class="form-control" step="0.01" value="0"></div>
                <div class="col-md-3"><label class="form-label">Q2 Amount</label><input type="number" name="q2_amount" class="form-control" step="0.01" value="0"></div>
                <div class="col-md-2"><label class="form-label">Q3 Amount</label><input type="number" name="q3_amount" class="form-control" step="0.01" value="0"></div>
                <div class="col-md-2"><label class="form-label">Q4 Amount</label><input type="number" name="q4_amount" class="form-control" step="0.01" value="0"></div>
                <div class="col-md-12"><label class="form-label">Justification</label><textarea name="justification" class="form-control" rows="2"></textarea></div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Planning</button>
                    <a href="<?= BASE_URL ?>/admin/budgets/planning" class="btn btn-outline-secondary ms-2">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>
</div>