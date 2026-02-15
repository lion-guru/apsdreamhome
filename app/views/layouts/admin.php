<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $this->getCsrfToken() ?? '' ?>">
    <title>Admin - <?= $title ?? 'APS Dream Home' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0;
            padding: 15px 20px;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card .card-body {
            padding: 2rem;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar p-3">
            <div class="text-center mb-4">
                <h4><i class="fas fa-crown me-2"></i>Admin Panel</h4>
                <small class="text-light">APS Dream Home</small>
            </div>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-1">
                    <a href="/admin/dashboard" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/about" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/about') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-info-circle me-2"></i>About Us
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/users" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/properties" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/properties') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-building me-2"></i>Properties
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/leads" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/leads') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-user-plus me-2"></i>Leads
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/mlm-analytics" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-analytics') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-pie me-2"></i>MLM Analytics
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/mlm-engagement" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-engagement') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-handshake me-2"></i>MLM Engagement
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/customers" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/customers') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-users me-2"></i>Customers
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/associates" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/associates') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-network-wired me-2"></i>Associates
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/employees" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/employees') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-user-tie me-2"></i>Employees
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/reports" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/crm-dashboard" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/crm-dashboard') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-line me-2"></i>CRM Dashboard
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/mlm-plan-builder" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-plan-builder') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-tools me-2"></i>MLM Builder
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/settings" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/database" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/database') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-database me-2"></i>Database
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/logs" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/logs') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-file-alt me-2"></i>Logs
                    </a>
                </li>
            </ul>

            <div class="mt-auto">
                <a href="/admin/logout" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">
                        <i class="fas fa-user-shield me-2"></i>
                        Welcome, <?= htmlspecialchars($_SESSION['auser'] ?? 'Admin') ?>
                    </span>

                    <div class="d-flex align-items-center">
                        <span class="me-3">
                            <i class="fas fa-clock me-1"></i>
                            <?= date('M d, Y H:i') ?>
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/admin/settings">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a></li>
                                <li><a class="dropdown-item" href="/admin/database">
                                    <i class="fas fa-database me-2"></i>Database
                                </a></li>
                                <li><a class="dropdown-item" href="/admin/logs">
                                    <i class="fas fa-file-alt me-2"></i>Logs
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/admin/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="p-4">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        // Initialize DataTables for admin tables
        $(document).ready(function() {
            $('.admin-table').DataTable({
                pageLength: 25,
                responsive: true,
                order: [[0, 'desc']]
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);

        // Enhanced fetch with CSRF protection for admin AJAX calls
        function adminFetch(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            // Merge headers
            if (options.headers) {
                options.headers = { ...defaultOptions.headers, ...options.headers };
            } else {
                options.headers = defaultOptions.headers;
            }

            return fetch(url, options)
                .then(response => {
                    if (response.status === 403) {
                        alert('CSRF token validation failed. Please refresh the page and try again.');
                        window.location.reload();
                        throw new Error('CSRF validation failed');
                    }
                    return response;
                });
        }

        // Global error handler for admin AJAX calls
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            if (jqxhr.status === 403 && jqxhr.responseJSON?.error === 'CSRF token validation failed') {
                alert('Security session expired. Please refresh the page and try again.');
                window.location.reload();
            }
        });
    </script>
</body>
</html>
