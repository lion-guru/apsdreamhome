<?php
$page_title = $page_title ?? 'EMI Tracker';
$current_page = 'emi-tracker';
$bookings = $bookings ?? [];
$installments = $installments ?? [];
$stats = $stats ?? ['total_bookings'=>0,'active_emis'=>0,'total_paid'=>0,'total_pending'=>0,'overdue_count'=>0,'next_payment'=>null];
$today = date('Y-m-d');
?>

<div class="aps-cp-hero style-68644">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-calendar-check me-2"></i>EMI Tracker</h2>
            <p>Track your payment schedule, upcoming installments, and penalties.</p>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-file-contract"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= $stats['total_bookings'] ?></div>
                <div class="aps-cp-stat-label">Total Bookings</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= $stats['active_emis'] ?></div>
                <div class="aps-cp-stat-label">Active EMIs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format($stats['total_paid']) ?></div>
                <div class="aps-cp-stat-label">Total Paid</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--red">
            <div class="aps-cp-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format($stats['total_pending']) ?></div>
                <div class="aps-cp-stat-label">Total Pending</div>
                <?php if ($stats['overdue_count'] > 0): ?>
                    <small class="text-danger fw-bold"><?= $stats['overdue_count'] ?> overdue</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Next Payment Alert -->
<?php if ($stats['next_payment']): ?>
<?php $np = $stats['next_payment']; ?>
<div class="aps-cp-card mb-4 style-95767">
    <div class="aps-cp-card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="mb-1 style-23621"><i class="fas fa-bell me-2"></i>Next Payment Due</h6>
                <div class="fw-bold">Installment #<?= htmlspecialchars($np['installment_number'] ?? '—') ?> — <?= htmlspecialchars($np['booking_number'] ?? '') ?></div>
                <div class="text-muted">Due: <?= date('D, d M Y', strtotime($np['due_date'])) ?> | Plot: <?= htmlspecialchars($np['plot_number'] ?? '—') ?>, <?= htmlspecialchars($np['colony_name'] ?? '') ?></div>
            </div>
            <div class="text-end">
                <div class="fs-4 fw-bold text-primary">₹<?= number_format((float)($np['amount'] ?? 0)) ?></div>
                <a href="<?= BASE_URL ?>/user/installments/<?= $np['id'] ?>/pay" class="btn btn-primary btn-sm mt-1">
                    <i class="fas fa-credit-card me-1"></i> Pay Now
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Installment Table -->
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-list me-2 text-primary"></i>Payment Schedule</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($installments)): ?>
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-calendar-check"></i></div>
                <h5>No installments yet</h5>
                <p>Book a property to start your EMI schedule.</p>
                <a href="<?= BASE_URL ?>/properties" class="btn btn-primary"><i class="fas fa-search me-1"></i> Browse Properties</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Booking</th>
                            <th>Plot / Colony</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Penalty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($installments as $idx => $inst):
                            $status = $inst['status'] ?? 'pending';
                            $amount = (float)($inst['amount'] ?? 0);
                            $paid = (float)($inst['paid_amount'] ?? 0);
                            $penalty = (float)($inst['accrued_penalty'] ?? 0);
                            $isOverdue = ($status !== 'paid' && $status !== 'completed' && strtotime($inst['due_date'] ?? '') < strtotime($today));
                            $isPaid = ($status === 'paid' || $status === 'completed');
                            $badgeClass = $isPaid ? 'success' : ($isOverdue ? 'danger' : 'warning');
                        ?>
                        <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                            <td><strong><?= $idx + 1 ?></strong></td>
                            <td>
                                <small class="text-muted"><?= htmlspecialchars($inst['booking_number'] ?? '') ?></small>
                                <br>Inst #<?= htmlspecialchars($inst['installment_number'] ?? '—') ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($inst['plot_number'] ?? '—') ?>
                                <br><small class="text-muted"><?= htmlspecialchars($inst['colony_name'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                    <?= date('d M Y', strtotime($inst['due_date'])) ?>
                                    <?php if ($isOverdue): ?>
                                        <br><small class="text-danger">Overdue</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="fw-bold">₹<?= number_format($amount) ?></td>
                            <td>
                                <?php if ($isPaid): ?>
                                    <span class="text-success">₹<?= number_format($paid) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">₹<?= number_format($paid) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($penalty > 0): ?>
                                    <span class="text-danger fw-bold">₹<?= number_format($penalty) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">₹0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?php if ($isPaid): ?><i class="fas fa-check me-1"></i>Paid
                                    <?php elseif ($isOverdue): ?><i class="fas fa-exclamation me-1"></i>Overdue
                                    <?php else: ?><i class="fas fa-hourglass me-1"></i>Due
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$isPaid): ?>
                                    <a href="<?= BASE_URL ?>/user/installments/<?= $inst['id'] ?>/pay" class="btn btn-primary btn-sm">
                                        <i class="fas fa-credit-card me-1"></i> Pay
                                    </a>
                                    <a href="<?= BASE_URL ?>/user/installments/<?= $inst['id'] ?>/demand-letter" class="btn btn-outline-secondary btn-sm" title="Demand Letter">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
