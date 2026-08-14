<?php $pageTitle = $page_title ?? 'Edge Computing Research'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-flask me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php foreach (($research_areas ?? []) as $key => $r): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><?= htmlspecialchars($r['title'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                        <div class="progress mb-2" class="style-40280">
                            <div class="progress-bar bg-<?= ($r['progress'] ?? 0) < 50 ? 'danger' : (($r['progress'] ?? 0) < 75 ? 'warning' : 'success') ?>" class="style-85819"><?= $r['progress'] ?? 0 ?>%</div>
                        </div>
                        <p class="small mb-1"><strong>Researchers:</strong> <?= $r['researchers'] ?? 0 ?></p>
                        <p class="small mb-1"><strong>Focus:</strong> <?= htmlspecialchars($r['focus'] ?? '') ?></p>
                        <p class="small mb-0"><strong>Timeline:</strong> <?= htmlspecialchars($r['timeline'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
