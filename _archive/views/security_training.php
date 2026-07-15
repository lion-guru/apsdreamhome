<?php $programs = $training_programs ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Advanced Security Training</h4>
    </div>
    <div class="row g-4">
        <?php if (!empty($programs)): foreach ($programs as $key => $p): ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-1 text-<?= $key === 'cybersecurity_basics' ? 'primary' : ($key === 'advanced_threat_detection' ? 'danger' : ($key === 'quantum_security' ? 'info' : 'success')) ?> mb-3"><i class="fas fa-<?= $key === 'cybersecurity_basics' ? 'book' : ($key === 'advanced_threat_detection' ? 'bug' : ($key === 'quantum_security' ? 'microchip' : 'sitemap')) ?>"></i></div>
                        <h5 class="card-title"><?= htmlspecialchars($p['title'] ?? '-') ?></h5>
                        <div class="mb-2"><span class="badge bg-secondary"><?= htmlspecialchars($p['audience'] ?? '-') ?></span></div>
                        <p class="text-muted small mb-1"><i class="fas fa-clock me-1"></i><?= htmlspecialchars($p['duration'] ?? '-') ?></p>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between"><small>Completion</small><small class="fw-bold"><?= htmlspecialchars($p['completion_rate'] ?? '0%') ?></small></div>
                            <div class="progress" style="height:6px"><div class="progress-bar bg-success" style="width:<?= (int)($p['completion_rate'] ?? 0) ?>%"></div></div>
                        </div>
                        <div class="mt-2"><small class="text-muted">Score: <strong><?= htmlspecialchars($p['assessment_score'] ?? '-') ?></strong></small></div>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info mb-0">No training programs available.</div></div>
        <?php endif; ?>
    </div>
</div>
