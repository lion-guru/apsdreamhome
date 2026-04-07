<?php
/**
 * User Inquiries Page
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inquiries - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../layouts/header_new_v2.php'; ?>

    <div class="container py-5">
        <h3 class="mb-4"><i class="fas fa-envelope me-2 text-success"></i>My Inquiries</h3>

        <?php if (empty($inquiries)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No inquiries yet</h5>
                    <p class="text-muted">Start exploring properties to make inquiries!</p>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inquiries as $inq): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?php echo $inq['type'] === 'property_listing' ? 'success' : 'info'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $inq['type'] ?? 'General')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <p class="mb-0" style="max-width: 300px;">
                                                <?php echo htmlspecialchars(substr($inq['message'] ?? '', 0, 100)); ?>
                                                <?php if (strlen($inq['message'] ?? '') > 100): ?>...<?php endif; ?>
                                            </p>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = match($inq['status'] ?? 'new') {
                                                'new' => 'primary',
                                                'contacted' => 'info',
                                                'pending' => 'warning',
                                                'in_progress' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($inq['status'] ?? 'new'); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClass = match($inq['priority'] ?? 'medium') {
                                                'high' => 'danger',
                                                'medium' => 'warning',
                                                'low' => 'info',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?php echo $priorityClass; ?>"><?php echo ucfirst($inq['priority'] ?? 'Medium'); ?></span>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($inq['created_at'])); ?>
                                            <br><small class="text-muted"><?php echo date('h:i A', strtotime($inq['created_at'])); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../../layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
