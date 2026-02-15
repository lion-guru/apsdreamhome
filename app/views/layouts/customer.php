<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Customer Panel - APS Dream Home' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            font-size: 14px;
        }

        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover, .sidebar-menu li.active a {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
        }

        .sidebar-menu i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .navbar-custom {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            padding: 0.5rem 1.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4>APS Dream Home</h4>
            <small>Customer Panel</small>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li class="<?= ($active_page ?? '') == 'dashboard' ? 'active' : '' ?>">
                    <a href="/customer/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?= ($active_page ?? '') == 'properties' ? 'active' : '' ?>">
                    <a href="/customer/properties"><i class="fas fa-home"></i> My Properties</a>
                </li>
                <li class="<?= ($active_page ?? '') == 'payments' ? 'active' : '' ?>">
                    <a href="/customer/payments"><i class="fas fa-credit-card"></i> Payments</a>
                </li>
                <li class="<?= ($active_page ?? '') == 'bookings' ? 'active' : '' ?>">
                    <a href="/customer/bookings"><i class="fas fa-calendar-check"></i> Bookings</a>
                </li>
                <li class="<?= ($active_page ?? '') == 'profile' ? 'active' : '' ?>">
                    <a href="/customer/profile"><i class="fas fa-user"></i> My Profile</a>
                </li>
                <li>
                    <a href="/customer/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light navbar-custom rounded">
            <button type="button" id="sidebarCollapse" class="btn btn-info d-md-none">
                <i class="fas fa-align-left"></i>
            </button>
            <div class="ml-auto d-flex align-items-center">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-user-circle mr-1"></i>
                        <?= h($_SESSION['customer_name'] ?? 'Customer') ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="/customer/profile"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/customer/logout"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <div class="container-fluid">
            <?= $content ?? '' ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });

        // Format currency function for JS
        function formatCurrency(amount) {
            return '₹' + parseFloat(amount).toLocaleString('en-IN');
        }
    </script>

    <?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
