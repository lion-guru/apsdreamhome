<?php $pageTitle = 'Request Refund'; ?>
<?php $payment = $payment ?? null; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>payments">Payments</a></li>
            <li class="breadcrumb-item active">Request Refund</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-undo me-2"></i>Refund Request</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if ($payment): ?>
                    <div class="alert alert-info"><strong>Payment:</strong> ₹<?= number_format($payment['amount'] ?? 0) ?> via <?= htmlspecialchars($payment['gateway'] ?? '-') ?> on <?= htmlspecialchars($payment['created_at'] ?? '-') ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= BASE_URL ?>payments/refund">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Transaction ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="transaction_id" value="<?= htmlspecialchars($payment['transaction_id'] ?? '') ?>" required placeholder="Enter transaction ID">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Refund Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group"><span class="input-group-text">₹</span><input type="number" class="form-control" name="amount" step="0.01" min="1" required placeholder="Amount to refund"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reason for Refund <span class="text-danger">*</span></label>
                                <select class="form-select" name="reason" required>
                                    <option value="">Select reason...</option>
                                    <option value="duplicate">Duplicate Payment</option>
                                    <option value="cancelled">Order Cancelled</option>
                                    <option value="customer_request">Customer Request</option>
                                    <option value="service_issue">Service Issue</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Detailed Reason</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Explain why this refund is needed"></textarea>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="confirm" id="confirm" required>
                            <label class="form-check-label" for="confirm">I confirm that this refund is valid and authorized</label>
                        </div>
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-undo me-2"></i>Submit Refund Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
