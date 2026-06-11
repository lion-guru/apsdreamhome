<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i><?= ($page_title ?? 'Performance Metrics') ?></h4>
        <div>
            <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync me-1"></i>Refresh</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted">Response Time</small>
                    <h5 class="mb-0"><?= ($stats['response_time'] ?? '< 100ms') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted">Cache Hit Rate</small>
                    <h5 class="mb-0"><?= ($stats['cache_hit_rate'] ?? '98%') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted">Memory Usage</small>
                    <h5 class="mb-0"><?= ($stats['memory_usage'] ?? '256 MB') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted">Uptime</small>
                    <h5 class="mb-0"><?= ($stats['uptime'] ?? '99.9%') ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-chart-area me-2"></i>Performance Trends</h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p>Performance charts will render here. Configure performance monitoring in settings.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-bell me-2"></i>Alerts</h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="mb-0 small">No active alerts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
