<?php $pageTitle = $page_title ?? 'Mobile Edge Computing'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-mobile-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $md = $mobile_data ?? []; ?>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-network-wired me-2"></i>MEC Nodes</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Type</th><th>Count</th><th>Capacity</th></tr></thead>
                            <tbody>
                                <?php foreach (($md['mec_nodes'] ?? []) as $k => $v): ?>
                                    <tr><td class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></td><td><?= number_format($v['count'] ?? 0) ?></td><td><?= htmlspecialchars($v['capacity'] ?? '-') ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-battery-three-quarters me-2"></i>Battery Optimization</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach (($md['battery_optimization'] ?? []) as $k => $v): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></span>
                            <span class="badge bg-success"><?= htmlspecialchars($v) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Mobile Optimization</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach (($md['mobile_optimization'] ?? []) as $k => $v): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></span>
                            <span class="badge bg-info"><?= htmlspecialchars($v) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Latency Reduction</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($md['latency_reduction'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
