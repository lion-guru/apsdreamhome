<?php $asd = $ai_security_data ?? []; $anomaly = $asd['anomaly_detection'] ?? []; $behavioral = $asd['behavioral_analysis'] ?? []; $predictive = $asd['predictive_security'] ?? []; $automated = $asd['automated_response'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-brain me-2"></i>AI-Powered Security Monitoring</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body aps-cp-card-body"><div class="fs-1 text-primary mb-2"><i class="fas fa-bullseye"></i></div><h3 class="fw-bold"><?= htmlspecialchars($anomaly['detection_accuracy'] ?? '0%') ?></h3><small class="text-muted">Detection Accuracy</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body aps-cp-card-body"><div class="fs-1 text-success mb-2"><i class="fas fa-check-circle"></i></div><h3 class="fw-bold"><?= htmlspecialchars($anomaly['false_positive_rate'] ?? '0%') ?></h3><small class="text-muted">False Positive Rate</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body aps-cp-card-body"><div class="fs-1 text-warning mb-2"><i class="fas fa-chart-line"></i></div><h3 class="fw-bold"><?= htmlspecialchars($predictive['threat_prediction']['accuracy'] ?? '0%') ?></h3><small class="text-muted">Threat Prediction Accuracy</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body aps-cp-card-body"><div class="fs-1 text-info mb-2"><i class="fas fa-gauge-high"></i></div><h3 class="fw-bold"><?= htmlspecialchars($automated['response_automation_rate'] ?? '0%') ?></h3><small class="text-muted">Response Automation</small></div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-eye me-2"></i>Anomaly Detection</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Detection Methods</h6>
                    <?php $dm = $anomaly['detection_methods'] ?? []; foreach ($dm as $k => $v): ?><div class="mb-2"><span class="badge bg-primary me-2"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Response Integration</h6>
                    <?php $ri = $anomaly['response_integration'] ?? []; foreach ($ri as $k => $v): ?><div class="mb-2"><span class="badge bg-info me-2"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-user-tag me-2"></i>Behavioral Analysis</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">User Behavior Models</h6>
                    <?php $ubm = $behavioral['user_behavior_models'] ?? []; foreach ($ubm as $k => $v): ?><div class="mb-2"><i class="fas fa-user-check text-success me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Risk Scoring</h6>
                    <?php $rs = $behavioral['risk_scoring'] ?? []; foreach ($rs as $k => $v): ?><div class="mb-2"><i class="fas fa-triangle-exclamation text-warning me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-robot me-2"></i>Automated Response</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php $rc = $automated['response_categories'] ?? []; foreach ($rc as $k => $v): ?>
                            <div class="col-md-6"><div class="p-3 bg-light rounded text-center h-100"><i class="fas fa-<?= $k === 'threat_containment' ? 'ban' : ($k === 'user_notification' ? 'bell' : ($k === 'system_recovery' ? 'rotate' : 'microscope')) ?> fa-2x text-primary mb-2"></i><h6><?= ucwords(str_replace('_', ' ', $k)) ?></h6><small class="text-muted"><?= htmlspecialchars($v) ?></small></div></div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <h6 class="fw-bold text-secondary">Automation Benefits</h6>
                    <?php $ab = $automated['automation_benefits'] ?? []; foreach ($ab as $k => $v): ?><div class="mb-1"><i class="fas fa-plus-circle text-success me-1"></i><small><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-crystal-ball me-2"></i>Predictive Security</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $tp = $predictive['threat_prediction'] ?? []; ?>
                    <div class="row g-3 mb-3">
                        <div class="col-4"><div class="text-center p-2 bg-light rounded"><div class="fw-bold text-primary"><?= htmlspecialchars($tp['accuracy'] ?? '-') ?></div><small>Accuracy</small></div></div>
                        <div class="col-4"><div class="text-center p-2 bg-light rounded"><div class="fw-bold text-info"><?= htmlspecialchars($tp['prediction_horizon'] ?? '-') ?></div><small>Horizon</small></div></div>
                        <div class="col-4"><div class="text-center p-2 bg-light rounded"><div class="fw-bold text-warning"><?= count($tp['threat_categories'] ?? []) ?></div><small>Categories</small></div></div>
                    </div>
                    <h6 class="fw-bold text-secondary">Preventive Actions</h6>
                    <?php $pa = $predictive['preventive_actions'] ?? []; foreach ($pa as $k => $v): ?><div class="mb-2"><i class="fas fa-shield text-success me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
