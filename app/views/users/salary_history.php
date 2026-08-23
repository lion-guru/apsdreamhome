<?php
$salary_history = $salary_history ?? [];
$stats = $stats ?? ['total' => 0, 'paid' => 0, 'pending' => 0, 'total_earned' => 0];

function salStatusBadge($status) {
    $map = ['paid' => 'success', 'processed' => 'info', 'pending' => 'warning', 'held' => 'danger'];
    $cls = $map[strtolower($status)] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars($status ?? '') . '</span>';
}
function salMonth($date) {
    if (!$date) return '—';
    return date('M Y', strtotime($date));
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-sal-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.emp-sal-stat:hover { transform: translateY(-2px); }
.emp-sal-stat .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.emp-sal-row { transition: background 0.15s; }
.emp-sal-row:hover { background: #f8fafc; }
.emp-sal-slip-btn { border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px 12px; font-size: 0.78rem; transition: all 0.2s; }
.emp-sal-slip-btn:hover { background: #7c2d12; color: #fff; border-color: #7c2d12; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-wallet me-2 text-primary"></i>Salary History</h4>
            <p class="text-muted mb-0 small"><?= $stats['total'] ?> payslips on record</p>
        </div>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card emp-sal-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div><div class="fw-bold fs-4"><?= $stats['total'] ?></div><div class="text-muted small">Total Payslips</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-sal-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $stats['paid'] ?></div><div class="text-muted small">Paid</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-sal-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= $stats['pending'] ?></div><div class="text-muted small">Pending</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-sal-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon style-94511"><i class="fas fa-indian-rupee-sign"></i></div>
                    <div><div class="fw-bold fs-4 style-50238">₹<?= number_format($stats['total_earned']) ?></div><div class="text-muted small">Total Earned</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Table -->
    <?php if (empty($salary_history)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-wallet fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Salary Records</h5>
                <p class="text-muted small">Your payslips will appear here once processed</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Period</th>
                                <th class="text-end">Basic</th>
                                <th class="text-end">HRA</th>
                                <th class="text-end">Allowances</th>
                                <th class="text-end">Deductions</th>
                                <th class="text-end">Net Pay</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salary_history as $s): ?>
                                <tr class="emp-sal-row">
                                    <td>
                                        <div class="fw-semibold"><?= salMonth($s['pay_date'] ?? $s['month'] ?? '') ?></div>
                                        <?php if (!empty($s['pay_date'])): ?>
                                            <small class="text-muted"><?= date('d M Y', strtotime($s['pay_date'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">₹<?= number_format((float)($s['basic'] ?? 0)) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($s['hra'] ?? 0)) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($s['allowances'] ?? 0)) ?></td>
                                    <td class="text-end text-danger">-₹<?= number_format((float)($s['deductions'] ?? 0)) ?></td>
                                    <td class="text-end fw-bold">₹<?= number_format((float)($s['net_pay'] ?? 0)) ?></td>
                                    <td><?= salStatusBadge($s['status'] ?? 'Pending') ?></td>
                                    <td class="text-end">
                                        <button class="emp-sal-slip-btn" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Payslip</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
