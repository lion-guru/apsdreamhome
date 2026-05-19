<?php
$page_title = $page_title ?? 'My Bookings';
$current_page = 'bookings';
$bookings = $bookings ?? [];
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">My Bookings</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-file-invoice text-success me-2"></i>My Bookings</h4>
            <a href="<?= BASE_URL ?>/plots" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Book New Plot</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-file-invoice fa-4x mb-3"></i>
                <h5>No Bookings Yet</h5>
                <p>Browse available plots and book your dream plot today!</p>
                <a href="<?= BASE_URL ?>/plots" class="btn btn-primary"><i class="fas fa-search me-1"></i>Browse Plots</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Plot</th>
                            <th>Colony</th>
                            <th>Total</th>
                            <th>Token Paid</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
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
                                <div class="progress" style="height:4px;width:80px;">
                                    <div class="progress-bar bg-success" style="width:<?= min(100, ($tokenPaid / $tokenRequired) * 100) ?>%"></div>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($bStatus) ?></span></td>
                            <td><small><?= date('d M Y', strtotime($b['created_at'] ?? $b['booking_date'] ?? 'now')) ?></small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/confirmation" class="btn btn-outline-info" title="View Details"><i class="fas fa-eye"></i></a>
                                    <?php if ($bStatus === 'pending' && $tokenRequired > $tokenPaid): ?>
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/pay" class="btn btn-success" title="Pay Token"><i class="fas fa-credit-card"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/booking/<?= $b['id'] ?>/receipt" class="btn btn-outline-secondary" title="Print Receipt"><i class="fas fa-print"></i></a>
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