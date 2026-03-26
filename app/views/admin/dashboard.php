<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/login');
    exit;
}

// Get current page context
$current_section = $_GET['section'] ?? 'dashboard';
$current_module = $_GET['module'] ?? 'overview';

// Set page variables
$page_title = 'SuperAdmin Control Center - APS Dream Home';
$admin_layout = true;  // tells base.php to skip public header/footer
$active_page = 'dashboard';

// Define full admin sections for super_admin
$all_sections = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'modules' => [
            'overview' => ['title' => 'Overview', 'icon' => 'fas fa-chart-pie', 'url' => '/admin/dashboard'],
            'analytics' => ['title' => 'Analytics', 'icon' => 'fas fa-chart-line', 'url' => '/admin/analytics'],
            'reports' => ['title' => 'Reports', 'icon' => 'fas fa-file-alt', 'url' => '/admin/reports'],
            'activity' => ['title' => 'Activity Log', 'icon' => 'fas fa-history', 'url' => '/admin/logs']
        ]
    ],
    'crm' => [
        'title' => 'CRM & Leads',
        'icon' => 'fas fa-bullseye',
        'modules' => [
            'leads' => ['title' => 'All Leads', 'icon' => 'fas fa-users', 'url' => '/admin/leads'],
            'followups' => ['title' => 'Follow Ups', 'icon' => 'fas fa-calendar-check', 'url' => '/admin/followups'],
            'customers' => ['title' => 'Customers', 'icon' => 'fas fa-user-check', 'url' => '/admin/customers'],
            'campaigns' => ['title' => 'Campaigns', 'icon' => 'fas fa-bullhorn', 'url' => '/admin/campaigns']
        ]
    ],
    'properties' => [
        'title' => 'Properties & Inventory',
        'icon' => 'fas fa-city',
        'modules' => [
            'projects' => ['title' => 'Projects', 'icon' => 'fas fa-building', 'url' => '/admin/projects'],
            'plots' => ['title' => 'Plots / Land', 'icon' => 'fas fa-map', 'url' => '/admin/plots'],
            'residential' => ['title' => 'Residential', 'icon' => 'fas fa-home', 'url' => '/admin/residential'],
            'commercial' => ['title' => 'Commercial', 'icon' => 'fas fa-store', 'url' => '/admin/commercial']
        ]
    ],
    'mlm' => [
        'title' => 'MLM Network',
        'icon' => 'fas fa-sitemap',
        'modules' => [
            'network' => ['title' => 'Network Tree', 'icon' => 'fas fa-project-diagram', 'url' => '/admin/network'],
            'associates' => ['title' => 'Associates', 'icon' => 'fas fa-user-tie', 'url' => '/admin/associates'],
            'ranks' => ['title' => 'Rank & Tiers', 'icon' => 'fas fa-medal', 'url' => '/admin/ranks'],
            'performance' => ['title' => 'Performance', 'icon' => 'fas fa-chart-bar', 'url' => '/admin/performance']
        ]
    ],
    'financial' => [
        'title' => 'Financial',
        'icon' => 'fas fa-rupee-sign',
        'modules' => [
            'bookings' => ['title' => 'Bookings', 'icon' => 'fas fa-file-contract', 'url' => '/admin/bookings'],
            'transactions' => ['title' => 'Transactions', 'icon' => 'fas fa-exchange-alt', 'url' => '/admin/transactions'],
            'commissions' => ['title' => 'Commissions', 'icon' => 'fas fa-percentage', 'url' => '/admin/commissions'],
            'invoices' => ['title' => 'Invoices', 'icon' => 'fas fa-file-invoice-dollar', 'url' => '/admin/invoices']
        ]
    ],
    'hr' => [
        'title' => 'Team & HR',
        'icon' => 'fas fa-users-cog',
        'modules' => [
            'staff' => ['title' => 'Staff Members', 'icon' => 'fas fa-user-friends', 'url' => '/admin/staff'],
            'roles' => ['title' => 'Roles & Access', 'icon' => 'fas fa-user-shield', 'url' => '/admin/roles'],
            'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-clock', 'url' => '/admin/attendance']
        ]
    ],
    'content' => [
        'title' => 'Content Management',
        'icon' => 'fas fa-images',
        'modules' => [
            'gallery' => ['title' => 'Gallery / Media', 'icon' => 'fas fa-image', 'url' => '/admin/gallery'],
            'blog' => ['title' => 'Blog & News', 'icon' => 'fas fa-newspaper', 'url' => '/admin/blog'],
            'testimonials' => ['title' => 'Testimonials', 'icon' => 'fas fa-quote-left', 'url' => '/admin/testimonials']
        ]
    ],
    'system' => [
        'title' => 'System & Settings',
        'icon' => 'fas fa-cogs',
        'modules' => [
            'settings' => ['title' => 'General Settings', 'icon' => 'fas fa-cog', 'url' => '/admin/settings'],
            'api' => ['title' => 'API Integrations', 'icon' => 'fas fa-code', 'url' => '/admin/api'],
            'ai_hub' => ['title' => 'AI Features', 'icon' => 'fas fa-robot', 'url' => '/admin/ai-hub'],
            'backup' => ['title' => 'Backup & Logs', 'icon' => 'fas fa-save', 'url' => '/admin/backup']
        ]
    ]
];

