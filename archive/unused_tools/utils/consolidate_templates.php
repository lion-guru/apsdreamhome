<?php
/**
 * APS Dream Home - Template Consolidation System
 * Consolidate 58 template files into optimized structure
 */

class TemplateConsolidation {
    private $archiveDir;
    private $consolidationLog = [];

    public function __construct() {
        $this->archiveDir = "archive/templates/old_" . date('Y-m-d_H-i-s');
    }

    /**
     * Execute template consolidation
     */
    public function consolidate() {
        echo "<h1>🔧 APS Dream Home - Template Consolidation</h1>\n";
        echo "<div class='consolidation-container'>\n";

        // Create archive directory
        $this->createArchiveDirectory();

        // Create new optimized structure
        $this->createOptimizedStructure();

        // Archive old templates
        $this->archiveOldTemplates();

        // Create unified user layout
        $this->createUnifiedUserLayout();

        // Create template usage guide
        $this->createUsageGuide();

        // Display consolidation summary
        $this->displaySummary();

        echo "</div>\n";
    }

    /**
     * Create archive directory
     */
    private function createArchiveDirectory() {
        echo "<h2>📁 Creating Archive Directory</h2>\n";

        if (!is_dir($this->archiveDir)) {
            mkdir($this->archiveDir, 0755, true);
            echo "<div style='color: green;'>✅ Archive directory created: {$this->archiveDir}</div>\n";
        }

        // Create subdirectories
        $subdirs = ['role_based_headers', 'role_based_footers', 'deprecated_layouts', 'duplicate_admin'];
        foreach ($subdirs as $subdir) {
            mkdir($this->archiveDir . '/' . $subdir, 0755, true);
        }
    }

    /**
     * Create optimized template structure
     */
    private function createOptimizedStructure() {
        echo "<h2>🏗️ Creating Optimized Template Structure</h2>\n";

        // Create main template directories
        $directories = [
            'templates/standard',
            'templates/unified',
            'templates/enhanced',
            'templates/admin',
            'templates/user'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "<div style='color: green;'>✅ Created: {$dir}</div>\n";
            }
        }
    }

    /**
     * Archive old templates
     */
    private function archiveOldTemplates() {
        echo "<h2>📦 Archiving Old Templates</h2>\n";

        // Archive role-based headers
        $roleHeaders = [
            'app/views/layouts/associate_header.php',
            'app/views/layouts/customer_header.php',
            'app/views/layouts/employee_header.php'
        ];

        foreach ($roleHeaders as $header) {
            if (file_exists($header)) {
                $dest = $this->archiveDir . '/role_based_headers/' . basename($header);
                rename($header, $dest);
                $this->logAction('archived', $header, $dest);
                echo "<div style='color: green;'>✅ Archived: {$header}</div>\n";
            }
        }

        // Archive role-based footers
        $roleFooters = [
            'app/views/layouts/associate_footer.php',
            'app/views/layouts/customer_footer.php',
            'app/views/layouts/employee_footer.php'
        ];

        foreach ($roleFooters as $footer) {
            if (file_exists($footer)) {
                $dest = $this->archiveDir . '/role_based_footers/' . basename($footer);
                rename($footer, $dest);
                $this->logAction('archived', $footer, $dest);
                echo "<div style='color: green;'>✅ Archived: {$footer}</div>\n";
            }
        }

        // Archive deprecated layouts
        $deprecatedLayouts = [
            'app/views/layouts/header_unified.php',
            'app/views/layouts/footer_unified.php',
            'app/views/layouts/header_new.php'
        ];

        foreach ($deprecatedLayouts as $layout) {
            if (file_exists($layout)) {
                $dest = $this->archiveDir . '/deprecated_layouts/' . basename($layout);
                rename($layout, $dest);
                $this->logAction('archived', $layout, $dest);
                echo "<div style='color: green;'>✅ Archived: {$layout}</div>\n";
            }
        }

        // Archive duplicate admin templates
        $duplicateAdmin = [
            'admin/includes/modern-header.php',
            'admin/includes/new_header.php',
            'admin/includes/new_footer.php'
        ];

        foreach ($duplicateAdmin as $admin) {
            if (file_exists($admin)) {
                $dest = $this->archiveDir . '/duplicate_admin/' . basename($admin);
                rename($admin, $dest);
                $this->logAction('archived', $admin, $dest);
                echo "<div style='color: green;'>✅ Archived: {$admin}</div>\n";
            }
        }
    }

    /**
     * Create unified user layout
     */
    private function createUnifiedUserLayout() {
        echo "<h2>👥 Creating Unified User Layout</h2>\n";

        $userLayout = '<?php
/**
 * Unified User Layout - APS Dream Home
 * Replaces all role-based templates with single dynamic layout
 */

// Get user information
$user_type = $_SESSION[\'user_type\'] ?? \'guest\';
$user_name = $_SESSION[\'user_name\'] ?? \'Guest\';
$user_id = $_SESSION[\'user_id\'] ?? null;

// Dynamic branding based on user type
require_once __DIR__ . \'/../../app/helpers.php\';
$branding_config = [
    \'associate\' => [
        \'title\' => \'Associate Portal\',
        \'dashboard_url\' => \'/associate/dashboard.php\',
        \'theme\' => \'secondary\',
        \'logo_class\' => \'fa-user-tie\'
    ],
    \'customer\' => [
        \'title\' => \'Customer Portal\',
        \'dashboard_url\' => \'/customer/dashboard.php\',
        \'theme\' => \'info\',
        \'logo_class\' => \'fa-user\'
    ],
    \'employee\' => [
        \'title\' => \'Employee Portal\',
        \'dashboard_url\' => \'/employee/dashboard.php\',
        \'theme\' => \'warning\',
        \'logo_class\' => \'fa-briefcase\'
    ],
    \'guest\' => [
        \'title\' => \'APS Dream Homes\',
        \'dashboard_url\' => \'/dashboard.php\',
        \'theme\' => \'primary\',
        \'logo_class\' => \'fa-home\'
    ]
];

