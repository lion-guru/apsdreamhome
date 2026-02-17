<?php
/**
 * API Sandbox - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

if (!isAdmin()) {
    header("location:index.php?error=access_denied");
    exit();
}

$db = \App\Core\App::database();
$sandboxes = $db->fetchAll("SELECT * FROM api_sandbox ORDER BY created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Sandbox - APS Dream Home</title>
    <?php include 'admin_header.php'; ?>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <h3 class="page-title">API Sandbox</h3>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Endpoint</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sandboxes)): ?>
                                    <tr><td colspan="4" class="text-center">No sandboxes found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($sandboxes as $sandbox): ?>
                                        <tr>
                                            <td><?php echo h($sandbox['id']); ?></td>
                                            <td><?php echo h($sandbox['name']); ?></td>
                                            <td><?php echo h($sandbox['endpoint']); ?></td>
                                            <td><?php echo h($sandbox['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'admin_footer.php'; ?>
</body>
</html>

