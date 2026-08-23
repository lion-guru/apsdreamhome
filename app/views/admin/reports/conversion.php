<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-chart-line me-2"></i>Conversion Analytics</h1>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>Total Leads (12mo)</h6>
                    <h2 class="mb-0"><?= array_sum(array_map(function($d) { return (int)($d['leads'] ?? 0); }, $conversion_data ?: [])) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>Total Deals (12mo)</h6>
                    <h2 class="mb-0"><?= array_sum(array_map(function($d) { return (int)($d['deals'] ?? 0); }, $conversion_data ?: [])) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>Overall Conversion</h6>
                    <?php $totalLeads = array_sum(array_map(function($d) { return (int)($d['leads'] ?? 0); }, $conversion_data ?: [])); ?>
                    <?php $totalDeals = array_sum(array_map(function($d) { return (int)($d['deals'] ?? 0); }, $conversion_data ?: [])); ?>
                    <h2 class="mb-0"><?= $totalLeads > 0 ? round($totalDeals * 100 / $totalLeads, 1) : 0 ?>%</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">Monthly Conversion Trends</h5></div>
        <div class="card-body aps-cp-card-body">
            <canvas id="conversionTrendChart" height="100"></canvas>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Monthly Breakdown</h5></div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($conversion_data)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead><tr><th>Month</th><th>Leads</th><th>Proposals</th><th>Deals</th><th>Leadâ†’Deal %</th><th>Proposalâ†’Deal %</th></tr></thead>
                        <tbody>
                            <?php foreach ($conversion_data as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['month'] ?? '') ?></strong></td>
                                <td><?= (int)($row['leads'] ?? 0) ?></td>
                                <td><?= (int)($row['proposals'] ?? 0) ?></td>
                                <td><span class="badge bg-success"><?= (int)($row['deals'] ?? 0) ?></span></td>
                                <td>
                                    <div class="progress style-87912">
                                        <div class="progress-bar bg-success style-83710"></div>
                                    </div>
                                    <small><?= ($row['lead_to_deal_pct'] ?? 0) ?>%</small>
                                </td>
                                <td>
                                    <div class="progress style-87912">
                                        <div class="progress-bar bg-info style-12894"></div>
                                    </div>
                                    <small><?= ($row['proposal_to_deal_pct'] ?? 0) ?>%</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No conversion data available</h5>
                    <p>Data will appear once leads and deals are created in the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
<?php if (!empty($conversion_data)): ?>
new Chart(document.getElementById('conversionTrendChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($conversion_data, 'month')) ?>,
        datasets: [
            { label: 'Leads', data: <?= json_encode(array_map('intval', array_column($conversion_data, 'leads'))) ?>, backgroundColor: '#007bff', borderRadius: 3 },
            { label: 'Proposals', data: <?= json_encode(array_map('intval', array_column($conversion_data, 'proposals'))) ?>, backgroundColor: '#ffc107', borderRadius: 3 },
            { label: 'Deals', data: <?= json_encode(array_map('intval', array_column($conversion_data, 'deals'))) ?>, backgroundColor: '#28a745', borderRadius: 3 },
        ]
    },
    options: { responsive: true }
});
<?php endif; ?>
</script>
