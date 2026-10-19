<?php
/** Admin Payroll - List */

$page_title = $page_title ?? 'Payroll Management';
$runs = $runs ?? [];
$filters = $filters ?? [];
$stats = $stats ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.run-row { transition: background 0.15s; }
.run-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-money-bill-wave me-2 text-primary"></i>Payroll Management</h4>
            <p class="text-muted mb-0 small">Process and manage employee payroll</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/payroll/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Payroll Run
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['runs_by_status']['draft'] ?? 0 ?></div>
                    <div class="text-white-50 small">Draft Runs</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['runs_by_status']['calculated'] ?? 0 ?></div>
                    <div class="text-white-50 small">Calculated</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['runs_by_status']['completed'] ?? 0 ?></div>
                    <div class="text-white-50 small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['overdue_returns'] ?? 0 ?></div>
                    <div class="text-white-50 small">Overdue Returns</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="calculating" <?= ($filters['status'] ?? '') === 'calculating' ? 'selected' : '' ?>>Calculating</option>
                        <option value="review" <?= ($filters['status'] ?? '') === 'review' ? 'selected' : '' ?>>Review</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="processing" <?= ($filters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['from_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['to_date'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Runs Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($runs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Payroll Runs</h5>
                <p class="text-muted small mb-3">Create your first payroll run</p>
                <a href="<?= BASE_URL ?>/admin/payroll/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Run</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Run #</th>
                            <th>Period</th>
                            <th style="width: 100px">Pay Date</th>
                            <th style="width: 100px">Employees</th>
                            <th style="width: 150px">Gross / Net</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($runs as $run): 
                            $statusClass = [
                                'draft' => 'secondary',
                                'calculating' => 'info',
                                'review' => 'warning',
                                'approved' => 'primary',
                                'processing' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ][$run['status']] ?? 'secondary';
                        ?>
                        <tr class="run-row">
                            <td><code class="small"><?= htmlspecialchars($run['run_number']) ?></code></td>
                            <td>
                                <div><?= date('d M Y', strtotime($run['period_start'])) ?> - <?= date('d M Y', strtotime($run['period_end'])) ?></div>
                            </td>
                            <td><?= date('d M Y', strtotime($run['pay_date'])) ?></td>
                            <td><?= $run['total_employees'] ?? 0 ?></td>
                            <td>
                                <div class="small">Gross: <span class="fw-semibold">₹<?= number_format($run['total_gross'] ?? 0, 2) ?></span></div>
                                <div class="small text-muted">Net: <span class="fw-semibold">₹<?= number_format($run['total_net'] ?? 0, 2) ?></span></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst($run['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if ($run['status'] === 'draft'): ?>
                                        <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>/calculate" class="btn btn-outline-success" onclick="return confirm('Calculate payroll for this run?')"><i class="fas fa-calculator"></i></a>
                                    <?php elseif ($run['status'] === 'calculated'): ?>
                                        <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>/approve" class="btn btn-outline-primary" onclick="return confirm('Approve this payroll?')"><i class="fas fa-check"></i></a>
                                    <?php elseif ($run['status'] === 'approved'): ?>
                                        <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>/process" class="btn btn-outline-success" onclick="return confirm('Process and pay?')"><i class="fas fa-play"></i></a>
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