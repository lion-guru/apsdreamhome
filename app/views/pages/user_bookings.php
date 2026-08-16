<?php
$page_title = $page_title ?? __('bookings_page_title', [], 'My Bookings');
$current_page = 'bookings';
$bookings = $bookings ?? [];
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active"><?php echo __('bookings_breadcrumb', [], 'My Bookings'); ?></li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-file-invoice text-success me-2"></i><?php echo __('bookings_heading', [], 'My Bookings'); ?></h4>
            <a href="<?= BASE_URL ?>/plots" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i><?php echo __('book_new_plot', [], 'Book New Plot'); ?></a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-file-invoice fa-4x mb-3"></i>
                <h5><?php echo __('bookings_empty_title', [], 'No Bookings Yet'); ?></h5>
                <p><?php echo __('bookings_empty_desc', [], 'Browse available plots and book your dream plot today!'); ?></p>
                <a href="<?= BASE_URL ?>/plots" class="btn btn-primary"><i class="fas fa-search me-1"></i><?php echo __('browse_plots', [], 'Browse Plots'); ?></a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th><?php echo __('bookings_th_plot', [], 'Plot'); ?></th>
                            <th><?php echo __('bookings_th_colony', [], 'Colony'); ?></th>
                            <th><?php echo __('bookings_th_total', [], 'Total'); ?></th>
                            <th><?php echo __('bookings_th_token', [], 'Token Paid'); ?></th>
                            <th><?php echo __('bookings_th_status', [], 'Status'); ?></th>
                            <th><?php echo __('bookings_th_date', [], 'Date'); ?></th>
                            <th><?php echo __('bookings_th_actions', [], 'Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b):
                            $bStatus = $b['status'] ?? 'pending';
                            $badgeClass = match($bStatus) {
                                'confirmed','completed' => 'success',
                                'cancelled' => 'danger',
                                'pending' => 'warning',
                                default => 'secondary'
                            };
                            $tokenPaid = (float)($b['amount'] ?? 0);
                            $totalAmt = (float)($b['total_amount'] ?? 0);
                            $tokenRequired = $totalAmt * 0.25;
                        ?>
                        <tr>
                            <td><strong>#<?= $b['id'] ?></strong></td>
                            <td><strong>#<?= htmlspecialchars($b['plot_number'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($b['colony_name'] ?? 'N/A') ?></td>
                            <td>₹<?= number_format($totalAmt) ?></td>
                            <td>
                                ₹<?= number_format($tokenPaid) ?>
                                <?php if ($tokenRequired > 0): ?>
                                <div class="progress" class="style-52430">
                                    <div class="progress-bar bg-success" class="style-15112"></div>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($bStatus) ?></span></td>
                            <td><small><?= date('d M Y', strtotime($b['created_at'] ?? $b['booking_date'] ?? 'now')) ?></small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/confirmation" class="btn btn-outline-info" title="<?php echo __('view_details', [], 'View Details'); ?>"><i class="fas fa-eye"></i></a>
                                    <?php if ($bStatus === 'pending' && $tokenRequired > $tokenPaid): ?>
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/pay" class="btn btn-success" title="<?php echo __('pay_token', [], 'Pay Token'); ?>"><i class="fas fa-credit-card"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/receipt" class="btn btn-outline-secondary" title="<?php echo __('print_receipt', [], 'Print Receipt'); ?>"><i class="fas fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>