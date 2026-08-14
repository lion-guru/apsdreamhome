<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>Booking Details</h1>
        <div>
            <?php if (isset($status['booking']) && $status['booking']['status'] === 'pending'): ?>
                <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/<?= $status['booking']['id'] ?>/approve" class="btn btn-success btn-sm" onclick="return confirm('Approve this booking? Plot will be marked as booked and commission will be processed.')">
                    <i class="fas fa-check me-1"></i>Approve
                </a>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <?php if (isset($status['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($status['error']) ?></div>
    <?php elseif (!empty($status['booking'])): $b = $status['booking']; ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between">
                        <h5 class="mb-0">Booking Information</h5>
                        <span class="badge bg-<?= $b['status'] === 'confirmed' ? 'primary' : ($b['status'] === 'cancelled' ? 'danger' : ($b['status'] === 'completed' ? 'success' : 'warning')) ?> fs-6">
                            <?= htmlspecialchars($b['status']) ?>
                        </span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="table-responsive"><table class="table table-bordered mb-0">
                            <tr><th class="style-58160">Booking ID</th><td>#<?= $b['id'] ?></td></tr>
                            <tr><th>Booking Number</th><td><?= htmlspecialchars($b['booking_number'] ?? 'N/A') ?></td></tr>
                            <tr><th>Customer</th><td>
                                <?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?>
                                <?php if ($b['customer_phone'] ?? ''): ?><br><small class="text-muted">ðŸ“ž <?= htmlspecialchars($b['customer_phone']) ?></small><?php endif; ?>
                                <?php if ($b['customer_email'] ?? ''): ?><br><small class="text-muted">âœ‰ <?= htmlspecialchars($b['customer_email']) ?></small><?php endif; ?>
                            </td></tr>
                            <tr><th>Plot</th><td>
                                Plot #<?= htmlspecialchars($b['plot_number'] ?? 'N/A') ?>
                                <?php if ($b['block'] ?? ''): ?> | Block <?= htmlspecialchars($b['block']) ?><?php endif; ?>
                                <?php if ($b['colony_name'] ?? ''): ?><br><small><?= htmlspecialchars($b['colony_name']) ?></small><?php endif; ?>
                            </td></tr>
                            <tr><th>Total Amount</th><td><strong>â‚¹<?= number_format((float)$status['total_amount'], 2) ?></strong></td></tr>
                            <tr><th>Paid Amount</th><td>â‚¹<?= number_format((float)$status['paid_amount'], 2) ?></td></tr>
                            <tr><th>Token Progress</th><td>
                                <div class="progress" class="style-39312">
                                    <div class="progress-bar bg-<?= $status['token_percentage'] >= 25 ? 'success' : 'danger' ?>" class="style-61073">
                                        <?= $status['token_percentage'] ?>%
                                    </div>
                                </div>
                                <small class="text-muted">Deadline: <?= htmlspecialchars($status['token_deadline'] ?? 'N/A') ?></small>
                            </td></tr>
                            <tr><th>Token Requirement Met</th><td>
                                <span class="badge bg-<?= $status['token_met'] ? 'success' : 'danger' ?>">
                                    <?= $status['token_met'] ? 'Yes (25% paid)' : 'No (pending)' ?>
                                </span>
                                <?php if (!$status['token_met'] && $b['status'] === 'pending'): ?>
                                    <br><small class="text-warning">â�³ Awaiting 25% token payment before approval</small>
                                <?php endif; ?>
                            </td></tr>
                            <tr><th>Payment Status</th><td><?= htmlspecialchars($b['payment_status'] ?? 'N/A') ?></td></tr>
                            <tr><th>Booking Type</th><td><?= htmlspecialchars($b['booking_type'] ?? 'N/A') ?></td></tr>
                            <tr><th>Booking Date</th><td><?= htmlspecialchars($b['booking_date'] ?? 'N/A') ?></td></tr>
                            <tr><th>Created</th><td><?= htmlspecialchars($b['created_at'] ?? 'N/A') ?></td></tr>
                            <?php if ($b['confirmed_at'] ?? ''): ?>
                            <tr><th>Confirmed At</th><td><?= htmlspecialchars($b['confirmed_at']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($b['cancellation_reason'] ?? ''): ?>
                            <tr><th>Cancellation Reason</th><td class="text-danger"><?= htmlspecialchars($b['cancellation_reason']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($b['notes'] ?? ''): ?>
                            <tr><th>Notes</th><td><?= nl2br(htmlspecialchars($b['notes'])) ?></td></tr>
                            <?php endif; ?>
                        </table></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">EMI Schedule</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <p>Total EMIs: <strong><?= $status['emi_count'] ?? 0 ?></strong></p>
                        <p>Paid EMIs: <strong><?= $status['paid_emis'] ?? 0 ?></strong></p>
                        <div class="progress mb-3" class="style-76750">
                            <div class="progress-bar bg-success" class="style-46071"></div>
                        </div>
                        <?php if (!empty($status['emis'])): ?>
                        <div class="table-responsive"><table class="table table-sm">
                            <thead><tr><th>#</th><th>Due</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($status['emis'] as $emi): ?>
                                <tr>
                                    <td><?= $emi['installment_no'] ?></td>
                                    <td><small><?= htmlspecialchars($emi['due_date'] ?? '') ?></small></td>
                                    <td>â‚¹<?= number_format((float)($emi['amount'] ?? 0)) ?></td>
                                    <td><span class="badge bg-<?= ($emi['status'] ?? '') === 'paid' ? 'success' : 'warning' ?>"><?= htmlspecialchars($emi['status'] ?? 'pending') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h5 class="mb-0">Record Payment</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/bookings/payment">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <div class="mb-2"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount (â‚¹)" required></div>
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

<?php if (isset($status['booking']) && $status['booking']['status'] === 'pending'): ?>
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/bookings/<?= $status['booking']['id'] ?>/reject">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Booking #<?= $status['booking']['id'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Reject booking by <strong><?= htmlspecialchars($status['booking']['customer_name'] ?? 'N/A') ?></strong> for plot #<?= htmlspecialchars($status['booking']['plot_number'] ?? 'N/A') ?>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for rejection</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Optional reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>