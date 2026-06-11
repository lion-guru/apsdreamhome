<?php
$activities = $activities ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Recent Activities</h1>
    </div>

    <?php if (empty($activities)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-history fa-3x mb-3"></i>
                <p class="mb-0">No activities recorded yet.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="position-relative" style="padding-left: 30px;">
            <div class="position-absolute start-0 top-0 bottom-0" style="width: 2px; background: #dee2e6; left: 14px;"></div>
            <?php foreach ($activities as $a): ?>
                <div class="card shadow-sm mb-3 ms-4 position-relative">
                    <div class="position-absolute rounded-circle bg-primary" style="width: 12px; height: 12px; left: -28px; top: 18px;"></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="card-title mb-1"><?= htmlspecialchars($a['description'] ?? '') ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($a['timestamp'] ?? '') ?></small>
                        </div>
                        <p class="card-text text-muted small mb-0">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($a['user'] ?? '') ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
