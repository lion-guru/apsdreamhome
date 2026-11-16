<?php
/**
 * Admin Payroll - Statutory Returns
 */
$page_title = $page_title ?? 'Statutory Returns - APS Dream Home';
$returns = $returns ?? [];
$filters = $filters ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/admin/dashboard">Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/admin/payroll">Payroll</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Statutory Returns</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i> Statutory Returns Filing</h2>
        </div>
        <div>
            <a href="<?= $base ?>/admin/payroll" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Payroll
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= $base ?>/admin/statutory-returns" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Return Type</label>
                    <select name="return_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="pf" <?= ($filters['return_type'] ?? '') === 'pf' ? 'selected' : '' ?>>Provident Fund (PF)</option>
                        <option value="esi" <?= ($filters['return_type'] ?? '') === 'esi' ? 'selected' : '' ?>>Employee State Insurance (ESI)</option>
                        <option value="tds" <?= ($filters['return_type'] ?? '') === 'tds' ? 'selected' : '' ?>>TDS (Salary / 192)</option>
                        <option value="pt" <?= ($filters['return_type'] ?? '') === 'pt' ? 'selected' : '' ?>>Professional Tax (PT)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending Filing</option>
                        <option value="filed" <?= ($filters['status'] ?? '') === 'filed' ? 'selected' : '' ?>>Filed</option>
                        <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Year</label>
                    <input type="number" name="period_year" class="form-control form-control-sm" placeholder="YYYY" value="<?= htmlspecialchars((string)($filters['period_year'] ?? '')) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Month</label>
                    <select name="period_month" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (int)($filters['period_month'] ?? 0) === $m ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Return Type</th>
                            <th>Period</th>
                            <th>Due Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Filed On</th>
                            <th>Acknowledgement No.</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($returns)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success d-block"></i>
                                    No statutory returns found matching the selected criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($returns as $ret): ?>
                                <tr>
                                    <td class="ps-3 fw-bold">
                                        <span class="badge bg-secondary me-1"><?= strtoupper(htmlspecialchars($ret['return_type'] ?? 'RETURN')) ?></span>
                                        <?= htmlspecialchars($ret['description'] ?? $ret['return_type'] ?? '') ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)($ret['period_month'] ?? '')) ?>/<?= htmlspecialchars((string)($ret['period_year'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($ret['due_date'] ?? '—') ?></td>
                                    <td class="fw-bold">₹<?= number_format((float)($ret['total_amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php
                                        $st = strtolower($ret['status'] ?? 'pending');
                                        $badgeClass = $st === 'filed' ? 'bg-success' : ($st === 'overdue' ? 'bg-danger' : 'bg-warning text-dark');
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($st) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($ret['filed_at'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($ret['acknowledgement_no'] ?? '—') ?></td>
                                    <td class="text-end pe-3">
                                        <?php if ($st !== 'filed'): ?>
                                            <form method="POST" action="<?= $base ?>/admin/statutory-returns/<?= (int)$ret['id'] ?>/file" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Confirm filing this statutory return?')">
                                                    <i class="fas fa-check me-1"></i> Mark Filed
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-success small"><i class="fas fa-check-double me-1"></i> Completed</span>
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
</div>
