<?php $stats = $stats ?? []; $recentCalls = $recentCalls ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Voice Agent Dashboard</h4>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center">
                <h3><?= $stats['total_calls'] ?? 0 ?></h3>
                <small>Total Calls</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center">
                <h3><?= $stats['connected'] ?? 0 ?></h3>
                <small>Connected</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body text-center">
                <h3><?= $stats['converted'] ?? 0 ?></h3>
                <small>Converted</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body text-center">
                <h3><?= $stats['avg_duration'] ?? '0:00' ?></h3>
                <small>Avg Duration</small>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Recent Calls</span>
                <a href="<?= BASE_URL ?>admin/voice-users/history" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Agent</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCalls)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No recent calls.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentCalls as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['customer_name'] ?? $c['phone'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($c['agent_name'] ?? $c['agent'] ?? 'Auto') ?></td>
                                        <td><?= $c['duration'] ?? $c['call_duration'] ?? '0:00' ?></td>
                                        <td><span class="badge bg-<?= ($c['status'] ?? 'pending') === 'completed' ? 'success' : (($c['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= ucfirst($c['status'] ?? 'pending') ?></span></td>
                                        <td><small><?= htmlspecialchars($c['created_at'] ?? $c['call_time'] ?? '-') ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header aps-cp-card-header">Quick Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="<?= BASE_URL ?>admin/voice-users/schedule" class="btn btn-outline-primary"><i class="fas fa-calendar-plus"></i> Schedule Calls</a>
                <a href="<?= BASE_URL ?>admin/voice-users/extracted-leads" class="btn btn-outline-success"><i class="fas fa-users"></i> View Extracted Leads</a>
                <a href="<?= BASE_URL ?>admin/voice-users/scripts" class="btn btn-outline-info"><i class="fas fa-scroll"></i> Manage Scripts</a>
                <a href="<?= BASE_URL ?>admin/voice-users/oln" class="btn btn-outline-warning"><i class="fas fa-funnel"></i> Lead Nurturing</a>
            </div>
        </div>
    </div>
</div>
