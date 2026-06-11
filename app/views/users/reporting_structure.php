<?php
$reportingStructure = $reportingStructure ?? [];
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Reporting Structure</h1>

    <?php if (empty($reportingStructure)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-sitemap fa-3x mb-3"></i>
                <p class="mb-0">No reporting structure data available.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body aps-cp-card-body">
                <?php foreach ($reportingStructure as $level => $group): ?>
                    <?php if (is_numeric($level)): ?>
                        <!-- Top-level manager -->
                        <div class="text-center mb-4">
                            <div class="d-inline-block p-3 bg-primary text-white rounded-3 shadow-sm" style="min-width: 220px;">
                                <i class="fas fa-user-tie fa-2x mb-1"></i>
                                <h5 class="mb-0"><?= htmlspecialchars($group['name'] ?? '') ?></h5>
                                <small><?= htmlspecialchars($group['designation'] ?? '') ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Sub-level group -->
                        <div class="mb-3 ms-4 ps-4" style="border-left: 2px solid #dee2e6;">
                            <h6 class="text-muted mb-3"><?= htmlspecialchars($level) ?></h6>
                            <?php foreach ($group as $member): ?>
                                <div class="d-flex align-items-center mb-2 p-2 bg-light rounded-3" style="max-width: 400px;">
                                    <div class="me-3">
                                        <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($member['name'] ?? '') ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($member['designation'] ?? '') ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
