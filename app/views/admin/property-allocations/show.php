<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Allocation Details - <?= htmlspecialchars($allocation['allocation_number'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/property-allocations" class="btn btn-secondary">Back</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header aps-cp-card-header"><h5>Allocation Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Allocation #:</strong> <?= htmlspecialchars($allocation['allocation_number'] ?? 'N/A') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong>
                            <span class="badge bg-<?= ($allocation['status'] ?? '') === 'confirmed' ? 'success' : (($allocation['status'] ?? '') === 'cancelled' ? 'danger' : 'warning') ?>">
                                <?= $allocation['status_label'] ?? $allocation['status'] ?>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Booking Amount:</strong> ₹<?= number_format($allocation['booking_amount'] ?? 0) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Total Price:</strong> ₹<?= number_format($allocation['total_price'] ?? 0) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Installment Plan:</strong> <?= ucwords(str_replace('_', ' ', $allocation['installment_plan'] ?? 'N/A')) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Installments:</strong> <?= $allocation['installment_months'] ?? 0 ?> months
                        </div>
                    </div>
                    <?php if (!empty($allocation['notes'])): ?>
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p><?= nl2br(htmlspecialchars($allocation['notes'])) ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="mt-3">
                        <strong>Created:</strong> <?= $allocation['created_at'] ?? 'N/A' ?>
                    </div>
                </div>
            </div>

            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><h5>Payment History</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($payment_history)): ?>
                    <div class="table-responsive"><table class="table table-striped">
                        <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($payment_history as $pmt): ?>
                            <tr>
                                <td><?= $pmt['payment_date'] ?? $pmt['created_at'] ?></td>
                                <td>₹<?= number_format($pmt['amount'] ?? 0) ?></td>
                                <td><?= ucfirst($pmt['payment_method'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-success"><?= ucfirst($pmt['status'] ?? 'completed') ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <?php else: ?>
                    <p class="text-muted">No payments recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header aps-cp-card-header"><h5>Customer</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($allocation['customer_name'] ?? 'N/A') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($allocation['customer_email'] ?? 'N/A') ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($allocation['customer_phone'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header aps-cp-card-header"><h5>Property</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p><strong>Title:</strong> <?= htmlspecialchars($allocation['property_title'] ?? 'N/A') ?></p>
                    <p><strong>Plot #:</strong> <?= htmlspecialchars($allocation['plot_number'] ?? 'N/A') ?></p>
                    <p><strong>Area:</strong> <?= number_format($allocation['area_sqft'] ?? 0) ?> sq.ft.</p>
                </div>
            </div>

            <?php if (($allocation['status'] ?? '') === 'pending'): ?>
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><h5>Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <a href="<?= BASE_URL ?>/admin/property-allocations/<?= $allocation['id'] ?>/confirm" class="btn btn-success w-100 mb-2" onclick="return confirm('Confirm this allocation?')">Confirm Allocation</a>
                    <a href="<?= BASE_URL ?>/admin/property-allocations/<?= $allocation['id'] ?>/cancel" class="btn btn-danger w-100" onclick="return confirm('Cancel this allocation?')">Cancel Allocation</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
