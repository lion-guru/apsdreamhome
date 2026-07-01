<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-line text-primary me-2"></i>Sales Manager Dashboard</h1>
                    <p class="text-muted small mb-0">Real-time sales performance and pipeline overview</p>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="<?php echo BASE_URL; ?>/admin/lead-kanban" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-columns me-1"></i>Pipeline Kanban
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-1"></i>All Leads
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-primary text-white h-100">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">Total Leads</div>
                                    <div class="h2 mb-0 fw-bold"><?php echo number_format($stats['total_leads']); ?></div>
                                    <small class="opacity-75">+<?php echo (int)$stats['new_leads_week']; ?> this week</small>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-success text-white h-100">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">Bookings (30d)</div>
                                    <div class="h2 mb-0 fw-bold"><?php echo number_format($stats['bookings_month']); ?></div>
                                    <small class="opacity-75">₹<?php echo number_format($stats['revenue_month'] / 1000, 0); ?>K revenue</small>
                                </div>
                                <i class="fas fa-handshake fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-warning text-white h-100">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">Conversion Rate</div>
                                    <div class="h2 mb-0 fw-bold"><?php echo number_format($stats['conversion_rate'], 1); ?>%</div>
                                    <small class="opacity-75">₹<?php echo number_format($stats['avg_deal_size'] / 1000, 0); ?>K avg deal</small>
                                </div>
                                <i class="fas fa-bullseye fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card border-0 shadow-sm bg-gradient-info text-white h-100">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-uppercase small opacity-75">Commissions (30d)</div>
                                    <div class="h2 mb-0 fw-bold">₹<?php echo number_format($stats['commissions_month'] / 1000, 0); ?>K</div>
                                    <small class="opacity-75"><?php echo (int)$stats['active_agents']; ?> active agents</small>
                                </div>
                                <i class="fas fa-coins fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Lead Trend (Last 6 Months)</h5>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <canvas id="leadTrendChart" height="100"></canvas>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="fas fa-user-plus me-2 text-info"></i>Recent Leads</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($recentLeads, 0, 5) as $l): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="small"><?php echo htmlspecialchars($l['name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($l['phone'] ?? $l['email'] ?? '—'); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-<?php
                                                        $s = $l['status'] ?? 'new';
                                                        echo $s === 'closed_won' ? 'success' : ($s === 'closed_lost' ? 'danger' : 'secondary');
                                                    ?>"><?php echo htmlspecialchars($s); ?></span>
                                                    <?php if (($l['score'] ?? 0) > 0): ?>
                                                        <br><span class="badge bg-warning mt-1"><?php echo (int)$l['score']; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i>Recent Bookings</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($recentBookings, 0, 5) as $b): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="small"><?php echo htmlspecialchars($b['user_name'] ?? 'Customer'); ?></strong>
                                                    <br><small class="text-muted"><?php echo date('M j', strtotime($b['created_at'])); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <strong class="text-success">₹<?php echo number_format((float)$b['amount'] / 1000, 0); ?>K</strong>
                                                    <br><span class="badge bg-<?php echo ($b['status'] ?? '') === 'confirmed' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($b['status'] ?? 'pending'); ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top Performers (30d)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($leaderboard as $i => $p): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-<?php echo $i === 0 ? 'warning' : ($i === 1 ? 'secondary' : 'light text-dark'); ?> me-2" style="width:24px;"><?php echo $i + 1; ?></span>
                                            <div>
                                                <strong class="small"><?php echo htmlspecialchars($p['name']); ?></strong>
                                                <br><small class="text-muted"><?php echo (int)$p['won_count']; ?> won / <?php echo (int)$p['lead_count']; ?> leads</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($leaderboard)): ?>
                                    <div class="text-muted text-center small py-3">No agent activity in last 30 days</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-filter me-2 text-info"></i>Pipeline Breakdown</h5>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <?php
                            $stageColors = [
                                'new' => 'primary', 'contacted' => 'info', 'qualified' => 'success',
                                'proposal' => 'warning', 'negotiation' => 'secondary',
                                'closed_won' => 'success', 'closed_lost' => 'danger', 'nurture' => 'info'
                            ];
                            $totalLeadsInPipeline = array_sum(array_map(function($r) { return (int)$r['count']; }, $pipelineByStage));
                            foreach ($pipelineByStage as $stage => $row):
                                $count = (int)$row['count'];
                                $pct = $totalLeadsInPipeline > 0 ? round(($count / $totalLeadsInPipeline) * 100, 1) : 0;
                                $color = $stageColors[$stage] ?? 'secondary';
                            ?>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $stage))); ?></small>
                                        <small class="text-muted"><?php echo $count; ?> (<?php echo $pct; ?>%)</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $pct; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($pipelineByStage)): ?>
                                <p class="text-muted text-center small mb-0">No pipeline data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); }
.bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('leadTrendChart').getContext('2d');
    const trendData = <?php echo json_encode($monthlyTrend); ?>;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.label),
            datasets: [{
                label: 'Leads',
                data: trendData.map(d => d.count),
                borderColor: '#0d9488',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#0d9488'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
});
</script>
