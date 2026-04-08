<?php
/**
 * Associate Dashboard View
 */
$associate = $associate ?? [];
$network = $network ?? [];
$commissions = $commissions ?? [];
$stats = $stats ?? [];
$page_title = $page_title ?? 'Associate Dashboard';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card { transition: transform 0.2s; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base; ?>/associate/dashboard">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo $base; ?>/associate/dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>/team/genealogy">My Network</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>/admin/commission">Commissions</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Associate'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/customer/profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="card bg-gradient-primary text-white mb-4 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Associate'); ?>!</h4>
                        <p class="mb-0 opacity-75">Track your network performance and earnings</p>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-1">Current Rank</h5>
                        <span class="badge bg-warning text-dark fs-6"><?php echo htmlspecialchars($associate['rank'] ?? 'Bronze'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small">Total Earnings</p>
                                <h4 class="mb-0">₹<?php echo number_format($stats['total_earnings'] ?? 0); ?></h4>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="fas fa-rupee-sign text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small">This Month</p>
                                <h4 class="mb-0">₹<?php echo number_format($stats['month_earnings'] ?? 0); ?></h4>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10">
                                <i class="fas fa-calendar text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small">Network Size</p>
                                <h4 class="mb-0"><?php echo number_format($stats['network_size'] ?? 0); ?></h4>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10">
                                <i class="fas fa-users text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small">Direct Referrals</p>
                                <h4 class="mb-0"><?php echo number_format($stats['direct_referrals'] ?? 0); ?></h4>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10">
                                <i class="fas fa-user-plus text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Network Overview -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Network Overview</h5>
                        <a href="<?php echo $base; ?>/team/genealogy" class="btn btn-sm btn-primary">View Full Tree</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($network)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Level</th>
                                            <th>Members</th>
                                            <th>Active</th>
                                            <th>Commission</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($network as $level): ?>
                                            <tr>
                                                <td>Level <?php echo $level['level']; ?></td>
                                                <td><?php echo $level['members']; ?></td>
                                                <td><?php echo $level['active']; ?></td>
                                                <td>₹<?php echo number_format($level['commission'] ?? 0); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No network data available</p>
                                <a href="<?php echo $base; ?>/register?ref=<?php echo $_SESSION['user_id'] ?? ''; ?>" class="btn btn-primary">
                                    <i class="fas fa-share-alt me-2"></i>Invite Members
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Commissions -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Recent Earnings</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($commissions)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($commissions, 0, 5) as $commission): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <small class="text-muted"><?php echo $commission['type']; ?></small>
                                            <p class="mb-0 fw-medium">₹<?php echo number_format($commission['amount']); ?></p>
                                        </div>
                                        <small class="text-muted"><?php echo date('M d', strtotime($commission['date'])); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No recent earnings</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo $base; ?>/register?ref=<?php echo $_SESSION['user_id'] ?? ''; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Invite Member
                            </a>
                            <a href="<?php echo $base; ?>/admin/payouts" class="btn btn-outline-success">
                                <i class="fas fa-wallet me-2"></i>Request Payout
                            </a>
                            <a href="<?php echo $base; ?>/customer/documents" class="btn btn-outline-info">
                                <i class="fas fa-file me-2"></i>My Documents
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
