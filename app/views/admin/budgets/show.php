<?php
// Budget Show / Detail View
$budget = $budget ?? [];
$expenses = $expenses ?? [];
$variance = $variance ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Budget Details: <?= htmlspecialchars($budget['budget_name'] ?? '') ?></h1>
        <div>
            <?php if (($budget['status'] ?? '') === 'draft'): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/budgets/approve/<?= $budget['id'] ?? '' ?>" class="d-inline" onsubmit="return confirm('Approve budget?');">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-success me-2"><i class="fas fa-check me-2"></i>Approve</button>
            </form>
            <?php endif; ?>
            <?php if (($budget['status'] ?? '') === 'approved'): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/budgets/close/<?= $budget['id'] ?? '' ?>" class="d-inline" onsubmit="return confirm('Close budget?');">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-warning me-2"><i class="fas fa-lock me-2"></i>Close</button>
            </form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/budgets/edit/<?= $budget['id'] ?? '' ?>" class="btn btn-outline-secondary"><i class="fas fa-edit me-2"></i>Edit</a>
            <a href="<?= BASE_URL ?>/admin/budgets" class="btn btn-secondary ms-2">Back</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted">Budget Name</h6>
                    <p class="mb-0 fw-bold"><?= htmlspecialchars($budget['budget_name'] ?? '') ?></p>
                    <small class="text-muted">ID: <?= $budget['id'] ?? '' ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted">Department</h6>
                    <p class="mb-0 fw-bold"><?= htmlspecialchars($budget['department_name'] ?? '—') ?></p>
                    <small class="text-muted"><?= htmlspecialchars($budget['department_code'] ?? '') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted">Fiscal Year</h6>
                    <p class="mb-0 fw-bold"><?= htmlspecialchars($budget['fiscal_year'] ?? '') ?></p>
                    <small class="text-muted">Period: <?= date('M d', strtotime($budget['period_start'] ?? '')) ?> - <?= date('M d', strtotime($budget['period_end'] ?? '')) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted">Status</h6>
                    <p class="mb-0"><span class="badge bg-<?= match($budget['status'] ?? '') { 'approved' => 'success', 'rejected' => 'danger', 'closed' => 'secondary', 'submitted' => 'info', default => 'warning' } ?>"><?= ucfirst($budget['status'] ?? 'draft') ?></span></p>
                    <small class="text-muted">Created: <?= date('M d, Y', strtotime($budget['created_at'] ?? '')) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Allocated</h6>
                    <h3>₹<?= number_format((float)($budget['allocated_amount'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Spent</h6>
                    <h3>₹<?= number_format((float)($budget['spent_amount'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Committed</h6>
                    <h3>₹<?= number_format((float)($budget['committed_amount'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-<?= ((float)($budget['remaining_amount'] ?? 0) >= 0) ? 'success' : 'danger' ?> text-white">
                <div class="card-body">
                    <h6>Remaining</h6>
                    <h3>₹<?= number_format((float)($budget['remaining_amount'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Expenses</strong>
                    <a href="#" onclick="document.getElementById('expenseForm').scrollIntoView(); return false;" class="btn btn-sm btn-outline-primary">+ Add Expense</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($expenses)): ?>
                    <div class="p-4 text-center text-muted">No expenses recorded</div>
                    <?php else: ?>
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Vendor</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td><?= date('M d', strtotime($exp['expense_date'])) ?></td>
                                <td><?= htmlspecialchars($exp['vendor'] ?? '—') ?></td>
                                <td>₹<?= number_format((float)$exp['amount'], 2) ?></td>
                                <td><span class="badge bg-<?= match($exp['status']) { 'paid'=>'success', 'approved'=>'info', 'pending'=>'warning', 'rejected'=>'danger', default=>'secondary' } ?>"><?= ucfirst($exp['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Variance / Usage</strong></div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        <?php $util = ($budget['allocated_amount'] > 0) ? (((float)$budget['spent_amount'] + (float)$budget['committed_amount']) / (float)$budget['allocated_amount']) * 100 : 0; ?>
                        <div class="progress-bar bg-success" style="width: <?= min(100, max(0, $util)) ?>%">Used <?= round($util, 1) ?>%</div>
                    </div>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Allocated: ₹<?= number_format((float)($budget['allocated_amount'] ?? 0), 2) ?></span>
                        <span class="text-muted">Available: ₹<?= number_format((float)($budget['remaining_amount'] ?? 0), 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="card" id="expenseForm">
                <div class="card-header"><strong>Record Expense</strong></div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/budgets/add-expense/<?= $budget['id'] ?? '' ?>" class="row g-2">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="col-md-4">
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="vendor" class="form-control" placeholder="Vendor / Description" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="Amount" required>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>