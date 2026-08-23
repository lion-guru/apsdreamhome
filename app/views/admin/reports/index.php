<?php
$reports = $reports ?? [];
$page_title = $page_title ?? 'Reports & Analytics';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Reports & Analytics</h2>
                <p class="text-muted mb-0">View system performance and statistics</p>
            </div>
            <a href="<?php echo e($base); ?>/admin/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Search and Export -->
        <?php require __DIR__ . '/../partials/search_bar.php'; ?>
        <?php require __DIR__ . '/../partials/export_buttons.php'; ?>
        <?php require __DIR__ . '/../partials/mobile_optimization.php'; ?>
        <?php require __DIR__ . '/../partials/realtime_updates.php'; ?>

        <!-- Report Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><i class="fas fa-users me-2 text-primary"></i>User Registrations</h5>
                        <p class="text-muted">Monthly user registration trends</p>
                        <div class="mt-3">
                            <strong>Total:</strong> 0 users
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><i class="fas fa-eye me-2 text-success"></i>Property Views</h5>
                        <p class="text-muted">Property listing view statistics</p>
                        <div class="mt-3">
                            <strong>Total:</strong> <?php echo array_sum($reports['property_views']['data'] ?? [0]); ?> views
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><i class="fas fa-rupee-sign me-2 text-info"></i>Revenue</h5>
                        <p class="text-muted">Monthly revenue analytics</p>
                        <div class="mt-3">
                            <strong>Total:</strong> ₹<?php echo number_format(array_sum($reports['revenue']['data'] ?? [0])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Analytics Overview</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-center">User Growth</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Users</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $user_reg_labels = [];
                                            $user_reg_data = [];
                                            if (isset($reports['user_registrations']) && isset($reports['user_registrations']['labels'])) {
                                                $user_reg_labels = $reports['user_registrations']['labels'];
                                            }
                                            if (isset($reports['user_registrations']) && isset($reports['user_registrations']['data'])) {
                                                $user_reg_data = $reports['user_registrations']['data'];
                                            }
                                            if (empty($user_reg_labels)):
                                            ?>
                                                <tr>
                                                    <td colspan="2" class="text-center py-5">
                                                        <i class="fas fa-users fa-3x text-muted mb-3 style-82835"></i>
                                                        <h5 class="text-muted">No user registration data</h5>
                                                        <p class="text-muted mb-3">Registration trends will appear here once users start signing up.</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                            <?php foreach ($user_reg_labels as $i => $label):
                                            ?>
                                                <tr>
                                                    <td><?php echo e($label); ?></td>
                                                    <td><?php echo $user_reg_data[$i] ?? 0; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-center">Property Views</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $labels = $reports['property_views']['labels'] ?? [];
                                            $data = $reports['property_views']['data'] ?? [];
                                            if (empty($labels)):
                                            ?>
                                                <tr>
                                                    <td colspan="2" class="text-center py-5">
                                                        <i class="fas fa-eye fa-3x text-muted mb-3 style-82835"></i>
                                                        <h5 class="text-muted">No property view data</h5>
                                                        <p class="text-muted mb-3">View statistics will appear once properties start receiving traffic.</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                            <?php foreach ($labels as $i => $label):
                                            ?>
                                                <tr>
                                                    <td><?php echo e($label); ?></td>
                                                    <td><?php echo $data[$i] ?? 0; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-center">Revenue</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="2" class="text-center py-5">
                                                    <i class="fas fa-rupee-sign fa-3x text-muted mb-3 style-82835"></i>
                                                    <h5 class="text-muted">No revenue data</h5>
                                                    <p class="text-muted mb-3">Revenue analytics will appear once transactions are recorded.</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
