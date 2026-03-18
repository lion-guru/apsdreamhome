<?php
/* Admin Header */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home Admin'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Admin CSS -->
    <style>
        body {
            padding-top: 56px;
        }

        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .nav-link {
            font-weight: 500;
            color: #333;
        }

        .nav-link .feather {
            margin-right: 4px;
            color: #727272;
        }

        .nav-link.active {
            color: #007bff;
        }

        .nav-link:hover .feather,
        .nav-link.active .feather {
            color: inherit;
        }
    </style>
</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">APS Dream Home Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="<?php echo BASE_URL ?? '/'; ?>/admin/logout">Sign out</a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($active_page ?? '') == 'dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <!-- Dashboard Submenu -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#dashboardSubmenu" aria-expanded="false">
                                <i class="fas fa-chart-line"></i> Role Dashboards <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <ul class="collapse nav flex-column ms-3" id="dashboardSubmenu">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'agent-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/agent">
                                        <i class="fas fa-user-tie"></i> Agent Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'builder-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/builder">
                                        <i class="fas fa-hard-hat"></i> Builder Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'ceo-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/ceo">
                                        <i class="fas fa-crown"></i> CEO Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'cfo-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/cfo">
                                        <i class="fas fa-calculator"></i> CFO Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'cm-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/cm">
                                        <i class="fas fa-user"></i> CM Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'coo-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/coo">
                                        <i class="fas fa-cogs"></i> COO Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'cto-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/cto">
                                        <i class="fas fa-laptop-code"></i> CTO Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'director-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/director">
                                        <i class="fas fa-user-tie"></i> Director Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'finance-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/finance">
                                        <i class="fas fa-money-bill-wave"></i> Finance Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'hr-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/hr">
                                        <i class="fas fa-users-cog"></i> HR Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'it-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/it">
                                        <i class="fas fa-server"></i> IT Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'marketing-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/marketing">
                                        <i class="fas fa-bullhorn"></i> Marketing Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'operations-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/operations">
                                        <i class="fas fa-tasks"></i> Operations Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'sales-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/sales">
                                        <i class="fas fa-chart-bar"></i> Sales Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($active_page ?? '') == 'superadmin-dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/dashboard/superadmin">
                                        <i class="fas fa-user-shield"></i> Super Admin Dashboard
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($active_page ?? '') == 'properties' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/properties">
                                <i class="fas fa-building"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($active_page ?? '') == 'users' ? 'active' : ''; ?>" href="<?php echo BASE_URL ?? '/'; ?>/admin/users">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL ?? '/'; ?>" target="_blank">
                                <i class="fas fa-external-link-alt"></i> View Site
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-3">