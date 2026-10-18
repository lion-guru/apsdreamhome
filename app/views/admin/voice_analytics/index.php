<?php
/**
 * Voice Calling Analytics
 * S347
 */
$page_title = $page_title ?? 'Voice Calling Analytics';
$range = $range ?? '7d';
$analytics = $analytics ?? [];
$overview = $analytics['overview'] ?? [];
$daily_trend = $analytics['daily_trend'] ?? [];
$agent_performance = $analytics['agent_performance'] ?? [];
$interest_breakdown = $analytics['interest_breakdown'] ?? [];
$call_outcomes = $analytics['call_outcomes'] ?? [];
$hourly_distribution = $analytics['hourly_distribution'] ?? [];
$lead_conversion_funnel = $analytics['lead_conversion_funnel'] ?? [];
$provider_comparison = $analytics['provider_comparison'] ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Voice Calling Analytics</h4>
            <div class="btn-group" role="group">
                <?php foreach (['24h','7d','30d','90d'] as $r): ?>
                <a href="/admin/voice-analytics?range=<?= $r ?>" 
                   class="btn btn-outline-<?= $range === $r ? 'primary' : 'secondary' ?> btn-sm">
                    <?= $r ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Overview Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-white-50">Total Calls</h6>
                        <h2 class="mb-0"><?= number_format($overview['total_calls'] ?? 0) ?></h2>
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
                        <h2 class="mb-0"><?= number_format($overview['connected_calls'] ?? 0) ?></h2>
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
                        <h6 class="card-title">Avg Duration</h6>
                        <h2 class="mb-0"><?= round($overview['avg_duration'] ?? 0) ?>s</h2>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
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
                        <h2 class="mb-0"><?= number_format($overview['total_leads'] ?? 0) ?></h2>
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
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Conversion Rate</h6>
                <h2 class="text-primary mb-0"><?= number_format($overview['conversion_rate'] ?? 0, 2) ?>%</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Cost Per Call</h6>
                <h2 class="text-success mb-0">₹<?= number_format($overview['cost_per_call'] ?? 0, 2) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Connect Rate</h6>
                <h2 class="text-info mb-0">
                    <?= $overview['total_calls'] > 0 ? round($overview['connected_calls']/$overview['total_calls']*100, 1) : 0 ?>%
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- Daily Trend Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-area me-1"></i>Daily Call Trend</h5>
            </div>
            <div class="card-body">
                <?php if (empty($daily_trend)): ?>
                    <div class="text-center text-muted py-4">No data for selected period</div>
                <?php else: ?>
                    <canvas id="trendChart" height="100"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Second Row Charts -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-users me-1"></i>Agent Performance</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($agent_performance)): ?>
                    <div class="text-center text-muted py-3">No agent data</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr><th>Agent</th><th>Calls</th><th>Connected</th><th>Interested</th><th>Leads</th><th>Avg Dur</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agent_performance as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['agent_name'] ?? 'Unknown') ?></td>
                                    <td><?= $a['total_calls'] ?></td>
                                    <td><?= $a['connected'] ?></td>
                                    <td><?= $a['interested'] ?></td>
                                    <td><?= $a['leads_created'] ?></td>
                                    <td><?= round($a['avg_duration'] ?? 0) ?>s</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-1"></i>Interest Breakdown</h6>
            </div>
            <div class="card-body">
                <?php if (empty($interest_breakdown)): ?>
                    <div class="text-center text-muted py-3">No data</div>
                <?php else: ?>
                    <canvas id="interestChart" height="150"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Third Row -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-flag-checkered me-1"></i>Call Outcomes</h6>
            </div>
            <div class="card-body">
                <?php if (empty($call_outcomes)): ?>
                    <div class="text-center text-muted py-3">No data</div>
                <?php else: ?>
                    <canvas id="outcomeChart" height="150"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-clock me-1"></i>Hourly Distribution</h6>
            </div>
            <div class="card-body">
                <?php if (empty($hourly_distribution)): ?>
                    <div class="text-center text-muted py-3">No data</div>
                <?php else: ?>
                    <canvas id="hourlyChart" height="150"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Lead Conversion Funnel -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-funnel-dollar me-1"></i>Lead Conversion Funnel</h6>
            </div>
            <div class="card-body">
                <?php if (empty($lead_conversion_funnel)): ?>
                    <div class="text-center text-muted py-3">No data</div>
                <?php else: ?>
                    <div class="funnel-chart">
                        <?php foreach ($lead_conversion_funnel as $i => $stage): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong><?= $stage['stage'] ?></strong></span>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary me-2" style="width: <?= max($stage['pct'], 5) ?>%; height: 20px; border-radius: 4px;"></div>
                                <span class="text-muted small"><?= number_format($stage['count']) ?> (<?= $stage['pct'] ?>%)</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cloud me-1"></i>Provider Comparison</h6>
            </div>
            <div class="card-body">
                <?php if (empty($provider_comparison)): ?>
                    <div class="text-center text-muted py-3">No provider data</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Provider</th><th>Calls</th><th>Connected</th><th>Avg Dur</th><th>Interested</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($provider_comparison as $p): ?>
                                <tr>
                                    <td><?= ucfirst($p['provider']) ?></td>
                                    <td><?= $p['total_calls'] ?></td>
                                    <td><?= $p['connected'] ?></td>
                                    <td><?= round($p['avg_duration'] ?? 0) ?>s</td>
                                    <td><?= $p['interested'] ?></td>
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
    <?php if (!empty($daily_trend)): ?>
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($daily_trend, 'date')) ?>,
            datasets: [
                { label: 'Total', data: <?= json_encode(array_column($daily_trend, 'total')) ?>, borderColor: '#3b82f6', tension: 0.3 },
                { label: 'Connected', data: <?= json_encode(array_column($daily_trend, 'connected')) ?>, borderColor: '#10b981', tension: 0.3 },
                { label: 'Interested', data: <?= json_encode(array_column($daily_trend, 'interested')) ?>, borderColor: '#f59e0b', tension: 0.3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    <?php endif; ?>

    // Interest Breakdown
    <?php if (!empty($interest_breakdown)): ?>
    new Chart(document.getElementById('interestChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($interest_breakdown, 'level')) ?>,
            datasets: [{ data: <?= json_encode(array_column($interest_breakdown, 'count')) ?>, backgroundColor: ['#10b981','#f59e0b','#ef4444','#6b7280'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
    <?php endif; ?>

    // Call Outcomes
    <?php if (!empty($call_outcomes)): ?>
    new Chart(document.getElementById('outcomeChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($call_outcomes, 'outcome')) ?>,
            datasets: [{ data: <?= json_encode(array_column($call_outcomes, 'count')) ?>, backgroundColor: ['#10b981','#ef4444','#f59e0b','#3b82f6','#6b7280'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
    <?php endif; ?>

    // Hourly Distribution
    <?php if (!empty($hourly_distribution)): ?>
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($h) => sprintf('%02d:00', $h['hour']), $hourly_distribution)) ?>,
            datasets: [{ label: 'Calls', data: <?= json_encode(array_column($hourly_distribution, 'count')) ?>, backgroundColor: '#3b82f6' }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    <?php endif; ?>
});
</script>