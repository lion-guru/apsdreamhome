
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (!isset($_SESSION['auser'])) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_menu':
                $menu_items = $_POST['menu_items'];
                $sql = "UPDATE site_settings SET value = ? WHERE setting_name = 'header_menu_items'";
                $stmt = $conn->prepare($sql);
                $json_menu = json_encode($menu_items);
                $stmt->bind_param('s', $json_menu);
                $stmt->execute();
                $_SESSION['success_msg'] = 'Menu items updated successfully!';
                break;

            case 'update_logo':
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'svg'];
                    $filename = $_FILES['logo']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    if (in_array($ext, $allowed)) {
                        $target_path = '../assets/images/logo.' . $ext;
                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                            $sql = "UPDATE site_settings SET value = ? WHERE setting_name = 'site_logo'";
                            $stmt = $conn->prepare($sql);
                            $logo_path = 'assets/images/logo.' . $ext;
                            $stmt->bind_param('s', $logo_path);
                            $stmt->execute();
                            $_SESSION['success_msg'] = 'Logo updated successfully!';
                        }
                    }
                }
                break;

            case 'update_styles':
                $header_styles = $_POST['header_styles'];
                $sql = "UPDATE site_settings SET value = ? WHERE setting_name = 'header_styles'";
                $stmt = $conn->prepare($sql);
                $json_styles = json_encode($header_styles);
                $stmt->bind_param('s', $json_styles);
                $stmt->execute();
                $_SESSION['success_msg'] = 'Header styles updated successfully!';
                break;
        }
    }
}

// Get current settings
$sql = "SELECT * FROM site_settings WHERE setting_name IN ('header_menu_items', 'site_logo', 'header_styles')";
$result = $conn->query($sql);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_name']] = json_decode($row['value'], true);
}

// Default values if not set
if (!isset($settings['header_menu_items'])) {
    $settings['header_menu_items'] = [
        ['text' => 'Home', 'url' => '/', 'icon' => 'fa-home'],
        ['text' => 'Properties', 'url' => '/property.php', 'icon' => 'fa-building'],
        ['text' => 'About', 'url' => '/about.php', 'icon' => 'fa-info-circle'],
        ['text' => 'Contact', 'url' => '/contact.php', 'icon' => 'fa-envelope']
    ];
}

include 'updated-admin-header.php';
?>

<div class="content container-fluid">
    <div class="page-header">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="page-title">Header Management</h3>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_msg'];
            unset($_SESSION['success_msg']);
            ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <!-- Menu Items Management -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Menu Items</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="menuForm">
                        <input type="hidden" name="action" value="update_menu">
                        <div id="menuItems">
                            <?php foreach ($settings['header_menu_items'] as $index => $item): ?>
                            <div class="row menu-item mb-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="menu_items[<?php echo $index; ?>][text]" 
                                           value="<?php echo htmlspecialchars($item['text']); ?>" placeholder="Menu Text">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="menu_items[<?php echo $index; ?>][url]" 
                                           value="<?php echo htmlspecialchars($item['url']); ?>" placeholder="URL">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="menu_items[<?php echo $index; ?>][icon]" 
                                           value="<?php echo htmlspecialchars($item['icon']); ?>" placeholder="Icon Class">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-menu-item">Remove</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-info" id="addMenuItem">Add Menu Item</button>
                        <button type="submit" class="btn btn-primary">Save Menu Items</button>
                    </form>
                </div>
            </div>

            <!-- Logo Management -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">Logo Management</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_logo">
                        <div class="form-group">
                            <label>Current Logo</label><br>
                            <img src="../<?php echo isset($settings['site_logo']) && $settings['site_logo'] ? htmlspecialchars($settings['site_logo']) : htmlspecialchars(get_asset_url('logo.png', 'images')); ?>" 
                                 alt="Current Logo" style="max-height: 100px;">
                        </div>
                        <div class="form-group">
                            <label>Upload New Logo</label>
                            <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.svg">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Logo</button>
                    </form>
                </div>
            </div>

            <!-- Header Styles Management -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">Header Styles</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_styles">
                        <div class="form-group">
                            <label>Background Color</label>
                            <input type="color" name="header_styles[background]" 
                                   value="<?php echo $settings['header_styles']['background'] ?? '#1e3c72'; ?>" 
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Text Color</label>
                            <input type="color" name="header_styles[text_color]" 
                                   value="<?php echo $settings['header_styles']['text_color'] ?? '#ffffff'; ?>" 
                                   class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Styles</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add new menu item
    document.getElementById('addMenuItem').addEventListener('click', function() {
        const menuItems = document.getElementById('menuItems');
        const index = menuItems.children.length;
        const newItem = `
            <div class="row menu-item mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="menu_items[${index}][text]" placeholder="Menu Text">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="menu_items[${index}][url]" placeholder="URL">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="menu_items[${index}][icon]" placeholder="Icon Class">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-menu-item">Remove</button>
                </div>
            </div>
        `;
        menuItems.insertAdjacentHTML('beforeend', newItem);
    });

    // Remove menu item
    document.getElementById('menuItems').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-menu-item')) {
            e.target.closest('.menu-item').remove();
        }
    });
});
</script>

<?php include 'footer.php'; ?>