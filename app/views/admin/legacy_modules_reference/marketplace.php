<?php
require_once __DIR__ . '/core/init.php';

// Check if user is logged in and has admin privileges
adminAccessControl(['superadmin', 'admin']);

$db = \App\Core\App::database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }

    // Logic to add app would go here
    $app_name = $_POST['app_name'] ?? '';
    $provider = $_POST['provider'] ?? '';
    $app_url = $_POST['app_url'] ?? '';

    $sql = "INSERT INTO marketplace_apps (app_name, provider, app_url, created_at) VALUES (?, ?, ?, NOW())";
    $db->execute($sql, [$app_name, $provider, $app_url]);

    $message = "App added successfully!";
}

$apps = $db->fetchAll("SELECT * FROM marketplace_apps ORDER BY created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Marketplace & Ecosystem</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'>
</head>
<body>
<div class='container py-4'>
    <h2>Marketplace & Ecosystem</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method='post'>
        <?php echo getCsrfField(); ?>
        <div class='mb-3'>
            <label>App/Integration Name</label>
            <input type='text' name='app_name' class='form-control' required>
        </div>
        <div class='mb-3'>
            <label>Provider/Developer</label>
            <input type='text' name='provider' class='form-control' required>
        </div>
        <div class='mb-3'>
            <label>App URL</label>
            <input type='text' name='app_url' class='form-control' required>
        </div>
        <button class='btn btn-success'>Add App</button>
    </form>

    <table class='table table-bordered mt-4'>
        <thead>
            <tr>
                <th>Name</th>
                <th>Provider</th>
                <th>URL</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($apps)): ?>
                <?php foreach($apps as $a): ?>
                    <tr>
                        <td><?= h($a['app_name']) ?></td>
                        <td><?= h($a['provider']) ?></td>
                        <td><?= h($a['app_url']) ?></td>
                        <td><?= $a['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No apps found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <p class='mt-3'>*Allow third-party developers/partners to build and offer integrations, apps, or services on your platform.</p>
</div>
</body>
</html>
