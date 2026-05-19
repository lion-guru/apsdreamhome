<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home - Admin'; ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/app/views/admin/assets/img/favicon.png">
    <meta name="description" content="<?php echo $page_description ?? 'Admin Panel'; ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: #1e1b4b;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo i {
            font-size: 1.3rem;
            color: #a5b4fc;
        }

        .sidebar-sub {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.7rem;
            margin-top: 4px;
        }

        .sidebar-section {
            padding: 15px 15px 5px;
            font-size: 0.65rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.35);
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 10px;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 2px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 9px 12px;
            color: #c7d2fe;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sidebar-link:hover {
            background: #312e81;
            color: #fff;
        }

        .sidebar-link.active {
            background: #4f46e5;
            color: #fff;
        }

        .sidebar-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 0.95rem;
            color: #a5b4fc;
            text-align: center;
        }

        .sidebar-link.active i,
        .sidebar-link:hover i {
            color: #fff;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Top Navigation */
        .top-nav {
            background: #fff;
            height: 60px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .toggle-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            display: none;
        }

        .breadcrumb {
            font-size: 0.85rem;
            color: #64748b;
        }

        .breadcrumb strong {
            color: #1e293b;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-icon {
            position: relative;
            background: none;
            border: none;
            font-size: 1.1rem;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
        }

        .nav-icon:hover {
            background: #f1f5f9;
        }

        .nav-icon .badge {
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.55rem;
            padding: 2px 5px;
            border-radius: 10px;
            background: #ef4444;
            color: #fff;
        }

        /* User Profile Dropdown */
        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .user-box:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #4f46e5;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
        }

        .user-role {
            font-size: 0.7rem;
            color: #64748b;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            display: none;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            color: #374151;
            text-decoration: none;
            font-size: 0.875rem;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
        }

        .dropdown-item i {
            width: 16px;
            color: #6b7280;
        }

        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 8px 0;
        }

        /* Page Content */
        .page-content {
            padding: 24px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-btn {
                display: block;
            }
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>

<body>
    <?php
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
    $adminName = $_SESSION['admin_name'] ?? 'Admin';
    $adminRole = $_SESSION['admin_role'] ?? 'admin';
    $base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
    $isActive = function ($path) use ($currentUrl, $base) {
        $full = $base . $path;
        return $currentUrl === $full || strpos($currentUrl, $full . '/') === 0 || strpos($currentUrl, $full . '?') === 0;
    };
    ?>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-logo">
                <i class="fas fa-home"></i>
                <span>APS Dream Home</span>
            </a>
            <div class="sidebar-sub"><?php echo ucfirst(str_replace('_', ' ', $adminRole)); ?> Panel</div>
        </div>

        <div class="sidebar-section">Main</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/dashboard" class="sidebar-link <?php echo $isActive('/admin/dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/analytics" class="sidebar-link <?php echo $isActive('/admin/analytics') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Analytics
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/reports" class="sidebar-link <?php echo $isActive('/admin/reports') ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
            </li>
        </ul>

        <div class="sidebar-section">CRM & Sales</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/leads" class="sidebar-link <?php echo $isActive('/admin/leads') ? 'active' : ''; ?>">
                    <i class="fas fa-bullseye"></i> Leads
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/leads/scoring" class="sidebar-link <?php echo $isActive('/admin/leads/scoring') ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i> Lead Scoring
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/customers" class="sidebar-link <?php echo $isActive('/admin/customers') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Customers
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/deals" class="sidebar-link <?php echo $isActive('/admin/deals') ? 'active' : ''; ?>">
                    <i class="fas fa-handshake"></i> Deals
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/sales" class="sidebar-link <?php echo $isActive('/admin/sales') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Sales
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/campaigns" class="sidebar-link <?php echo $isActive('/admin/campaigns') ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i> Campaigns
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/bookings" class="sidebar-link <?php echo $isActive('/admin/bookings') ? 'active' : ''; ?>">
                    <i class="fas fa-file-contract"></i> Bookings
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Properties</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/properties" class="sidebar-link <?php echo $isActive('/admin/properties') ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i> All Properties
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/projects" class="sidebar-link <?php echo $isActive('/admin/projects') ? 'active' : ''; ?>">
                    <i class="fas fa-project-diagram"></i> Projects
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/plots" class="sidebar-link <?php echo $isActive('/admin/plots') ? 'active' : ''; ?>">
                    <i class="fas fa-map"></i> Plots / Land
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/sites" class="sidebar-link <?php echo $isActive('/admin/sites') ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i> Sites
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/resell-properties" class="sidebar-link <?php echo $isActive('/admin/resell-properties') ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i> Resell Properties
                </a>
            </li>
        </ul>

        <div class="sidebar-section">MLM Network</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/network/tree" class="sidebar-link <?php echo $isActive('/admin/network/tree') ? 'active' : ''; ?>">
                    <i class="fas fa-sitemap"></i> Network Tree
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/network/genealogy" class="sidebar-link <?php echo $isActive('/admin/network/genealogy') ? 'active' : ''; ?>">
                    <i class="fas fa-project-diagram"></i> Genealogy
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/network/ranks" class="sidebar-link <?php echo $isActive('/admin/network/ranks') ? 'active' : ''; ?>">
                    <i class="fas fa-medal"></i> Ranks
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/associates" class="sidebar-link <?php echo $isActive('/admin/associates') ? 'active' : ''; ?>">
                    <i class="fas fa-handshake"></i> Associates
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/commission" class="sidebar-link <?php echo $isActive('/admin/commission') ? 'active' : ''; ?>">
                    <i class="fas fa-percentage"></i> Commissions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/payouts" class="sidebar-link <?php echo $isActive('/admin/payouts') ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave"></i> Payouts
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Financial</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/payments" class="sidebar-link <?php echo $isActive('/admin/payments') ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/emi" class="sidebar-link <?php echo $isActive('/admin/emi') ? 'active' : ''; ?>">
                    <i class="fas fa-calculator"></i> EMI Plans
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/accounting" class="sidebar-link <?php echo $isActive('/admin/accounting') ? 'active' : ''; ?>">
                    <i class="fas fa-calculator"></i> Accounting
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/expenses" class="sidebar-link <?php echo $isActive('/admin/expenses') ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave"></i> Expenses
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Operations</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/tasks" class="sidebar-link <?php echo $isActive('/admin/tasks') ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i> Tasks
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/visits" class="sidebar-link <?php echo $isActive('/admin/visits') ? 'active' : ''; ?>">
                    <i class="fas fa-walking"></i> Site Visits
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/support_tickets" class="sidebar-link <?php echo $isActive('/admin/support_tickets') ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> Support Tickets
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Marketing</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/gallery" class="sidebar-link <?php echo $isActive('/admin/gallery') ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i> Gallery
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/testimonials" class="sidebar-link <?php echo $isActive('/admin/testimonials') ? 'active' : ''; ?>">
                    <i class="fas fa-comment"></i> Testimonials
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/news" class="sidebar-link <?php echo $isActive('/admin/news') ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i> News
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/blog" class="sidebar-link <?php echo $isActive('/admin/blog') ? 'active' : ''; ?>">
                    <i class="fas fa-blog"></i> Blog
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/media" class="sidebar-link <?php echo $isActive('/admin/media') ? 'active' : ''; ?>">
                    <i class="fas fa-photo-video"></i> Media Library
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/engagement" class="sidebar-link <?php echo $isActive('/admin/engagement') ? 'active' : ''; ?>">
                    <i class="fas fa-heart"></i> Engagement
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/careers" class="sidebar-link <?php echo $isActive('/admin/careers') ? 'active' : ''; ?>">
                    <i class="fas fa-briefcase"></i> Careers / Jobs
                </a>
            </li>
        </ul>

        <div class="sidebar-section">AI & Technology</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/ai" class="sidebar-link <?php echo $isActive('/admin/ai') ? 'active' : ''; ?>">
                    <i class="fas fa-brain"></i> AI Hub
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/ai-settings" class="sidebar-link <?php echo $isActive('/admin/ai-settings') ? 'active' : ''; ?>">
                    <i class="fas fa-robot"></i> AI Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/ai/analytics" class="sidebar-link <?php echo $isActive('/admin/ai/analytics') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-area"></i> AI Analytics
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Users & Team</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/users" class="sidebar-link <?php echo $isActive('/admin/users') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> All Users
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/employees" class="sidebar-link <?php echo $isActive('/admin/employees') ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i> Employees
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/hrm/employees" class="sidebar-link <?php echo $isActive('/admin/hrm/employees') ? 'active' : ''; ?>">
                    <i class="fas fa-id-badge"></i> HRM / Employees
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/roles" class="sidebar-link <?php echo $isActive('/admin/roles') ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i> Roles & Permissions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/invoices" class="sidebar-link <?php echo $isActive('/admin/invoices') ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i> Invoices
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Locations</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/locations/states" class="sidebar-link <?php echo $isActive('/admin/locations/states') ? 'active' : ''; ?>">
                    <i class="fas fa-globe"></i> States / Districts
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Content & Settings</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/pages" class="sidebar-link <?php echo $isActive('/admin/pages') ? 'active' : ''; ?>">
                    <i class="fas fa-file"></i> CMS Pages
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/legal-pages" class="sidebar-link <?php echo $isActive('/admin/legal-pages') ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Legal Pages
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/activity-log" class="sidebar-link <?php echo $isActive('/admin/activity-log') ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Activity Log
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/settings" class="sidebar-link <?php echo $isActive('/admin/settings') ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Site Settings
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/api-keys" class="sidebar-link <?php echo $isActive('/admin/api-keys') ? 'active' : ''; ?>">
                    <i class="fas fa-key"></i> API Keys
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Account</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/" target="_blank" class="sidebar-link">
                    <i class="fas fa-external-link-alt"></i> View Website
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?php echo $base; ?>/admin/logout" class="sidebar-link text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    Admin / <strong><?php echo $page_title ?? 'Dashboard'; ?></strong>
                </div>
            </div>

            <div class="nav-right">
                <!-- Notifications -->
                <button class="nav-icon" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </button>

                <!-- Messages -->
                <button class="nav-icon" onclick="toggleMessages()">
                    <i class="fas fa-envelope"></i>
                    <span class="badge">5</span>
                </button>

                <!-- Profile Dropdown -->
                <div style="position: relative;">
                    <div class="user-box" onclick="toggleProfile()">
                        <div class="user-avatar"><?php echo strtoupper(substr($adminName, 0, 1)); ?></div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($adminName); ?></div>
                            <div class="user-role"><?php echo ucfirst(str_replace('_', ' ', $adminRole)); ?></div>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: #64748b;"></i>
                    </div>

                    <!-- Profile Dropdown Menu -->
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="<?php echo $base; ?>/admin/profile" class="dropdown-item">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="<?php echo $base; ?>/admin/profile/security" class="dropdown-item">
                            <i class="fas fa-shield-alt"></i> Security
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $base; ?>/admin/settings" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $base; ?>/admin/logout" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="page-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Profile Dropdown Toggle
        function toggleProfile() {
            document.getElementById('profileDropdown').classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const userBox = document.querySelector('.user-box');
            const dropdown = document.getElementById('profileDropdown');
            if (!userBox.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Notifications toggle (placeholder)
        function toggleNotifications() {
            alert('Notifications panel - To be implemented');
        }

        // Messages toggle (placeholder)
        function toggleMessages() {
            alert('Messages panel - To be implemented');
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>

</html>