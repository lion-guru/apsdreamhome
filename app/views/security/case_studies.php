<?php $cases = $case_studies ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-book-open me-2"></i>Security Case Studies</h4>
    </div>
    <?php if (!empty($cases)): $i = 0; foreach ($cases as $key => $cs): $i++; ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-<?= $i === 1 ? 'microchip' : ($i === 2 ? 'robot' : 'shield-halved') ?> me-2 text-<?= $i === 1 ? 'primary' : ($i === 2 ? 'success' : 'warning') ?>"></i><?= htmlspecialchars($cs['title'] ?? '-') ?></h5>
                <div><span class="badge bg-info me-1"><?= htmlspecialchars($cs['implementation_time'] ?? '-') ?></span><span class="badge bg-success">ROI: <?= htmlspecialchars($cs['roi_achieved'] ?? '-') ?></span></div>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-triangle-exclamation me-1"></i>Challenge</h6>
                        <p class="small text-muted"><?= htmlspecialchars($cs['challenge'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-lightbulb me-1"></i>Solution</h6>
                        <p class="small text-muted"><?= htmlspecialchars($cs['solution'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-check-circle me-1"></i>Results</h6>
                        <?php $results = $cs['results'] ?? []; if (!empty($results)): ?>
                            <ul class="list-unstyled small mb-0"><?php foreach ($results as $r): ?><li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($r) ?></li><?php endforeach; ?></ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; else: ?>
        <div class="alert alert-info mb-0">No case studies available.</div>
    <?php endif; ?>
</div>
