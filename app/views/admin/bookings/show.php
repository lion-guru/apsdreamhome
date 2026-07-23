<?php
$page_title = __('admin_booking_details');
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
</style>';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= __('admin_booking_details') ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= __('admin_back_to_bookings') ?>
        </a>
        <a href="<?= BASE_URL ?>admin/bookings/<?= $booking['id'] ?>/edit" class="btn btn-primary ms-2">
            <i class="fas fa-edit"></i> <?= __('admin_edit_booking') ?>
        </a>
        <button type="button" class="btn btn-danger ms-2" onclick="confirmDelete()">
            <i class="fas fa-trash"></i> <?= __('admin_delete') ?>
        </button>
    </div>
</div>

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
                    <?php $payment_percentage = ($booking['total_amount'] > 0) ? ($total_paid / $booking['total_amount']) * 100 : 0; ?>
                    <div class="progress-bar" role="progressbar"
                        style="width: <?= min($payment_percentage, 100) ?>%"
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
                <?php if ($booking['associate_name']): ?>
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
                <form method="POST" action="<?= BASE_URL ?>admin/bookings/<?= $booking['id'] ?>/destroy" style="display: inline;">
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
            <form method="POST" action="<?= BASE_URL ?>admin/bookings/<?= $booking['id'] ?>/payment">
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

