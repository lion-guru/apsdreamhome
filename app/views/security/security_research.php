<?php $projects = $research_projects ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-flask me-2"></i>Security Research & Development</h4>
    </div>
    <div class="row g-4">
        <?php if (!empty($projects)): foreach ($projects as $key => $p): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($p['title'] ?? '-') ?></h5>
                            <span class="badge bg-<?= (($p['status'] ?? '') === 'Active Research' || ($p['status'] ?? '') === 'In Development' ? 'warning' : (($p['status'] ?? '') === 'Implementation Phase' ? 'success' : 'secondary')) ?>"><?= htmlspecialchars($p['status'] ?? '-') ?></span>
                        </div>
                        <p class="text-muted small mb-2"><i class="fas fa-calendar me-1"></i>Timeline: <?= htmlspecialchars($p['timeline'] ?? '-') ?> | <i class="fas fa-users me-1"></i><?= htmlspecialchars($p['researchers'] ?? '0') ?> researchers</p>
                        <p class="card-text small"><?= htmlspecialchars($p['focus'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info mb-0">No research projects available.</div></div>
        <?php endif; ?>
    </div>
</div>
