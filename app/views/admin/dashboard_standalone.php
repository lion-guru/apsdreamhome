<?php
// Standalone Admin Dashboard - Now uses Unified Layout with RBAC Sidebar
require __DIR__ . '/layouts/unified_start.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">Dashboard</h1>
        <p class="text-muted mb-0">Welcome back! Here's your system overview.</p>
    </div>
    <button class="btn btn-primary" onclick="location.reload()">
        <i class="fas fa-sync-alt me-2"></i>Refresh
    </button>
</div>

<!-- Stats Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon p"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon s"><i class="fas fa-building"></i></div>
            <div>
                <div class="stat-label">Properties</div>
                <div class="stat-value"><?php echo number_format($stats['total_properties'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon w"><i class="fas fa-bullseye"></i></div>
            <div>
                <div class="stat-label">Total Leads</div>
                <div class="stat-value"><?php echo number_format($stats['total_leads'] ?? 0); ?></div>
                <div class="stat-change"><i class="fas fa-arrow-up"></i> <?php echo $stats['new_leads_today'] ?? 0; ?> today</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon i"><i class="fas fa-network-wired"></i></div>
            <div>
                <div class="stat-label">Associates</div>
                <div class="stat-value"><?php echo number_format($stats['total_associates'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon u"><i class="fas fa-rupee-sign"></i></div>
            <div>
                <div class="stat-label">Revenue (30 Days)</div>
                <div class="stat-value">&#8377;<?php echo number_format($stats['revenue_month'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon p"><i class="fas fa-user-tie"></i></div>
            <div>
                <div class="stat-label">Employees</div>
                <div class="stat-value"><?php echo number_format($stats['total_employees'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon w"><i class="fas fa-file-contract"></i></div>
            <div>
                <div class="stat-label">Pending Bookings</div>
                <div class="stat-value"><?php echo number_format($stats['pending_bookings'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon s"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-label">System Status</div>
                <div class="stat-value text-success">Online</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-body">
        <h6 class="mb-3"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="<?php echo $base; ?>/admin/leads/create" class="btn btn-outline-primary w-100 py-3">
                    <i class="fas fa-user-plus mb-2" style="font-size:1.5rem;display:block"></i>Add New Lead
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>/admin/properties/create" class="btn btn-outline-success w-100 py-3">
                    <i class="fas fa-plus mb-2" style="font-size:1.5rem;display:block"></i>Add Property
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>/admin/bookings/create" class="btn btn-outline-warning w-100 py-3">
                    <i class="fas fa-file-contract mb-2" style="font-size:1.5rem;display:block"></i>New Booking
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo $base; ?>/admin/gallery/create" class="btn btn-outline-info w-100 py-3">
                    <i class="fas fa-image mb-2" style="font-size:1.5rem;display:block"></i>Upload Photo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Leads + System -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fas fa-user-clock me-2"></i>Recent Leads</h6>
                    <a href="<?php echo $base; ?>/admin/leads" class="btn btn-sm btn-primary">View All</a>
                </div>
                <?php if(!empty($recentLeads)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach($recentLeads as $lead): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($lead['name']??'Unknown'); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($lead['email']??''); ?></small>
                        </div>
                        <span class="badge bg-<?php echo ($lead['status']??'new')==='converted'?'success':(($lead['status']??'new')==='contacted'?'warning':'info'); ?>">
                            <?php echo ucfirst($lead['status']??'new'); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-3">No leads yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-server me-2"></i>System Overview</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><span>Database</span><span class="fw-semibold text-success">Connected</span></div>
                    <div class="progress" style="height:6px"><div class="progress-bar bg-success" style="width:100%"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><span>Active Users</span><span class="fw-semibold"><?php echo number_format($stats['total_users'] ?? 0); ?></span></div>
                    <div class="progress" style="height:6px"><div class="progress-bar bg-primary" style="width:<?php echo min(100,$stats['total_users'] ?? 0); ?>%"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><span>Properties Listed</span><span class="fw-semibold"><?php echo number_format($stats['total_properties'] ?? 0); ?></span></div>
                    <div class="progress" style="height:6px"><div class="progress-bar bg-info" style="width:<?php echo min(100,($stats['total_properties'] ?? 0)*10); ?>%"></div></div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-4"><div class="h5 mb-0 text-primary"><?php echo number_format($stats['total_properties'] ?? 0); ?></div><small class="text-muted">Properties</small></div>
                    <div class="col-4"><div class="h5 mb-0 text-success"><?php echo number_format($stats['total_leads'] ?? 0); ?></div><small class="text-muted">Leads</small></div>
                    <div class="col-4"><div class="h5 mb-0 text-warning"><?php echo number_format($stats['pending_bookings'] ?? 0); ?></div><small class="text-muted">Pending</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/unified_end.php'; ?>
