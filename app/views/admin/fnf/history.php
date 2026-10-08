<?php
/**
 * F&F Settlement History View
 * Variables: $settlements, $employees, $employee_id, $status, $page, $total_pages, $total
 */

$page_title = $page_title ?? 'F&F Settlement History';
$settlements = $settlements ?? [];
$employees = $employees ?? [];
$employee_id = $employee_id ?? 0;
$status = $status ?? '';
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$total = $total ?? 0;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Full &amp; Final Settlement History</h1>
        <a href="<?= BASE_URL ?>/admin/fnf/calculator" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Settlement
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= $employee_id == $emp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['employee_code'] ?? '') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="calculated" <?= $status === 'calculated' ? 'selected' : '' ?>>Calculated</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/fnf/history" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $total ?></h3>
                    <small>Total Settlements</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <?php $paid = array_filter($settlements, fn($s) => $s['status'] === 'paid'); ?>
                    <h3><?= count($paid) ?></h3>
                    <small>Paid</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <?php $pending = array_filter($settlements, fn($s) => in_array($s['status'], ['calculated','approved'])); ?>
                    <h3><?= count($pending) ?></h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <?php $totalNet = array_sum(array_column($settlements, 'net_payable')); ?>
                    <h3>₹<?= number_format($totalNet, 0) ?></h3>
                    <small>Total Payable</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlements Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($settlements)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Settlements Found</h5>
                <p class="text-muted">Create your first settlement using the calculator</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Settlement No</th>
                            <th>Employee</th>
                            <th>Exit Type</th>
                            <th>Last Working Day</th>
                            <th class="text-end">Earnings</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end text-success">Net Payable</th>
                            <th class="text-center">Status</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settlements as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['settlement_no']) ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($s['employee_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($s['employee_code'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?=
                                    $s['exit_type'] === 'termination' ? 'danger' :
                                    ($s['exit_type'] === 'retirement' ? 'info' :
                                    ($s['exit_type'] === 'abandonment' ? 'dark' : 'primary')) ?>">
                                    <?= ucfirst(htmlspecialchars($s['exit_type'])) ?>
                                </span>
                            </td>
                            <td><?= $s['last_working_day'] ? date('d M Y', strtotime($s['last_working_day'])) : '-' ?></td>
                            <td class="text-end">₹<?= number_format($s['earnings_total'] ?? 0, 2) ?></td>
                            <td class="text-end">₹<?= number_format($s['deductions_total'] ?? 0, 2) ?></td>
                            <td class="text-end text-success fw-bold">₹<?= number_format($s['net_payable'] ?? 0, 2) ?></td>
                            <td class="text-center">
                                <?php $st = $s['status']; ?>
                                <span class="badge bg-<?=
                                    $st === 'paid' ? 'success' :
                                    ($st === 'approved' ? 'info' :
                                    ($st === 'calculated' ? 'warning' : 'secondary')) ?>">
                                    <?= ucfirst($st) ?>
                                </span>
                            </td>
                            <td><?= !empty($s['created_at']) ? date('d M Y H:i', strtotime($s['created_at'])) : '-' ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/fnf/view/<?= $s['id'] ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($s['status'] === 'calculated'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/fnf/approve/<?= $s['id'] ?>" class="d-inline" onsubmit="return confirm('Approve this settlement?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-outline-success" title="Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($s['status'] === 'approved'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/fnf/mark-paid/<?= $s['id'] ?>" class="d-inline" onsubmit="return confirm('Mark as paid?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="payment_reference" value="PAID-<?= htmlspecialchars($s['settlement_no']) ?>">
                                        <button type="submit" class="btn btn-outline-success" title="Mark Paid"><i class="fas fa-money-bill-wave"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.badge { font-size: 0.75rem; }
</style>
