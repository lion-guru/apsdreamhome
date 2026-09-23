<?php
/**
 * @var array $booking
 * @var float|int $total_paid
 * @var float|int $total_commission
 * @var array $payments
 * @var array $emis
 * @var array $commissions
 * @var array $documents
 * @var array $activity_logs
 */
$page_title = $page_title ?? __('admin_booking_details');
$active_page = 'bookings';
$extraHead = '<style>
    .status-badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    .payment-row:hover {
        background-color: #f8f9fa;
    }
    .commission-row:hover {
        background-color: #f0f8ff;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 500;
        padding: 12px 20px;
        border-radius: 8px 8px 0 0;
        margin-right: 4px;
    }
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #198754, #20c997);
        color: #fff;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        background: #f1f5f9;
        color: #1e293b;
    }
    .tab-content { padding: 24px 0; }
    .badge-status {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    .payment-row:hover { background-color: #f8f9fa; }
    .commission-row:hover { background-color: #f0f8ff; }
    .action-btns .btn { margin-right: 8px; margin-bottom: 8px; }
</style>';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2"><i class="fas fa-file-contract text-primary me-2"></i><?= __('admin_booking_details') ?>
            <?php if (!empty($booking['booking_number'])): ?>
            <small class="fs-6 text-muted fw-normal">&nbsp;#<?= htmlspecialchars($booking['booking_number']) ?></small>
            <?php endif; ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/bookings">Bookings</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($booking['booking_number'] ?? 'Detail') ?></li>
            </ol>
        </nav>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/bookings" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= __('admin_back_to_bookings') ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/bookings/<?= $booking['id'] ?>/edit" class="btn btn-primary ms-2">
            <i class="fas fa-edit"></i> <?= __('admin_edit_booking') ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/bookings/<?= $booking['id'] ?>/legal-kit" class="btn btn-success ms-2">
            <i class="fas fa-file-archive me-1"></i> Legal Kit
        </a>
        <a href="<?= BASE_URL ?>/admin/sales/bookings/<?= $booking['id'] ?>/legal-kit" class="btn btn-outline-success ms-2" title="Legal Kit (Sales Lifecycle)">
            <i class="fas fa-file-archive me-1"></i> Sales Legal Kit
        </a>
        <button type="button" class="btn btn-danger ms-2" onclick="confirmDelete()">
            <i class="fas fa-trash"></i> <?= __('admin_delete') ?>
        </button>
    </div>
</div>

<!-- 360° Quick Actions Bar -->
<div class="card aps-cp-card mb-4" style="border-left: 4px solid #198754;">
    <div class="card-body aps-cp-card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <h6 class="fw-bold mb-0 me-3"><i class="fas fa-link me-1"></i> 360° Quick Links:</h6>
            <?php 
            $bookingId = $booking['id'] ?? 0;
            $plotId = $booking['plot_id'] ?? 0;
            $customerId = $booking['customer_id'] ?? 0;
            $associateId = $booking['associate_id'] ?? 0;
            ?>
            <a href="#tab-booking" class="btn btn-sm btn-outline-success active" title="Booking Details" data-bs-toggle="tab" data-bs-target="#tab-booking">
                <i class="fas fa-file-contract me-1"></i> Booking Details
            </a>
            <?php if ($plotId): ?>
                <a href="#tab-plot" class="btn btn-sm btn-outline-primary" title="View Assigned Plot" data-bs-toggle="tab" data-bs-target="#tab-plot">
                    <i class="fas fa-th me-1"></i> Plot
                </a>
            <?php endif; ?>
            <?php if ($customerId): ?>
                <a href="#tab-customer" class="btn btn-sm btn-outline-info" title="View Customer Profile" data-bs-toggle="tab" data-bs-target="#tab-customer">
                    <i class="fas fa-user me-1"></i> Customer
                </a>
            <?php endif; ?>
            <?php if ($associateId): ?>
                <a href="#tab-associate" class="btn btn-sm btn-outline-warning" title="View Associate Details" data-bs-toggle="tab" data-bs-target="#tab-associate">
                    <i class="fas fa-user-tie me-1"></i> Associate
                </a>
            <?php endif; ?>
            <a href="#tab-payments" class="btn btn-sm btn-outline-secondary" title="Payments & EMI" data-bs-toggle="tab" data-bs-target="#tab-payments">
                <i class="fas fa-money-bill-wave me-1"></i> Payments
            </a>
            <a href="#tab-commissions" class="btn btn-sm btn-outline-success" title="Commission Details" data-bs-toggle="tab" data-bs-target="#tab-commissions">
                <i class="fas fa-percent me-1"></i> Commissions
            </a>
            <a href="#tab-documents" class="btn btn-sm btn-outline-dark" title="Documents & Registry" data-bs-toggle="tab" data-bs-target="#tab-documents">
                <i class="fas fa-file-alt me-1"></i> Documents
            </a>
            <a href="#tab-history" class="btn btn-sm btn-outline-dark" title="Status History" data-bs-toggle="tab" data-bs-target="#tab-history">
                <i class="fas fa-history me-1"></i> History
            </a>
        </div>
    </div>
</div>

<!-- 360° Tabbed Interface -->
<ul class="nav nav-tabs mb-4" id="bookingTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-booking-btn" data-bs-toggle="tab" data-bs-target="#tab-booking" type="button" role="tab">
            <i class="fas fa-file-contract me-1"></i> Booking Details
        </button>
    </li>
    <?php if ($plotId): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-plot-btn" data-bs-toggle="tab" data-bs-target="#tab-plot" type="button" role="tab">
            <i class="fas fa-th me-1"></i> Plot
        </button>
    </li>
    <?php endif; ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-customer-btn" data-bs-toggle="tab" data-bs-target="#tab-customer" type="button" role="tab">
            <i class="fas fa-user me-1"></i> Customer
        </button>
    </li>
    <?php if ($associateId): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-associate-btn" data-bs-toggle="tab" data-bs-target="#tab-associate" type="button" role="tab">
            <i class="fas fa-user-tie me-1"></i> Associate
        </button>
    </li>
    <?php endif; ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-payments-btn" data-bs-toggle="tab" data-bs-target="#tab-payments" type="button" role="tab">
            <i class="fas fa-money-bill-wave me-1"></i> Payments & EMI
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-commissions-btn" data-bs-toggle="tab" data-bs-target="#tab-commissions" type="button" role="tab">
            <i class="fas fa-percent me-1"></i> Commissions
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-documents-btn" data-bs-toggle="tab" data-bs-target="#tab-documents" type="button" role="tab">
            <i class="fas fa-file-alt me-1"></i> Documents
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-history-btn" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab">
            <i class="fas fa-history me-1"></i> History
        </button>
    </li>
</ul>

<div class="tab-content" id="bookingTabsContent">

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<!-- Booking Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> <?= __('admin_booking_information') ?>
                </h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><?= __('admin_booking_number') ?>:</strong><br>
                            <span class="badge bg-primary"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></span>
                        </p>

                        <p><strong><?= __('admin_property') ?>:</strong><br>
                            <?= htmlspecialchars($booking['property_title'] ?? '') ?><br>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['property_location'] ?? '') ?>
                            </small>
                        </p>

                        <p><strong><?= __('admin_total') ?>:</strong><br>
                            <span class="text-success fw-bold">₹<?= number_format(floatval($booking['total_amount'] ?? 0), 2) ?></span>
                        </p>

                        <p><strong><?= __('admin_booking_date') ?>:</strong><br>
                            <?= date('d F Y', strtotime($booking['booking_date'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><?= __('admin_status_label') ?>:</strong><br>
                            <?php
                            $statusColors = [
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'completed' => 'info',
                                'cancelled' => 'danger'
                            ];
                            $color = $statusColors[$booking['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?> status-badge">
                                <?= ucfirst(htmlspecialchars($booking['status'] ?? '')) ?>
                            </span>
                        </p>

                        <p><strong><?= __('admin_created_label') ?>:</strong><br>
                            <?= date('d F Y h:i A', strtotime($booking['created_at'])) ?></p>

                        <p><strong><?= __('admin_last_updated') ?>:</strong><br>
                            <?= $booking['updated_at'] ? date('d F Y h:i A', strtotime($booking['updated_at'])) : 'N/A' ?></p>

                        <?php if (!empty($booking['notes'])): ?>
                            <p><strong><?= __('admin_notes_label') ?>:</strong><br>
                                <?= nl2br(htmlspecialchars($booking['notes'] ?? '')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie"></i> <?= __('admin_financial_summary') ?>
                </h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('admin_total') ?></label>
                    <h4 class="text-primary">₹<?= number_format(floatval($booking['total_amount'] ?? 0), 2) ?></h4>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('admin_total_paid') ?></label>
                    <h4 class="text-success">₹<?= number_format($total_paid, 2) ?></h4>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('admin_balance_due') ?></label>
                    <h4 class="text-danger">₹<?= number_format(floatval($booking['total_amount'] ?? 0) - $total_paid, 2) ?></h4>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><?= __('admin_total_commission') ?></label>
                    <h4 class="text-info">₹<?= number_format($total_commission, 2) ?></h4>
                </div>

                <div class="progress mb-3">
                    <?php $payment_percentage = ($booking['booking_amount'] > 0) ? ($total_paid / $booking['booking_amount']) * 100 : 0; ?>
                    <div class="progress-bar" role="progressbar"
                        aria-valuenow="<?= $payment_percentage ?>"
                        aria-valuemin="0" aria-valuemax="100">
                        <?= round($payment_percentage, 1) ?>% Paid
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer & Associate Information -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user"></i> <?= __('admin_customer_information') ?>
                </h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <p><strong><?= __('admin_name_label') ?>:</strong><br><?= htmlspecialchars($booking['customer_name'] ?? '') ?></p>
                <p><strong><?= __('admin_email_label') ?>:</strong><br>
                    <a href="mailto:<?= htmlspecialchars($booking['customer_email'] ?? '') ?>">
                        <?= htmlspecialchars($booking['customer_email'] ?? '') ?>
                    </a>
                </p>
                <p><strong><?= __('admin_phone_label') ?>:</strong><br>
                    <a href="tel:<?= htmlspecialchars($booking['customer_phone'] ?? '') ?>">
                        <?= htmlspecialchars($booking['customer_phone'] ?? '') ?>
                    </a>
                </p>
                <?php if (!empty($booking['customer_address'])): ?>
                    <p><strong><?= __('admin_address_label') ?>:</strong><br>
                        <?= nl2br(htmlspecialchars($booking['customer_address'] ?? '')) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-tie"></i> <?= __('admin_associate_information') ?>
                </h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($booking['associate_name'])): ?>
                    <p><strong><?= __('admin_name_label') ?>:</strong><br><?= htmlspecialchars($booking['associate_name'] ?? '') ?></p>
                    <p><strong><?= __('admin_email_label') ?>:</strong><br>
                        <a href="mailto:<?= htmlspecialchars($booking['associate_email'] ?? '') ?>">
                            <?= htmlspecialchars($booking['associate_email'] ?? '') ?>
                        </a>
                    </p>
                    <?php if (!empty($booking['associate_phone'])): ?>
                        <p><strong><?= __('admin_phone_label') ?>:</strong><br>
                            <a href="tel:<?= htmlspecialchars($booking['associate_phone'] ?? '') ?>">
                                <?= htmlspecialchars($booking['associate_phone'] ?? '') ?>
                            </a>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted"><?= __('admin_no_associate_assigned') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment History -->
<div class="card mb-4">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-money-bill-wave"></i> <?= __('admin_payment_history') ?>
        </h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <?php if (empty($payments)): ?>
            <p class="text-muted"><?= __('admin_no_payments_recorded') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?= __('admin_date_label') ?></th>
                            <th><?= __('admin_amount_label') ?></th>
                            <th><?= __('admin_method_label') ?></th>
                            <th><?= __('admin_transaction_id') ?></th>
                            <th><?= __('admin_status_label') ?></th>
                            <th><?= __('admin_receipt_label') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr class="payment-row">
                                <td><?= date('d M Y h:i A', strtotime($payment['created_at'])) ?></td>
                                <td class="fw-bold text-success">₹<?= number_format(floatval($payment['amount'] ?? 0), 2) ?></td>
                                <td><?= ucfirst(htmlspecialchars($payment['payment_method'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($payment['transaction_id'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $payment['status'] == 'completed' ? 'success' : 'warning' ?>">
                                        <?= ucfirst(htmlspecialchars($payment['status'] ?? '')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($payment['receipt_number'])): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewReceipt('<?= $payment['receipt_number'] ?>')">
                                            <i class="fas fa-receipt"></i> <?= __('admin_view_button') ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-3">
            <button class="btn btn-primary" onclick="addPayment()">
                <i class="fas fa-plus"></i> <?= __('admin_add_payment') ?>
            </button>
        </div>
    </div>
</div>

<!-- Commission History -->
<div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-percentage"></i> <?= __('admin_commission_history') ?>
        </h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <?php if (empty($commissions)): ?>
            <p class="text-muted"><?= __('admin_no_commissions_recorded') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?= __('admin_date_label') ?></th>
                            <th><?= __('admin_associate') ?></th>
                            <th><?= __('admin_type_label') ?></th>
                            <th><?= __('admin_amount_label') ?></th>
                            <th><?= __('admin_status_label') ?></th>
                            <th><?= __('admin_description_label') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commissions as $commission): ?>
                            <tr class="commission-row">
                                <td><?= date('d M Y h:i A', strtotime($commission['created_at'])) ?></td>
                                <td><?= htmlspecialchars($commission['user_id'] ?? '') ?></td>
                                <td><?= ucfirst(htmlspecialchars($commission['commission_type'] ?? '')) ?></td>
                                <td class="fw-bold text-info">₹<?= number_format(floatval($commission['amount'] ?? 0), 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $commission['status'] == 'paid' ? 'success' : 'warning' ?>">
                                        <?= ucfirst(htmlspecialchars($commission['status'] ?? '')) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($commission['description'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Booking 360° Tabs -->
<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-tabs nav-tabs-custom" id="booking360Tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="emi-tab" data-bs-toggle="tab" data-bs-target="#emi-schedule" type="button" role="tab" aria-controls="emi-schedule" aria-selected="true">
                    <i class="fas fa-calendar-check me-2"></i> EMI Schedule & Overdue
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payment-receipts" type="button" role="tab" aria-controls="payment-receipts" aria-selected="false">
                    <i class="fas fa-receipt me-2"></i> Payment Receipts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="commissions-tab" data-bs-toggle="tab" data-bs-target="#commission-payouts" type="button" role="tab" aria-controls="commission-payouts" aria-selected="false">
                    <i class="fas fa-percent me-2"></i> Commission Payouts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">
                    <i class="fas fa-file-alt me-2"></i> Documents
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content" id="booking360TabContent">
    <!-- Tab 1: EMI Schedule & Overdue -->
    <div class="tab-pane fade show active" id="emi-schedule" role="tabpanel" aria-labelledby="emi-tab">
        <div class="row g-4">
            <div class="col-12">
                <h5><i class="fas fa-calendar-check me-2"></i> EMI Schedule</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Due Date</th>
                            <th>EMI Amount</th>
                            <th>Paid Amount</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($emi_schedules)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No EMI schedules found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($emi_schedules as $emi): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($emi['due_date'] ?? '')) ?></td>
                            <td>₹<?= number_format(floatval($emi['emi_amount'] ?? 0), 2) ?></td>
                            <td>₹<?= number_format(floatval($emi['paid_amount'] ?? 0), 2) ?></td>
                            <td>₹<?= number_format(floatval($emi['balance'] ?? 0), 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $emi['status'] === 'paid' ? 'success' : ($emi['status'] === 'overdue' ? 'danger' : ($emi['status'] === 'pending' ? 'warning' : 'secondary')) ?> fs-6">
                                    <?= ucfirst($emi['status'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/bookings/<?= $emi['booking_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($emi_schedules)): ?>
            <div class="mt-3">
                <div class="row">
                    <div class="col-6">
                        <p><strong>Total EMIs:</strong> <?= count($emi_schedules) ?></p>
                        <p><strong>Total Overdue:</strong> <?= count(array_filter($emi_schedules, fn($e) => $e['status'] === 'overdue')) ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p><strong>Total Paid:</strong> ₹<?= number_format(array_sum(array_map(fn($e) => $e['paid_amount'] ?? 0, $emi_schedules)), 2) ?></p>
                        <p><strong>Total Balance:</strong> ₹<?= number_format(array_sum(array_map(fn($e) => $e['balance'] ?? 0, $emi_schedules)), 2) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab 2: Payment Receipts -->
    <div class="tab-pane fade" id="payment-receipts" role="tabpanel" aria-labelledby="payments-tab">
        <div class="row g-4">
            <div class="col-12">
                <h5><i class="fas fa-receipt me-2"></i> Payment Receipts</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No payment receipts recorded</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['receipt_number'] ?? 'N/A') ?></td>
                            <td><?= date('d M Y h:i A', strtotime($payment['created_at'])) ?></td>
                            <td><?= ucfirst(htmlspecialchars($payment['payment_method'] ?? '')) ?></td>
                            <td>₹<?= number_format(floatval($payment['amount'] ?? 0), 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $payment['status'] === 'completed' ? 'success' : 'warning' ?> fs-6">
                                    <?= ucfirst($payment['status'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary" onclick="printReceipt('<?= $payment['receipt_number'] ?>')">
                                    <i class="fas fa-print"></i> Print
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Commission Payouts -->
    <div class="tab-pane fade" id="commission-payouts" role="tabpanel" aria-labelledby="commissions-tab">
        <div class="row g-4">
            <div class="col-12">
                <h5><i class="fas fa-percent me-2"></i> Commission Payouts</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Associate</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($commissions)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No commissions recorded</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($commissions as $commission): ?>
                        <tr>
                            <td><?= date('d M Y h:i A', strtotime($commission['created_at'])) ?></td>
                            <td><?= htmlspecialchars($commission['user_id'] ?? '') ?></td>
                            <td><?= ucfirst(htmlspecialchars($commission['commission_type'] ?? '')) ?></td>
                            <td class="fw-bold text-info">₹<?= number_format(floatval($commission['amount'] ?? 0), 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $commission['status'] == 'paid' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($commission['status'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($commission['description'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 4: Documents (Dual Soft & Hard Copy Lifecycle) -->
    <div class="tab-pane fade" id="tab-documents" role="tabpanel" aria-labelledby="tab-documents-btn">
        <div class="row g-4">
            <!-- Statutory Legal Deeds & Downloads Box -->
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2f6 100%); border-left: 5px solid #0d6efd !important;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-primary">
                                    <i class="fas fa-balance-scale me-2"></i> वैधानिक विलेख व डिजिटल प्रारूप (Statutory Legal Deeds & Soft Copies)
                                </h5>
                                <p class="text-muted small mb-0">
                                    त्रिपक्षीय मास्टर डीड (Master Deed) एवं रद्दीकरण समझौता विलेख (Cancellation & Settlement Deeds) को ₹100/₹500 स्टाम्प पेपर अथवा भरणीय (Fillable) प्रारूप में प्राप्त करें।
                                </p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadExecutedDocModal">
                                    <i class="fas fa-file-upload me-1"></i> हस्ताक्षरित भौतिक विलेख अपलोड करें (Upload Executed Scan)
                                </button>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <a href="<?= BASE_URL ?>/documents/cancellation_settlement_deed.html" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-print me-1"></i> रद्दीकरण विलेख (Printable Stamp Paper Mode - 95mm)
                            </a>
                            <a href="<?= BASE_URL ?>/downloads/cancellation_settlement_deed_hindi.doc" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-file-word me-1"></i> भरणीय रद्दीकरण विलेख (.DOC Hindi)
                            </a>
                            <a href="<?= BASE_URL ?>/downloads/cancellation_settlement_deed_english.doc" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-file-word me-1"></i> Fillable Cancellation Deed (.DOC English)
                            </a>
                            <a href="<?= BASE_URL ?>/admin/bookings/<?= $booking['id'] ?>/legal-kit" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-archive me-1"></i> Complete Legal Kit (ZIP)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Table & Physical Archive Info -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-folder-open me-2 text-warning"></i> संलग्न विलेख व भौतिक फाइल ट्रैकिंग (Booking Documents & Archive)</h5>
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <i class="fas fa-archive text-info me-1"></i> दोहरी सुरक्षा: डिजिटल स्कैन + कार्यालय लॉकर ट्रैकिंग
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle bg-white shadow-sm">
                        <thead class="table-light">
                            <tr>
                                <th>दस्तावेज़ का नाम (Document)</th>
                                <th>प्रकार (Type)</th>
                                <th>दस्तावेज़ संख्या (Doc No.)</th>
                                <th>कार्यालय भौतिक स्थान (Hard Copy Archive)</th>
                                <th>दिनांक</th>
                                <th>स्थिति (Status)</th>
                                <th class="text-center">कार्रवाई (Action)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documents)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                    इस बुकिंग के लिए अभी तक कोई हस्ताक्षरित भौतिक विलेख या स्कैन संलग्न नहीं है।
                                    <br>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadExecutedDocModal">
                                        <i class="fas fa-upload me-1"></i> प्रथम विलेख अपलोड करें
                                    </button>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td>
                                    <strong><i class="fas fa-file-pdf text-danger me-2"></i><?= htmlspecialchars($doc['document_name'] ?? 'Document') ?></strong>
                                    <?php if (!empty($doc['notes'])): ?>
                                        <div class="small text-muted mt-1"><?= htmlspecialchars($doc['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= ucfirst(str_replace('_', ' ', $doc['document_type'] ?? 'General')) ?>
                                    </span>
                                </td>
                                <td><code><?= htmlspecialchars($doc['document_number'] ?? 'N/A') ?></code></td>
                                <td>
                                    <?php if (!empty($doc['physical_location'])): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-archive me-1"></i> <?= htmlspecialchars($doc['physical_location']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">डिजिटल केवल / Not Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y', strtotime($doc['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($doc['status'] ?? '') === 'approved' || ($doc['status'] ?? '') === 'verified' ? 'success' : (($doc['status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($doc['status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/admin/bookings/documents/<?= $doc['id'] ?>/download" class="btn btn-sm btn-outline-primary" title="डाउनलोड / देखें">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
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
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel"><?= __('admin_confirm_delete') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= __('admin_confirm_delete_booking') ?> <strong><?= htmlspecialchars($booking['booking_number'] ?? '') ?></strong>?<br>
                <?= __('admin_delete_warning') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('admin_cancel') ?></button>
                <form method="POST" action="<?= BASE_URL ?>/admin/bookings/<?= $booking['id'] ?>/destroy" >
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger"><?= __('admin_delete') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel"><?= __('admin_add_payment') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/bookings/<?= $booking['id'] ?>/payment">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="amount" class="form-label"><?= __('admin_amount_label') ?> (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="amount" name="amount"
                                step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label"><?= __('admin_payment_method') ?></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value=""><?= __('admin_select_method') ?></option>
                            <option value="cash"><?= __('admin_cash') ?></option>
                            <option value="bank_transfer"><?= __('admin_bank_transfer') ?></option>
                            <option value="cheque"><?= __('admin_cheque') ?></option>
                            <option value="online"><?= __('admin_online_payment') ?></option>
                            <option value="upi"><?= __('admin_upi') ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label"><?= __('admin_transaction_id') ?></label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                            placeholder="<?= __('admin_enter_transaction_id') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('admin_cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('admin_add_payment') ?></button>
                </div>
            </form>
        </div>
    </div>
<!-- Upload Executed Document Modal -->
<div class="modal fade" id="uploadExecutedDocModal" tabindex="-1" aria-labelledby="uploadExecutedDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="uploadExecutedDocModalLabel">
                    <i class="fas fa-file-signature me-2"></i> हस्ताक्षरित भौतिक विलेख / स्कैन अपलोड करें
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/bookings/<?= $booking['id'] ?>/upload-document" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="alert alert-info py-2 px-3 small border-start border-4 border-info mb-3">
                        <i class="fas fa-info-circle me-1"></i> स्टाम्प पेपर पर हस्ताक्षरित त्रिपक्षीय मास्टर डीड अथवा रद्दीकरण समझौता विलेख की स्कैन प्रति (PDF/JPG) अपलोड करें और कार्यालय में इसके भौतिक फाइल स्थान (Cabinet/Shelf) को दर्ज करें।
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="doc_name" class="form-label fw-semibold">विलेख का शीर्षक / दस्तावेज़ का नाम <span class="text-danger">*</span></label>
                            <input type="text" id="doc_name" name="document_name" class="form-control" placeholder="उदा: Signed Tripartite Master Deed on Rs.500 Stamp Paper" required>
                        </div>

                        <div class="col-md-5">
                            <label for="doc_type" class="form-label fw-semibold">विलेख का प्रकार (Type) <span class="text-danger">*</span></label>
                            <select id="doc_type" name="document_type" class="form-select" required>
                                <option value="signed_master_deed">हस्ताक्षरित मास्टर डीड (Signed Master Deed)</option>
                                <option value="cancellation_settlement_deed">रद्दीकरण व समझौता विलेख (Cancellation Deed)</option>
                                <option value="stamp_paper_agreement">स्टाम्प पेपर अनुबंध (Stamp Paper Agreement)</option>
                                <option value="allotment_letter">आवंटन पत्र (Allotment Letter)</option>
                                <option value="noc">अनापत्ति प्रमाण पत्र (NOC)</option>
                                <option value="possession_handover">कब्जा हस्तांतरण पत्र (Possession Handover)</option>
                                <option value="registry_deed">रजिस्ट्री बैनामा (Registry Deed)</option>
                                <option value="other">अन्य विलेख (Other)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="doc_number" class="form-label fw-semibold">स्टाम्प / विलेख संख्या (Stamp / Document No.)</label>
                            <input type="text" id="doc_number" name="document_number" class="form-control" placeholder="उदा: STAMP-UP-2026-0089">
                        </div>

                        <div class="col-md-6">
                            <label for="doc_status" class="form-label fw-semibold">सत्यापन स्थिति (Status)</label>
                            <select id="doc_status" name="status" class="form-select">
                                <option value="verified" selected>सत्यापित (Verified & Archived)</option>
                                <option value="approved">स्वीकृत (Approved)</option>
                                <option value="pending">लंबित (Pending Verification)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="physical_loc" class="form-label fw-semibold">
                                <i class="fas fa-archive text-primary me-1"></i> कार्यालय में हार्ड कॉपी का भौतिक स्थान (Hard Copy Physical Rack / Locker)
                            </label>
                            <input type="text" id="physical_loc" name="physical_location" class="form-control" placeholder="उदा: Gorakhpur HQ — Legal Cabinet 2, Shelf B, Folder #<?= htmlspecialchars($booking['booking_number'] ?? '') ?>">
                            <small class="text-muted">कार्यालय में मूल हार्ड कॉपी किस अलमारी/शेल्फ/बॉक्स में रखी गई है ताकि त्वरित रूप से मिल सके।</small>
                        </div>

                        <div class="col-12">
                            <label for="doc_file" class="form-label fw-semibold">स्कैन फ़ाइल चुनें (PDF, JPG, PNG, DOC) <span class="text-danger">*</span></label>
                            <input type="file" id="doc_file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        </div>

                        <div class="col-12">
                            <label for="doc_notes" class="form-label fw-semibold">अतिरिक्त विवरण / गवाह व नोट (Internal Notes)</label>
                            <textarea id="doc_notes" name="notes" class="form-control" rows="2" placeholder="गवाहों के नाम, उप-निबंधक बही संख्या या अन्य प्रासंगिक विवरण..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> सुरक्षित रूप से अपलोड व आर्काइव करें
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete() {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function addPayment() {
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }

    function viewReceipt(receiptNumber) {
        window.open('<?= BASE_URL ?>/admin/payments/receipt/' + receiptNumber, '_blank');
    }
</script>

