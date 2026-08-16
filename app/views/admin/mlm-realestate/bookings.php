<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>Bookings</h1>
        <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Booking</a>
    </div>

    <?php if (count(array_filter($bookings, fn($b) => $b['status'] === 'pending')) > 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-1"></i> 
        <strong><?= count(array_filter($bookings, fn($b) => $b['status'] === 'pending')) ?> pending booking(s)</strong> 
        require approval. Review and approve/reject below.
    </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Customer</th><th>Agent</th><th>Plot</th><th>Total</th><th>Paid</th><th>Mode</th><th>Status</th><th>Date</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr class="<?= $b['status'] === 'pending' ? 'table-warning' : ($b['status'] === 'confirmed' ? 'table-success' : ($b['status'] === 'cancelled' ? 'table-danger' : '')) ?>">
                            <td>#<?= $b['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?>
                                <?php if ($b['customer_phone'] ?? ''): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($b['customer_phone'] ?? '') ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['agent_name'] ?? 'N/A') ?></td>
                            <td>
                                <small>
                                    <?= htmlspecialchars($b['plot_ref'] ?? 'N/A') ?>
                                    <?php if ($b['plot_area'] ?? ''): ?>
                                        <br><?= htmlspecialchars($b['plot_area'] ?? '') ?> sqft
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>₹<?= number_format((float)($b['total_amount'] ?? 0), 2) ?></td>
                            <td>₹<?= number_format((float)($b['amount'] ?? 0), 2) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($b['payment_mode'] ?? ($b['payment_status'] ?? 'N/A')) ?></span></td>
                            <td>
                                <?php
                                $status = $b['status'] ?? 'unknown';
                                $badgeClass = match($status) {
                                    'completed' => 'success',
                                    'confirmed' => 'primary',
                                    'cancelled' => 'danger',
                                    'pending' => 'warning',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars($status ?? '') ?></span>
                                <?php if ($status === 'pending'): ?>
                                    <br><small class="text-muted">Needs approval</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['created_at'] ?? '') ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/<?= $b['id'] ?>" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/<?= $b['id'] ?>/approve" class="btn btn-outline-success" title="Approve" onclick="return confirm('Approve booking #<?= $b['id'] ?>? This will mark the plot as booked and trigger commission.')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $b['id'] ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <?php if ($b['status'] === 'pending'): ?>
                                <div class="modal fade" id="rejectModal<?= $b['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/bookings/<?= $b['id'] ?>/reject">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Booking #<?= $b['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Reject booking by <strong><?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?></strong>?</p>
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
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-calendar fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No bookings found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>