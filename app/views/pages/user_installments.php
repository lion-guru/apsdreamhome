<?php
$page_title = $page_title ?? 'My Installments';
$current_page = $current_page ?? 'installments';
$stats = $stats ?? ['total' => 0, 'paid' => 0, 'pending' => 0, 'overdue' => 0, 'total_due' => 0];
$installments = $installments ?? [];
?>
<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-calendar-check me-2"></i>My Installments</h2>
            <p class="text-muted">Track your payment schedule and upcoming dues</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Bookings
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-list"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= (int)$stats['total'] ?></div>
                <div class="aps-cp-stat-label">Total Installments</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= (int)$stats['paid'] ?></div>
                <div class="aps-cp-stat-label">Paid</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= (int)$stats['pending'] ?></div>
                <div class="aps-cp-stat-label">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-stat aps-cp-stat--red">
            <div class="aps-cp-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= (int)$stats['overdue'] ?></div>
                <div class="aps-cp-stat-label">Overdue</div>
            </div>
        </div>
    </div>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Booking</th>
                        <th>Plot</th>
                        <th>Colony</th>
                        <th>Installment #</th>
                        <th>Due Date</th>
                        <th>Amount Due</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($installments)): ?>
                        <?php foreach ($installments as $i => $inst): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/user/bookings/<?= $inst['booking_id'] ?>">
                                        <?= htmlspecialchars($inst['booking_number'] ?? '-') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($inst['plot_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($inst['colony_name'] ?? '-') ?></td>
                                <td><?= (int)($inst['installment_number'] ?? 0) ?></td>
                                <td><?= date('d M Y', strtotime($inst['due_date'] ?? 'now')) ?></td>
                                <td>₹<?= number_format((float)($inst['amount_due'] ?? 0), 2) ?></td>
                                <td>₹<?= number_format((float)($inst['amount_paid'] ?? 0), 2) ?></td>
                                <td>
                                    <?php
                                        $status = $inst['status'] ?? 'pending';
                                        $badgeClass = [
                                            'paid' => 'success',
                                            'pending' => 'warning',
                                            'overdue' => 'danger',
                                            'partial' => 'info',
                                        ][$status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($inst['status'] ?? 'pending') !== 'paid'): ?>
                                        <a href="<?= BASE_URL ?>/user/installments/<?= $inst['id'] ?>/pay"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-credit-card me-1"></i>Pay
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-check fa-3x mb-3"></i>
                                <h5>No Installments Found</h5>
                                <p class="mb-3">You don't have any payment installments yet.</p>
                                <a href="<?= BASE_URL ?>/user/bookings/new" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Book a Plot
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($installments) && ($stats['overdue'] > 0 || $stats['pending'] > 0)): ?>
<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Total Amount Due:</strong> ₹<?= number_format($stats['total_due'], 2) ?>
    <span class="ms-3">
        <a href="<?= BASE_URL ?>/user/payment-history" class="btn btn-sm btn-outline-info">
            <i class="fas fa-receipt me-1"></i>View Payment History
        </a>
    </span>
</div>
<?php endif; ?>