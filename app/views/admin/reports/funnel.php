<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-filter me-2"></i>Sales Funnel Report</h1>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>Warm Leads</h6>
                    <h2 class="mb-0"><?= $funnel['warm_leads'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>Proposals Sent</h6>
                    <h2 class="mb-0"><?= $funnel['proposals'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>In Negotiation</h6>
                    <h2 class="mb-0"><?= $funnel['negotiations'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <h6>Closed Deals</h6>
                    <h2 class="mb-0"><?= $funnel['closed_deals'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">Funnel Visualization</h5></div>
        <div class="card-body aps-cp-card-body">
            <canvas id="funnelChart" height="100"></canvas>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">Monthly Closed Deals (Last 6 Months)</h5></div>
        <div class="card-body aps-cp-card-body">
            <canvas id="monthlyChart" height="80"></canvas>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Conversion Rate</h5></div>
        <div class="card-body text-center py-4">
            <?php $convRate = ($funnel['warm_leads'] ?? 0) > 0 ? round(($funnel['closed_deals'] ?? 0) * 100 / $funnel['warm_leads'], 1) : 0; ?>
            <h1 class="display-4 text-primary"><?= $convRate ?>%</h1>
            <p class="text-muted">Overall Lead-to-Deal Conversion</p>
            <div class="progress style-20185">
                <div class="progress-bar bg-success style-751"><?= $convRate ?>%</div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
new Chart(document.getElementById('funnelChart'), {
    type: 'bar',
    data: {
        labels: ['Warm Leads', 'Proposals', 'Negotiations', 'Closed Deals'],
        datasets: [{
            label: 'Count',
            data: [<?= $funnel['warm_leads'] ?>, <?= $funnel['proposals'] ?>, <?= $funnel['negotiations'] ?>, <?= $funnel['closed_deals'] ?>],
            backgroundColor: ['#ffc107', '#17a2b8', '#007bff', '#28a745'],
            borderRadius: 5,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

<?php if (!empty($monthly_conversions)): ?>
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthly_conversions, 'month')) ?>,
        datasets: [{
            label: 'Closed Deals',
            data: <?= json_encode(array_map('intval', array_column($monthly_conversions, 'deals'))) ?>,
            borderColor: '#28a745',
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(40,167,69,0.1)',
        }]
    },
    options: { responsive: true }
});
<?php endif; ?>
</script>
