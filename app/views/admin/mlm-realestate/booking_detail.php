<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>Booking Details</h1>
        <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if (isset($status['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($status['error']) ?></div>
    <?php elseif (!empty($status['booking'])): $b = $status['booking']; ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Booking Information</h5></div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr><th style="width:200px;">Booking ID</th><td>#<?= $b['id'] ?></td></tr>
                            <tr><th>Customer</th><td><?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?></td></tr>
                            <tr><th>Total Amount</th><td>₹<?= number_format((float)$status['total_amount'], 2) ?></td></tr>
                            <tr><th>Paid Amount</th><td>₹<?= number_format((float)$status['paid_amount'], 2) ?></td></tr>
                            <tr><th>Token %</th><td>
                                <div class="progress" style="height:20px;">
                                    <div class="progress-bar bg-<?= $status['token_percentage'] >= 25 ? 'success' : 'danger' ?>" style="width:<?= min(100, $status['token_percentage']) ?>%">
                                        <?= $status['token_percentage'] ?>%
                                    </div>
                                </div>
                            </td></tr>
                            <tr><th>Token Deadline</th><td><?= htmlspecialchars($status['token_deadline']) ?></td></tr>
                            <tr><th>Token Met</th><td><span class="badge bg-<?= $status['token_met'] ? 'success' : 'danger' ?>"><?= $status['token_met'] ? 'Yes' : 'No' ?></span></td></tr>
                            <tr><th>Status</th><td><span class="badge bg-<?= $b['status'] === 'completed' ? 'success' : ($b['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($b['status']) ?></span></td></tr>
                            <tr><th>Payment Status</th><td><?= htmlspecialchars($b['payment_status'] ?? 'N/A') ?></td></tr>
                            <tr><th>Booking Date</th><td><?= htmlspecialchars($b['booking_date'] ?? 'N/A') ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">EMI Schedule</h5></div>
                    <div class="card-body">
                        <p>Total EMIs: <strong><?= $status['emi_count'] ?? 0 ?></strong></p>
                        <p>Paid EMIs: <strong><?= $status['paid_emis'] ?? 0 ?></strong></p>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-success" style="width:<?= ($status['emi_count'] ?? 0) > 0 ? (($status['paid_emis'] ?? 0) * 100 / $status['emi_count']) : 0 ?>%"></div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h5 class="mb-0">Record Payment</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/bookings/payment">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <div class="mb-2"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount (₹)" required></div>
                            <div class="mb-2">
                                <select name="mode" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-money-bill me-1"></i>Record Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Booking not found.</div>
    <?php endif; ?>
</div>
