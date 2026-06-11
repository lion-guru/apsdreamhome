<?php $ps = $partnerships ?? []; $cyber = $ps['cybersecurity_firms'] ?? []; $research = $ps['research_institutions'] ?? []; $govt = $ps['government_agencies'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-handshake me-2"></i>Security Partnerships</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-building-shield text-primary me-2"></i>Cybersecurity Firms</h5>
            <?php if (!empty($cyber)): foreach ($cyber as $c): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body aps-cp-card-body">
                        <h6 class="fw-bold"><?= htmlspecialchars($c['partner'] ?? '-') ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($c['collaboration'] ?? '-') ?></p>
                        <?php $js = $c['joint_solutions'] ?? []; if (!empty($js)): ?>
                            <ul class="list-unstyled mb-0 small"><li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i><?= implode('</li><li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i>', array_map('htmlspecialchars', $js)) ?></li></ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; else: ?><p class="text-muted">No data</p><?php endif; ?>
        </div>
        <div class="col-md-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-flask text-warning me-2"></i>Research Institutions</h5>
            <?php if (!empty($research)): foreach ($research as $r): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body aps-cp-card-body">
                        <h6 class="fw-bold"><?= htmlspecialchars($r['partner'] ?? '-') ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($r['collaboration'] ?? '-') ?></p>
                        <?php $jp = $r['joint_projects'] ?? []; if (!empty($jp)): ?>
                            <ul class="list-unstyled mb-0 small"><li class="mb-1"><i class="fas fa-flask text-info me-1"></i><?= implode('</li><li class="mb-1"><i class="fas fa-flask text-info me-1"></i>', array_map('htmlspecialchars', $jp)) ?></li></ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; else: ?><p class="text-muted">No data</p><?php endif; ?>
        </div>
        <div class="col-md-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-landmark text-danger me-2"></i>Government Agencies</h5>
            <?php if (!empty($govt)): foreach ($govt as $g): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body aps-cp-card-body">
                        <h6 class="fw-bold"><?= htmlspecialchars($g['partner'] ?? '-') ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($g['collaboration'] ?? '-') ?></p>
                        <?php $jp = $g['joint_projects'] ?? []; if (!empty($jp)): ?>
                            <ul class="list-unstyled mb-0 small"><li class="mb-1"><i class="fas fa-gov text-danger me-1"></i><?= implode('</li><li class="mb-1"><i class="fas fa-gov text-danger me-1"></i>', array_map('htmlspecialchars', $jp)) ?></li></ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; else: ?><p class="text-muted">No data</p><?php endif; ?>
        </div>
    </div>
</div>
