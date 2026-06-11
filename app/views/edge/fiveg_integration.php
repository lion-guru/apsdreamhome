<?php $pageTitle = $page_title ?? '5G Network Integration'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-satellite-dish me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $fd = $fiveg_data ?? []; ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-signal"></i></div>
                    <h5 class="mb-1"><?= htmlspecialchars($fd['network_status']['average_speed'] ?? '-') ?></h5>
                    <small class="text-muted">Avg Speed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-clock"></i></div>
                    <h5 class="mb-1"><?= htmlspecialchars($fd['network_status']['latency'] ?? '-') ?></h5>
                    <small class="text-muted">Latency</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-2"><i class="fas fa-wifi"></i></div>
                    <h5 class="mb-1"><?= htmlspecialchars($fd['network_status']['network_coverage'] ?? '-') ?></h5>
                    <small class="text-muted">Coverage</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-server"></i></div>
                    <h5 class="mb-1"><?= number_format($fd['network_status']['connected_devices'] ?? 0) ?></h5>
                    <small class="text-muted">Connected Devices</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Coverage Areas</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Area</th><th>Coverage</th><th>Speed</th><th>Latency</th></tr></thead>
                            <tbody>
                                <?php foreach (($fd['coverage_areas'] ?? []) as $area => $data): ?>
                                    <tr><td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $area))) ?></td><td><?= htmlspecialchars($data['coverage'] ?? '-') ?></td><td><?= htmlspecialchars($data['speed'] ?? '-') ?></td><td><?= htmlspecialchars($data['latency'] ?? '-') ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Performance Metrics</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($fd['performance_metrics'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-microchip me-2"></i>Application Optimization</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($fd['application_optimization'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) ?></th><td><?= htmlspecialchars($v['improvement'] ?? '-') ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
