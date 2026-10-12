<?php
/**
 * Customer Self-Service: Society Maintenance Dues View
 */
$dues = $dues ?? [];
$total_pending = $total_pending ?? 0.00;
$total_paid = $total_paid ?? 0.00;
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($page_title ?? 'My Society Maintenance Dues') ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold mb-1"><i class="fas fa-tools text-primary me-2"></i>My Society Maintenance Dues</h2>
                <p class="text-muted mb-0">Monthly infrastructure, security, water supply &amp; park maintenance</p>
            </div>
            <div>
                <a href="<?= $base ?>/customer/dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-danger border-4">
                    <span class="text-muted small">Total Pending Maintenance</span>
                    <h3 class="mb-0 fw-bold text-danger">₹<?= number_format($total_pending, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-success border-4">
                    <span class="text-muted small">Total Paid Maintenance</span>
                    <h3 class="mb-0 fw-bold text-success">₹<?= number_format($total_paid, 2) ?></h3>
                </div>
            </div>
        </div>

        <!-- Dues List -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Maintenance Ledger</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Month</th>
                                <th>Colony &amp; Plot</th>
                                <th>Area (Sqft)</th>
                                <th class="text-end">Amount Due</th>
                                <th class="text-end">Paid</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($dues)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success d-block opacity-50"></i>
                                    No maintenance dues found for your account. All clear!
                                </td>
                            </tr>
                        <?php else: foreach ($dues as $d): ?>
                            <tr>
                                <td class="fw-semibold"><?= date('F Y', strtotime($d['bill_month'] . '-01')) ?></td>
                                <td>
                                    <div class="fw-medium"><?= $e($d['colony_name'] ?: 'Colony #' . $d['colony_id']) ?></div>
                                    <span class="badge bg-light text-dark border">Plot <?= $e($d['plot_number'] ?: ('#' . $d['plot_id'])) ?></span>
                                </td>
                                <td><?= number_format((float)$d['area_sqft']) ?> sqft</td>
                                <td class="text-end fw-bold">₹<?= number_format((float)$d['amount_due'], 2) ?></td>
                                <td class="text-end text-success fw-semibold">₹<?= number_format((float)$d['paid_amount'], 2) ?></td>
                                <td>
                                    <?php if ($d['status'] === 'paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($d['status'] === 'partially_paid'): ?>
                                        <span class="badge bg-warning text-dark">Partial</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (in_array($d['status'], ['pending', 'partially_paid'])): ?>
                                        <a href="<?= $e($d['upi_pay_link']) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-qrcode me-1"></i> Pay ₹<?= number_format($d['pending_balance'], 2) ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= $base ?>/admin/maintenance-dues/receipt/<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-receipt me-1"></i> Receipt
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-muted small text-center mt-4">
            <i class="fas fa-shield-alt me-1 text-primary"></i> Society Maintenance payments ensure smooth operations of 24x7 security, water supply, and colony greenery.
        </div>
    </div>
</body>
</html>
