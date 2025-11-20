<?php
require_once __DIR__ . '/config/config.php';
global $con;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']['is_authenticated'] !== true) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - APS Dream Homes</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/img/favicon.ico">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Modern UI CSS -->
    <link href="css/modern-ui.css" rel="stylesheet">
    
    <!-- Admin Enhancements CSS -->
    <link href="css/admin-enhancements.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/admin-style.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Custom Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4bb543;
            --info-color: #00b4d8;
            --warning-color: #f9c74f;
            --danger-color: #ef476f;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #333;
            overflow-x: hidden;
        }
        
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            padding-top: calc(var(--header-height) + 20px);
            transition: all 0.3s;
        }
        
        .header {
            height: var(--header-height);
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 100;
            padding: 0 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #333;
            padding: 5px 15px;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            background: #f5f7fb;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-transform: uppercase;
            overflow: hidden;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-avatar .initials {
            line-height: 1;
            font-size: 16px;
        }
        
        .user-dropdown .dropdown-menu {
            min-width: 220px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 10px 0;
            margin-top: 10px;
        }
        
        .user-dropdown .dropdown-item {
            padding: 8px 20px;
            font-size: 14px;
            color: #495057;
            transition: all 0.2s;
        }
        
        .user-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .user-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
            color: #6c757d;
        }
        
        /* Profile Picture Upload Styles */
        .profile-upload-wrapper {
            position: relative;
            display: inline-block;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .profile-upload-wrapper:hover .profile-upload-hover {
            opacity: 1;
            visibility: visible;
        }
        
        .profile-upload-hover {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .profile-upload-hover i {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .profile-upload-hover span {
            font-size: 0.8rem;
        }
        
        .profile-upload-controls {
            position: absolute;
            bottom: 5px;
            right: 5px;
            display: flex;
            gap: 5px;
        }
        
        .profile-upload-btn,
        .profile-remove-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .profile-upload-btn:hover,
        .profile-remove-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }
        
        .profile-remove-btn {
            background: #dc3545;
        }
        
        .profile-remove-btn:hover {
            background: #bb2d3b;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .sidebar-menu {
            padding: 15px 0;
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: #6c757d;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 0 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px 0;
        }
        
        .dropdown-item {
            padding: 8px 20px;
            color: #6c757d;
            transition: all 0.3s;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }
        
        .badge {
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 50px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header {
                left: 0;
            }
            
            .sidebar.show {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    <i class="fas fa-home"></i>
                    <span>APS Dream Home</span>
                </a>
            </div>
            <nav class="sidebar-menu">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['properties.php', 'add_property.php', 'edit_property.php', 'property_types.php']) ? 'active' : ''; ?>" href="properties.php">
                            <i class="fas fa-building"></i>
                            <span>Properties</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['customers.php', 'add_customer.php', 'edit_customer.php']) ? 'active' : ''; ?>" href="customers.php">
                            <i class="fas fa-users"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['bookings.php', 'add_booking.php', 'edit_booking.php']) ? 'active' : ''; ?>" href="bookings.php">
                            <i class="fas fa-calendar-check"></i>
                            <span>Bookings</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['leads.php', 'add_lead.php', 'view_lead.php']) ? 'active' : ''; ?>" href="leads.php">
                            <i class="fas fa-user-tie"></i>
                            <span>Leads</span>
                            <span class="badge bg-danger ms-auto">5</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['visits.php', 'schedule_visit.php']) ? 'active' : ''; ?>" href="visits.php">
                            <i class="fas fa-calendar-day"></i>
                            <span>Site Visits</span>
                            <span class="badge bg-warning ms-auto">3</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'reports/') !== false ? 'active' : ''; ?>" href="#" data-bs-toggle="collapse" data-bs-target="#reportsMenu">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                            <i class="fas fa-chevron-right ms-auto"></i>
                        </a>
                        <div class="collapse" id="reportsMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link" href="reports/sales.php">Sales Report</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="reports/leads.php">Leads Report</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="reports/visits.php">Visits Report</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item mt-4">
                        <div class="px-3 small text-uppercase fw-bold text-muted">Management</div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'add_user.php', 'edit_user.php']) ? 'active' : ''; ?>" href="users.php">
                            <i class="fas fa-user-cog"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <button class="btn btn-link d-lg-none" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <?php
                // Get user data including profile picture
                $user_id = $_SESSION['admin_session']['user_id'] ?? 0;
                $user = [];
                if ($user_id) {
                    $conn = $con;
                    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
                    $user = $result->fetch_assoc();
                }
                
                // Get user name with fallback to session or default
                $user_name = $user['name'] ?? ($_SESSION['admin_session']['username'] ?? 'Admin');
                $user_role = 'Administrator'; // You can make this dynamic based on user role
                $initials = !empty($user_name) ? strtoupper(substr($user_name, 0, 1)) : 'A';
                
                // Set profile picture URL
                $profile_pic = !empty($user['profile_picture']) 
                    ? 'uploads/profile_pictures/' . $user['profile_picture'] 
                    : 'assets/img/default-avatar.php?name=' . urlencode($user_name);
                ?>
                <div class="dropdown user-dropdown">
                    <a class="dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="<?php echo $profile_pic; ?>" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                            <?php else: ?>
                                <span class="initials"><?php echo $initials; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-none d-md-block ms-2">
                            <div class="fw-bold user-name"><?php echo htmlspecialchars($user_name); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($user_role); ?></small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="fas fa-user-edit me-2"></i> Edit Profile</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="fas fa-key me-2"></i> Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </header>
            
            <div class="container-fluid px-0">
