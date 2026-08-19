<?php
$foreclosure_stats = $foreclosure_stats ?? [];
$foreclosure_trend = $foreclosure_trend ?? [];
$foreclosure_data  = $foreclosure_data ?? [];
$filters           = $filters ?? [];
$page_title        = $page_title ?? 'EMI Foreclosure Report';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">EMI Foreclosure Report</h2>
            <p class="text-muted mb-0">Track and analyze all foreclosure activities across the portfolio</p>
        </div>
        <a href="<?php echo $base; ?>/admin/emi" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to EMI Plans
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2"><i class="fas fa-file-alt fa-2x text-primary"></i></div>
                    <h3 class="mb-0"><?php echo number_format($foreclosure_stats['total_attempts'] ?? 0); ?></h3>
                    <small class="text-muted">Total Attempts</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2"><i class="fas fa-check-circle fa-2x text-success"></i></div>
                    <h3 class="mb-0"><?php echo number_format($foreclosure_stats['successful_attempts'] ?? 0); ?></h3>
                    <small class="text-muted">Successful</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2"><i class="fas fa-times-circle fa-2x text-danger"></i></div>
                    <h3 class="mb-0"><?php echo number_format($foreclosure_stats['failed_attempts'] ?? 0); ?></h3>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2"><i class="fas fa-rupee-sign fa-2x text-warning"></i></div>
                    <h3 class="mb-0">₹<?php echo number_format($foreclosure_stats['total_foreclosure_amount'] ?? 0); ?></h3>
                    <small class="text-muted">Total Amount</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Success Rate</h5>
                    <?php
                    $total = $foreclosure_stats['total_attempts'] ?? 0;
                    $success = $foreclosure_stats['successful_attempts'] ?? 0;
                    $rate = $total > 0 ? round(($success / $total) * 100, 1) : 0;
                    ?>
                    <h2 class="mb-0 text-<?php echo $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger'); ?>">
                        <?php echo $rate; ?>%
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Average Foreclosure Amount</h5>
                    <h2 class="mb-0">₹<?php echo number_format($foreclosure_stats['average_foreclosure_amount'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo $base; ?>/admin/emi/foreclosure-report" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customer ID</label>
                    <input type="number" name="customer_id" class="form-control" placeholder="Filter by customer" value="<?php echo htmlspecialchars($filters['customer_id'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?php echo $base; ?>/admin/emi/foreclosure-report" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Trend -->
    <?php if (!empty($foreclosure_trend)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Foreclosure Trend (Last 12 Months)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Total Attempts</th>
                            <th class="text-center">Successful</th>
                            <th class="text-end">Amount Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($foreclosure_trend as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['month']); ?></td>
                            <td class="text-center"><?php echo number_format($row['total_attempts']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-success"><?php echo number_format($row['successful_attempts']); ?></span>
                            </td>
                            <td class="text-end">₹<?php echo number_format($row['total_foreclosure_amount']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Detailed Foreclosure Log -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Foreclosure Activity Log</h5>
            <span class="badge bg-secondary"><?php echo count($foreclosure_data); ?> records</span>
        </div>
        <div class="card-body">
            <?php if (empty($foreclosure_data)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No foreclosure records found<?php echo !empty($filters) ? ' for the selected filters' : ''; ?>.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Property</th>
                                <th>Status</th>
                                <th class="text-end">Amount</th>
                                <th>Date</th>
                                <th>Foreclosed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($foreclosure_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['property_title'] ?? '-'); ?></td>
                                <td>
                                    <?php if (($row['foreclosure_status'] ?? '') === 'success'): ?>
                                        <span class="badge bg-success">Success</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">₹<?php echo number_format($row['foreclosure_amount'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($row['attempted_at'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['admin_name'] ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
