<?php
$extraHead = '<style>
    body { background: #f5f6fa; }
    .dashboard-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .dashboard-card:hover { transform: translateY(-3px); }
    .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
</style>';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient p-4 rounded-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="text-white mb-1">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h3>
                        <p class="text-white-50 mb-0">
                            <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email'] ?? ''); ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($user['phone'] ?? ''); ?>
                        </p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-light btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>Post Property
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($registered)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><strong>Congratulations!</strong> Your account has been created successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($loginSuccess)): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-sign-in-alt me-2"></i>You have been logged in successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?php echo count($properties); ?></h3>
                        <p class="text-muted mb-0">My Properties</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?php echo count($inquiries); ?></h3>
                        <p class="text-muted mb-0">My Inquiries</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?php echo array_sum(array_column($properties, 'views')); ?></h3>
                        <p class="text-muted mb-0">Property Views</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-plus-circle mb-2 fs-4"></i><br>
                                Post New Property
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?php echo BASE_URL; ?>/user/properties" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-list mb-2 fs-4"></i><br>
                                View My Properties
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?php echo BASE_URL; ?>/user/inquiries" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-history mb-2 fs-4"></i><br>
                                Inquiry History
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?php echo BASE_URL; ?>/user/profile" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-user-cog mb-2 fs-4"></i><br>
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($properties)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-building me-2 text-primary"></i>My Recent Properties</h5>
                        <a href="<?php echo BASE_URL; ?>/user/properties" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($properties, 0, 5) as $p): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($p['address'] ?? ''); ?></small>
                                            </td>
                                            <td><span class="badge bg-secondary"><?php echo ucfirst($p['property_type']); ?></span></td>
                                            <td class="text-success fw-bold">Rs.<?php echo number_format($p['price']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($p['status'] ?? 'pending') {
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($p['status'] ?? 'pending'); ?></span>
                                            </td>
                                            <td><i class="fas fa-eye me-1"></i><?php echo $p['views'] ?? 0; ?></td>
                                            <td><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($inquiries)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2 text-success"></i>Recent Inquiries</h5>
                        <a href="<?php echo BASE_URL; ?>/user/inquiries" class="btn btn-sm btn-success">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($inquiries, 0, 5) as $inq): ?>
                                        <tr>
                                            <td><span class="badge bg-info"><?php echo ucfirst($inq['type'] ?? 'General'); ?></span></td>
                                            <td><?php echo htmlspecialchars(substr($inq['message'] ?? '', 0, 80)); ?>...</td>
                                            <td>
                                                <?php
                                                $statusClass = match($inq['status'] ?? 'new') {
                                                    'new' => 'primary',
                                                    'contacted' => 'info',
                                                    'pending' => 'warning',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($inq['status'] ?? 'new'); ?></span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($inq['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
