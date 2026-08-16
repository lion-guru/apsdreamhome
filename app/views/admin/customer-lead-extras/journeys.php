<?php
// Session started by controller
$page_title = 'Customer Journeys';
$page_description = 'Track and analyze customer journeys';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Customer Journeys</h1>
            <p class="text-muted">Track and analyze customer journeys</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-sync-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Journeys</h6>
                            <h3 class="mb-0"><?php echo $stats['total_journeys'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-play-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active Journeys</h6>
                            <h3 class="mb-0"><?php echo $stats['active_journeys'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Completed Journeys</h6>
                            <h3 class="mb-0"><?php echo $stats['completed_journeys'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Avg Duration</h6>
                            <h3 class="mb-0"><?= ($stats['avg_duration'] ?? null) ? round((float)$stats['avg_duration']) . ' days' : 'N/A' ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customer Journeys</h5>
                <a href="<?php echo BASE_URL; ?>/admin/customer-lead/journeys" class="btn btn-outline-primary btn-sm">Refresh</a>
            </div>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Journey</th>
                            <th>Started</th>
                            <th>Last Touch</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($journeys)): ?>
                            <?php foreach ($journeys as $journey): ?>
                                <?php
                                $started = new DateTime($journey['started_at']);
                                $lastTouch = new DateTime($journey['last_touch_at']);
                                $duration = $started->diff($lastTouch);
                                $durationDays = $duration->days;
                                $statusClass = strpos(strtolower($journey['journey']), 'completed') !== false || 
                                             strpos(strtolower($journey['journey']), 'converted') !== false ? 
                                             'success' : (strpos(strtolower($journey['journey']), 'active') !== false ? 'primary' : 'secondary');
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="<?= BASE_URL ?>/assets/images/user/default-avatar.jpg" alt="Avatar" class="rounded-circle" width="32" height="32" />
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($journey['customer_name'] ?? 'Unknown'); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($journey['customer_email'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" class="style-65684" title="<?php echo htmlspecialchars($journey['journey'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($journey['journey'] ?? 'No journey'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($journey['started_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($journey['last_touch_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark"><?php echo $durationDays; ?> days</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($journey['journey'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/customer-lead/journeys/<?php echo $journey['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No customer journeys found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>