<?php
// Super Admin MLM Commission Settings Page
require_once __DIR__ . '/../includes/config/config.php';
session_start();
// TODO: Add super admin authentication check

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_share = isset($_POST['company_share']) ? floatval($_POST['company_share']) : 0;
    $levels = isset($_POST['levels']) ? $_POST['levels'] : [];
    // Save to settings table
    $con->query("DELETE FROM mlm_commission_settings");
    $stmt = $con->prepare("INSERT INTO mlm_commission_settings (level, percent) VALUES (?, ?)");
    foreach ($levels as $level => $percent) {
        $stmt->bind_param('id', $level, $percent);
        $stmt->execute();
    }
    $stmt->close();
    // Save company share
    $con->query("DELETE FROM mlm_company_share");
    $stmt2 = $con->prepare("INSERT INTO mlm_company_share (share_percent) VALUES (?)");
    $stmt2->bind_param('d', $company_share);
    $stmt2->execute();
    $stmt2->close();
    $msg = 'Settings saved!';
}
// Fetch current settings
$settings = [];
$res = $con->query("SELECT level, percent FROM mlm_commission_settings ORDER BY level ASC");
while ($row = $res->fetch_assoc()) {
    $settings[$row['level']] = $row['percent'];
}
$company_share = 25;
$res2 = $con->query("SELECT share_percent FROM mlm_company_share LIMIT 1");
if ($row2 = $res2->fetch_assoc()) {
    $company_share = $row2['share_percent'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MLM Commission Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>MLM Commission Plan Settings</h2>
        <?php if (!empty($msg)) echo '<div class="alert alert-success">'.$msg.'</div>'; ?>
        <form method="post">
            <div class="mb-3">
                <label for="company_share" class="form-label">Company Max Commission Share (%)</label>
                <input type="number" step="0.01" min="0" max="100" class="form-control" name="company_share" id="company_share" value="<?php echo htmlspecialchars($company_share); ?>" required>
            </div>
            <h5>Level-wise Commission Distribution</h5>
            <div id="levels">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label">Level <?php echo $i; ?></label>
                        <div class="col-sm-4">
                            <input type="number" step="0.01" min="0" max="100" class="form-control" name="levels[<?php echo $i; ?>]" value="<?php echo isset($settings[$i]) ? htmlspecialchars($settings[$i]) : ''; ?>">
                        </div>
                        <div class="col-sm-6 text-muted small">% of company share for this level</div>
                    </div>
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
        </form>
    </div>
</body>
</html>
