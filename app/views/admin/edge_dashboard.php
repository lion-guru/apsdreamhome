<?php $pageTitle = $pageTitle ?? 'Edge Dashboard'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-microchip me-2"></i>Edge Computing Dashboard</h4>
        <span class="badge bg-<?= ($status ?? 'offline') === 'online' ? 'success' : 'danger' ?> fs-6"><?= ucfirst($status ?? 'offline') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-server"></i></div>
                    <h5 class="mb-1"><?= count($edge_nodes ?? []) ?></h5>
                    <small class="text-muted">Edge Nodes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                    <h5 class="mb-1"><?= count(array_filter($edge_nodes ?? [], fn($n) => ($n['status'] ?? '') === 'online')) ?></h5>
                    <small class="text-muted">Online</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-danger mb-2"><i class="fas fa-exclamation-circle"></i></div>
                    <h5 class="mb-1"><?= count(array_filter($edge_nodes ?? [], fn($n) => ($n['status'] ?? '') === 'offline')) ?></h5>
                    <small class="text-muted">Offline</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-clock"></i></div>
                    <h5 class="mb-1"><?= ($status === 'online' ? 'Active' : 'Inactive') ?></h5>
                    <small class="text-muted">System Status</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-network-wired me-2"></i>Edge Nodes</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Name</th><th>Location</th><th>Status</th><th>Uptime</th><th>Last Ping</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($edge_nodes)): ?>
                            <?php foreach ($edge_nodes as $node): ?>
                                <tr>
                                    <td><?= $node['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($node['name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($node['location'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($node['status'] ?? 'offline') === 'online' ? 'success' : 'danger' ?>"><?= ucfirst($node['status'] ?? 'offline') ?></span></td>
                                    <td><?= htmlspecialchars($node['uptime'] ?? '0h') ?></td>
                                    <td><?= htmlspecialchars($node['last_ping'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No edge nodes configured</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
