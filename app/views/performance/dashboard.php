<?php
/**
 * Performance Dashboard View
 * Data: $metrics
 */
$pageTitle = $pageTitle ?? 'Performance Dashboard';
$metrics = $metrics ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-tachometer-alt me-2 text-primary"></i><?= htmlspecialchars($pageTitle) ?></h2>
        <button onclick="location.reload()" class="btn btn-outline-primary"><i class="fas fa-sync me-1"></i> Refresh</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock text-primary fa-2x mb-2"></i>
                    <h5 class="mb-1"><?= number_format($metrics['page_load'] ?? 0, 3) ?>s</h5>
                    <small class="text-muted">Avg Page Load</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-database text-info fa-2x mb-2"></i>
                    <h5 class="mb-1"><?= number_format($metrics['queries_per_sec'] ?? 0) ?></h5>
                    <small class="text-muted">DB Queries/sec</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-memory text-success fa-2x mb-2"></i>
                    <h5 class="mb-1"><?= number_format($metrics['cache_hit_rate'] ?? 0, 1) ?>%</h5>
                    <small class="text-muted">Cache Hit Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                    <h5 class="mb-1"><?= number_format($metrics['error_rate'] ?? 0, 2) ?>%</h5>
                    <small class="text-muted">Error Rate</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-server me-2"></i>System Resources</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-primary mb-1"><?= number_format($metrics['memory_used_mb'] ?? 0) ?> MB</h4>
                                <small class="text-muted">Memory Used</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-info mb-1"><?= number_format($metrics['memory_peak_mb'] ?? 0) ?> MB</h4>
                                <small class="text-muted">Peak Memory</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-warning mb-1"><?= number_format($metrics['cpu_usage'] ?? 0, 1) ?>%</h4>
                                <small class="text-muted">CPU Usage</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-secondary mb-1"><?= $metrics['disk_free_gb'] ?? 0 ?> GB</h4>
                                <small class="text-muted">Disk Free</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card h-100">
                <div class="card-header aps-cp-card-header"><i class="fas fa-database me-2"></i>Database Stats</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-primary mb-1"><?= number_format($metrics['total_tables'] ?? 0) ?></h4>
                                <small class="text-muted">Tables</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-success mb-1"><?= number_format($metrics['total_rows'] ?? 0) ?></h4>
                                <small class="text-muted">Total Rows</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-info mb-1"><?= number_format($metrics['db_size_mb'] ?? 0, 1) ?> MB</h4>
                                <small class="text-muted">DB Size</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-2">
                        <div class="col-6">
                            <small>Slow Queries:</small>
                            <div class="fw-bold"><?= number_format($metrics['slow_queries'] ?? 0) ?></div>
                        </div>
                        <div class="col-6">
                            <small>Total Queries:</small>
                            <div class="fw-bold"><?= number_format($metrics['total_queries'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-plug me-2"></i>PHP Extensions</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php 
                        $required = ['pdo_mysql','mysqli','mbstring','openssl','curl','gd','zip','json','intl','sockets','redis','opcache'];
                        $loaded = get_loaded_extensions();
                        foreach ($required as $ext): ?>
                        <span class="badge bg-<?= in_array($ext, $loaded) ? 'success' : 'danger' ?> me-1 mb-1"><?= $ext ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>System Info</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td>PHP Version</td><td><?= phpversion() ?></td></tr>
                            <tr><td>Server Software</td><td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td></tr>
                            <tr><td>OS</td><td><?= php_uname('s') . ' ' . php_uname('r') ?></td></tr>
                            <tr><td>Memory Limit</td><td><?= ini_get('memory_limit') ?></td></tr>
                            <tr><td>Max Execution Time</td><td><?= ini_get('max_execution_time') ?>s</td></tr>
                            <tr><td>OPcache</td><td><?= function_exists('opcache_get_status') && @opcache_get_status()['opcache_enabled'] ? 'Enabled' : 'Disabled' ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>