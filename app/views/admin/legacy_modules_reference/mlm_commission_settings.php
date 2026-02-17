<?php
// Super Admin MLM Commission Settings Page
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setSessionget_flash('error', "Invalid security token. Please refresh the page and try again.");
        header("Location: mlm_commission_settings.php");
        exit();
    }

    $company_share = isset($_POST['company_share']) ? floatval($_POST['company_share']) : 0;
    $levels = isset($_POST['levels']) ? $_POST['levels'] : [];
    
    try {
        $db->beginTransaction();
        
        $db->execute("DELETE FROM mlm_commission_settings");
        foreach ($levels as $level => $percent) {
            if ($percent !== '') {
                $db->execute("INSERT INTO mlm_commission_settings (level, percent) VALUES (:level, :percent)", [
                    'level' => $level,
                    'percent' => $percent
                ]);
            }
        }
        
        // Save company share
        $db->execute("DELETE FROM mlm_company_share");
        $db->execute("INSERT INTO mlm_company_share (share_percent) VALUES (:share_percent)", [
            'share_percent' => $company_share
        ]);
        
        $db->commit();
        setSessionget_flash('success', 'Settings saved!');
    } catch (Exception $e) {
        if ($db->isInTransaction()) {
            $db->rollBack();
        }
        setSessionget_flash('error', "Error saving settings: " . $e->getMessage());
    }
    
    header("Location: mlm_commission_settings.php");
    exit();
}

// Fetch current settings
$settings = [];
$rows = $db->fetchAll("SELECT level, percent FROM mlm_commission_settings ORDER BY level ASC");
foreach ($rows as $row) {
    $settings[$row['level']] = $row['percent'];
}

$company_share = 25;
$row2 = $db->fetch("SELECT share_percent FROM mlm_company_share LIMIT 1");
if ($row2) {
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
        <?php if ($success = getSessionget_flash('success')): ?>
            <div class="alert alert-success"><?php echo h($success); ?></div>
        <?php endif; ?>
        <?php if ($error = getSessionget_flash('error')): ?>
            <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <?php echo getCsrfField(); ?>
            <div class="mb-3">
                <label for="company_share" class="form-label">Company Max Commission Share (%)</label>
                <input type="number" step="0.01" min="0" max="100" class="form-control" name="company_share" id="company_share" value="<?php echo h($company_share); ?>" required>
            </div>
            <h5>Level-wise Commission Distribution</h5>
            <div id="levels">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label">Level <?php echo $i; ?></label>
                        <div class="col-sm-4">
                            <input type="number" step="0.01" min="0" max="100" class="form-control" name="levels[<?php echo $i; ?>]" value="<?php echo isset($settings[$i]) ? h($settings[$i]) : ''; ?>">
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

