<?php $bd = $benchmark_data ?? []; $enc = $bd['encryption_performance'] ?? []; $td = $bd['threat_detection_performance'] ?? []; $auth = $bd['authentication_performance'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-gauge-high me-2"></i>Security Performance Benchmarks</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-lock text-primary me-2"></i>Encryption Performance</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($enc)): foreach ($enc as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <div class="d-flex justify-content-between"><small class="text-muted">Throughput</small><strong><?= htmlspecialchars($v['throughput'] ?? '-') ?></strong></div>
                            <div class="d-flex justify-content-between"><small class="text-muted">Latency</small><strong><?= htmlspecialchars($v['latency'] ?? '-') ?></strong></div>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No data</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-bug text-warning me-2"></i>Threat Detection</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($td)): foreach ($td as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <div class="d-flex justify-content-between"><small class="text-muted">Accuracy</small><strong><?= htmlspecialchars($v['accuracy'] ?? '-') ?></strong></div>
                            <div class="d-flex justify-content-between"><small class="text-muted">Response Time</small><strong><?= htmlspecialchars($v['response_time'] ?? '-') ?></strong></div>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No data</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-fingerprint text-success me-2"></i>Authentication</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($auth)): foreach ($auth as $k => $v): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold"><?= ucwords(str_replace('_', ' ', $k)) ?></h6>
                            <div class="d-flex justify-content-between"><small class="text-muted">Avg Time</small><strong><?= htmlspecialchars($v['average_time'] ?? '-') ?></strong></div>
                            <div class="d-flex justify-content-between"><small class="text-muted">Success Rate</small><strong><?= htmlspecialchars($v['success_rate'] ?? '-') ?></strong></div>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No data</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
