<?php
/**
 * Pricing Plan History View
 */
$page_title = $page_title ?? 'Pricing Plan History';
$colony = $colony ?? [];
$plans = $plans ?? [];
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
                <div>
                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Pricing
                    </a>
                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing-plan/create" class="btn btn-primary ms-2">
                        <i class="fas fa-plus me-1"></i>New Plan Version
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Colony: <?= htmlspecialchars($colony['name'] ?? '') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($plans)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No pricing plans yet</h5>
                            <p class="text-muted">Create your first pricing plan version to get started.</p>
                            <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing-plan/create" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-1"></i>Create First Plan
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Version</th>
                                        <th>Name</th>
                                        <th>Base Price</th>
                                        <th>Premiums</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($plans as $plan): ?>
                                        <tr class="<?= $plan['is_active'] ? 'table-success' : '' ?>">
                                            <td><strong>v<?= (int)$plan['version'] ?></strong></td>
                                            <td><?= htmlspecialchars($plan['name']) ?></td>
                                            <td>₹<?= number_format($plan['base_price_per_sqft'], 2) ?>/sqft</td>
                                            <td>
                                                <small>
                                                    <?php 
                                                    $premiums = json_decode($plan['premiums'], true);
                                                    if ($premiums) {
                                                        echo 'Corner: ' . ($premiums['corner_premium_pct'] ?? 0) . '% ';
                                                        echo 'Park: ' . ($premiums['park_facing_premium_pct'] ?? 0) . '% ';
                                                        echo 'Road: ' . ($premiums['wide_road_premium_pct'] ?? 0) . '%';
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($plan['is_active']): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($plan['created_by'] ?? '—') ?></td>
                                            <td><?= date('M d, Y H:i', strtotime($plan['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (!$plan['is_active']): ?>
                                                        <form method="POST" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing-plan/<?= (int)$plan['id'] ?>/activate">
                                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                            <button type="submit" class="btn btn-outline-success" title="Activate">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing-plan/<?= (int)$plan['id'] ?>/apply" class="btn btn-outline-primary" title="Apply to plots">
                                                        <i class="fas fa-magic"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$colony['id'] ?>/pricing-plan/<?= (int)$plan['id'] ?>/view" class="btn btn-outline-info" title="View details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
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