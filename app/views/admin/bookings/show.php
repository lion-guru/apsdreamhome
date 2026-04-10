<?php
$page_title = 'Booking Details';
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
include APP_PATH . '/views/admin/layouts/header.php';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Booking Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/bookings" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Bookings
        </a>
        <a href="/admin/bookings/<?= $booking['id'] ?>/edit" class="btn btn-primary ms-2">
            <i class="fas fa-edit"></i> Edit Booking
        </a>
        <button type="button" class="btn btn-danger ms-2" onclick="confirmDelete()">
            <i class="fas fa-trash"></i> Delete
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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> Booking Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Booking Number:</strong><br>
                            <span class="badge bg-primary"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></span>
                        </p>

                        <p><strong>Property:</strong><br>
                            <?= htmlspecialchars($booking['property_title'] ?? '') ?><br>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['property_location'] ?? '') ?>
                            </small>
                        </p>

                        <p><strong>Total Amount:</strong><br>
                            <span class="text-success fw-bold">₹<?= number_format(floatval($booking['total_amount'] ?? 0), 2) ?></span>
                        </p>

                        <p><strong>Booking Date:</strong><br>
                            <?= date('d F Y', strtotime($booking['booking_date'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong><br>
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

                        <p><strong>Created:</strong><br>
                            <?= date('d F Y h:i A', strtotime($booking['created_at'])) ?></p>

                        <p><strong>Last Updated:</strong><br>
                            <?= date('d F Y h:i A', strtotime($booking['updated_at'])) ?></p>

                        <?php if (!empty($booking['notes'])): ?>
                            <p><strong>Notes:</strong><br>
                                <?= nl2br(htmlspecialchars($booking['notes'] ?? '')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie"></i> Financial Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Total Amount</label>
                    <h4 class="text-primary">₹<?= number_format(floatval(booking['total_amount'] ?? 0), 2) ?></h4>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Total Paid</label>
                    <h4 class="text-success">₹<?= number_format($total_paid, 2) ?></h4>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Balance Due</label>
                    <h4 class="text-danger">₹<?= number_format(floatval(booking['total_amount'] ?? 0) - $total_paid, 2) ?></h4>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Total Commission</label>
                    <h4 class="text-info">₹<?= number_format($total_commission, 2) ?></h4>
                </div>

                <div class="progress mb-3">
                    <?php $payment_percentage = ($total_paid / $booking['total_amount']) * 100; ?>
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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user"></i> Customer Information
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong><br><?= htmlspecialchars($booking['customer_name'] ?? '') ?></p>
                <p><strong>Email:</strong><br>
                    <a href="mailto:<?= htmlspecialchars($booking['customer_email'] ?? '') ?>">
                        <?= htmlspecialchars($booking['customer_email'] ?? '') ?>
                    </a>
                </p>
                <p><strong>Phone:</strong><br>
                    <a href="tel:<?= htmlspecialchars($booking['customer_phone'] ?? '') ?>">
                        <?= htmlspecialchars($booking['customer_phone'] ?? '') ?>
                    </a>
                </p>
                <?php if (!empty($booking['customer_address'])): ?>
                    <p><strong>Address:</strong><br>
                        <?= nl2br(htmlspecialchars($booking['customer_address'] ?? '')) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-tie"></i> Associate Information
                </h5>
            </div>
            <div class="card-body">
                <?php if ($booking['associate_name']): ?>
                    <p><strong>Name:</strong><br><?= htmlspecialchars($booking['associate_name'] ?? '') ?></p>
                    <p><strong>Email:</strong><br>
                        <a href="mailto:<?= htmlspecialchars($booking['associate_email'] ?? '') ?>">
                            <?= htmlspecialchars($booking['associate_email'] ?? '') ?>
                        </a>
                    </p>
                    <?php if (!empty($booking['associate_phone'])): ?>
                        <p><strong>Phone:</strong><br>
                            <a href="tel:<?= htmlspecialchars($booking['associate_phone'] ?? '') ?>">
                                <?= htmlspecialchars($booking['associate_phone'] ?? '') ?>
                            </a>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">No associate assigned (Direct Booking)</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment History -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-money-bill-wave"></i> Payment History
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($payments)): ?>
            <p class="text-muted">No payments recorded yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Receipt</th>
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
                                            <i class="fas fa-receipt"></i> View
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
                <i class="fas fa-plus"></i> Add Payment
            </button>
        </div>
    </div>
</div>

<!-- Commission History -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-percentage"></i> Commission History
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($commissions)): ?>
            <p class="text-muted">No commissions recorded yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete booking <strong><?= htmlspecialchars($booking['booking_number'] ?? '') ?></strong>?<br>
                This action cannot be undone and will also delete all related payments and commissions.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="/admin/bookings/<?= $booking['id'] ?>/destroy" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
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
                <h5 class="modal-title" id="paymentModalLabel">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/admin/bookings/<?= $booking['id'] ?>/payment">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="amount" name="amount"
                                step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online Payment</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                            placeholder="Enter transaction ID">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Payment</button>
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
        // Open receipt in new window or modal
        window.open(`/admin/receipts/${receiptNumber}`, '_blank');
    }

    // Auto-refresh page every 30 seconds to show latest updates
    setTimeout(() => {
        window.location.reload();
    }, 30000);
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>