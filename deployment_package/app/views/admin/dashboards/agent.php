<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-user-tie me-2"></i>Agent/Associate Dashboard</h2>
        </div>
    </div>

    <!-- Agent Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">My Commissions</h6>
                    <h3>₹<?php echo $my_commissions['total'] ?? '0'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-wallet me-1"></i>₹<?php echo $my_commissions['pending'] ?? '0'; ?> Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">My Network Size</h6>
                    <h3><?php echo $my_network['total_associates'] ?? '0'; ?></h3>
                    <p class="text-muted mb-0"><?php echo $my_network['active_associates'] ?? '0'; ?> Active now</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">My Total Bookings</h6>
                    <h3><?php echo $my_commissions['bookings_count'] ?? '0'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-star me-1"></i>Top Performer</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales Performance -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Sales Performance</h5>
                </div>
                <div class="card-body" style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p>Performance Visualization (Integration Pending)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network Activity -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Network Activity</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <?php if (empty($my_network['recent_activity'])): ?>
                            <li class="list-group-item text-center py-4 text-muted">No recent activity</li>
                        <?php else: ?>
                            <?php foreach ($my_network['recent_activity'] as $activity): ?>
                                <li class="list-group-item">
                                    <strong><?php echo $activity['associate_name']; ?></strong>
                                    <span class="text-muted"><?php echo $activity['action']; ?></span>
                                    <div class="text-end mt-1"><small><?php echo $activity['date']; ?></small></div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
