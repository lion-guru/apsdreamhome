<?php
/** Admin Payroll - Show Run */

$page_title = $page_title ?? 'Payroll Run: ' . ($run['run_number'] ?? 'Unknown');
$run = $run ?? [];
$items = $items ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.run-row { transition: background 0.15s; }
.run-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-money-bill-wave me-2 text-primary"></i><?= htmlspecialchars($run['run_number'] ?? 'Unknown') ?></h4>
            <p class="text-muted mb-0 small"><?= date('d M Y', strtotime($run['period_start'])) ?> - <?= date('d M Y', strtotime($run['period_end'])) ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($run['status'] === 'draft'): ?>
                <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>/calculate" class="btn btn-success" onclick="return confirm('Calculate payroll for this run?')"><i class="fas fa-calculator me-1"></i> Calculate</a>
            <?php elseif ($run['status'] === 'calculated'): ?>
                <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>/approve" class="btn btn-primary" onclick="return confirm('Approve this payroll?')"><i class="fas fa-check me-1"></i> Approve</a>
            <?php elseif ($run['status'] === 'approved'): ?>
                <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>/process" class="btn btn-success" onclick="return confirm('Process and pay?')"><i class="fas fa-play me-1"></i> Process</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $run['total_employees'] ?? 0 ?></div>
                    <div class="text-white-50 small">Employees</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4">₹<?= number_format($run['total_gross'] ?? 0, 2) ?></div>
                    <div class="text-white-50 small">Gross</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4">₹<?= number_format($run['total_deductions'] ?? 0, 2) ?></div>
                    <div class="text-white-50 small">Deductions</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4">₹<?= number_format($run['total_net'] ?? 0, 2) ?></div>
                    <div class="text-white-50 small">Net Pay</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-4">
        <span class="badge bg-<?= 
            $run['status'] === 'completed' ? 'success' : 
            ($run['status'] === 'approved' ? 'primary' : 
            ($run['status'] === 'calculated' ? 'info' : 
            ($run['status'] === 'processing' ? 'info' : 
            ($run['status'] === 'draft' ? 'secondary' : 'danger')))) ?> fs-6 px-3 py-2">
            <?= ucfirst($run['status']) ?>
        </span>
        <?php if ($run['processed_at']): ?>
            <span class="text-muted ms-2">Processed: <?= date('d M Y H:i', strtotime($run['processed_at'])) ?></span>
        <?php endif; ?>
        <?php if ($run['approved_at']): ?>
            <span class="text-muted ms-2">Approved: <?= date('d M Y H:i', strtotime($run['approved_at'])) ?></span>
        <?php endif; ?>
    </div>

    <!-- Items Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-list me-2"></i>Payroll Items (<?= count($items) ?>)</h5>
            <?php if ($run['status'] === 'approved'): ?>
                <a href="<?= BASE_URL ?>/admin/payroll/<?= $run['id'] ?>/export" class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i> Export CSV</a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Payroll Items</h5>
                <p class="text-muted small mb-3">Run calculation to generate items</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px">Employee</th>
                            <th style="width: 150px">Basic</th>
                            <th style="width: 120px">Allowances</th>
                            <th style="width: 120px">Gross</th>
                            <th style="width: 120px">Deductions</th>
                            <th style="width: 120px">Net Pay</th>
                            <th style="width: 100px">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): 
                            $statusClass = [
                                'draft' => 'secondary',
                                'calculated' => 'info',
                                'verified' => 'warning',
                                'approved' => 'primary',
                                'paid' => 'success',
                            ][$item['status']] ?? 'secondary';
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($item['employee_name'] ?? '—') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($item['employee_email'] ?? '') ?></div>
                            </td>
                            <td>₹<?= number_format($item['basic_salary'] ?? 0, 2) ?></td>
                            <td>
                                <div class="small">HRA: ₹<?= number_format($item['hra'] ?? 0, 2) ?></div>
                                <div class="small">DA: ₹<?= number_format($item['da'] ?? 0, 2) ?></div>
                                <div class="small">Other: ₹<?= number_format(($item['conveyance'] ?? 0) + ($item['medical_allowance'] ?? 0) + ($item['special_allowance'] ?? 0), 2) ?></div>
                            </td>
                            <td class="fw-semibold">₹<?= number_format($item['gross_earnings'] ?? 0, 2) ?></td>
                            <td>
                                <div class="small">PF: ₹<?= number_format($item['pf_employee'] ?? 0, 2) ?></div>
                                <div class="small">TDS: ₹<?= number_format($item['tds'] ?? 0, 2) ?></div>
                                <div class="small">Other: ₹<?= number_format(($item['professional_tax'] ?? 0) + ($item['advance_recovery'] ?? 0) + ($item['other_deductions'] ?? 0), 2) ?></div>
                            </td>
                            <td class="fw-semibold text-success">₹<?= number_format($item['net_pay'] ?? 0, 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst($item['status']) ?>
                                </span>
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