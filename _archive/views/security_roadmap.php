<?php $roadmap = $roadmap_data ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-map-signs me-2"></i>Security Roadmap</h4>
    </div>
    <div class="row g-4">
        <?php if (!empty($roadmap)): $year_keys = ['2024', '2025', '2026']; $year_colors = ['2024' => 'primary', '2025' => 'success', '2026' => 'warning']; ?>
            <?php foreach ($year_keys as $yr): $quarters = $roadmap[$yr] ?? []; ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-<?= $year_colors[$yr] ?? 'secondary' ?> text-white"><h5 class="mb-0"><?= htmlspecialchars($yr, ENT_QUOTES, 'UTF-8') ?></h5></div>
                        <div class="card-body aps-cp-card-body">
                            <?php if (!empty($quarters)): foreach ($quarters as $q => $item): ?>
                                <div class="mb-3 pb-2 border-bottom">
                                    <span class="badge bg-<?= $year_colors[$yr] ?? 'secondary' ?> mb-1"><?= strtoupper($q) ?></span>
                                    <p class="mb-0 small"><?= htmlspecialchars($item) ?></p>
                                </div>
                            <?php endforeach; else: ?><p class="text-muted mb-0">No milestones defined</p><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info mb-0">No roadmap data available.</div></div>
        <?php endif; ?>
    </div>
</div>
