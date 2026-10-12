<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-history me-2 text-info"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/gst/dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Return Type</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>ARN</th>
                            <th>Filed Date</th>
                            <th>Filed By</th>
                            <th>Error</th>
                            <th>Retries</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="10" class="text-center py-5 text-muted">No filing logs found</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><span class="badge bg-<?= $log['return_type'] === 'GSTR1' ? 'info' : 'warning' ?>"><?= htmlspecialchars($log['return_type']) ?></span></td>
                                    <td><?= htmlspecialchars($log['return_period']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $log['status']==='filed'?'success':
                                            ($log['status']==='failed'?'danger':
                                            ($log['status']==='validated'?'info':
                                            ($log['status']==='computed'?'warning':'secondary'))) ?>">
                                            <?= ucfirst($log['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($log['arn'] ?? '-') ?></td>
                                    <td><?= $log['filed_date'] ? date('d M Y H:i', strtotime($log['filed_date'])) : '-' ?></td>
                                    <td><?= $log['filed_by'] ? 'User #' . $log['filed_by'] : '-' ?></td>
                                    <td class="small text-danger"><?= htmlspecialchars(mb_strimwidth($log['error_message'] ?? '', 0, 50, '...')) ?></td>
                                    <td><?= $log['retry_count'] ?></td>
                                    <td><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                                    <td class="text-end">
                                        <?php if ($log['status'] === 'draft' || $log['status'] === 'computed'): ?>
                                            <a href="<?= $base ?>/admin/gst/gstr1?period=<?= urlencode($log['return_period']) ?>" class="btn btn-sm btn-outline-primary">Open</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>