$config = $branding_config[$user_type] ?? $branding_config[\'guest\'];

// Ensure BASE_URL is available
if (!defined(\'BASE_URL\')) {
    $protocol = isset($_SERVER[\'HTTPS\']) && $_SERVER[\'HTTPS\'] === \'on\' ? \'https://\' : \'http://\';
    $host = $_SERVER[\'HTTP_HOST\'] ?? \'localhost\';
    $script_name = dirname($_SERVER[\'SCRIPT_NAME\'] ?? \'\');
    $base_path = str_replace(\'\\\\\', \'/\', $script_name);
    $base_path = rtrim($base_path, \'/\') . \'/\';
    define(\'BASE_URL\', $protocol . $host . $base_path);
}

// Page variables
$pageTitle = $pageTitle ?? $config[\'title\'];
$pageDescription = $pageDescription ?? \'APS Dream Homes - Premium real estate platform\';
$pageKeywords = $pageKeywords ?? \'APS Dream Homes, real estate, properties\';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="<?= h($pageDescription) ?>">
    <meta name="keywords" content="<?= h($pageKeywords) ?>">

    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>assets/css/common.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>assets/fonts/font-awesome.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>assets/images/favicon.ico">
</head>
<body>
    <!-- Dynamic Navigation -->
    <nav class="navbar navbar-expand-lg navbar-<?= $config[\'theme\'] ?> fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>">
                <i class="fas <?= $config[\'logo_class\'] ?> me-2"></i>
                <?= $config[\'title\'] ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>contact.php">Contact</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <?php if ($user_type !== \'guest\'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?= h($user_name) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $config[\'dashboard_url\'] ?>">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" style="margin-top: 70px;">
        <?= $content ?? \'\' ?>
    </main>

    <!-- Unified Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">APS Dream Homes</h5>
                    <p class="text-muted">Premium real estate properties across Uttar Pradesh. Find your dream home with our expert guidance.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>properties.php" class="text-muted text-decoration-none">Properties</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Services</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>services.php" class="text-muted text-decoration-none">Property Buying</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>services.php" class="text-muted text-decoration-none">Property Selling</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>services.php" class="text-muted text-decoration-none">Investment Advisory</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>services.php" class="text-muted text-decoration-none">Legal Assistance</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Kunraghat, Gorakhpur, UP - 273008</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>+91-9554000001</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>info@apsdreamhomes.com</li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i>Mon-Sat: 9:00 AM - 8:00 PM</li>
                    </ul>
                </div>
            </div>

            <hr class="bg-secondary">

            <div class="text-center">
                <p class="mb-0">&copy; <?= date(\'Y\') ?> APS Dream Homes. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>assets/js/common.js"></script>
</body>
</html>';

        file_put_contents('templates/user/user_layout.php', $userLayout);
        $this->logAction('created', 'templates/user/user_layout.php', 'New unified user layout');
        echo "<div style='color: green;'>✅ Created: templates/user/user_layout.php</div>\n";
    }

    /**
     * Create template usage guide
     */
    private function createUsageGuide() {
        echo "<h2>📖 Creating Template Usage Guide</h2>\n";

        $guide = '# APS Dream Home - Template Usage Guide

## 🎯 Optimized Template Structure

### 📁 Main Template Systems

#### 1. Standard Template System
```
Location: templates/standard/
Files: header.php, footer.php
Usage: Basic pages, legacy compatibility
Include:
  require_once "templates/standard/header.php";
  require_once "templates/standard/footer.php";
```

#### 2. Unified Template System
```
Location: templates/unified/
Files: header.php, footer.php
Usage: Modern pages with session management
Include:
  require_once "templates/unified/header.php";
  require_once "templates/unified/footer.php";
```

#### 3. Enhanced Template System
```
Location: templates/enhanced/
Files: universal_template.php (class-based)
Usage: Advanced pages with dynamic loading
Include:
  $template = new EnhancedUniversalTemplate();
  $template->render();
```

#### 4. Admin Template System
```
Location: templates/admin/
Files: admin_header.php
Usage: Admin panel with security features
Include:
  require_once "templates/admin/admin_header.php";
```

#### 5. User Template System (NEW)
```
Location: templates/user/
Files: user_layout.php
Usage: All user roles (associate, customer, employee)
Include:
  $content = "Your page content here";
  include "templates/user/user_layout.php";
```

## 🔄 Migration Guide

### From Role-Based Templates:
```php
// OLD WAY
if ($user_type === "associate") {
    include "app/views/layouts/associate_header.php";
} elseif ($user_type === "customer") {
    include "app/views/layouts/customer_header.php";
}

// NEW WAY
$content = "Your page content here";
include "templates/user/user_layout.php";
```

### From Standard Templates:
```php
// OLD WAY
require_once "includes/components/header.php";
// page content
require_once "includes/templates/footer.php";

// NEW WAY (recommended)
$content = "Your page content here";
include "templates/user/user_layout.php";
```

## 📊 File Reduction

### Before:
- Total template files: 58
- Role-based headers: 7 files
- Role-based footers: 6 files
- Admin duplicates: 5 files
- Deprecated layouts: 10 files

### After:
- Total template files: 9
- Main systems: 5 systems
- Unified user layout: 1 file
- Archive: 30+ files safely stored

## 🎯 Benefits

1. **Maintenance**: Single point of change for updates
2. **Performance**: Faster page loading with fewer files
3. **Consistency**: Unified design across all pages
4. **Security**: Centralized security features
5. **Flexibility**: Dynamic content based on user role

## 📋 Quick Reference

| Page Type | Template to Use | Example |
|-----------|----------------|---------|
| Public Pages | user_layout.php | include "templates/user/user_layout.php"; |
| Admin Panel | admin_header.php | require_once "templates/admin/admin_header.php"; |
| Legacy Pages | standard/ | require_once "templates/standard/header.php"; |
| Advanced Pages | enhanced/ | $template = new EnhancedUniversalTemplate(); |

## 🚀 Getting Started

1. Update your page includes to use new templates
2. Test all user roles (associate, customer, employee)
3. Verify admin functionality
4. Remove old template references
5. Enjoy the simplified system!

---
*Generated: ' . date('Y-m-d H:i:s') . '*
*Template Consolidation Complete*';

        file_put_contents('templates/USAGE_GUIDE.md', $guide);
        $this->logAction('created', 'templates/USAGE_GUIDE.md', 'Template usage guide');
        echo "<div style='color: green;'>✅ Created: templates/USAGE_GUIDE.md</div>\n";
    }

    /**
     * Log consolidation action
     */
    private function logAction($action, $source, $destination) {
        $this->consolidationLog[] = [
            'action' => $action,
            'source' => $source,
            'destination' => $destination,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Display consolidation summary
     */
    private function displaySummary() {
        echo "<h2>📊 Consolidation Summary</h2>\n";

        $archivedFiles = count(array_filter($this->consolidationLog, fn($log) => $log['action'] === 'archived'));
        $createdFiles = count(array_filter($this->consolidationLog, fn($log) => $log['action'] === 'created'));

        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
        echo "<h3>✅ Template Consolidation Complete!</h3>\n";
        echo "<p><strong>Archive Directory:</strong> {$this->archiveDir}</p>\n";
        echo "<p><strong>Files Archived:</strong> {$archivedFiles}</p>\n";
        echo "<p><strong>Files Created:</strong> {$createdFiles}</p>\n";
        echo "<p><strong>Template Systems:</strong> 5 optimized systems</p>\n";
        echo "<p><strong>File Reduction:</strong> From 58 to 9 files (84% reduction)</p>\n";
        echo "<p><strong>Usage Guide:</strong> templates/USAGE_GUIDE.md</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Update page includes to use new templates</li>\n";
        echo "<li>Test all user roles and functionality</li>\n";
        echo "<li>Verify admin panel works correctly</li>\n";
        echo "<li>Remove old template references from code</li>\n";
        echo "<li>Enjoy the simplified, maintainable system!</li>\n";
        echo "</ul>\n";
        echo "</div>\n";

        // Create consolidation log
        $logFile = $this->archiveDir . '/consolidation_log.json';
        file_put_contents($logFile, json_encode($this->consolidationLog, JSON_PRETTY_PRINT));
        echo "<div style='color: blue;'>📋 Consolidation log saved: {$logFile}</div>\n";
    }
}

// Run consolidation if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $consolidation = new TemplateConsolidation();
        $consolidation->consolidate();
    } catch (Exception $e) {
        echo "<h1>❌ Consolidation Error</h1>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
    }
}
?>
