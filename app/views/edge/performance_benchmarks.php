<?php $pageTitle = $page_title ?? 'Edge Computing Benchmarks'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-chart-bar me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $bd = $benchmark_data ?? []; ?>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-clock me-2"></i>Latency Benchmarks</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($bd['latency_benchmarks'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><span class="badge bg-success"><?= htmlspecialchars($v['improvement'] ?? '-') ?></span></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Throughput Benchmarks</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($bd['throughput_benchmarks'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v['rate'] ?? $v['capacity'] ?? '-') ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-expand-arrows-alt me-2"></i>Scalability Benchmarks</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($bd['scalability_benchmarks'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v['performance'] ?? $v['scale_rate'] ?? '-') ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
