<?php
require_once __DIR__ . '/core/init.php';

// Check if user is superadmin
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=' . urlencode('Access Denied: Superadmin privilege required.'));
    exit();
}

// Fetch current settings
$stmt = $pdo->query('SELECT * FROM site_settings WHERE setting_name IN ("header_menu_items", "site_logo", "header_styles", "footer_content", "footer_links", "social_links", "footer_status", "footer_about_us", "footer_quick_links", "footer_services", "footer_contact_address", "footer_contact_phone", "footer_contact_email")');
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_name']] = $row['value'];
}

$headerMenuItems = json_decode($settings['header_menu_items'] ?? '[]', true);
$headerStyles = json_decode($settings['header_styles'] ?? '{}', true);
$siteLogo = $settings['site_logo'] ?? '';
$footerContent = $settings['footer_content'] ?? '';
$footerLinks = json_decode($settings['footer_links'] ?? '[]', true);
$socialLinks = json_decode($settings['social_links'] ?? '[]', true);

// New Footer Settings
$footerStatus = $settings['footer_status'] ?? 'inactive';
$footerAboutUs = $settings['footer_about_us'] ?? '';
$footerQuickLinks = json_decode($settings['footer_quick_links'] ?? '[]', true);
$footerServices = json_decode($settings['footer_services'] ?? '[]', true);
$footerContactAddress = $settings['footer_contact_address'] ?? '';
$footerContactPhone = $settings['footer_contact_phone'] ?? '';
$footerContactEmail = $settings['footer_contact_email'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please try again.";
    } else {
        require_once __DIR__ . '/../includes/log_admin_action_db.php';
    try {
        // Validate and sanitize input
        $headerMenuItems = json_decode($_POST['header_menu_items'], true);
        if (json_last_error()) {
            throw new Exception('Invalid menu items format');
        }

        $headerStyles = json_decode($_POST['header_styles'], true);
        if (json_last_error()) {
            throw new Exception('Invalid header styles format');
        }

        // Footer dynamic fields
        $footerContent = $_POST['footer_content'] ?? '';
        $footerLinks = json_decode($_POST['footer_links'] ?? '[]', true);
        if (json_last_error()) {
            throw new Exception('Invalid footer links format');
        }
        $socialLinks = json_decode($_POST['social_links'] ?? '[]', true);
        if (json_last_error()) {
            throw new Exception('Invalid social links format');
        }

        // New Footer Fields
        $footerStatus = isset($_POST['footer_status']) ? 'active' : 'inactive';
        $footerAboutUs = $_POST['footer_about_us'] ?? '';

        $footerQuickLinks = [];
        if (isset($_POST['footer_quick_links_text']) && isset($_POST['footer_quick_links_url'])) {
            foreach ($_POST['footer_quick_links_text'] as $index => $text) {
                if (!empty($text) && !empty($_POST['footer_quick_links_url'][$index])) {
                    $footerQuickLinks[] = [
                        'text' => sanitize_input($text),
                        'url' => sanitize_input($_POST['footer_quick_links_url'][$index])
                    ];
                }
            }
        }

        $footerServices = [];
        if (isset($_POST['footer_services_text']) && isset($_POST['footer_services_url'])) {
            foreach ($_POST['footer_services_text'] as $index => $text) {
                if (!empty($text) && !empty($_POST['footer_services_url'][$index])) {
                    $footerServices[] = [
                        'text' => sanitize_input($text),
                        'url' => sanitize_input($_POST['footer_services_url'][$index])
                    ];
                }
            }
        }

        $footerContactAddress = $_POST['footer_contact_address'] ?? '';
        $footerContactPhone = $_POST['footer_contact_phone'] ?? '';
        $footerContactEmail = $_POST['footer_contact_email'] ?? '';

        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['site_logo']['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }

            $uploadDir = '../assets/images/';
            $fileName = 'logo_' . time() . '_' . basename($_FILES['site_logo']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $uploadPath)) {
                $logoPath = 'assets/images/' . $fileName;
                // Update logo path in database
                $stmt = $pdo->prepare('UPDATE site_settings SET value = ? WHERE setting_name = "site_logo"');
                $stmt->execute([$logoPath]);
            }
        }

        // Update header menu items
        $stmt = $pdo->prepare('UPDATE site_settings SET value = ? WHERE setting_name = "header_menu_items"');
        $stmt->execute([json_encode($headerMenuItems)]);

        // Update header styles
        $stmt = $pdo->prepare('UPDATE site_settings SET value = ? WHERE setting_name = "header_styles"');
        $stmt->execute([json_encode($headerStyles)]);

        // Update footer content
        $stmt = $pdo->prepare('UPDATE site_settings SET value = ? WHERE setting_name = "footer_content"');
        $stmt->execute([$footerContent]);
        $stmt = $pdo->prepare('UPDATE site_settings SET value = ? WHERE setting_name = "footer_links"');
        $stmt->execute([json_encode($footerLinks)]);
        $stmt = $pdo->prepare('UPDATE site_settings SET value = ? WHERE setting_name = "social_links"');
        $stmt->execute([json_encode($socialLinks)]);

        // Update new footer settings
        updateSetting($pdo, 'footer_status', $footerStatus);
        updateSetting($pdo, 'footer_about_us', $footerAboutUs);
        updateSetting($pdo, 'footer_quick_links', json_encode($footerQuickLinks));
        updateSetting($pdo, 'footer_services', json_encode($footerServices));
        updateSetting($pdo, 'footer_contact_address', $footerContactAddress);
        updateSetting($pdo, 'footer_contact_phone', $footerContactPhone);
        updateSetting($pdo, 'footer_contact_email', $footerContactEmail);

        $socialLinks = [];
        if (isset($_POST['social_link_platform']) && isset($_POST['social_link_url']) && isset($_POST['social_link_icon'])) {
            foreach ($_POST['social_link_platform'] as $index => $platform) {
                if (!empty($platform) && !empty($_POST['social_link_url'][$index]) && !empty($_POST['social_link_icon'][$index])) {
                    $socialLinks[] = [
                        'platform' => sanitize_input($platform),
                        'url' => sanitize_input($_POST['social_link_url'][$index]),
                        'icon' => sanitize_input($_POST['social_link_icon'][$index])
                    ];
                }
            }
        }
        updateSetting($pdo, 'social_links', json_encode($socialLinks));

        $_SESSION['success_message'] = 'Header & Footer settings updated successfully!';
        log_admin_action_db('update_site_settings', 'Header/Footer settings updated');
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        log_admin_action_db('update_site_settings_failed', 'Header/Footer settings update failed: ' . $e->getMessage());
    }

    header('Location: header_footer_settings.php');
    exit();
}
}

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Header & Footer Settings</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="header-tab" data-bs-toggle="tab" data-bs-target="#header-settings" type="button" role="tab" aria-controls="header-settings" aria-selected="true">Header</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer-settings" type="button" role="tab" aria-controls="footer-settings" aria-selected="false">Footer</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-settings" type="button" role="tab" aria-controls="preview-settings" aria-selected="false">Live Preview</button>
        </li>
    </ul>
    <div class="tab-content" id="settingsTabsContent">
        <div class="tab-pane fade show active" id="header-settings" role="tabpanel" aria-labelledby="header-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Header Settings</h6>
                </div>
                <div class="card-body">
                    <form action="header_footer_settings.php" method="POST" enctype="multipart/form-data">
                        <?php echo getCsrfField(); ?>
                        <div class="form-group">
                            <label for="header_status">Header Status</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="header_status" name="header_status" <?php echo ($settings['header_status'] ?? 'inactive') == 'active' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="header_status">Active/Inactive</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="site_logo">Site Logo</label>
                            <?php if ($siteLogo): ?>
                                <div class="mb-2">
                                    <img src="../<?php echo h($siteLogo); ?>" alt="Current Logo" style="max-height: 100px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" id="site_logo" name="site_logo">
                        </div>

                        <div class="form-group">
                            <label for="header_menu_items">Header Menu Items</label>
                            <div id="menuItems">
                                <?php foreach ($headerMenuItems as $index => $item): ?>
                                    <div class="menu-item mb-2">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" name="menu_text[]" placeholder="Menu Text" value="<?php echo h($item['text']); ?>" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="menu_url[]" placeholder="URL" value="<?php echo h($item['url']); ?>" required>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" name="menu_icon[]" placeholder="Font Awesome Icon" value="<?php echo h($item['icon']); ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-menu-item">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="addMenuItem">Add Menu Item</button>
                        </div>

                        <div class="form-group">
                            <label>Header Styles</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="header_background">Background Color</label>
                                    <input type="color" class="form-control" id="header_background" name="header_background" value="<?php echo h($headerStyles['background'] ?? '#1e3c72'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="header_text_color">Text Color</label>
                                    <input type="color" class="form-control" id="header_text_color" name="header_text_color" value="<?php echo h($headerStyles['text_color'] ?? '#ffffff'); ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="footer-settings" role="tabpanel" aria-labelledby="footer-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Footer Settings</h6>
                </div>
                <div class="card-body">
                    <form action="header_footer_settings.php" method="POST">
                        <div class="form-group">
                            <label for="footer_status">Footer Status</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="footer_status" name="footer_status" <?php echo ($footerStatus == 'active' || (isset($settings['footer_status']) && $settings['footer_status'] == 'active')) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="footer_status">Active/Inactive</label>
                            </div>
                        </div>

                        <!-- About Us Section -->
                        <div class="form-group">
                            <label for="footer_about_us">About Us Content</label>
                            <textarea class="form-control" id="footer_about_us" name="footer_about_us" rows="3"><?php echo h($settings['footer_about_us'] ?? ''); ?></textarea>
                        </div>

                        <!-- Quick Links Section -->
                        <div class="form-group">
                            <label for="footer_quick_links">Quick Links</label>
                            <div id="footerQuickLinks">
                                <?php foreach (json_decode($settings['footer_quick_links'] ?? '[]', true) as $index => $link): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="footer_quick_links_text[]" placeholder="Link Text" value="<?php echo h($link['text']); ?>">
                                        <input type="text" class="form-control" name="footer_quick_links_url[]" placeholder="Link URL" value="<?php echo h($link['url']); ?>">
                                        <button type="button" class="btn btn-danger remove-footer-link">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="addFooterQuickLink">Add Quick Link</button>
                        </div>

                        <!-- Services Section -->
                        <div class="form-group">
                            <label for="footer_services">Services</label>
                            <div id="footerServices">
                                <?php foreach (json_decode($settings['footer_services'] ?? '[]', true) as $index => $service): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="footer_services_text[]" placeholder="Service Name" value="<?php echo h($service['text']); ?>">
                                        <input type="text" class="form-control" name="footer_services_url[]" placeholder="Service URL" value="<?php echo h($service['url']); ?>">
                                        <button type="button" class="btn btn-danger remove-footer-service">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="addFooterService">Add Service</button>
                        </div>

                        <!-- Contact Info Section -->
                        <div class="form-group">
                            <label for="footer_contact_address">Contact Address</label>
                            <input type="text" class="form-control" id="footer_contact_address" name="footer_contact_address" value="<?php echo h($settings['footer_contact_address'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="footer_contact_phone">Contact Phone</label>
                            <input type="text" class="form-control" id="footer_contact_phone" name="footer_contact_phone" value="<?php echo h($settings['footer_contact_phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="footer_contact_email">Contact Email</label>
                            <input type="email" class="form-control" id="footer_contact_email" name="footer_contact_email" value="<?php echo h($settings['footer_contact_email'] ?? ''); ?>">
                        </div>

                        <!-- Social Links Section -->
                        <div class="form-group">
                            <label>Social Links</label>
                            <div id="socialLinksContainer">
                                <?php foreach (json_decode($settings['social_links'] ?? '[]', true) as $index => $link): ?>
                                    <div class="input-group mb-2 social-link-item">
                                        <input type="text" class="form-control" name="social_link_platform[]" placeholder="Platform (e.g., facebook)" value="<?php echo h($link['platform']); ?>">
                                        <input type="text" class="form-control" name="social_link_url[]" placeholder="URL" value="<?php echo h($link['url']); ?>">
                                        <input type="text" class="form-control" name="social_link_icon[]" placeholder="Font Awesome Icon Class (e.g., fab fa-facebook-f)" value="<?php echo h($link['icon']); ?>">
                                        <button type="button" class="btn btn-danger remove-social-link">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="addSocialLink">Add Social Link</button>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Footer Changes</button>
                    </form>
                </div>
            </div>
        </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Footer Settings</h6>
                </div>
                <div class="card-body">
                    <form action="header_footer_settings.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="footer_content">Footer Copyright Content</label>
                            <textarea class="form-control" id="footer_content" name="footer_content" rows="3"><?php echo h($footerContent); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="footer_links">Footer Navigation Links</label>
                            <div id="footerLinks">
                                <?php foreach ($footerLinks as $index => $link): ?>
                                    <div class="footer-link-item mb-2">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="footer_link_text[]" placeholder="Link Text" value="<?php echo h($link['text']); ?>" required>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="footer_link_url[]" placeholder="URL" value="<?php echo h($link['url']); ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-footer-link">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="addFooterLink">Add Footer Link</button>
                        </div>

                        <div class="form-group">
                            <label for="social_links">Social Links (Alternative Format)</label>
                            <div id="socialLinks">
                                <?php foreach ($socialLinks as $index => $social): ?>
                                    <div class="social-item mb-2">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="social_link_icon[]" placeholder="Font Awesome Icon" value="<?php echo h($social['icon']); ?>" required>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="social_link_url[]" placeholder="URL" value="<?php echo h($social['url']); ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-social-item">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="addSocialItem">Add Social Item</button>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="preview-settings" role="tabpanel" aria-labelledby="preview-tab">
            <div class="card card-body bg-light border">
                <h5 class="mb-3">Live Frontend Preview</h5>
                <iframe id="frontendPreview" src="../preview.php" style="width:100%;min-height:500px;border:1px solid #ccc;"></iframe>
                <small class="text-muted">This is a live preview of how your frontend header and footer will look after saving changes.</small>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure Bootstrap Tabs work
        var triggerTabList = [].slice.call(document.querySelectorAll('#settingsTabs button'));
        triggerTabList.forEach(function(triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });
        // Optionally, refresh preview on save
        document.querySelector('form').addEventListener('submit', function() {
            setTimeout(function() {
                var preview = document.getElementById('frontendPreview');
                if (preview) preview.contentWindow.location.reload();
            }, 1000);
        });
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.getElementById('menuItems');
        const addMenuItem = document.getElementById('addMenuItem');

        // Add new menu item
        addMenuItem.addEventListener('click', function() {
            const menuItem = document.createElement('div');
            menuItem.className = 'menu-item mb-2';
            menuItem.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="menu_text[]" placeholder="Menu Text" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="menu_url[]" placeholder="URL" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="menu_icon[]" placeholder="Font Awesome Icon">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-menu-item">Remove</button>
                    </div>
                </div>
            `;
            menuItems.appendChild(menuItem);
        });

        // Remove menu item
        menuItems.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-menu-item')) {
                e.target.closest('.menu-item').remove();
            }
        });

        // Footer links dynamic fields
        const footerLinks = document.getElementById('footerLinks');
        const addFooterLink = document.getElementById('addFooterLink');
        addFooterLink.addEventListener('click', function() {
            const linkItem = document.createElement('div');
            linkItem.className = 'footer-link-item mb-2';
            linkItem.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="footer_link_text[]" placeholder="Link Text" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="footer_link_url[]" placeholder="URL" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-footer-link">Remove</button>
                    </div>
                </div>
            `;
            footerLinks.appendChild(linkItem);
        });
        footerLinks.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-footer-link')) {
                e.target.closest('.footer-link-item').remove();
            }
        });

        // Social links dynamic fields
        const socialLinks = document.getElementById('socialLinks');
        const addSocialLink = document.getElementById('addSocialLink');
        addSocialLink.addEventListener('click', function() {
            const socialItem = document.createElement('div');
            socialItem.className = 'social-link-item mb-2';
            socialItem.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="social_link_icon[]" placeholder="Font Awesome Icon" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="social_link_url[]" placeholder="URL" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-social-link">Remove</button>
                    </div>
                </div>
            `;
            socialLinks.appendChild(socialItem);
        });
        socialLinks.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-social-link')) {
                e.target.closest('.social-link-item').remove();
            }
        });

        // On form submit, collect footer and social links
        document.querySelector('form').addEventListener('submit', function(e) {
            // Collect menu items
            const menuItemsData = [];
            document.querySelectorAll('.menu-item').forEach(function(item) {
                menuItemsData.push({
                    text: item.querySelector('[name="menu_text[]"]').value,
                    url: item.querySelector('[name="menu_url[]"]').value,
                    icon: item.querySelector('[name="menu_icon[]"]').value
                });
            });

            // Create hidden input for menu items
            const menuItemsInput = document.createElement('input');
            menuItemsInput.type = 'hidden';
            menuItemsInput.name = 'header_menu_items';
            menuItemsInput.value = JSON.stringify(menuItemsData);
            this.appendChild(menuItemsInput);

            // Create hidden input for header styles
            const headerStyles = {
                background: document.getElementById('header_background').value,
                text_color: document.getElementById('header_text_color').value
            };
            const headerStylesInput = document.createElement('input');
            headerStylesInput.type = 'hidden';
            headerStylesInput.name = 'header_styles';
            headerStylesInput.value = JSON.stringify(headerStyles);
            this.appendChild(headerStylesInput);

            // Collect footer links
            const footerLinksData = [];
            document.querySelectorAll('.footer-link-item').forEach(function(item) {
                footerLinksData.push({
                    text: item.querySelector('[name="footer_link_text[]"]').value,
                    url: item.querySelector('[name="footer_link_url[]"]').value
                });
            });
            let footerLinksInput = document.createElement('input');
            footerLinksInput.type = 'hidden';
            footerLinksInput.name = 'footer_links';
            footerLinksInput.value = JSON.stringify(footerLinksData);
            this.appendChild(footerLinksInput);
            // Collect social links
            const socialLinksData = [];
            document.querySelectorAll('.social-link-item').forEach(function(item) {
                socialLinksData.push({
                    icon: item.querySelector('[name="social_link_icon[]"]').value,
                    url: item.querySelector('[name="social_link_url[]"]').value
                });
            });
            let socialLinksInput = document.createElement('input');
            socialLinksInput.type = 'hidden';
            socialLinksInput.name = 'social_links';
            socialLinksInput.value = JSON.stringify(socialLinksData);
            this.appendChild(socialLinksInput);
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Social Links functionality
        const socialLinksContainer = document.getElementById('socialLinksContainer');
        const addSocialLinkButton = document.getElementById('addSocialLink');

        addSocialLinkButton.addEventListener('click', function() {
            const newLink = document.createElement('div');
            newLink.classList.add('input-group', 'mb-2', 'social-link-item');
            newLink.innerHTML = `
                <input type="text" class="form-control" name="social_link_platform[]" placeholder="Platform (e.g., facebook)">
                <input type="text" class="form-control" name="social_link_url[]" placeholder="URL">
                <input type="text" class="form-control" name="social_link_icon[]" placeholder="Font Awesome Icon Class (e.g., fab fa-facebook-f)">
                <button type="button" class="btn btn-danger remove-social-link">Remove</button>
            `;
            socialLinksContainer.appendChild(newLink);
        });

        socialLinksContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-social-link')) {
                event.target.closest('.social-link-item').remove();
            }
        });

        // Header Menu Items functionality
        const menuItemsContainer = document.getElementById('menuItems');
        const addMenuItemButton = document.getElementById('addMenuItem');

        addMenuItemButton.addEventListener('click', function() {
            const newItem = document.createElement('div');
            newItem.classList.add('menu-item', 'mb-2');
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="menu_text[]" placeholder="Menu Text" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="menu_url[]" placeholder="URL" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="menu_icon[]" placeholder="Font Awesome Icon">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-menu-item">Remove</button>
                    </div>
                </div>
            `;
            menuItemsContainer.appendChild(newItem);
        });

        menuItemsContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-menu-item')) {
                event.target.closest('.menu-item').remove();
            }
        });
    });
    </script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
