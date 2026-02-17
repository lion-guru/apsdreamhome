<?php
/**
 * Manage Site Settings
 * Standardized Admin Page
 */
require_once __DIR__ . '/core/init.php';

// Only superadmin can manage system-wide site settings
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=' . urlencode('Access Denied: Superadmin privilege required.'));
    exit();
}

$msg = '';
$error = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $db = \App\Core\App::database();
        foreach ($_POST['settings'] as $key => $value) {
            $db->execute("REPLACE INTO site_settings (setting_name, value) VALUES (:key, :value)", [
                'key' => $key,
                'value' => $value
            ]);
        }
        $msg = 'Settings updated successfully!';
    }
}

// Fetch all settings
$db = \App\Core\App::database();
$rows = $db->fetchAll("SELECT setting_name, value FROM site_settings ORDER BY setting_name");
$settings = [];
if ($rows) {
    foreach ($rows as $row) {
        $settings[$row['setting_name']] = $row['value'];
    }
}

$page_title = "Manage Site Settings";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Manage Site Settings</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Site Settings</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">System Settings Configuration</h4>
                        <?php if ($msg): ?>
                            <div class="alert alert-success"><?php echo h($msg); ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <?php echo getCsrfField(); ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Setting Name</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($settings as $name => $val): ?>
                                        <tr>
                                            <td style="width: 30%;"><strong><?php echo h($name); ?></strong></td>
                                            <td>
                                                <input type="text" name="settings[<?php echo h($name); ?>]" class="form-control" value="<?php echo h($val); ?>">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary">Save All Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
