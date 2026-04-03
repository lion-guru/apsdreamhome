<?php
// Standalone Admin Dashboard
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$admin_name = $admin_name ?? 'Admin';
$admin_role = $admin_role ?? 'admin';
$stats = $stats ?? [];
$recentLeads = $recentLeads ?? [];
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
$base = BASE_URL;
$uri = $_SERVER['REQUEST_URI'] ?? '/admin/dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | APS Dream Home Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f1f5f9;overflow-x:hidden}
        .sidebar{position:fixed;top:0;left:0;width:260px;height:100vh;background:#1e1b4b;z-index:1000;overflow-y:auto;transition:transform .3s}
        .sidebar::-webkit-scrollbar{width:4px}
        .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
        .sidebar-header{padding:20px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-logo{color:#fff;font-size:1.1rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:10px}
        .sidebar-logo i{font-size:1.3rem;color:#a5b4fc}
        .sidebar-sub{color:rgba(255,255,255,.5);font-size:.7rem;margin-top:4px}
        .sidebar-sec{padding:15px 15px 5px;font-size:.65rem;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:600;letter-spacing:.05em}
        .sidebar-menu{list-style:none;padding:0 10px;margin:0}
        .sidebar-item{margin-bottom:2px}
        .sidebar-link{display:flex;align-items:center;padding:9px 12px;color:#c7d2fe;text-decoration:none;border-radius:8px;font-size:.85rem;font-weight:500;transition:all .2s}
        .sidebar-link:hover{background:#312e81;color:#fff}
        .sidebar-link.active{background:#4f46e5;color:#fff}
        .sidebar-link i{width:20px;margin-right:10px;font-size:.95rem;color:#a5b4fc;text-align:center}
        .sidebar-link.active i,.sidebar-link:hover i{color:#fff}
        .main-content{margin-left:260px;min-height:100vh}
        .top-nav{background:#fff;height:60px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:100}
        .nav-left{display:flex;align-items:center;gap:15px}
        .toggle-btn{background:none;border:none;font-size:1.2rem;color:#64748b;cursor:pointer;display:none}
        .nav-right{display:flex;align-items:center;gap:15px}
        .nav-icon{position:relative;background:none;border:none;font-size:1.1rem;color:#64748b;cursor:pointer;padding:8px;border-radius:8px}
        .nav-icon .badge{position:absolute;top:2px;right:2px;font-size:.55rem;padding:2px 5px;border-radius:10px;background:#ef4444;color:#fff}
        .user-box{display:flex;align-items:center;gap:10px;padding:5px 10px;border-radius:8px}
        .user-av{width:36px;height:36px;border-radius:50%;background:#4f46e5;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.85rem}
        .page-content{padding:24px}
        .stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;display:flex;align-items:flex-start;gap:15px}
        .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
        .stat-icon.p{background:#eef2ff;color:#4f46e5}
        .stat-icon.s{background:#ecfdf5;color:#10b981}
        .stat-icon.w{background:#fffbeb;color:#f59e0b}
        .stat-icon.d{background:#fef2f2;color:#ef4444}
        .stat-icon.i{background:#f0fdfa;color:#14b8a6}
        .stat-icon.u{background:#faf5ff;color:#a855f7}
        .stat-label{font-size:.72rem;color:#64748b;text-transform:uppercase;font-weight:500;margin-bottom:4px}
        .stat-value{font-size:1.5rem;font-weight:700;color:#1e293b}
        .stat-change{font-size:.72rem;margin-top:4px;color:#10b981}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .btn-primary{background:#4f46e5;border-color:#4f46e5}
        .btn-primary:hover{background:#4338ca;border-color:#4338ca}
        @media(max-width:991px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.show{transform:translateX(0)}
            .main-content{margin-left:0}
            .toggle-btn{display:block}
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-logo"><i class="fas fa-home"></i><span>APS Dream Home</span></a>
            <div class="sidebar-sub"><?php echo ucfirst(str_replace('_',' ',$admin_role)); ?> Panel</div>
        </div>
        <div class="sidebar-sec">Main</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-link active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        </ul>
        <div class="sidebar-sec">CRM & Leads</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/leads" class="sidebar-link"><i class="fas fa-bullseye"></i> Leads</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/campaigns" class="sidebar-link"><i class="fas fa-bullhorn"></i> Campaigns</a></li>
        </ul>
        <div class="sidebar-sec">Properties</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/properties" class="sidebar-link"><i class="fas fa-building"></i> All Properties</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/plots" class="sidebar-link"><i class="fas fa-map"></i> Plots / Land</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/sites" class="sidebar-link"><i class="fas fa-map-marker-alt"></i> Sites</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/bookings" class="sidebar-link"><i class="fas fa-file-contract"></i> Bookings</a></li>
        </ul>
        <div class="sidebar-sec">MLM Network</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/team/genealogy" class="sidebar-link"><i class="fas fa-sitemap"></i> Network Tree</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/associate/dashboard" class="sidebar-link"><i class="fas fa-handshake"></i> Associates</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/agent/login" class="sidebar-link"><i class="fas fa-user-tie"></i> Agents</a></li>
        </ul>
        <div class="sidebar-sec">Content</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/gallery" class="sidebar-link"><i class="fas fa-images"></i> Gallery</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/legal-pages" class="sidebar-link"><i class="fas fa-file-alt"></i> Legal Pages</a></li>
        </ul>
        <div class="sidebar-sec">Team</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/users" class="sidebar-link"><i class="fas fa-users"></i> Users</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/employee/dashboard" class="sidebar-link"><i class="fas fa-user-tie"></i> Employees</a></li>
        </ul>
        <div class="sidebar-sec">Financial</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/bookings" class="sidebar-link"><i class="fas fa-rupee-sign"></i> Payments</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/settings" class="sidebar-link"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
        <div class="sidebar-sec">Account</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="<?php echo $base; ?>/" target="_blank" class="sidebar-link"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            <li class="sidebar-item"><a href="<?php echo $base; ?>/admin/logout" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
                <div style="font-size:.85rem;color:#64748b">Dashboard / <strong style="color:#1e293b">Overview</strong></div>
            </div>
            <div class="nav-right">
                <button class="nav-icon"><i class="fas fa-bell"></i><span class="badge">3</span></button>
                <button class="nav-icon"><i class="fas fa-envelope"></i><span class="badge">5</span></button>
                <div class="user-box">
                    <div class="user-av"><?php echo strtoupper(substr($admin_name,0,1)); ?></div>
                    <div><div style="font-size:.85rem;font-weight:600;color:#1e293b"><?php echo htmlspecialchars($admin_name); ?></div><div style="font-size:.7rem;color:#64748b"><?php echo ucfirst(str_replace('_',' ',$admin_role)); ?></div></div>
                </div>
            </div>
        </nav>

        <div class="page-content">
            <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div><h1 class="h3 mb-1 fw-bold">Dashboard</h1><p class="text-muted mb-0">Welcome back! Here's your system overview.</p></div>
                <button class="btn btn-primary" onclick="location.reload()"><i class="fas fa-sync-alt me-2"></i>Refresh</button>
            </div>

            <!-- Stats Row 1 -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon p"><i class="fas fa-users"></i></div><div><div class="stat-label">Total Users</div><div class="stat-value"><?php echo number_format($stats['total_users']); ?></div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon s"><i class="fas fa-building"></i></div><div><div class="stat-label">Properties</div><div class="stat-value"><?php echo number_format($stats['total_properties']); ?></div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon w"><i class="fas fa-bullseye"></i></div><div><div class="stat-label">Total Leads</div><div class="stat-value"><?php echo number_format($stats['total_leads']); ?></div><div class="stat-change"><i class="fas fa-arrow-up"></i> <?php echo $stats['new_leads_today']; ?> today</div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon i"><i class="fas fa-network-wired"></i></div><div><div class="stat-label">Associates</div><div class="stat-value"><?php echo number_format($stats['total_associates']); ?></div></div></div></div>
            </div>

            <!-- Stats Row 2 -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon u"><i class="fas fa-rupee-sign"></i></div><div><div class="stat-label">Revenue (30 Days)</div><div class="stat-value">&#8377;<?php echo number_format($stats['revenue_month'],2); ?></div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon p"><i class="fas fa-user-tie"></i></div><div><div class="stat-label">Employees</div><div class="stat-value"><?php echo number_format($stats['total_employees']??0); ?></div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon w"><i class="fas fa-file-contract"></i></div><div><div class="stat-label">Pending Bookings</div><div class="stat-value"><?php echo number_format($stats['pending_bookings']); ?></div></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon s"><i class="fas fa-check-circle"></i></div><div><div class="stat-label">System Status</div><div class="stat-value text-success">Online</div></div></div></div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                    <div class="row g-3">
                        <div class="col-md-3"><a href="<?php echo $base; ?>/admin/leads/create" class="btn btn-outline-primary w-100 py-3"><i class="fas fa-user-plus mb-2" style="font-size:1.5rem;display:block"></i>Add New Lead</a></div>
                        <div class="col-md-3"><a href="<?php echo $base; ?>/admin/properties/create" class="btn btn-outline-success w-100 py-3"><i class="fas fa-plus mb-2" style="font-size:1.5rem;display:block"></i>Add Property</a></div>
                        <div class="col-md-3"><a href="<?php echo $base; ?>/admin/bookings/create" class="btn btn-outline-warning w-100 py-3"><i class="fas fa-file-contract mb-2" style="font-size:1.5rem;display:block"></i>New Booking</a></div>
                        <div class="col-md-3"><a href="<?php echo $base; ?>/admin/gallery/create" class="btn btn-outline-info w-100 py-3"><i class="fas fa-image mb-2" style="font-size:1.5rem;display:block"></i>Upload Photo</a></div>
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
                                    <div><div class="fw-semibold"><?php echo htmlspecialchars($lead['name']??'Unknown'); ?></div><small class="text-muted"><?php echo htmlspecialchars($lead['email']??''); ?></small></div>
                                    <span class="badge bg-<?php echo ($lead['status']??'new')==='converted'?'success':(($lead['status']??'new')==='contacted'?'warning':'info'); ?>"><?php echo ucfirst($lead['status']??'new'); ?></span>
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
                            <div class="mb-3"><div class="d-flex justify-content-between mb-1"><span>Database</span><span class="fw-semibold text-success">Connected</span></div><div class="progress" style="height:6px"><div class="progress-bar bg-success" style="width:100%"></div></div></div>
                            <div class="mb-3"><div class="d-flex justify-content-between mb-1"><span>Active Users</span><span class="fw-semibold"><?php echo number_format($stats['total_users']); ?></span></div><div class="progress" style="height:6px"><div class="progress-bar bg-primary" style="width:<?php echo min(100,$stats['total_users']); ?>%"></div></div></div>
                            <div class="mb-3"><div class="d-flex justify-content-between mb-1"><span>Properties Listed</span><span class="fw-semibold"><?php echo number_format($stats['total_properties']); ?></span></div><div class="progress" style="height:6px"><div class="progress-bar bg-info" style="width:<?php echo min(100,$stats['total_properties']*10); ?>%"></div></div></div>
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
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>setTimeout(function(){document.querySelectorAll('.alert').forEach(a=>{try{new bootstrap.Alert(a).close()}catch(e){}})},5000);</script>
</body>
</html>
