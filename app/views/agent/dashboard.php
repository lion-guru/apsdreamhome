<?php
// Agent Dashboard - Supports MLM Company Agents & Freelancer/Independent Agents
$page_title = $page_title ?? 'Agent Dashboard - APS Dream Home';
$page_description = $page_description ?? 'Manage your real estate business';
$agent_type = $agent_type ?? ($_SESSION['agent_type'] ?? 'mlm_company');
$agent_stats = $agent_stats ?? [];
$recent_leads = $recent_leads ?? [];
$assigned_properties = $assigned_properties ?? [];
$my_properties = $my_properties ?? [];
$commission_summary = $commission_summary ?? [];
$network_stats = $network_stats ?? [];
$site_visits = $site_visits ?? [];
$performance = $performance ?? [];
$gamify = $gamify ?? [];

$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$agent_name = $_SESSION['user_name'] ?? 'Agent';
$active_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="icon" type="image/png" href="<?= $base ?>/app/views/admin/assets/img/favicon.png">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f8fafc;overflow-x:hidden}
        
        /* Sidebar Styles */
        .sidebar{position:fixed;top:0;left:0;width:260px;height:100vh;background:linear-gradient(180deg, #15803d 0%, #22c55e 100%);z-index:1000;overflow-y:auto;transition:transform .3s;box-shadow:4px 0 15px rgba(0,0,0,0.1)}
        .sidebar::-webkit-scrollbar{width:4px}
        .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.3);border-radius:2px}
        .sidebar-header{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-logo{color:#fff;font-size:1.1rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:10px}
        .sidebar-logo i{font-size:1.4rem;color:#dcfce7}
        .sidebar-sub{color:rgba(255,255,255,.7);font-size:.75rem;margin-top:6px}
        .sidebar-menu{list-style:none;padding:15px 10px;margin:0}
        .sidebar-item{margin-bottom:2px}
        .sidebar-link{display:flex;align-items:center;padding:12px 14px;color:#dcfce7;text-decoration:none;border-radius:10px;font-size:.9rem;font-weight:500;transition:all .2s}
        .sidebar-link:hover{background:rgba(255,255,255,.15);color:#fff}
        .sidebar-link.active{background:#fff;color:#15803d}
        .sidebar-link i{width:24px;margin-right:12px;font-size:1rem;color:#dcfce7;text-align:center}
        .sidebar-link.active i,.sidebar-link:hover i{color:#15803d}
        
        .main-content{margin-left:260px;min-height:100vh;transition:margin-left .3s}
        .top-nav{background:#fff;height:64px;padding:0 28px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:100;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .nav-left{display:flex;align-items:center;gap:16px}
        .toggle-btn{background:none;border:none;font-size:1.3rem;color:#64748b;cursor:pointer;display:none;padding:8px;border-radius:8px}
        .toggle-btn:hover{background:#f1f5f9}
        .nav-right{display:flex;align-items:center;gap:16px}
        .nav-icon{position:relative;background:none;border:none;font-size:1.1rem;color:#64748b;cursor:pointer;padding:8px;border-radius:8px}
        .nav-icon:hover{background:#f1f5f9}
        .user-box{display:flex;align-items:center;gap:12px;padding:8px 14px;border-radius:10px;cursor:pointer;transition:background .2s}
        .user-box:hover{background:#f1f5f9}
        .user-av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg, #15803d, #22c55e);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.9rem}
        .page-content{padding:28px}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .btn-primary{background:#15803d;border-color:#15803d}
        .btn-primary:hover{background:#14532d;border-color:#14532d}
        .btn-outline-primary{border-color:#15803d;color:#15803d}
        .btn-outline-primary:hover{background:#15803d;color:#fff}
        
        /* Stat Cards */
        .stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;transition:all .2s}
        .stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem}
        .stat-icon.green{background:#dcfce7;color:#15803d}
        .stat-icon.blue{background:#dbeafe;color:#2563eb}
        .stat-icon.orange{background:#ffedd5;color:#ea580c}
        .stat-icon.purple{background:#f3e8ff;color:#9333ea}
        .stat-icon.red{background:#fee2e2;color:#dc2626}
        .stat-icon.gold{background:#fef3c7;color:#d97706}
        
        /* Agent Type Badge */
        .agent-type-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:.75rem;font-weight:600}
        .agent-type-badge.mlm{background:#dcfce7;color:#15803d}
        .agent-type-badge.freelancer{background:#dbeafe;color:#2563eb}
        .agent-type-badge.independent{background:#fef3c7;color:#d97706}
        
        /* Commission breakdown */
        .commission-breakdown{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:16px}
        .commission-item{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px}
        .commission-item.primary{border-color:#15803d;background:#f0fdf4}
        .commission-item.secondary{border-color:#2563eb;background:#eff6ff}
        
        /* Network stats */
        .network-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
        .network-stat{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-align:center}
        .network-stat.highlight{border-color:#15803d;background:#f0fdf4}
        
        /* Lead/Property lists */
        .list-group-item{border:none;border-bottom:1px solid #f1f5f9;padding:12px 16px;transition:background .2s}
        .list-group-item:last-child{border-bottom:none}
        .list-group-item:hover{background:#f8fafc}
        .lead-status{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;font-size:.7rem;font-weight:600}
        .lead-status.new{background:#dbeafe;color:#2563eb}
        .lead-status.contacted{background:#ffedd5;color:#ea580c}
        .lead-status.qualified{background:#dcfce7;color:#15803d}
        .lead-status.proposal{background:#f3e8ff;color:#9333ea}
        .lead-status.negotiation{background:#fef3c7;color:#d97706}
        .lead-status.converted{background:#dcfce7;color:#15803d}
        .lead-status.lost{background:#fee2e2;color:#dc2626}
        
        .property-status{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;font-size:.7rem;font-weight:600}
        .property-status.available{background:#dcfce7;color:#15803d}
        .property-status.booked{background:#fef3c7;color:#d97706}
        .property-status.sold{background:#dbeafe;color:#2563eb}
        .property-status.reserved{background:#f3e8ff;color:#9333ea}
        
        /* Performance metrics */
        .perf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}
        .perf-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;text-align:center}
        .perf-card.highlight{border-color:#15803d;background:#f0fdf4}
        
        /* Site visits */
        .visit-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:12px;transition:all .2s}
        .visit-card:hover{transform:translateX(4px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .visit-time{background:#15803d;color:#fff;padding:4px 10px;border-radius:6px;font-size:.75rem;font-weight:600}
        .visit-date{color:#64748b;font-size:.8rem}
        
        @media(max-width:991px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.show{transform:translateX(0)}
            .main-content{margin-left:0}
            .toggle-btn{display:block}
        }
    </style>
</head>
<body>
    <!-- Agent Sidebar -->
    <aside class="sidebar" id="sidebarMenu">
        <div class="sidebar-header">
            <a href="<?php echo $base; ?>/agent/dashboard" class="sidebar-logo">
                <i class="fas fa-home"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-sub">Agent Portal
                <span class="agent-type-badge <?php echo $agent_type; ?>">
                    <?php 
                        echo $agent_type === 'freelancer' ? 'Freelancer' : 
                            ($agent_type === 'independent' ? 'Independent' : 'MLM Company');
                    ?>
                </span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/dashboard" class="sidebar-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/leads" class="sidebar-link">
                    <i class="fas fa-users"></i> My Leads
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/properties" class="sidebar-link">
                    <i class="fas fa-building"></i> My Properties
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/commissions" class="sidebar-link">
                    <i class="fas fa-rupee-sign"></i> Commissions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/wallet" class="sidebar-link">
                    <i class="fas fa-wallet"></i> Wallet
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/deals" class="sidebar-link">
                    <i class="fas fa-handshake"></i> My Deals
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/properties" class="sidebar-link">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/profile" class="sidebar-link">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/agent/logout" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="document.getElementById('sidebarMenu').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" class="style-93188">
                        <li class="breadcrumb-item"><a href="<?php echo $base; ?>/agent/dashboard">Agent</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div class="nav-right">
                <button class="nav-icon" title="Notifications" onclick="window.location.href='<?php echo $base; ?>/user/notifications'"><i class="fas fa-bell"></i></button>
                <button class="nav-icon" title="Messages" onclick="window.location.href='<?php echo $base; ?>/user/notifications'"><i class="fas fa-envelope"></i></button>
                <div class="dropdown">
                    <div class="user-box" data-bs-toggle="dropdown">
                        <div class="user-av"><?php echo strtoupper(substr($agent_name,0,1)); ?></div>
                        <div>
                            <div class="style-46756"><?php echo htmlspecialchars($agent_name); ?></div>
                            <div class="style-46475"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                        </div>
                        <i class="fas fa-chevron-down ms-2" class="style-46475"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/agent/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/agent/profile"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo $base; ?>/agent/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="page-content">
            
            <?php if (!empty($commission_summary['missed_commissions'])): ?>
            <!-- FOMO Strategy: Missed Commissions Banner -->
            <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 d-flex align-items-center" role="alert" class="style-38485">
                <div class="stat-icon red me-3 flex-shrink-0" class="style-83006">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="alert-heading fw-bold mb-1" class="style-8077">You Missed â‚¹<?php echo $commission_summary['total_missed']; ?> in Commissions!</h5>
                    <p class="mb-0 text-danger">Your account is currently inactive. You missed out on commissions this month because of it. 
                        <a href="<?php echo $base; ?>/agent/renew" class="fw-bold text-decoration-underline" class="style-29076">Activate your account now</a> to prevent losing more money!
                    </p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['success'] ?? ''); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error'] ?? ''); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Header with Stats -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($page_title); ?></h2>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($page_description); ?></p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo $base; ?>/agent/leads" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Lead
                    </a>
                    <a href="<?php echo $base; ?>/agent/deals" class="btn btn-outline-primary">
                        <i class="fas fa-handshake me-1"></i> My Deals
                    </a>
                </div>
            </div>

            <!-- FOMO Strategy: Missed Commissions Banner -->
            <?php if (!empty($commission_summary['missed_commissions']) && count($commission_summary['missed_commissions']) > 0): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-4 d-flex align-items-center" class="style-85605">
                <div class="me-3 fs-3 text-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="alert-heading text-warning mb-1" class="style-58498">Action Required: Missed Commissions!</h5>
                    <p class="mb-0 text-dark">
                        You have missed out on <strong>â‚¹<?php echo $commission_summary['total_missed'] ?? '0.00'; ?></strong> in network commissions this month because your ID is currently inactive.
                        <br>
                        <small>Activate your ID or renew your subscription to start earning from your team's sales again.</small>
                    </p>
                </div>
                <div class="ms-auto ps-3 border-start border-warning d-none d-md-block">
                    <a href="<?php echo $base; ?>/agent/subscription" class="btn btn-warning fw-bold text-dark">Activate Now</a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Main Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small">Total Leads</p>
                                <h4 class="mb-0"><?php echo $agent_stats['total_leads'] ?? 0; ?></h4>
                            </div>
                            <div class="stat-icon green"><i class="fas fa-bullseye"></i></div>
                        </div>
                        <small class="text-success">
                            <i class="fas fa-check me-1"></i><?php echo $agent_stats['converted_leads'] ?? 0; ?> converted 
                            (<?php echo $agent_stats['conversion_rate'] ?? '0%'; ?>)
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small"><?php echo $agent_type === 'freelancer' ? 'My Listings' : 'Properties'; ?></p>
                                <h4 class="mb-0"><?php echo $agent_stats['total_properties'] ?? 0; ?></h4>
                            </div>
                            <div class="stat-icon blue"><i class="fas fa-building"></i></div>
                        </div>
                        <small class="text-info">
                            <i class="fas fa-check-circle me-1"></i><?php echo $agent_stats['sold_properties'] ?? 0; ?> sold
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small">Total Commission</p>
                                <h4 class="mb-0 text-warning">â‚¹<?php echo $commission_summary['total_commission'] ?? ($agent_stats['total_commission'] ?? 0); ?></h4>
                            </div>
                            <div class="stat-icon gold"><i class="fas fa-rupee-sign"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small">
                                    <?php 
                                        if ($agent_type === 'freelancer' || $agent_type === 'independent') {
                                            echo 'This Month Sales';
                                        } else {
                                            echo 'Direct Team';
                                        }
                                    ?>
                                </p>
                                <h4 class="mb-0">
                                    <?php 
                                        if ($agent_type === 'freelancer' || $agent_type === 'independent') {
                                            echo $performance['this_month']['count'] ?? 0;
                                        } else {
                                            echo $network_stats['direct_count'] ?? 0;
                                        }
                                    ?>
                                </h4>
                            </div>
                            <div class="stat-icon <?php echo ($agent_type === 'freelancer' || $agent_type === 'independent') ? 'purple' : 'green'; ?>">
                                <i class="fas <?php echo ($agent_type === 'freelancer' || $agent_type === 'independent') ? 'fa-chart-line' : 'fa-users'; ?>"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agent Type Specific Sections -->
            <?php if ($agent_type === 'mlm_company'): ?>
            <!-- MLM Company Agent Sections -->
            <div class="row g-3 mb-4">
                <!-- Network/Team Stats -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-sitemap text-success me-2"></i>Network Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="network-stats">
                                <div class="network-stat highlight">
                                    <h4 class="mb-1 text-success"><?php echo $network_stats['direct_count'] ?? 0; ?></h4>
                                    <p class="text-muted mb-0 small">Direct Referrals</p>
                                </div>
                                <div class="network-stat">
                                    <h4 class="mb-1 text-primary"><?php echo $network_stats['team_size'] ?? 0; ?></h4>
                                    <p class="text-muted mb-0 small">Total Team Size</p>
                                </div>
                                <div class="network-stat">
                                    <h4 class="mb-1 text-info">â‚¹<?php echo $network_stats['team_gv'] ?? '0.00'; ?></h4>
                                    <p class="text-muted mb-0 small">Team Group Volume</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Commission Breakdown -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-chart-pie text-warning me-2"></i>Commission Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <div class="commission-breakdown">
                                <div class="commission-item primary">
                                    <div class="text-muted small">Direct Sales</div>
                                    <div class="fw-bold text-success">â‚¹<?php echo $commission_summary['total_direct'] ?? '0.00'; ?></div>
                                </div>
                                <div class="commission-item secondary">
                                    <div class="text-muted small">Network/Override</div>
                                    <div class="fw-bold text-info">â‚¹<?php echo $commission_summary['total_network'] ?? '0.00'; ?></div>
                                </div>
                                <?php if (!empty($commission_summary['total_missed']) && $commission_summary['total_missed'] > 0): ?>
                                <div class="commission-item" class="style-25241">
                                    <div class="text-muted small">Missed (Inactive ID)</div>
                                    <div class="fw-bold text-danger">â‚¹<?php echo $commission_summary['total_missed']; ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total Earned</span>
                                <span class="fw-bold text-success fs-5">â‚¹<?php echo $commission_summary['total_commission'] ?? '0.00'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Commissions -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history text-warning me-2"></i>Recent Commissions</h5>
                            <a href="<?php echo $base; ?>/agent/commissions" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($commission_summary['direct_commissions']) && empty($commission_summary['network_commissions'])): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-rupee-sign fa-2x mb-2 d-block"></i>
                                No commissions yet
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php 
                                $allCommissions = array_merge(
                                    array_slice($commission_summary['direct_commissions'] ?? [], 0, 5),
                                    array_slice($commission_summary['network_commissions'] ?? [], 0, 5)
                                );
                                usort($allCommissions, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
                                $allCommissions = array_slice($allCommissions, 0, 10);
                                foreach ($allCommissions as $comm): 
                                    $isDirect = in_array($comm['type'] ?? '', ['direct_sale', 'level_bonus']);
                                ?>
                                <div class="list-group-item border-0 px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge <?php echo $isDirect ? 'bg-success' : 'bg-info'; ?> me-2">
                                                <?php echo $isDirect ? 'Direct' : 'Network'; ?>
                                            </span>
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $comm['type'] ?? 'Commission')); ?></strong>
                                            <?php if (!empty($comm['description'])): ?>
                                            <small class="text-muted ms-2"><?php echo htmlspecialchars($comm['description']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-<?php echo $isDirect ? 'success' : 'info'; ?>">
                                                â‚¹<?php echo number_format($comm['amount'] ?? 0, 2); ?>
                                            </div>
                                            <small class="text-muted"><?php echo date('d M Y', strtotime($comm['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Freelancer/Independent Agent Sections -->
            <div class="row g-3 mb-4">
                <!-- Performance Metrics -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Performance Metrics</h5>
                        </div>
                        <div class="card-body">
                            <div class="perf-grid">
                                <div class="perf-card highlight">
                                    <h4 class="text-primary mb-1"><?php echo $performance['this_month']['count'] ?? 0; ?></h4>
                                    <p class="text-muted mb-0 small">This Month Sales</p>
                                    <small class="text-success">â‚¹<?php echo $performance['this_month']['volume'] ?? '0.00'; ?></small>
                                </div>
                                <div class="perf-card">
                                    <h4 class="text-info mb-1"><?php echo $performance['last_month']['count'] ?? 0; ?></h4>
                                    <p class="text-muted mb-0 small">Last Month Sales</p>
                                    <small class="text-info">â‚¹<?php echo $performance['last_month']['volume'] ?? '0.00'; ?></small>
                                </div>
                                <div class="perf-card">
                                    <h4 class="text-warning mb-1"><?php echo $performance['career']['count'] ?? 0; ?></h4>
                                    <p class="text-muted mb-0 small">Career Total Sales</p>
                                    <small class="text-warning">â‚¹<?php echo $performance['career']['volume'] ?? '0.00'; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brokerage Info -->
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-percent text-primary me-2"></i>Brokerage Model</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 small">Model</p>
                                    <h6><?php echo ucfirst(str_replace('_', ' ', $commission_summary['brokerage_model'] ?? 'flat_percentage')); ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 small"><?php 
                                        $model = $commission_summary['brokerage_model'] ?? 'flat_percentage';
                                        if ($model === 'flat_percentage') echo 'Rate %';
                                        elseif ($model === 'flat_rate_sqft') echo 'Rate/sqft';
                                        else echo 'Flat Fee';
                                    ?></p>
                                    <h6>
                                        <?php 
                                            $model = $commission_summary['brokerage_model'] ?? 'flat_percentage';
                                            if ($model === 'flat_percentage') echo $commission_summary['brokerage_rate'] ?? 0 . '%';
                                            elseif ($model === 'flat_rate_sqft') echo 'â‚¹' . number_format($commission_summary['brokerage_rate'] ?? 0, 2) . '/sqft';
                                            else echo 'â‚¹' . number_format($commission_summary['flat_fee'] ?? 0, 2);
                                        ?>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-check text-success me-2"></i>Upcoming Site Visits</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($site_visits)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-calendar fa-2x mb-2 d-block"></i>
                                No upcoming visits
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($site_visits as $visit): ?>
                                <div class="visit-card list-group-item border-0 px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="visit-time"><?php echo date('H:i', strtotime($visit['visit_time'])); ?></span>
                                                <span class="visit-date"><?php echo date('d M Y', strtotime($visit['visit_date'])); ?></span>
                                            </div>
                                            <strong><?php echo htmlspecialchars($visit['visitor_name'] ?? $visit['lead_name'] ?? 'Client'); ?></strong>
                                            <small class="text-muted ms-2"><?php echo htmlspecialchars($visit['visitor_phone'] ?? $visit['lead_phone'] ?? ''); ?></small>
                                        </div>
                                        <?php if (!empty($visit['colony_name'])): ?>
                                        <span class="badge bg-success"><?php echo htmlspecialchars($visit['colony_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Commissions -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history text-warning me-2"></i>Recent Commissions</h5>
                            <a href="<?php echo $base; ?>/agent/commissions" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($commission_summary['commissions'])): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-rupee-sign fa-2x mb-2 d-block"></i>
                                No commissions yet
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php 
                                $comms = array_slice($commission_summary['commissions'] ?? [], 0, 10);
                                foreach ($comms as $comm): 
                                ?>
                                <div class="list-group-item border-0 px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($comm['property_title'] ?? 'Property Sale'); ?></strong>
                                            <?php if (!empty($comm['description'])): ?>
                                            <small class="text-muted ms-2"><?php echo htmlspecialchars($comm['description']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">â‚¹<?php echo number_format($comm['amount'] ?? 0, 2); ?></div>
                                            <small class="text-muted"><?php echo date('d M Y', strtotime($comm['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>

            <!-- Recent Leads (Common for both) -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-bullseye text-primary me-2"></i>Recent Leads</h5>
                            <a href="<?php echo $base; ?>/agent/leads" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recent_leads)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-bullseye fa-2x mb-2 d-block"></i>
                                No leads yet. Contact your admin to assign leads.
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($recent_leads, 0, 10) as $lead): ?>
                                <div class="list-group-item border-0 px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></strong>
                                            <span class="lead-status <?php echo strtolower($lead['status'] ?? 'new'); ?> ms-2">
                                                <?php echo ucfirst($lead['status'] ?? 'New'); ?>
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($lead['phone'] ?? ''); ?></small>
                                            <small class="text-muted"><?php echo date('d M Y', strtotime($lead['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties/Listings (Common for both) -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-building text-info me-2"></i><?php echo $agent_type === 'freelancer' ? 'My Listings' : 'Assigned Properties'; ?></h5>
                            <a href="<?php echo $base; ?>/agent/properties" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php 
                            $props = $agent_type === 'freelancer' ? ($my_properties ?? []) : ($assigned_properties ?? []);
                            if (empty($props)): 
                            ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-building fa-2x mb-2 d-block"></i>
                                No properties <?php echo $agent_type === 'freelancer' ? 'listed' : 'assigned'; ?> yet
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($props, 0, 10) as $prop): ?>
                                <div class="list-group-item border-0 px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($prop['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($prop['image']); ?>" alt="" class="style-97710">
                                            <?php else: ?>
                                            <div class="style-44705">
                                                <i class="fas fa-building text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($prop['title'] ?? 'Property'); ?></strong>
                                                <?php if (!empty($prop['colony_name']) || !empty($prop['location'])): ?>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($prop['colony_name'] ?? $prop['location'] ?? ''); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary">â‚¹<?php echo number_format($prop['price'] ?? 0); ?></div>
                                            <span class="property-status <?php echo strtolower($prop['status'] ?? 'available'); ?>">
                                                <?php echo ucfirst($prop['status'] ?? 'Available'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // Auto-dismiss alerts
    setTimeout(function(){
        document.querySelectorAll('.alert').forEach(function(alert){
            alert.classList.remove('show');
            setTimeout(function(){ alert.remove(); }, 150);
        });
    }, 5000);
    </script>
</body>
</html>