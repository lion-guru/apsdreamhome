<?php
// Budget Variance Report View
$variance = $variance ?? [];
$deptSummary = $deptSummary ?? [];
$fiscalYears = $fiscalYears ?? [];
$filters = $filters ?? ['fiscal_year' => '', 'budget_id' => null];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Budget Variance Report</h1>
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
                    <label class="form-label">Specific Budget</label>
                    <input type="number" name="budget_id" class="form-control" value="<?= $filters['budget_id'] ? htmlspecialchars($filters['budget_id']) : '' ?>" placeholder="Budget ID (optional)">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/budgets/variance-report" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($variance)): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body"><h6>Allocated</h6><h3>₹<?= number_format((float)($variance[0]['allocated_amount'] ?? 0), 2) ?></h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark"><div class="card-body"><h6>Spent</h6><h3>₹<?= number_format((float)($variance[0]['spent_amount'] ?? 0), 2) ?></h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white"><div class="card-body"><h6>Committed</h6><h3>₹<?= number_format((float)($variance[0]['committed_amount'] ?? 0), 2) ?></h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-<?= ((float)($variance[0]['remaining_amount'] ?? 0) >= 0) ? 'primary' : 'danger' ?> text-white"><div class="card-body"><h6>Remaining</h6><h3>₹<?= number_format((float)($variance[0]['remaining_amount'] ?? 0), 2) ?></h3></div></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header"><strong>Department Budget Summary</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Department</th><th>Code</th><th>Budget Count</th><th>Allocated</th><th>Spent</th><th>Committed</th><th>Remaining</th></tr></thead>
                <tbody>
                    <?php if (empty($deptSummary)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No budget data found for this period</td></tr>
                    <?php else: ?>
                    <?php foreach ($deptSummary as $dept): ?>
                    <tr>
                        <td><?= htmlspecialchars($dept['department_name'] ?? '—') ?></td>
                        <td><code><?= htmlspecialchars($dept['department_code'] ?? '') ?></code></td>
                        <td><?= $dept['budget_count'] ?? 0 ?></td>
                        <td>₹<?= number_format((float)($dept['total_allocated'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($dept['total_spent'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($dept['total_committed'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($dept['total_remaining'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($variance) && count($variance) > 0): ?>
    <div class="card">
        <div class="card-header"><strong>Budget Variance — Per Budget Detail</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Budget Name</th><th>Department</th><th>Fiscal Year</th><th>Allocated</th><th>Spent</th><th>Committed</th><th>Available</th><th>Usage %</th></tr></thead>
                <tbody>
                    <?php foreach ($variance as $v): ?>
                    <?php $util = ((float)($v['allocated_amount'] ?? 0) > 0) ? (((float)($v['spent_amount'] ?? 0) + (float)($v['committed_amount'] ?? 0)) / (float)$v['allocated_amount']) * 100 : 0; ?>
                    <tr>
                        <td><?= htmlspecialchars($v['budget_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($v['department_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($v['fiscal_year'] ?? '') ?></td>
                        <td>₹<?= number_format((float)($v['allocated_amount'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($v['spent_amount'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($v['committed_amount'] ?? 0), 2) ?></td>
                        <td>₹<?= number_format((float)($v['available_amount'] ?? 0), 2) ?></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-<?= $util > 100 ? 'danger' : ($util > 80 ? 'warning' : 'success') ?>" style="width: <?= min(100, max(0, $util)) ?>%"></div>
                            </div>
                            <small><?= round($util, 1) ?>%</small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>