<?php
$page_title = $page_title ?? 'Installment Collection Report';
$base = defined('BASE_URL') ? BASE_URL : '';
$report = $report ?? [];
$start_date = $start_date ?? date('Y-m-01', strtotime('-1 month'));
$end_date = $end_date ?? date('Y-m-t');
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<?php include __DIR__ . '/../layouts/admin.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="m-0"><i class="fas fa-file-invoice me-2 text-info"></i>Installment Collection Report</h4>
        <a href="<?= $base ?>/admin/investment/analytics" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="fas fa-filter me-1"></i>Generate Report</button>
                </div>
                <div class="col-md-3">
                    <a href="<?= $base ?>/admin/investment/installment-report/export?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-outline-success w-100 btn-sm">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if (!empty($report)): 
        $totalCollected = array_sum(array_column($report, 'total_collected'));
        $totalPaid = array_sum(array_column($report, 'paid_amount'));
        $totalFailed = array_sum(array_column($report, 'failed_amount'));
        $totalCount = array_sum(array_column($report, 'count'));
        $paidCount = array_sum(array_column($report, 'paid_count'));
        $failedCount = array_sum(array_column($report, 'failed_count'));
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center bg-primary text-white">
                <div class="card-body">
                    <div class="fs-4 fw-bold"><?= number_format($totalCount) ?></div>
                    <small>Total Installments</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center bg-success text-white">
                    <div class="card-body">
                        <div class="fs-4 fw-bold">₹<?= number_format($totalCollected, 2) ?></div>
                        <small>Total Collected</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center bg-warning text-white">
                    <div class="card-body">
                        <div class="fs-4 fw-bold"><?= $totalCount > 0 ? round(($paidCount / $totalCount) * 100, 1) : 0 ?>%</div>
                        <small>Success Rate</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center bg-info text-white">
                    <div class="card-body">
                        <div class="fs-4 fw-bold">₹<?= number_format($totalFailed, 2) ?></div>
                        <small>Failed Amount</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Report Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($report)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Installments</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Paid Amount</th>
                                <th class="text-end">Failed Amount</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Failed</th>
                                <th class="text-end">Success Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report as $row): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($row['paid_date'])) ?></td>
                                    <td class="text-end"><?= number_format($row['count']) ?></td>
                                    <td class="text-end">₹<?= number_format($row['total_collected'], 2) ?></td>
                                    <td class="text-end text-success">₹<?= number_format($row['paid_amount'], 2) ?></td>
                                    <td class="text-end text-danger">₹<?= number_format($row['failed_amount'], 2) ?></td>
                                    <td class="text-end text-success"><?= number_format($row['paid_count']) ?></td>
                                    <td class="text-end text-danger"><?= number_format($row['failed_count']) ?></td>
                                    <td class="text-end">
                                        <?php $rate = $row['count'] > 0 ? round(($row['paid_count'] / $row['count']) * 100, 1) : 0; ?>
                                        <span class="badge bg-<?= $rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger') ?>">
                                            <?= $rate ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Total Row -->
                            <tr class="table-active fw-bold">
                                <td>Total</td>
                                <td class="text-end"><?= number_format($totalCount) ?></td>
                                <td class="text-end">₹<?= number_format($totalCollected, 2) ?></td>
                                <td class="text-end text-success">₹<?= number_format($totalPaid, 2) ?></td>
                                <td class="text-end text-danger">₹<?= number_format($totalFailed, 2) ?></td>
                                <td class="text-end text-success"><?= number_format($paidCount) ?></td>
                                <td class="text-end text-danger"><?= number_format($failedCount) ?></td>
                                <td class="text-end">
                                    <?php $overallRate = $totalCount > 0 ? round(($paidCount / $totalCount) * 100, 1) : 0; ?>
                                    <span class="badge bg-<?= $overallRate >= 90 ? 'success' : ($overallRate >= 70 ? 'warning' : 'danger') ?>">
                                        <?= $overallRate ?>%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No installment data for selected period</h5>
                    <p class="text-muted">Try adjusting the date range</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/admin.php'; ?>