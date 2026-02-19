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
        .customer-footer {
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
                <i class="fas fa-user mr-2"></i>
                Customer Panel
            </h4>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li>
                    <a href="/customer/dashboard" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/dashboard') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>डैशबोर्ड
                    </a>
                </li>
                <li>
                    <a href="/customer/properties" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/properties') !== false || strpos($_SERVER['REQUEST_URI'], '/customer/property/') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-search"></i>प्रॉपर्टी सर्च
                    </a>
                </li>
                <li>
                    <a href="/customer/favorites" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/favorites') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-heart"></i>मेरे फेवरिट्स
                    </a>
                </li>
                <li>
                    <a href="/customer/bookings" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/bookings') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check"></i>मेरी बुकिंग्स
                    </a>
                </li>
                <li>
                    <a href="/customer/payments" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/payments') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-credit-card"></i>मेरे पेमेंट्स
                    </a>
                </li>
                <li>
                    <a href="/customer/reviews" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/reviews') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-star"></i>मेरे रिव्यूज
                    </a>
                </li>
                <li>
                    <a href="/customer/alerts" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/alerts') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-bell"></i>प्रॉपर्टी अलर्ट्स
                    </a>
                </li>
                <li>
                    <a href="/customer/emi-calculator" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/emi-calculator') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-calculator"></i>EMI कैल्कुलेटर
                    </a>
                </li>
                <li>
                    <a href="/customer/property-views" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/property-views') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-history"></i>व्यू हिस्ट्री
                    </a>
                </li>
                <li>
                    <a href="/customer/associate-benefits" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/associate-benefits') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>असोसिएट बेनेफिट्स
                    </a>
                </li>
                <li>
                    <a href="/customer/become-associate" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/become-associate') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-handshake"></i>असोसिएट बनें
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
                        <a class="dropdown-item" href="#">नई प्रॉपर्टी आपकी प्रेफरेंस में</a>
                        <a class="dropdown-item" href="#">बुकिंग कन्फर्मेशन</a>
                        <a class="dropdown-item" href="#">पेमेंट अपडेट</a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link text-muted" type="button" data-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg mr-2"></i>
                        <?= $_SESSION['customer_name'] ?? 'Customer' ?>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="/customer/profile">
                            <i class="fas fa-user mr-2"></i>मेरा प्रोफाइल
                        </a>
                        <a class="dropdown-item" href="/customer/settings">
                            <i class="fas fa-cog mr-2"></i>सेटिंग्स
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/customer/logout">
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
    <footer class="customer-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 APS Dream Home. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p>Customer Portal v1.0</p>
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
    </script>
</body>
</html>
