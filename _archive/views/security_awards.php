<?php $ad = $awards_data ?? []; $awards = $ad['received_awards'] ?? []; $recognition = $ad['industry_recognition'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-trophy me-2"></i>Security Awards & Recognition</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <h5 class="fw-bold mb-3"><i class="fas fa-medal text-warning me-2"></i>Received Awards</h5>
            <?php if (!empty($awards)): foreach ($awards as $k => $a): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div><h6 class="fw-bold mb-1"><?= htmlspecialchars($a['award'] ?? '-') ?></h6><p class="text-muted small mb-0"><?= htmlspecialchars($a['organization'] ?? '-') ?> — <?= htmlspecialchars($a['category'] ?? '-') ?></p></div>
                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($a['date_received'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; else: ?><p class="text-muted">No awards data</p><?php endif; ?>
        </div>
        <div class="col-md-5">
            <h5 class="fw-bold mb-3"><i class="fas fa-star text-info me-2"></i>Industry Recognition</h5>
            <?php if (!empty($recognition)): foreach ($recognition as $k => $r): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body aps-cp-card-body">
                        <h6 class="fw-bold"><?= htmlspecialchars($r['recognition'] ?? '-') ?></h6>
                        <div class="d-flex justify-content-between"><small class="text-muted">Category</small><strong><?= htmlspecialchars($r['category'] ?? '-') ?></strong></div>
                        <div class="d-flex justify-content-between"><small class="text-muted">Year</small><strong><?= htmlspecialchars($r['year'] ?? '-') ?></strong></div>
                        <div class="d-flex justify-content-between"><small class="text-muted">Position</small><strong><?= htmlspecialchars($r['position'] ?? $r['score'] ?? '-') ?></strong></div>
                    </div>
                </div>
            <?php endforeach; else: ?><p class="text-muted">No recognition data</p><?php endif; ?>
        </div>
    </div>
</div>
