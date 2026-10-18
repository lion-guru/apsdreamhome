<?php
// Budget Index View
$budgets = $budgets ?? [];
$filters = $filters ?? ['status' => '', 'fiscal_year' => ''];
$page = $page ?? 1;
$perPage = $perPage ?? 20;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$fiscalYears = $fiscalYears ?? [];
$statuses = $statuses ?? ['draft', 'submitted', 'approved', 'rejected'];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Budget Management</h1>
        <a href="<?= BASE_URL ?>/admin/budgets/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create Budget</a>
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
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/budgets" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Budget Name</th>
                        <th>Department</th>
                        <th>Fiscal Year</th>
                        <th>Allocated</th>
                        <th>Spent</th>
                        <th>Committed</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($budgets)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No budgets found</td></tr>
                    <?php else: ?>
                    <?php foreach ($budgets as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['budget_name']) ?></td>
                        <td><?= htmlspecialchars($b['department_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($b['fiscal_year']) ?></td>
                        <td>₹<?= number_format($b['allocated_amount'], 2) ?></td>
                        <td>₹<?= number_format($b['spent_amount'] ?? 0, 2) ?></td>
                        <td>₹<?= number_format($b['committed_amount'] ?? 0, 2) ?></td>
                        <td>₹<?= number_format($b['remaining_amount'] ?? 0, 2) ?></td>
                        <td><span class="badge bg-<?= match($b['status']) { 'approved'=> 'success', 'rejected'=> 'danger', 'closed'=> 'secondary', default=>'warning' } ?>"><?= ucfirst($b['status']) ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/budgets/<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            <a href="<?= BASE_URL ?>/admin/budgets/edit/<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                            <?php if ($b['status'] === 'draft'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/budgets/approve/<?= $b['id'] ?>" class="d-inline" onsubmit="return confirm('Approve budget?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>