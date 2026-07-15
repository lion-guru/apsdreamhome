<?php $pageTitle = $pageTitle ?? 'Threat Detection'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-shield-virus me-2"></i>Threat Detection</h4>
        <button class="btn btn-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Scan Now</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Threats</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Threat Name</th><th>Category</th><th>Severity</th><th>Source IP</th><th>Status</th><th>Detected At</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($threats)): ?>
                            <?php foreach ($threats as $t): ?>
                                <tr>
                                    <td><?= $t['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($t['name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($t['category'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($t['severity'] ?? 'low') === 'critical' ? 'danger' : (($t['severity'] ?? 'low') === 'high' ? 'warning' : 'secondary') ?>"><?= ucfirst($t['severity'] ?? 'low') ?></span></td>
                                    <td><?= htmlspecialchars($t['source_ip'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($t['status'] ?? 'open') === 'resolved' ? 'success' : 'danger' ?>"><?= ucfirst($t['status'] ?? 'open') ?></span></td>
                                    <td><?= htmlspecialchars($t['detected_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No threats found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
