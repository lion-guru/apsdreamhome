<?php
/**
 * Customer Colony Development Feed
 * Live milestone progress bars + site photos for the customer's booked colonies.
 */
$colonies = $colonies ?? [];
$base = BASE_URL ?? '';
$catIcons = [
    'roads' => 'fa-road', 'drainage' => 'fa-water', 'electricity' => 'fa-bolt',
    'boundary' => 'fa-fence', 'water' => 'fa-droplet', 'landscaping' => 'fa-tree', 'other' => 'fa-hammer',
];
$statusColors = ['completed' => 'success', 'in_progress' => 'primary', 'on_hold' => 'warning', 'not_started' => 'secondary', 'cancelled' => 'dark'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Colony Development Feed') ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        .feed-hero { background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; border-radius: 16px; }
        .milestone-card { border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; }
        .progress { height: 10px; border-radius: 6px; }
        .site-photo { width: 100%; height: 150px; object-fit: cover; border-radius: 10px; cursor: zoom-in; }
        .cat-badge { font-size: 0.7rem; }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-hard-hat me-2"></i>Colony Development Feed</h2>
            <p class="text-muted mb-0">Live construction progress of your booked colon<?= count($colonies) === 1 ? 'y' : 'ies' ?></p>
        </div>
        <a href="<?= BASE_URL ?>/customer/dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <?php if (empty($colonies)): ?>
        <div class="card border-0 bg-light"><div class="card-body text-center py-5">
            <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
            <h6 class="text-muted">No Booked Colonies</h6>
            <p class="text-muted small">Development updates will appear here once you book a plot.</p>
        </div></div>
    <?php else: ?>
        <?php foreach ($colonies as $c):
            $avg = round((float)($c['avg_progress'] ?? 0));
            $done = (int)($c['done_milestones'] ?? 0);
            $total = (int)($c['total_milestones'] ?? 0);
        ?>
        <div class="feed-hero p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($c['name'] ?? '') ?></h4>
                    <small class="opacity-75"><i class="fas fa-location-dot me-1"></i><?= htmlspecialchars(trim(($c['location'] ?? '') . ', ' . ($c['district_name'] ?? ''), ', ')) ?></small>
                </div>
                <div class="text-end">
                    <div class="fs-3 fw-bold"><?= $avg ?>%</div>
                    <small class="opacity-75"><?= $done ?>/<?= $total ?> milestones done</small>
                </div>
            </div>
            <div class="progress mt-2 bg-white bg-opacity-25">
                <div class="progress-bar bg-warning" style="width: <?= $avg ?>%"></div>
            </div>
        </div>

        <?php if (!empty($c['photos'])): ?>
        <h6 class="fw-bold mb-2"><i class="fas fa-camera me-1"></i> Latest Site Photos</h6>
        <div class="row g-2 mb-4">
            <?php foreach ($c['photos'] as $ph): ?>
                <div class="col-6 col-md-3">
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($ph['site_photo_path']) ?>" target="_blank">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($ph['site_photo_path']) ?>" class="site-photo" alt="<?= htmlspecialchars($ph['milestone_name'] ?? 'Site photo') ?>" loading="lazy">
                    </a>
                    <small class="text-muted"><?= htmlspecialchars($ph['milestone_name'] ?? '') ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <h6 class="fw-bold mb-2"><i class="fas fa-list-check me-1"></i> Milestones</h6>
        <div class="row g-2 mb-5">
            <?php if (empty($c['milestones'])): ?>
                <div class="col-12"><div class="alert alert-light border mb-0">Progress tracking starts soon for this colony.</div></div>
            <?php else: ?>
            <?php foreach ($c['milestones'] as $m):
                $pct = max(0, min(100, (int)($m['progress_pct'] ?? 0)));
                $st = $m['status'] ?? 'not_started';
            ?>
                <div class="col-md-6">
                    <div class="milestone-card p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span><i class="fas <?= $catIcons[$m['category']] ?? 'fa-hammer' ?> me-1 text-primary"></i>
                                <strong><?= htmlspecialchars($m['milestone_name'] ?? ucfirst($m['category'] ?? '')) ?></strong></span>
                            <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?> cat-badge"><?= ucfirst(str_replace('_', ' ', $st)) ?></span>
                        </div>
                        <div class="progress mb-1"><div class="progress-bar" style="width: <?= $pct ?>%"></div></div>
                        <small class="text-muted"><?= $pct ?>% · updated <?= !empty($m['updated_at']) ? date('d M Y', strtotime($m['updated_at'])) : '—' ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
