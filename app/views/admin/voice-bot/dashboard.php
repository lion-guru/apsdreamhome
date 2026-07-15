<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-microphone-alt me-2 text-purple"></i>Voice Bot Dashboard</h4>
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['total_calls'] ?? 0) ?></h3>
                    <small>Total Calls</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['connected'] ?? 0) ?></h3>
                    <small>Connected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['interested'] ?? 0) ?></h3>
                    <small>Interested</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['avg_duration'] ?? 0) ?>s</h3>
                    <small>Avg Duration</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header"><i class="fas fa-list me-1"></i>Recent Calls</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Phone</th><th>Duration</th><th>Status</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php if (!empty($calls)): ?>
                            <?php foreach ($calls as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                                    <td><?= (int)($c['duration'] ?? 0) ?>s</td>
                                    <td><span class="badge bg-<?= $c['status'] === 'completed' ? 'success' : 'warning' ?>"><?= htmlspecialchars($c['status'] ?? '') ?></span></td>
                                    <td><small><?= htmlspecialchars($c['created_at'] ?? '') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-muted text-center">No calls yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>