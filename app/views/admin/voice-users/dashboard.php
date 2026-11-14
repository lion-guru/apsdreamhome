<?php $stats = $stats ?? []; $recentCalls = $recentCalls ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-robot text-primary me-2" aria-hidden="true"></i>AI Calling Dashboard</h4>
    <a href="<?= BASE_URL ?>/admin/voice-users/history" class="btn btn-sm btn-outline-primary"><i class="fas fa-clock-rotate-left me-1" aria-hidden="true"></i>View All Calls</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center">
                <h3><?= (int)($stats['total_calls'] ?? 0) ?></h3>
                <small>Calls Today</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center">
                <h3><?= (int)($stats['connected'] ?? 0) ?></h3>
                <small>Connected</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body text-center">
                <h3><?= (int)($stats['converted'] ?? 0) ?></h3>
                <small>Converted</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body text-center">
                <h3><?= htmlspecialchars($stats['avg_duration'] ?? '0:00') ?></h3>
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
                <a href="<?= BASE_URL ?>/admin/voice-users/history" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Lead / Phone</th>
                                <th scope="col">Script</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Status</th>
                                <th scope="col">Outcome</th>
                                <th scope="col">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCalls)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No recent calls.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentCalls as $c): ?>
                                    <?php
                                    $dur = (int)($c['duration_seconds'] ?? 0);
                                    $durLabel = $dur > 0 ? sprintf('%d:%02d', intdiv($dur, 60), $dur % 60) : '—';
                                    $stCls = match ($c['status'] ?? '') {
                                        'completed' => 'success',
                                        'in_progress' => 'primary',
                                        'failed' => 'danger',
                                        'no_answer' => 'warning',
                                        default => 'secondary',
                                    };
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($c['lead_name'] ?? '—') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($c['lead_phone'] ?? $c['phone'] ?? '—') ?></small>
                                        </td>
                                        <td><small><?= htmlspecialchars($c['script_name'] ?? $c['script_template'] ?? 'AI Call') ?></small></td>
                                        <td><?= htmlspecialchars($durLabel) ?></td>
                                        <td><span class="badge bg-<?= $stCls ?>"><?= ucfirst(str_replace('_', ' ', $c['status'] ?? 'pending')) ?></span></td>
                                        <td><small><?= htmlspecialchars($c['customer_response'] ?? '—') ?></small></td>
                                        <td><small><?= htmlspecialchars($c['created_at'] ?? '—') ?></small></td>
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
            <div class="card-body">
                <a href="<?= BASE_URL ?>/admin/voice-users/schedule" class="btn btn-primary w-100 mb-2"><i class="fas fa-calendar-check me-2" aria-hidden="true"></i>Call Schedule</a>
                <a href="<?= BASE_URL ?>/admin/voice-users/extracted-leads" class="btn btn-outline-success w-100 mb-2"><i class="fas fa-user-check me-2" aria-hidden="true"></i>Extracted Leads</a>
                <a href="<?= BASE_URL ?>/admin/voice-users/scripts" class="btn btn-outline-info w-100 mb-2"><i class="fas fa-file-alt me-2" aria-hidden="true"></i>Call Scripts</a>
                <a href="<?= BASE_URL ?>/admin/voice-users/history" class="btn btn-outline-secondary w-100"><i class="fas fa-list me-2" aria-hidden="true"></i>Call History</a>
            </div>
        </div>
    </div>
</div>
