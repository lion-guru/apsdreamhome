<?php
/** Admin Expense Reimbursement - List */

$page_title = $page_title ?? 'Expense Reimbursement';
$claims = $claims ?? [];
$categories = $categories ?? [];
$employees = $employees ?? [];
$filters = $filters ?? [];
$page = $page ?? 1;
$total = $total ?? 0;
$perPage = 25;
$totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.claim-row { transition: background 0.15s; }
.claim-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
.amount-col { font-family: 'SF Mono', 'Fira Code', monospace; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>Expense Reimbursement</h4>
            <p class="text-muted mb-0 small">Review and approve employee expense claims</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/expense-reimbursement/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Claim
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Claims</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['by_status']['submitted'] ?? 0 ?></div>
                    <div class="text-white-50 small">Pending Review</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['by_status']['approved'] ?? 0 ?></div>
                    <div class="text-white-50 small">Approved</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['by_status']['paid'] ?? 0 ?></div>
                    <div class="text-white-50 small">Paid</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Employee</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ($filters['employee_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="submitted" <?= ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['from_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['to_date'] ?? '') ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/expense-reimbursement" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($claims)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Expense Claims</h5>
                <p class="text-muted small mb-3">Create your first expense claim or wait for submissions</p>
                <a href="<?= BASE_URL ?>/admin/expense-reimbursement/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Claim</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Claim #</th>
                            <th>Employee</th>
                            <th style="width: 150px">Category</th>
                            <th style="width: 120px">Amount</th>
                            <th style="width: 100px">Date</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 120px">Submitted</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $claim): 
                            $statusClass = [
                                'draft' => 'secondary',
                                'submitted' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'paid' => 'primary',
                                'cancelled' => 'secondary',
                            ][$claim['status']] ?? 'secondary';
                            
                            $isOverdue = $claim['claim_date'] && $claim['claim_date'] < date('Y-m-d') && in_array($claim['status'], ['submitted', 'approved']);
                        ?>
                        <tr class="claim-row">
                            <td><code class="small"><?= htmlspecialchars($claim['claim_number']) ?></code></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($claim['employee_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($claim['employee_email'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                    <?= htmlspecialchars($claim['category_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="amount-col fw-semibold"><?= number_format($claim['total_amount'] ?? 0, 2) ?></td>
                            <td><?= $claim['claim_date'] ? date('d M Y', strtotime($claim['claim_date'])) : '—' ?></td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst($claim['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $claim['submitted_at'] ? date('d M Y H:i', strtotime($claim['submitted_at'])) : 
                                    ($claim['created_at'] ? date('d M Y H:i', strtotime($claim['created_at'])) : '—') ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/expense-reimbursement/<?= $claim['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if ($claim['status'] === 'submitted'): ?>
                                        <a href="<?= BASE_URL ?>/admin/expense-reimbursement/<?= $claim['id'] ?>/approve" class="btn btn-outline-success" onclick="return confirm('Approve this claim?')"><i class="fas fa-check"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/expense-reimbursement/<?= $claim['id'] ?>/reject" class="btn btn-outline-danger" onclick="return confirm('Reject this claim?')"><i class="fas fa-times"></i></a>
                                    <?php elseif ($claim['status'] === 'approved'): ?>
                                        <a href="<?= BASE_URL ?>/admin/expense-reimbursement/<?= $claim['id'] ?>/pay" class="btn btn-outline-info" onclick="return confirm('Mark as paid?')"><i class="fas fa-money-bill-wave"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>