// Role-based filtering
$user_role = $_SESSION['admin_role'] ?? 'admin';
if ($user_role === 'super_admin') {
    $admin_sections = $all_sections; // Super admin sees everything
} else {
    // Regular admin sees limited sections
    $allowed_sections = ['dashboard', 'crm', 'properties', 'content'];
    $admin_sections = array_intersect_key($all_sections, array_flip($allowed_sections));
}

// Content for base layout
ob_start();
?>

<!-- Enterprise Admin Dashboard -->
<div class="enterprise-admin-dashboard">
    <!-- Top Navigation Bar -->
    <nav class="admin-top-navbar">
        <div class="navbar-content">
            <div class="navbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="brand-logo">
                    <i class="fas fa-crown text-warning"></i>
                    <span><?= ucfirst(str_replace('_', ' ', $_SESSION['admin_role'] ?? 'Admin')) ?></span>
                    <small class="text-muted">Control Center</small>
                </div>
            </div>

            <div class="navbar-center">
                <div class="quick-search">
                    <input type="text" placeholder="Quick search anything..." class="form-control">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="navbar-right">
                <div class="admin-info">
                    <div class="admin-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin') ?>&background=2c3e50&color=fff" alt="Admin">
                    </div>
                    <div class="admin-details">
                        <div class="admin-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'System Admin') ?></div>
                        <div class="admin-role"><?= ucfirst(str_replace('_', ' ', $_SESSION['admin_role'] ?? 'Administrator')) ?></div>
                    </div>
                </div>

                <div class="navbar-actions">
                    <button class="btn btn-outline-light" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger">3</span>
                    </button>
                    <button class="btn btn-outline-light" onclick="toggleTheme()">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-th"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content-area">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h6>Navigation</h6>
            </div>

            <nav class="sidebar-nav">
                <?php foreach ($admin_sections as $section_key => $section): ?>
                    <div class="nav-section">
                        <div class="nav-section-header" onclick="toggleNavSection('<?php echo $section_key; ?>')">
                            <i class="<?php echo $section['icon']; ?>"></i>
                            <span><?php echo $section['title']; ?></span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </div>

                        <div class="nav-section-content" id="nav-<?php echo $section_key; ?>">
                            <?php foreach ($section['modules'] as $module_key => $module): ?>
                                <a href="?section=<?php echo $section_key; ?>&module=<?php echo $module_key; ?>"
                                    class="nav-item <?php echo ($current_section === $section_key && $current_module === $module_key) ? 'active' : ''; ?>">
                                    <i class="<?php echo $module['icon']; ?>"></i>
                                    <span><?php echo $module['title']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="system-info">
                    <small class="text-muted">System Version: 2.0.1</small>
                    <small class="text-muted">Last Backup: 2 hours ago</small>
                </div>
            </div>
        </aside>

        <!-- Content Area -->
        <main class="admin-content">
            <!-- Breadcrumb -->
            <nav class="admin-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="?section=dashboard&module=overview">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <?php if (isset($admin_sections[$current_section])): ?>
                        <li class="breadcrumb-item">
                            <a href="?section=<?php echo $current_section; ?>&module=overview">
                                <i class="<?php echo $admin_sections[$current_section]['icon']; ?>"></i>
                                <?php echo $admin_sections[$current_section]['title']; ?>
                            </a>
                        </li>
                        <?php if (isset($admin_sections[$current_section]['modules'][$current_module])): ?>
                            <li class="breadcrumb-item active">
                                <i class="<?php echo $admin_sections[$current_section]['modules'][$current_module]['icon']; ?>"></i>
                                <?php echo $admin_sections[$current_section]['modules'][$current_module]['title']; ?>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ol>
            </nav>

            <!-- Module Content -->
            <div class="module-content">
                <?php
                // Load module content based on current section and module
                $module_file = __DIR__ . "/modules/{$current_section}/{$current_module}.php";
                if (file_exists($module_file)) {
                    include $module_file;
                } else {
                    // Check if this is a direct page request (like properties.php)
                    if (strpos($_SERVER['REQUEST_URI'], '/admin/properties') !== false) {
                        include __DIR__ . '/modules/properties/residential.php';
                    } elseif (strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false) {
                        // Show default dashboard content
                        echo '<div class="welcome-module">';
                        echo '<div class="module-header">';
                        echo '<h3><i class="fas fa-rocket"></i> Welcome to SuperAdmin Control Center</h3>';
                        echo '<p class="text-muted">Select a module from sidebar to get started</p>';
                        echo '</div>';

                        echo '<div class="quick-stats-grid">';
                        echo '<div class="stat-card bg-primary">';
                        echo '<div class="stat-icon"><i class="fas fa-home"></i></div>';
                        echo '<div class="stat-content"><h4>156</h4><p>Total Properties</p></div>';
                        echo '</div>';

                        echo '<div class="stat-card bg-success">';
                        echo '<div class="stat-icon"><i class="fas fa-users"></i></div>';
                        echo '<div class="stat-content"><h4>1,234</h4><p>Total Users</p></div>';
                        echo '</div>';

                        echo '<div class="stat-card bg-warning">';
                        echo '<div class="stat-icon"><i class="fas fa-handshake"></i></div>';
                        echo '<div class="stat-content"><h4>89</h4><p>Active Deals</p></div>';
                        echo '</div>';

                        echo '<div class="stat-card bg-info">';
                        echo '<div class="stat-icon"><i class="fas fa-chart-line"></i></div>';
                        echo '<div class="stat-content"><h4>94%</h4><p>System Health</p></div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        // Show default dashboard
                        echo '<div class="welcome-module">';
                        echo '<div class="module-header">';
                        echo '<h3><i class="fas fa-rocket"></i> Welcome to SuperAdmin Control Center</h3>';
                        echo '<p class="text-muted">Select a module from sidebar to get started</p>';
                        echo '</div>';

                        echo '<div class="quick-stats-grid">';
                        echo '<div class="stat-card bg-primary">';
                        echo '<div class="stat-icon"><i class="fas fa-home"></i></div>';
                        echo '<div class="stat-content"><h4>156</h4><p>Total Properties</p></div>';
                        echo '</div>';

                        echo '<div class="stat-card bg-success">';
                        echo '<div class="stat-icon"><i class="fas fa-users"></i></div>';
                        echo '<div class="stat-content"><h4>1,234</h4><p>Total Users</p></div>';
                        echo '</div>';

                        echo '<div class="stat-card bg-warning">';
                        echo '<div class="stat-icon"><i class="fas fa-handshake"></i></div>';
                        echo '<div class="stat-content"><h4>89</h4><p>Active Deals</p></div>';
                        echo '</div>';

                        echo '<div class="stat-card bg-info">';
                        echo '<div class="stat-icon"><i class="fas fa-chart-line"></i></div>';
                        echo '<div class="stat-content"><h4>94%</h4><p>System Health</p></div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </main>
    </div>
</div>

<!-- Enterprise Admin Styles -->
<style>
    .enterprise-admin-dashboard {
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
    }

    /* Top Navigation */
    .admin-top-navbar {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .navbar-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1.5rem;
        height: 65px;
    }

    .navbar-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sidebar-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
        transition: var(--transition);
    }

    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .brand-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .brand-logo i {
        font-size: 1.5rem;
    }

    .brand-logo span {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .brand-logo small {
        font-size: 0.7rem;
        opacity: 0.8;
    }

    .navbar-center {
        flex: 1;
        max-width: 400px;
    }

    .quick-search {
        display: flex;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 25px;
        overflow: hidden;
    }

    .quick-search input {
        border: none;
        background: transparent;
        color: white;
        padding: 0.5rem 1rem;
        flex: 1;
    }

    .quick-search input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .quick-search button {
        border: none;
        background: transparent;
        color: white;
        padding: 0.5rem 1rem;
    }

    .navbar-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .admin-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .admin-avatar img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .admin-details .admin-name {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .admin-details .admin-role {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .navbar-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-actions .btn {
        position: relative;
        padding: 0.5rem 0.75rem;
    }

    .navbar-actions .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 0.6rem;
        padding: 2px 5px;
    }

    /* Main Content Area */
    .main-content-area {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    /* Sidebar */
    .admin-sidebar {
        width: 280px;
        background: white;
        border-right: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
    }

    .admin-sidebar.collapsed {
        width: 70px;
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .sidebar-header h6 {
        margin: 0;
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 600;
    }

    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 0;
    }

    .nav-section {
        margin-bottom: 0.5rem;
    }

    .nav-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1.5rem;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 600;
        color: #495057;
    }

    .nav-section-header:hover {
        background: #f8f9fa;
    }

    .nav-section-header i:first-child {
        width: 20px;
        text-align: center;
    }

    .nav-arrow {
        transition: var(--transition);
        font-size: 0.8rem;
    }

    .nav-section.expanded .nav-arrow {
        transform: rotate(180deg);
    }

    .nav-section-content {
        background: #f8f9fa;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .nav-section.expanded .nav-section-content {
        max-height: 500px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem 0.75rem 3rem;
        color: #6c757d;
        text-decoration: none;
        transition: var(--transition);
        border-left: 3px solid transparent;
    }

    .nav-item:hover {
        background: #e9ecef;
        color: #495057;
        text-decoration: none;
    }

    .nav-item.active {
        background: #007bff;
        color: white;
        border-left-color: #0056b3;
    }

    .nav-item i {
        width: 20px;
        text-align: center;
        margin-right: 0.75rem;
    }

    .sidebar-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    .system-info small {
        display: block;
        font-size: 0.7rem;
    }

    /* Main Content */
    .admin-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .admin-breadcrumb {
        background: white;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .breadcrumb {
        background: transparent;
        margin: 0;
        padding: 0;
    }

    .breadcrumb-item {
        font-size: 0.9rem;
    }

    .module-content {
        flex: 1;
        overflow-y: auto;
        padding: 2rem;
    }

    /* Welcome Module */
    .welcome-module {
        text-align: center;
        padding: 3rem 2rem;
    }

    .module-header h3 {
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .quick-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-card.bg-primary {
        border-left: 4px solid #007bff;
    }

    .stat-card.bg-success {
        border-left: 4px solid #28a745;
    }

    .stat-card.bg-warning {
        border-left: 4px solid #ffc107;
    }

    .stat-card.bg-info {
        border-left: 4px solid #17a2b8;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .stat-card.bg-primary .stat-icon {
        background: #007bff;
    }

    .stat-card.bg-success .stat-icon {
        background: #28a745;
    }

    .stat-card.bg-warning .stat-icon {
        background: #ffc107;
    }

    .stat-card.bg-info .stat-icon {
        background: #17a2b8;
    }

    .stat-content h4 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-content p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .navbar-center {
            display: none;
        }

        .admin-sidebar {
            position: fixed;
            left: -280px;
            top: 65px;
            height: calc(100vh - 65px);
            z-index: 999;
        }

        .admin-sidebar.active {
            left: 0;
        }

        .admin-content {
            margin-left: 0;
        }

        .quick-stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Enterprise Admin JavaScript -->
<script>
    let sidebarCollapsed = false;

    document.addEventListener('DOMContentLoaded', function() {
        initializeAdminDashboard();
        setActiveNavigation();
    });

    function initializeAdminDashboard() {
        console.log('Enterprise Admin Dashboard initialized');

        // Auto-expand first section
        const firstSection = document.querySelector('.nav-section');
        if (firstSection) {
            firstSection.classList.add('expanded');
        }

        // Set current active navigation
        setActiveNavigation();
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        sidebarCollapsed = !sidebarCollapsed;

        if (sidebarCollapsed) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }
    }

    function toggleNavSection(sectionId) {
        const section = document.getElementById(`nav-${sectionId}`);
        const header = section.previousElementSibling;

        // Close all other sections
        document.querySelectorAll('.nav-section').forEach(nav => {
            nav.classList.remove('expanded');
        });

        // Toggle current section
        if (section) {
            header.parentElement.classList.toggle('expanded');
        }
    }

    function setActiveNavigation() {
        const currentSection = '<?php echo $current_section; ?>';
        const currentModule = '<?php echo $current_module; ?>';

        // Expand current section
        const currentNavSection = document.getElementById(`nav-${currentSection}`);
        if (currentNavSection) {
            currentNavSection.parentElement.classList.add('expanded');
        }

        // Set active nav item
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });

        const activeItem = document.querySelector(`[href*="section=${currentSection}&module=${currentModule}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
        }
    }

    function toggleNotifications() {
        alert('Notifications panel would open here');
    }

    function toggleTheme() {
        document.body.classList.toggle('dark-theme');
        console.log('Theme toggled');
    }

    // Mobile sidebar handling
    if (window.innerWidth <= 768) {
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('adminSidebar');
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isToggle = e.target.classList.contains('sidebar-toggle');

            if (!isClickInsideSidebar && !isToggle) {
                sidebar.classList.remove('active');
            }
        });
    }
</script>

<?php
$content = ob_get_clean();

// Include base layout
require_once __DIR__ . '/../layouts/base.php';
?>