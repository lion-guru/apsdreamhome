<?php
/**
 * Universal Dashboard Template for APS Dream Homes
 * Provides a standardized layout for all admin dashboards
 */

/**
 * Generates a complete dashboard page
 *
 * @param string $type The dashboard type (e.g., 'marketing', 'owner', 'hybrid', 'land')
 * @param array $stats Array of statistics boxes
 * @param array $quick_actions Array of quick action buttons
 * @param array $recent_activities Array of recent activity items
 * @param string $custom_content Custom HTML content to inject
 * @return string The complete HTML output
 */
function generateUniversalDashboard($type, $stats, $quick_actions, $recent_activities, $custom_content = '') {
    $title = ucfirst($type) . ' Dashboard - APS Dream Homes';
    $username = getAuthUsername() ?? 'Administrator';
    $role = getAuthSubRole() ?? 'Admin';

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo h($title); ?></title>
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom Dashboard CSS -->
        <style>
            :root {
                --sidebar-width: 260px;
                --top-navbar-height: 60px;
                --primary-color: #4e73df;
                --secondary-color: #858796;
                --success-color: #1cc88a;
                --info-color: #36b9cc;
                --warning-color: #f6c23e;
                --danger-color: #e74a3b;
                --light-bg: #f8f9fc;
            }

            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--light-bg);
                overflow-x: hidden;
            }

            /* Sidebar Styles */
            #sidebar {
                width: var(--sidebar-width);
                height: 100vh;
                position: fixed;
                left: 0;
                top: 0;
                z-index: 1000;
                background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
                color: white;
                transition: all 0.3s;
                overflow-y: auto;
            }

            #sidebar .sidebar-header {
                padding: 1.5rem;
                text-align: center;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            #sidebar .nav-link {
                color: rgba(255, 255, 255, 0.8);
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                transition: all 0.2s;
            }

            #sidebar .nav-link:hover {
                color: white;
                background: rgba(255, 255, 255, 0.1);
            }

            #sidebar .nav-link.active {
                color: white;
                font-weight: 600;
                background: rgba(0, 0, 0, 0.1);
            }

            #sidebar .nav-link i {
                width: 20px;
                margin-right: 10px;
                font-size: 1.1rem;
            }

            /* Main Content Styles */
            #content {
                margin-left: var(--sidebar-width);
                min-height: 100vh;
                transition: all 0.3s;
            }

            #top-navbar {
                height: var(--top-navbar-height);
                background: white;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
                display: flex;
                align-items: center;
                padding: 0 1.5rem;
                position: sticky;
                top: 0;
                z-index: 999;
            }

            .dashboard-container {
                padding: 1.5rem;
            }

            /* Stats Card Styles */
            .stats-card {
                border: none;
                border-radius: 0.5rem;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
                transition: transform 0.2s;
                overflow: hidden;
            }

            .stats-card:hover {
                transform: translateY(-5px);
            }

            .stats-card .card-body {
                padding: 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .stats-icon {
                font-size: 2rem;
                opacity: 0.3;
            }

            /* Quick Actions */
            .action-btn {
                border-radius: 0.5rem;
                padding: 1rem;
                text-align: center;
                text-decoration: none;
                transition: all 0.2s;
                display: block;
                margin-bottom: 1rem;
                background: white;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
                border: 1px solid #e3e6f0;
            }

            .action-btn:hover {
                background: var(--light-bg);
                transform: translateY(-2px);
            }

            .action-btn i {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
                display: block;
            }

            /* Recent Activity */
            .activity-item {
                padding: 1rem 0;
                border-bottom: 1px solid #e3e6f0;
            }

            .activity-item:last-child {
                border-bottom: none;
            }

            @media (max-width: 992px) {
                #sidebar {
                    left: -var(--sidebar-width);
                }
                #sidebar.active {
                    left: 0;
                }
                #content {
                    margin-left: 0;
                }
            }
        </style>
    </head>
    <body>
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="sidebar-header">
                <h5 class="mb-0"><i class="fas fa-home me-2"></i>APS Dream Home</h5>
                <small class="text-white-50"><?php echo h($role); ?> Panel</small>
            </div>
            <div class="nav flex-column mt-3">
                <a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="propertyview.php" class="nav-link"><i class="fas fa-building"></i> Properties</a>
                <a href="booking.php" class="nav-link"><i class="fas fa-calendar-check"></i> Bookings</a>
                <a href="customer_management.php" class="nav-link"><i class="fas fa-users"></i> Customers</a>
                <a href="agent.php" class="nav-link"><i class="fas fa-user-tie"></i> Partners</a>
                <a href="transactions.php" class="nav-link"><i class="fas fa-exchange-alt"></i> Transactions</a>
                <a href="projects.php" class="nav-link"><i class="fas fa-project-diagram"></i> Projects</a>
                <a href="ledger.php" class="nav-link"><i class="fas fa-book"></i> Ledger</a>
                <hr class="mx-3 opacity-25">
                <a href="manage_site_settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav id="top-navbar">
                <button type="button" id="sidebarCollapse" class="btn btn-link d-lg-none text-dark">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 d-none d-md-inline text-muted small">Welcome, <strong><?php echo h($username); ?></strong></span>
                    <div class="dropdown">
                        <button class="btn btn-link text-dark dropdown-toggle p-0 text-decoration-none" type="button" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=4e73df&color=fff" alt="User" class="rounded-circle" width="32" height="32">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="dashboard-container">
                <!-- Header -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><?php echo h(ucfirst($type)); ?> Dashboard</h1>
                    <a href="generate_report.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-download fa-sm text-white-50 me-1"></i> Generate Report
                    </a>
                </div>

                <!-- Stats Cards -->
                <div class="row">
                    <?php foreach ($stats as $stat): ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card h-100 border-start border-4 border-<?php echo h($stat['change_type'] === 'positive' ? 'success' : ($stat['change_type'] === 'pending' ? 'warning' : 'primary')); ?>">
                                <div class="card-body">
                                    <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1 text-<?php echo h($stat['change_type'] === 'positive' ? 'success' : ($stat['change_type'] === 'pending' ? 'warning' : 'primary')); ?>">
                                            <?php echo h($stat['label']); ?>
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo h($stat['value']); ?></div>
                                        <?php if (isset($stat['change'])): ?>
                                            <div class="text-xs mt-2 <?php echo h($stat['change_type'] === 'positive' ? 'text-success' : 'text-muted'); ?>">
                                                <i class="fas <?php echo h($stat['change_type'] === 'positive' ? 'fa-arrow-up' : 'fa-info-circle'); ?> me-1"></i>
                                                <?php echo h($stat['change']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="stats-icon text-gray-300">
                                        <i class="<?php echo h($stat['icon']); ?>"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row">
                    <!-- Custom Content Area -->
                    <div class="col-lg-8 mb-4">
                        <?php echo $custom_content; ?>
                    </div>

                    <!-- Sidebar Content: Actions & Activity -->
                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($quick_actions as $action): ?>
                                        <div class="col-6">
                                            <a href="<?php echo h($action['url']); ?>" class="action-btn text-<?php echo h($action['color'] ?? 'primary'); ?>">
                                                <i class="<?php echo h($action['icon']); ?>"></i>
                                                <span class="small"><?php echo h($action['title']); ?></span>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="px-3">
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="activity-item">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="<?php echo h($activity['icon']); ?> me-2"></i>
                                                <span class="small font-weight-bold"><?php echo h($activity['title']); ?></span>
                                            </div>
                                            <p class="text-muted small mb-0"><?php echo h($activity['description']); ?></p>
                                            <small class="text-muted-50" style="font-size: 0.7rem;"><?php echo h($activity['time']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="p-3 text-center border-top">
                                    <a href="activity_log.php" class="small text-decoration-none">View All Activity</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebarCollapse = document.getElementById('sidebarCollapse');
                const sidebar = document.getElementById('sidebar');

                if (sidebarCollapse) {
                    sidebarCollapse.addEventListener('click', function() {
                        sidebar.classList.toggle('active');
                    });
                }
            });
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
