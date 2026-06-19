<?php
$page_title = $page_title ?? __('user_bookings_title', 'My Bookings');
$current_page = 'bookings';
$bookings = $bookings ?? [];
$user = $user ?? [];
$statusColors = [
    'token_paid' => 'primary',
    'agreement_signed' => 'indigo',
    'emi_active' => 'amber',
    'partially_paid' => 'info',
    'fully_paid' => 'success',
    'cancelled' => 'danger',
    'transferred' => 'secondary',
    'registration_done' => 'success',
];
$statusLabels = [
    'token_paid' => __('user_bookings_status_token_paid', 'Token Paid'),
    'agreement_signed' => __('user_bookings_status_agreement_signed', 'Agreement Signed'),
    'emi_active' => __('user_bookings_status_emi_active', 'EMI Active'),
    'partially_paid' => __('user_bookings_status_partially_paid', 'Partially Paid'),
    'fully_paid' => __('user_bookings_status_fully_paid', 'Fully Paid'),
    'cancelled' => __('user_bookings_status_cancelled', 'Cancelled'),
    'transferred' => __('user_bookings_status_transferred', 'Transferred'),
    'registration_done' => __('user_bookings_status_registered', 'Registered'),
];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-file-invoice-dollar me-2"></i><?= __('user_bookings_title', 'My Bookings') ?></h2>
            <p><?= __('user_bookings_subtitle', 'Track your plot bookings, payment schedules, and download demand letters.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings/new" class="btn btn-light">
                <i class="fas fa-plus me-2"></i><?= __('user_book_a_plot', 'Book a Plot') ?>
            </a>
        </div>
    </div>
</div>

<?php if (empty($bookings)): ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h5><?= __('user_bookings_empty_heading', 'No bookings yet') ?></h5>
                <p><?= __('user_bookings_empty_desc', 'You haven\'t made any plot bookings. Browse our colonies and book your dream plot.') ?></p>
                <a href="<?= BASE_URL ?>/properties" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i><?= __('user_bookings_browse_properties', 'Browse Properties') ?>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>

    <?php
    $totalBookings = count($bookings);
    $activeEmis = 0;
    $totalPending = 0;
    $totalPaid = 0;
    foreach ($bookings as $b) {
        if (($b['status'] ?? '') === 'emi_active' || ($b['status'] ?? '') === 'partially_paid') {
            $activeEmis++;
        }
        $totalPending += (float)($b['total_plot_value'] ?? 0) - (float)($b['total_paid'] ?? 0);
        $totalPaid += (float)($b['total_paid'] ?? 0);
    }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="aps-cp-stat aps-cp-stat--blue">
                <div class="aps-cp-stat-icon"><i class="fas fa-file-contract"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-value"><?= $totalBookings ?></div>
                    <div class="aps-cp-stat-label"><?= __('user_bookings_stat_total', 'Total Bookings') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="aps-cp-stat aps-cp-stat--amber">
                <div class="aps-cp-stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-value"><?= $activeEmis ?></div>
                    <div class="aps-cp-stat-label"><?= __('user_bookings_stat_active_emis', 'Active EMIs') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="aps-cp-stat aps-cp-stat--green">
                <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-value">₹<?= number_format($totalPaid) ?></div>
                    <div class="aps-cp-stat-label"><?= __('user_bookings_stat_total_paid', 'Total Paid') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="aps-cp-stat aps-cp-stat--red">
                <div class="aps-cp-stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-value">₹<?= number_format($totalPending > 0 ? $totalPending : 0) ?></div>
                    <div class="aps-cp-stat-label"><?= __('user_bookings_stat_pending', 'Pending Amount') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <h5><i class="fas fa-list"></i> <?= __('user_bookings_all_bookings', 'All Bookings') ?> (<?= $totalBookings ?>)</h5>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead>
                        <tr>
                            <th><?= __('user_bookings_col_booking', 'Booking #') ?></th>
                            <th><?= __('user_bookings_col_plot', 'Plot') ?></th>
                            <th><?= __('user_bookings_col_colony', 'Colony') ?></th>
                            <th><?= __('user_bookings_col_status', 'Status') ?></th>
                            <th><?= __('user_bookings_col_date', 'Booking Date') ?></th>
                            <th><?= __('user_bookings_col_total', 'Total Value') ?></th>
                            <th><?= __('user_bookings_col_paid', 'Paid') ?></th>
                            <th><?= __('user_bookings_col_pending', 'Pending') ?></th>
                            <th class="text-end"><?= __('user_bookings_col_action', 'Action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b):
                            $bStatus = $b['status'] ?? 'token_paid';
                            $colorClass = $statusColors[$bStatus] ?? 'secondary';
                            $statusLabel = $statusLabels[$bStatus] ?? ucfirst($bStatus);
                            $totalVal = (float)($b['total_plot_value'] ?? 0);
                            $paid = (float)($b['total_paid'] ?? 0);
                            $pending = max(0, $totalVal - $paid);
                        ?>
                        <tr style="cursor:pointer;" onclick="window.location='<?= BASE_URL ?>/user/bookings/<?= (int)$b['id'] ?>'">
                            <td><strong><?= htmlspecialchars($b['booking_number'] ?? 'N/A') ?></strong></td>
                            <td>
                                <?= htmlspecialchars($b['plot_number'] ?? 'N/A') ?>
                                <?php if (!empty($b['block'])): ?>
                                    <br><small class="text-muted"><?= __('user_bookings_block_prefix', 'Block') ?> <?= htmlspecialchars($b['block']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['colony_name'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge bg-<?= $colorClass ?>"><?= $statusLabel ?></span>
                                <?php if ((int)($b['overdue_count'] ?? 0) > 0): ?>
                                    <br><small class="text-danger"><?= $b['overdue_count'] ?> <?= __('user_bookings_overdue', 'overdue') ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($b['booking_date'] ?? $b['created_at'] ?? 'now')) ?></td>
                            <td>₹<?= number_format($totalVal) ?></td>
                            <td>
                                <span class="text-success fw-semibold">₹<?= number_format($paid) ?></span>
                                <?php if ($totalVal > 0): ?>
                                    <br><small class="text-muted"><?= round(($paid / $totalVal) * 100) ?>%</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($pending > 0): ?>
                                    <span class="text-danger fw-semibold">₹<?= number_format($pending) ?></span>
                                <?php else: ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> <?= __('user_bookings_settled', 'Settled') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/user/bookings/<?= (int)$b['id'] ?>" class="aps-cp-icon-btn" title="<?= __('user_bookings_view_details', 'View Details') ?>" onclick="event.stopPropagation()">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>
