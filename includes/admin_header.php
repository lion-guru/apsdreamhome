<?php
session_start();
require_once __DIR__ . '/db_settings.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /apsdreamhomefinal/admin/login.php');
    exit;
}

// Get unread notification count
$unread_notifications = 0;
$conn = get_db_connection();
if ($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'");
    $stmt->execute();
    $result = $stmt->get_result();
    $unread_notifications = $result->fetch_object()->count;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title ?? 'Admin Dashboard - APS Dream Homes'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Admin CSS -->
    <link href="/apsdreamhomefinal/assets/css/admin.css" rel="stylesheet">
    <!-- Add Flatpickr CSS -->
    <link rel="stylesheet" href="/node_modules/flatpickr/dist/flatpickr.min.css">
    
    <?php if (isset($additional_css)) echo $additional_css; ?>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="/apsdreamhomefinal/admin/">APS Dream Homes</a>
        
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch">
                <button class="btn btn-primary" id="btnNavbarSearch" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_notifications > 0): ?>
                        <span class="badge bg-danger"><?php echo $unread_notifications; ?></span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="/apsdreamhomefinal/admin/notifications.php">View All Notifications</a></li>
                    <li><a class="dropdown-item" href="/apsdreamhomefinal/admin/notification_management.php">Manage Notifications</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="markAllRead()">Mark All as Read</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="/apsdreamhomefinal/admin/settings.php">Settings</a></li>
                    <li><a class="dropdown-item" href="/apsdreamhomefinal/admin/activity_log.php">Activity Log</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/apsdreamhomefinal/admin/logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Property Management</div>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/properties.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Properties
                        </a>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/projects.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-building"></i></div>
                            Projects
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Lead Management</div>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/leads.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Leads
                        </a>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/visits.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar"></i></div>
                            Visit Scheduling
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Communication</div>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/notification_management.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-bell"></i></div>
                            Notifications
                            <?php if ($unread_notifications > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $unread_notifications; ?></span>
                            <?php endif; ?>
                        </a>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/email_templates.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Email Templates
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Reports</div>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/analytics.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Analytics
                        </a>
                        <a class="nav-link" href="/apsdreamhomefinal/admin/reports.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            Reports
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <!-- Main content will be here -->
</main>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add Flatpickr JS -->
    <script src="/node_modules/flatpickr/dist/flatpickr.min.js"></script>
    <!-- Admin JS -->
    <script src="/apsdreamhomefinal/assets/js/admin.js"></script>
    
    <script>
    function markAllRead() {
        fetch('/apsdreamhomefinal/admin/api/mark_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    </script>
    
    <?php if (isset($additional_js)) echo $additional_js; ?>
</body>
</html>
