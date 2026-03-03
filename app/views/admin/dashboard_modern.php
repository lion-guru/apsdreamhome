<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="http://localhost.//public/css/dashboard.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }

        .sidebar-logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .sidebar-logo h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 25px;
        }

        .menu-title {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 20px;
            margin-bottom: 10px;
        }

        .menu-item {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 25px;
        }

        .menu-item.active {
            background: var(--secondary-color);
            color: white;
        }

        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: white;
        }

        .menu-item i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f8f9fa;
        }

        /* Header */
        .header {
            background: white;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-bar {
            position: relative;
            width: 300px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .search-bar i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 20px;
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .notification-btn:hover {
            color: var(--secondary-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .user-menu:hover {
            background: #e9ecef;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            line-height: 1;
        }

        .user-role {
            font-size: 12px;
            color: #666;
            line-height: 1;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--secondary-color);
        }

        .stat-card.success::before { background: var(--success-color); }
        .stat-card.warning::before { background: var(--warning-color); }
        .stat-card.danger::before { background: var(--danger-color); }
        .stat-card.info::before { background: var(--info-color); }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.primary { background: rgba(52, 152, 219, 0.1); color: var(--secondary-color); }
        .stat-icon.success { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .stat-icon.warning { background: rgba(243, 156, 18, 0.1); color: var(--warning-color); }
        .stat-icon.danger { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
        .stat-icon.info { background: rgba(23, 162, 184, 0.1); color: var(--info-color); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .stat-change {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .stat-change.positive {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .stat-change.negative {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .chart-options {
            display: flex;
            gap: 10px;
        }

        .chart-option {
            padding: 6px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            background: white;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chart-option:hover,
        .chart-option.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 10px;
            position: relative;
        }

        .chart-placeholder {
            text-align: center;
            color: #999;
        }

        /* Recent Activity */
        .activity-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: start;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .activity-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .activity-time {
            color: #999;
            font-size: 12px;
        }

        /* Mobile Responsive */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #333;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .search-bar {
                width: 200px;
            }

            .user-info {
                display: none;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-content {
                padding: 20px;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-logo">
                <img src="http://localhost.//public/assets/images/logo/apslogo.png" alt="APS Dream Home">
                <h3>APS Dream Home</h3>
            </a>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-title">Dashboard</div>
                <a href="#" class="menu-item active">
                    <i class="bi bi-speedometer2"></i>
                    Overview
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-graph-up"></i>
                    Analytics
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-calendar3"></i>
                    Calendar
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Properties</div>
                <a href="#" class="menu-item">
                    <i class="bi bi-house"></i>
                    All Properties
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-plus-circle"></i>
                    Add Property
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-star"></i>
                    Featured
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-eye"></i>
                    Virtual Tours
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Customers & Leads</div>
                <a href="#" class="menu-item">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-person-plus"></i>
                    Leads
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-chat-dots"></i>
                    Communications
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-funnel"></i>
                    Campaigns
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Financial</div>
                <a href="#" class="menu-item">
                    <i class="bi bi-receipt"></i>
                    Invoices
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-credit-card"></i>
                    Payments
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-graph-down"></i>
                    Reports
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-calculator"></i>
                    Budget
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Employees</div>
                <a href="#" class="menu-item">
                    <i class="bi bi-person-badge"></i>
                    Staff
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-clock-history"></i>
                    Attendance
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-cash-stack"></i>
                    Payroll
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-trophy"></i>
                    Performance
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">System</div>
                <a href="#" class="menu-item">
                    <i class="bi bi-gear"></i>
                    Settings
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-shield-check"></i>
                    Security
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-file-text"></i>
                    Reports
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="bi bi-list"></i>
                </button>
                
                <div class="search-bar">
                    <input type="text" placeholder="Search anything...">
                    <i class="bi bi-search"></i>
                </div>
            </div>

            <div class="header-right">
                <button class="notification-btn">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge">3</span>
                </button>

                <div class="user-menu">
                    <img src="http://localhost.//public/assets/images/user/default-avatar.jpg" alt="User" class="user-avatar">
                    <div class="user-info">
                        <span class="user-name">Admin User</span>
                        <span class="user-role">Administrator</span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="dashboard-content">
            <h1 class="page-title">Dashboard Overview</h1>
            <p class="page-subtitle">Welcome back! Here's what's happening with your business today.</p>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon primary">
                            <i class="bi bi-house"></i>
                        </div>
                    </div>
                    <div class="stat-value">248</div>
                    <div class="stat-label">Total Properties</div>
                    <span class="stat-change positive">
                        <i class="bi bi-arrow-up"></i> 12% from last month
                    </span>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon success">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="stat-value">1,429</div>
                    <div class="stat-label">Active Customers</div>
                    <span class="stat-change positive">
                        <i class="bi bi-arrow-up"></i> 8% from last month
                    </span>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon warning">
                            <i class="bi bi-person-plus"></i>
                        </div>
                    </div>
                    <div class="stat-value">89</div>
                    <div class="stat-label">New Leads</div>
                    <span class="stat-change negative">
                        <i class="bi bi-arrow-down"></i> 3% from last month
                    </span>
                </div>

                <div class="stat-card info">
                    <div class="stat-header">
                        <div class="stat-icon info">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                    </div>
                    <div class="stat-value">₹24.5L</div>
                    <div class="stat-label">Monthly Revenue</div>
                    <span class="stat-change positive">
                        <i class="bi bi-arrow-up"></i> 18% from last month
                    </span>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Revenue Overview</h3>
                        <div class="chart-options">
                            <button class="chart-option active">Week</button>
                            <button class="chart-option">Month</button>
                            <button class="chart-option">Year</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">
                            <i class="bi bi-bar-chart" style="font-size: 48px;"></i>
                            <p>Revenue Chart</p>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Property Status</h3>
                        <div class="chart-options">
                            <button class="chart-option active">All</button>
                            <button class="chart-option">Active</button>
                            <button class="chart-option">Sold</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">
                            <i class="bi bi-pie-chart" style="font-size: 48px;"></i>
                            <p>Property Distribution</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-card">
                <div class="chart-header">
                    <h3 class="chart-title">Recent Activity</h3>
                    <button class="chart-option">View All</button>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--secondary-color);">
                            <i class="bi bi-house-plus"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">New Property Added</div>
                            <div class="activity-description">Luxury Villa in Mumbai added to inventory</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Lead Converted</div>
                            <div class="activity-description">John Doe converted to customer for Property #1234</div>
                            <div class="activity-time">4 hours ago</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Invoice Generated</div>
                            <div class="activity-description">Invoice #INV-2024-001 generated for ₹12,50,000</div>
                            <div class="activity-time">6 hours ago</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon" style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color);">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Meeting Scheduled</div>
                            <div class="activity-description">Property viewing scheduled for tomorrow at 3:00 PM</div>
                            <div class="activity-time">8 hours ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost.//public/js/dashboard.js"></script>
    <script>
        // Mobile Menu Toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Menu Item Active State
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Chart Options
        document.querySelectorAll('.chart-option').forEach(option => {
            option.addEventListener('click', function() {
                const parent = this.parentElement;
                parent.querySelectorAll('.chart-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Simulate Real-time Updates
        function updateStats() {
            // This would connect to your backend API
            console.log('Updating dashboard stats...');
        }

        // Update stats every 30 seconds
        setInterval(updateStats, 30000);

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>
