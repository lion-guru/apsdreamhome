<?php
// DEPRECATED: This file is nearly identical to customer_header.php
// Associate header template - only differs in title text
// Use app/views/layouts/customer_header.php instead and customize title
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Associate Panel - APS Dream Home' ?></title>

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

        /* Navbar Styles */
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
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

        .sidebar.collapsed {
            margin-left: -260px;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }

        .sidebar-menu i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }

        .top-navbar .navbar-toggler {
            border: none;
            color: var(--primary-color);
        }

        .top-navbar .dropdown-toggle::after {
            display: none;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
            border-radius: 10px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            border-bottom: none;
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            transform: translateY(-1px);
        }

        /* Tables */
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
            color: #495057;
        }

        .table td {
            vertical-align: middle;
        }

        /* Badges */
        .badge {
            font-size: 0.8em;
        }

        /* Progress Bars */
        .progress {
            height: 8px;
            border-radius: 4px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 260px;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            border: none;
        }

        /* Form Controls */
        .form-control {
            border-radius: 8px;
            border: 2px solid #e3e6f0;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Dropdown */
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Footer */
        .associate-footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4>
                <i class="fas fa-users mr-2"></i>
                Associate Panel
            </h4>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li>
                    <a href="/associate/dashboard" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/dashboard') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>डैशबोर्ड
                    </a>
                </li>
                <li>
                    <a href="/associate/team" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/team') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>टीम मैनेजमेंट
                    </a>
                </li>
                <li>
                    <a href="/associate/business" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/business') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i>बिजनेस ओवरव्यू
                    </a>
                </li>
                <li>
                    <a href="/associate/earnings" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/earnings') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-money-bill-wave"></i>अर्निंग्स
                    </a>
                </li>
                <li>
                    <a href="/associate/payouts" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/payouts') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-credit-card"></i>पेआउट्स
                    </a>
                </li>
                <li>
                    <a href="/associate/profile" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/profile') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-user"></i>प्रोफाइल
                    </a>
                </li>
                <li>
                    <a href="/associate/kyc" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/kyc') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-id-card"></i>KYC
                    </a>
                </li>
                <li>
                    <a href="/associate/rank" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/rank') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-trophy"></i>रैंक & अचीवमेंट्स
                    </a>
                </li>
                <li>
                    <a href="/associate/reports" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/reports') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i>रिपोर्ट्स
                    </a>
                </li>
                <li>
                    <a href="/associate/support" class="<?= (strpos($_SERVER['REQUEST_URI'], '/associate/support') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-headset"></i>सपोर्ट
                    </a>
                </li>
                <li class="mt-4">
                    <a href="/associate/logout" class="text-danger">
                        <i class="fas fa-sign-out-alt"></i>लॉगआउट
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>

            <div class="ml-auto d-flex align-items-center">
                <!-- Notifications -->
                <div class="dropdown mr-3">
                    <button class="btn btn-link text-muted position-relative" type="button" data-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge badge-danger badge-pill position-absolute" style="top: -5px; right: -5px;">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#">नई पेआउट रिक्वेस्ट अप्रूव्ड</a>
                        <a class="dropdown-item" href="#">टीम में 2 नए मेंबर्स जुड़े</a>
                        <a class="dropdown-item" href="#">कमिशन क्रेडिट हुआ</a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link text-muted" type="button" data-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg mr-2"></i>
                        <?= $_SESSION['associate_name'] ?? 'Associate' ?>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="/associate/profile">
                            <i class="fas fa-user mr-2"></i>प्रोफाइल
                        </a>
                        <a class="dropdown-item" href="/associate/settings">
                            <i class="fas fa-cog mr-2"></i>सेटिंग्स
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/associate/logout">
                            <i class="fas fa-sign-out-alt mr-2"></i>लॉगआउट
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?= $_SESSION['success'] ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Page Content -->
        <div class="fade-in">
            <?php if (isset($content)) echo $content; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="associate-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 APS Dream Home. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p>Associate Portal v1.0</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('mainContent').classList.toggle('expanded');
        }

        // Auto-hide sidebar on mobile
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('mainContent').classList.add('expanded');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                $('.dropdown-menu').removeClass('show');
            }
        });

        // Active menu highlighting
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto-refresh dashboard data every 5 minutes
        if (window.location.pathname.includes('/associate/dashboard')) {
            setInterval(function() {
                // Refresh dashboard stats via AJAX if needed
            }, 300000);
        }
    </script>
</body>
</html>
