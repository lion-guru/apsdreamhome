<?php
/**
 * Manager Layout - Team Manager Access
 */
if (!defined('BASE_PATH')) exit;

$currentUser = $currentUser ?? [];
$currentRole = $currentRole ?? 'manager';
$roleName = $roleName ?? 'Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Manager Dashboard'; ?> | APS Dream Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #ea580c;
            --sidebar-bg: #7c2d12;
            --main-bg: #fff7ed;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--main-bg);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
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
            margin-left: 250px;
            background: #fff;
            padding: 15px 25px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 25px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #fed7aa;
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
            <small class="text-white-50">Manager Portal</small>
        </div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'team' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/team">
                    <i class="fas fa-users"></i> My Team
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'leads' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/leads">
                    <i class="fas fa-bullseye"></i> Team Leads
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'performance' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/performance">
                    <i class="fas fa-chart-line"></i> Performance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'attendance' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/attendance">
                    <i class="fas fa-clock"></i> Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'leaves' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/leaves">
                    <i class="fas fa-calendar-alt"></i> Leave Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'bookings' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/bookings">
                    <i class="fas fa-file-contract"></i> Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'tasks' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/tasks">
                    <i class="fas fa-tasks"></i> Tasks
                </a>
            </li>
            <li class="nav-item mt-3">
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
            <small class="text-muted">Manager Dashboard</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-warning text-dark"><?php echo ucwords(str_replace('_', ' ', $roleName)); ?></span>
            <span><?php echo $currentUser['name'] ?? 'Manager'; ?></span>
        </div>
    </nav>
    
    <main class="main-content">
        <?php echo $content ?? ''; ?>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
