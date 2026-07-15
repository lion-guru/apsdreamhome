<?php $pageTitle = $pageTitle ?? 'Incident Response'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-bolt me-2"></i>Incident Response</h4>
        <button class="btn btn-danger btn-sm"><i class="fas fa-plus me-1"></i>New Incident</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Incidents</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Title</th><th>Severity</th><th>Status</th><th>Assigned To</th><th>Created</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($incidents)): ?>
                            <?php foreach ($incidents as $inc): ?>
                                <tr>
                                    <td><?= $inc['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($inc['title'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($inc['severity'] ?? 'low') === 'critical' ? 'danger' : (($inc['severity'] ?? 'low') === 'high' ? 'warning' : 'secondary') ?>"><?= ucfirst($inc['severity'] ?? 'low') ?></span></td>
                                    <td><span class="badge bg-<?= ($inc['status'] ?? 'open') === 'resolved' ? 'success' : (($inc['status'] ?? 'open') === 'in_progress' ? 'info' : 'warning') ?>"><?= str_replace('_', ' ', ucfirst($inc['status'] ?? 'open')) ?></span></td>
                                    <td><?= htmlspecialchars($inc['assigned_to'] ?? 'Unassigned') ?></td>
                                    <td><?= htmlspecialchars($inc['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No incidents recorded</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
