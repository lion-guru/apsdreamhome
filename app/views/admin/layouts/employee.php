<?php
/**
 * Employee Layout - Limited Access
 */
if (!defined('BASE_PATH')) exit;

$currentUser = $currentUser ?? [];
$currentRole = $currentRole ?? 'employee';
$roleName = $roleName ?? 'Employee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Employee Dashboard'; ?> | APS Dream Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0891b2;
            --sidebar-bg: #164e63;
            --main-bg: #f0fdfa;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--main-bg);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
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
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .nav-link i {
            width: 22px;
        }
        
        .top-navbar {
            margin-left: 220px;
            background: #fff;
            padding: 15px 25px;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .main-content {
            margin-left: 220px;
            padding: 25px;
            min-height: calc(100vh - 60px);
        }
        
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .top-navbar, .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'tasks' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/tasks">
                    <i class="fas fa-tasks"></i> My Tasks
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_page ?? '') === 'leads' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/leads">
                    <i class="fas fa-user-friends"></i> My Leads
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
            <li class="nav-item mt-3">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/profile">
                    <i class="fas fa-user"></i> My Profile
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
        <h5 class="mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h5>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-info"><?php echo ucwords(str_replace('_', ' ', $roleName)); ?></span>
            <span><?php echo $currentUser['name'] ?? 'User'; ?></span>
        </div>
    </nav>
    
    <main class="main-content">
        <?php echo $content ?? ''; ?>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
