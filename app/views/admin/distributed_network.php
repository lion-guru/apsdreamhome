<?php $pageTitle = $pageTitle ?? 'Distributed Network'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-sitemap me-2"></i>Distributed Network Topology</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-globe me-2"></i>Network Nodes</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Node Name</th><th>Type</th><th>Region</th><th>Status</th><th>Latency</th><th>Last Sync</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($networks)): ?>
                            <?php foreach ($networks as $n): ?>
                                <tr>
                                    <td><?= $n['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($n['name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($n['type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($n['region'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($n['status'] ?? 'offline') === 'online' ? 'success' : 'danger' ?>"><?= ucfirst($n['status'] ?? 'offline') ?></span></td>
                                    <td><?= htmlspecialchars($n['latency'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($n['last_sync'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No network nodes found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
