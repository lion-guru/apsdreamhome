<?php $pageTitle = $page_title ?? 'Real-time Data Processing'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-bolt me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $rd = $realtime_data ?? []; ?>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-stream me-2"></i>Data Streams</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <thead><tr><th>Stream</th><th>Rate</th><th>Volume</th></tr></thead>
                        <tbody>
                            <?php foreach (($rd['data_streams'] ?? []) as $k => $v): ?>
                                <tr><td class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></td><td><?= htmlspecialchars($v['rate'] ?? '-') ?></td><td><?= htmlspecialchars($v['volume'] ?? '-') ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Throughput Metrics</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($rd['throughput_metrics'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-clock me-2"></i>Processing Latency</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($rd['processing_latency'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-expand-arrows-alt me-2"></i>Scalability</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($rd['scalability_stats'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= is_bool($v) ? ($v ? 'Yes' : 'No') : htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
