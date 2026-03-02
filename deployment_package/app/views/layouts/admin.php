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
    <link href="<?= BASE_URL ?>public/css/dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar admin-sidebar p-3">
            <div class="text-center mb-4">
                <h4><i class="fas fa-crown me-2"></i>Admin Panel</h4>
                <small class="text-light">APS Dream Home</small>
            </div>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/dashboard" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/ai/hub" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/ai') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-robot me-2"></i>AI Hub
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/about" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/about') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-info-circle me-2"></i>About Us
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/users" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/projects" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/projects') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-project-diagram me-2"></i>Projects
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/properties" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/properties') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-building me-2"></i>Properties
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/emi" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/emi') !== false && strpos($_SERVER['REQUEST_URI'], 'foreclosure') === false) ? 'active' : '' ?>">
                        <i class="fas fa-money-check-alt me-2"></i>EMI Management
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/emi/foreclosure-report" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/emi/foreclosure-report') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Foreclosure Reports
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/leads" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/leads') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-user-plus me-2"></i>Leads
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/mlm-analytics" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-analytics') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-pie me-2"></i>MLM Analytics
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/mlm-engagement" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-engagement') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-handshake me-2"></i>MLM Engagement
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/customers" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/customers') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-users-cog me-2"></i>Customers
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/news" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/news') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-newspaper me-2"></i>News
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/careers" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/careers') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-briefcase me-2"></i>Careers
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/media" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/media') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-images me-2"></i>Media
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/associates" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/associates') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-network-wired me-2"></i>Associates
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/employees" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/employees') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-user-tie me-2"></i>Employees
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/reports" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/crm-dashboard" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/crm-dashboard') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-line me-2"></i>CRM Dashboard
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/accounting" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/accounting') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-calculator me-2"></i>Accounting
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/mlm-plan-builder" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-plan-builder') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-tools me-2"></i>MLM Builder
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/settings" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/database" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/database') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-database me-2"></i>Database
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>admin/logs" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/logs') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-file-alt me-2"></i>Logs
                    </a>
                </li>
            </ul>

            <div class="mt-auto">
                <a href="<?= BASE_URL ?>admin/logout" class="nav-link text-danger">
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
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/settings">
                                        <i class="fas fa-cog me-2"></i>Settings
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/database">
                                        <i class="fas fa-database me-2"></i>Database
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/logs">
                                        <i class="fas fa-file-alt me-2"></i>Logs
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>admin/logout">
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
                order: [
                    [0, 'desc']
                ]
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
                options.headers = {
                    ...defaultOptions.headers,
                    ...options.headers
                };
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