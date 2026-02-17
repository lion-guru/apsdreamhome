<?php
// Simple navigation menu for admin
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">
            <i class="fas fa-home me-2"></i>APS Admin
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                
                <!-- MLM Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="mlmDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users"></i> MLM System
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="mlmDropdown">
                        <li><a class="dropdown-item" href="mlm_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> MLM Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="mlm_associates.php">
                            <i class="fas fa-users"></i> Associates Management
                        </a></li>
                        <li><a class="dropdown-item" href="mlm_commissions.php">
                            <i class="fas fa-money-bill-wave"></i> Commissions
                        </a></li>
                        <li><a class="dropdown-item" href="mlm_salary.php">
                            <i class="fas fa-hand-holding-usd"></i> Salary Management
                        </a></li>
                        <li><a class="dropdown-item" href="mlm_payouts.php">
                            <i class="fas fa-credit-card"></i> Payouts
                        </a></li>
                        <li><a class="dropdown-item" href="mlm_reports.php">
                            <i class="fas fa-chart-bar"></i> Reports & Analytics
                        </a></li>
                        <li><a class="dropdown-item" href="mlm_settings.php">
                            <i class="fas fa-cog"></i> MLM Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="network_inspector.php">
                            <i class="fas fa-sitemap"></i> Network Inspector
                        </a></li>
                        <li><a class="dropdown-item" href="commission_agreements.php">
                            <i class="fas fa-file-contract"></i> Commission Agreements
                        </a></li>
                        <li><a class="dropdown-item" href="mlm_notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                        </a></li>
                    </ul>
                </li>
                
                <!-- Property Management -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="propertyDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-building"></i> Properties
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="properties.php">
                            <i class="fas fa-list"></i> All Properties
                        </a></li>
                        <li><a class="dropdown-item" href="bookings.php">
                            <i class="fas fa-calendar"></i> Bookings
                        </a></li>
                        <li><a class="dropdown-item" href="customers.php">
                            <i class="fas fa-users"></i> Customers
                        </a></li>
                    </ul>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield"></i> Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a></li>
                        <li><a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-2">
    <div class="container-fluid">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="admin_dashboard.php">Home</a>
            </li>
            <?php
            // Dynamic breadcrumb based on current page
            $current_page = basename($_SERVER['PHP_SELF']);
            $page_titles = [
                'mlm_dashboard.php' => 'MLM Dashboard',
                'mlm_associates.php' => 'Associates',
                'mlm_commissions.php' => 'Commissions',
                'mlm_salary.php' => 'Salary Management',
                'mlm_payouts.php' => 'Payouts',
                'mlm_reports.php' => 'MLM Reports',
                'mlm_settings.php' => 'MLM Settings'
            ];
            
            if (isset($page_titles[$current_page])) {
                echo '<li class="breadcrumb-item active" aria-current="page">' . $page_titles[$current_page] . '</li>';
            }
            ?>
        </ol>
    </div>
</nav>
