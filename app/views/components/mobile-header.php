<?php

// TODO: Add proper error handling with try-catch blocks

/**
 * APS Dream Home - Mobile Optimized Header Component
 * Modern responsive header with mobile navigation for all user types
 */

// Check if user is authenticated
$isAuthenticated = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['role'] ?? '';
$userAvatar = $_SESSION['user_avatar'] ?? '';
?>

<header class="bg-white shadow-sm sticky top-0 z-50">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Mobile Header -->
    <div class="mobile-header d-lg-none">
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-2 mx-auto">
            <img src="<?= BASE_URL ?>/assets/images/logo/apslogonew.jpg" class="img-fluid" alt="htmlspecialchars(__('aps_dream_home', 'APS Dream Home'))" class="style-2609" onerror="this.style.display='none'">
            <span class="text-lg font-bold text-primary">__('aps_dream_home', 'APS Dream Home')</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <?php if ($isAuthenticated): ?>
                <div class="dropdown">
                    <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="dropdown">
                        <img src="<?= !empty($userAvatar) ? htmlspecialchars($userAvatar) : (BASE_URL . '/assets/images/logo/apslogonew.jpg') ?>" class="img-fluid"
                             alt="Profile" class="rounded-circle" class="style-35333">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?php echo htmlspecialchars($userName); ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if ($userRole === 'associate'): ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>associate/dashboard">
                                <i class="bi bi-house-door me-2"></i>Associate Dashboard
                            </a></li>
                        <?php elseif ($userRole === 'agent'): ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>agent/dashboard">
                                <i class="bi bi-person-badge me-2"></i>Agent Dashboard
                            </a></li>
                        <?php elseif ($userRole === 'customer'): ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/dashboard">
                                <i class="bi bi-person-circle me-2"></i>Customer Dashboard
                            </a></li>
                        <?php elseif ($userRole === 'employee'): ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>employee/dashboard">
                                <i class="bi bi-building me-2"></i>Employee Dashboard
                            </a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile">
                            <i class="bi bi-person me-2"></i>Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?php echo BASE_URL; ?>logout" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login" class="btn btn-outline-primary btn-sm">__('component_login', 'Login')</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Desktop Header -->
    <div class="d-none d-lg-block">
        <div class="container-fluid px-4 py-3">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-lg-3">
                    <a href="<?php echo BASE_URL; ?>" class="d-flex align-items-center text-decoration-none">
                        <img src="<?= BASE_URL ?>/assets/images/logo/apslogonew.jpg" class="me-2" alt="APS Dream Home" class="style-92690" onerror="this.style.display='none'">
                        <span class="h5 mb-0 text-primary fw-bold">APS Dream Home</span>
                    </a>
                </div>

                <!-- Navigation -->
                <div class="col-lg-6">
                    <nav class="navbar navbar-expand-lg navbar-light p-0">
                        <div class="navbar-nav mx-auto">
                            <a href="<?php echo BASE_URL; ?>" class="nav-link px-3 <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">__('component_home', 'Home')</a>

                            <?php if ($isAuthenticated): ?>
                                <?php if ($userRole === 'associate'): ?>
                                    <a href="<?php echo BASE_URL; ?>associate/dashboard" class="nav-link px-3">Dashboard</a>
                                    <a href="<?php echo BASE_URL; ?>associate/team" class="nav-link px-3">Team</a>
                                    <a href="<?php echo BASE_URL; ?>associate/commissions" class="nav-link px-3">__('component_commissions', 'Commissions')</a>
                                <?php elseif ($userRole === 'agent'): ?>
                                    <a href="<?php echo BASE_URL; ?>agent/dashboard" class="nav-link px-3">Dashboard</a>
                                    <a href="<?php echo BASE_URL; ?>properties" class="nav-link px-3">__('component_properties', 'Properties')</a>
                                    <a href="<?php echo BASE_URL; ?>agent/leads" class="nav-link px-3">__('component_leads', 'Leads')</a>
                                <?php elseif ($userRole === 'customer'): ?>
                                    <a href="<?php echo BASE_URL; ?>user/dashboard" class="nav-link px-3">Dashboard</a>
                                    <a href="<?php echo BASE_URL; ?>properties" class="nav-link px-3">__('component_properties', 'Properties')</a>
                                    <a href="<?php echo BASE_URL; ?>user/saved-searches" class="nav-link px-3">__('component_my_inquiries', 'My Inquiries')</a>
                                <?php elseif ($userRole === 'employee'): ?>
                                    <a href="<?php echo BASE_URL; ?>employee/dashboard" class="nav-link px-3">Dashboard</a>
                                    <a href="<?php echo BASE_URL; ?>employee/tasks" class="nav-link px-3">__('component_tasks', 'Tasks')</a>
                                    <a href="<?php echo BASE_URL; ?>employee/attendance" class="nav-link px-3">__('component_attendance', 'Attendance')</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>properties" class="nav-link px-3">__('component_properties', 'Properties')</a>
                                <a href="#about" class="nav-link px-3">__('component_about', 'About')</a>
                                <a href="#contact" class="nav-link px-3">__('component_contact', 'Contact')</a>
                            <?php endif; ?>
                        </div>
                    </nav>
                </div>

                <!-- User Menu -->
                <div class="col-lg-3 text-end">
                    <?php if ($isAuthenticated): ?>
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none d-flex align-items-center ms-auto p-0" type="button" data-bs-toggle="dropdown">
                                <img src="<?= !empty($userAvatar) ? htmlspecialchars($userAvatar) : (BASE_URL . '/assets/images/logo/apslogonew.jpg') ?>" class="rounded-circle me-2"
                                     alt="Profile" class="style-58830">
                                <div class="text-start d-none d-md-block">
                                    <div class="fw-bold small"><?php echo htmlspecialchars($userName); ?></div>
                                    <div class="text-muted small">
                                        <?php echo ucfirst($userRole); ?>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <?php if ($userRole === 'associate'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>associate/dashboard">
                                        <i class="bi bi-house-door me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>associate/team">
                                        <i class="bi bi-people me-2"></i>Team
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>associate/commissions">
                                        <i class="bi bi-cash me-2"></i>Commissions
                                    </a></li>
                                <?php elseif ($userRole === 'agent'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>agent/dashboard">
                                        <i class="bi bi-person-badge me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>properties">
                                        <i class="bi bi-house me-2"></i>Properties
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>agent/leads">
                                        <i class="bi bi-person-lines-fill me-2"></i>Leads
                                    </a></li>
                                <?php elseif ($userRole === 'customer'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/dashboard">
                                        <i class="bi bi-person-circle me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>properties">
                                        <i class="bi bi-house me-2"></i>Properties
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/saved-searches">
                                        <i class="bi bi-envelope me-2"></i>My Inquiries
                                    </a></li>
                                <?php elseif ($userRole === 'employee'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>employee/dashboard">
                                        <i class="bi bi-building me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>employee/tasks">
                                        <i class="bi bi-list-check me-2"></i>Tasks
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>employee/attendance">
                                        <i class="bi bi-calendar-check me-2"></i>Attendance
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile">
                                    <i class="bi bi-person me-2"></i>Profile Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="<?php echo BASE_URL; ?>logout" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="<?php echo BASE_URL; ?>login" class="btn btn-outline-primary">__('component_login', 'Login')</a>
                            <a href="<?php echo BASE_URL; ?>register" class="btn btn-primary">__('component_register_btn', 'Register')</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Navigation -->
    <div class="sidebar d-lg-none" id="mobileSidebar">
        <div class="p-3">
            <!-- User Info -->
            <?php if ($isAuthenticated): ?>
                <div class="text-center mb-4 pb-3 border-bottom">
                    <img src="<?= !empty($userAvatar) ? htmlspecialchars($userAvatar) : (BASE_URL . '/assets/images/logo/apslogonew.jpg') ?>" class="rounded-circle mb-2"
                         alt="Profile" class="style-12174">
                    <div class="fw-bold"><?php echo htmlspecialchars($userName); ?></div>
                    <small class="text-muted"><?php echo ucfirst($userRole); ?></small>
                </div>
            <?php endif; ?>

            <!-- Navigation Menu -->
            <nav class="navbar navbar-light p-0">
                <ul class="navbar-nav w-100">
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>" class="nav-link">
                            <i class="bi bi-house-door me-2"></i>Home
                        </a>
                    </li>

                    <?php if ($isAuthenticated): ?>
                        <?php if ($userRole === 'associate'): ?>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>associate/dashboard" class="nav-link">
                                    <i class="bi bi-house-door me-2"></i>Associate Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>associate/team" class="nav-link">
                                    <i class="bi bi-people me-2"></i>My Team
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>associate/commissions" class="nav-link">
                                    <i class="bi bi-cash me-2"></i>Commissions
                                </a>
                            </li>
                        <?php elseif ($userRole === 'agent'): ?>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>agent/dashboard" class="nav-link">
                                    <i class="bi bi-person-badge me-2"></i>Agent Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>properties" class="nav-link">
                                    <i class="bi bi-house me-2"></i>Properties
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>agent/leads" class="nav-link">
                                    <i class="bi bi-person-lines-fill me-2"></i>My Leads
                                </a>
                            </li>
                        <?php elseif ($userRole === 'customer'): ?>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>customer/dashboard" class="nav-link">
                                    <i class="bi bi-person-circle me-2"></i>Customer Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>customer/properties" class="nav-link">
                                    <i class="bi bi-house me-2"></i>Browse Properties
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>customer/inquiries" class="nav-link">
                                    <i class="bi bi-envelope me-2"></i>My Inquiries
                                </a>
                            </li>
                        <?php elseif ($userRole === 'employee'): ?>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>employee/dashboard" class="nav-link">
                                    <i class="bi bi-building me-2"></i>Employee Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>employee/tasks" class="nav-link">
                                    <i class="bi bi-list-check me-2"></i>My Tasks
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>employee/attendance" class="nav-link">
                                    <i class="bi bi-calendar-check me-2"></i>Attendance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo BASE_URL; ?>employee/payroll" class="nav-link">
                                    <i class="bi bi-cash me-2"></i>Payroll
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>profile" class="nav-link">
                                <i class="bi bi-person me-2"></i>Profile Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="<?php echo BASE_URL; ?>logout">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>properties" class="nav-link">
                                <i class="bi bi-house me-2"></i>Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#about" class="nav-link">
                                <i class="bi bi-info-circle me-2"></i>About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#contact" class="nav-link">
                                <i class="bi bi-envelope me-2"></i>Contact
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>login" class="nav-link">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>register" class="nav-link">
                                <i class="bi bi-person-plus me-2"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="d-lg-none" id="mobileOverlay" class="style-69131" onclick="closeMobileMenu()"></div>
</header>

<script>
// Mobile menu functionality
function toggleMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');
    const body = document.body;

    if (sidebar.classList.contains('show')) {
        closeMobileMenu();
    } else {
        sidebar.classList.add('show');
        overlay.style.display = 'block';
        body.style.overflow = 'hidden';
    }
}

function closeMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');
    const body = document.body;

    sidebar.classList.remove('show');
    overlay.style.display = 'none';
    body.style.overflow = 'auto';
}

// Close mobile menu when clicking on nav links
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('#mobileSidebar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Only close menu if it's not a dropdown toggle
            if (!this.hasAttribute('data-bs-toggle')) {
                closeMobileMenu();
            }
        });
    });
});
</script>
