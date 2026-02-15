<?php
// Admin panel: Manage Header Menu
session_start();
// Simple admin check (replace with your own logic)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
    exit;
}

// require_once __DIR__ . '/../includes/functions/asset_helper.php'; // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead
require_once __DIR__ . '/../config.php';

// DB Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die('DB Error');

// Handle form submission (save menu)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_json'])) {
    $menu = $_POST['menu_json'];
    $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, value) VALUES ('header_menu_items', ?) ON DUPLICATE KEY UPDATE value=?");
    $stmt->bind_param('ss', $menu, $menu);
    $stmt->execute();
    $stmt->close();
    $msg = 'Menu updated successfully!';
}

// Fetch menu from DB
$res = $conn->query("SELECT value FROM site_settings WHERE setting_name='header_menu_items'");
if ($row = $res->fetch_assoc()) {
    $menu_items = json_decode($row['value'], true);
} else {
    $menu_items = [];
}
$conn->close();

// Default icons for selection
$icon_options = [
    'fa-home', 'fa-building', 'fa-info-circle', 'fa-envelope',
    'fa-newspaper', 'fa-comments', 'fa-user-plus', 'fa-sign-in-alt',
    'fa-tachometer-alt', 'fa-sign-out-alt'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Header Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/font-awesome.min.css">
    <style>.icon-preview { font-size: 1.4em; margin-right: 6px; }</style>
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Header Menu Management</h2>
    <?php if (!empty($msg)): ?><div class="alert alert-success">Menu updated!</div><?php endif; ?>
    <form method="post" id="menuForm">
        <input type="hidden" name="menu_json" id="menu_json">
        <table class="table table-bordered bg-white" id="menuTable">
            <thead class="table-light">
                <tr>
                    <th>Text</th>
                    <th>URL</th>
                    <th>Icon</th>
                    <th>Sub-menus</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($menu_items as $i => $item): ?>
                <tr class="main-menu-row">
                    <td><input type="text" class="form-control" value="<?=htmlspecialchars($item['text'])?>"></td>
                    <td><input type="text" class="form-control" value="<?=htmlspecialchars($item['url'])?>"></td>
                    <td>
                        <select class="form-select">
                            <?php foreach ($icon_options as $icon): ?>
                                <option value="<?=$icon?>" <?=$icon==$item['icon']?'selected':''?>><?=$icon?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="icon-preview"><i class="fa <?=$item['icon']?>"></i></span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary add-submenu">Add Sub-menu</button>
                        <table class="table table-sm submenu-table mb-0 mt-2">
                            <tbody>
                            <?php if (!empty($item['children'])): foreach ($item['children'] as $sub): ?>
                                <tr class="submenu-row">
                                    <td><input type="text" class="form-control" value="<?=htmlspecialchars($sub['text'])?>" placeholder="Sub-menu Text"></td>
                                    <td><input type="text" class="form-control" value="<?=htmlspecialchars($sub['url'])?>" placeholder="Sub-menu URL"></td>
                                    <td>
                                        <select class="form-select">
                                            <?php foreach ($icon_options as $icon): ?>
                                                <option value="<?=$icon?>" <?=$icon==$sub['icon']?'selected':''?>><?=$icon?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="icon-preview"><i class="fa <?=$sub['icon']?>"></i></span>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-submenu">Delete</button></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Delete</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary" id="addRow">Add Menu Item</button>
        <button type="submit" class="btn btn-primary">Save Menu</button>
    </form>
    <a href="/admin/" class="btn btn-link mt-4">Back to Admin Dashboard</a>
</div>
<script src="/assets/js/jquery.min.js"></script>
<script>
$(function() {
    // Add new row
    $('#addRow').click(function() {
        let icons = <?php echo json_encode($icon_options); ?>;
        let iconOptions = icons.map(i => `<option value=\"${i}\">${i}</option>`).join('');
        $('#menuTable tbody').append(`
            <tr class=\"main-menu-row\">
                <td><input type=\"text\" class=\"form-control\"></td>
                <td><input type=\"text\" class=\"form-control\"></td>
                <td><select class=\"form-select\">${iconOptions}</select> <span class=\"icon-preview\"></span></td>
                <td><button type=\"button\" class=\"btn btn-sm btn-outline-primary add-submenu\">Add Sub-menu</button><table class=\"table table-sm submenu-table mb-0 mt-2\"><tbody></tbody></table></td>
                <td><button type=\"button\" class=\"btn btn-danger btn-sm remove-row\">Delete</button></td>
            </tr>
        `);
    });
    // Remove row
    $('#menuTable').on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });
    // Add sub-menu row
    $('#menuTable').on('click', '.add-submenu', function() {
        let icons = <?php echo json_encode($icon_options); ?>;
        let iconOptions = icons.map(i => `<option value=\"${i}\">${i}</option>`).join('');
        $(this).siblings('.submenu-table').find('tbody').append(`
            <tr class=\"submenu-row\">
                <td><input type=\"text\" class=\"form-control\" placeholder=\"Sub-menu Text\"></td>
                <td><input type=\"text\" class=\"form-control\" placeholder=\"Sub-menu URL\"></td>
                <td><select class=\"form-select\">${iconOptions}</select> <span class=\"icon-preview\"></span></td>
                <td><button type=\"button\" class=\"btn btn-danger btn-sm remove-submenu\">Delete</button></td>
            </tr>
        `);
    });
    // Remove sub-menu row
    $('#menuTable').on('click', '.remove-submenu', function() {
        $(this).closest('tr').remove();
    });
    // Icon preview (main menu)
    $('#menuTable').on('change', 'select', function() {
        let icon = $(this).val();
        $(this).siblings('.icon-preview').html(`<i class=\"fa ${icon}\"></i>`);
    });
    // On submit: collect menu & submenus
    $('#menuForm').submit(function(e) {
        let menu = [];
        $('#menuTable tbody tr.main-menu-row').each(function() {
            let text = $(this).find('input').eq(0).val().trim();
            let url = $(this).find('input').eq(1).val().trim();
            let icon = $(this).find('select').eq(0).val();
            let children = [];
            $(this).find('.submenu-table tbody tr.submenu-row').each(function() {
                let subtext = $(this).find('input').eq(0).val().trim();
                let suburl = $(this).find('input').eq(1).val().trim();
                let subicon = $(this).find('select').val();
                if (subtext && suburl && subicon) children.push({text: subtext, url: suburl, icon: subicon});
            });
            if (text && url && icon) menu.push(children.length ? {text, url, icon, children} : {text, url, icon});
        });
        $('#menu_json').val(JSON.stringify(menu));
        // Allow form to submit
    });
    // Initial icon preview
    $('#menuTable select').each(function() {
        let icon = $(this).val();
        $(this).siblings('.icon-preview').html(`<i class=\"fa ${icon}\"></i>`);
    });
});
</script>
</body>
</html>
