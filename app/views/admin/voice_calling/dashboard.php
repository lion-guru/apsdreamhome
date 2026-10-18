<?php
/**
 * Voice Calling Dashboard
 * S347 - AI Voice Calling System
 */
$page_title = $page_title ?? 'AI Voice Calling Dashboard';
$stats = $stats ?? [];
$trend = $trend ?? [];
$providers = $providers ?? [];
$recentCalls = $recentCalls ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-phone-volume me-2 text-primary"></i>AI Voice Calling Dashboard</h4>
            <div>
                <a href="/admin/voice-calling/provider-config" class="btn btn-outline-primary me-2">
                    <i class="fas fa-cog me-1"></i>Provider Config
                </a>
                <a href="/admin/ivr-flows" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-sitemap me-1"></i>IVR Flows
                </a>
                <a href="/admin/voice-analytics" class="btn btn-primary">
                    <i class="fas fa-chart-line me-1"></i>Analytics
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Provider Status Cards -->
<div class="row mb-4">
    <div class="col-12">
        <h6 class="mb-3"><i class="fas fa-cloud me-1"></i>Voice Providers</h6>
        <div class="row">
            <?php foreach ($providers as $p): ?>
            <div class="col-md-3 mb-3">
                <div class="card h-100 <?= $p['configured'] ? 'border-success' : 'border-warning' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0"><?= htmlspecialchars($p['display_name']) ?></h6>
                            <span class="badge bg-<?= $p['configured'] ? 'success' : 'warning' ?>">
                                <?= $p['configured'] ? 'Configured' : 'Not Configured' ?>
                            </span>
                        </div>
                        <small class="text-muted d-block mb-2">Type: <?= htmlspecialchars($p['provider_type']) ?></small>
                        <?php if ($p['configured']): ?>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary test-provider-btn" 
                                        data-provider="<?= htmlspecialchars($p['service_name']) ?>">
                                    <i class="fas fa-play me-1"></i>Test
                                </button>
                                <a href="/admin/voice-calling/provider-config?provider=<?= urlencode($p['service_name']) ?>" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit me-1"></i>Config
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="/admin/voice-calling/provider-config?provider=<?= urlencode($p['service_name']) ?>" 
                               class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-plus me-1"></i>Add Credentials
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Key Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Today's Calls</h6>
                        <h2 class="mb-0"><?= number_format($stats['today_calls'] ?? 0) ?></h2>
                    </div>
                    <i class="fas fa-phone fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Connected</h6>
                        <h2 class="mb-0"><?= number_format($stats['today_connected'] ?? 0) ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Interested</h6>
                        <h2 class="mb-0"><?= number_format($stats['today_interested'] ?? 0) ?></h2>
                    </div>
                    <i class="fas fa-heart fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Leads Created</h6>
                        <h2 class="mb-0"><?= number_format($stats['today_leads'] ?? 0) ?></h2>
                    </div>
                    <i class="fas fa-user-plus fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Conversion Rate</h6>
                <h2 class="text-primary mb-0"><?= number_format($stats['conversion_rate'] ?? 0, 1) ?>%</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Avg Duration</h6>
                <h2 class="text-success mb-0"><?= round($stats['avg_duration'] ?? 0) ?>s</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Active Agents</h6>
                <h2 class="text-info mb-0"><?= $stats['active_agents'] ?? 0 ?> / <?= $stats['total_agents'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- 7-Day Trend Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-area me-1"></i>7-Day Call Trend</h5>
            </div>
            <div class="card-body">
                <?php if (empty($trend)): ?>
                    <div class="text-center text-muted py-4">No call data available for the last 7 days</div>
                <?php else: ?>
                    <canvas id="trendChart" height="80"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Calls -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-1"></i>Recent Calls</h5>
                <a href="/admin/ai-calling/call-logs" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentCalls)): ?>
                    <div class="text-center text-muted py-4">No recent calls</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Agent</th>
                                    <th>Lead</th>
                                    <th>Phone</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Interest</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCalls as $call): ?>
                                <tr>
                                    <td><?= date('M d, H:i', strtotime($call['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($call['agent_display_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($call['lead_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($call['lead_phone'] ?? 'N/A') ?></td>
                                    <td><?= round($call['duration'] ?? 0) ?>s</td>
                                    <td>
                                        <span class="badge bg-<?= $call['status'] === 'completed' ? 'success' : ($call['status'] === 'failed' ? 'danger' : 'secondary') ?>">
                                            <?= ucfirst($call['status'] ?? 'unknown') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= in_array($call['interest_level'] ?? '', ['hot','warm']) ? 'success' : ($call['interest_level'] === 'cold' ? 'danger' : 'secondary') ?>">
                                            <?= ucfirst($call['interest_level'] ?? 'unknown') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    <?php if (!empty($trend)): ?>
    const ctx = document.getElementById('trendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($trend, 'date')) ?>,
                datasets: [
                    { label: 'Total', data: <?= json_encode(array_column($trend, 'total')) ?>, borderColor: '#3b82f6', tension: 0.3 },
                    { label: 'Connected', data: <?= json_encode(array_column($trend, 'completed')) ?>, borderColor: '#10b981', tension: 0.3 },
                    { label: 'Interested', data: <?= json_encode(array_column($trend, 'interested')) ?>, borderColor: '#f59e0b', tension: 0.3 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true } } }
        });
    }
    <?php endif; ?>

    // Test Provider Buttons
    document.querySelectorAll('.test-provider-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const provider = this.dataset.provider;
            const original = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testing...';
            this.disabled = true;

            fetch('/admin/voice-calling/test-provider', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>' },
                body: 'provider=' + encodeURIComponent(provider)
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                this.innerHTML = original;
                this.disabled = false;
            })
            .catch(() => {
                alert('Test failed');
                this.innerHTML = original;
                this.disabled = false;
            });
        });
    });
});
</script>