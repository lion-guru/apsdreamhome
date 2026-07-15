<?php $zd = $zero_trust_data ?? []; $overview = $zd['architecture_overview'] ?? []; $policies = $zd['access_policies'] ?? []; $verification = $zd['continuous_verification'] ?? []; $progress = $zd['implementation_progress'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-shield-halved me-2"></i>Zero-Trust Security Architecture</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-gem me-2"></i>Core Principles</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $principles = $overview['core_principles'] ?? []; if (!empty($principles)): ?>
                        <?php foreach ($principles as $key => $desc): ?>
                            <div class="mb-3"><h6 class="text-primary"><?= ucwords(str_replace('_', ' ', $key)) ?></h6><p class="text-muted small mb-0"><?= htmlspecialchars($desc) ?></p></div>
                        <?php endforeach; ?>
                    <?php else: ?><p class="text-muted mb-0">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-cubes me-2"></i>Architecture Components</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $components = $overview['architecture_components'] ?? []; if (!empty($components)): ?>
                        <?php foreach ($components as $key => $desc): ?>
                            <div class="mb-3"><h6 class="text-success"><?= ucwords(str_replace('_', ' ', $key)) ?></h6><p class="text-muted small mb-0"><?= htmlspecialchars($desc) ?></p></div>
                        <?php endforeach; ?>
                    <?php else: ?><p class="text-muted mb-0">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-key me-2"></i>Access Policies</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Policy Types</h6>
                    <?php $ptypes = $policies['policy_types'] ?? []; if (!empty($ptypes)): ?>
                        <?php foreach ($ptypes as $key => $desc): ?>
                            <div class="mb-2"><span class="badge bg-primary me-2"><?= ucwords(str_replace('_', ' ', $key)) ?></span><small class="text-muted"><?= htmlspecialchars($desc) ?></small></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Policy Enforcement</h6>
                    <?php $penf = $policies['policy_enforcement'] ?? []; if (!empty($penf)): ?>
                        <?php foreach ($penf as $key => $desc): ?>
                            <div class="mb-2"><span class="badge bg-info me-2"><?= ucwords(str_replace('_', ' ', $key)) ?></span><small class="text-muted"><?= htmlspecialchars($desc) ?></small></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-check-double me-2"></i>Continuous Verification</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Verification Methods</h6>
                    <?php $vm = $verification['verification_methods'] ?? []; if (!empty($vm)): ?>
                        <?php foreach ($vm as $key => $desc): ?>
                            <div class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong><?= ucwords(str_replace('_', ' ', $key)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($desc) ?></small></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Verification Frequency</h6>
                    <?php $vf = $verification['verification_frequency'] ?? []; if (!empty($vf)): ?>
                        <?php foreach ($vf as $key => $desc): ?>
                            <div class="mb-2"><small><span class="badge bg-warning text-dark me-2"><?= ucwords(str_replace('_', ' ', $key)) ?></span><?= htmlspecialchars($desc) ?></small></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Implementation Progress</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php $pkeys = ['planning_completed' => 'Planning', 'infrastructure_deployed' => 'Infrastructure', 'policies_implemented' => 'Policies', 'monitoring_active' => 'Monitoring', 'training_completed' => 'Training']; ?>
                        <?php foreach ($pkeys as $k => $l): ?>
                            <?php $v = $progress[$k] ?? '0%'; $p = (int) $v; ?>
                            <div class="col-md-4 col-lg">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="fs-3 fw-bold text-<?= $p >= 90 ? 'success' : ($p >= 50 ? 'warning' : 'danger') ?>"><?= htmlspecialchars($v) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 text-center"><small class="text-muted"><i class="fas fa-bullseye me-1"></i>Target Go-Live: <strong><?= htmlspecialchars($progress['go_live_target'] ?? 'TBD') ?></strong></small></div>
                </div>
            </div>
        </div>
    </div>
</div>
