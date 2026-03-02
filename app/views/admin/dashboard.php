<?php
$page_title = $data['title'] ?? 'Admin Dashboard - APS Dream Home';
$page_description = $data['description'] ?? 'Admin dashboard for managing properties, users, and system settings';
?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Properties</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['stats']['total_properties'] ?? 150; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['stats']['total_users'] ?? 2500; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Leads</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['stats']['total_leads'] ?? 850; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-phone fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data['stats']['total_revenue'] ?? '₹2.5Cr'; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <!-- Recent Properties -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Properties</h6>
                <a href="<?php echo BASE_URL; ?>/admin/properties" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($data['recent_properties'])): ?>
                    <?php foreach ($data['recent_properties'] as $property): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($property->title); ?></h6>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($property->location); ?>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-rupee-sign me-1"></i><?php echo number_format($property->price); ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php echo $property->status === 'Active' ? 'success' : 'warning'; ?>">
                                        <?php echo htmlspecialchars($property->status); ?>
                                    </span>
                                    <p class="text-muted small mb-0"><?php echo $property->created_at; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No recent properties found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                <a href="<?php echo BASE_URL; ?>/admin/users" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($data['recent_users'])): ?>
                    <?php foreach ($data['recent_users'] as $user): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($user->name); ?></h6>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($user->email); ?>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($user->phone); ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success">Active</span>
                                    <p class="text-muted small mb-0"><?php echo $user->registered_at; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No recent users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Leads -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Leads</h6>
                <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Property Interest</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Date</th>
                                <th>Assigned To</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['recent_leads'])): ?>
                                <?php foreach ($data['recent_leads'] as $lead): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lead->name); ?></td>
                                        <td><?php echo htmlspecialchars($lead->email); ?></td>
                                        <td><?php echo htmlspecialchars($lead->phone); ?></td>
                                        <td><?php echo htmlspecialchars($lead->property_interest); ?></td>
                                        <td><?php echo htmlspecialchars($lead->budget); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($lead->status) {
                                                    'New' => 'primary',
                                                    'Contacted' => 'info',
                                                    'Qualified' => 'success',
                                                    default => 'secondary'
                                                }; 
                                            ?>">
                                                <?php echo htmlspecialchars($lead->status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($lead->source); ?></td>
                                        <td><?php echo $lead->created_at; ?></td>
                                        <td><?php echo htmlspecialchars($lead->assigned_to); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No recent leads found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/properties/create" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Property
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/users/create" class="btn btn-success w-100">
                            <i class="fas fa-user-plus me-2"></i>Add User
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/reports" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/settings" class="btn btn-warning w-100">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>