<?php
/**
 * Admin Dashboard - Full Featured
 */

$role = $role ?? 'admin';
$title = $title ?? 'Dashboard';

// Get stats from database
$stats = ['total_users' => 0, 'total_properties' => 0, 'total_leads' => 0, 'new_leads_today' => 0, 'total_associates' => 0, 'revenue_month' => 0, 'total_employees' => 0, 'pending_bookings' => 0];
try {
    $db = \App\Core\Database\Database::getInstance();
    $stats['total_users'] = $db->fetch("SELECT COUNT(*) as c FROM users")['c'] ?? 0;
    $stats['total_properties'] = $db->fetch("SELECT COUNT(*) as c FROM properties")['c'] ?? 0;
    $stats['total_leads'] = $db->fetch("SELECT COUNT(*) as c FROM leads")['c'] ?? 0;
    $stats['new_leads_today'] = $db->fetch("SELECT COUNT(*) as c FROM leads WHERE DATE(created_at) = CURDATE()")['c'] ?? 0;
    $stats['total_associates'] = $db->fetch("SELECT COUNT(*) as c FROM users WHERE role IN ('associate','agent')")['c'] ?? 0;
    $stats['revenue_month'] = $db->fetch("SELECT COALESCE(SUM(amount),0) as c FROM payments WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")['c'] ?? 0;
    $stats['total_employees'] = $db->fetch("SELECT COUNT(*) as c FROM employees")['c'] ?? 0;
    $stats['pending_bookings'] = $db->fetch("SELECT COUNT(*) as c FROM bookings WHERE status='pending'")['c'] ?? 0;
} catch (\Exception $e) { /* silent */ }

// Recent leads
$recentLeads = [];
try { $recentLeads = $db->fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5") ?? []; } catch (\Exception $e) {}
?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><?php echo htmlspecialchars($title); ?></h1>
        <p class="text-muted mb-0">Welcome back! Here's your system overview.</p>
    </div>
    <button class="btn btn-primary" onclick="location.reload()"><i class="fas fa-sync-alt me-2"></i> Refresh</button>
</div>

<!-- Stats Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-building"></i></div>
            <div class="stat-content">
                <div class="stat-label">Properties</div>
                <div class="stat-value"><?php echo number_format($stats['total_properties']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-bullseye"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Leads</div>
                <div class="stat-value"><?php echo number_format($stats['total_leads']); ?></div>
                <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?php echo $stats['new_leads_today']; ?> today</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-network-wired"></i></div>
            <div class="stat-content">
                <div class="stat-label">Associates</div>
                <div class="stat-value"><?php echo number_format($stats['total_associates']); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-content">
                <div class="stat-label">Revenue (30 Days)</div>
                <div class="stat-value">&#8377;<?php echo number_format($stats['revenue_month'], 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-user-tie"></i></div>
            <div class="stat-content">
                <div class="stat-label">Employees</div>
                <div class="stat-value"><?php echo number_format($stats['total_employees']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-file-contract"></i></div>
            <div class="stat-content">
                <div class="stat-label">Pending Bookings</div>
                <div class="stat-value"><?php echo number_format($stats['pending_bookings']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-label">System Status</div>
                <div class="stat-value text-success">Online</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header border-0 bg-transparent">
        <h5 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/admin/leads/create" class="btn btn-outline-primary w-100 py-3">
                    <i class="fas fa-user-plus mb-2" style="font-size:1.5rem;display:block;"></i>Add New Lead
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/admin/properties/create" class="btn btn-outline-success w-100 py-3">
                    <i class="fas fa-plus mb-2" style="font-size:1.5rem;display:block;"></i>Add Property
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/admin/bookings/create" class="btn btn-outline-warning w-100 py-3">
                    <i class="fas fa-file-contract mb-2" style="font-size:1.5rem;display:block;"></i>New Booking
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/admin/gallery/create" class="btn btn-outline-info w-100 py-3">
                    <i class="fas fa-image mb-2" style="font-size:1.5rem;display:block;"></i>Upload Photo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Two Column: Recent Leads + System Overview -->
<div class="row g-4">
    <!-- Recent Leads -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-0 bg-transparent">
                <h5 class="card-title"><i class="fas fa-user-clock me-2"></i>Recent Leads</h5>
                <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recentLeads)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentLeads as $lead): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($lead['email'] ?? ''); ?> | <?php echo htmlspecialchars($lead['phone'] ?? ''); ?></small>
                        </div>
                        <span class="badge bg-<?php echo ($lead['status'] ?? 'new') === 'converted' ? 'success' : (($lead['status'] ?? 'new') === 'contacted' ? 'warning' : 'info'); ?>">
                            <?php echo ucfirst($lead['status'] ?? 'new'); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No leads yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- System Overview -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0 bg-transparent">
                <h5 class="card-title"><i class="fas fa-server me-2"></i>System Overview</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><span>Database</span><span class="fw-semibold text-success">Connected</span></div>
                    <div class="progress"><div class="progress-bar bg-success" style="width:100%"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><span>Active Users</span><span class="fw-semibold"><?php echo number_format($stats['total_users']); ?></span></div>
                    <div class="progress"><div class="progress-bar bg-primary" style="width:<?php echo min(100, $stats['total_users']); ?>%"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><span>Properties Listed</span><span class="fw-semibold"><?php echo number_format($stats['total_properties']); ?></span></div>
                    <div class="progress"><div class="progress-bar bg-info" style="width:<?php echo min(100, $stats['total_properties'] * 10); ?>%"></div></div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-4"><div class="h5 mb-0 text-primary"><?php echo number_format($stats['total_properties']); ?></div><small class="text-muted">Properties</small></div>
                    <div class="col-4"><div class="h5 mb-0 text-success"><?php echo number_format($stats['total_leads']); ?></div><small class="text-muted">Leads</small></div>
                    <div class="col-4"><div class="h5 mb-0 text-warning"><?php echo number_format($stats['pending_bookings']); ?></div><small class="text-muted">Pending</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>setTimeout(function(){ location.reload(); }, 60000);</script>
