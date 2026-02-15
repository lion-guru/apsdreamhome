<?php
// Simple Admin Panel for Managing site_settings Table
define('IN_ADMIN', true);
require_once __DIR__ . '/../includes/config.php';
global $con;
$conn = $con;
if (!$conn) die('DB connection failed.');
?>

<?php include __DIR__ . '/includes/admin_nav.php'; ?>

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $conn->prepare("REPLACE INTO site_settings (setting_name, value) VALUES (?, ?)");
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
    }
    header('Location: manage_site_settings.php?updated=1');
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Fetch all settings
$res = $conn->query("SELECT setting_name, value FROM site_settings ORDER BY setting_name");
$settings = [];
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['value'];
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Site Settings</title>
<style>body{font-family:sans-serif;background:#f8f9fa;}table{background:#fff;margin:2em auto;border-radius:8px;box-shadow:0 2px 8px #ccc;padding:2em;}th,td{padding:8px;}input[type=text]{width:350px;}button{padding:8px 16px;}</style>
</head>
<body>
<h2 style="text-align:center">Manage Site Settings</h2>
<?php if(isset($_GET['updated'])) echo '<p style="color:green;text-align:center">Settings updated!</p>'; ?>
<form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><table><tr><th>Setting Name</th><th>Value</th></tr>
<?php foreach($settings as $name=>$val): ?>
<tr><td><?=htmlspecialchars($name)?></td><td><input type="text" name="settings[<?=htmlspecialchars($name)?>]" value="<?=htmlspecialchars($val)?>"></td></tr>
<?php endforeach; ?>
</table>
<div style="text-align:center"><button type="submit">Save All</button></div>
</form>
</body></html>
