<?php
/**
 * Pricing Applications View
 */
$page_title = $page_title ?? 'Pricing Applications';
$colony = $colony ?? [];
$applications = $applications ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="http://localhost/apsdreamhome/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/apsdreamhome/assets/css/premium-theme.css?v=12" rel="stylesheet">
    <link href="http://localhost/apsdreamhome/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/../layouts/admin_header.php'; ?>
    
    <main class="admin-main">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
                <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Pricing
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Colony: <?= htmlspecialchars($colony['name'] ?? '') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($applications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No applications yet</h5>
                            <p class="text-muted">Apply a pricing plan to see applications here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plan</th>
                                        <th>Version</th>
                                        <th>Base Price</th>
                                        <th>Plots Updated</th>
                                        <th>Total Value</th>
                                        <th>Applied By</th>
                                        <th>Applied At</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($app['plan_name'] ?? '') ?></td>
                                            <td><strong>v<?= (int)$app['plan_version'] ?></strong></td>
                                            <td>₹<?= number_format($app['base_price_per_sqft'] ?? 0, 2) ?>/sqft</td>
                                            <td><span class="badge bg-info"><?= (int)$app['plots_updated'] ?></span></td>
                                            <td><strong>₹<?= number_format($app['total_value'] ?? 0, 2) ?></strong></td>
                                            <td><?= htmlspecialchars($app['applied_by'] ?? '—') ?></td>
                                            <td><?= date('M d, Y H:i', strtotime($app['applied_at'])) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing-plan/<?= (int)$app['pricing_plan_id'] ?>/view" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye me-1"></i>Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</body>
</html>