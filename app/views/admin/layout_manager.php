<?php
// Admin Layout Management Interface
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../Services/LayoutManager.php';
require_once __DIR__ . '/../../Core/Database/Database.php';

use App\Core\Database\Database;

$database = Database::getInstance();
$layoutManager = new LayoutManager($database);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'premium_layout' => isset($_POST['premium_layout']),
        'header_type' => $_POST['header_type'] ?? 'dynamic',
        'footer_type' => $_POST['footer_type'] ?? 'dynamic',
        'navigation_items' => json_decode($_POST['navigation_items'] ?? '[]', true),
        'footer_content' => $_POST['footer_content'] ?? '',
        'custom_css' => $_POST['custom_css'] ?? '',
        'custom_js' => $_POST['custom_js'] ?? ''
    ];

    $layoutManager->updateLayoutSettings($settings);
    $_SESSION['success'] = 'Layout settings updated successfully!';
    header('Location: /admin/layout-manager');
    exit;
}

// Get current settings
$settings = $layoutManager->getLayoutSettings();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Manager - APS Dream Home Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .layout-preview {
            border: 2px dashed #007bff;
            min-height: 200px;
            background: #f8f9fa;
        }

        .nav-item-preview {
            border: 1px solid #dee2e6;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }

        .code-editor {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .layout-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4"><i class="fas fa-palette me-2"></i>Layout Manager</h1>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/admin/layout-manager">
                    <div class="row">
                        <!-- Layout Type Selection -->
                        <div class="col-md-6">
                            <div class="layout-section p-4">
                                <h4><i class="fas fa-toggle-on me-2"></i>Layout Type</h4>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="premium_layout" <?php echo $settings['premium_layout'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="premium_layout">
                                        Use Premium Layout (Active/Premium)
                                    </label>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Premium: Uses dynamic header/footer from active/ folder<br>
                                    Standard: Uses basic header/footer from main folder
                                </small>
                            </div>
                        </div>

                        <!-- Navigation Management -->
                        <div class="col-md-6">
                            <div class="layout-section p-4">
                                <h4><i class="fas fa-bars me-2"></i>Navigation Items</h4>
                                <div id="navigation-container">
                                    <?php foreach ($settings['navigation_items'] as $index => $item): ?>
                                        <div class="nav-item-preview mb-2">
                                            <div class="row align-items-center">
                                                <div class="col-1">
                                                    <i class="fas fa-grip-vertical text-muted"></i>
                                                </div>
                                                <div class="col-8">
                                                    <input type="text" class="form-control" name="nav_label_<?php echo $index; ?>"
                                                        value="<?php echo htmlspecialchars($item['label']); ?>" placeholder="Label">
                                                    <input type="text" class="form-control mt-1" name="nav_url_<?php echo $index; ?>"
                                                        value="<?php echo htmlspecialchars($item['url']); ?>" placeholder="URL">
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="nav_active_<?php echo $index; ?>"
                                                            <?php echo ($item['active'] ?? false) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Active</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addNavItem()">
                                    <i class="fas fa-plus me-1"></i>Add Item
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Footer Content -->
                        <div class="col-md-6">
                            <div class="layout-section p-4">
                                <h4><i class="fas fa-footer me-2"></i>Footer Content</h4>
                                <textarea class="form-control code-editor" name="footer_content" rows="6" placeholder="HTML content for footer"><?php echo htmlspecialchars($settings['footer_content']); ?></textarea>
                                <small class="text-muted">
                                    <i class="fas fa-code me-1"></i>
                                    HTML content for premium footer. Leave empty for default.
                                </small>
                            </div>
                        </div>

                        <!-- Custom CSS -->
                        <div class="col-md-6">
                            <div class="layout-section p-4">
                                <h4><i class="fas fa-paint-brush me-2"></i>Custom CSS</h4>
                                <textarea class="form-control code-editor" name="custom_css" rows="6" placeholder="Custom CSS..."><?php echo htmlspecialchars($settings['custom_css']); ?></textarea>
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i>
                                    Additional CSS to include in all pages
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden navigation data -->
                    <input type="hidden" name="navigation_items" id="navigation_items" value='<?php echo json_encode($settings['navigation_items']); ?>'>

                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Layout Settings
                            </button>
                            <a href="/admin/dashboard" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Layout Preview -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="layout-section p-4">
                            <h4><i class="fas fa-eye me-2"></i>Layout Preview</h4>
                            <div class="layout-preview p-3">
                                <h6>Current Layout: <span class="badge bg-<?php echo $settings['premium_layout'] ? 'success' : 'secondary'; ?>"><?php echo $settings['premium_layout'] ? 'Premium' : 'Standard'; ?></span></h6>
                                <div class="mt-3">
                                    <strong>Navigation Items:</strong>
                                    <ul class="mt-2">
                                        <?php foreach ($settings['navigation_items'] as $item): ?>
                                            <?php if ($item['active'] ?? false): ?>
                                                <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($item['label']); ?> → <?php echo htmlspecialchars($item['url']); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let navCounter = <?php echo count($settings['navigation_items']); ?>;

        function addNavItem() {
            const container = document.getElementById('navigation-container');
            const newNav = document.createElement('div');
            newNav.className = 'nav-item-preview mb-2';
            newNav.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-1"><i class="fas fa-grip-vertical text-muted"></i></div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="nav_label_${navCounter}" placeholder="Label">
                        <input type="text" class="form-control mt-1" name="nav_url_${navCounter}" placeholder="URL">
                    </div>
                    <div class="col-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nav_active_${navCounter}" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newNav);
            navCounter++;
            updateNavigationData();
        }

        function updateNavigationData() {
            const navItems = [];
            const navContainer = document.getElementById('navigation-container');
            const navElements = navContainer.querySelectorAll('.nav-item-preview');

            navElements.forEach((element, index) => {
                const label = element.querySelector(`input[name="nav_label_${index}"]`)?.value || '';
                const url = element.querySelector(`input[name="nav_url_${index}"]`)?.value || '';
                const active = element.querySelector(`input[name="nav_active_${index}"]`)?.checked || false;

                if (label || url) {
                    navItems.push({
                        label,
                        url,
                        active
                    });
                }
            });

            document.getElementById('navigation_items').value = JSON.stringify(navItems);
        }

        // Auto-update navigation data when inputs change
        document.addEventListener('input', function(e) {
            if (e.target.name.startsWith('nav_')) {
                updateNavigationData();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.name.startsWith('nav_')) {
                updateNavigationData();
            }
        });
    </script>
</body>

</html>