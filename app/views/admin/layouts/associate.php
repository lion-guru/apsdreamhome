<?php
/**
 * Associate Layout - MLM Associate Access
 */
if (!defined('BASE_PATH')) exit;

$currentUser = $currentUser ?? [];
$currentRole = $currentRole ?? 'associate';
$roleName = $roleName ?? 'Associate';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Associate Dashboard'; ?> | APS Dream Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #16a34a;
            --sidebar-bg: #14532d;
            --main-bg: #f0fdf4;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--main-bg);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background: var(--sidebar-bg);
            padding-top: 60px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 12px 20px;
            font-size: 0.9rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        
        .top-navbar {
            margin-left: 240px;
            background: #fff;
            padding: 15px 25px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .main-content {
            margin-left: 240px;
            padding: 25px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #bbf7d0;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .top-navbar, .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="px-3 pb-3 border-bottom border-secondary">
            <div class="text-white fw-bold">APS Dream Home</div>
            <small class="text-white-50">MLM Associate Portal</small>
        </div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'network' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/tree">
                    <i class="fas fa-sitemap"></i> My Network
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'referrals' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/referrals">
                    <i class="fas fa-user-plus"></i> My Referrals
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'commissions' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/commissions">
                    <i class="fas fa-percentage"></i> My Commissions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'payouts' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/payouts">
                    <i class="fas fa-wallet"></i> Payouts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'properties' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/properties">
                    <i class="fas fa-building"></i> Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'leads' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/leads">
                    <i class="fas fa-bullseye"></i> Leads
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link <?php echo ($active_page ?? '') === 'rank' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/rank">
                    <i class="fas fa-medal"></i> My Rank
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/profile">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <nav class="top-navbar d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h5>
            <small class="text-muted">Welcome back, <?php echo $currentUser['name'] ?? 'Associate'; ?>!</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-success"><?php echo ucwords(str_replace('_', ' ', $roleName)); ?></span>
        </div>
    </nav>
    
    <main class="main-content">
        <?php echo $content ?? ''; ?>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
