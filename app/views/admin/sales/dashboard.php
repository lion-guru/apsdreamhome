<?php
/** @var array $stats */
/** @var array $recent */
/** @var array $overdue */
$stats = $stats ?? [];
$recent = $recent ?? [];
$overdue = $overdue ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$statusBadge = function ($s) {
    $map = [
        'token_paid'        => 'bg-info',
        'agreement_signed'  => 'bg-primary',
        'emi_active'        => 'bg-warning text-dark',
        'partially_paid'    => 'bg-warning text-dark',
        'fully_paid'        => 'bg-success',
        'cancelled'         => 'bg-danger',
        'transferred'       => 'bg-secondary',
        'registration_done' => 'bg-success',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-chart-line me-2"></i>Module 2: Customer Sales + Allotment</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/new" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Booking
        </a>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-primary text-white">
                    <div class="aps-cp-stat-value"><?= (int)($stats['total_bookings'] ?? 0) ?></div>
                    <div class="aps-cp-stat-label">Total Bookings</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-success text-white">
                    <div class="aps-cp-stat-value"><?= (int)($stats['active_bookings'] ?? 0) ?></div>
                    <div class="aps-cp-stat-label">Active</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-warning text-dark">
                    <div class="aps-cp-stat-value"><?= (int)($stats['overdue_count'] ?? 0) ?></div>
                    <div class="aps-cp-stat-label">Overdue</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-info text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($stats['total_collected'] ?? 0) / 100000, 2) ?>L</div>
                    <div class="aps-cp-stat-label">Total Collected</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header d-flex justify-content-between">
                <h5 class="m-0"><i class="fas fa-list me-2"></i>Recent Bookings</h5>
                <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings" class="btn btn-link btn-sm">View all <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive"><table class="table table-sm m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Plot</th>
                            <th>Status</th>
                            <th class="text-end">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No bookings yet</td></tr>
                        <?php else: foreach ($recent as $b): ?>
                            <tr>
                                <td>
                                    <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($b['id'] ?? 0) ?>">
                                        <?= htmlspecialchars((string)($b['booking_number'] ?? '')) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars((string)($b['customer_name'] ?? '—')) ?></td>
                                <td><?= htmlspecialchars((string)($b['plot_code'] ?? '—')) ?></td>
                                <td><span class="badge <?= $statusBadge($b['status'] ?? '') ?>"><?= htmlspecialchars((string)($b['status'] ?? '')) ?></span></td>
                                <td class="text-end">&#8377;<?= number_format((float)($b['agreement_value'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="m-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Overdue Installments</h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive"><table class="table table-sm m-0">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Due</th>
                            <th class="text-end">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($overdue)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">No overdue</td></tr>
                        <?php else: foreach ($overdue as $o): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($o['booking_number'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($o['due_date'] ?? '')) ?></td>
                                <td class="text-end text-danger fw-bold">&#8377;<?= number_format((float)($o['amount_due'] ?? 0)) ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-warning" href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($o['booking_id'] ?? 0) ?>">Open</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
